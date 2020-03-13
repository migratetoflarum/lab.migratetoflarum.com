<?php

Route::resource('scans', Api\ScanController::class, ['only' => ['show', 'store']]);
Route::resource('websites', Api\WebsiteController::class, ['only' => ['index']]);
Route::resource('tasks', Api\TaskController::class, ['only' => ['index']]);
Route::post('opt-out-check', Api\OptOutController::class . '@check');
