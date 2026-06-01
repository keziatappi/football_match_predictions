<?php

namespace App\Http\Controllers;

use App\Models\FootballMatch;
use App\Services\PredictionService;
use Illuminate\Http\JsonResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class PredictionController extends Controller
{
    public function __construct(
        private readonly PredictionService $predictionService,
    ) {}

    /**
     * Display the upcoming matches dashboard.
     */
    public function index(): InertiaResponse
    {
        $matches = FootballMatch::query()
            ->where('status', 'upcoming')
            ->with(['homeStat', 'awayStat'])
            ->orderBy('match_date')
            ->get()
            ->map(fn (FootballMatch $match) => [
                'id'         => $match->id,
                'home_team'  => $match->home_team,
                'away_team'  => $match->away_team,
                'league'     => $match->league,
                'match_date' => $match->match_date->format('D, d M Y – H:i'),
                'home_logo'  => $match->home_logo,
                'away_logo'  => $match->away_logo,
                'home_stat'  => $match->homeStat,
                'away_stat'  => $match->awayStat,
            ]);

        return Inertia::render('Matches/Index', [
            'matches' => $matches,
            'apiConfig' => [
                'hasKey' => !empty(config('services.api_football.key')) && config('services.api_football.key') !== 'your_api_key_here',
                'season' => config('services.api_football.season'),
            ]
        ]);
    }

    /**
     * Trigger API-Football synchronization.
     */
    public function sync(): JsonResponse
    {
        try {
            // Call artisan command programmatically, limited to 2 fixtures per league to prevent HTTP timeouts
            \Illuminate\Support\Facades\Artisan::call('fixtures:sync', [
                '--force' => true,
                '--limit' => 2,
            ]);
            $output = \Illuminate\Support\Facades\Artisan::output();

            return response()->json([
                'success' => true,
                'message' => 'Fixtures synced successfully!',
                'output'  => $output,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to sync fixtures: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Run the ML prediction for a specific match and return JSON.
     */
    public function predict(FootballMatch $match): JsonResponse
    {
        try {
            $prediction = $this->predictionService->predict($match);

            return response()->json([
                'success'    => true,
                'match_id'   => $match->id,
                'prediction' => $prediction,
            ]);
        } catch (\RuntimeException $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 503);
        }
    }
}
