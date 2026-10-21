const { test, expect } = require('@playwright/test');
const productsData = require('./data/products_data.json');
const { cleanRupiah } = require('./helpers/financial_helper.js');

const BASE_URL = process.env.BASE_URL || 'http://127.0.0.1:8000';
const CALC_URL = `${BASE_URL}/simulasi-kb/calculate`;

function escapeRegex(text) {
  return String(text).replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
}

// Baris pada "spreadsheet" simulasi (label di kolom pertama, input/output di kolom kedua).
function simRow(page, label) {
  return page.locator('tr').filter({
    has: page.locator('.kb-cell-label', { hasText: new RegExp(`^${escapeRegex(label)}$`) }),
  });
}

async function fillField(page, label, value) {
  const input = simRow(page, label).locator('input').first();
  await input.fill(String(value));
  await input.blur();
}

async function pressEnterField(page, label) {
  await simRow(page, label).locator('input').first().press('Enter');
}

async function selectField(page, label, value) {
  await simRow(page, label).locator('select').selectOption({ label: value });
}

async function selectPreferred(page, label, preferred) {
  const select = simRow(page, label).locator('select');
  const options = (await select.locator('option').allTextContents()).map((o) => o.trim());
  const candidate =
    options.find((o) => o === preferred) ||
    options.find((o) => o.toUpperCase().includes(preferred.toUpperCase())) ||
    options.find((o) => o !== '' && o !== 'Pilih');

  if (candidate) {
    await select.selectOption({ label: candidate });
  }
}

async function readSelectValue(page, label) {
  return (await simRow(page, label).locator('select').inputValue()).trim();
}

async function readOutput(page, label) {
  const loc = simRow(page, label).locator('.kb-sheet-output');
  if ((await loc.count()) === 0) return '';
  return (await loc.first().innerText()).trim();
}

async function readRowError(page, label) {
  const loc = simRow(page, label).locator('.text-danger.small');
  if ((await loc.count()) === 0) return '';
  return (await loc.first().innerText()).trim();
}

async function readWarning(page) {
  const loc = page.locator('.alert-warning');
  if ((await loc.count()) === 0) return '';
  return (await loc.first().innerText()).trim();
}

async function getCsrfToken(page) {
  return page.evaluate(() => {
    const inline = Array.from(document.querySelectorAll('script'))
      .map((s) => s.textContent || '')
      .find((text) => text.includes('const csrf'));
    const match = inline && inline.match(/const csrf = '([^']+)'/);
    return match ? match[1] : '';
  });
}

// Bandingkan UI dengan engine server: hitung ulang via endpoint yang sama.
async function apiCalculate(page, payload) {
  const csrf = await getCsrfToken(page);
  const response = await page.request.post(CALC_URL, {
    headers: {
      'Content-Type': 'application/json',
      Accept: 'application/json',
      'X-CSRF-TOKEN': csrf,
    },
    data: payload,
  });

  return { status: response.status(), body: await response.json().catch(() => null) };
}

async function waitForCalculation(page) {
  await expect
    .poll(async () => (await readOutput(page, 'ANGSURAN')).trim(), { timeout: 20000 })
    .not.toBe('-');
}

async function setupSimulation(page, scenario, overrides = {}) {
  const gaji = overrides.gaji ?? scenario.gaji_pensiun ?? 5000000;

  await test.step('Buka halaman Simulasi', async () => {
    await page.goto('/simulasi');
    await expect(simRow(page, 'Tanggal Lahir')).toBeVisible();
  });

  await test.step('Pilih Jenis Pensiun', async () => {
    await selectField(page, 'Jenis Pensiun', scenario.jenis_pensiun || 'Sendiri');
  });

  await test.step('Pilih Bank Tujuan', async () => {
    await selectPreferred(page, 'Bank Tujuan', 'KB');
  });

  await test.step('Input Tanggal Simulasi', async () => {
    await fillField(page, 'Tanggal Simulasi', scenario.tanggal_simulasi);
  });

  await test.step('Input Tanggal Lahir', async () => {
    await fillField(page, 'Tanggal Lahir', scenario.tanggal_lahir);
  });

  await test.step('Input Gaji Pensiun', async () => {
    await fillField(page, 'Gaji Pensiun', gaji);
  });

  await test.step('Input Angsuran Lainnya', async () => {
    await fillField(page, 'Angsuran Lainnya', overrides.angsuran_lainnya ?? scenario.angsuran_lainnya ?? 0);
  });

  await test.step('Input Tenor', async () => {
    await fillField(page, 'Tenor', overrides.tenor ?? 60);
  });

  await test.step('Input Plafond', async () => {
    await fillField(page, 'Plafond', overrides.plafond ?? 100000000);
  });

  await test.step('Jalankan Perhitungan Simulasi', async () => {
    await pressEnterField(page, 'Plafond');
    await waitForCalculation(page);
  });

  return {
    produk: await readSelectValue(page, 'Produk'),
    jenisPensiun: await readSelectValue(page, 'Jenis Pensiun'),
    bankTujuan: await readSelectValue(page, 'Bank Tujuan'),
    gaji,
  };
}

