<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWorkRequestTimesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('work_request_times', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_request_id')->constrained()->cascadeOnDelete();
            $table->foreignId('rest_id')->constrained()->cascadeOnDelete()->nullable();
            $table->tinyInteger('status')->comment('0:勤務外, 1:勤務中, 2:休憩開始, 3:休憩終了');
            $table->dateTime('before_time');
            $table->dateTime('after_time');
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
        Schema::dropIfExists('work_request_times');
    }
}
