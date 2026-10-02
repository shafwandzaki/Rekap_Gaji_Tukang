<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proyek extends Model
{
    protected $fillable = ['nama'];

    public function rekaps(): HasMany
    {
        return $this->hasMany(Rekap::class);
    }
}