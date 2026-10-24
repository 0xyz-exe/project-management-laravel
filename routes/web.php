<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProjectController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('projects.index'));
Route::get('/dashboard', fn (Request $r) => redirect()->route('projects.index'))->middleware('auth')->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
    Route::post('/projects', [ProjectController::class, 'store'])->name('projects.store');
    Route::get('/projects/{id}', [ProjectController::class, 'show'])->whereNumber('id')->name('projects.show');
    Route::post('/projects/{id}/tasks', [ProjectController::class, 'task'])->whereNumber('id')->name('projects.tasks.store');
    Route::patch('/projects/{id}/tasks/{task}', [ProjectController::class, 'updateTask'])->whereNumber('id')->name('projects.tasks.update');
    Route::post('/projects/{id}/tasks/{task}/comments', [ProjectController::class, 'comment'])->whereNumber('id')->name('projects.comments.store');
    Route::post('/projects/{id}/milestones', [ProjectController::class, 'milestone'])->whereNumber('id')->name('projects.milestones.store');
});

require __DIR__.'/auth.php';
