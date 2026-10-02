<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Masuk - Rekap Gaji Tukang</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-[#131315] text-white min-h-screen flex items-center justify-center p-4">

    <div class="w-full max-w-sm p-6">
        <h1 class="text-xl font-semibold mb-1">Rekap Gaji Tukang</h1>
        <p class="text-sm mb-6">Silakan masuk untuk melanjutkan</p>

        <form method="POST" action="{{ route('login') }}">
            @csrf
            <div class="flex flex-col gap-2">
                <input id="email" placeholder="Email" type="email" name="email" value="{{ old('email') }}"
                    class="w-full bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none px-3 py-2 mb-1" required autofocus>
                @error('email')
                    <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                @enderror

                <input id="password" placeholder="Password" type="password" name="password"
                    class="w-full bg-white/5 border border-white/10 rounded-lg text-white focus:outline-none px-3 py-2 mb-1" required>
                @error('password')
                    <p class="text-sm text-red-600 mb-2">{{ $message }}</p>
                @enderror
            </div>

            <label class="flex items-center gap-2 text-sm mt-3 mb-5">
                <input type="checkbox" name="ingat"> Ingat saya
            </label>

            <button type="submit"
                    class="w-full bg-blue-700 text-white rounded-lg py-2 font-medium">
                Masuk
            </button>
        </form>
    </div>

</body>
</html>