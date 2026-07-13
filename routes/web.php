<?php

use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductTemplateController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\DataSimulasiController;
use App\Http\Controllers\DnkaController;
use App\Http\Controllers\LoanOperationController;
use App\Http\Controllers\PerjanjianKreditController;
use App\Http\Controllers\SiController;
use App\Http\Controllers\SppkController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DatabaseMaintenanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KbSimulationController;
use App\Http\Controllers\MailMergeController;
use App\Http\Controllers\SimulationController;
use App\Http\Controllers\PicNbpController;
use App\Http\Controllers\BanpotController;
use App\Http\Controllers\NominatifController;
use Illuminate\Support\Facades\Log;

Route::get('/__ping', fn () => response('ok', 200));

Route::put('/product-templates/{product_template}', [ProductTemplateController::class, 'update'])->name('product_templates.update')->middleware('auth');

// Products
Route::get('/products', [ProductController::class, 'index'])->name('products.index')->middleware('auth');
Route::get('/products/create', [ProductController::class, 'create'])->name('products.create')->middleware('auth');
Route::post('/products', [ProductController::class, 'store'])->name('products.store')->middleware('auth');
Route::get('/products/engine-options', [ProductController::class, 'engineOptions'])->name('products.engine_options')->middleware('auth');
Route::get('/products/{product}/configuration', [ProductController::class, 'configuration'])->name('products.configuration')->middleware('auth');
// Product Templates
Route::get('/product-templates', [ProductTemplateController::class, 'index'])->name('product_templates.index')->middleware('auth');
Route::get('/product-templates/create', [ProductTemplateController::class, 'create'])->name('product_templates.create')->middleware('auth');
Route::post('/product-templates', [ProductTemplateController::class, 'store'])->name('product_templates.store')->middleware('auth');
Route::get('/product-templates/{productTemplate}/load-items', [ProductTemplateController::class, 'loadItems'])->name('product_templates.load_items')->middleware('auth');
Route::get('/product-templates/{product_template}/edit', [ProductTemplateController::class, 'edit'])->name('product_templates.edit')->middleware('auth');
Route::delete('/product-templates/{product_template}', [ProductTemplateController::class, 'destroy'])->name('product_templates.destroy')->middleware('auth');
// Accounts
Route::get('/accounts', [AccountController::class, 'index'])->name('accounts.index')->middleware('auth');
Route::get('/accounts/create', [AccountController::class, 'create'])->name('accounts.create')->middleware('auth');
Route::post('/accounts', [AccountController::class, 'store'])->name('accounts.store')->middleware('auth');
Route::get('/accounts/{id}/edit', [AccountController::class, 'edit'])->name('accounts.edit')->middleware('auth');
Route::put('/accounts/{id}', [AccountController::class, 'update'])->name('accounts.update')->middleware('auth');
Route::delete('/accounts/{id}', [AccountController::class, 'destroy'])->name('accounts.destroy')->middleware('auth');

