<?php

use Illuminate\Support\Facades\Route;

Route::get('/', AppController::class . '@home');
Route::get('/scans/{id}', AppController::class . '@scan');
Route::get('/opt-out', AppController::class . '@home');
Route::get('/tasks', AppController::class . '@home');
Route::get('/showcase', AppController::class . '@showcase');
