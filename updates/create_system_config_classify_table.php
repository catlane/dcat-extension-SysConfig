<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemConfigClassifyTable extends Migration
{
    public $withinTransaction = FALSE;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('system_config_classify')) {

            Schema::create('system_config_classify', function (Blueprint $table) {
                $table->increments('id');
                $table->string('classify_name')->default('')
                    ->comment('分类名称');
                $table->integer('sort')->default('0')
                    ->comment('排序(降序)');
                $table->integer('scene')->default('0')
                    ->comment('场景 0默认后台;1前台首页..');
                $table->timestamps();
            });
        }
    }


    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

        Schema::dropIfExists('system_config_classify');
    }
}
