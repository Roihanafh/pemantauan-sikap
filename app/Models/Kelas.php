<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Kelas extends Model
{
    use HasFactory;

    protected $table = 'kelas';

    protected $fillable = [
        'nama',
        'sekolah_id',
    ];

    protected $casts = [
        'sekolah_id' => 'integer',
    ];

    /**
     * Kelas belongs to Sekolah
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Kelas has many Siswa
     */
    public function siswa(): HasMany
    {
        return $this->hasMany(Siswa::class);
    }

    /**
     * Kelas has many Guru (many-to-many)
     */
    public function guru(): BelongsToMany
    {
        return $this->belongsToMany(Guru::class, 'guru_kelas');
    }
}
