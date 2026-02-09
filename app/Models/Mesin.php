<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model Mesin
 * 
 * CATATAN: Field user_id menyimpan operator yang DITUGASKAN/BERTANGGUNG JAWAB pada mesin.
 * - user_id di tabel mesins = Operator yang ditugaskan pada mesin (bisa berubah, bisa NULL)
 * - user_id di tabel pengecekan_mesins = Operator yang melakukan pengecekan (tidak berubah, data historis)
 * - Ketika operator dihapus, user_id di mesins menjadi NULL (mesin tidak punya operator)
 */
class Mesin extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_mesin',
        'user_id',
        'deskripsi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke User (Operator yang ditugaskan pada mesin)
     * 
     * CATATAN: Ini adalah operator yang DITUGASKAN pada mesin, bisa berubah sewaktu-waktu, bisa NULL jika operator dihapus.
     * Operator yang melakukan pengecekan tersimpan di tabel pengecekan_mesins.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function komponenMesins(): HasMany
    {
        return $this->hasMany(KomponenMesin::class);
    }

    public function komponenMesin(): HasMany
    {
        return $this->hasMany(KomponenMesin::class);
    }

    public function pengecekan(): HasMany
    {
        return $this->hasMany(PengecekanMesin::class);
    }
}
