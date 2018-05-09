<?php

Route::resource('scans', Api\ScanController::class, ['only' => ['show', 'store']]);
Route::resource('extensions', Api\ExtensionController::class, ['only' => ['index']]);
