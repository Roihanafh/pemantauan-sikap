<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sekolah extends Model
{
    use HasFactory;

    protected $table = 'sekolah';

    protected $fillable = [
        'nama',
    ];

    /**
     * Sekolah has many Guru
     */
    public function guru(): HasMany
    {
        return $this->hasMany(Guru::class);
    }

    /**
     * Sekolah has many Kelas
     */
    public function kelas(): HasMany
    {
        return $this->hasMany(Kelas::class);
    }
}
