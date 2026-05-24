<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchStat extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'team_name',
        'is_home',
        'api_team_id',
        'recent_form_score',
        'h2h_win_rate',
        'xg_for',
        'xg_against',
        'shots_on_target_ratio',
        'ppda',
        'rest_days',
        'key_player_absence',
        'distance_travelled_km',
        'points_difference',
        'match_stakes',
    ];

    protected function casts(): array
    {
        return [
            'is_home'              => 'boolean',
            'recent_form_score'    => 'float',
            'h2h_win_rate'         => 'float',
            'xg_for'               => 'float',
            'xg_against'           => 'float',
            'shots_on_target_ratio' => 'float',
            'ppda'                 => 'float',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function match(): BelongsTo
    {
        return $this->belongsTo(FootballMatch::class, 'match_id');
    }
}
