<?php

namespace App\Observers;

use App\Models\MRequest;
use App\Models\MAudit;
use App\Models\User;
use App\Notifications\MaintenanceRequestCreated;
use App\Notifications\MaintenanceRequestApproved;
use App\Notifications\MaintenanceRequestRejected;
use Illuminate\Support\Facades\Notification;

class MRequestObserver
{
    /**
     * Handle the MRequest "created" event.
     */
    public function created(MRequest $mRequest): void
    {
        // Record audit trail
        MAudit::create([
            'mesin_id' => $mRequest->mesin_id,
            'm_request_id' => $mRequest->id,
            'action_type' => 'request_created',
            'user_id' => $mRequest->created_by,
            'deskripsi_perubahan' => "Request maintenance dibuat: {$mRequest->request_number}",
            'perubahan_data' => [
                'mesin' => $mRequest->mesin->nama_mesin ?? null,
                'urgency' => $mRequest->urgency_level,
                'deskripsi' => $mRequest->problema_deskripsi,
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Notifikasi ke Admin & Super Admin
        $admins = User::role(['super_admin', 'panel_user'])->get();
        Notification::send($admins, new MaintenanceRequestCreated($mRequest));
    }

    /**
     * Handle the MRequest "updated" event.
     */
    public function updated(MRequest $mRequest): void
    {
        // Check if status approved
        if ($mRequest->isDirty('status') && $mRequest->status === 'approved') {
            // Record audit trail
            MAudit::create([
                'mesin_id' => $mRequest->mesin_id,
                'm_request_id' => $mRequest->id,
                'action_type' => 'admin_approved',
                'user_id' => $mRequest->approved_by,
                'deskripsi_perubahan' => "Request {$mRequest->request_number} disetujui",
                'perubahan_data' => [
                    'approved_by' => $mRequest->approver->name ?? null,
                    'approved_at' => $mRequest->approved_at,
                    'notes' => $mRequest->approval_notes,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notifikasi ke Teknisi (role teknisi)
            $teknisi = User::role('teknisi')->get();
            Notification::send($teknisi, new MaintenanceRequestApproved($mRequest));
        }

        // Check if status rejected
        if ($mRequest->isDirty('status') && $mRequest->status === 'rejected') {
            // Record audit trail
            MAudit::create([
                'mesin_id' => $mRequest->mesin_id,
                'm_request_id' => $mRequest->id,
                'action_type' => 'admin_rejected',
                'user_id' => $mRequest->approved_by,
                'deskripsi_perubahan' => "Request {$mRequest->request_number} ditolak",
                'perubahan_data' => [
                    'rejected_by' => $mRequest->approver->name ?? null,
                    'rejected_at' => $mRequest->rejected_at,
                    'reason' => $mRequest->rejection_reason,
                ],
                'ip_address' => request()->ip(),
                'user_agent' => request()->userAgent(),
            ]);

            // Notifikasi ke creator (operator yang buat request)
            $creator = $mRequest->creator;
            if ($creator) {
                $creator->notify(new MaintenanceRequestRejected($mRequest));
            }
        }
    }

    /**
     * Handle the MRequest "deleted" event.
     */
    public function deleted(MRequest $mRequest): void
    {
        //
    }

    /**
     * Handle the MRequest "restored" event.
     */
    public function restored(MRequest $mRequest): void
    {
        //
    }

    /**
     * Handle the MRequest "force deleted" event.
     */
    public function forceDeleted(MRequest $mRequest): void
    {
        //
    }
}
