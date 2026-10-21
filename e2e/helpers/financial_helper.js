/**
 * Financial Math & Business Logic Helper Engine
 */

// Data Referensi Tabel Asuransi (KB vs non-KB)
const insuranceTable = [
  // KB (Memerlukan Tenor & Tier Usia Debitur)
  { product: 'Regular', bank_tujuan: 'KB', usia_max_tier: 49, tenor: 120, premium_per_million: 52.2600 },
  { product: 'Regular', bank_tujuan: 'KB', usia_max_tier: 59, tenor: 120, premium_per_million: 89.7800 },
  { product: 'Regular', bank_tujuan: 'KB', usia_max_tier: 69, tenor: 120, premium_per_million: 141.7100 },
  { product: 'Regular', bank_tujuan: 'KB', usia_max_tier: 74, tenor: 120, premium_per_million: 143.3200 },
  { product: 'Platinum', bank_tujuan: 'KB', usia_max_tier: 80, tenor: 120, premium_per_million: 296.6400 },

  // MANTAP / Non-KB (Hanya Berdasarkan Tenor)
  { product: 'Platinum', bank_tujuan: 'MANTAP', usia_max_tier: null, tenor: 120, premium_per_million: 220.0000 },
  { product: 'Platinum', bank_tujuan: 'MANTAP', usia_max_tier: null, tenor: 60, premium_per_million: 163.0000 },
  { product: 'Regular', bank_tujuan: 'MANTAP', usia_max_tier: null, tenor: 120, premium_per_million: 220.0000 }
];

// Lookup Tarif Asuransi
function getInsuranceRate(productName, bankTujuan, tenor, usiaDebitur) {
  if (bankTujuan === 'KB') {
    const matched = insuranceTable.find(row =>
      productName.toLowerCase().includes(row.product.toLowerCase()) &&
      row.bank_tujuan === 'KB' &&
      row.tenor === tenor &&
      usiaDebitur <= row.usia_max_tier
    );
    return matched ? matched.premium_per_million : 0;
  } else {
    const matched = insuranceTable.find(row =>
      productName.toLowerCase().includes(row.product.toLowerCase()) &&
      row.bank_tujuan === bankTujuan &&
      row.tenor === tenor
    );
    return matched ? matched.premium_per_million : 0;
  }
}

// Hitung Tenor Rekomendasi + Aturan Disbursement Buffer (<= 7 Hari)
function calculateExpectedTenorRekomendasi(tglLahirStr, tglPengajuanStr, usiaMaxProduk, tenorMaxProduk) {
  const tglLahir = new Date(tglLahirStr);
  const tglPengajuan = new Date(tglPengajuanStr);

  let nextBirthday = new Date(tglPengajuan.getFullYear(), tglLahir.getMonth(), tglLahir.getDate());
  if (nextBirthday < tglPengajuan) {
    nextBirthday.setFullYear(tglPengajuan.getFullYear() + 1);
  }

  const diffTime = nextBirthday.getTime() - tglPengajuan.getTime();
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

  let ageYears = tglPengajuan.getFullYear() - tglLahir.getFullYear();
  let ageMonths = tglPengajuan.getMonth() - tglLahir.getMonth();
  if (tglPengajuan.getDate() < tglLahir.getDate()) {
    ageMonths--;
  }
  if (ageMonths < 0) {
    ageYears--;
    ageMonths += 12;
  }

  const totalAgeInMonths = (ageYears * 12) + ageMonths;
  const maxAllowedAgeInMonths = usiaMaxProduk * 12;
  let sisaBulanMurni = maxAllowedAgeInMonths - totalAgeInMonths;

  // Disbursement Buffer: Jika selisih menuju ulang tahun <= 7 hari, potong 1 bulan
  if (diffDays >= 0 && diffDays <= 7) {
    sisaBulanMurni -= 1;
  }

  return Math.min(tenorMaxProduk, Math.max(0, sisaBulanMurni));
}

