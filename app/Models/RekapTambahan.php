<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RekapTambahan extends Model
{
    protected $fillable = ['rekap_id', 'keterangan', 'nominal', 'tipe'];

    public function rekap(): BelongsTo
    {
        return $this->belongsTo(Rekap::class);
    }
}