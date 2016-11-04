<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InitTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('brackets', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->integer('size')->unsigned();
            $table->unique('name');
        });
        Schema::create('comps', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('spec_id1')->unsigned();
            $table->integer('spec_id2')->unsigned();
            $table->integer('spec_id3')->unsigned()->nullable()->default(null);
            $table->integer('spec_id4')->unsigned()->nullable()->default(null);
            $table->integer('spec_id5')->unsigned()->nullable()->default(null);
            $table->index('spec_id1');
            $table->index('spec_id2');
            $table->index('spec_id3');
            $table->index('spec_id4');
            $table->index('spec_id5');
        });
        Schema::create('genders', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->unique('name');
        });
        Schema::create('factions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->unique('name');
        });
        Schema::create('groups', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('leaderboard_id')->unsigned();
            $table->integer('faction_id')->unsigned();
            $table->integer('wins')->unsigned();
            $table->integer('losses')->unsigned();
            $table->index('leaderboard_id');
            $table->index('faction_id');
            $table->unique(['leaderboard_id', 'faction_id', 'wins', 'losses']);
        });
        Schema::create('leaderboards', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('region_id')->unsigned();
            $table->integer('bracket_id')->unsigned();
            $table->timestamps();
            $table->index('region_id');
            $table->index('bracket_id');
        });
        Schema::create('players', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('uid');
            $table->string('name');
            $table->integer('realm_id')->unsigned();
            $table->integer('faction_id')->unsigned();
            $table->integer('race_id')->unsigned();
            $table->integer('role_id')->unsigned();
            $table->integer('spec_id')->unsigned();
            $table->integer('gender_id')->unsigned();
            $table->index('uid');
            $table->index('realm_id');
            $table->index('faction_id');
            $table->index('race_id');
            $table->index('role_id');
            $table->index('spec_id');
            $table->index('gender_id');
            $table->unique(['uid', 'realm_id', 'faction_id']);
        });
        Schema::create('races', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('faction_id')->unsigned();
            $table->string('name');
            $table->index('faction_id');
        });
        Schema::create('regions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->unique('name');
        });
        Schema::create('realms', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('region_id')->unsigned();
            $table->string('name');
            $table->string('slug');
            $table->index('region_id');
        });
        Schema::create('roles', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->unique('name');
        });
        Schema::create('snapshots', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('player_id')->unsigned();
            $table->integer('group_id')->unsigned();
            $table->integer('spec_id')->unsigned();
            $table->integer('rating')->unsigned();
            $table->integer('team_id')->unsigned();
            $table->integer('comp_id')->unsigned();
            $table->index('player_id');
            $table->index('group_id');
            $table->index('spec_id');
            $table->index('team_id');
            $table->index('comp_id');
        });
        Schema::create('specs', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('role_id')->unsigned();
            $table->integer('spec_type_id')->unsigned();
            $table->string('name');
            $table->index('role_id');
            $table->index('spec_type_id');
        });
        Schema::create('spec_types', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
        });
        Schema::create('stats', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('player_id')->unsigned();
            $table->integer('bracket_id')->unsigned();
            $table->integer('leaderboard_id')->unsigned();
            $table->integer('ranking')->unsigned();
            $table->integer('rating')->unsigned();
            $table->integer('season_wins')->unsigned();
            $table->integer('season_losses')->unsigned();
            $table->integer('weekly_wins')->unsigned();
            $table->integer('weekly_losses')->unsigned();
            $table->index('player_id');
            $table->index('bracket_id');
            $table->index('leaderboard_id');
            $table->unique(['player_id', 'bracket_id']);
        });
        Schema::create('teams', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->integer('player_id1')->unsigned();
            $table->integer('player_id2')->unsigned();
            $table->integer('player_id3')->unsigned()->nullable()->default(null);
            $table->integer('player_id4')->unsigned()->nullable()->default(null);
            $table->integer('player_id5')->unsigned()->nullable()->default(null);
            $table->index('player_id1');
            $table->index('player_id2');
            $table->index('player_id3');
            $table->index('player_id4');
            $table->index('player_id5');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('brackets');
        Schema::dropIfExists('comps');
        Schema::dropIfExists('genders');
        Schema::dropIfExists('groups');
        Schema::dropIfExists('factions');
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('players');
        Schema::dropIfExists('races');
        Schema::dropIfExists('realms');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('snapshots');
        Schema::dropIfExists('specs');
        Schema::dropIfExists('spec_types');
        Schema::dropIfExists('stats');
        Schema::dropIfExists('teams');
    }
}
