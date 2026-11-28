<?php

use Illuminate\Support\Facades\Route;


Route::view('/admin/login', 'admin.login');
Route::view('/admin', 'admin.dashboard');
Route::view('/admin/nurseries', 'admin.nurseries.index');
Route::view('/admin/users', 'admin.users.index');
Route::view('/admin/tickets', 'admin.tickets.index');
Route::view('/admin/age-groups', 'admin.age-groups.index');