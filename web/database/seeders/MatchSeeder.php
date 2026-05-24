<?php

namespace Database\Seeders;

use App\Models\FootballMatch;
use App\Models\MatchStat;
use Illuminate\Database\Seeder;

class MatchSeeder extends Seeder
{
    /**
     * Seed 6 realistic upcoming matches with full tactical stats.
     */
    public function run(): void
    {
        $fixtures = [
            [
                'home_team'  => 'Arsenal',
                'away_team'  => 'Chelsea',
                'league'     => 'Premier League',
                'match_date' => now()->addDays(2)->setTime(20, 0),
                'home_stat'  => [
                    'recent_form_score'     => 2.4,
                    'h2h_win_rate'          => 0.6,
                    'xg_for'                => 2.1,
                    'xg_against'            => 0.8,
                    'shots_on_target_ratio' => 0.42,
                    'ppda'                  => 8.5,
                    'rest_days'             => 5,
                    'key_player_absence'    => 1,
                    'distance_travelled_km' => 0,
                    'points_difference'     => 12,
                    'match_stakes'          => 3,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 1.8,
                    'h2h_win_rate'          => 0.2,
                    'xg_for'                => 1.6,
                    'xg_against'            => 1.2,
                    'shots_on_target_ratio' => 0.35,
                    'ppda'                  => 10.2,
                    'rest_days'             => 4,
                    'key_player_absence'    => 2,
                    'distance_travelled_km' => 15,
                    'points_difference'     => -12,
                    'match_stakes'          => 3,
                ],
            ],
            [
                'home_team'  => 'Real Madrid',
                'away_team'  => 'Barcelona',
                'league'     => 'La Liga',
                'match_date' => now()->addDays(3)->setTime(21, 0),
                'home_stat'  => [
                    'recent_form_score'     => 2.6,
                    'h2h_win_rate'          => 0.4,
                    'xg_for'                => 2.3,
                    'xg_against'            => 0.9,
                    'shots_on_target_ratio' => 0.45,
                    'ppda'                  => 9.1,
                    'rest_days'             => 6,
                    'key_player_absence'    => 0,
                    'distance_travelled_km' => 0,
                    'points_difference'     => 3,
                    'match_stakes'          => 3,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 2.8,
                    'h2h_win_rate'          => 0.4,
                    'xg_for'                => 2.5,
                    'xg_against'            => 0.7,
                    'shots_on_target_ratio' => 0.48,
                    'ppda'                  => 7.8,
                    'rest_days'             => 5,
                    'key_player_absence'    => 1,
                    'distance_travelled_km' => 620,
                    'points_difference'     => -3,
                    'match_stakes'          => 3,
                ],
            ],
            [
                'home_team'  => 'Bayern Munich',
                'away_team'  => 'Borussia Dortmund',
                'league'     => 'Bundesliga',
                'match_date' => now()->addDays(4)->setTime(18, 30),
                'home_stat'  => [
                    'recent_form_score'     => 2.2,
                    'h2h_win_rate'          => 0.6,
                    'xg_for'                => 2.4,
                    'xg_against'            => 1.0,
                    'shots_on_target_ratio' => 0.40,
                    'ppda'                  => 7.2,
                    'rest_days'             => 7,
                    'key_player_absence'    => 0,
                    'distance_travelled_km' => 0,
                    'points_difference'     => 8,
                    'match_stakes'          => 3,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 2.0,
                    'h2h_win_rate'          => 0.2,
                    'xg_for'                => 1.9,
                    'xg_against'            => 1.1,
                    'shots_on_target_ratio' => 0.38,
                    'ppda'                  => 9.5,
                    'rest_days'             => 4,
                    'key_player_absence'    => 1,
                    'distance_travelled_km' => 580,
                    'points_difference'     => -8,
                    'match_stakes'          => 3,
                ],
            ],
            [
                'home_team'  => 'AC Milan',
                'away_team'  => 'Inter Milan',
                'league'     => 'Serie A',
                'match_date' => now()->addDays(5)->setTime(20, 45),
                'home_stat'  => [
                    'recent_form_score'     => 1.6,
                    'h2h_win_rate'          => 0.4,
                    'xg_for'                => 1.5,
                    'xg_against'            => 1.3,
                    'shots_on_target_ratio' => 0.33,
                    'ppda'                  => 11.0,
                    'rest_days'             => 3,
                    'key_player_absence'    => 3,
                    'distance_travelled_km' => 0,
                    'points_difference'     => -5,
                    'match_stakes'          => 3,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 2.4,
                    'h2h_win_rate'          => 0.6,
                    'xg_for'                => 2.0,
                    'xg_against'            => 0.8,
                    'shots_on_target_ratio' => 0.41,
                    'ppda'                  => 8.0,
                    'rest_days'             => 6,
                    'key_player_absence'    => 0,
                    'distance_travelled_km' => 10,
                    'points_difference'     => 5,
                    'match_stakes'          => 3,
                ],
            ],
            [
                'home_team'  => 'PSG',
                'away_team'  => 'Marseille',
                'league'     => 'Ligue 1',
                'match_date' => now()->addDays(6)->setTime(21, 0),
                'home_stat'  => [
                    'recent_form_score'     => 2.6,
                    'h2h_win_rate'          => 0.8,
                    'xg_for'                => 2.7,
                    'xg_against'            => 0.6,
                    'shots_on_target_ratio' => 0.50,
                    'ppda'                  => 6.5,
                    'rest_days'             => 7,
                    'key_player_absence'    => 0,
                    'distance_travelled_km' => 0,
                    'points_difference'     => 20,
                    'match_stakes'          => 1,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 1.4,
                    'h2h_win_rate'          => 0.0,
                    'xg_for'                => 1.2,
                    'xg_against'            => 1.5,
                    'shots_on_target_ratio' => 0.28,
                    'ppda'                  => 12.0,
                    'rest_days'             => 3,
                    'key_player_absence'    => 2,
                    'distance_travelled_km' => 780,
                    'points_difference'     => -20,
                    'match_stakes'          => 1,
                ],
            ],
            [
                'home_team'  => 'Liverpool',
                'away_team'  => 'Man City',
                'league'     => 'Premier League',
                'match_date' => now()->addDays(7)->setTime(16, 30),
                'home_stat'  => [
                    'recent_form_score'     => 2.8,
                    'h2h_win_rate'          => 0.4,
                    'xg_for'                => 2.2,
                    'xg_against'            => 0.7,
                    'shots_on_target_ratio' => 0.44,
                    'ppda'                  => 7.0,
                    'rest_days'             => 6,
                    'key_player_absence'    => 1,
                    'distance_travelled_km' => 0,
                    'points_difference'     => 2,
                    'match_stakes'          => 3,
                ],
                'away_stat'  => [
                    'recent_form_score'     => 2.6,
                    'h2h_win_rate'          => 0.4,
                    'xg_for'                => 2.4,
                    'xg_against'            => 0.9,
                    'shots_on_target_ratio' => 0.46,
                    'ppda'                  => 7.5,
                    'rest_days'             => 5,
                    'key_player_absence'    => 0,
                    'distance_travelled_km' => 340,
                    'points_difference'     => -2,
                    'match_stakes'          => 3,
                ],
            ],
        ];

        foreach ($fixtures as $fixture) {
            $match = FootballMatch::create([
                'home_team'  => $fixture['home_team'],
                'away_team'  => $fixture['away_team'],
                'league'     => $fixture['league'],
                'match_date' => $fixture['match_date'],
                'status'     => 'upcoming',
            ]);

            MatchStat::create(array_merge(
                $fixture['home_stat'],
                [
                    'match_id'  => $match->id,
                    'team_name' => $fixture['home_team'],
                    'is_home'   => true,
                ],
            ));

            MatchStat::create(array_merge(
                $fixture['away_stat'],
                [
                    'match_id'  => $match->id,
                    'team_name' => $fixture['away_team'],
                    'is_home'   => false,
                ],
            ));
        }
    }
}
