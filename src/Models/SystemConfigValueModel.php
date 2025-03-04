<?php

namespace Catlane\DcatSysConfig\Models;

use Catlane\DcatSysConfig\Casts\SystemConfigValueJson;
use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SystemConfigValueModel extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'system_config_value';
    protected $casts = [
        'value' => SystemConfigValueJson::class,
    ];

}
