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
    'base-domain' => url(''),
    'showcase-domain' => config('scanner.showcase_domain'),
];
?>
<div id="app" {!! collect($data)->map(function ($value, string $attr) {
        return 'data-' . $attr . '="' . e($value) . '"';
    })->implode(' ') !!}></div>
<script src="{{ mix('js/showcase.js') }}"></script>
@include('analytics', ['matomo' => config('services.matomo-showcase')])
</body>
</html>
