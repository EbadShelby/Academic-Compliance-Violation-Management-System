<?php

/**
 * Upload Helper
 *
 * Centralised, reusable file-upload utilities for the ACVMS.
 * All controllers that handle file uploads should use these functions
 * instead of duplicating logic inline.
 *
 * ── Security model ────────────────────────────────────────────────────────
 *  1. MIME type validated from file CONTENT via mime_content_type()
 *     (never from the browser-supplied Content-Type header).
 *  2. File extension validated against an explicit whitelist.
 *  3. Double-extension attack detection (e.g. shell.php.jpg → rejected).
 *  4. Unique filename generated with uniqid() — original name never stored
 *     on disk.
 *  5. basename() used on every path to prevent directory traversal.
 *  6. Upload directory protected by .htaccess (deny all direct access).
 * ─────────────────────────────────────────────────────────────────────────
 */

// ── Phase 9 constants ─────────────────────────────────────────────────────────

/** Maximum allowed file size: 5 MB */
define('UPLOAD_MAX_SIZE', 5 * 1024 * 1024);

/** Storage directory (relative to BASE_PATH) for evidence files */
define('UPLOAD_EVIDENCE_DIR', 'storage/uploads/evidence');

/**
 * Allowed MIME types.
 * Only real images and PDFs — no executables, no scripts.
 */
define('UPLOAD_ALLOWED_MIMES', [
    'image/jpeg',
    'image/png',
    'application/pdf',
]);

/**
 * Allowed file extensions (explicit whitelist, lower-case).
 * Matched against the LAST extension segment only.
 */
define('UPLOAD_ALLOWED_EXTS', [
    'jpg',
    'jpeg',
    'png',
    'pdf',
]);

/**
 * Dangerous extensions that must never appear ANYWHERE in the filename.
 * Catches double-extension attacks like image.php.jpg.
 */
define('UPLOAD_BLOCKED_EXTS', [
    'php', 'php3', 'php4', 'php5', 'php7', 'phtml', 'phar',
    'exe', 'sh', 'bat', 'cmd', 'com', 'pl', 'py', 'rb',
    'js', 'html', 'htm', 'htaccess', 'asp', 'aspx', 'cgi',
]);

// ── Public API ────────────────────────────────────────────────────────────────

/**
 * Validate a single normalised file array from $_FILES.
 *
 * The file array must contain:
 *   name, tmp_name, size, error  (matching PHP's $_FILES structure)
 *
 * Returns an array of human-readable error strings.
 * An empty array means the file is valid.
 *
 * @param  array  $file  Normalised single-file array.
 * @return string[]      Error messages (empty = valid).
 */
function upload_validate_file(array $file): array
{
    $errors   = [];
    $origName = $file['name'] ?? '';

    // 1. PHP upload error code
    if (($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        $errors[] = upload_error_message((int) ($file['error'] ?? UPLOAD_ERR_NO_FILE), $origName);
        return $errors; // no point checking further
    }

    // 2. File size
    if ((int) ($file['size'] ?? 0) > UPLOAD_MAX_SIZE) {
        $mb = number_format(UPLOAD_MAX_SIZE / 1048576, 0);
        $errors[] = '"' . htmlspecialchars($origName) . '" exceeds the ' . $mb . ' MB size limit.';
    }

    // 3. Double-extension / blocked-extension check on original filename
    $allParts = explode('.', strtolower(basename($origName)));
    array_shift($allParts); // drop the base name, keep only extension parts
    foreach ($allParts as $part) {
        if (in_array($part, UPLOAD_BLOCKED_EXTS, true)) {
            $errors[] = '"' . htmlspecialchars($origName) . '" contains a blocked extension (' . htmlspecialchars($part) . ').';
            return $errors; // hard stop — don't process further
        }
    }

    // 4. Real MIME type from file content
    $tmpPath  = $file['tmp_name'] ?? '';
    if (!is_uploaded_file($tmpPath)) {
        $errors[] = '"' . htmlspecialchars($origName) . '" is not a valid uploaded file.';
        return $errors;
    }

    $realMime = mime_content_type($tmpPath);
    if ($realMime === false || !in_array($realMime, UPLOAD_ALLOWED_MIMES, true)) {
        $detected  = $realMime !== false ? htmlspecialchars($realMime) : 'unknown';
        $errors[] = '"' . htmlspecialchars($origName) . '" has a disallowed file type (' . $detected . '). '
                  . 'Only JPG, PNG, and PDF are accepted.';
    }

    // 5. Extension whitelist check (last extension only)
    $lastExt = strtolower(pathinfo($origName, PATHINFO_EXTENSION));
    if (!in_array($lastExt, UPLOAD_ALLOWED_EXTS, true)) {
        $errors[] = '"' . htmlspecialchars($origName) . '" has a disallowed extension (.' . htmlspecialchars($lastExt) . ').';
    }

    return $errors;
}

/**
 * Generate a unique, safe filename for storage.
 *
 * Format:  evidence_{uniqid}.{ext}
 * Example: evidence_6841a91c2f4ab.pdf
 *
 * The original filename is NEVER used on disk.
 *
 * @param  string $originalName  Original client filename (for extension extraction only).
 * @return string                Safe unique filename.
 */
function upload_generate_filename(string $originalName): string
{
    $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

    // Fallback: if extension is somehow empty or blocked, use 'bin'
    if ($ext === '' || in_array($ext, UPLOAD_BLOCKED_EXTS, true)) {
        $ext = 'bin';
    }

    return 'evidence_' . uniqid('', true) . '.' . $ext;
}

/**
 * Return the absolute path to the evidence upload directory.
 * Creates the directory (with restrictive permissions) if it does not exist.
 *
 * @return string  Absolute path, with trailing slash.
 */
function upload_evidence_dir(): string
{
    $dir = BASE_PATH . '/' . UPLOAD_EVIDENCE_DIR . '/';

    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);

        // Write .htaccess guard if Apache is available
        $htaccess = $dir . '.htaccess';
        if (!file_exists($htaccess)) {
            file_put_contents($htaccess, "Order Allow,Deny\nDeny from all\n");
        }
    }

    return $dir;
}

