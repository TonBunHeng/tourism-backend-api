<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json([
        'service'   => 'Tourism API Backend',
        'status'    => 'online',
        'version'   => '1.0.0',
        'timestamp' => now()->toIso8601String(),
    ]);
});
