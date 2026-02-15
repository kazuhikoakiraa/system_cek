<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MaintenanceReport extends Model
{
    use HasFactory;

    protected $fillable = [
        'detail_pengecekan_mesin_id',
        'mesin_id',
        'komponen_mesin_id',
        'issue_description',
        'status',
        'foto_sebelum',
        'foto_sesudah',
        'catatan_teknisi',
        'teknisi_id',
        'tanggal_mulai',
        'tanggal_selesai',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
    ];

    public function detailPengecekanMesin(): BelongsTo
    {
        return $this->belongsTo(DetailPengecekanMesin::class);
    }

    public function mesin(): BelongsTo
    {
        return $this->belongsTo(DaftarPengecekan::class);
    }

    public function komponenMesin(): BelongsTo
    {
        return $this->belongsTo(KomponenMesin::class);
    }

    public function teknisi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teknisi_id');
    }

    public function spareParts(): BelongsToMany
    {
        return $this->belongsToMany(SparePart::class, 'maintenance_report_spare_part')
            ->withPivot(['jumlah_digunakan', 'catatan'])
            ->withTimestamps();
    }

    /**
     * Get all spare part transactions related to this maintenance report
     */
    public function sparePartTransactions(): MorphMany
    {
        return $this->morphMany(SparePartTransaction::class, 'reference');
    }
}
