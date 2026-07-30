<?php

const MAX_UPLOAD_BYTES = 5 * 1024 * 1024; // 5 MB

const ALLOWED_IMAGE_MIME_TYPES = [
    'image/jpeg' => 'jpg',
    'image/png' => 'png',
    'image/webp' => 'webp',
];

/**
 * Validates and stores an uploaded image given a single-file entry shaped
 * like one element of $_FILES (keys: name, type, tmp_name, error, size).
 * Returns the relative path (under assets/images/uploads/) to store in the
 * DB, or null if no file was submitted -- callers treat null as "leave the
 * existing image alone" on edit forms. Throws InvalidArgumentException on a
 * submitted-but-invalid file so the caller can show a form error instead of
 * silently accepting a bad upload.
 */
function process_uploaded_file(?array $file): ?string
{
    if ($file === null || !isset($file['error']) || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        throw new InvalidArgumentException('Upload failed (error code ' . $file['error'] . ').');
    }

    if (!is_uploaded_file($file['tmp_name'])) {
        throw new InvalidArgumentException('Invalid upload.');
    }

    if ($file['size'] > MAX_UPLOAD_BYTES) {
        throw new InvalidArgumentException('Image is too large (max 5 MB).');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);

    if (!isset(ALLOWED_IMAGE_MIME_TYPES[$mimeType])) {
        throw new InvalidArgumentException('Unsupported image type. Use JPEG, PNG, or WebP.');
    }

    if (getimagesize($file['tmp_name']) === false) {
        throw new InvalidArgumentException('File is not a valid image.');
    }

    $extension = ALLOWED_IMAGE_MIME_TYPES[$mimeType];
    $filename = bin2hex(random_bytes(16)) . '.' . $extension;
    $destination = __DIR__ . '/../../assets/images/uploads/' . $filename;

    if (!move_uploaded_file($file['tmp_name'], $destination)) {
        throw new InvalidArgumentException('Could not save the uploaded image.');
    }

    return 'images/uploads/' . $filename;
}

/** Convenience wrapper for a plain, non-repeated <input type="file" name="$fieldName"> field. */
function handle_image_upload(string $fieldName): ?string
{
    return process_uploaded_file($_FILES[$fieldName] ?? null);
}

/**
 * PHP's $_FILES nests repeated file inputs (name="stills[0][image]") as
 * $_FILES['stills']['name'][0]['image'], ['type'][0]['image'], etc. --
 * metadata key before row index, not the natural [index]['image'] shape.
 * This reshapes it into $result[index]['image'] = ['name'=>..,'tmp_name'=>..,...]
 * so each row's file can be handed to process_uploaded_file() directly.
 */
function reshape_files(string $topLevelKey): array
{
    if (!isset($_FILES[$topLevelKey])) {
        return [];
    }

    $result = [];
    foreach ($_FILES[$topLevelKey] as $metaKey => $byIndex) {
        foreach ($byIndex as $index => $bySubfield) {
            foreach ($bySubfield as $subfield => $value) {
                $result[$index][$subfield][$metaKey] = $value;
            }
        }
    }

    return $result;
}

/**
 * Deletes a previously-uploaded image given its stored relative path.
 * No-ops for null/empty paths and for anything that doesn't resolve inside
 * assets/images/uploads/ (defense in depth -- these paths only ever
 * originate from handle_image_upload()'s own randomized filenames).
 */
function delete_uploaded_image(?string $path): void
{
    if ($path === null || $path === '') {
        return;
    }

    $uploadsDir = realpath(__DIR__ . '/../../assets/images/uploads');
    $fullPath = realpath(__DIR__ . '/../../assets/' . ltrim($path, '/'));

    if ($uploadsDir === false || $fullPath === false) {
        return;
    }

    if (str_starts_with($fullPath, $uploadsDir) && is_file($fullPath)) {
        unlink($fullPath);
    }
}
