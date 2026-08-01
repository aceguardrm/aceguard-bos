<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>
        @yield('title', 'AceGuard Secure CRM')
    </title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <style>
        :root{
            --ag-primary:#0F4C81;
            --ag-secondary:#1F2937;
            --ag-success:#10B981;
            --ag-warning:#F59E0B;
            --ag-danger:#EF4444;
            --ag-background:#F5F7FA;
            --ag-card:#FFFFFF;
            --ag-text:#1E293B;
            --ag-border:#E5E7EB;
        }

        body{
            margin:0;
            font-family:'Inter',sans-serif;
            background:var(--ag-background);
            color:var(--ag-text);
        }

        .ag-app{
            display:flex;
            min-height:100vh;
        }

        .ag-content{
            flex:1;
            display:flex;
            flex-direction:column;
        }

        .ag-main{
            padding:32px;
        }
    </style>

    @stack('styles')

</head>

<body>

<div class="ag-app">

    @include('components.navigation.sidebar')

    <div class="ag-content">

        @include('components.layout.header')

        <main class="ag-main">

            @yield('content')

        </main>

    </div>

</div>

@stack('scripts')

</body>
</html>