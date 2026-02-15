<?php

use App\Http\Controllers\LaporanExportController;
use App\Http\Controllers\SparePartTransactionPdfController;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

// Home route
Route::get('/', function () {
    return redirect('/admin');
});

// Laporan Export Routes
Route::middleware(['auth'])->prefix('laporan')->name('laporan.')->group(function () {
    Route::get('/export-pdf', [LaporanExportController::class, 'exportPdf'])->name('export-pdf');
    Route::get('/export-excel', [LaporanExportController::class, 'exportExcel'])->name('export-excel');
});

// Spare Part Transaction Export
Route::middleware(['auth'])->group(function () {
    Route::get('/spare-part-transactions/pdf', [SparePartTransactionPdfController::class, 'download'])->name('spare-part-transactions.pdf');
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

// Password Reset Routes
Route::get('/forgot-password', function () {
    return view('auth.forgot-password');
})->middleware('guest')->name('password.request');

Route::post('/forgot-password', function (Request $request) {
    $request->validate(['email' => 'required|email']);

    $status = Password::sendResetLink(
        $request->only('email')
    );

    return $status === Password::RESET_LINK_SENT
        ? back()->with(['status' => __($status)])
        : back()->withErrors(['email' => __($status)]);
})->middleware('guest')->name('password.email');

Route::get('/reset-password/{token}', function (string $token) {
    return view('auth.reset-password', ['token' => $token]);
})->middleware('guest')->name('password.reset');

Route::post('/reset-password', function (Request $request) {
    $request->validate([
        'token' => 'required',
        'email' => 'required|email',
        'password' => 'required|min:8|confirmed',
    ]);

    $status = Password::reset(
        $request->only('email', 'password', 'password_confirmation', 'token'),
        function ($user, $password) {
            $user->forceFill([
                'password' => Hash::make($password)
            ])->setRememberToken(Str::random(60));

            $user->save();

            event(new PasswordReset($user));
        }
    );

    return $status === Password::PASSWORD_RESET
        ? redirect()->route('filament.admin.auth.login')->with('status', __($status))
        : back()->withErrors(['email' => [__($status)]]);
})->middleware('guest')->name('password.update');