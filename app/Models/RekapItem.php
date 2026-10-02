<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapItem extends Model
{
    protected $fillable = [
        'rekap_id', 'pekerja_id', 'nama', 'jabatan', 'hari', 'gaji_harian', 'total',
    ];

    protected $casts = [
        'hari' => 'decimal:1',
    ];

    public function rekap(): BelongsTo
    {
        return $this->belongsTo(Rekap::class);
    }

    public function pekerja(): BelongsTo
    {
        return $this->belongsTo(Pekerja::class);
    }
}