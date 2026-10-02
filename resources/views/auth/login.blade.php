<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Rekap Gaji Tukang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm bg-white rounded-xl shadow p-6">
        <h1 class="text-xl font-semibold mb-1">Rekap Gaji Tukang</h1>
        <p class="text-sm text-gray-500 mb-6">Silakan masuk untuk melanjutkan</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf

            <label class="block text-sm mb-1" for="email">Email</label>
            <input id="email" type="email" name="email" value="{{ old('email') }}"
                   class="w-full border rounded-lg px-3 py-2 mb-1" required autofocus>
            @error('email')
                <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
            @enderror

            <label class="block text-sm mb-1 mt-3" for="password">Password</label>
            <input id="password" type="password" name="password"
                   class="w-full border rounded-lg px-3 py-2 mb-1" required>
            @error('password')
                <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
            @enderror

            <label class="flex items-center gap-2 text-sm mt-3 mb-5">
                <input type="checkbox" name="ingat"> Ingat saya
            </label>

            <button type="submit"
                    class="w-full bg-gray-900 text-white rounded-lg py-2 font-medium">
                Masuk
            </button>
        </form>
    </div>

</body>
</html>