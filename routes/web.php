<?php

use App\Http\Controllers\LaporanExportController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

// Home route
Route::get('/', function () {
    return redirect('/admin');
});

// Laporan Export Routes
Route::middleware(['auth'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/export-pdf', [LaporanExportController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/export-excel', [LaporanExportController::class, 'exportExcel'])->name('export-excel');
});

// Email Verification Handler - Tidak butuh auth karena signed URL
Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);
    
    // Verify hash matches
    if (!hash_equals($hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }
    
    // Mark email as verified
    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();
    }
    
    return redirect('/admin/login')->with('success', 'Email Anda berhasil diverifikasi! Silakan login.');
})->middleware(['signed', 'throttle:6,1'])->name('verification.verify');

// Email Verification Routes (butuh auth)
Route::middleware('auth')->group(function () {
    
    // Email verification notice
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // Resend verification email
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Link verifikasi telah dikirim ulang!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});