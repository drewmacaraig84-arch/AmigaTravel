<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\ProofArchivalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ProofArchiveController extends Controller
{
    /**
     * Download a pre-retention backup ZIP archive.
     */
    public function download(Request $request, string $filename): BinaryFileResponse
    {
        $user = Auth::user();

        // Staff / Admin check
        $isStaff = $user instanceof User && ($user->hasAdminPermission('proofs') || $user->role === 'admin' || $user->is_admin);

        if (! $isStaff) {
            abort(403, 'Unauthorized to access proof archives.');
        }

        // Sanitize filename to prevent directory traversal
        $safeFilename = basename($filename);
        $filepath = storage_path('app/' . ProofArchivalService::ARCHIVE_DIR . '/' . $safeFilename);

        if (! file_exists($filepath) || ! is_file($filepath)) {
            abort(404, 'Archive file not found.');
        }

        return response()->download($filepath, $safeFilename);
    }
}
