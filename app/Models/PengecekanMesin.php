<?php

namespace App\Models;

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
     * Prevent deletion of pengecekan records
     */
    public function canDelete(): bool
    {
        return false;
    }
}
