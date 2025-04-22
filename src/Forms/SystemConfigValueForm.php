<?php

namespace Catlane\DcatSysConfig\Forms;

use Catlane\DcatSysConfig\Models\SystemConfigModel;
use Catlane\DcatSysConfig\Models\SystemConfigValueModel;
use Dcat\Admin\Form\NestedForm;
use Dcat\Admin\Widgets\Form;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;
use OSS\OssClient;

class SystemConfigValueForm extends Form
{
    protected $activeId = 0;
    public function __construct($data = [], $key = NULL)
    {
        $this->activeId = $data['activeId'] ?? 0;
        parent::__construct($data, $key);
    }


    /**
     * Handle the form request.
     *
     * @param array $input
     *
     * @return mixed
     */
    public function handle(array $input)
    {
        $data = request()->all();
        $request = request();
        try {
            //初始化下提交参数,去除无效的
            foreach ($data as $k => $v) {
                if (strpos( $k,'_') === 0) {
                    unset($data[$k]);
                    continue;
                }
                if (strpos( $k,'.') !== FALSE) {
                    $data[substr($k, 0, strpos($k,'.'))][] = $v;
                    unset($data[$k]);
                    continue;
                }
            }
            foreach ($data as $k => $v) {


                $keys = SystemConfigModel::where('config_key', $k)->first();
                if (!$keys) {
                    continue;
                }
                if ($keys->required && $v === null) {
                    throw new \Exception($keys->config_name . '不能为空');
                }


                $configValueModel = SystemConfigValueModel::where('config_key', $k)->first();

                if (!$configValueModel) {
                    $configValueModel = new SystemConfigValueModel();
                    $configValueModel->config_key = $k;
                }
                if ($keys->type == 5) {
                    //暂时放弃图片上传
                    //                    if ($request->file($k) === null) {
                    //                        $configValueModel->value = $v;
                    //                    }else{
                    //                        $content = $request->file($k)->getContent();
                    //                        $type = $request->file($k)->getMimeType();
                    //                        $type = explode('/', $type);
                    //                        $type = $type[1] ?? 'png';
                    //                        $ossClient = new OssClient('', '', '', false);
                    //                        $bucket = 'ussms';
                    //                        $fileName = md5(time()) .'.'. $type;
                    //                        $result = $ossClient->putObject($bucket, 'images/' . $fileName, $content);
                    //                        $configValueModel->value = 'images/'.$fileName;
                    //                    }

                }elseif ($keys->type == 6) {

                    foreach ($v as $kk => $vv) {
                        if (isset($v[$kk]['_remove_']) ) unset($v[$kk]['_remove_']);
                        if ($vv['_remove_']) {
                            unset($v[$kk]);
                            continue;
                        }
                    }

                    $v = array_values($v);
                    $newV = [];
                    //重新排序
                    for ($i = 1;$i<=count($v);$i++){
                        $newV["new_{$i}"] = $v[$i - 1];
                    }
                    $configValueModel->value = $newV;

                }elseif (in_array($keys->type, [10,12,13,14])){
                    foreach ($v as $kkk => $vvv){
                        if ($vvv === null) {
                            unset($v[$kkk]);
                        }
                    }
                    $configValueModel->value = $v;
                }else{
                    $configValueModel->value = trim($v) ?? '';
                }

                $configValueModel->save();
            }
            DB::commit();
            Redis::del('system_config');
            return $this->response()->success('保存成功.');
        } catch (\Exception $exception) {
            DB::rollBack();
            return $this->response()->success('保存失败.' . $exception->getMessage());
        }
    }




    /**
     * Build a form here.
     */
    public function form()
    {
        $configGroup = SystemConfigModel::where('parent_id', $this->activeId)
                                        ->orderBy('sort', 'asc')
                                        ->with([
                                            'configValue',
                                            'children' => function ($query) {
                                                $query->with(['configValue']);
                                            }
                                        ])
                                        ->get()
                                        ->toArray();


        foreach ($configGroup as $item) {
            if ($item['config_type'] == 0) {
                $input = $this->renderInput($item);
                continue;
            }
            $this->fieldset($item['config_name'], function (Form $form) use ($item) {

                foreach ($item['children'] as $child) {
                    if ($child['config_type'] == 1) {
                        continue;
                    }
                    $this->renderInput($child);
                }

            });
        }





        $this->disableResetButton();
    }



