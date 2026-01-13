<?php

function sportdata_sanitize_id_for_filename(string $value): string
{
    $value = trim($value);
    if ($value === '') {
        return '';
    }
    return preg_replace('/[^a-zA-Z0-9_-]/', '_', $value);
}

/**
 * Returns a relative URL (from /sportdataapp/PHP/*.php pages) to the user's icon, or null.
 *
 * @return array{url:string, absPath:string, mtime:int}|null
 */
function sportdata_find_user_icon(string $group_id, string $user_id): ?array
{
    $safeGroup = sportdata_sanitize_id_for_filename($group_id);
    $safeUser = sportdata_sanitize_id_for_filename($user_id);
    if ($safeGroup === '' || $safeUser === '') {
        return null;
    }

    $base = $safeGroup . '__' . $safeUser;
    $dir = __DIR__ . '/../uploads/user_icons/';

    $extensions = ['webp', 'png', 'jpg', 'jpeg', 'gif'];
    foreach ($extensions as $ext) {
        $abs = $dir . $base . '.' . $ext;
        if (is_file($abs)) {
            $mtime = @filemtime($abs);
            if ($mtime === false) {
                $mtime = 0;
            }
            // From /PHP/* pages, uploads is at ../uploads
            $url = '../uploads/user_icons/' . rawurlencode($base . '.' . $ext);
            if ($mtime > 0) {
                $url .= '?v=' . $mtime;
            }
            return ['url' => $url, 'absPath' => $abs, 'mtime' => (int)$mtime];
        }
    }

    return null;
}

/**
 * Delete existing icon files for a user (all supported extensions).
 */
function sportdata_delete_user_icons(string $group_id, string $user_id): void
{
    $safeGroup = sportdata_sanitize_id_for_filename($group_id);
    $safeUser = sportdata_sanitize_id_for_filename($user_id);
    if ($safeGroup === '' || $safeUser === '') {
        return;
    }

    $base = $safeGroup . '__' . $safeUser;
    $dir = __DIR__ . '/../uploads/user_icons/';
    $extensions = ['webp', 'png', 'jpg', 'jpeg', 'gif'];

    foreach ($extensions as $ext) {
        $abs = $dir . $base . '.' . $ext;
        if (is_file($abs)) {
            @unlink($abs);
        }
    }
}
