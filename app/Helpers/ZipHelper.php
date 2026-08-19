<?php
/**
 * Zip Packaging & Batch Download Helper
 */

class ZipHelper {
    public static function createZipFromAssets(array $assets, string $zipFileName = 'marketing_assets.zip'): string {
        if (!class_exists('ZipArchive')) {
            throw new Exception("PHP ZipArchive extension is required for batch downloads.");
        }

        $tmpDir = sys_get_temp_dir();
        $zipFilePath = $tmpDir . '/' . uniqid('batch_dl_', true) . '.zip';

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Unable to create zip file at $zipFilePath");
        }

        $usedNames = [];
        foreach ($assets as $asset) {
            $realPath = __DIR__ . '/../../' . $asset['file_path'];
            if (file_exists($realPath)) {
                $baseName = $asset['original_name'] ?: basename($asset['file_path']);
                // Avoid name collisions in zip
                $entryName = $baseName;
                $counter = 1;
                while (isset($usedNames[$entryName])) {
                    $ext = pathinfo($baseName, PATHINFO_EXTENSION);
                    $nameOnly = pathinfo($baseName, PATHINFO_FILENAME);
                    $entryName = $nameOnly . '_' . $counter . ($ext ? '.' . $ext : '');
                    $counter++;
                }
                $usedNames[$entryName] = true;
                $zip->addFile($realPath, $entryName);
            }
        }

        $zip->close();
        return $zipFilePath;
    }
}
