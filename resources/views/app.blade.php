<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <title>{{ config('app.name') }}</title>
</head>
<body>
<?php
$data = [
    'csrf' => csrf_token(),
    'preload' => isset($preload) ? json_encode($preload) : '[]',
    'sponsoring' => json_encode(config('sponsoring')),
];
?>
<div id="app" {!! collect($data)->map(function ($value, string $attr) {
        return 'data-' . $attr . '="' . e($value) . '"';
    })->implode(' ') !!}></div>
<script src="{{ mix('js/app.js') }}"></script>
@include('parts.analytics')
</body>
</html>