// Simulation
Route::get('/simulation', [SimulationController::class, 'index'])->name('simulation.index')->middleware('auth');
Route::get('/data-simulasi/upload', function () {
    return redirect()
        ->route('data_simulasi.list')
        ->with('error', 'Fitur import PDF ke Data Simulasi sudah dinonaktifkan.');
})->name('data_simulasi.index')->middleware('auth');
Route::get('/data-simulasi', [DataSimulasiController::class, 'index'])->name('data_simulasi.list')->middleware('auth');
Route::get('/data-simulasi/trial', [DataSimulasiController::class, 'trialIndex'])->name('data_simulasi.trial.list')->middleware('auth');
Route::patch('/data-simulasi/{dataSimulasi}/confirm', [DataSimulasiController::class, 'confirm'])->name('data_simulasi.confirm')->middleware('auth');
Route::get('/data-simulasi/{dataSimulasi}/edit', [DataSimulasiController::class, 'edit'])->name('data_simulasi.edit')->middleware('auth');
Route::put('/data-simulasi/{dataSimulasi}', [DataSimulasiController::class, 'update'])->name('data_simulasi.update')->middleware('auth');
Route::patch('/data-simulasi/{dataSimulasi}/back-to-trial', [DataSimulasiController::class, 'backToTrial'])->name('data_simulasi.back_to_trial')->middleware('auth');
Route::get('/data-simulasi/{dataSimulasi}/pelengkap', [DataSimulasiController::class, 'editPelengkap'])->name('data_simulasi.pelengkap.edit')->middleware('auth');
Route::put('/data-simulasi/{dataSimulasi}/pelengkap', [DataSimulasiController::class, 'updatePelengkap'])->name('data_simulasi.pelengkap.update')->middleware('auth');
Route::post('/data-simulasi/{dataSimulasi}/pelengkap/ocr-pdf', [DataSimulasiController::class, 'extractPelengkapFromPdf'])->name('data_simulasi.pelengkap.ocr_pdf')->middleware('auth');
Route::get('/data-simulasi/{dataSimulasi}/upload-idpb', [DataSimulasiController::class, 'showUploadIdpb'])->name('data_simulasi.idpb.upload_form')->middleware('auth');
Route::post('/data-simulasi/{dataSimulasi}/upload-idpb', [DataSimulasiController::class, 'uploadIdpb'])->name('data_simulasi.idpb.upload')->middleware('auth');
Route::get('/data-simulasi/{dataSimulasi}/upload-permohonan-cif', [DataSimulasiController::class, 'showUploadPermohonanCif'])->name('data_simulasi.permohonan_cif.upload_form')->middleware('auth');
Route::post('/data-simulasi/{dataSimulasi}/upload-permohonan-cif', [DataSimulasiController::class, 'uploadPermohonanCif'])->name('data_simulasi.permohonan_cif.upload')->middleware('auth');
Route::get('/data-simulasi/{dataSimulasi}/upload-pelunasan-to-kb', [DataSimulasiController::class, 'showUploadPelunasanToKb'])->name('data_simulasi.pelunasan_to_kb.upload_form')->middleware('auth');
Route::post('/data-simulasi/{dataSimulasi}/upload-pelunasan-to-kb', [DataSimulasiController::class, 'uploadPelunasanToKb'])->name('data_simulasi.pelunasan_to_kb.upload')->middleware('auth');
Route::delete('/data-simulasi/{dataSimulasi}', [DataSimulasiController::class, 'destroy'])->name('data_simulasi.destroy')->middleware('auth');

// Perjanjian Kredit
Route::get('/perjanjian-kredit/{dataSimulasi}/generate', [PerjanjianKreditController::class, 'generate'])->name('perjanjian_kredit.generate')->middleware('auth');
Route::get('/perjanjian-kredit/{dataSimulasi}/download/{filename}', [PerjanjianKreditController::class, 'download'])->name('perjanjian_kredit.download')->middleware('auth');

// SI
Route::get('/si/{dataSimulasi}/generate/to', [SiController::class, 'generateTakeOver'])->name('si.generate_to')->middleware('auth');
Route::get('/si/{dataSimulasi}/generate/new-topup', [SiController::class, 'generateNewTopup'])->name('si.generate_new_topup')->middleware('auth');
Route::get('/si/{dataSimulasi}/download/{filename}', [SiController::class, 'download'])->name('si.download')->middleware('auth');

