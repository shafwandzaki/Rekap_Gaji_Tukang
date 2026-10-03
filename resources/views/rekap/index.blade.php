@extends('app')

@section('isi')

    <div class="mb-6 flex items-center justify-between gap-3">
        <h1 class="text-2xl font-bold">Rekap Gaji</h1>
        <a href="{{ route('rekap.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-500">
            + Tambah
        </a>
    </div>

    {{-- Filter proyek --}}
    <form method="GET" action="{{ route('rekap.index') }}" class="mb-4">
        <select name="proyek" onchange="this.form.submit()"
                class="w-full rounded-lg border border-white/10 bg-[#1c1c1e] px-4 py-2.5 text-sm text-white md:w-72">
            <option value="">Semua proyek</option>
            @foreach ($proyeks as $p)
                <option value="{{ $p->id }}" @selected((string) $proyekId === (string) $p->id)>{{ $p->nama }}</option>
            @endforeach
        </select>
    </form>

    <div class="space-y-3">
        @forelse ($rekaps as $r)
            <a href="{{ route('rekap.show', $r) }}"
               class="flex items-center justify-between rounded-xl border border-white/10 px-5 py-4 transition-colors hover:bg-white/5">
                <div>
                    <p class="font-semibold">{{ $r->proyek->nama }}</p>
                    <p class="text-sm text-gray-300">
                        {{ $r->tanggal_mulai->format('d/m/Y') }}
                        @if (! $r->tanggal_mulai->isSameDay($r->tanggal_selesai))
                            - {{ $r->tanggal_selesai->format('d/m/Y') }}
                        @endif
                    </p>
                    <p class="mt-1 font-semibold">Rp {{ number_format($r->total, 0, ',', '.') }}</p>
                </div>
                <span class="text-xl text-gray-400">&gt;</span>
            </a>
        @empty
            <p class="rounded-xl border border-white/10 px-5 py-8 text-center text-gray-500">
                Belum ada rekap.
            </p>
        @endforelse
    </div>

@endsection