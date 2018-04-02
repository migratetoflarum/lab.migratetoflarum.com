<?php

Route::get('/', AppController::class . '@home');
Route::get('/login', AppController::class . '@home');
Route::get('/register', AppController::class . '@home');
Route::get('/account', AppController::class . '@home');
Route::get('/scans/{id}', AppController::class . '@scan');

$this->post('login', Auth\LoginController::class . '@login')->name('login');
$this->post('logout', Auth\LoginController::class . '@logout')->name('logout');
$this->post('register', 'Auth\RegisterController@register')->name('register');

$this->get('password/reset', 'Auth\ForgotPasswordController@showLinkRequestForm')->name('password.request');
$this->post('password/email', 'Auth\ForgotPasswordController@sendResetLinkEmail')->name('password.email');
$this->get('password/reset/{token}', 'Auth\ResetPasswordController@showResetForm')->name('password.reset');
$this->post('password/reset', 'Auth\ResetPasswordController@reset');

Route::get('auth/{provider}', Auth\SocialiteController::class . '@redirect');
Route::get('auth/{provider}/callback', Auth\SocialiteController::class . '@callback');
