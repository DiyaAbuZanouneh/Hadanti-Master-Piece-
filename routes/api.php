<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Admin\NurseryController as AdminNurseryController;
use App\Http\Controllers\Api\Admin\UserController as AdminUserController;


Route::post('/register', [AuthController::class,'register']);
Route::post('/login', [AuthController::class,'login']);


Route::middleware('auth:sanctum')->group(function (){
    Route::get('/me', [AuthController::class,'me']);
    Route::post('/logout', [AuthController::class,'logout']);
});


Route::middleware(['auth:sanctum', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/nurseries', [AdminNurseryController::class, 'index']);
    Route::get('/nurseries/{nursery}', [AdminNurseryController::class, 'show']);
    Route::post('/nurseries/{nursery}/approve', [AdminNurseryController::class, 'approve']);
    Route::post('/nurseries/{nursery}/reject', [AdminNurseryController::class, 'reject']);    
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::patch('/users/{user}/status', [AdminUserController::class, 'updateStatus']);

});