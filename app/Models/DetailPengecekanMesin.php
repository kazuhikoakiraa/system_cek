<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DetailPengecekanMesin extends Model
{
    use HasFactory;

    protected $fillable = [
        'pengecekan_mesin_id',
        'komponen_mesin_id',
        'status_sesuai',
        'keterangan',
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
}