function buildCalcPayload(setup, scenario, overrides = {}) {
  return {
    produk: setup.produk,
    jenis_pensiun: setup.jenisPensiun,
    bank_tujuan: setup.bankTujuan,
    mutasi: 'Non Mutasi',
    plafond: 100000000,
    tenor: 60,
    rate_percent_override: 16,
    admin_angsuran_percent_override: 10,
    tanggal_lahir: scenario.tanggal_lahir,
    tanggal_simulasi: scenario.tanggal_simulasi,
    instansi: scenario.instansi || 'TASPEN',
    gaji_pensiun: setup.gaji,
    angsuran_lainnya: scenario.angsuran_lainnya || 0,
    blokir_angsuran: '1',
    pelunasan: 0,
    nomor_hp: '08xxxxxxxx',
    ...overrides,
  };
}

test.describe('Core Test Engine: Validasi Simulasi Pinjaman Laravel', () => {
  productsData.forEach((scenario) => {
    test.describe(`[Produk: ${scenario.product_name}]`, () => {
      test('TC-BOUND: Validasi batas Min Plafond, Max Plafond & Max Tenor (berbasis input)', async ({ page }) => {
        const setup = await setupSimulation(page, scenario, { tenor: 60, plafond: 100000000 });
        await test.step('Verifikasi Produk terpilih sesuai skenario', async () => {
          expect(setup.produk).toBe(scenario.expected_produk);
        });

        let basePayload;
        let baseLimits;
        await test.step('Ambil batas plafond & tenor dari engine server', async () => {
          basePayload = buildCalcPayload(setup, scenario);
          baseLimits = (await apiCalculate(page, basePayload)).body.limits;
        });

        await test.step('Validasi batas normal: plafond & tenor diterima', async () => {
          expect(baseLimits.plafond_min_valid).toBe(true);
          expect(baseLimits.plafond_valid).toBe(true);
          expect(baseLimits.tenor_valid).toBe(true);
        });

        // --- Plafond di bawah batas MINIMUM produk ---
        const belowMin = Math.max(1, Math.floor(Number(baseLimits.plafond_min)) - 100000);
        await test.step('Uji plafond di bawah batas minimum', async () => {
          const belowLimits = (await apiCalculate(page, { ...basePayload, plafond: belowMin })).body.limits;
          expect(belowLimits.plafond_min_valid).toBe(false);
          expect(belowLimits.is_valid).toBe(false);
        });

        await test.step('Validasi pesan error plafond minimum di UI', async () => {
          await fillField(page, 'Plafond', belowMin);
          await pressEnterField(page, 'Plafond');
          await expect.poll(() => readRowError(page, 'Plafond'), { timeout: 20000 }).toMatch(/bawah batas minimal/i);
          await expect.poll(() => readWarning(page), { timeout: 20000 }).toMatch(/plafond min/i);
        });

        // --- Plafond di atas batas MAKSIMUM ---
        const aboveMax = Math.floor(Number(baseLimits.plafond_max_displayed ?? baseLimits.plafond_max)) + 10000000;
        await test.step('Uji plafond di atas batas maksimum', async () => {
          const aboveLimits = (await apiCalculate(page, { ...basePayload, plafond: aboveMax })).body.limits;
          expect(aboveLimits.plafond_valid).toBe(false);
        });

        await test.step('Validasi pesan error plafond maksimum di UI', async () => {
          await fillField(page, 'Plafond', aboveMax);
          await pressEnterField(page, 'Plafond');
          await expect.poll(() => readRowError(page, 'Plafond'), { timeout: 20000 }).toMatch(/maksimal/i);
        });

        // --- Tenor di atas batas MAKSIMUM (bukan hanya plafond) ---
        const tenorAbove = Math.floor(Number(baseLimits.tenor_max)) + 12;
        await test.step('Uji tenor di atas batas maksimum', async () => {
          const tenorLimits = (await apiCalculate(page, { ...basePayload, plafond: 100000000, tenor: tenorAbove })).body.limits;
          expect(tenorLimits.tenor_valid).toBe(false);
        });

        await test.step('Validasi pesan error tenor maksimum di UI', async () => {
          await fillField(page, 'Plafond', 100000000);
          await fillField(page, 'Tenor', tenorAbove);
          await pressEnterField(page, 'Tenor');
          await expect.poll(() => readRowError(page, 'Tenor'), { timeout: 20000 }).toMatch(/maksimal\s+\d+\s+bulan/i);
        });

        // --- Batas bergantung pada seluruh input (mis. gaji), bukan hanya produk & plafond ---
        await test.step('Validasi batas mengikuti gaji debitur', async () => {
          const lowerGaji = (await apiCalculate(page, { ...basePayload, gaji_pensiun: 3000000 })).body.limits.plafond_max;
          const higherGaji = (await apiCalculate(page, { ...basePayload, gaji_pensiun: 6000000 })).body.limits.plafond_max;
          expect(Number(higherGaji)).toBeGreaterThan(Number(lowerGaji));
        });
      });

      test('TC-RECOM: Tenor Max & Plafond Max mengikuti engine untuk input yang sama', async ({ page }) => {
        const setup = await setupSimulation(page, scenario, { tenor: 60, plafond: 100000000 });

        let data;
        await test.step('Ambil hasil rekomendasi dari engine server', async () => {
          const payload = buildCalcPayload(setup, scenario);
          data = (await apiCalculate(page, payload)).body.data;
        });

        await test.step('Validasi Tenor Max di UI sesuai engine', async () => {
          await expect
            .poll(() => readOutput(page, 'Tenor Max'), { timeout: 20000 })
            .toMatch(new RegExp(`${Math.round(Number(data.tenor_max))}\\s*bulan`));
        });

        await test.step('Validasi Plafond Max di UI sesuai engine', async () => {
          await expect
            .poll(async () => cleanRupiah(await readOutput(page, 'Plafond Max')), { timeout: 20000 })
            .toBe(Math.round(Number(data.plafond_max)));
        });

        // Tenor Max dipengaruhi tanggal lahir (debitur lebih tua => sisa masa lebih pendek).
        await test.step('Validasi Tenor Max menyesuaikan usia debitur', async () => {
          const payload = buildCalcPayload(setup, scenario);
          const older = (await apiCalculate(page, { ...payload, tanggal_lahir: '1948-01-01' })).body.data.tenor_max;
          expect(Number(older)).toBeLessThan(Number(data.tenor_max));
        });
      });

      test('TC-CALC: Rincian finansial di UI sama dengan hasil engine server', async ({ page }) => {
        const setup = await setupSimulation(page, scenario, { tenor: 60, plafond: 100000000 });

        let data;
        await test.step('Ambil rincian finansial dari engine server', async () => {
          const payload = buildCalcPayload(setup, scenario);
          data = (await apiCalculate(page, payload)).body.data;
        });

        const mappings = [
          ['ANGSURAN', 'angsuran'],
          ['Biaya Adm Angs', 'biaya_adm_angs'],
          ['Total Angsuran', 'total_angsuran'],
          ['PROVISI', 'provisi'],
          ['ADMINISTRASI', 'administrasi'],
          ['ASURANSI', 'asuransi'],
          ['TATA LAKSANA', 'tata_laksana'],
          ['TOTAL BIAYA', 'total_biaya'],
          ['SISA GAJI AKHIR', 'sisa_gaji_akhir'],
          ['TERIMA BERSIH', 'terima_bersih'],
        ];

        for (const [label, key] of mappings) {
          await test.step(`Cocokkan "${label}" di UI dengan engine`, async () => {
            const expected = Math.round(Number(data[key]));
            await expect
              .poll(async () => cleanRupiah(await readOutput(page, label)), { timeout: 20000 })
              .toBe(expected);
          });
        }
      });
    });
  });
});
