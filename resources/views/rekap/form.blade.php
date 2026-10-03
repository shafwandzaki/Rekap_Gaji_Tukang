@php
    // Data awal untuk Alpine: dari input lama (kalau validasi gagal),
    // dari database (kalau edit), atau kosong (kalau tambah).
    $daftarPekerja = $pekerjas
        ->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'jabatan' => $p->jabatan])
        ->values()->all();

    $itemsAwal = array_values(old('items', isset($rekap)
        ? $rekap->items->map(fn ($i) => [
            'pekerja_id'  => $i->pekerja_id ?? '',
            'hari'        => rtrim(rtrim((string) $i->hari, '0'), '.'),
            'gaji_harian' => $i->gaji_harian,
        ])->values()->all()
        : []));

    // Jasa Mandor: ambil dari input lama / database
    $jasa = collect(old('tambahans', isset($rekap) ? $rekap->tambahans->toArray() : []))
        ->firstWhere('keterangan', 'Jasa Mandor');

    $awal = [
        'pekerjas'   => $daftarPekerja,
        'items'      => $itemsAwal,
        'jasaMandor' => $jasa['nominal'] ?? '',
        'mulai'      => old('tanggal_mulai', isset($rekap) ? $rekap->tanggal_mulai->format('Y-m-d') : ''),
        'selesai'    => old('tanggal_selesai', isset($rekap) ? $rekap->tanggal_selesai->format('Y-m-d') : ''),
    ];

    // text-base di mobile (16px) supaya iOS tidak auto-zoom saat input difokuskan.
    // min-w-0 supaya input tidak melebar melebihi kontainer flex/grid.
    $kelasInput = 'w-full min-w-0 rounded-lg border border-white/10 bg-[#1c1c1e] px-3 py-2.5 text-base text-white sm:py-2 sm:text-sm';
@endphp

<script>
    function rekapForm(awal) {
        return {
            pekerjas: awal.pekerjas,
            items: awal.items,
            jasaMandor: awal.jasaMandor,
            mulai: awal.mulai,
            selesai: awal.selesai,
            pilihan: '',   // pekerja yang baru dipilih di dropdown tambah

            // Ubah teks jadi angka. "6,5" dan "6.5" sama-sama jadi 6.5
            angka(v) {
                const n = parseFloat(String(v ?? '').replace(',', '.'));
                return isNaN(n) ? 0 : n;
            },
            totalItem(item) {
                return Math.round(this.angka(item.hari) * this.angka(item.gaji_harian));
            },
            get totalGaji() {
                return this.items.reduce((s, i) => s + this.totalItem(i), 0);
            },
            get totalAkhir() {
                return this.totalGaji + Math.round(this.angka(this.jasaMandor));
            },
            rp(n) {
                return 'Rp ' + Math.round(n).toLocaleString('id-ID');
            },

            // Cari pekerja berdasarkan id (untuk menampilkan nama dan jabatan di kartu)
            cari(id) {
                return this.pekerjas.find(p => p.id == id);
            },
            nama(id) {
                const p = this.cari(id);
                return p ? p.nama : '(pekerja sudah dihapus)';
            },
            jabatan(id) {
                const p = this.cari(id);
                return p ? p.jabatan : '';
            },

            // Pekerja yang belum masuk rekap (isi dropdown tambah)
            get tersedia() {
                const terpakai = this.items.map(i => String(i.pekerja_id));
                return this.pekerjas.filter(p => !terpakai.includes(String(p.id)));
            },

            // Dipanggil saat pekerja dipilih di dropdown: langsung jadi kartu
            tambahPekerja() {
                if (!this.pilihan) return;
                this.items.push({ pekerja_id: this.pilihan, hari: '', gaji_harian: '' });
                this.pilihan = '';
            },
            hapusPekerja(index) {
                this.items.splice(index, 1);
            },
        };
    }
</script>

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        <p class="mb-1 font-semibold">Periksa kembali isian berikut:</p>
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

@if ($pekerjas->isEmpty())
    <div class="mb-6 rounded-lg border border-yellow-500/30 bg-yellow-500/10 px-4 py-3 text-sm text-yellow-200">
        Belum ada pekerja. <a href="{{ route('pekerja.create') }}" class="underline">Tambah pekerja</a> dulu.
    </div>
@endif

