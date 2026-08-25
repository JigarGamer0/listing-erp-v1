<?php

$sourceDir = realpath(__DIR__ . '/../') . '/';
$destinationZip = realpath(__DIR__ . '/../') . '/listing_erp_deploy.zip';

if (file_exists($destinationZip)) {
    unlink($destinationZip);
}

$zip = new ZipArchive();
if ($zip->open($destinationZip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== TRUE) {
    die("Cannot open <$destinationZip>\n");
}

$excludePatterns = [
    '/^\.git/',
    '/^node_modules/',
    '/^scratch/',
    '/^storage\/framework\/cache\/data/',
    '/^storage\/framework\/sessions/',
    '/^storage\/framework\/views/',
    '/^storage\/logs/',
    '/^listing_erp_deploy\.zip/'
];

$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($sourceDir, RecursiveDirectoryIterator::SKIP_DOTS),
    RecursiveIteratorIterator::SELF_FIRST
);

$count = 0;
foreach ($files as $fileInfo) {
    $filePath = $fileInfo->getRealPath();
    $relativePath = substr($filePath, strlen($sourceDir));
    $relativePath = str_replace('\\', '/', $relativePath);

    // Check against exclude patterns
    $exclude = false;
    foreach ($excludePatterns as $pattern) {
        if (preg_match($pattern, $relativePath)) {
            $exclude = true;
            break;
        }
    }

    if ($exclude) {
        continue;
    }

    if ($fileInfo->isDir()) {
        $zip->addEmptyDir($relativePath);
    } else {
        $zip->addFile($filePath, $relativePath);
        $count++;
    }
}

$zip->close();
echo "Successfully created ZIP archive with $count files at: $destinationZip\n";
