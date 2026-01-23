<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Mesin extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama_mesin',
        'user_id',
        'deskripsi',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function operator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function komponenMesins(): HasMany
    {
        return $this->hasMany(KomponenMesin::class);
    }
}
