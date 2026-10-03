<?php

namespace App\Exports;

use App\Models\Rekap;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class RekapExport implements FromArray, WithColumnWidths, WithEvents, WithTitle
{
    private array $baris = [];
    private int $akhirItem;        // nomor baris terakhir pekerja
    private int $awalTambahan;     // nomor baris pertama tambahan
    private int $barisTotal;       // nomor baris "Total"

    public function __construct(private Rekap $rekap)
    {
        $rekap->loadMissing('proyek', 'items', 'tambahans');

        // Baris 1-2: judul dan periode, baris 3: header tabel
        $this->baris[] = ['Proyek ' . $rekap->proyek->nama, '', '', '', ''];
        $this->baris[] = [$this->periode(), '', '', '', ''];
        $this->baris[] = ['Nama', 'Jabatan', 'Hari', 'Gaji', 'Total Gaji'];

        // Baris pekerja
        foreach ($rekap->items as $item) {
            $this->baris[] = [
                $item->nama,
                $item->jabatan,
                (float) $item->hari,
                $item->gaji_harian,
                $item->total,
            ];
        }
        $this->akhirItem    = count($this->baris);
        $this->awalTambahan = $this->akhirItem + 1;

        // Baris tambahan (Jasa Mandor, dll.)
        foreach ($rekap->tambahans as $t) {
            $this->baris[] = [
                $t->keterangan, '', '', '',
                $t->tipe === 'potong' ? -$t->nominal : $t->nominal,
            ];
        }

        // Baris total
        $this->baris[] = ['Total', '', '', '', $rekap->total];
        $this->barisTotal = count($this->baris);
    }

    public function array(): array
    {
        return $this->baris;
    }

    public function title(): string
    {
        return 'Rekap Gaji';
    }

    public function columnWidths(): array
    {
        return ['A' => 24, 'B' => 14, 'C' => 8, 'D' => 16, 'E' => 18];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $s    = $event->sheet->getDelegate();
                $last = $this->barisTotal;

                // Judul dan periode digabung selebar tabel
                $s->mergeCells('A1:E1');
                $s->mergeCells('A2:E2');
                $s->getStyle('A1:E2')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $s->getStyle('A1')->getFont()->setBold(true)->setSize(14);

                // Baris tambahan dan total: label digabung A sampai D
                for ($r = $this->awalTambahan; $r <= $last; $r++) {
                    $s->mergeCells("A{$r}:D{$r}");
                    $s->getStyle("A{$r}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Header tabel
                $s->getStyle('A3:E3')->getFont()->setBold(true);
                $s->getStyle('A3:E3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $s->getStyle('A3:E3')->getFill()
                    ->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('E7E6E6');

                // Isi pekerja: kolom nama sampai hari rata tengah
                $s->getStyle("A4:C{$this->akhirItem}")->getAlignment()
                    ->setHorizontal(Alignment::HORIZONTAL_CENTER);

                // Format rupiah (tampilan titik/koma mengikuti pengaturan Excel)
                $rupiah = '"Rp "#,##0;[Red]-"Rp "#,##0';
                $s->getStyle("D4:E{$last}")->getNumberFormat()->setFormatCode($rupiah);

                // Baris total tebal
                $s->getStyle("A{$last}:E{$last}")->getFont()->setBold(true);

                // Garis tepi seluruh tabel
                $s->getStyle("A1:E{$last}")->getBorders()->getAllBorders()
                    ->setBorderStyle(Border::BORDER_THIN);
            },
        ];
    }

    // "20/09 s/d 26/09/2026" atau "26/09/2026" kalau hanya satu hari
    private function periode(): string
    {
        $mulai   = $this->rekap->tanggal_mulai;
        $selesai = $this->rekap->tanggal_selesai;

        if ($mulai->isSameDay($selesai)) {
            return $selesai->format('d/m/Y');
        }

        return $mulai->format('d/m') . ' s/d ' . $selesai->format('d/m/Y');
    }
}