<?php

use Catlane\SysConfig\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('sys/sys-config', 'Catlane\SysConfig\Http\Controllers\SystemConfigController');
Route::resource('sys/sys-config-value', 'Catlane\SysConfig\Http\Controllers\SystemConfigValueController');
