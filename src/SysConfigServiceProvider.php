<?php

namespace Catlane\DcatSysConfig;

use Dcat\Admin\Extend\ServiceProvider;
use Dcat\Admin\Admin;

class SysConfigServiceProvider extends ServiceProvider
{
    protected $js = [
        'js/index.js',
    ];
    protected $css = [
        'css/index.css',
    ];


    protected $menu = [
        [
            'title' => '系统配置项',
            'uri'   => '',
            'icon'  => 'fa-gears',
        ],
        [
            'parent' => '系统配置项', // 指定父级菜单
            'title'  => '配置设置',
            'uri'    => 'sys/sys-config',
        ],
        [
            'parent' => '系统配置项', // 指定父级菜单
            'title'  => '配置项设置',
            'uri'    => 'sys/sys-config-value',
        ],

    ];
    public function register()
    {
        //
    }

    public function init()
    {
        parent::init();

        //

    }

    public function settingForm()
    {
        return new Setting($this);
    }
}
