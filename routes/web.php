<?php

use App\Http\Controllers\Admin\ArchiveController;
use App\Http\Controllers\Admin\LieuController;
use App\Http\Controllers\Admin\SyntheseController;
use App\Http\Controllers\Conge\AbsenceController;
use App\Http\Controllers\Conge\MotifController;
use App\Http\Controllers\Planning\categorieController;
use App\Http\Controllers\Planning\PlanningController;
use App\Http\Controllers\Planning\TacheController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\WelcomeController;
use App\Models\Admin\Archive;
use App\Models\Admin\Lieu;
use App\Models\Conge\Absence;
use App\Models\Conge\Motif;
use App\Models\Planning\categorie;
use App\Models\Planning\Planning;
use App\Models\User;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::get('/', [WelcomeController::class, 'index'])->name('home');

    //
    // Profil
    //
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    //
    // planning
    //
    Route::resource('/planning', PlanningController::class);
    // importation du planning
    Route::get('/plannings/importer', [PlanningController::class, 'importerPlanning'])->name('planning.importer');
    // planning d'un salarié en particulier
    Route::get('/planning/salarie/{salarie_id}', [PlanningController::class, 'showPlanningBySalarie'])->name('planning.salarie');
    // validation d'une tâche dans le planning
    Route::put('/planning/validateTache/{planning}', [PlanningController::class, 'validateTache'])->name('planning.validateTache');
    // vérifier si une tâche est validée
    Route::get('/planning/isTacheValidated/{planning}', [PlanningController::class, 'isTacheValidated'])->name('planning.isTacheValidated');
    Route::get('/planning/model', [PlanningController::class, 'model']);
    Route::get('/api/planning/events', [PlanningController::class, 'getEventsForRange'])->name('planning.getEventsForRange');

    //
    // Tache
    //
    Route::resource('/tache', TacheController::class);
    Route::get('/tache/model/{tache}/{ability}', [TacheController::class, 'model'])->name('tache.model');
    Route::get('/tache/salarie/{salarie_id}', [TacheController::class, 'showTacheBySalarie'])->name('tache.salarie');
    Route::bind('salarie_id', function ($salarie_id) {
        return User::find($salarie_id);
    });

    //
    // Synthese
    //
    Route::resource('/synthese', SyntheseController::class);
    // Réinitialisation des filtres
    Route::get('/synthese/{user}', [SyntheseController::class, 'show'])->name('synthese.show');
    // Export CSV
    Route::get('/export-csv{id}', [SyntheseController::class, 'export'])->name('export.csv');
    // Graphique à l'année
    Route::get('/synthese/graphique/{user}', [SyntheseController::class, 'showGraphiqueYear'])->name('synthese.graphique');
    // Validation des tâches
    Route::get('/synthese/validation/{salarie_id}', [SyntheseController::class, 'showTacheValidation'])->name('synthese.tacheValidation');
    Route::bind('salarie_id', function ($salarie_id) {
        return User::find($salarie_id);
    });

    //
    // Lieu
    //
    Route::get('/lieu/{lieu_id}/undelete', [LieuController::class, 'undelete'])->name('lieu.undelete');
    Route::bind('lieu_id', function ($lieu_id) {
        return Lieu::onlyTrashed()->find($lieu_id);
    });
    Route::get('/lieu/corbeille', [LieuController::class, 'corbeille'])->name('lieu.corbeille');
    Route::resource('/lieu', LieuController::class);

    //
    // Archive
    //
    Route::get('/archive/json', [ArchiveController::class, 'json'])->name('archive.json');
    Route::get('/archive/{archive_id}/undelete', [ArchiveController::class, 'undelete'])->name('archive.undelete');
    Route::bind('archive_id', function ($archive_id) {
        return Archive::onlyTrashed()->find($archive_id);
    });
    Route::post('/archive/archivate/{planning_id}', [ArchiveController::class, 'archivate'])->name('archive.archivate');
    Route::bind('planning_id', function ($planning_id) {
        return Planning::find($planning_id);
    });
    Route::resource('/archive', ArchiveController::class);

    //
    // Absence
    //
    Route::get('/absence/corbeille', [absenceController::class, 'corbeille'])->name('absence.corbeille');

    Route::get('/absence/json', [AbsenceController::class, 'json'])->name('absence.json');
    Route::get('/absence/{absence_id}/undelete', [AbsenceController::class, 'undelete'])->name('absence.undelete');
    Route::get('/absence/{absence}/confirm', [AbsenceController::class, 'confirm'])->name('absence.confirm');
    Route::get('/absence/{absence}/refuse', [AbsenceController::class, 'refuse'])->name('absence.refuse');
    Route::bind('absence_id', function ($absence_id) {
        return absence::onlyTrashed()->find($absence_id);
    });
    Route::get('/absence/salarie/{salarie_id}', [AbsenceController::class, 'showAbsenceBySalarie'])->name('absence.salarie');
    Route::bind('salarie_id', function ($salarie_id) {
        return User::find($salarie_id);
    });
    Route::resource('/absence', AbsenceController::class);
    Route::get('/absence/tableau/{salarie_id}', [AbsenceController::class, 'getTableauData'])->name('absence.tableau');
    Route::bind('salarie_id', function ($salarie_id) {
        return $salarie_id ? User::find($salarie_id) : null;
    });

    //
    // Motif
    //
    Route::get('/motif/corbeille', [motifController::class, 'corbeille'])->name('motif.corbeille');

    Route::get('/motif/{motif_id}/undelete', [MotifController::class, 'undelete'])->name('motif.undelete');
    Route::bind('motif_id', function ($motif_id) {
        return motif::onlyTrashed()->find($motif_id);
    });
    Route::get('/motif/json', [MotifController::class, 'json'])->name('motif.json');
    Route::resource('/motif', MotifController::class);

    //
    // categorie
    //
    Route::get('/categorie/{categorie_id}/undelete', [categorieController::class, 'undelete'])->name('categorie.undelete');
    Route::bind('categorie_id', function ($categorie_id) {
        return categorie::onlyTrashed()->find($categorie_id);
    });
    Route::get('/categorie/json', [categorieController::class, 'json'])->name('categorie.json');
    Route::resource('/categorie', categorieController::class);
});

require __DIR__ . '/auth.php';
