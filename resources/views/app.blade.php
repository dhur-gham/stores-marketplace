<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Stores Marketplace') }}</title>

        <meta name="description" content="Discover a wide variety of stores and products in our marketplace. Shop from multiple stores, compare prices, and find the best deals all in one place.">
        <meta name="keywords" content="stores, marketplace, shopping, products, online store, e-commerce">
        <meta name="author" content="{{ config('app.name', 'Stores Marketplace') }}">
        <meta name="theme-color" content="#3b82f6">

        <link rel="icon" href="/icon.webp" type="image/webp" sizes="any">
        <link rel="apple-touch-icon" href="/icon.webp">
        <link rel="image_src" href="/icon.webp">

        <meta property="og:type" content="website">
        <meta property="og:title" content="{{ config('app.name', 'Stores Marketplace') }}">
        <meta property="og:description" content="Discover a wide variety of stores and products in our marketplace. Shop from multiple stores, compare prices, and find the best deals all in one place.">
        <meta property="og:image" content="{{ url('/icon.webp') }}">
        <meta property="og:url" content="{{ url('/') }}">
        <meta property="og:site_name" content="{{ config('app.name', 'Stores Marketplace') }}">

        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:title" content="{{ config('app.name', 'Stores Marketplace') }}">
        <meta name="twitter:description" content="Discover a wide variety of stores and products in our marketplace. Shop from multiple stores, compare prices, and find the best deals all in one place.">
        <meta name="twitter:image" content="{{ url('/icon.webp') }}">

        @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    </head>
    <body>
        <div id="app"></div>
    </body>
</html>

