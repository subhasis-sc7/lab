<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTLcFlaggedMgitFurtherTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('t_lc_flagged_mgit_further', function (Blueprint $table) {
       $table->increments('id');
        $table->integer('enroll_id');
        $table->integer('sample_id');
        $table->string('ict');
        $table->string('culture_smear');
        $table->string('bhi');
        $table->string('result');
        $table->string('result_date');
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
        Schema::dropIfExists('t_lc_flagged_mgit_further');
    }
}
