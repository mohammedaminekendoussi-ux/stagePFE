<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ChangePasswordController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\StructureController;
use App\Http\Controllers\Admin\EmploiDuTempsController;
use App\Http\Controllers\Admin\BackupController;
use App\Http\Controllers\Directeur\DashboardController as DirecteurDashboard;
use App\Http\Controllers\Directeur\RapportController;
use App\Http\Controllers\Directeur\EmploiDuTempsController as DirecteurEmploiController;
use App\Http\Controllers\Directeur\DossierController;
use App\Http\Controllers\Formateur\PlanningController;
use App\Http\Controllers\Formateur\CoursController;
use App\Http\Controllers\Formateur\NotesController;
use App\Http\Controllers\Formateur\AbsenceController;

// Redirection racine
Route::get('/', fn() => redirect()->route('login'));

// Authentification
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Changement de mot de passe
Route::middleware(['auth'])->group(function () {
    Route::get('/change-password', [ChangePasswordController::class, 'showForm'])->name('password.change.form');
    Route::post('/change-password', [ChangePasswordController::class, 'update'])->name('password.change.update');
});

// Routes protégées (mot de passe changé)
Route::middleware(['auth', 'password.changed'])->group(function () {

    // Admin
    Route::prefix('admin')->name('admin.')->middleware('role:administrateur')->group(function () {
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::patch('/users/{id}/toggle', [UserController::class, 'toggleActif'])->name('users.toggle');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');
        Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');

        Route::get('/structure/filieres', [StructureController::class, 'filieres'])->name('structure.filieres');
        Route::post('/structure/filieres', [StructureController::class, 'storeFiliere'])->name('structure.filieres.store');
        Route::put('/structure/filieres/{id}', [StructureController::class, 'updateFiliere'])->name('structure.filieres.update');
        Route::delete('/structure/filieres/{id}', [StructureController::class, 'destroyFiliere'])->name('structure.filieres.destroy');

        Route::get('/structure/groupes', [StructureController::class, 'groupes'])->name('structure.groupes');
        Route::post('/structure/groupes', [StructureController::class, 'storeGroupe'])->name('structure.groupes.store');
        Route::put('/structure/groupes/{id}', [StructureController::class, 'updateGroupe'])->name('structure.groupes.update');
        Route::delete('/structure/groupes/{id}', [StructureController::class, 'destroyGroupe'])->name('structure.groupes.destroy');

        Route::get('/structure/modules', [StructureController::class, 'modules'])->name('structure.modules');
        Route::post('/structure/modules', [StructureController::class, 'storeModule'])->name('structure.modules.store');
        Route::put('/structure/modules/{id}', [StructureController::class, 'updateModule'])->name('structure.modules.update');
        Route::delete('/structure/modules/{id}', [StructureController::class, 'destroyModule'])->name('structure.modules.destroy');

        Route::get('/emploi', [EmploiDuTempsController::class, 'index'])->name('emploi.index');
        Route::post('/emploi', [EmploiDuTempsController::class, 'store'])->name('emploi.store');
        Route::put('/emploi/{id}', [EmploiDuTempsController::class, 'update'])->name('emploi.update');
        Route::delete('/emploi/{id}', [EmploiDuTempsController::class, 'destroy'])->name('emploi.destroy');
        Route::get('/emploi/formateurs/{moduleId}', [EmploiDuTempsController::class, 'getFormateurs'])->name('emploi.formateurs');
        Route::get('/emploi/salles', [EmploiDuTempsController::class, 'getSallesDisponibles'])->name('emploi.salles');
        Route::get('/emploi/semestres/{groupeId}', [EmploiDuTempsController::class, 'getSemestresByGroupe'])->name('admin.emploi.semestres');

        Route::get('/backup', [BackupController::class, 'download'])->name('backup.download');

        Route::get('/absences/list/{etudiantId}/{moduleId}', [DashboardController::class, 'listAbsences'])->name('admin.absences.list');
        Route::post('/absences/justifier', [DashboardController::class, 'justifierAbsence'])->name('admin.absences.justifier');
    });

    // Directeur
    Route::prefix('directeur')->name('directeur.')->middleware('role:directeur')->group(function () {
        Route::get('/dashboard', [DirecteurDashboard::class, 'index'])->name('dashboard');
        Route::get('/rapports', [RapportController::class, 'index'])->name('rapports');
        Route::get('/rapports/export-pdf', [RapportController::class, 'exportPdf'])->name('rapports.export');
        Route::get('/emploi-du-temps', [DirecteurEmploiController::class, 'index'])->name('emploi.index');
        Route::get('/dossiers', [DossierController::class, 'index'])->name('dossiers.index');
    });

    // Formateur
    Route::prefix('formateur')->name('formateur.')->middleware('role:formateur')->group(function () {
        Route::get('/planning', [PlanningController::class, 'index'])->name('planning.index');
        Route::get('/cours', [CoursController::class, 'index'])->name('cours.index');
        Route::post('/cours', [CoursController::class, 'store'])->name('cours.store');
        Route::delete('/cours/{id}', [CoursController::class, 'destroy'])->name('cours.destroy');
        Route::get('/notes', [NotesController::class, 'index'])->name('notes.index');
        Route::post('/notes/save', [NotesController::class, 'save'])->name('notes.save');
        Route::post('/notes/validate', [NotesController::class, 'validateNotes'])->name('notes.validate');
        Route::get('/absences', [AbsenceController::class, 'index'])->name('absences.index');
        Route::post('/absences/{seanceId}', [AbsenceController::class, 'store'])->name('absences.store');
    });

    // Étudiant
    Route::prefix('etudiant')->name('etudiant.')->middleware('role:etudiant')->group(function () {
        Route::get('/dashboard', [App\Http\Controllers\Etudiant\DashboardController::class, 'index'])->name('dashboard');
        Route::get('/cours', [App\Http\Controllers\Etudiant\CoursController::class, 'index'])->name('cours.index');
        Route::get('/cours/telecharger/{id}', [App\Http\Controllers\Etudiant\CoursController::class, 'telecharger'])->name('cours.telecharger');
        Route::get('/notes', [App\Http\Controllers\Etudiant\NoteController::class, 'index'])->name('notes');
        Route::get('/absences', [App\Http\Controllers\Etudiant\AbsenceController::class, 'index'])->name('absences');
    });

});