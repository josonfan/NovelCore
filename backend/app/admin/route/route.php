<?php
use think\facade\Route;

Route::group('Admins', function () {
    Route::post('add', 'Admins/add')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('info', 'Admins/info', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::post('assignRoles', 'Admins/assignRoles')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Admins/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::get('roles', 'Admins/roles')->middleware('AdminAuth')->middleware('RbacAuth');

});
Route::group('Login', function () {
    Route::rule('login', 'Login/login', 'POST|GET');
    Route::rule('logout', 'Login/logout', 'POST|GET')->middleware('AdminAuth');
});
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
    Route::rule('index', 'Menus/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::miss(function(){
    return json(['code'=>404,'msg'=>'接口不存在']);
});
