<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Model PengecekanMesin
 * 
 * PENTING: Field user_id menyimpan operator yang MELAKUKAN PENGECEKAN, bukan operator yang ditugaskan ke daftar pengecekan.
 * - user_id di tabel pengecekan_mesins = Operator yang melakukan pengecekan (authuser saat pengecekan dibuat)
 * - user_id di tabel mesins (daftar_pengecekan) = Operator yang ditugaskan/bertanggung jawab pada daftar pengecekan
 * 
 * Ketika operator daftar pengecekan diganti, user_id di pengecekan TIDAK BERUBAH karena ini adalah data historis.
 * Data pengecekan harus tetap menunjukkan siapa yang benar-benar melakukan pengecekan tersebut.
 */
class PengecekanMesin extends Model
{
    use HasFactory;

    protected $fillable = [
        'mesin_id',
        'user_id',
        'tanggal_pengecekan',
        'status',
    ];

    protected $casts = [
        'tanggal_pengecekan' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(DaftarPengecekan::class);
    }

    /**
     * Alias untuk backward compatibility - gunakan daftarPengecekan() untuk yang baru
     */
    public function daftarPengecekan(): BelongsTo
    {
        return $this->belongsTo(DaftarPengecekan::class, 'mesin_id');
    }

    /**
     * Relasi ke User (Operator yang melakukan pengecekan)
     * 
     * CATATAN: Ini adalah operator yang MELAKUKAN pengecekan, bukan operator yang ditugaskan ke daftar pengecekan.
     * Data ini tidak akan berubah meskipun operator daftar pengecekan diganti.
     */
    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detailPengecekan(): HasMany
    {
        return $this->hasMany(DetailPengecekanMesin::class);
    }

    /**
     * Scope untuk membatasi data hanya 1 tahun terakhir
     * Digunakan untuk tampilan di tabel sistem
     */
    public function scopeWithinCurrentYear(Builder $query): Builder
    {
        return $query->where('tanggal_pengecekan', '>=', now()->startOfYear());
    }

    /**
     * Scope untuk filter berdasarkan rentang tanggal
     */
    public function scopeDateRange(Builder $query, $startDate, $endDate): Builder
    {
        return $query->whereBetween('tanggal_pengecekan', [$startDate, $endDate]);
    }

    /**
     * Prevent deletion of pengecekan records
     */
    public function canDelete(): bool
    {
        return false;
    }
}
