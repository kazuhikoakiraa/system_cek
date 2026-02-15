<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MAudit extends Model
{
    use HasFactory;

    protected $table = 'm_audits';

    protected $fillable = [
        'mesin_id',
        'm_request_id',
        'm_log_id',
        'action_type',
        'user_id',
        'deskripsi_perubahan',
        'perubahan_data',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'perubahan_data' => 'json',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function mRequest(): BelongsTo
    {
        return $this->belongsTo(MRequest::class, 'm_request_id');
    }

    public function mLog(): BelongsTo
    {
        return $this->belongsTo(MLog::class, 'm_log_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
