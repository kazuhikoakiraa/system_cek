<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class MLog extends Model
{
    use HasFactory;

    protected $table = 'm_logs';

    protected $fillable = [
        'm_request_id',
        'teknisi_id',
        'tanggal_mulai',
        'tanggal_selesai',
        'catatan_teknisi',
        'foto_sebelum',
        'foto_sesudah',
        'biaya_service',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function request(): BelongsTo
    {
        return $this->belongsTo(MRequest::class, 'm_request_id');
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'm_log_spare_parts', 'm_log_id', 'spare_part_id')
            ->withPivot('jumlah_digunakan', 'harga_satuan', 'catatan')
            ->withTimestamps();
    }

    public function audits(): HasMany
    {
        return $this->hasMany(MAudit::class, 'm_log_id');
    }
}
