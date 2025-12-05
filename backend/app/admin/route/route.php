<?php
use think\facade\Route;

Route::group('Admins', function () {
    Route::post('add', 'Admins/add')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::get('index', 'Admins/index')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignRoles', 'Admins/assignRoles')->middleware('AdminAuth');
});

Route::post('Login/login', 'Login/login');

Route::group('Roles', function () {
    Route::post('create', 'Roles/create')->middleware('AdminAuth');
    Route::post('assignPermissions', 'Roles/assignPermissions')->middleware('AdminAuth');
    Route::get('index', 'Roles/index')->middleware('AdminAuth');
});

Route::group('Permissions', function () {
    Route::post('create', 'Permissions/create')->middleware('AdminAuth');
    Route::get('index', 'Permissions/index')->middleware('AdminAuth');
});

Route::group('Menus', function () {
    Route::get('index', 'Menus/index')->middleware('AdminAuth')->middleware('RbacAuth');
});