// Hitung Rekomendasi Dinamis Terintegrasi (Tenor, Max Angsuran, Plafond Rekomendasi)
function calculateIntegratedRecommendations(input, prodConfig) {
  const { tglLahir, tglPengajuan, gajiPensiun } = input;

  const tenorRekomendasi = calculateExpectedTenorRekomendasi(
    tglLahir,
    tglPengajuan,
    prodConfig.usia_max,
    prodConfig.tenor_max_produk
  );

  const maxAngsuranDBR = gajiPensiun * (prodConfig.dbr_percent || 0.90);
  const minSisaGaji = gajiPensiun < 1100000 ? 110000 : 0;
  const maxAngsuranSisaGaji = gajiPensiun - minSisaGaji;
  const maxAngsuranDiizinkan = Math.floor(Math.min(maxAngsuranDBR, maxAngsuranSisaGaji));

  const r = (prodConfig.rate_percent || 0.20) / 12;
  const netAngsuranPokok = maxAngsuranDiizinkan - (prodConfig.iuran_tetap || 0);

  let calculatedPlafondMax = 0;
  if (r > 0 && tenorRekomendasi > 0) {
    calculatedPlafondMax = netAngsuranPokok * (1 - Math.pow(1 + r, -tenorRekomendasi)) / r;
  }

  const plafondRekomendasi = Math.floor(Math.min(calculatedPlafondMax, prodConfig.plafond_max));

  return {
    tenorRekomendasi,
    maxAngsuranDiizinkan,
    plafondRekomendasi
  };
}

// Hitung Rincian Finansial Lengkap
function calculateSimulasiKredit(input, prodConfig, usiaDebitur) {
  const { plafond, tenor, sisa_gaji_awal, pelunasan = 0 } = input;

  // 1. PMT Anuitas
  const r = prodConfig.rate_percent / 12;
  const pmtBase = (plafond * r) / (1 - Math.pow(1 + r, -tenor));
  const pokokAngsuran = Math.round(pmtBase);
  const angsuran = pokokAngsuran + (prodConfig.iuran_tetap || 0);

  // 2. Adm Angsuran & Total Angsuran
  const admAngsuran = Math.round((angsuran * (prodConfig.admin_angsuran_percent || 0)) + (prodConfig.data_maintenance || 0));
  const totalAngsuran = angsuran + admAngsuran;

  // 3. Biaya Awal
  const provisi = Math.round(plafond * (prodConfig.provisi_percent || 0));
  const administrasi = Math.round(plafond * (prodConfig.admin_percent || 0));
  
  const premiumPerMillion = getInsuranceRate(prodConfig.product_name, prodConfig.bank_tujuan, tenor, usiaDebitur);
  const asuransi = Math.round((plafond / 1000000) * premiumPerMillion);

  const blokirAngsuran = totalAngsuran * 1;
  const tataLaksana = Math.round(
    (plafond * (prodConfig.tata_laksana_percent || 0)) +
    (prodConfig.taspen_or_asabri_fee || 0) +
    (prodConfig.materai || 80000) +
    (plafond * (prodConfig.tata_laksana_plus_percent || 0))
  );

  const simpananPokok = prodConfig.simpanan_pokok || 0;
  const totalBiaya = provisi + administrasi + asuransi + blokirAngsuran + tataLaksana + pelunasan + simpananPokok;

  const sisaGajiAkhir = sisa_gaji_awal - totalAngsuran;
  const terimaBersih = plafond - totalBiaya;

  return {
    angsuran,
    admAngsuran,
    totalAngsuran,
    provisi,
    administrasi,
    asuransi,
    blokirAngsuran,
    tataLaksana,
    totalBiaya,
    sisaGajiAkhir,
    terimaBersih
  };
}

// Utility Pembersih Format Rupiah ke Float/Integer
function cleanRupiah(str) {
  if (!str) return 0;
  return Number(str.replace(/[^0-9,-]/g, '').replace(',', '.'));
}

module.exports = {
  getInsuranceRate,
  calculateExpectedTenorRekomendasi,
  calculateIntegratedRecommendations,
  calculateSimulasiKredit,
  cleanRupiah
};