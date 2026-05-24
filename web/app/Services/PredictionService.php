<?php

namespace App\Services;

use App\Models\FootballMatch;
use App\Models\MatchStat;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PredictionService
{
    /**
     * Base URL for the FastAPI ML microservice.
     */
    private string $baseUrl;

    public function __construct()
    {
        $this->baseUrl = config('services.ml.url', 'http://127.0.0.1:8001');
    }

    /**
     * Build the JSON payload from match stats and POST it to FastAPI.
     *
     * @return array{home_win: float, draw: float, away_win: float}
     *
     * @throws \RuntimeException When the ML service is unreachable or returns an error.
     */
    public function predict(FootballMatch $match): array
    {
        $homeStat = $match->homeStat;
        $awayStat = $match->awayStat;

        if (! $homeStat || ! $awayStat) {
            throw new \RuntimeException("Match #{$match->id} is missing home or away stats.");
        }

        $payload = [
            'home' => $this->buildTeamPayload($homeStat),
            'away' => $this->buildTeamPayload($awayStat),
        ];

        Log::info('Sending prediction request to ML service', ['match_id' => $match->id]);

        $response = Http::timeout(10)
            ->post("{$this->baseUrl}/predict", $payload);

        if ($response->failed()) {
            Log::error('ML service returned an error', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new \RuntimeException('Prediction service unavailable. Please try again later.');
        }

        return $response->json();
    }

    /**
     * Map an Eloquent MatchStat to the FastAPI TeamStats schema.
     */
    private function buildTeamPayload(MatchStat $stat): array
    {
        return [
            'recent_form_score'    => $stat->recent_form_score,
            'h2h_win_rate'         => $stat->h2h_win_rate,
            'is_home_advantage'    => $stat->is_home ? 1 : 0,
            'xg_for'               => $stat->xg_for,
            'xg_against'           => $stat->xg_against,
            'shots_on_target_ratio' => $stat->shots_on_target_ratio,
            'ppda'                 => $stat->ppda,
            'rest_days'            => $stat->rest_days,
            'key_player_absence'   => $stat->key_player_absence,
            'distance_travelled_km' => $stat->distance_travelled_km,
            'points_difference'    => $stat->points_difference,
            'match_stakes'         => $stat->match_stakes,
        ];
    }
}
