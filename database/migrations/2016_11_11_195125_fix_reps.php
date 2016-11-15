<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FixReps extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        DB::table('reps')->truncate();
        Schema::table('reps', function ($table) {
            $table->dropIndex(['leaderboard_id']);
            $table->dropColumn('leaderboard_id');
            $table->date('for_date');
            $table->integer('num_leaderboards')->unsigned()->default(0);
            $table->integer('bracket_id')->unsigned();
            $table->integer('region_id')->unsigned()->nullable()->default(null);
            $table->index('for_date');
            $table->index('bracket_id');
            $table->index('region_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('reps', function ($table) {
            $table->integer('leaderboard_id')->unsigned();
            $table->index('leaderboard_id');
            $table->dropIndex(['for_date']);
            $table->dropColumn('for_date');
            $table->dropColumn('num_leaderboards');
            $table->dropIndex(['bracket_id']);
            $table->dropColumn('bracket_id');
            $table->dropIndex(['region_id']);
            $table->dropColumn('region_id');
        });
    }
}