<div x-data="rekapForm(@js($awal))" class="w-full max-w-4xl">

    <h2 class="mb-4 font-semibold">Form Rekap</h2>

    {{-- Nama proyek --}}
    <label for="proyek" class="mb-1 block text-sm text-gray-300">Nama Proyek</label>
    <input id="proyek" type="text" name="proyek" placeholder="Proyek..."
           value="{{ old('proyek', $rekap->proyek->nama ?? '') }}"
           class="{{ $kelasInput }} mb-5" autocomplete="off">

    {{-- Tanggal --}}
    <label class="mb-1 block text-sm text-gray-300">Tanggal</label>
    <div class="mb-2 grid grid-cols-[1fr_auto_1fr] items-center gap-2">
        <input type="date" name="tanggal_mulai" x-model="mulai"
            @change="if (!selesai) selesai = mulai"
            class="{{ $kelasInput }} scheme-dark">
        <span class="text-gray-400">-</span>
        <input type="date" name="tanggal_selesai" x-model="selesai"
            :min="mulai"
            class="{{ $kelasInput }} scheme-dark">
    </div>
    <p class="mb-5 text-xs text-gray-500">Untuk satu hari saja, isi kedua tanggal sama.</p>

    {{-- Pekerja --}}
    <label class="mb-2 block text-sm text-gray-300">Pekerja</label>

    <p x-show="items.length === 0" class="mb-3 rounded-xl border border-dashed border-white/10 px-4 py-6 text-center text-sm text-gray-500">
        Belum ada pekerja di rekap ini. Pilih dari daftar di bawah.
    </p>

    <div class="space-y-3">
        <template x-for="(item, index) in items" :key="item.pekerja_id">
            <div class="rounded-xl border border-white/10 p-3 sm:p-4">

                {{-- Nama + jabatan + hapus --}}
                <div class="mb-3 flex items-center gap-2">
                    <div class="min-w-0 flex-1">
                        <span class="font-semibold" x-text="nama(item.pekerja_id)"></span>
                        <span class="ml-2 text-xs text-gray-400" x-text="jabatan(item.pekerja_id)"></span>
                        <input type="hidden" :name="`items[${index}][pekerja_id]`" :value="item.pekerja_id">
                    </div>

                    <button type="button" @click="hapusPekerja(index)"
                            class="shrink-0 rounded-lg border border-red-500 px-3 py-3 text-sm text-red-500 hover:bg-red-500/10"
                            title="Hapus pekerja" aria-label="Hapus pekerja">
                        <x-svg-hapus></x-svg-hapus>
                    </button>
                </div>

                {{-- Hari, gaji harian --}}
                <div class="gap-4 mb-4 flex">
                    <div class="w-16 sm:w-24">
                        <label class="mb-1 block text-xs text-gray-400">Hari</label>
                        <input type="text" inputmode="decimal" placeholder="0"
                               :name="`items[${index}][hari]`" x-model="item.hari"
                               class="{{ $kelasInput }}">
                    </div>
                    <div class="w-full">
                        <label class="mb-1 block text-xs text-gray-400">Gaji harian (Rp)</label>
                        <input type="number" min="0" inputmode="numeric"
                               :name="`items[${index}][gaji_harian]`" x-model="item.gaji_harian" placeholder="0"
                               class="{{ $kelasInput }}">
                    </div>
                </div>

                {{-- Total per pekerja --}}
                <div class="flex items-center justify-between border-t border-white/10 pt-3">
                    <p class="text-xs text-gray-400">Total</p>
                    <p class="font-semibold" x-text="rp(totalItem(item))"></p>
                </div>
            </div>
        </template>
    </div>

    {{-- Tambah pekerja: pilih dulu, kartu langsung muncul --}}
    <select x-model="pilihan" @change="tambahPekerja()" :disabled="tersedia.length === 0"
            class="mt-3 w-full rounded-xl border border-white/10 bg-[#1c1c1e] py-3 text-center text-sm text-white hover:bg-white/5 disabled:opacity-50 sm:py-2.5">
        <option value="" x-text="tersedia.length === 0 ? 'Semua pekerja sudah ditambahkan' : '+ Tambah Pekerja'"></option>
        <template x-for="p in tersedia" :key="p.id">
            <option :value="p.id" x-text="p.nama + ' (' + p.jabatan + ')'"></option>
        </template>
    </select>

    {{-- Tambahan: Jasa Mandor (tetap, satu isian) --}}
    <div class="mt-6 rounded-xl border border-white/10 p-3 sm:p-4">
        <p class="mb-3 text-sm">Tambahan <span class="text-xs text-gray-400">(Opsional)</span></p>

        <div class="flex items-center gap-3">
            <label for="jasa_mandor" class="w-28 shrink-0 text-sm text-gray-300 sm:w-36">Jasa Mandor</label>
            <input id="jasa_mandor" type="number" min="0" inputmode="numeric" placeholder="0"
                   x-model="jasaMandor" class="{{ $kelasInput }} sm:max-w-xs">
        </div>

        {{-- Dikirim ke server hanya kalau nominalnya lebih dari 0 --}}
        <template x-if="angka(jasaMandor) > 0">
            <div>
                <input type="hidden" name="tambahans[0][keterangan]" value="Jasa Mandor">
                <input type="hidden" name="tambahans[0][tipe]" value="tambah">
                <input type="hidden" name="tambahans[0][nominal]" :value="Math.round(angka(jasaMandor))">
            </div>
        </template>
    </div>

    {{-- Total --}}
    <div class="mt-6 flex items-center justify-between gap-3">
        <span class="text-base font-semibold sm:text-lg">Total</span>
        <span class="text-right text-lg font-semibold break-all" x-text="rp(totalAkhir)"></span>
    </div>

    {{-- Tombol --}}
    <div class="mt-6 flex gap-3 sm:flex-row sm:justify-end">
        <button type="submit"
                class="w-full rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition-colors hover:bg-blue-400 sm:w-auto sm:py-2.5">
            Simpan
        </button>
        <a href="{{ route('rekap.index') }}"
           class="w-full rounded-lg bg-gray-200 px-6 py-3 text-center text-sm font-semibold text-black transition-colors hover:bg-gray-300 sm:w-auto sm:py-2.5">
            Batal
        </a>
    </div>
</div>