<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KomponenMesin extends Model
{
    use HasFactory;

    protected $fillable = [
        'mesin_id',
        'nama_komponen',
        'standar',
        'frekuensi',
        'catatan',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class);
    }
}
