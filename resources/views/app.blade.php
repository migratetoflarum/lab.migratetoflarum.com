<!doctype html>
<html lang="{{ app()->getLocale() }}">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link href="{{ mix('css/app.css') }}" rel="stylesheet">
    <link href="{{ mix('images/fontawesome-flask-solid.svg') }}" rel="icon">
    <title>{{ config('app.name') }}</title>
</head>
<body>
<?php
$data = [
    'csrf' => csrf_token(),
    'base-domain' => url(''),
    'showcase-domain' => config('scanner.showcase_domain'),
    'preload' => isset($preload) ? \GuzzleHttp\json_encode($preload) : '[]',
    'sponsoring' => \GuzzleHttp\json_encode(config('sponsoring')),
];
if ($probability = config('scanner.secret_extensions_probability')) {
    $data['secret-extension-probability'] = $probability;
}
?>
<div id="app" {!! collect($data)->map(function ($value, string $attr) {
        return 'data-' . $attr . '="' . e($value) . '"';
    })->implode(' ') !!}></div>
<script src="{{ mix('js/app.js') }}"></script>
@include('analytics', ['matomo' => config('services.matomo-lab')])
</body>
</html>
