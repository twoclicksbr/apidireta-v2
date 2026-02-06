<?php

use Illuminate\Support\Facades\Route;

Route::domain(env('API_DOMAIN'))->group(function () {
    Route::get('/', function () {
        return response()->json([
            'message' => 'ApiDireta API',
            'site' => env('SITE_DOMAIN'),
            'docs' => env('DOCS_DOMAIN'),
            'endpoint' => request()->url(),
            'status' => 'online',
            'timestamp' => now()->format('d/m/Y H:i:s'),
        ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
    });

    Route::prefix('v2')->group(function () {
        Route::get('/', function () {
            return response()->json([
                'message' => 'ApiDireta API - Versão 2',
                'site' => env('SITE_DOMAIN'),
                'docs' => env('DOCS_DOMAIN'),
                'endpoint' => request()->url(),
                'status' => 'online',
                'version' => 'v2',
                'timestamp' => now()->format('d/m/Y H:i:s'),
            ], 200, [], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
        });

        Route::prefix('admin')->group(function () {
            Route::get('{module}', [\App\Http\Controllers\Controller::class, 'index']);
            Route::post('{module}', [\App\Http\Controllers\Controller::class, 'store']);
            Route::get('{module}/{id}', [\App\Http\Controllers\Controller::class, 'show']);
            Route::put('{module}/{id}', [\App\Http\Controllers\Controller::class, 'update']);
            Route::patch('{module}/{id}', [\App\Http\Controllers\Controller::class, 'update']);
            Route::delete('{module}/{id}', [\App\Http\Controllers\Controller::class, 'destroy']);
            Route::patch('{module}/{id}/restore', [\App\Http\Controllers\Controller::class, 'restore']);
        });
    });
});
