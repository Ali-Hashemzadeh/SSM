<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return json_encode(['success' => true]);
});

require __DIR__.'/api/panel.php';
require __DIR__.'/api/client.php';
require __DIR__.'/api/auth.php';