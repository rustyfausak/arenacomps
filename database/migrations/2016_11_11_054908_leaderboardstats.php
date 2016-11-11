<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Leaderboardstats extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('leaderboard_id')->unsigned();
            $table->integer('role_id')->unsigned()->nullable()->default(null);
            $table->integer('spec_id')->unsigned()->nullable()->default(null);
            $table->integer('race_id')->unsigned()->nullable()->default(null);
            $table->integer('num')->unsigned()->default(0);
            $table->timestamps();
            $table->index('leaderboard_id');
            $table->index('role_id');
            $table->index('spec_id');
            $table->index('race_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('reps');
    }
}
