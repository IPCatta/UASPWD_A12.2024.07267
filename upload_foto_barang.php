<?php

declare(strict_types=1);

function barang_upload_foto(array $file, string $targetDirAbs, string $targetDirRel, int $maxBytes = 3145728): array
{
    if (!isset($file['error']) || !is_int($file['error'])) {
        return ['ok' => false, 'error' => 'Upload tidak valid.'];
    }

    if ($file['error'] === UPLOAD_ERR_NO_FILE) {
        return ['ok' => true, 'path' => null];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'error' => 'Upload gagal (kode: ' . $file['error'] . ').'];
    }

    if (!isset($file['size']) || (int)$file['size'] <= 0) {
        return ['ok' => false, 'error' => 'File upload kosong.'];
    }

    if ((int)$file['size'] > $maxBytes) {
        return ['ok' => false, 'error' => 'Ukuran foto terlalu besar. Maksimal ' . (int) floor($maxBytes / 1024 / 1024) . 'MB.'];
    }

    $tmp = $file['tmp_name'] ?? '';
    if (!is_string($tmp) || $tmp === '' || !is_uploaded_file($tmp)) {
        return ['ok' => false, 'error' => 'Temporary upload tidak valid.'];
    }

    $allowedMime = [
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
    ];

    $mime = null;
    if (function_exists('finfo_open')) {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo) {
            $mime = @finfo_file($finfo, $tmp);
            @finfo_close($finfo);
        }
    }

    if (!$mime && function_exists('getimagesize')) {
        $info = @getimagesize($tmp);
        if (is_array($info) && isset($info['mime'])) {
            $mime = $info['mime'];
        }
    }

    if (!is_string($mime) || !isset($allowedMime[$mime])) {
        return ['ok' => false, 'error' => 'Format foto tidak didukung. Gunakan JPG/PNG/WEBP.'];
    }

    if (!is_dir($targetDirAbs)) {
        @mkdir($targetDirAbs, 0755, true);
    }

    if (!is_dir($targetDirAbs) || !is_writable($targetDirAbs)) {
        return ['ok' => false, 'error' => 'Folder upload tidak bisa ditulis: ' . $targetDirRel];
    }

    $ext = $allowedMime[$mime];
    $rand = bin2hex(random_bytes(10));
    $filename = 'barang_' . date('Ymd_His') . '_' . $rand . '.' . $ext;
    $destAbs = rtrim($targetDirAbs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . $filename;
    $destRel = rtrim($targetDirRel, '/') . '/' . $filename;

    if (!@move_uploaded_file($tmp, $destAbs)) {
        return ['ok' => false, 'error' => 'Gagal menyimpan foto ke server.'];
    }

    return ['ok' => true, 'path' => $destRel];
}

function barang_delete_foto(?string $relativePath, string $uploadsBaseAbs): void
{
    if (!$relativePath) {
        return;
    }

    $relativePath = str_replace('\\', '/', $relativePath);
    if (strpos($relativePath, '../') !== false) {
        return;
    }

    $abs = rtrim($uploadsBaseAbs, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $realBase = @realpath($uploadsBaseAbs);
    $realFile = @realpath($abs);

    if ($realBase && $realFile && strpos($realFile, $realBase) !== 0) {
        return;
    }

    if (is_file($abs)) {
        @unlink($abs);
    }
}

