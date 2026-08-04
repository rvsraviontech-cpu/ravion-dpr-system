<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">

    <title>Ravion DPR</title>

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    @vite([
        'resources/css/app.css',
        'resources/js/app.js',
    ])

    @stack('styles')
</head>

<body style="background: {{ config('ravion.theme.page_bg') }};">

<div class="min-h-screen">

    <x-sidebar />

    <main class="ml-[272px] min-h-screen overflow-x-auto p-6">

        <x-topbar />

        @yield('content')

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@stack('scripts')

</body>
</html>