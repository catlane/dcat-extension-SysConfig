<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemConfigTable extends Migration
{
    public $withinTransaction = FALSE;
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('system_config')) {
            Schema::create('system_config', function (Blueprint $table) {

                $table->increments('id');
                $table->string('config_name')->default('')->comment('配置名称');
                $table->string('config_key')->default('')->comment('配置key');
                $table->tinyInteger('required')->default('0')
                    ->comment('是否必填[1必填 0非必填]');
                $table->integer('config_classify_id')->default('0')
                    ->comment('分类id');
                $table->integer('type')->default('0')
                    ->comment('1文本框,2数字框,3文本域,4富文本,5图片上传');
                $table->string('help')->nullable()->comment('help内容');
                $table->text('extra')->comment('json键值');
                $table->string('range_extra')->nullable(FALSE)->default('[]')->comment('json键值');
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
        Schema::dropIfExists('system_config');
    }
}
