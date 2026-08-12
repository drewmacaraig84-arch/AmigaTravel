<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('storage_asset_path')) {
    function storage_asset_path(?string $path): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $path = (string) $path;

        if (preg_match('#^https?://#i', $path) === 1) {
            return $path;
        }

        $normalizedPath = ltrim($path, '/');

        if ($normalizedPath === '') {
            return null;
        }

        if (str_starts_with($normalizedPath, 'storage/')) {
            return asset($normalizedPath);
        }

        if (str_starts_with($normalizedPath, 'public/')) {
            return Storage::disk('public')->url($normalizedPath);
        }

        return Storage::disk('public')->url($normalizedPath);
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
            return 'Philippine Airline';
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
