<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class SparePart extends Model
{
    use HasFactory;

    protected $fillable = [
        'kode_suku_cadang',
        'nama_suku_cadang',
        'deskripsi',
        'stok',
        'satuan',
    ];

    public function maintenanceReports(): BelongsToMany
    {
        return $this->belongsToMany(MaintenanceReport::class, 'maintenance_report_spare_part')
            ->withPivot(['jumlah_digunakan', 'catatan'])
            ->withTimestamps();
    }
}