// SPPK
Route::get('/sppk/{dataSimulasi}/preview', [SppkController::class, 'preview'])->name('sppk.preview')->middleware('auth');
Route::get('/sppk/{dataSimulasi}/generate', [SppkController::class, 'generate'])->name('sppk.generate')->middleware('auth');
Route::get('/simulation/accounts', [SimulationController::class, 'getAccountsByProduct'])->middleware('auth');
Route::post('/simulation/import-ocr-rows', [SimulationController::class, 'importOcrRows'])->name('simulation.import_ocr_rows')->middleware('auth');
Route::post('/simulation/import-pdf-text', [SimulationController::class, 'importPdfText'])->name('simulation.import_pdf_text')->middleware('auth');
Route::post('/simulation/preview-pdf-text', [SimulationController::class, 'previewPdfText'])->name('simulation.preview_pdf_text')->middleware('auth');
Route::post('/data-simulasi', function () {
    return response()->json([
        'message' => 'Fitur import PDF ke Data Simulasi sudah dinonaktifkan.',
    ], 410);
})->name('data_simulasi.store')->middleware('auth');
Route::get('/simulasi-kb', [KbSimulationController::class, 'index'])->name('kb_simulasi.index')->middleware('auth');
Route::get('/simulasi-kb/goal-seeker', [KbSimulationController::class, 'goalSeekerIndex'])->name('kb_simulasi.goal_seeker')->middleware('auth');
Route::post('/simulasi-kb/calculate', [KbSimulationController::class, 'calculate'])->name('kb_simulasi.calculate')->middleware('auth');
Route::post('/simulasi-kb/goal-seek', [KbSimulationController::class, 'goalSeek'])->name('kb_simulasi.goal_seek')->middleware('auth');
Route::post('/simulasi-kb/store', [KbSimulationController::class, 'store'])->name('kb_simulasi.store')->middleware('auth');
Route::post('/simulasi-kb/download-pdf', [KbSimulationController::class, 'downloadPdf'])->name('kb_simulasi.download_pdf')->middleware('auth');
Route::post('/simulation/batches/{batchId}/map-proposal', [SimulationController::class, 'mapBatchToProposal'])->name('simulation.map_batch_to_proposal')->middleware('auth');
Route::get('/dnka/horizontal/download-template', [DnkaController::class, 'downloadHorizontalTemplate'])->name('dnka.horizontal.download_template')->middleware('auth');
Route::get('/dnka/vertical/download-template', [DnkaController::class, 'downloadVerticalTemplate'])->name('dnka.vertical.download_template')->middleware('auth');
Route::get('/datanominatif/download-template', [DnkaController::class, 'downloadDatanominatifTemplate'])->name('datanominatif.download_template')->middleware('auth');
Route::get('/data-los-bulk/download-template', [DnkaController::class, 'downloadDataLosBulkTemplate'])->name('data_los_bulk.download_template')->middleware('auth');
Route::get('/data-rekening/download-template', [DnkaController::class, 'downloadDataRekeningTemplate'])->name('data_rekening.download_template')->middleware('auth');
Route::get('/repayment-schedule/download-template', [DnkaController::class, 'downloadRepaymentScheduleTemplate'])->name('repayment_schedule.download_template')->middleware('auth');
Route::get('/permohonan-cif/download-template', [DnkaController::class, 'downloadPermohonanCifTemplate'])->name('permohonan_cif.download_template')->middleware('auth');
Route::get('/pelunasan-to-kb/download-template', [DnkaController::class, 'downloadPelunasanToKbTemplate'])->name('pelunasan_to_kb.download_template')->middleware('auth');
Route::get('/excel-bundle/preview', [DnkaController::class, 'previewExcelBundle'])->name('excel_bundle.preview')->middleware('auth');
Route::get('/excel-bundle/download', function () {
    $context = [
        'query' => request()->query(),
        'path' => request()->path(),
        'user_id' => auth()->id(),
    ];

    try {
        Log::channel('single')->info('excel_bundle.download route hit', $context);
    } catch (\Throwable) {
        // Ignore logger channel failures.
    }

    @file_put_contents(
        storage_path('logs/excel_bundle_debug.log'),
        '[' . now()->format('Y-m-d H:i:s') . '] excel_bundle.download route hit ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL,
        FILE_APPEND
    );

    return app(DnkaController::class)->downloadExcelBundle();
})->name('excel_bundle.download')->middleware('auth');

