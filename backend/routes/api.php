<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RepositoryController;

Route::get('/repos', [RepositoryController::class, 'index']);
