<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DetailPengecekanMesin extends Model
{
    use HasFactory;

    protected $table = 'detail_pengecekan_daftar';

    protected $fillable = [
        'pengecekan_mesin_id',
        'komponen_mesin_id',
        'status_sesuai',
        'keterangan',
        'status_komponen', // Untuk backward compatibility jika ada
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function pengecekanMesin(): BelongsTo
    {
        return $this->belongsTo(PengecekanMesin::class);
    }

    public function komponenMesin(): BelongsTo
    {
        return $this->belongsTo(KomponenMesin::class);
    }

    public function maintenanceReports(): HasMany
    {
        return $this->hasMany(MaintenanceReport::class);
    }
}
