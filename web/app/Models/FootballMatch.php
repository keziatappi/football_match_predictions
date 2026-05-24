<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class FootballMatch extends Model
{
    use HasFactory;

    protected $table = 'matches';

    protected $fillable = [
        'home_team',
        'away_team',
        'league',
        'match_date',
        'status',
        'home_logo',
        'away_logo',
        'api_fixture_id',
        'api_league_id',
        'season',
        'league_logo',
    ];

    protected function casts(): array
    {
        return [
            'match_date' => 'datetime',
        ];
    }

    // ── Relationships ────────────────────────────────────────────

    public function stats(): HasMany
    {
        return $this->hasMany(MatchStat::class, 'match_id');
    }

    public function homeStat(): HasOne
    {
        return $this->hasOne(MatchStat::class, 'match_id')->where('is_home', true);
    }

    public function awayStat(): HasOne
    {
        return $this->hasOne(MatchStat::class, 'match_id')->where('is_home', false);
    }
}
