<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class TestController extends Controller
{
    public function ping()
    {
        return response()->json([
            'message' => 'API accessible',
            'timestamp' => now(),
        ]);
    }

    public function csrfTest(Request $request)
    {
        Log::info('=== CSRF DEBUG ===');
        Log::info('Session ID: ' . $request->session()->getId());
        Log::info('CSRF Token: ' . csrf_token());
        Log::info('Session Token: ' . $request->session()->token());
        Log::info('Request Headers: ', $request->headers->all());
        Log::info('Cookies: ', $request->cookies->all());
        Log::info('==================');

        return response()->json([
            'message' => 'Test CSRF',
            'session_id' => $request->session()->getId(),
            'csrf_token' => csrf_token(),
            'session_token' => $request->session()->token(),
            'cookies' => $request->cookies->all(),
            'headers' => [
                'x-csrf-token' => $request->header('X-CSRF-TOKEN'),
                'x-xsrf-token' => $request->header('X-XSRF-TOKEN'),
            ]
        ]);
    }
}
