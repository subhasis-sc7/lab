<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTPcrTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('t_pcr', function (Blueprint $table) {
         $table->increments('id');
          $table->integer('enroll_id');

          $table->integer('sample_id');

          $table->integer('completed');
          $table->string('test_date');
          $table->integer('status')->default(1);
          $table->integer('created_by');
          $table->integer('updated_by');
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
         Schema::dropIfExists('t_pcr');
    }
}
