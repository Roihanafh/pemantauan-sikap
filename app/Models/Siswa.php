<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'nama',
        'gender',
        'kelas_id',
    ];

    /**
     * Siswa belongs to Kelas
     */
    public function kelas(): BelongsTo
    {
        return $this->belongsTo(Kelas::class);
    }

    /**
     * Siswa has many Penilaian
     */
    public function penilaian(): HasMany
    {
        return $this->hasMany(Penilaian::class);
    }

    /**
     * Fat Model Methods
     */

    /**
     * Dapatkan penilaian siswa untuk pertemuan tertentu
     * 
     * @param int $pertemuan
     * @return Penilaian|null
     */
    public function getPenilaianPertemuan($pertemuan)
    {
        return $this->penilaian()
            ->where('pertemuan', $pertemuan)
            ->first();
    }

    /**
     * Hitung rata-rata semua nilai siswa
     * 
     * @return float
     */
    public function hitungRataPenilaian()
    {
        $penilaian = $this->penilaian()->get();
        
        if ($penilaian->isEmpty()) {
            return 0;
        }

        $totalSemua = 0;
        $totalKategori = 0;

        foreach ($penilaian as $p) {
            $totalSemua += $p->respect + $p->participation + $p->self_direction + $p->caring + $p->transfer;
            $totalKategori += 5;
        }

        return round($totalSemua / $totalKategori, 2);
    }

    /**
     * Cek apakah siswa sudah dinilai di pertemuan tertentu
     * 
     * @param int $pertemuan
     * @return bool
     */
    public function sudahDinilaiPertemuan($pertemuan): bool
    {
        return $this->penilaian()
            ->where('pertemuan', $pertemuan)
            ->exists();
    }
}
