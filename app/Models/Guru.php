<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Guru extends Model
{
    use HasFactory;

    protected $table = 'guru';

    protected $fillable = [
        'user_id',
        'sekolah_id',
    ];

    protected $casts = [
        'user_id' => 'integer',
        'sekolah_id' => 'integer',
    ];

    /**
     * Guru belongs to User
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Guru belongs to Sekolah
     */
    public function sekolah(): BelongsTo
    {
        return $this->belongsTo(Sekolah::class);
    }

    /**
     * Guru has many Kelas (many-to-many)
     */
    public function kelas(): BelongsToMany
    {
        return $this->belongsToMany(Kelas::class, 'guru_kelas');
    }
}
