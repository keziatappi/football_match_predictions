<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matches', function (Blueprint $table) {
            $table->id();
            $table->string('home_team');
            $table->string('away_team');
            $table->string('league');
            $table->dateTime('match_date');
            $table->enum('status', ['upcoming', 'completed'])->default('upcoming');
            $table->string('home_logo')->nullable();
            $table->string('away_logo')->nullable();
            $table->unsignedBigInteger('api_fixture_id')->unique()->nullable();
            $table->unsignedBigInteger('api_league_id')->nullable();
            $table->unsignedInteger('season')->nullable();
            $table->string('league_logo')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matches');
    }
};
