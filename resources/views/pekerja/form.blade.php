<h2 class="mb-4 font-semibold">Form Pekerja</h2>

<div class="grid gap-6 md:grid-cols-2">

    {{-- Nama Pekerja --}}
    <div>
        <label for="nama" class="mb-1 block text-sm text-gray-300">Nama Pekerja</label>
        <input
            id="nama"
            type="text"
            name="nama"
            value="{{ old('nama', $pekerja->nama ?? '') }}"
            class="w-full rounded-lg border bg-[#1c1c1e] px-4 py-2.5 text-sm text-white
                   {{ $errors->has('nama') ? 'border-red-500/60' : 'border-white/10' }}"
            autofocus
        >
        @error('nama')
            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>

    {{-- Jabatan --}}
    <div>
        <label for="jabatan" class="mb-1 block text-sm text-gray-300">Jabatan</label>
        <select
            id="jabatan"
            name="jabatan"
            class="w-full rounded-lg border bg-[#1c1c1e] px-4 py-2.5 text-sm text-white
                   {{ $errors->has('jabatan') ? 'border-red-500/60' : 'border-white/10' }}"
        >
            <option value="">Pilih Jabatan</option>
            <option value="Tukang" @selected(old('jabatan', $pekerja->jabatan ?? '') === 'Tukang')>Tukang</option>
            <option value="Kenek" @selected(old('jabatan', $pekerja->jabatan ?? '') === 'Kenek')>Kenek</option>
        </select>
        @error('jabatan')
            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
        @enderror
    </div>
</div>

<div class="mt-8 flex items-center gap-3">
    <button type="submit"
            class="rounded-lg bg-green-600 px-6 py-2.5 text-sm font-semibold text-white transition-colors hover:bg-green-500">
        Simpan
    </button>
    <a href="{{ route('pekerja.index') }}"
       class="rounded-lg bg-gray-200 px-6 py-2.5 text-sm font-semibold text-black transition-colors hover:bg-gray-300">
        Batal
    </a>
</div>