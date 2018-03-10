<?php

Route::get('/', AppController::class . '@home');
Route::get('/scans/{id}', AppController::class . '@scan');
