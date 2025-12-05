<?php
use think\facade\Route;

Route::group('Admins', function () {
    Route::post('add', 'Admins/add')->middleware('AdminAuth')->middleware('RbacAuth');    
    Route::rule('detail', 'Admins/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Admins/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignRoles', 'Admins/assignRoles')->middleware('AdminAuth')->middleware('RbacAuth');
});
Route::group('Login', function () {    
    Route::rule('login', 'Login/login', 'POST|GET');    
    Route::rule('logout', 'Login/logout', 'POST|GET')->middleware('AdminAuth');
    Route::rule('info', 'Login/info', 'GET|POST')->middleware('AdminAuth');
    Route::rule('update', 'Login/update', 'POST')->middleware('AdminAuth');
    Route::rule('changePassword', 'Login/changePassword', 'POST')->middleware('AdminAuth');
});
Route::group('Roles', function () {
    Route::post('create', 'Roles/create')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignPermissions', 'Roles/assignPermissions')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Roles/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Permissions', function () {
    Route::post('create', 'Permissions/create')->middleware('AdminAuth');
    Route::get('index', 'Permissions/index')->middleware('AdminAuth');
});

Route::group('Menus', function () {
    Route::rule('index', 'Menus/index', 'GET|POST')->middleware('AdminAuth');
    Route::rule('list', 'Menus/list', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Menus/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Menus/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Menus/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Menus/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('bindPermissions', 'Menus/bindPermissions', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::miss(function(){
    return json(['code'=>404,'msg'=>'接口不存在']);
});
