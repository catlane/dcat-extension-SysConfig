<?php

use Catlane\SysConfig\Models\SystemConfigValueModel;
use \Illuminate\Support\Facades\Redis;
if (!function_exists('sys_config')) {
    function sys_config($configKey, $defaultRes = NULL)
    {
        $keyName = '';
        if (strpos($configKey, '.') !== FALSE) {
            $keyName = substr($configKey, strpos($configKey,'.') + 1);
            $configKey = substr($configKey, 0, strpos($configKey,'.'));
        }

        $cacheData = Redis::hget('system_config', $configKey);
        if (!$cacheData) {
            $result = SystemConfigValueModel::leftJoin('system_config', 'system_config_value.config_key','=','system_config.config_key')->where('system_config_value.config_key', $configKey)
                ->select(['system_config_value.value', 'system_config.type','system_config.extra'])
                ->first();
            if (!$result) {
                return $defaultRes;
            }

            if ($result->type == 6) {
                $primaryKey = '';
                $extra = json_decode($result->extra, TRUE);
                foreach ($extra as $k => $v) {
                    if ($v['primary_key']) {
                        $primaryKey = $v['key'];
                    }
                }
                $value = json_decode($result->value, TRUE) ?? [];

                $newArr = [];
                foreach ($value as $item) {
                    $newArr[$item[$primaryKey]] = $item;
                }
                $value = json_encode($newArr);
            }elseif($result->type == 7){
                $value = explode(';', $result->value);
                $value = json_encode($value);
            }else{
                $value = $result->value;
            }
            Redis::hset('system_config', $configKey, $value);
            $cacheData = $value;
        }
        $newValue = json_decode($cacheData, TRUE);
        if (is_array($newValue)) {
            if ($keyName) {
                return $newValue[$keyName] ?? $defaultRes;
            }
            return $newValue ?? $defaultRes;
        }

        return $cacheData ?? $defaultRes;
    }
}
