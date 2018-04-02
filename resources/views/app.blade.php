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
@if (isset($errors) && count($errors) > 0)
    <div class="container">
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
<?php
$data = [
    'csrf' => csrf_token(),
    'user' => auth()->id(),
    'preload' => isset($preload) ? json_encode($preload) : '[]',
];
?>
<div id="app" {!! collect($data)->map(function ($value, string $attr) {
        return 'data-' . $attr . '="' . e($value) . '"';
    })->implode(' ') !!}></div>
<script src="{{ mix('js/app.js') }}"></script>
@include('parts.analytics')
</body>
</html>
