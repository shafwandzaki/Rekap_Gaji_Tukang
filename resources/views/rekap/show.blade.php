@extends('app')

@section('isi')

    <a href="{{ route('rekap.index') }}" class="mb-4 inline-block text-2xl font-semibold text-gray-300 hover:text-white">
        &larr; Detail Rekap
    </a>

    <div class="mb-4">
        <h1 class="text-xl font-bold">{{ $rekap->proyek->nama }}</h1>
        <p class="text-sm text-gray-400">
            {{ $rekap->tanggal_mulai->format('d/m/Y') }}
            @if (! $rekap->tanggal_mulai->isSameDay($rekap->tanggal_selesai))
                s/d {{ $rekap->tanggal_selesai->format('d/m/Y') }}
            @endif
        </p>
    </div>

    <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="w-full text-left text-sm">
            <thead class="bg-white/5 text-gray-300">
                <tr>
                    <th class="px-4 py-3">Nama</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3">Hari</th>
                    <th class="px-4 py-3 text-right">Gaji Harian</th>
                    <th class="px-4 py-3 text-right">Total</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @foreach ($rekap->items as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->nama }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $item->jabatan }}</td>
                        <td class="px-4 py-3">{{ rtrim(rtrim(number_format($item->hari, 1, ',', '.'), '0'), ',') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->gaji_harian, 0, ',', '.') }}</td>
                        <td class="px-4 py-3 text-right">{{ number_format($item->total, 0, ',', '.') }}</td>
                    </tr>
                @endforeach

                @foreach ($rekap->tambahans as $t)
                    <tr class="bg-white/5">
                        <td colspan="4" class="px-4 py-3 text-center">{{ $t->keterangan }}</td>
                        <td class="px-4 py-3 text-right {{ $t->tipe === 'potong' ? 'text-red-300' : '' }}">
                            {{ $t->tipe === 'potong' ? '-' : '' }}{{ number_format($t->nominal, 0, ',', '.') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="border-t border-white/20 font-semibold">
                    <td colspan="4" class="px-4 py-3 text-center">Total</td>
                    <td class="px-4 py-3 text-right">{{ number_format($rekap->total, 0, ',', '.') }}</td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="mt-6 flex flex-wrap items-center gap-3">
        {{-- Export Excel belum dibuat, diaktifkan di langkah berikutnya --}}
        <a href="{{ route('rekap.export', $rekap) }}" class="rounded-lg bg-green-600 px-5 py-2 text-sm hover:bg-green-400 font-bold">
            Export XLS
        </a>

        <a href="{{ route('rekap.edit', $rekap) }}"
           class="rounded-lg bg-blue-600 px-5 py-2 text-sm font-semibold text-white hover:bg-blue-500">
            Edit
        </a>

        <form method="POST" action="{{ route('rekap.destroy', $rekap) }}"
              onsubmit="return confirm('Hapus rekap ini? Tindakan tidak bisa dibatalkan.')">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="rounded-lg bg-red-600 px-5 py-2 text-sm font-semibold text-white hover:bg-red-500">
                Hapus
            </button>
        </form>
    </div>

@endsection