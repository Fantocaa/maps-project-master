<?php

use App\Http\Controllers\MdAgentController;
use App\Http\Controllers\MdBiayaController;
use App\Http\Controllers\MdBiayaNameController;
use App\Http\Controllers\MdCompanyController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MdMapsController;
use App\Http\Controllers\MdSatuanController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    // if (Auth::check()) {
    // The user is logged in, redirect them to the desired page
    return redirect('/login');
    // } else {
    //     return Inertia::render('Welcome', [
    //         'canLogin' => Route::has('login'),
    //         // 'canRegister' => Route::has('register'),
    //         'laravelVersion' => Application::VERSION,
    //         'phpVersion' => PHP_VERSION,
    //     ]);
    // }
});

// Route::get('/register', function () {
// })->middleware(['auth', 'verified', 'role:superadmin']);

// Route::get('/registeruser', [RegisterUserController::class, 'create'])->name('registeruser')->middleware(['auth', 'verified', 'role:superadmin']);

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified', 'role:superadmin'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('/manage/user/{id}/edit', [UserController::class, 'edit_user'])->name('user.edit');
    Route::patch('/manage/user/update', [UserController::class, 'update_user'])->name('user.update');
    Route::put('/manage/user/{id}/update_password', [UserController::class, 'update_password'])->name('user.update.password');
    Route::delete('/manage/user/{id}/delete', [UserController::class, 'destroy_user'])->name('user.destroy');

    Route::post('/manage/company/new', [MdCompanyController::class, 'store'])->name('company.store');
    Route::get('/manage/company/{id}/edit', [MdCompanyController::class, 'edit_company'])->name('company.edit');
    Route::patch('/manage/company/update', [MdCompanyController::class, 'update_company'])->name('company.update');
    Route::delete('/manage/company/{id}/delete', [MdCompanyController::class, 'destroy_company'])->name('company.destroy');

    Route::post('/manage/agent/new', [MdAgentController::class, 'store'])->name('agent.store');
    Route::get('/manage/agent/{id}/edit', [MdAgentController::class, 'edit_agent'])->name('agent.edit');
    Route::patch('/manage/agent/update', [MdAgentController::class, 'update_agent'])->name('agent.update');
    Route::delete('/manage/agent/{id}/delete', [MdAgentController::class, 'destroy_agent'])->name('agent.destroy');

    Route::post('/manage/unit/new', [MdSatuanController::class, 'store'])->name('unit.store');
    Route::get('/manage/unit/{id}/edit', [MdSatuanController::class, 'edit_unit'])->name('unit.edit');
    Route::patch('/manage/unit/update', [MdSatuanController::class, 'update_unit'])->name('unit.update');
    Route::delete('/manage/unit/{id}/delete', [MdSatuanController::class, 'destroy_unit'])->name('unit.destroy');

    Route::post('/manage/biaya/new', [MdBiayaNameController::class, 'store'])->name('biaya.store');
    Route::get('/manage/biaya/{id}/edit', [MdBiayaNameController::class, 'edit_biaya'])->name('biaya.edit');
    Route::patch('/manage/biaya/update', [MdBiayaNameController::class, 'update_biaya'])->name('biaya.update');
    Route::delete('/manage/biaya/{id}/delete', [MdBiayaNameController::class, 'destroy_biaya'])->name('biaya.destroy');
});

Route::get('/components/buttons', function () {
    return Inertia::render('Components/Buttons');
})->middleware(['auth', 'verified'])->name('components.buttons');

Route::get('/manage/user', function () {
    return Inertia::render('Components/Register');
})->middleware(['auth', 'verified'])->name('manage.user');

Route::get('/manage/company', function () {
    return Inertia::render('Components/Company');
})->middleware(['auth', 'verified'])->name('manage.company');

//Maps Route

Route::get('/maps', function () {
})->middleware('auth.redirect');

Route::get('/maps/user', function () {
    return Inertia::render('Maps/MapsUser');
})->middleware(['auth', 'verified', 'role:user|superuser|admin|superadmin'])->name('mapsUser');

Route::get('/maps/superuser', function () {
    return Inertia::render('Maps/MapsSuperUser');
})->middleware(['auth', 'verified', 'role:superuser|admin|superadmin'])->name('mapsSuperUser');

Route::get('/maps/admin', function () {
    return Inertia::render('Maps/MapsAdmin');
})->middleware(['auth', 'verified', 'role:admin|superadmin'])->name('mapsAdmin');

Route::get('/maps/admincopy', function () {
    return Inertia::render('Maps/MapsAdmin Copy');
})->middleware(['auth', 'verified', 'role:admin|superadmin'])->name('mapsAdmincopy');


require __DIR__ . '/auth.php';
