<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifyTodosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('todos');

        Schema::create('todos', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->index()->comment('유저식별코드');
            $table->longText('text')->nullable()->comment('할일 내용');
            $table->tinyInteger('state')->default(1)->comment('1: 미완료, 2: 완료');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
