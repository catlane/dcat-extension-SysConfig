<?php

namespace Catlane\SysConfig\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Illuminate\Database\Eloquent\Model;

class SystemConfigClassifyModel extends Model
{
	use HasDateTimeFormatter;
    protected $table = 'system_config_classify';

}
