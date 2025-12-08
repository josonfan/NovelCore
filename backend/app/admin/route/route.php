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
    Route::rule('detail', 'Roles/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Roles/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Roles/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Permissions', function () {
    Route::post('create', 'Permissions/create')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('index', 'Permissions/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Permissions/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Permissions/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Permissions/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Menus', function () {
    Route::rule('index', 'Menus/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('list', 'Menus/list', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Menus/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Menus/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Menus/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Menus/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('bindPermissions', 'Menus/bindPermissions', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('options', 'Menus/options', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Sites', function () {
    Route::rule('index', 'Sites/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Sites/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Sites/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Sites/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Sites/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Sites/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('DomainList', function () {
    Route::rule('index', 'DomainList/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'DomainList/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'DomainList/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'DomainList/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'DomainList/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Categories', function () {
    Route::rule('index', 'Categories/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Categories/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Categories/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Categories/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Categories/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Categories/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Tags', function () {
    Route::rule('index', 'Tags/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Tags/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Tags/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Tags/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Tags/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('toggle', 'Tags/toggle', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Novels', function () {
    Route::rule('index', 'Novels/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Novels/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Novels/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Novels/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Novels/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('bindTags', 'Novels/bindTags', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::group('Chapters', function () {
    Route::rule('index', 'Chapters/index', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('detail', 'Chapters/detail', 'GET|POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('create', 'Chapters/create', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('update', 'Chapters/update', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
    Route::rule('delete', 'Chapters/delete', 'POST')->middleware('AdminAuth')->middleware('RbacAuth');
});

Route::miss(function(){
    return json(['code'=>404,'msg'=>'接口不存在']);
});
