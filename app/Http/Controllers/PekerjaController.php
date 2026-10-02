<?php

namespace App\Http\Controllers;

use App\Models\Pekerja;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PekerjaController extends Controller
{
    public function index()
    {
        $pekerjas = Pekerja::orderBy('nama')->get();

        return view('pekerja.index', compact('pekerjas'));
    }

    public function create()
    {
        return view('pekerja.create');
    }

    public function store(Request $request)
    {
        Pekerja::create($this->validasi($request));

        return redirect()->route('pekerja.index')
            ->with('sukses', 'Pekerja berhasil ditambahkan.');
    }

    public function edit(Pekerja $pekerja)
    {
        return view('pekerja.edit', compact('pekerja'));
    }

    public function update(Request $request, Pekerja $pekerja)
    {
        $pekerja->update($this->validasi($request));

        return redirect()->route('pekerja.index')
            ->with('sukses', 'Data pekerja diperbarui.');
    }

    public function destroy(Pekerja $pekerja)
    {
        $pekerja->delete();

        return redirect()->route('pekerja.index')
            ->with('sukses', 'Pekerja dihapus.');
    }

    private function validasi(Request $request): array
    {
        $data = $request->validate([
            'nama'    => ['required', 'string', 'max:20'],
            'jabatan' => ['required', Rule::in(['Tukang', 'Kenek'])],
        ], [
            'nama.required'    => 'Nama wajib diisi.',
            'nama.max'         => 'Nama maksimal 20 karakter.',
            'jabatan.required' => 'Jabatan wajib dipilih.',
            'jabatan.in'       => 'Jabatan tidak valid.',
        ]);

        $data['nama'] = trim($data['nama']);

        return $data;
    }
}