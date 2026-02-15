<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class SparePartTransaction extends Model
{
    use HasFactory;

    protected $fillable = [
        'nomor_transaksi',
        'spare_part_id',
        'tipe_transaksi',
        'tanggal_transaksi',
        'user_id',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'reference_type',
        'reference_id',
        'keterangan',
        'dokumen',
        'status_approval',
        'approved_by',
        'approved_at',
        'approval_notes',
    ];

    protected $casts = [
        'tanggal_transaksi' => 'datetime',
        'approved_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Boot method untuk auto-generate nomor transaksi
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($transaction) {
            if (empty($transaction->nomor_transaksi)) {
                $transaction->nomor_transaksi = self::generateNomorTransaksi();
            }
        });
    }

    /**
     * Generate nomor transaksi unik
     * Format: TRX-YYYYMMDD-XXXX
     */
    public static function generateNomorTransaksi(): string
    {
        $date = now()->format('Ymd');
        $prefix = 'TRX-' . $date . '-';
        
        $lastTransaction = self::where('nomor_transaksi', 'like', $prefix . '%')
            ->orderBy('nomor_transaksi', 'desc')
            ->first();

        if ($lastTransaction) {
            $lastNumber = (int) substr($lastTransaction->nomor_transaksi, -4);
            $newNumber = $lastNumber + 1;
        } else {
            $newNumber = 1;
        }

        return $prefix . str_pad($newNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Relasi ke SparePart
     */
    public function sparePart(): BelongsTo
    {
        return $this->belongsTo(SparePart::class);
    }

    /**
     * Relasi ke User (yang melakukan transaksi)
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
     * Polymorphic relation untuk reference
     */
    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Cek apakah transaksi tipe masuk (IN)
     */
    public function isIncoming(): bool
    {
        return in_array($this->tipe_transaksi, ['IN', 'RETURN']);
    }

    /**
     * Cek apakah transaksi tipe keluar (OUT)
     */
    public function isOutgoing(): bool
    {
        return $this->tipe_transaksi === 'OUT';
    }

    /**
     * Cek apakah sudah approved
     */
    public function isApproved(): bool
    {
        return $this->status_approval === 'approved';
    }

    /**
     * Cek apakah pending approval
     */
    public function isPending(): bool
    {
        return $this->status_approval === 'pending';
    }

    /**
     * Scope untuk filter berdasarkan tipe transaksi
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('tipe_transaksi', $type);
    }

    /**
     * Scope untuk filter approved transactions
     */
    public function scopeApproved($query)
    {
        return $query->where('status_approval', 'approved');
    }

    /**
     * Scope untuk filter pending transactions
     */
    public function scopePending($query)
    {
        return $query->where('status_approval', 'pending');
    }

    /**
     * Scope untuk filter berdasarkan periode
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('tanggal_transaksi', [$startDate, $endDate]);
    }
}
