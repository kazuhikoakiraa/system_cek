<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePartStockOpname extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_opname',
        'tanggal_opname',
        'periode',
        'user_id',
        'status',
        'completed_at',
        'approved_by',
        'approved_at',
        'catatan',
        'dokumen',
    ];

    protected $casts = [
        'tanggal_opname' => 'date',
        'completed_at' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate nomor opname
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($opname) {
            if (empty($opname->nomor_opname)) {
                $opname->nomor_opname = self::generateNomorOpname();
            }
        });
    }

    /**
     * Generate nomor opname unik
     * Format: SO-YYYYMM-XXXX
     */
    public static function generateNomorOpname(): string
    {
        $yearMonth = now()->format('Ym');
        $prefix = 'SO-' . $yearMonth . '-';
        
        $lastOpname = self::where('nomor_opname', 'like', $prefix . '%')
            ->orderBy('nomor_opname', 'desc')
            ->first();

        if ($lastOpname) {
            $lastNumber = (int) substr($lastOpname->nomor_opname, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke User (yang melakukan opname)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi ke User (yang approve)
     */
    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    /**
     * Relasi ke detail opname
     */
    public function details(): HasMany
    {
        return $this->hasMany(SparePartStockOpnameDetail::class, 'stock_opname_id');
    }

    /**
     * Cek apakah masih draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Cek apakah sudah completed
     */
    public function isCompleted(): bool
    {
        return in_array($this->status, ['completed', 'approved']);
    }

    /**
     * Cek apakah sudah approved
     */
    public function isApproved(): bool
    {
        return $this->status === 'approved';
    }

    /**
     * Hitung total item yang diaudit
     */
    public function getTotalItemsAttribute(): int
    {
        return $this->details()->count();
    }

    /**
     * Hitung total item yang match
     */
    public function getTotalMatchAttribute(): int
    {
        return $this->details()->where('status_item', 'match')->count();
    }

    /**
     * Hitung total selisih (absolut)
     */
    public function getTotalSelisihAttribute(): int
    {
        return abs($this->details()->sum('selisih'));
    }
}
