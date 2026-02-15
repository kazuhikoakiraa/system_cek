<?php

namespace App\Observers;

use App\Models\Mesin;
use Illuminate\Validation\ValidationException;

class MesinObserver
{
    /**
     * Handle the Mesin "creating" event.
     * Validasi sebelum mesin dibuat
     */
    public function creating(Mesin $mesin): void
    {
        if ($mesin->user_id) {
            $this->validateOperatorAvailability($mesin->user_id, null);
        }
    }

    /**
     * Handle the Mesin "updating" event.
     * Validasi sebelum mesin diupdate
     */
    public function updating(Mesin $mesin): void
    {
        // Hanya validasi jika user_id berubah
        if ($mesin->isDirty('user_id') && $mesin->user_id) {
            $this->validateOperatorAvailability($mesin->user_id, $mesin->id);
        }
    }

    /**
     * Validasi apakah operator sudah ditugaskan ke mesin lain
     * 
     * @param int $userId ID operator yang akan ditugaskan
     * @param int|null $currentMesinId ID mesin saat ini (untuk kasus update)
     * @throws ValidationException
     */
    private function validateOperatorAvailability(int $userId, ?int $currentMesinId): void
    {
        $existingAssignment = Mesin::where('user_id', $userId)
            ->when($currentMesinId, function ($query) use ($currentMesinId) {
                return $query->where('id', '!=', $currentMesinId);
            })
            ->first();

        if ($existingAssignment) {
            throw ValidationException::withMessages([
                'user_id' => 'Operator ini sudah bertanggung jawab atas daftar pengecekan: ' . $existingAssignment->nama_mesin . '. Satu operator hanya boleh bertanggung jawab atas 1 daftar pengecekan.',
            ]);
        }
    }
}
