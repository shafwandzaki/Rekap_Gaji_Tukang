<?php

namespace App\Http\Controllers;

use App\Models\Pekerja;
use App\Models\Proyek;
use App\Models\Rekap;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RekapController extends Controller
{
    // Daftar rekap (+ filter proyek)
    public function index(Request $request)
    {
        $proyekId = $request->query('proyek');

        $rekaps = Rekap::with('proyek')
            ->when($proyekId, fn ($q) => $q->where('proyek_id', $proyekId))
            ->orderByDesc('tanggal_mulai')
            ->orderByDesc('id')
            ->get();

        $proyeks = Proyek::orderBy('nama')->get();

        return view('rekap.index', compact('rekaps', 'proyeks', 'proyekId'));
    }

    // Form tambah
    public function create()
    {
        $pekerjas = Pekerja::orderBy('nama')->get();
        $proyeks  = Proyek::orderBy('nama')->get();

        return view('rekap.create', compact('pekerjas', 'proyeks'));
    }

    // Simpan rekap baru
    public function store(Request $request)
    {
        $data = $this->validasi($request);

        $this->simpan($data, new Rekap());

        return redirect()->route('rekap.index')
            ->with('sukses', 'Rekap berhasil disimpan.');
    }

    // Halaman detail rekap
    public function show(Rekap $rekap)
    {
        $rekap->load('proyek', 'items', 'tambahans');

        $totalGaji = $rekap->items->sum('total');

        $totalTambahan = $rekap->tambahans->sum(
            fn ($t) => $t->tipe === 'tambah' ? $t->nominal : -$t->nominal
        );

        return view('rekap.show', compact('rekap', 'totalGaji', 'totalTambahan'));
    }

    // Form ubah
    public function edit(Rekap $rekap)
    {
        $rekap->load('proyek', 'items', 'tambahans');

        $pekerjas = Pekerja::orderBy('nama')->get();
        $proyeks  = Proyek::orderBy('nama')->get();

        return view('rekap.edit', compact('rekap', 'pekerjas', 'proyeks'));
    }

    // Simpan perubahan
    public function update(Request $request, Rekap $rekap)
    {
        $data = $this->validasi($request);

        $this->simpan($data, $rekap);

        return redirect()->route('rekap.index')
            ->with('sukses', 'Rekap diperbarui.');
    }

    // Hapus (item + tambahan ikut terhapus lewat cascade)
    public function destroy(Rekap $rekap)
    {
        $rekap->delete();

        return redirect()->route('rekap.index')
            ->with('sukses', 'Rekap dihapus.');
    }

    // ---------------------------------------------------------

    private function validasi(Request $request): array
    {
        // Izinkan penulisan desimal dengan koma, mis. "6,5" menjadi "6.5"
        $items = collect($request->input('items', []))
            ->map(function ($item) {
                if (isset($item['hari'])) {
                    $item['hari'] = str_replace(',', '.', $item['hari']);
                }
                return $item;
            })
            ->all();

        $request->merge(['items' => $items]);

        return $request->validate([
            'proyek'          => ['required', 'string', 'max:100'],
            'tanggal_mulai'   => ['required', 'date'],
            'tanggal_selesai' => ['required', 'date', 'after_or_equal:tanggal_mulai'],
            'catatan'         => ['nullable', 'string', 'max:500'],

            'items'               => ['required', 'array', 'min:1'],
            'items.*.pekerja_id'  => ['required', 'distinct', 'exists:pekerjas,id'],
            'items.*.hari'        => ['required', 'numeric', 'min:0.5', 'max:31'],
            'items.*.gaji_harian' => ['required', 'integer', 'min:1', 'max:100000000'],

            'tambahans'              => ['nullable', 'array'],
            'tambahans.*.keterangan' => ['required', 'string', 'max:100'],
            'tambahans.*.nominal'    => ['required', 'integer', 'min:1', 'max:1000000000'],
            'tambahans.*.tipe'       => ['required', 'in:tambah,potong'],
        ], [
            'proyek.required'                => 'Nama proyek wajib diisi.',
            'tanggal_mulai.required'         => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required'       => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai tidak boleh sebelum tanggal mulai.',
            'items.required'                 => 'Tambahkan minimal satu pekerja.',
            'items.min'                      => 'Tambahkan minimal satu pekerja.',
            'items.*.pekerja_id.required'    => 'Pilih pekerja.',
            'items.*.pekerja_id.distinct'    => 'Satu pekerja tidak boleh dimasukkan dua kali.',
            'items.*.pekerja_id.exists'      => 'Pekerja tidak ditemukan.',
            'items.*.hari.required'          => 'Jumlah hari wajib diisi.',
            'items.*.hari.numeric'           => 'Jumlah hari harus berupa angka.',
            'items.*.hari.min'               => 'Hari minimal 0,5.',
            'items.*.hari.max'               => 'Hari maksimal 31.',
            'items.*.gaji_harian.required'   => 'Gaji harian wajib diisi.',
            'items.*.gaji_harian.integer'    => 'Gaji harian harus berupa angka bulat.',
            'items.*.gaji_harian.min'        => 'Gaji harian minimal 1.',
            'tambahans.*.keterangan.required'=> 'Keterangan tambahan wajib diisi.',
            'tambahans.*.nominal.required'   => 'Nominal tambahan wajib diisi.',
            'tambahans.*.nominal.integer'    => 'Nominal tambahan harus berupa angka bulat.',
        ]);
    }

    // Dipakai bersama oleh store() dan update()
    private function simpan(array $data, Rekap $rekap): void
    {
        DB::transaction(function () use ($data, $rekap) {

            // 1. Proyek: ambil kalau sudah ada, buat kalau belum
            $proyek = Proyek::firstOrCreate(['nama' => trim($data['proyek'])]);

            // 2. Ambil data pekerja sekaligus (untuk salinan nama + jabatan)
            $pekerjas = Pekerja::whereIn('id', collect($data['items'])->pluck('pekerja_id'))
                ->get()
                ->keyBy('id');

            // 3. Simpan rekap (baru atau yang sudah ada)
            $rekap->fill([
                'proyek_id'       => $proyek->id,
                'tanggal_mulai'   => $data['tanggal_mulai'],
                'tanggal_selesai' => $data['tanggal_selesai'],
                'catatan'         => $data['catatan'] ?? null,
            ])->save();

            // 4. Saat edit: hapus baris lama, lalu isi ulang
            $rekap->items()->delete();
            $rekap->tambahans()->delete();

            // 5. Baris pekerja, total DIHITUNG ULANG di server
            $totalGaji = 0;
            foreach ($data['items'] as $baris) {
                $pekerja = $pekerjas[$baris['pekerja_id']];
                $total   = (int) round($baris['hari'] * $baris['gaji_harian']);
                $totalGaji += $total;

                $rekap->items()->create([
                    'pekerja_id'  => $pekerja->id,
                    'nama'        => $pekerja->nama,
                    'jabatan'     => $pekerja->jabatan,
                    'hari'        => $baris['hari'],
                    'gaji_harian' => $baris['gaji_harian'],
                    'total'       => $total,
                ]);
            }

            // 6. Baris tambahan
            $totalTambahan = 0;
            foreach ($data['tambahans'] ?? [] as $baris) {
                $rekap->tambahans()->create([
                    'keterangan' => trim($baris['keterangan']),
                    'nominal'    => $baris['nominal'],
                    'tipe'       => $baris['tipe'],
                ]);

                $totalTambahan += $baris['tipe'] === 'tambah'
                    ? $baris['nominal']
                    : -$baris['nominal'];
            }

            // 7. Total keseluruhan
            $totalAkhir = $totalGaji + $totalTambahan;

            if ($totalAkhir < 0) {
                throw ValidationException::withMessages([
                    'tambahans' => 'Total rekap tidak boleh negatif. Periksa nominal potongan.',
                ]);
            }

            $rekap->update(['total' => $totalAkhir]);
        });
    }
}