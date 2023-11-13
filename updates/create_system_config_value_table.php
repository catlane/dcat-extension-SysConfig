<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSystemConfigValueTable extends Migration
{
    public $withinTransaction = FALSE;
    // 这里可以指定你的数据库连接
    public function getConnection()
    {
        return config('database.connection') ?: config('database.default');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (! Schema::hasTable('system_config_value')) {
            Schema::create('system_config_value', function (Blueprint $table) {

                $table->increments('id');
                $table->string('config_key')->default('')->comment('配置key');
                $table->longText('value')->comment('配置内容');
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
        Schema::dropIfExists('system_config_value');
    }
}
