<?php

namespace Catlane\SysConfig\Models;

use Dcat\Admin\Traits\HasDateTimeFormatter;
use Dcat\Admin\Traits\ModelTree;
use Illuminate\Database\Eloquent\Model;

class SystemConfigModel extends Model
{
	use HasDateTimeFormatter;
    use ModelTree;

    protected $table = 'system_config';
    protected $casts = [
        'extra'  => 'json',
        'range_extra' => 'json'
    ];

    // 父级ID字段名称，默认值为 parent_id
    protected $parentColumn = 'parent_id';

    // 排序字段名称，默认值为 order
    protected $orderColumn = 'sort';

    // 标题字段名称，默认值为 title
    protected $titleColumn = 'config_name';

    public function configValue()
    {
        return $this->belongsTo(SystemConfigValueModel::class, 'config_key', 'config_key');
    }
    public function children()
    {
        return $this->hasMany(SystemConfigModel::class, 'parent_id', 'id');
    }

}
