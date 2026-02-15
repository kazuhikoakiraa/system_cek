<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_suku_cadang',
        'nama_suku_cadang',
        'category_id',
        'deskripsi',
        'stok',
        'stok_minimum',
        'stok_maksimum',
        'lokasi_penyimpanan',
        'satuan',
        'harga_satuan',
        'batch_number',
        'serial_number',
        'supplier',
        'tanggal_pengadaan',
        'tahun_pengadaan',
        'tanggal_warranty_mulai',
        'tanggal_warranty_expired',
        'warranty_bulan',
        'status',
        'foto',
        'spesifikasi_teknis',
        'part_number',
        'manufacturer',
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'date',
        'tanggal_warranty_mulai' => 'date',
        'tanggal_warranty_expired' => 'date',
        'harga_satuan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relasi ke Kategori
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(SparePartCategory::class, 'category_id');
    }

    /**
     * Relasi ke Maintenance Reports
     */
    public function maintenanceReports(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceReport::class, 'maintenance_report_spare_part')
            ->withPivot(['jumlah_digunakan', 'catatan'])
            ->withTimestamps();
    }

    /**
     * Relasi ke Transaksi (Log Keluar Masuk)
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(SparePartTransaction::class);
    }

    /**
     * Relasi ke Stock Opname Details
     */
    public function stockOpnameDetails(): HasMany
    {
        return $this->hasMany(SparePartStockOpnameDetail::class);
    }

    /**
     * Cek apakah stok di bawah minimum (Low Stock Alert)
     */
    public function isLowStock(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }

    /**
     * Cek apakah stok habis
     */
    public function isOutOfStock(): bool
    {
        return $this->stok <= 0;
    }

    /**
     * Cek apakah warranty masih aktif
     */
    public function isWarrantyActive(): bool
    {
        if (!$this->tanggal_warranty_expired) {
            return false;
        }
        return now()->lte($this->tanggal_warranty_expired);
    }

    /**
     * Cek apakah warranty akan expired dalam X hari
     */
    public function isWarrantyExpiringSoon(int $days = 30): bool
    {
        if (!$this->tanggal_warranty_expired) {
            return false;
        }
        return now()->diffInDays($this->tanggal_warranty_expired, false) <= $days 
            && now()->lte($this->tanggal_warranty_expired);
    }

    /**
     * Get total nilai stok (stok * harga_satuan)
     */
    public function getNilaiStokAttribute(): float
    {
        return $this->stok * ($this->harga_satuan ?? 0);
    }

    /**
     * Get status stok (badge color purpose)
     */
    public function getStatusStokAttribute(): string
    {
        if ($this->isOutOfStock()) {
            return 'out_of_stock';
        } elseif ($this->isLowStock()) {
            return 'low_stock';
        } elseif ($this->stok >= $this->stok_maksimum) {
            return 'over_stock';
        }
        return 'normal';
    }

    /**
     * Scope untuk filter stok rendah
     */
    public function scopeLowStock($query)
    {
        return $query->whereRaw('stok <= stok_minimum');
    }

    /**
     * Scope untuk filter stok habis
     */
    public function scopeOutOfStock($query)
    {
        return $query->where('stok', '<=', 0);
    }

    /**
     * Scope untuk filter warranty aktif
     */
    public function scopeWarrantyActive($query)
    {
        return $query->whereNotNull('tanggal_warranty_expired')
            ->where('tanggal_warranty_expired', '>=', now());
    }

    /**
     * Scope untuk filter warranty akan expired
     */
    public function scopeWarrantyExpiringSoon($query, int $days = 30)
    {
        return $query->whereNotNull('tanggal_warranty_expired')
            ->whereBetween('tanggal_warranty_expired', [now(), now()->addDays($days)]);
    }
}
