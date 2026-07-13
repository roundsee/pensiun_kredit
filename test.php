<?php
require "D:/system_kredit_pensiun/system_kredit_pensiun/vendor/autoload.php";
$app = require_once "D:/system_kredit_pensiun/system_kredit_pensiun/bootstrap/app.php";
$kernel = $app->make("Illuminate\Contracts\Console\Kernel");
$kernel->bootstrap();

use App\Http\Controllers\DnkaController;
use App\Models\DataSimulasi;
use Illuminate\Http\Request;

try {
    $ds = DataSimulasi::latest()->first();
    if (!$ds) {
        throw new Exception("No DataSimulasi records found in database.");
    }

    // Bind request simulation id to avoid empty parameter error if needed
    $request = request();
    $request->merge(["data_simulasi_id" => $ds->id]);

    $controller = new DnkaController();
    $response = $controller->downloadExcelBundle();

    // Check if the response is download Excel
    if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse) {
        $file = $response->getFile();
        if ($file->isFile()) {
            // Copy file to storage/app/temp as we want it saved there, copy from temp or check
            $dest = storage_path("app/temp/" . $file->getFilename());
            if (!is_dir(dirname($dest))) {
                mkdir(dirname($dest), 0755, true);
            }
            if (copy($file->getPathname(), $dest)) {
                echo "BUNDLE_SAVE_OK\n";
            } else {
                throw new Exception("Failed to copy file to temp folder.");
            }
        } else {
            throw new Exception("File not found or not a valid file.");
        }
    } else {
        // It could be a RedirectResponse because of validation or redirect
        if ($response instanceof \Illuminate\Http\RedirectResponse) {
            $error = session("error") ?? "Redirect response returned. Possibly check validation errors.";
            throw new Exception($error);
        }
        throw new Exception("Unexpected response type: " . get_class($response));
    }
} catch (\Throwable $e) {
    echo "BUNDLE_SAVE_FAILED: " . $e->getMessage() . "\n";
}

