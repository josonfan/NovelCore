<?php
use think\facade\Route;

Route::group('Sites', function () {
    Route::post('register', 'Sites/register')->middleware('ServiceWhitelist');    
});
Route::group('Sync', function () {
    Route::post('receive', 'Sync/receive')->middleware('ServiceWhitelist');    
});
