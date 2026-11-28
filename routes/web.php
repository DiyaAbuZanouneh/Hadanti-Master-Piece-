<?php

use Illuminate\Support\Facades\Route;


Route::view('/admin/login', 'admin.login');
Route::view('/admin', 'admin.dashboard');
Route::view('/admin/nurseries', 'admin.nurseries.index');