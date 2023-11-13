<?php

namespace Catlane\SysConfig\Http\Controllers;

use Catlane\SysConfig\Models\SystemConfigClassifyModel;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Http\Controllers\AdminController;
use Dcat\Admin\Show;

class SystemConfigClassifyController extends AdminController
{
    protected $title = '系统设置-分组配置';

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Grid::make(new SystemConfigClassifyModel(), function (Grid $grid) {
            $grid->column('id')->sortable();
            $grid->column('classify_name');
            $grid->column('sort');
            $grid->column('scene')->display(function(){
                return $this->scene ? '前端配置' : '后台配置';
            })->label([
                1 => 'success',
                0 => 'warning',
            ]);;
            $grid->column('created_at');
            $grid->column('updated_at')->sortable();


            $grid->disableFilterButton();
            $grid->actions(function ($actions) {
                $actions->disableView();
            });
            $grid->filter(function (Grid\Filter $filter) {
                $filter->equal('id');
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
        return Show::make($id, new SystemConfigClassifyModel(), function (Show $show) {
            $show->field('id');
            $show->field('classify_name');
            $show->field('sort');
            $show->field('scene');
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
        return Form::make(new SystemConfigClassifyModel(), function (Form $form) {
            $form->display('id', );
            $form->text('classify_name', __('分组名称'))->required();
            $form->number('sort', __('排序'))->required()->help('正序');
            $form->radio('scene', __('场景'))->options([
                0 => '后台配置',
                1 => '前端配置'
            ])->default(0);


//            $form->table('extra', function (Form\NestedForm $table) {
//                $table->text('key');
//                $table->text('value');
//                $table->text('desc');
//            });

            $form->display('created_at');
            $form->display('updated_at');

            $form->disableCreatingCheck();
            $form->disableEditingCheck();
            $form->disableResetButton();
            $form->disableViewCheck();
        });
    }
}
