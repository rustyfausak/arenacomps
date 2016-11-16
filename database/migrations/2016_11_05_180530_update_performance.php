<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePerformance extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('performance', function (Blueprint $table) {
            $table->float('skill', 4, 2)->default(0);
            $table->integer('region_id')->unsigned()->nullable()->default(null)->after('season_id');
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
        Schema::table('performance', function (Blueprint $table) {
            $table->dropColumn('skill');
            $table->dropIndex(['region_id']);
            $table->dropColumn('region_id');
        });
    }
}
