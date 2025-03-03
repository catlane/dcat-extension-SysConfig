<?php

namespace Catlane\SysConfig\Http\Controllers;

use Catlane\SysConfig\Models\SystemConfigModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Layout\Column;
use Dcat\Admin\Show;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Tree;
use Dcat\Admin\Layout\Row;
use Dcat\Admin\Widgets\Box;
use Dcat\Admin\Widgets\Form  as WidgetsForm;
use Dcat\Admin\Form\NestedForm;

class SystemConfigController extends AdminController
{
    protected $title = '系统设置-配置设置';


    public function index(Content $content)
    {

        return $content->header($this->title())
            ->body(function (Row $row) {
                $tree = new Tree(new SystemConfigModel);

                $row->column(12, $tree);
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

            if ($form->isEditing()) {
                if (in_array($form->model()->type, [6,7,10,11,12])){
                    $extraName = "extra{$form->model()->type}";
                    $form->model()->$extraName = $form->model()->extra;
                    $form->model()->extra = '';
                }

            }
            $form->display('id');
            $form->select('parent_id', '父级')->options(SystemConfigModel::selectOptions(function($query){
                return $query::where('config_type', '<>', 0);
            }))->required();
            $disable = false;
            if (!$form->isCreating()) {
                $disable = TRUE;
            }
            $form->radio('config_type', '类型')->options([
                0 => '配置',
                1 => '分组'
            ])
                ->when(0, function (Form $form) {
                $form->text('config_name', __('配置名称'));
                if ($form->isCreating()) {
                    $form->text('config_key', 'Config Key')
                        ->creationRules([ "unique:system_config"])
                        ->updateRules(['required', "unique:system_config,config_key,{{id}}"]);
                }else{
                    $form->text('config_key', 'Config Key')
                        ->updateRules(["unique:system_config,config_key,{{id}}"])
                        ->disable();
                }

                $form->text('help', __('提示内容'))->default('');
                $form->switch('required', __('是否必填'))->default(1);
                $form->select('type', __('类型'))->options([
                    1 => '文本框',
                    2 => '数字框',
                    3 => '文本域',
                    4 => '富文本',
//                    5 => '图片上传',
                    13 => 'json固定值对象',
                    6 => 'json数组',
                    7 => '双范围选择',
                    8 => '密码',
                    9 => '开关',
                    10 => '下拉框',
                    11 => '单选框',
                    12 => '复选框',
                ])
                    ->when(6, function (Form $form) {
                        $form->divider();
                        $form->table('extra6','json键值', function (Form\NestedForm $table) {
                            $table->text('key', '键值');
                            $table->text('label', '标签');
                            $table->text('help', '提示信息');
                            $table->switch('required', '必填');
                            $table->switch('primary_key', '主键(数组key为当前值)');
                        });
                    })
                    ->when(7, function (Form $form) {
                        $form->divider();


                        $form->range("extra7.start", "extra7.end", '范围值');
                        $form->text('extra7.desc', '范围名称');
                    })
                    ->when(10, function (Form $form) {
                        $form->divider();


                        $form->table('extra10','下拉框选项', function (Form\NestedForm $table) {
                            $table->text('key', '键值(key)');
                            $table->text('label', '标签(value)');
                        });
                    })
                    ->when(11, function (Form $form) {
                        $form->divider();


                        $form->table('extra11','单选选项', function (Form\NestedForm $table) {
                            $table->text('key', '键值(key)');
                            $table->text('label', '标签(value)');
                        });
                    })
                    ->when(12, function (Form $form) {
                        $form->divider();


                        $form->table('extra12','复选框选项', function (Form\NestedForm $table) {
                            $table->text('key', '键值(key)');
                            $table->text('label', '标签(value)');
                        });
                    })
                    ->when(13, function (Form $form) {
                        $form->divider();
                        $form->table('extra13','json键值', function (Form\NestedForm $table) {
                            $table->text('key', '键值');
                            $table->text('label', '标签');
                            $table->text('help', '提示信息');
                            $table->switch('required', '必填');
                            $table->select('type', '类型')->options([
                                'text' => '文本框',
                                'number' => '数字框',
                                'url' => '网址',
                                'textarea' => '文本域',
                                'password' => '密码',
                                'switch' => '开关',
                            ]);
                        });
                    });
            })
                ->when(1, function (Form $form) {
                    $form->text('config_name_1', __('分组名称'))->value($form->model()->config_name);
                })
                ->default(0)->disable($disable);


            $form->hidden('extra');
            $form->submitted(function (Form $form) {

            });
            $form->saving(function (Form $form) {
                if ($form->isEditing()) {
                    $form->deleteInput('config_type');
                }
                /**
                 * 分组配置
                 */
                if ($form->model()->config_type || $form->input('config_type')){
                    $form->input('config_key','');
                    $form->input('type',0);
                    $form->input('help','');
                    $form->input('required',0);
                    $form->input('config_name',$form->config_name_1);
                }else{
                    if (strpos($form->config_key, '.')) {
                        return $form->response()->error('Config Key 不能包含英文.');
                    }
                    if (in_array($form->type, [6,7,10,11,12,13])){
                        $form->input('extra', $form->input("extra{$form->type}"));
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

                    if ($form->type == 13) {
                        if (!$form->extra) {
                            return $form->response()->error('Json键值不能为空');
                        }
                        foreach ($form->extra as $item) {
                            if (!$item['key']) {
                                return $form->response()->error('Json键值中“键值项”不可为空');
                            }
                            if (!$item['label']) {
                                return $form->response()->error('Json键值中“标签项”不可为空');
                            }
                            if (!$item['type']) {
                                return $form->response()->error('Json键值中“类型项”不可为空');
                            }
                        }

                    }

                    if ($form->type == 7) {
                        if (is_null($form->input('extra.start')) || is_null($form->input('extra.end'))) {
                            return $form->response()->error('范围值必填');
                        }
                        if ($form->input('extra.start') > $form->input('extra.end')) {
                            return $form->response()->error('范围值开始数字不能大于结束数字');
                        }
                    }
                    if ($form->type == 10 || $form->type == 11 || $form->type == 12) {
                        if (!$form->extra) {
                            return $form->response()->error('选项不能为空');
                        }
                    }
                }
                $form->deleteInput('config_name_1');
                $form->deleteInput('extra6');
                $form->deleteInput('extra7');
                $form->deleteInput('extra10');
                $form->deleteInput('extra11');
                $form->deleteInput('extra12');
                $form->deleteInput('extra13');

            });




            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableResetButton();
            $form->disableViewCheck();
        });
    }

    public function update($id)
    {
        try {

            $request = request();
            $model = SystemConfigModel::where('id', $id)->first();

            if ($request->parent_id === NULL) {
                throw new \Exception('请选择父级');
            }

            if ($model->config_type == 1) {
                if (!$request->config_name_1) {
                    throw new \Exception('请填写分组名称');
                }
                SystemConfigModel::where('id', $id)->update([
                    'parent_id' => $request->parent_id,
                    'config_name'  => $request->config_name_1
                ]);
                return $this->form()->response()->success('修改成功')->location('sys/sys-config');
            }



            if (in_array($model->type, [6,7,10,11,12])){
                $extraData = $request->post("extra{$model->type}");
                foreach ($extraData as $k=> $datum) {
                    if (isset($datum['_remove_']) && $datum['_remove_'] == 1) {
                        unset($extraData[$k]);
                    }
                }
                $request->merge([
                    'extra' => $extraData
                ]);
            }

            if ($model->type == 6) {

                if (!$request->extra) {
                    return $this->form()->response()->error('Json键值不能为空');
                }
                $primaryKeyTotal = 0;
                foreach ($request->extra as $item) {
                    if ($item['primary_key']) {
                        $primaryKeyTotal ++;
                    }
                    if (!$item['key']) {
                        return $this->form()->response()->error('Json键值中键值项不可为空');
                    }
                    if ($item['primary_key'] && !$item['required']) {
                        return $this->form()->response()->error('Json键值：' . $item['key'] . '为主键，必须选择必填');
                    }
                }
                if ($primaryKeyTotal > 1) {
                    return $this->form()->response()->error('Json键值主键只能选择一个');
                }
                if ($primaryKeyTotal == 0) {
                    return $this->form()->response()->error('Json键值主键必须选择一个');
                }

            }

            if ($model->type == 7) {
                if (is_null($request->extra['start']) || is_null($request->extra['end'])) {
                    return $this->form()->response()->error('范围值必填');
                }
                if ($request->extra['start'] > $request->extra['end']) {
                    return $this->form()->response()->error('范围值开始数字不能大于结束数字');
                }
            }
            if ($model->type == 10 || $model->type == 11 || $model->type == 12) {
                if (!$request->extra) {
                    return $this->form()->response()->error('选项不能为空');
                }
            }


            $requestData = [
                'parent_id' => $request->parent_id,
                'config_name' => $request->config_name,
                'help' => $request->help,
                'required' => $request->required,
                'type' => $request->type,
                'extra' => $request->extra,
            ];

            SystemConfigModel::where('id', $id)->update($requestData);
            return $this->form()->response()->success('修改成功')->location('sys/sys-config');
        } catch (\Exception $exception) {
            return $this->form()->response()->error($exception->getMessage());
        }

    }
}
