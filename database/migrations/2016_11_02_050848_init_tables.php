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
            $table->unique('name');
        });
        Schema::create('factions', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->increments('id');
            $table->string('name');
            $table->unique('name');
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
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('leaderboards');
        Schema::dropIfExists('regions');
        Schema::dropIfExists('brackets');
        Schema::dropIfExists('factions');
        Schema::dropIfExists('races');
        Schema::dropIfExists('realms');
        Schema::dropIfExists('roles');
        Schema::dropIfExists('specs');
        Schema::dropIfExists('spec_types');
    }
}
