<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('match_stats', function (Blueprint $table) {
            $table->id();

            $table->foreignId('match_id')
                  ->constrained('matches')
                  ->cascadeOnDelete();

            $table->string('team_name');
            $table->boolean('is_home');
            $table->unsignedBigInteger('api_team_id')->nullable();

            // ── Basic Form & H2H ─────────────────────────────────
            $table->float('recent_form_score')->comment('Avg points over last 5 games (0-3)');
            $table->float('h2h_win_rate')->comment('Win % against opponent in last 5 meetings');

            // ── Advanced Tactical Metrics ────────────────────────
            $table->float('xg_for')->comment('Expected Goals generated per game');
            $table->float('xg_against')->comment('Expected Goals conceded per game');
            $table->float('shots_on_target_ratio')->comment('SOT / total shots ratio');
            $table->float('ppda')->comment('Passes Allowed Per Defensive Action');

            // ── Squad Availability & Fatigue ─────────────────────
            $table->integer('rest_days')->comment('Days since last competitive match');
            $table->integer('key_player_absence')->comment('Number of key players missing');
            $table->integer('distance_travelled_km')->default(0)->comment('Travel distance (0 for home)');

            // ── Contextual & Psychological ───────────────────────
            $table->integer('points_difference')->comment('Points gap in league standings');
            $table->integer('match_stakes')->default(1)->comment('1: Normal, 2: Relegation, 3: Title/Derby');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('match_stats');
    }
};
