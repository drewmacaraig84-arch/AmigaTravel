<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_asset_path')) {
    function storage_asset_path(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = (string) $path;

        // Already a full URL — return as-is (e.g. Cloudinary / S3 URLs)
        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        // Strip leading storage/ or public/ prefix to get the disk-relative path
        if (str_starts_with($normalizedPath, 'storage/')) {
            $normalizedPath = substr($normalizedPath, strlen('storage/'));
        } elseif (str_starts_with($normalizedPath, 'public/')) {
            $normalizedPath = substr($normalizedPath, strlen('public/'));
        }

        if ($normalizedPath === '') {
            return null;
        }

        // Serve via the server-side /storage-file/ route so files are read from
        // the persistent volume and not from Railway's ephemeral storage URL
        // (which returns 404 after any redeploy).
        return url('/storage-file/' . ltrim($normalizedPath, '/'));
    }
}


if (! function_exists('normalize_operator_name')) {
    function normalize_operator_name(?string $operator, ?string $mode = null): ?string
    {
        if ($operator === null || trim($operator) === '') {
            return null;
        }

        $clean = trim($operator);
        $lower = strtolower($clean);

        if (str_contains($lower, '2go')) {
            return '2GO';
        }

        if (str_contains($lower, 'starlite')) {
            return 'Starlite';
        }

        if (str_contains($lower, 'airasia')) {
            return 'AirAsia';
        }

        if (str_contains($lower, 'cebu')) {
            return 'Cebu Pacific';
        }

        if (str_contains($lower, 'philippine') || str_contains($lower, 'pal')) {
            return 'Philippine Airlines';
        }

        return $clean;
    }
}

if (! function_exists('operator_is_ferry')) {
    function operator_is_ferry(?string $operator): bool
    {
        return in_array(normalize_operator_name($operator), ['2GO', 'Starlite'], true);
    }
}
