const fs = require('fs');
const path = require('path');

const OUT_DIR = path.join(process.cwd(), 'business-report');
const ASSETS_DIR = path.join(OUT_DIR, 'assets');

const STATUS_LABEL = {
  passed: 'Lulus',
  failed: 'Gagal',
  timedOut: 'Timeout',
  skipped: 'Dilewati',
  interrupted: 'Terhenti',
};

function esc(text) {
  return String(text ?? '')
    .replace(/&/g, '&amp;')
    .replace(/</g, '&lt;')
    .replace(/>/g, '&gt;')
    .replace(/"/g, '&quot;');
}

// Ambil langkah bisnis (test.step) saja; langkah teknis fixture/hook diabaikan.
function flattenSteps(steps, out = []) {
  for (const step of steps || []) {
    if (step.category === 'test.step') out.push(step);
    if (Array.isArray(step.steps) && step.steps.length) flattenSteps(step.steps, out);
  }
  return out;
}

function testTitle(test) {
  const suites = (test.parent && test.parent.titlePath ? test.parent.titlePath() : [])
    .filter((t) => t && !String(t).includes('.spec.'));
  return [...suites, test.title].join(' › ');
}

function copyAttachment(attachment, index) {
  if (!attachment.path || !fs.existsSync(attachment.path)) return null;
  fs.mkdirSync(ASSETS_DIR, { recursive: true });
  const ext = path.extname(attachment.path) || '.png';
  const safeName = `test-${index}${ext}`;
  const target = path.join(ASSETS_DIR, safeName);
  try {
    fs.copyFileSync(attachment.path, target);
  } catch (e) {
    return null;
  }
  return { name: attachment.name, file: `assets/${safeName}` };
}

class BusinessReporter {
  constructor(options = {}) {
    this.outputFile = options.outputFile || 'index.html';
    this.results = [];
  }

  onTestEnd(test, result) {
    this.results.push({ test, result });
  }

  onEnd(result) {
    fs.mkdirSync(OUT_DIR, { recursive: true });

    let passed = 0;
    let failed = 0;

    const cards = this.results.map(({ test, result }, index) => {
      const status = result.status;
      if (status === 'passed') passed += 1;
      else if (status !== 'skipped') failed += 1;

      const steps = flattenSteps(result.steps);
      const stepItems = steps
        .map((step, i) => {
          const hasError = Boolean(step.error);
          const dur = ((step.duration || 0) / 1000).toFixed(1);
          return `<li class="${hasError ? 'step-failed' : 'step-ok'}">
            <span class="step-no">${i + 1}.</span>
            <span class="step-title">${esc(step.title)}</span>
            <span class="step-meta">${hasError ? 'GAGAL' : 'OK'} &middot; ${dur}s</span>
          </li>`;
        })
        .join('\n');

      const errorBlock = result.error
        ? `<pre class="error">${esc(result.error.message || result.error)}</pre>`
        : '';

      const shots = (result.attachments || [])
        .filter((a) => (a.contentType || '').startsWith('image/'))
        .map((a) => copyAttachment(a, index))
        .filter(Boolean)
        .map(
          (a) =>
            `<figure class="shot"><a href="${a.file}" target="_blank"><img src="${a.file}" loading="lazy" alt="${esc(a.name)}"></a><figcaption>${esc(a.name)}</figcaption></figure>`
        )
        .join('\n');

      const duration = ((result.duration || 0) / 1000).toFixed(1);

      return `<section class="card ${status === 'passed' ? 'is-passed' : 'is-failed'}">
        <header class="card-head">
          <h3>${esc(testTitle(test))}</h3>
          <span class="badge">${esc(STATUS_LABEL[status] || status)}</span>
        </header>
        <p class="meta">Browser: ${esc(result.project ? result.project.name : '-')} &middot; Durasi: ${duration} detik &middot; Langkah: ${steps.length}</p>
        <ol class="steps">${stepItems || '<li class="step-ok">Tidak ada langkah tercatat</li>'}</ol>
        ${errorBlock}
        ${shots ? `<div class="shots">${shots}</div>` : ''}
      </section>`;
    });

    const total = this.results.length;
    const html = `<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Laporan Pengujian Sistem Kredit Pensiun</title>
<style>
  :root { --green:#15803d; --red:#b91c1c; --line:#e5e7eb; --ink:#111827; --muted:#6b7280; }
  * { box-sizing: border-box; }
  body { font-family: "Segoe UI", Arial, sans-serif; margin: 0; background: #f3f4f6; color: var(--ink); }
  .wrap { max-width: 960px; margin: 0 auto; padding: 24px 16px 64px; }
  h1 { font-size: 22px; margin: 0 0 4px; }
  .subtitle { color: var(--muted); margin: 0 0 16px; font-size: 14px; }
  .summary { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
  .chip { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 10px 16px; font-size: 14px; }
  .chip b { display: block; font-size: 20px; }
  .chip.ok b { color: var(--green); }
  .chip.bad b { color: var(--red); }
  .card { background: #fff; border: 1px solid var(--line); border-left: 5px solid var(--green); border-radius: 8px; margin-bottom: 18px; overflow: hidden; }
  .card.is-failed { border-left-color: var(--red); }
  .card-head { display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 14px 16px; border-bottom: 1px solid var(--line); }
  .card-head h3 { font-size: 15px; margin: 0; line-height: 1.4; }
  .badge { flex: none; font-size: 12px; font-weight: 600; padding: 4px 10px; border-radius: 999px; background: #dcfce7; color: var(--green); }
  .is-failed .badge { background: #fee2e2; color: var(--red); }
  .meta { font-size: 12px; color: var(--muted); margin: 10px 16px 0; }
  .steps { margin: 12px 0 0; padding: 0 16px 16px 16px; list-style: none; counter-reset: step; }
  .steps li { display: flex; align-items: baseline; gap: 8px; padding: 7px 0; border-bottom: 1px dashed var(--line); font-size: 14px; }
  .steps li:last-child { border-bottom: none; }
  .step-no { color: var(--muted); min-width: 22px; text-align: right; }
  .step-title { flex: 1; }
  .step-meta { font-size: 11px; color: var(--muted); white-space: nowrap; }
  .step-failed .step-title { color: var(--red); font-weight: 600; }
  .step-failed .step-meta { color: var(--red); font-weight: 600; }
  .error { margin: 0 16px 16px; padding: 12px; background: #fef2f2; border: 1px solid #fecaca; border-radius: 6px; color: var(--red); font-size: 13px; white-space: pre-wrap; }
  .shots { display: flex; flex-wrap: wrap; gap: 12px; padding: 0 16px 16px; }
  .shot { margin: 0; width: 260px; }
  .shot img { width: 100%; border: 1px solid var(--line); border-radius: 6px; display: block; }
  .shot figcaption { font-size: 11px; color: var(--muted); margin-top: 4px; }
  footer { color: var(--muted); font-size: 12px; margin-top: 24px; }
</style>
</head>
<body>
<div class="wrap">
  <h1>Laporan Hasil Pengujian Sistem Kredit Pensiun</h1>
  <p class="subtitle">Langkah pengujian dibaca seperti perintah bisnis &middot; Dibuat ${new Date().toLocaleString('id-ID')}</p>
  <div class="summary">
    <div class="chip"><b>${total}</b>Total Skenario</div>
    <div class="chip ok"><b>${passed}</b>Lulus</div>
    <div class="chip bad"><b>${failed}</b>Gagal</div>
  </div>
  ${cards.join('\n')}
  <footer>Report lanjutan untuk developer (trace, network, teknis) tersedia di folder <code>playwright-report/</code> (<code>npx playwright show-report</code>).</footer>
</div>
</body>
</html>`;

    const outFile = path.join(OUT_DIR, this.outputFile);
    fs.writeFileSync(outFile, html, 'utf8');
    console.log(`[business-report] ${outFile} (${passed} lulus, ${failed} gagal dari ${total})`);
    return { status: result.status };
  }
}

module.exports = BusinessReporter;
