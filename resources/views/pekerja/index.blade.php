@extends('app')

@section('isi')

    <div class="mb-6 flex items-center justify-between">
        <h1 class="text-2xl font-bold">Pekerja</h1>
        <a href="{{ route('pekerja.create') }}"
           class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-semibold text-white transition-colors hover:bg-blue-500">
            + Tambah
        </a>
    </div>

    <div class="overflow-x-auto rounded-xl border border-white/10">
        <table class="w-full text-left text-sm">
            <thead class="bg-white/5 text-gray-400">
                <tr>
                    <th class="px-4 py-3">Nama Pekerja</th>
                    <th class="px-4 py-3">Jabatan</th>
                    <th class="px-4 py-3 text-right">Aksi</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/10">
                @forelse ($pekerjas as $item)
                    <tr>
                        <td class="px-4 py-3">{{ $item->nama }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $item->jabatan }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('pekerja.edit', $item) }}"
                                   class="rounded-lg border border-blue-700 px-3 py-1.5 text-blue-400 transition-colors hover:bg-white/5">
                                    <x-svg-edit></x-svg-edit>
                                </a>
                                <form method="POST" action="{{ route('pekerja.destroy', $item) }}"
                                      onsubmit="return confirm('Hapus {{ addslashes($item->nama) }}? Riwayat rekap lama tetap aman.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="rounded-lg border border-red-500/30 px-3 py-1.5 text-red-300 transition-colors hover:bg-red-500/10">
                                        <x-svg-hapus></x-svg-hapus>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">Belum ada pekerja.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

@endsection