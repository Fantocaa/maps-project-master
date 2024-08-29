<?php

use App\Http\Controllers\MdAgentController;
use App\Http\Controllers\MdBiayaController;
use App\Http\Controllers\MdBiayaNameController;
use App\Http\Controllers\MdCompanyController;
use App\Http\Controllers\MdJenisBarangController;
use Inertia\Inertia;
use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Application;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RegisterUserController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\MdMapsController;
use App\Http\Controllers\MdSatuanController;
use App\Http\Controllers\UserCompanyController;

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
    return redirect('/login');
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

    Route::post('/manage/jenisbarang/new', [MdJenisBarangController::class, 'store'])->name('jenisbarang.store');
    Route::get('/manage/jenisbarang/{id}/edit', [MdJenisBarangController::class, 'edit_jenisbarang'])->name('jenisbarang.edit');
    Route::patch('/manage/jenisbarang/update', [MdJenisBarangController::class, 'update_jenisbarang'])->name('jenisbarang.update');
    Route::delete('/manage/jenisbarang/{id}/delete', [MdJenisBarangController::class, 'destroy_jenisbarang'])->name('jenisbarang.destroy');

    Route::get('/manage/viewmap/{id}/edit', [MdMapsController::class, 'edit_viewmap'])->name('viewmap.edit');
    Route::patch('/manage/viewmap/update', [MdMapsController::class, 'update_viewmap'])->name('viewmap.update');
    Route::delete('/manage/viewmap/{id}/delete', [MdMapsController::class, 'destroy_viewmap'])->name('viewmap.destroy');

    // Route::resource('maps', MdMapsController::class);

    Route::get('maps/index', [MdMapsController::class, 'index'])->name('maps.index');
    Route::post('maps/store', [MdMapsController::class, 'store'])->name('maps.store');

    Route::post('/maps/edit/{id}/', [MdMapsController::class, 'update_maps'])->name('update_form_maps');
    Route::delete('/maps/delete/{id}/', [MdMapsController::class, 'delete_maps'])->name('delete_form_maps');

    Route::get('/role', [MdMapsController::class, 'has_role'])->name('has_role');
    Route::get('/biaya', [MdBiayaController::class, 'index'])->name('index.biaya');

    Route::get('/user_companies', [UserCompanyController::class, 'index'])->name('index.user_companies');

    Route::get('/company', [MdCompanyController::class, 'index'])->name('index.company');
    Route::get('/agent', [MdAgentController::class, 'index'])->name('index.agent');
    Route::get('/unit', [MdSatuanController::class, 'index'])->name('index.unit');
    Route::get('/biaya_name', [MdBiayaNameController::class, 'index'])->name('index.biaya_name');
    Route::get('/jenis_barang', [MdJenisBarangController::class, 'index'])->name('index.jenisbarang_name');

    Route::get('/reverse-geocode', [MdMapsController::class, 'geocode']);

    Route::get('/maps/export', [MdMapsController::class, 'export'])->middleware(['role:superadmin']);

    // Route::get('/maps/export/{id}', [MdMapsController::class, 'export_by_id'])->middleware(['role:superadmin']);
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

Route::get('/maps', function () {})->middleware('auth.redirect');

Route::get('/maps/user', function () {
    return Inertia::render('Maps/MapsUser');
})->middleware(['auth', 'verified', 'role:user|superadmin'])->name('mapsUser');

Route::get('/maps/superuser', function () {
    return Inertia::render('Maps/MapsSuperUser');
})->middleware(['auth', 'verified', 'role:superuser|superadmin'])->name('mapsSuperUser');

Route::get('/maps/admin', function () {
    return Inertia::render('Maps/MapsAdmin');
})->middleware(['auth', 'verified', 'role:admin|superadmin'])->name('mapsAdmin');

// Route::get('/maps/admin', [MdMapsController::class, 'show'])->name('index.mapsAdmin')
//     ->middleware(['auth', 'verified', 'role:admin|superadmin'])->name('mapsAdmin');

require __DIR__ . '/auth.php';