/**
 * Move a validated uploaded file to the evidence directory.
 *
 * Returns the unique filename on success, or false on failure.
 *
 * @param  array   $file          Normalised single-file array (from $_FILES).
 * @param  string &$uniqueName    Output parameter — the generated filename.
 * @return string|false           Absolute path to the stored file, or false.
 */
function upload_move_file(array $file, string &$uniqueName): string|false
{
    $dir        = upload_evidence_dir();
    $uniqueName = upload_generate_filename($file['name']);
    $dest       = $dir . $uniqueName;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        error_log('ACVMS upload_move_file: move_uploaded_file() failed → ' . $dest);
        return false;
    }

    return $dest;
}

/**
 * Return the path stored in the database (relative to BASE_PATH).
 *
 * @param  string $uniqueName  The generated filename (from upload_generate_filename).
 * @return string
 */
function upload_relative_path(string $uniqueName): string
{
    return UPLOAD_EVIDENCE_DIR . '/' . $uniqueName;
}

/**
 * Normalise PHP's multi-file $_FILES array into a flat list of single-file
 * arrays, skipping any entries where the user chose no file.
 *
 * Usage:  $files = upload_normalise_files($_FILES['evidence']);
 *
 * @param  array $filesEntry  One top-level entry from $_FILES (e.g. $_FILES['evidence']).
 * @return array              Array of single-file arrays.
 */
function upload_normalise_files(array $filesEntry): array
{
    // Single file input (not multiple[])
    if (!is_array($filesEntry['name'])) {
        if (($filesEntry['error'] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            return [];
        }
        return [$filesEntry];
    }

    // Multiple file input (name="evidence[]")
    $normalised = [];
    $count      = count($filesEntry['name']);

    for ($i = 0; $i < $count; $i++) {
        if (($filesEntry['error'][$i] ?? UPLOAD_ERR_NO_FILE) === UPLOAD_ERR_NO_FILE) {
            continue;
        }
        $normalised[] = [
            'name'     => $filesEntry['name'][$i],
            'tmp_name' => $filesEntry['tmp_name'][$i],
            'type'     => $filesEntry['type'][$i],
            'size'     => $filesEntry['size'][$i],
            'error'    => $filesEntry['error'][$i],
        ];
    }

    return $normalised;
}

// ── Private: upload error code → human message ────────────────────────────────

/**
 * Convert a PHP UPLOAD_ERR_* constant to a user-friendly message.
 *
 * @param  int    $code      PHP upload error code.
 * @param  string $filename  Original filename for context.
 * @return string
 */
function upload_error_message(int $code, string $filename = ''): string
{
    $name = $filename !== '' ? '"' . htmlspecialchars($filename) . '"' : 'The file';

    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE =>
            $name . ' exceeds the maximum allowed upload size.',
        UPLOAD_ERR_PARTIAL =>
            $name . ' was only partially uploaded. Please try again.',
        UPLOAD_ERR_NO_FILE =>
            'No file was selected.',
        UPLOAD_ERR_NO_TMP_DIR =>
            'Server upload directory is missing. Contact the administrator.',
        UPLOAD_ERR_CANT_WRITE =>
            'Failed to write ' . $name . ' to disk. Contact the administrator.',
        UPLOAD_ERR_EXTENSION =>
            'Upload blocked by a server extension. Contact the administrator.',
        default =>
            $name . ' could not be uploaded (unknown error code ' . $code . ').',
    };
}
