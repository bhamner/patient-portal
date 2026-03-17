<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InviteAcceptFormController;
use App\Http\Controllers\InviteAcceptStoreController;
use App\Http\Controllers\InvitationCreateController;
use App\Http\Controllers\InvitationStoreController;
use App\Http\Controllers\OrganizationSignupFormController;
use App\Http\Controllers\OrganizationSignupStoreController;
use App\Http\Controllers\OrganizationSignupSuccessController;
use App\Http\Controllers\SmsTestFormController;
use App\Http\Controllers\SmsTestSendController;
use App\Http\Controllers\AppointmentCalendarController;
use App\Http\Controllers\AppointmentStoreController;
use App\Http\Controllers\AppointmentUpdateController;
use App\Http\Controllers\AppointmentDestroyController;
use App\Http\Controllers\OrganizationAppointmentSettingsController;
use App\Http\Controllers\OrganizationBrandingController;
use App\Http\Controllers\HolidayStoreController;
use App\Http\Controllers\HolidayDestroyController;
use App\Http\Controllers\OrganizationSettingsController;
use App\Http\Controllers\UserRoleUpdateController;
use App\Http\Controllers\UsersIndexController;

Route::view('/', 'pages.home')->name('home');
Route::view('/features', 'pages.features')->name('features');
Route::view('/pricing', 'pages.pricing')->name('pricing');
Route::view('/about', 'pages.about')->name('about');

// Invite-only registration (no token = invite-only message; valid token = registration form)
Route::get('/register', InviteAcceptFormController::class)->name('register');
Route::post('/register', InviteAcceptStoreController::class)->name('register.store');

// Organization signup (create org + admins, then Stripe checkout)
Route::get('/organization/signup', OrganizationSignupFormController::class)->name('organization.signup');
Route::post('/organization/signup', OrganizationSignupStoreController::class)->name('organization.signup.store');
Route::get('/organization/signup/success', OrganizationSignupSuccessController::class)->name('organization.signup.success');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('dashboard', 'dashboard')->name('dashboard');

    Route::middleware(['role:staff,physician,patient', 'resolve.organization'])->group(function () {
        Route::get('/appointments', AppointmentCalendarController::class)->name('appointments.calendar');
        Route::post('/appointments', AppointmentStoreController::class)->name('appointments.store');
        Route::put('/appointments/{appointment}', AppointmentUpdateController::class)->name('appointments.update');
        Route::delete('/appointments/{appointment}', AppointmentDestroyController::class)->name('appointments.destroy');
    });

    Route::get('/sms/test', SmsTestFormController::class)->name('sms.test');
    Route::post('/sms/test', SmsTestSendController::class)->name('sms.test.store');

    Route::middleware(['role:admin,staff', 'resolve.organization'])->group(function () {
        Route::get('/organization/plans', \App\Http\Controllers\OrganizationPlansController::class)->name('organization.plans');
        Route::get('/organization/settings', OrganizationSettingsController::class)->name('organization.settings');
        Route::get('/users', UsersIndexController::class)->name('users.index');
        Route::put('/users/{user}/roles', UserRoleUpdateController::class)->name('users.roles.update');
        Route::get('/invitations/create', InvitationCreateController::class)->name('invitations.create');
        Route::post('/invitations', InvitationStoreController::class)->name('invitations.store');
        Route::put('/organization/appointment-settings', OrganizationAppointmentSettingsController::class)->name('organization.appointment-settings');
        Route::put('/organization/branding', OrganizationBrandingController::class)->name('organization.branding');
        Route::post('/holidays', HolidayStoreController::class)->name('holidays.store');
        Route::delete('/holidays/{holiday}', HolidayDestroyController::class)->name('holidays.destroy');
    });
});

require __DIR__.'/settings.php';
