<?php

namespace Catlane\SysConfig\Http\Controllers;

use Catlane\SysConfig\Models\SystemConfigClassifyModel;
use Catlane\SysConfig\Models\SystemConfigModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class SystemConfigController extends AdminController
{
    protected $title = '系统设置-配置设置';
    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemConfigModel(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('config_name', __('配置名称'));
            $grid->column('config_key', __('Config key'));
            $grid->column('required', __('是否必填'))->display(function(){
                return $this->required ? '是' : '否';
            });
            $grid->column('config_classify_id', __('配置分类'))->display(function(){
                return SystemConfigClassifyModel::where('id', $this->config_classify_id)->value('classify_name') ?? '';
                return $this->classify_name;
            });
            $grid->column('type', __('类型'))->display(function(){
                switch ($this->type) {
                    case 1:
                        return '文本框';
                    case 2:
                        return '数字框';
                    case 3:
                        return '文本域';
                    case 4:
                        return '富文本';
                    case 5:
                        return '图片';
                    case 6:
                        return 'Json';
                }
            });
            $grid->column('created_at', __('创建时间'));
            $grid->column('updated_at', __('编辑时间'));

            $grid->disableViewButton();
            $grid->filter(function (Grid\Filter $filter) {
//                $filter->disableIdFilter();

                $filter->like('config_name', '配置名称');
//                $filter->expand();

            });
        });
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     *
     * @return Show
     */
    protected function detail($id)
    {
        return Show::make($id, new SystemConfigModel(), function (Show $show) {
            $show->field('id');
            $show->field('config_name');
            $show->field('config_key');
            $show->field('required');
            $show->field('config_classify_id');
            $show->field('type');
            $show->field('help');
            $show->field('created_at');
            $show->field('updated_at');
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Form::make(new SystemConfigModel(), function (Form $form) {
            $form->display('id');
            $form->text('config_name', __('配置名称'))->required();
            $form->text('help', __('提示内容'))->default('');
            if ($form->isCreating()) {
                $form->text('config_key', 'Config Key')
                    ->creationRules(['required', "unique:system_config"])
                    ->updateRules(['required', "unique:system_config,config_key,{{id}}"])
                    ->required();
            }else{
                $form->text('config_key', 'Config Key')
                    ->creationRules(['required', "unique:system_config"])
                    ->updateRules(['required', "unique:system_config,config_key,{{id}}"])
                    ->disable();
            }
            $form->switch('required', __('是否必填'))->default(1);


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

            $form->select('config_classify_id', __('配置分类'))->options($classifyList)->required();

            $form->select('type', __('类型'))->options([
                1 => '文本框',
                2 => '数字框',
                3 => '文本域',
                4 => '富文本',
                5 => '图片上传',
                6 => 'json',
                7 => '双范围选择',
                8 => '密码',
                9 => '开关',
                10 => '下拉框'
            ])->required()->when(6, function (Form $form) {
                $form->divider();
                $form->table('extra','json键值', function (Form\NestedForm $table) {
                    $table->text('key', '键值');
                    $table->text('label', '标签');
                    $table->text('help', '提示信息');
                    $table->switch('required', '必填');
                    $table->switch('primary_key', '主键(数组key为当前值)');
                });
            })->when(7, function (Form $form) {
                $form->divider();


                $form->range("range_extra.start", "range_extra.end", '范围值');
                $form->text('range_extra.desc', '范围名称');
            })->when(10, function (Form $form) {
                $form->divider();


                $form->table('extra','下拉框选项', function (Form\NestedForm $table) {
                    $table->text('key', '键值(key)');
                    $table->text('label', '标签(value)');
                });
            });;


            $form->saving(function (Form $form) {

                if (strpos($form->config_key, '.')) {
                    return $form->response()->error('Config Key 不能包含英文.');
                }

                if ($form->type == 6) {
                    if (!$form->extra) {
                        return $form->response()->error('Json键值不能为空');
                    }
                    $primaryKeyTotal = 0;
                    foreach ($form->extra as $item) {
                        if ($item['primary_key']) {
                            $primaryKeyTotal ++;
                        }
                        if (!$item['key']) {
                            return $form->response()->error('Json键值中键值项不可为空');
                        }
                        if ($item['primary_key'] && !$item['required']) {
                            return $form->response()->error('Json键值：' . $item['key'] . '为主键，必须选择必填');
                        }
                    }
                    if ($primaryKeyTotal > 1) {
                        return $form->response()->error('Json键值主键只能选择一个');
                    }
                    if ($primaryKeyTotal == 0) {
                        return $form->response()->error('Json键值主键必须选择一个');
                    }
                }

                if ($form->type == 7) {
                    if (is_null($form->input('range_extra.start')) || is_null($form->input('range_extra.end'))) {
                        return $form->response()->error('范围值必填');
                    }
                    if ($form->input('range_extra.start') > $form->input('range_extra.end')) {
                        return $form->response()->error('范围值开始数字不能大于结束数字');
                    }
                }
                if ($form->type == 10) {
                    if (!$form->extra) {
                        return $form->response()->error('下拉框选项不能为空');
                    }
                }

                if (!in_array($form->type, [6,10])) {
                    $form->deleteInput('extra');
                }
                if ($form->type != 7) {
                    $form->deleteInput('range_extra');
                }
                // 中断后续逻辑

            });




            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableResetButton();
            $form->disableViewCheck();
        });
    }
}
