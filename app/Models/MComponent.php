<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MComponent extends Model
{
    use HasFactory;

    protected $table = 'm_components';

    protected $fillable = [
        'mesin_id',
        'nama_komponen',
        'manufacturer',
        'part_number',
        'tanggal_pengadaan',
        'spesifikasi_teknis',
        'jadwal_ganti_bulan',
        'tanggal_perawatan_terakhir',
        'estimasi_tanggal_ganti_berikutnya',
        'nama_supplier',
        'harga_komponen',
        'status_komponen',
        'stok_minimal',
        'jumlah_terpasang',
        'lokasi_pemasangan',
        'catatan',
    ];

    protected $casts = [
        'tanggal_pengadaan' => 'datetime',
        'tanggal_perawatan_terakhir' => 'datetime',
        'estimasi_tanggal_ganti_berikutnya' => 'datetime',
        'harga_komponen' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function requests(): HasMany
    {
        return $this->hasMany(MRequest::class, 'komponen_id');
    }
}
