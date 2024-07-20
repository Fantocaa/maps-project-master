<?php

use App\Http\Controllers\MdAgentController;
use App\Http\Controllers\MdBiayaController;
use App\Http\Controllers\MdBiayaNameController;
use App\Http\Controllers\MdCompanyController;
use App\Http\Controllers\MdMapsController;
use App\Http\Controllers\MdSatuanController;
use App\Http\Controllers\UserCompanyController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware([])->group(function () {
    Route::resource('maps', MdMapsController::class);

    Route::get('/reverse-geocode', [MdMapsController::class, 'geocode']);

    Route::post('/maps/edit/{id}/', [MdMapsController::class, 'update_maps'])->name('update_form_maps');

    Route::delete('/maps/delete/{id}/', [MdMapsController::class, 'delete_maps'])->name('delete_form_maps');

    Route::get('/role', [MdMapsController::class, 'has_role'])->name('has_role');
    Route::get('/biaya', [MdBiayaController::class, 'index'])->name('index.biaya');

    Route::get('/user_companies', [UserCompanyController::class, 'index'])->name('index.user_companies');

    Route::get('/company', [MdCompanyController::class, 'index'])->name('index.company');
    Route::get('/agent', [MdAgentController::class, 'index'])->name('index.agent');
    Route::get('/unit', [MdSatuanController::class, 'index'])->name('index.unit');
    Route::get('/biaya_name', [MdBiayaNameController::class, 'index'])->name('index.biaya_name');

});
