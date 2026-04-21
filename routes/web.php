<?php

use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\DrugCategoryController;
use App\Http\Controllers\DrugController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PrescriptionController;
use App\Http\Controllers\PublicInquiryController;
use App\Http\Controllers\RadiologyOrderController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'public.welcome')->name('home');
Route::view('/about', 'public.about')->name('about');
Route::view('/services', 'public.services')->name('services');
Route::get('/shop', [CartController::class, 'shop'])->name('shop.index');
Route::post('/shop/cart/{product}', [CartController::class, 'add'])->name('cart.add');
Route::get('/cart', [CartController::class, 'cart'])->name('cart.index');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CartController::class, 'checkout'])->name('shop.checkout');
Route::post('/checkout', [CartController::class, 'processCheckout'])->name('shop.checkout.store');
Route::get('/checkout/{payment}/success', [CartController::class, 'success'])->name('shop.checkout.success');
Route::get('/checkout/{payment}/cancel', [CartController::class, 'cancel'])->name('shop.checkout.cancel');
Route::view('/contact-us', 'public.contact')->name('contact');
Route::post('/contact-us', [PublicInquiryController::class, 'store'])->name('contact.store');
Route::view('/location', 'public.location')->name('location');
Route::get('/appointment-check-in/{appointment}', [AppointmentController::class, 'checkIn'])
    ->middleware('signed')
    ->name('appointments.check-in');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/staff/login', [AuthController::class, 'showStaffLogin'])->name('staff.login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/verify-email', [AuthController::class, 'showVerifyEmail'])->name('verification.form');
    Route::post('/verify-email', [AuthController::class, 'verifyEmail'])->name('verification.verify');
    Route::post('/verify-email/resend', [AuthController::class, 'resendEmailVerification'])->name('verification.resend');
    Route::get('/verify-otp', [AuthController::class, 'showOtp'])->name('otp.form');
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp'])->name('otp.verify');
    Route::post('/verify-otp/resend', [AuthController::class, 'resendLoginOtp'])->name('otp.resend');
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/password', [AuthController::class, 'showChangePassword'])->name('password.edit');
    Route::put('/password', [AuthController::class, 'updatePassword'])->name('password.update');

    Route::get('/my-profile', [PatientController::class, 'profile'])
        ->middleware('role:patient')
        ->name('patients.profile');

    Route::resource('departments', DepartmentController::class)
        ->middleware('role:admin');

    Route::resource('doctors', DoctorController::class)
        ->except(['index', 'show'])
        ->middleware('role:admin');
    Route::resource('doctors', DoctorController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,receptionist');

    Route::resource('patients', PatientController::class)
        ->except(['index', 'show'])
        ->middleware('role:admin');
    Route::resource('patients', PatientController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,receptionist,doctor,rn,pct,nurse');

    Route::resource('appointments', AppointmentController::class)
        ->only(['create', 'store'])
        ->middleware('role:admin,receptionist,patient');
    Route::resource('appointments', AppointmentController::class)
        ->only(['edit', 'update', 'destroy'])
        ->middleware('role:admin,receptionist');
    Route::resource('appointments', AppointmentController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,receptionist,doctor,patient');

    Route::get('/appointments/{appointment}/consultation', [ConsultationController::class, 'edit'])
        ->middleware('role:admin,doctor')
        ->name('consultations.edit');
    Route::put('/appointments/{appointment}/consultation', [ConsultationController::class, 'update'])
        ->middleware('role:admin,doctor')
        ->name('consultations.update');

    Route::post('/payments/{payment}/stripe-checkout', [PaymentController::class, 'stripeCheckout'])
        ->middleware('role:patient')
        ->name('payments.stripe.checkout');
    Route::get('/payments/{payment}/stripe-success', [PaymentController::class, 'stripeSuccess'])
        ->middleware('role:patient')
        ->name('payments.stripe.success');
    Route::get('/payments/{payment}/stripe-cancel', [PaymentController::class, 'stripeCancel'])
        ->middleware('role:patient')
        ->name('payments.stripe.cancel');
    Route::post('/payments/{payment}/mobile-money', [PaymentController::class, 'mobileMoney'])
        ->middleware('role:patient')
        ->name('payments.mobile-money');

    Route::resource('payments', PaymentController::class)
        ->except(['index', 'show'])
        ->middleware('role:admin,cashier');
    Route::resource('payments', PaymentController::class)
        ->only(['index', 'show'])
        ->middleware('role:admin,cashier,patient');

    Route::resource('drug-categories', DrugCategoryController::class)
        ->except(['show'])
        ->middleware('role:pharmacist');
    Route::resource('drugs', DrugController::class)
        ->except(['show'])
        ->middleware('role:pharmacist');

    Route::get('/appointments/{appointment}/prescriptions/create', [PrescriptionController::class, 'create'])
        ->middleware('role:admin,doctor')
        ->name('prescriptions.create');
    Route::post('/appointments/{appointment}/prescriptions', [PrescriptionController::class, 'store'])
        ->middleware('role:admin,doctor')
        ->name('prescriptions.store');
    Route::resource('prescriptions', PrescriptionController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('role:pharmacist');

    Route::get('/appointments/{appointment}/radiology-orders/create', [RadiologyOrderController::class, 'create'])
        ->middleware('role:admin,doctor')
        ->name('radiology-orders.create');
    Route::post('/appointments/{appointment}/radiology-orders', [RadiologyOrderController::class, 'store'])
        ->middleware('role:admin,doctor')
        ->name('radiology-orders.store');
    Route::resource('radiology-orders', RadiologyOrderController::class)
        ->only(['index', 'show', 'edit', 'update'])
        ->middleware('role:admin,radiology');

    Route::get('/reports', [ReportController::class, 'index'])
        ->middleware('role:admin,receptionist,doctor,cashier,pharmacist,radiology,rn,pct,housekeeping,nurse,dietary,patient')
        ->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'exportCsv'])
        ->middleware('role:admin,receptionist,doctor,cashier,pharmacist,radiology,rn,pct,housekeeping,nurse,dietary,patient')
        ->name('reports.export');
});
