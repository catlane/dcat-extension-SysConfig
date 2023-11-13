<?php

namespace Catlane\SysConfig\Http\Controllers;

use Catlane\SysConfig\Forms\SettingForm;
use Catlane\SysConfig\Forms\SystemConfigValueForm;
use Catlane\SysConfig\Models\SystemConfigClassifyModel;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Widgets\Tab;


class SystemConfigValueController extends AdminController
{

    public function index(Content $content)
    {

        $tab = Tab::make();

        $request = request();


        $classifyList = SystemConfigClassifyModel::orderBy('scene', 'asc')->orderBy('sort', 'asc')
            ->select(['id','classify_name', 'scene'])
            ->get()
            ->keyBy('id')->toArray();
        foreach ($classifyList as $k => $value) {
            $classifyList[$k] = $value['classify_name'];
            if ($value['scene'] == 0) {
                $classifyList[$k] .= '-后台';
            }else{
                $classifyList[$k] .='-前台';
            }
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
