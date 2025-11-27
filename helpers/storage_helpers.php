<?php
// helpers/storage_helpers.php

function ensure_upload_dir() {
    $base = __DIR__ . '/../uploads/documents';
    if (!is_dir($base)) mkdir($base, 0755, true);
    return $base;
}

function safe_filename($name) {
    $name = preg_replace('/[^\w\-. ]+/', '_', mb_substr($name,0,200));
    return $name;
}

function store_document_file($tmpPath, $originalName) {
    $base = ensure_upload_dir();
    $ext = pathinfo($originalName, PATHINFO_EXTENSION);
    $safe = safe_filename($originalName);
    $stored = hash('sha256', $safe . microtime(true) . random_bytes(6));
    $filename = $stored . ($ext ? '.' . $ext : '');
    $dest = $base . '/' . $filename;
    if (!move_uploaded_file($tmpPath, $dest)) {
        // fallback if not an uploaded file
        if (!rename($tmpPath, $dest)) return false;
    }
    chmod($dest, 0644);
    return $filename;
}

function get_document_path($storedFilename) {
    $base = __DIR__ . '/../uploads/documents';
    $path = realpath($base . '/' . $storedFilename);
    // prevent directory traversal
    if (!$path || !str_starts_with($path, realpath($base))) return false;
    return $path;
}