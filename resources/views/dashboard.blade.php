@extends('app')

@section('isi')
    <h2 class="text-2xl font-bold mb-8">Dashboard</h2>
    <p>Halo, {{ Auth::user()->name }}!</p>
    <p class="text-gray-400 mb-4">Ngapain kita hari ini?</p>

    <div class="flex justify-between gap-2 mb-8">
        <div class="bg-white/5 border border-white/30 rounded-lg p-4 h-full w-full">
            <p class="text-sm">Jumlah Proyek</p>
            <p class="font-semibold text-xl">{{ $jumlahProyek }}</p>
        </div>
        <div class="bg-white/5 border border-white/30 rounded-lg p-4 h-full w-full">
            <p class="text-sm">Total Gaji</p>
            <p class="font-semibold text-xl">Rp {{ number_format($totalGaji, 0, ',', '.') }}</p>
        </div>
    </div>

    <p class="font-semibold text-2xl text-center">Selamat Datang!</p>
    <p class="font-normal text-sm text-center text-gray-400">Halaman dashboard aplikasi rekap gaji tukang.</p>
@endsection