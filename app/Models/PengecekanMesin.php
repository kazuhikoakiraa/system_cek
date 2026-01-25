<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

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
        return $this->belongsTo(Mesin::class);
    }

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
