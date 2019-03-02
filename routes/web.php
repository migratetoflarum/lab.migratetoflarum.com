<?php

Route::get('/', AppController::class . '@home');
Route::get('/scans/{id}', AppController::class . '@scan');
Route::get('/extensions', AppController::class . '@home');
Route::get('/opt-out', AppController::class . '@home');
