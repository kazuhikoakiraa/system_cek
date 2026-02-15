<?php

namespace App\Observers;

use App\Models\DaftarPengecekan;
use Illuminate\Validation\ValidationException;

class DaftarPengecekanObserver
{
    /**
     * Handle the DaftarPengecekan "creating" event.
     * Validasi sebelum daftar pengecekan dibuat
     */
    public function creating(DaftarPengecekan $daftarPengecekan): void
    {
        if ($daftarPengecekan->user_id) {
            $this->validateOperatorAvailability($daftarPengecekan->user_id, null);
        }
    }

    /**
     * Handle the DaftarPengecekan "updating" event.
     * Validasi sebelum daftar pengecekan diupdate
     */
    public function updating(DaftarPengecekan $daftarPengecekan): void
    {
        // Hanya validasi jika user_id berubah
        if ($daftarPengecekan->isDirty('user_id') && $daftarPengecekan->user_id) {
            $this->validateOperatorAvailability($daftarPengecekan->user_id, $daftarPengecekan->id);
        }
    }

    /**
     * Validasi apakah operator sudah ditugaskan ke daftar pengecekan lain
     * 
     * @param int $userId ID operator yang akan ditugaskan
     * @param int|null $currentDaftarPengecekanId ID daftar pengecekan saat ini (untuk kasus update)
     * @throws ValidationException
     */
    private function validateOperatorAvailability(int $userId, ?int $currentDaftarPengecekanId): void
    {
        $existingAssignment = DaftarPengecekan::where('user_id', $userId)
            ->when($currentDaftarPengecekanId, function ($query) use ($currentDaftarPengecekanId) {
                return $query->where('id', '!=', $currentDaftarPengecekanId);
            })
            ->first();

        if ($existingAssignment) {
            throw ValidationException::withMessages([
                'user_id' => 'Operator ini sudah bertanggung jawab atas daftar pengecekan: ' . $existingAssignment->nama_mesin . '. Satu operator hanya boleh bertanggung jawab atas 1 daftar pengecekan.',
            ]);
        }
    }
}
