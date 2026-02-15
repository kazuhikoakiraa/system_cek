<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model DaftarPengecekan
 * CATATAN: Ini adalah daftar item/mesin yang akan dicek
 * 
 * CATATAN: Field user_id menyimpan operator yang DITUGASKAN/BERTANGGUNG JAWAB pada daftar pengecekan.
 * - user_id di tabel daftar_pengecekan = Operator yang ditugaskan pada daftar pengecekan (bisa berubah, bisa NULL)
 * - user_id di tabel pengecekan_mesins = Operator yang melakukan pengecekan (tidak berubah, data historis)
 * - Ketika operator dihapus, user_id di daftar_pengecekan menjadi NULL (daftar tidak punya operator)
 */
class DaftarPengecekan extends Model
{
    use HasFactory;

    protected $table = 'daftar_pengecekan';

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
     * Relasi ke User (Operator yang ditugaskan pada daftar pengecekan)
     * 
     * CATATAN: Ini adalah operator yang DITUGASKAN pada daftar pengecekan, bisa berubah sewaktu-waktu, bisa NULL jika operator dihapus.
     * Operator yang melakukan pengecekan tersimpan di tabel pengecekan_mesins.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function komponenDaftarPengecekan(): HasMany
    {
        return $this->hasMany(KomponenDaftarPengecekan::class, 'mesin_id');
    }

    public function komponenMesins(): HasMany
    {
        return $this->hasMany(KomponenDaftarPengecekan::class, 'mesin_id');
    }

    // Alias untuk backward compatibility
    public function komponenMesin(): HasMany
    {
        return $this->hasMany(KomponenDaftarPengecekan::class, 'mesin_id');
    }

    public function pengecekan(): HasMany
    {
        return $this->hasMany(PengecekanMesin::class, 'mesin_id');
    }
}
