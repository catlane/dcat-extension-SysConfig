<?php

namespace Catlane\SysConfig\Forms;

use Catlane\SysConfig\Models\SystemConfigModel;
use Catlane\SysConfig\Models\SystemConfigValueModel;
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
//                if (is_array($v)) {
//                    foreach ($v as $key => $value) {
//                        $data["{$k}.{$key}"] = $value;
//                    }
//                    unset($data[$k]);
//                }
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
                    if ($request->file($k) === null) {
                        $configValueModel->value = $v;
                    }else{
                        $content = $request->file($k)->getContent();
                        $type = $request->file($k)->getMimeType();
                        $type = explode('/', $type);
                        $type = $type[1] ?? 'png';
                        $ossClient = new OssClient('', '', '', false);
                        $bucket = 'ussms';
                        $fileName = md5(time()) .'.'. $type;
                        $result = $ossClient->putObject($bucket, 'images/' . $fileName, $content);
                        $configValueModel->value = 'images/'.$fileName;
                    }

                }elseif ($keys->type == 6) {
                    foreach ($v as $kk => $vv) {
                        if (isset($v[$kk]['_remove_']) ) unset($v[$kk]['_remove_']);
                        if ($vv['_remove_']) {
                            unset($v[$kk]);
                            continue;
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
        $config = SystemConfigModel::leftJoin('system_config_value', 'system_config.config_key', '=', 'system_config_value.config_key')
            ->where('config_classify_id', $this->activeId)
            ->select([
                'system_config.*',
                'system_config_value.value'
            ])
            ->get()
            ->toArray();


        foreach ($config as $value) {
            $input = null;
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
                        'max'     => intval($value['range_extra']['end'] ?? 0),
                        'min'     => intval($value['range_extra']['start'] ?? 0),
                        'step'    => 1,
                        'postfix' => $value['range_extra']['desc'] ?? '%',
                        'type' => 'double',
                        'drag_interval' => TRUE,
                        'from' => intval(isset($rangValue[0]) ? $rangValue[0] : intval($value['range_extra']['start'] ?? 0)),
                        'to' => intval(isset($rangValue[1]) ? $rangValue[1] : intval($value['range_extra']['end'] ?? 0))
                    ])->render();
                    break;
                case 8:
                    $input = $this->password($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                    break;
                case 9:
                    $input = $this->switch($value['config_key'], $value['config_name'])->default($value['value'] ?? '');
                    break;

            }
            if ($value['required'] && $input) {
                $input->required();
            }
            if ($input && $value['help']) {
                $input->help($value['help']);
            }
        }

        $this->disableResetButton();
    }
}
