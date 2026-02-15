<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SparePartStockOpnameDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'stock_opname_id',
        'spare_part_id',
        'stok_sistem',
        'stok_fisik',
        'selisih',
        'keterangan',
        'foto',
        'status_item',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-calculate selisih
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($detail) {
            $detail->selisih = $detail->stok_fisik - $detail->stok_sistem;
            
            // Auto-determine status item
            if ($detail->selisih == 0) {
                $detail->status_item = 'match';
            } elseif ($detail->selisih > 0) {
                $detail->status_item = 'over';
            } else {
                $detail->status_item = 'short';
            }
        });
    }

    /**
     * Relasi ke stock opname
     */
    public function stockOpname(): BelongsTo
    {
        return $this->belongsTo(SparePartStockOpname::class, 'stock_opname_id');
    }

    /**
     * Relasi ke spare part
     */
    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    /**
     * Cek apakah stok match
     */
    public function isMatch(): bool
    {
        return $this->status_item === 'match';
    }

    /**
     * Cek apakah stok lebih (over)
     */
    public function isOver(): bool
    {
        return $this->status_item === 'over';
    }

    /**
     * Cek apakah stok kurang (short)
     */
    public function isShort(): bool
    {
        return $this->status_item === 'short';
    }
}
