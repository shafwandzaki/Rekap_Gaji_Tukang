<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pekerja extends Model
{
    protected $fillable = ['nama', 'jabatan', 'aktif'];

    protected $casts = [
        'aktif' => 'boolean',
    ];

    public function rekapItems(): HasMany
    {
        return $this->hasMany(RekapItem::class);
    }
}