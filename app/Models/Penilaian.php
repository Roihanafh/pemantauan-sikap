<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Penilaian extends Model
{
    use HasFactory;

    protected $table = 'penilaian';

    protected $fillable = [
        'siswa_id',
        'pertemuan',
        'respect',
        'participation',
        'self_direction',
        'caring',
        'transfer',
    ];

    protected $casts = [
        'siswa_id' => 'integer',
        'pertemuan' => 'integer',
        'respect' => 'integer',
        'participation' => 'integer',
        'self_direction' => 'integer',
        'caring' => 'integer',
        'transfer' => 'integer',
    ];

    /**
     * Penilaian belongs to Siswa
     */
    public function siswa(): BelongsTo
    {
        return $this->belongsTo(Siswa::class);
    }

    /**
     * Fat Model Methods untuk business logic
     */

    /**
     * Dapatkan semua siswa dari kelas tertentu dengan penilaian mereka
     * 
     * @param int $kelasId
     * @param int $pertemuan
     * @param int|null $sekolahId (opsional untuk filtering by sekolah)
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSiswaWithPenilaianByKelas($kelasId, $pertemuan = null, $sekolahId = null)
    {
        $query = Siswa::where('kelas_id', $kelasId);

        // Filter by school if provided
        if ($sekolahId) {
            $query->whereHas('kelas', function ($q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            });
        }

        return $query->with(['penilaian' => function ($query) use ($pertemuan) {
                if ($pertemuan) {
                    $query->where('pertemuan', $pertemuan);
                }
            }])
            ->get();
    }

    /**
     * Dapatkan semua siswa dari sekolah tertentu dengan penilaian mereka
     * 
     * @param int $sekolahId
     * @param int $pertemuan
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getSiswaWithPenilaianBySekolah($sekolahId, $pertemuan = null)
    {
        return Siswa::whereHas('kelas', function ($q) use ($sekolahId) {
                $q->where('sekolah_id', $sekolahId);
            })
            ->with(['penilaian' => function ($query) use ($pertemuan) {
                if ($pertemuan) {
                    $query->where('pertemuan', $pertemuan);
                }
            }])
            ->get();
    }

    /**
     * Dapatkan penilaian siswa untuk pertemuan tertentu
     * 
     * @param int $siswaId
     * @param int $pertemuan
     * @return self|null
     */
    public static function getPenilaianByPertemuan($siswaId, $pertemuan)
    {
        return self::where('siswa_id', $siswaId)
            ->where('pertemuan', $pertemuan)
            ->first();
    }

    /**
     * Hitung rata-rata nilai semua kategori (skala 1-4)
     * 
     * @return array
     */
    public function hitungRataNilai(): array
    {
        $rataRata = ($this->respect + $this->participation + $this->self_direction + $this->caring + $this->transfer) / 5;
        
        return [
            'respect' => $this->respect,
            'participation' => $this->participation,
            'self_direction' => $this->self_direction,
            'caring' => $this->caring,
            'transfer' => $this->transfer,
            'rata_rata' => round($rataRata, 2),
            'predikat' => $this->getPredikat($rataRata),
        ];
    }

    /**
     * Dapatkan predikat berdasarkan rata-rata nilai
     * 
     * @param float $rataRata
     * @return string
     */
    public function getPredikat($rataRata): string
    {
        if ($rataRata >= 3.5) {
            return 'Sangat Baik';
        } elseif ($rataRata >= 2.5) {
            return 'Baik';
        } elseif ($rataRata >= 1.5) {
            return 'Cukup';
        } else {
            return 'Kurang';
        }
    }

    /**
     * Simpan atau update penilaian
     * 
     * @param int $siswaId
     * @param int $pertemuan
     * @param array $dataPenilaian
     * @return self
     */
    public static function simpanPenilaian($siswaId, $pertemuan, array $dataPenilaian)
    {
        return self::updateOrCreate(
            [
                'siswa_id' => $siswaId,
                'pertemuan' => $pertemuan,
            ],
            $dataPenilaian
        );
    }

    /**
     * Dapatkan penilaian dalam format untuk DataTables
     * 
     * @param int $kelasId
     * @param int $pertemuan
     * @return array
     */
    public static function getPenilaianDataTables($kelasId, $pertemuan)
    {
        $siswas = self::getSiswaWithPenilaianByKelas($kelasId, $pertemuan);
        
        return $siswas->map(function ($siswa) use ($pertemuan) {
            $penilaian = $siswa->penilaian->first();
            
            return [
                'id' => $siswa->id,
                'nama' => $siswa->nama,
                'gender' => $siswa->gender,
                'respect' => $penilaian?->respect ?? 0,
                'participation' => $penilaian?->participation ?? 0,
                'self_direction' => $penilaian?->self_direction ?? 0,
                'caring' => $penilaian?->caring ?? 0,
                'transfer' => $penilaian?->transfer ?? 0,
                'penilaian_id' => $penilaian?->id,
            ];
        })->toArray();
    }

    /**
     * Validasi data penilaian (skala 1-4)
     * 
     * @param array $data
     * @return array
     */
    public static function validasiData(array $data): array
    {
        return [
            'siswa_id' => 'required|integer|exists:siswa,id',
            'pertemuan' => 'required|integer|min:1|max:16',
            'respect' => 'required|integer|min:1|max:4',
            'participation' => 'required|integer|min:1|max:4',
            'self_direction' => 'required|integer|min:1|max:4',
            'caring' => 'required|integer|min:1|max:4',
            'transfer' => 'required|integer|min:1|max:4',
        ];
    }
}
