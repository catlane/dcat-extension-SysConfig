<?php

namespace Catlane\SysConfig\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SystemConfigModel extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'system_config';
    protected $casts = [
        'extra'  => 'json',
        'range_extra' => 'json'
    ];
}
