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
        : [['pekerja_id' => '', 'hari' => '', 'gaji_harian' => '']]));

    $tambahansAwal = array_values(old('tambahans', isset($rekap)
        ? $rekap->tambahans->map(fn ($t) => [
            'keterangan' => $t->keterangan,
            'nominal'    => $t->nominal,
            'tipe'       => $t->tipe,
        ])->values()->all()
        : []));

    $awal = [
        'pekerjas'  => $daftarPekerja,
        'items'     => $itemsAwal,
        'tambahans' => $tambahansAwal,
        'mulai'     => old('tanggal_mulai', isset($rekap) ? $rekap->tanggal_mulai->format('Y-m-d') : ''),
        'selesai'   => old('tanggal_selesai', isset($rekap) ? $rekap->tanggal_selesai->format('Y-m-d') : ''),
    ];

    // text-base di mobile (16px) supaya iOS tidak auto-zoom saat input difokuskan.
    // min-w-0 supaya input tidak melebar melebihi kontainer flex/grid.
    $kelasInput = 'w-full min-w-0 rounded-lg border border-white/10 bg-[#1c1c1e] px-3 py-2.5 text-base text-white sm:py-2 sm:text-sm';
@endphp

{{-- Pastikan layout utama punya: <meta name="viewport" content="width=device-width, initial-scale=1"> --}}

<script>
    function rekapForm(awal) {
        return {
            pekerjas: awal.pekerjas,
            items: awal.items,
            tambahans: awal.tambahans,
            mulai: awal.mulai,
            selesai: awal.selesai,

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
            get totalTambahan() {
                return this.tambahans.reduce(
                    (s, t) => s + (t.tipe === 'potong' ? -1 : 1) * this.angka(t.nominal), 0);
            },
            get totalAkhir() {
                return this.totalGaji + this.totalTambahan;
            },
            rp(n) {
                return 'Rp ' + Math.round(n).toLocaleString('id-ID');
            },
            jabatan(id) {
                const p = this.pekerjas.find(p => p.id == id);
                return p ? p.jabatan : '';
            },
            // Apakah pekerja ini sudah dipilih di baris lain?
            dipakai(id, indexSaatIni) {
                return this.items.some((it, k) => k !== indexSaatIni && it.pekerja_id == id);
            },
            tambahPekerja() {
                this.items.push({ pekerja_id: '', hari: '', gaji_harian: '' });
            },
            hapusPekerja(index) {
                this.items.splice(index, 1);
            },
            tambahBaris() {
                this.tambahans.push({ keterangan: '', nominal: '', tipe: 'tambah' });
            },
            hapusBaris(index) {
                this.tambahans.splice(index, 1);
            },
        };
    }
</script>