// Channeling loan operations
Route::post('/loans/propose', [LoanOperationController::class, 'propose'])->name('loans.propose')->middleware('auth');
Route::post('/loans/{loan}/disburse', [LoanOperationController::class, 'disburse'])->name('loans.disburse')->middleware('auth');
Route::post('/loans/{loan}/installment', [LoanOperationController::class, 'receiveInstallment'])->name('loans.installment')->middleware('auth');
Route::post('/loans/{loan}/settle-lender', [LoanOperationController::class, 'settleToLender'])->name('loans.settle_lender')->middleware('auth');
Route::post('/journals/void', [LoanOperationController::class, 'voidJournal'])->name('journals.void')->middleware('auth');

Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show')->middleware('auth');
Route::get('/products/{product}/edit', [ProductController::class, 'edit'])->name('products.edit')->middleware('auth');


Route::get('/', function () {
    return view('welcome');
})->middleware('auth');



// Auth routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard route
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard')->middleware('auth');

// Database maintenance (for shared hosting without CLI access)
Route::get('/maintenance/db', [DatabaseMaintenanceController::class, 'index'])->name('maintenance.db.index')->middleware('auth');
Route::post('/maintenance/db/query', [DatabaseMaintenanceController::class, 'executeQuery'])->name('maintenance.db.query')->middleware('auth');
Route::post('/maintenance/db/optimize-clear', [DatabaseMaintenanceController::class, 'optimizeClear'])->name('maintenance.db.optimize_clear')->middleware('auth');
Route::post('/maintenance/db/config-clear', [DatabaseMaintenanceController::class, 'configClear'])->name('maintenance.db.config_clear')->middleware('auth');
Route::post('/maintenance/db/migrate', [DatabaseMaintenanceController::class, 'migrate'])->name('maintenance.db.migrate')->middleware('auth');
Route::post('/maintenance/db/seed', [DatabaseMaintenanceController::class, 'seed'])->name('maintenance.db.seed')->middleware('auth');
Route::post('/maintenance/db/migrate-seed', [DatabaseMaintenanceController::class, 'migrateAndSeed'])->name('maintenance.db.migrate_seed')->middleware('auth');

// Mail merge templates
Route::get('/mail-merge', [MailMergeController::class, 'index'])->name('mail_merge.index')->middleware('auth');
Route::post('/mail-merge/templates', [MailMergeController::class, 'store'])->name('mail_merge.store')->middleware('auth');
Route::post('/mail-merge/templates/existing', [MailMergeController::class, 'storeExisting'])->name('mail_merge.store_existing')->middleware('auth');
Route::get('/mail-merge/templates/fields', [MailMergeController::class, 'fields'])->name('mail_merge.fields')->middleware('auth');
Route::get('/mail-merge/templates/{mailMergeTemplate}/edit', [MailMergeController::class, 'edit'])->name('mail_merge.edit')->middleware('auth');
Route::put('/mail-merge/templates/{mailMergeTemplate}', [MailMergeController::class, 'update'])->name('mail_merge.update')->middleware('auth');
Route::get('/mail-merge/templates/{mailMergeTemplate}/preview/{dataSimulasi}', [MailMergeController::class, 'preview'])->name('mail_merge.preview')->middleware('auth');
Route::get('/mail-merge/templates/{mailMergeTemplate}/download/{dataSimulasi}', [MailMergeController::class, 'download'])->name('mail_merge.download')->middleware('auth');

// Petugas NBP (PIC)
Route::resource('/pic-nbp', PicNbpController::class)->middleware('auth')->names('pic_nbp');

// Banpot import
Route::get('/banpot', [BanpotController::class, 'list'])->name('banpot.index')->middleware('auth');
Route::get('/banpot/import', [BanpotController::class, 'create'])->name('banpot.create')->middleware('auth');
Route::post('/banpot/preview', [BanpotController::class, 'preview'])->name('banpot.preview')->middleware('auth');
Route::post('/banpot', [BanpotController::class, 'store'])->name('banpot.store')->middleware('auth');

// Initial Nominatif import
Route::get('/nominatif/import-initial', [NominatifController::class, 'create'])->name('nominatif.initial.create')->middleware('auth');
Route::post('/nominatif/import-initial/preview', [NominatifController::class, 'previewInitial'])->name('nominatif.initial.preview')->middleware('auth');
Route::post('/nominatif/import-initial', [NominatifController::class, 'storeInitial'])->name('nominatif.initial.store')->middleware('auth');
