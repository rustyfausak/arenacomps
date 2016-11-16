<?php

use App\Models\Bracket;

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Removefivevfive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Bracket::where('name', '=', '5v5')->delete();
        Schema::table('comps', function (Blueprint $table) {
            $table->dropIndex(['spec_id4']);
            $table->dropColumn('spec_id4');
            $table->dropIndex(['spec_id5']);
            $table->dropColumn('spec_id5');
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->dropIndex(['player_id4']);
            $table->dropColumn('player_id4');
            $table->dropIndex(['player_id5']);
            $table->dropColumn('player_id5');
        });
        Schema::table('performance', function ($table) {
            $table->integer('num_teams')->unsigned()->nullable()->default(null);
            $table->index('num_teams');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('comps', function (Blueprint $table) {
            $table->integer('spec_id4')->unsigned()->nullable()->default(null);
            $table->index('spec_id4');
            $table->integer('spec_id5')->unsigned()->nullable()->default(null);
            $table->index('spec_id5');
        });
        Schema::table('performance', function (Blueprint $table) {
            $table->dropIndex(['num_teams']);
            $table->dropColumn('num_teams');
        });
        Schema::table('teams', function (Blueprint $table) {
            $table->integer('player_id4')->unsigned()->nullable()->default(null);
            $table->index('player_id4');
            $table->integer('player_id5')->unsigned()->nullable()->default(null);
            $table->index('player_id5');
        });
    }
}
