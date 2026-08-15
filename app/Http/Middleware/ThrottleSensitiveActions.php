<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class ThrottleSensitiveActions
{
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'sensitive-actions:' . ($request->ip() ?? 'unknown') . ':' . $request->path() . ':' . $request->method();

        $attempts = Cache::get($key, 0);
        if ($attempts >= 50) {
            return response()->json([
                'message' => 'Too many sensitive actions from this device. Please wait a moment and try again.',
            ], 429);
        }

        if (!Cache::has($key)) {
            Cache::put($key, 1, now()->addMinutes(5));
        } else {
            Cache::increment($key);
        }

        if ($request->hasFile('proof') && ! $request->file('proof')->isValid()) {
            return response()->json([
                'message' => 'The uploaded proof file is invalid.',
            ], 422);
        }

        if ($request->hasFile('proof')) {
            $file = $request->file('proof');
            $maxSize = 10 * 1024 * 1024;
            if ($file->getSize() > $maxSize) {
                return response()->json([
                    'message' => 'The uploaded proof file exceeds the maximum allowed size of 10MB.',
                ], 422);
            }

            $allowedMimeTypes = ['image/jpeg', 'image/png', 'image/jpg', 'image/webp', 'application/pdf'];
            if (! in_array($file->getMimeType(), $allowedMimeTypes, true)) {
                return response()->json([
                    'message' => 'Only image or PDF proof files are allowed.',
                ], 422);
            }
        }

        return $next($request);
    }
}
