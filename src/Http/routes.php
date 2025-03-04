<?php

use Catlane\DcatSysConfig\Http\Controllers;
use Illuminate\Support\Facades\Route;

Route::resource('sys/sys-config', 'Catlane\DcatSysConfig\Http\Controllers\SystemConfigController');
Route::resource('sys/sys-config-value', 'Catlane\DcatSysConfig\Http\Controllers\SystemConfigValueController');