@if ($errors->any())
    <div class="mb-6 rounded-lg border border-red-500/30 bg-red-500/10 px-4 py-3 text-sm text-red-300">
        <p class="mb-1 font-semibold">Periksa kembali isian berikut:</p>
        <ul class="list-inside list-disc space-y-0.5">
            @foreach ($errors->all() as $error)
                <li class="wrap-break-words">{{ $error }}</li>
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
               class="{{ $kelasInput }} [color-scheme-dark]">
        <span class="text-gray-400">-</span>
        <input type="date" name="tanggal_selesai" x-model="selesai"
               class="{{ $kelasInput }} [color-scheme-dark]">
    </div>
    <p class="mb-5 text-xs text-gray-500">Untuk satu hari saja, isi kedua tanggal sama.</p>

    {{-- Pekerja --}}
    <label class="mb-2 block text-sm text-gray-300">Pekerja</label>

    <div class="space-y-3">
        <template x-for="(item, index) in items" :key="index">
            <div class="rounded-xl border border-white/10 p-3 sm:p-4">

                {{-- Baris pilih pekerja + hapus --}}
                <div class="mb-3 flex items-center gap-2">
                    <div class="min-w-0 flex-1 sm:flex sm:items-center sm:gap-3">
                        <select :name="`items[${index}][pekerja_id]`" x-model="item.pekerja_id"
                                class="{{ $kelasInput }} sm:max-w-xs">
                            <option value="">Pilih pekerja</option>
                            @foreach ($pekerjas as $p)
                                <option value="{{ $p->id }}" :disabled="dipakai({{ $p->id }}, index)">{{ $p->nama }}</option>
                            @endforeach
                        </select>
                        <span class="mt-1 block truncate text-xs text-gray-400 sm:mt-0"
                              x-text="jabatan(item.pekerja_id)"></span>
                    </div>

                    <button type="button" @click="hapusPekerja(index)"
                            class="shrink-0 self-start rounded-lg border border-red-500 px-3 py-3 text-sm text-red-500 hover:bg-red-500/10 sm:self-auto sm:px-2.5 sm:py-1"
                            title="Hapus baris" aria-label="Hapus pekerja">
                            <x-svg-hapus></x-svg-hapus>
                    </button>
                </div>

                {{-- Hari, gaji harian, total --}}
                <div class="grid gap-4 sm:flex sm:flex-wrap sm:items-end">
                    <div class="w-16">
                        <label class="mb-1 block text-xs text-gray-400">Hari</label>
                        <input type="text" inputmode="decimal" placeholder="7"
                               :name="`items[${index}][hari]`" x-model="item.hari"
                               class="{{ $kelasInput }}">
                    </div>
                    <div class="sm:w-56">
                        <label class="mb-1 block text-xs text-gray-400">Gaji harian (Rp)</label>
                        <input type="number" min="0" inputmode="numeric"
                               :name="`items[${index}][gaji_harian]`" x-model="item.gaji_harian"
                               class="{{ $kelasInput }}">
                    </div>
                    <div class="col-span-2 flex items-center justify-between border-t border-white/10 pt-3 sm:ml-auto sm:block sm:border-0 sm:pt-0 sm:text-right">
                        <p class="text-xs text-gray-400">Total</p>
                        <p class="font-semibold" x-text="rp(totalItem(item))"></p>
                    </div>
                </div>
            </div>
        </template>
    </div>

    <button type="button" @click="tambahPekerja()"
            class="mt-3 w-full rounded-xl border border-white/10 py-3 text-sm hover:bg-white/5 sm:py-2.5">
        + Tambah Pekerja
    </button>

    {{-- Tambahan --}}
    <div class="mt-6 rounded-xl border border-white/10 p-3 sm:p-4">
        <p class="mb-3 text-sm">Tambahan <span class="text-xs text-gray-400">(Opsional)</span></p>

        <div class="space-y-3 sm:space-y-2">
            <template x-for="(t, index) in tambahans" :key="index">
                {{-- Mobile: keterangan 1 baris penuh, lalu nominal + tipe + hapus. Desktop: 1 baris. --}}
                <div class="grid grid-cols-[1fr_auto] gap-2 rounded-lg border border-white/5 p-2 sm:flex sm:flex-wrap sm:items-center sm:border-0 sm:p-0">
                    <input type="text" placeholder="Jasa Mandor" list="saran-tambahan"
                           :name="`tambahans[${index}][keterangan]`" x-model="t.keterangan"
                           class="{{ $kelasInput }} col-span-2 sm:w-44">

                    <input type="number" min="0" inputmode="numeric" placeholder="0"
                           :name="`tambahans[${index}][nominal]`" x-model="t.nominal"
                           class="{{ $kelasInput }} sm:w-40">

                    <div class="flex gap-2">
                        <select :name="`tambahans[${index}][tipe]`" x-model="t.tipe"
                                class="{{ $kelasInput }} sm:w-28">
                            <option value="tambah">Tambah</option>
                            <option value="potong">Potong</option>
                        </select>
                        <button type="button" @click="hapusBaris(index)"
                                class="shrink-0 rounded-lg border border-red-500/30 px-3 py-2 text-sm text-red-300 hover:bg-red-500/10 sm:px-2.5 sm:py-1"
                                title="Hapus baris" aria-label="Hapus baris tambahan">✕</button>
                    </div>
                </div>
            </template>
        </div>

        <datalist id="saran-tambahan">
            <option value="Jasa Mandor"></option>
            <option value="Bonus"></option>
            <option value="Kasbon"></option>
        </datalist>

        <button type="button" @click="tambahBaris()"
                class="mt-3 py-1 text-sm text-blue-400 hover:text-blue-300">
            + Tambah baris
        </button>
    </div>

    {{-- Total --}}
    <div class="mt-6 flex items-center justify-between gap-3">
        <span class="text-base font-semibold sm:text-lg">Total</span>
        <span class="text-right text-lg font-semibold break-all"
              :class="totalAkhir < 0 ? 'text-red-400' : ''"
              x-text="rp(totalAkhir)"></span>
    </div>
    <p x-show="totalAkhir < 0" class="mt-1 text-right text-sm text-red-400">
        Total negatif. Periksa nominal potongan.
    </p>

    {{-- Tombol: di mobile penuh & bertumpuk (Simpan di atas), di desktop sejajar kanan --}}
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