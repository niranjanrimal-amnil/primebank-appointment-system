<?php

use Illuminate\Support\Facades\Route;
use AppointmentSystem\Controllers\OtpController;
use AppointmentSystem\Controllers\AppointmentController;
use AppointmentSystem\Controllers\ApiKeyController;

// API prefix: appointment-system
Route::prefix('appointment-system')->group(function () {
    
    // OTP Routes
    Route::prefix('otp')->group(function () {
        Route::post('generate', [OtpController::class, 'generate'])->name('appointment.otp.generate');
        Route::post('resend', [OtpController::class, 'resend'])->name('appointment.otp.resend');
        Route::post('verify', [OtpController::class, 'verify'])->name('appointment.otp.verify');
        Route::get('status/{accountNumber}', [OtpController::class, 'checkStatus'])->name('appointment.otp.status');
    });

    // Appointment Routes
    Route::prefix('appointments')->group(function () {
        // Get dropdown data
        Route::get('purposes/{purposeName?}', [AppointmentController::class, 'getPurposes'])->name('appointment.purposes');
        Route::get('locations/{purposeId}', [AppointmentController::class, 'getLocations'])->name('appointment.locations');
        Route::get('users/{purposeId}', [AppointmentController::class, 'getUsers'])->name('appointment.users');
        
        // Get available slots
        Route::post('slots', [AppointmentController::class, 'getAvailableSlots'])->name('appointment.slots');
        
        // Create appointment
        Route::post('create', [AppointmentController::class, 'create'])->name('appointment.create');
        
        // Get appointments by account
        Route::get('account/{accountNumber}', [AppointmentController::class, 'getByAccount'])->name('appointment.by-account');
        
        // Cancel appointment
        Route::post('{appointmentId}/cancel', [AppointmentController::class, 'cancel'])->name('appointment.cancel');
    });

    // API Key Management Routes (Admin only - add middleware as needed)
    Route::prefix('api-keys')->group(function () {
        Route::get('/', [ApiKeyController::class, 'index'])->name('appointment.api-keys.index');
        Route::post('/', [ApiKeyController::class, 'store'])->name('appointment.api-keys.store');
        Route::patch('{id}/toggle-status', [ApiKeyController::class, 'toggleStatus'])->name('appointment.api-keys.toggle');
        Route::delete('{id}', [ApiKeyController::class, 'destroy'])->name('appointment.api-keys.destroy');
    });
});