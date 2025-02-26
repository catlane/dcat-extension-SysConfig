<?php

namespace Catlane\SysConfig\Http\Controllers;

use Catlane\SysConfig\Forms\SettingForm;
use Catlane\SysConfig\Forms\SystemConfigValueForm;
use Catlane\SysConfig\Models\SystemConfigModel;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Tab;


class SystemConfigValueController extends AdminController
{

    public function index(Content $content)
    {

        $tab = Tab::make();

        $request = request();


        $classifyList = SystemConfigModel::where('config_type', 1)
            ->where('parent_id', 0)
            ->orderBy('sort', 'asc')
            ->select(['id','config_name'])
            ->get()
            ->keyBy('id')->toArray();


        foreach ($classifyList as $k => $value) {
            $classifyList[$k] = $value['config_name'];
        }







        foreach ($classifyList as $id => $name) {

            $isCheck = FALSE;
            if ($id == array_key_first($classifyList)) {
                $isCheck = TRUE;
            }
            $tab->add($name, new SystemConfigValueForm([
                'activeId' => $id
            ]), $isCheck, $id);

        }

        return $content
            ->title('配置项设置')
            ->body($tab->withCard());

    }
}
