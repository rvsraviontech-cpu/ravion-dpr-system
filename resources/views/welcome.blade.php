<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ravion ERP</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100 min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md bg-white rounded-xl shadow-lg p-8">

        <div class="text-center mb-8">
            <h1 class="text-3xl font-bold text-gray-900">
                Ravion ERP
            </h1>

            <p class="text-gray-500 mt-2">
                Ravion DPR System
            </p>
        </div>

        <div class="space-y-4">

            @auth
                <a href="{{ url('/dashboard') }}"
                   class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Go to Dashboard
                </a>
            @else
                <a href="{{ route('login') }}"
                   class="block w-full text-center bg-blue-600 text-white py-3 rounded-lg font-semibold hover:bg-blue-700">
                    Login
                </a>

                @if (Route::has('register'))
                    <a href="{{ route('register') }}"
                       class="block w-full text-center border border-gray-300 text-gray-700 py-3 rounded-lg font-semibold hover:bg-gray-50">
                        Register
                    </a>
                @endif
            @endauth

        </div>

        <div class="mt-8 text-center text-sm text-gray-400">
            © {{ date('Y') }} Ravion Homes. All rights reserved.
        </div>

    </div>

</body>
</html>