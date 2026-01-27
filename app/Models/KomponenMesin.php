<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

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

    public function detailPengecekan(): HasMany
    {
        return $this->hasMany(DetailPengecekanMesin::class);
    }

    /**
     * Cek apakah komponen sudah waktunya untuk dicek berdasarkan frekuensi
     */
    public function isCheckable(): bool
    {
        // Jika harian, selalu bisa dicek
        if ($this->frekuensi === 'harian') {
            return true;
        }

        // Ambil pengecekan terakhir untuk komponen ini
        $lastCheck = $this->detailPengecekan()
            ->with('pengecekanMesin')
            ->whereHas('pengecekanMesin')
            ->latest('created_at')
            ->first();

        // Jika belum pernah dicek, bisa dicek
        if (!$lastCheck) {
            return true;
        }

        $lastCheckDate = $lastCheck->pengecekanMesin->tanggal_pengecekan;
        $now = Carbon::now();

        // Cek berdasarkan frekuensi
        if ($this->frekuensi === 'mingguan') {
            // Bisa dicek jika sudah lewat 7 hari
            return $lastCheckDate->diffInDays($now) >= 7;
        }

        if ($this->frekuensi === 'bulanan') {
            // Bisa dicek jika sudah lewat 1 bulan
            return $lastCheckDate->diffInDays($now) >= 30;
        }

        return true;
    }

    /**
     * Dapatkan detail pengecekan terakhir
     */
    public function getLastCheck()
    {
        return $this->detailPengecekan()
            ->with('pengecekanMesin')
            ->whereHas('pengecekanMesin')
            ->latest('created_at')
            ->first();
    }

    /**
     * Dapatkan tanggal pengecekan berikutnya
     */
    public function getNextCheckDate(): ?Carbon
    {
        $lastCheck = $this->getLastCheck();
        
        if (!$lastCheck) {
            return null;
        }

        $lastCheckDate = $lastCheck->pengecekanMesin->tanggal_pengecekan;

        if ($this->frekuensi === 'mingguan') {
            return $lastCheckDate->copy()->addWeek();
        }

        if ($this->frekuensi === 'bulanan') {
            return $lastCheckDate->copy()->addMonth();
        }

        return null;
    }
}
