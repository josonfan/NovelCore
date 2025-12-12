<?php
use think\facade\Route;

Route::group('Sites', function () {
    Route::post('register', 'Sites/register')->middleware('ServiceWhitelist');
});
