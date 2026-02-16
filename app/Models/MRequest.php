<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;

class MRequest extends Model
{
    use HasFactory;

    public const ACTIVE_MACHINE_STATUSES = ['pending', 'approved', 'in_progress'];

    protected $table = 'm_requests';

    protected $fillable = [
        'request_number',
        'mesin_id',
        'komponen_id',
        'created_by',
        'requested_at',
        'problema_deskripsi',
        'urgency_level',
        'status',
        'approved_by',
        'approved_at',
        'approval_notes',
        'rejected_at',
        'rejection_reason',
    ];

    protected $casts = [
        'requested_at' => 'datetime',
        'approved_at' => 'datetime',
        'rejected_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(Mesin::class, 'mesin_id');
    }

    public function komponen(): BelongsTo
    {
        return $this->belongsTo(MComponent::class, 'komponen_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function logs(): HasMany
    {
        return $this->hasMany(MLog::class, 'm_request_id');
    }

    public function audits(): HasMany
    {
        return $this->hasMany(MAudit::class, 'm_request_id');
    }

    public function scopeActiveForMachineStatus(Builder $query): Builder
    {
        return $query->whereIn('status', self::ACTIVE_MACHINE_STATUSES);
    }

    public static function syncMachineStatus(int $mesinId): void
    {
        $mesin = Mesin::query()->find($mesinId);

        if (! $mesin) {
            return;
        }

        $hasActiveRequest = static::query()
            ->where('mesin_id', $mesinId)
            ->activeForMachineStatus()
            ->exists();

        $targetStatus = $hasActiveRequest ? 'maintenance' : 'aktif';

        if ($mesin->status !== $targetStatus) {
            $mesin->update(['status' => $targetStatus]);
        }
    }
}