    protected function renderInput($value)
    {
        $input = null;
        $value['value'] = $value['config_value']['value'] ?? NULL;
        switch ($value['type']) {
            case 1:
                $input = $this->text($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 2:
                $input = $this->number($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 3:
                $input = $this->textarea($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 4:
                $input = $this->editor($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 5:
                $input = $this->image($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 6:
                $defaultValue = json_decode($value['value'], TRUE);
                $this->table($value['config_key'],$value['config_name'], function (\Dcat\Admin\Form\NestedForm $table) use ($value) {
                    foreach ($value['extra'] as $item) {
                        if ($item['required']) {
                            $text = $table->text($item['key'], $item['label'] ??'')->required($item['required']);
                        }else{
                            $text = $table->text($item['key'], $item['label'] ??'');
                        }
                        if (!empty($item['help'])) {
                            $text->help($item['help'] ?? '');
                        }
                    }
                })->default($defaultValue);
                break;
            case 7:

                $rangValue = is_null($value['value']) ? [] : explode(';', $value['value']);

                $this->slider($value['config_key'], $value['config_name'])->options([
                    'max'     => intval($value['extra']['end'] ?? 0),
                    'min'     => intval($value['extra']['start'] ?? 0),
                    'step'    => 1,
                    'postfix' => $value['extra']['desc'] ?? '%',
                    'type' => 'double',
                    'drag_interval' => TRUE,
                    'from' => intval(isset($rangValue[0]) ? $rangValue[0] : intval($value['extra']['start'] ?? 0)),
                    'to' => intval(isset($rangValue[1]) ? $rangValue[1] : intval($value['extra']['end'] ?? 0))
                ])->render();
                break;
            case 8:
                $input = $this->password($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 9:
                $input = $this->switch($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                break;
            case 10:
            case 14:
                $options = [];
                foreach ($value['extra'] as $v) {
                    $options[$v['key']] = $v['label'];
                }

                if ($value['type'] == 10){
                    $input = $this->select($value['config_key'], $value['config_name'])->default($value['value'] ?? '')->options($options)->default($value['value'] ?? '');
                }else{
                    $input = $this->multipleSelect($value['config_key'], $value['config_name'])->default($value['value'] ?? '')->options($options)->default($value['value'] ?? '');
                }
                break;

            case 11:
                $options = [];
                foreach ($value['extra'] as $v) {
                    $options[$v['key']] = $v['label'];
                }

                $input = $this->radio($value['config_key'], $value['config_name'])->default($value['value'] ?? '')->options($options)->default($value['value'] ?? '');
                break;

            case 12:
                $options = [];
                foreach ($value['extra'] as $v) {
                    $options[$v['key']] = $v['label'];
                }

                $input = $this->checkbox($value['config_key'], $value['config_name'])->default($value['value'] ?? '')->options($options)->default($value['value'] ?? '');
                break;
            case 13:

                $defaultValue = json_decode($value['value'], TRUE);
                $this->embeds($value['config_key'],$value['config_name'], function ($form) use ($value,$defaultValue) {
                    foreach ($value['extra'] as $item) {
                        $input = $form->{$item['type']}($item['key'], $item['label'] ??'');
                        if($item['required']){
                            $input =  $input->required();
                        }
                        if($item['help']){
                            $input = $input->help($item['help']);
                        }
                        if (isset($defaultValue[$item['key']])) {
                            $input->default($defaultValue[$item['key']]);
                        }
                    }
                });
                break;


        }
        if ($value['required'] && $input && $value['type'] != 9) {
            $input->required();
        }
        if ($value['help']) {
            if ($input) {
                $input->help($value['help']);
            }else{
                $this->html("<div style='color: #737373'><i class=\"fa feather icon-help-circle\"></i>{$value['help']}</div>");
            }
        }

        return $input;
    }
}
