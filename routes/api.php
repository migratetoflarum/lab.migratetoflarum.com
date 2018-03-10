<?php

Route::resource('scans', Api\ScanController::class, ['only' => ['show', 'store']]);
