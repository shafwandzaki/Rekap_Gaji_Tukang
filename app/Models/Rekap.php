<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \Illuminate\Support\Carbon $tanggal_mulai
 * @property \Illuminate\Support\Carbon $tanggal_selesai
 */
class Rekap extends Model
{
    protected $fillable = [
        'proyek_id', 'tanggal_mulai', 'tanggal_selesai', 'total', 'catatan',
    ];

    protected $casts = [
        'tanggal_mulai'   => 'date',
        'tanggal_selesai' => 'date',
    ];

    public function proyek(): BelongsTo
    {
        return $this->belongsTo(Proyek::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RekapItem::class);
    }

    public function tambahans(): HasMany
    {
        return $this->hasMany(RekapTambahan::class);
    }
}