<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesin extends Model
{
    use HasFactory;

    protected $table = 'mesins';

    protected $fillable = [
        'kode_mesin',
        'serial_number',
        'nama_mesin',
        'manufacturer',
        'model_number',
        'tahun_pembuatan',
        'jenis_mesin',
        'lokasi_instalasi',
        'supplier',
        'tanggal_pengadaan',
        'harga_pengadaan',
        'nomor_invoice',
        'tanggal_waranty_expired',
        'umur_ekonomis_bulan',
        'estimasi_penggantian',
        'status',
        'kondisi_terakhir',
        'spesifikasi_teknis',
        'foto',
        'dokumen_pendukung',
        'user_id',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'datetime',
        'tanggal_waranty_expired' => 'datetime',
        'estimasi_penggantian' => 'datetime',
        'harga_pengadaan' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pemilik(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function komponens(): HasMany
    {
        return $this->hasMany(MComponent::class, 'mesin_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MRequest::class, 'mesin_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(MAudit::class, 'mesin_id');
    }
}


