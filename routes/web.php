<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\Admin\EmploiDuTempsController;

Route::get('/', function () {
    return redirect()->route('admin.dashboard');
});

Route::prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Gestion utilisateurs
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{id}/toggle', [UserController::class, 'toggleActif'])->name('users.toggle');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

    // Gestion structure académique
    // Filières
    Route::get('/structure/filieres', [StructureController::class, 'filieres'])->name('structure.filieres');
    Route::post('/structure/filieres', [StructureController::class, 'storeFiliere'])->name('structure.filieres.store');
    Route::put('/structure/filieres/{id}', [StructureController::class, 'updateFiliere'])->name('structure.filieres.update');
    Route::delete('/structure/filieres/{id}', [StructureController::class, 'destroyFiliere'])->name('structure.filieres.destroy');

    // Groupes
    Route::get('/structure/groupes', [StructureController::class, 'groupes'])->name('structure.groupes');
    Route::post('/structure/groupes', [StructureController::class, 'storeGroupe'])->name('structure.groupes.store');
    Route::put('/structure/groupes/{id}', [StructureController::class, 'updateGroupe'])->name('structure.groupes.update');
    Route::delete('/structure/groupes/{id}', [StructureController::class, 'destroyGroupe'])->name('structure.groupes.destroy');

    // Modules
    Route::get('/structure/modules', [StructureController::class, 'modules'])->name('structure.modules');
    Route::post('/structure/modules', [StructureController::class, 'storeModule'])->name('structure.modules.store');
    Route::put('/structure/modules/{id}', [StructureController::class, 'updateModule'])->name('structure.modules.update');
    Route::delete('/structure/modules/{id}', [StructureController::class, 'destroyModule'])->name('structure.modules.destroy');


    // Emploi du temps
Route::get('/emploi', [EmploiDuTempsController::class, 'index'])->name('emploi.index');
Route::post('/emploi', [EmploiDuTempsController::class, 'store'])->name('emploi.store');
Route::put('/emploi/{id}', [EmploiDuTempsController::class, 'update'])->name('emploi.update');
Route::delete('/emploi/{id}', [EmploiDuTempsController::class, 'destroy'])->name('emploi.destroy');
Route::get('/emploi/formateurs/{moduleId}', [EmploiDuTempsController::class, 'getFormateurs'])->name('emploi.formateurs');

Route::get('/emploi/salles', [EmploiDuTempsController::class, 'getSallesDisponibles'])->name('emploi.salles');
});