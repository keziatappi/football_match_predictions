<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ApiFootballService
{
    protected string $key;
    protected string $url;
    protected int $season;
    protected array $leagues;

    public function __construct()
    {
        $this->key = config('services.api_football.key', '');
        $this->url = config('services.api_football.url', 'https://v3.football.api-sports.io');
        $this->season = (int) config('services.api_football.season', 2025);
        
        $leaguesStr = config('services.api_football.leagues', '39,140,78,135,61');
        $this->leagues = array_filter(array_map('intval', explode(',', $leaguesStr)));
    }

    /**
     * Get the configured leagues.
     */
    public function getLeagues(): array
    {
        return $this->leagues;
    }

    /**
     * Get the configured season.
     */
    public function getSeason(): int
    {
        return $this->season;
    }

    /**
     * Send GET request to API-Football with caching.
     */
    protected function get(string $endpoint, array $query = []): array
    {
        // Cache API responses for 1 day to preserve API limit (100 req/day)
        $cacheKey = 'api_football_' . str_replace('/', '_', $endpoint) . '_' . md5(serialize($query));

        return Cache::remember($cacheKey, now()->addDay(), function () use ($endpoint, $query) {
            if (empty($this->key) || $this->key === 'your_api_key_here') {
                Log::warning("API-Football Key is not configured. Returning empty response for {$endpoint}.");
                return [];
            }

            // Free plan limits us to 10 requests/minute. Sleep for 6 seconds between cache-miss requests.
            sleep(6);

            try {
                $response = Http::withHeaders([
                    'x-apisports-key' => $this->key,
                ])
                ->timeout(15)
                ->get("{$this->url}/{$endpoint}", $query);

                if ($response->failed()) {
                    Log::error("API-Football request failed: {$endpoint}", [
                        'status' => $response->status(),
                        'body' => $response->body()
                    ]);
                    return [];
                }

                $data = $response->json();
                
                // API-Football returns errors array if there's any subscription issues or key invalidity
                if (!empty($data['errors'])) {
                    Log::error("API-Football API returned errors", ['errors' => $data['errors']]);
                    return [];
                }

                return $data['response'] ?? [];
            } catch (\Exception $e) {
                Log::error("API-Football exception occurred: " . $e->getMessage());
                return [];
            }
        });
    }

    /**
     * Get all fixtures for a league and season.
     */
    public function getLeagueFixtures(int $leagueId, int $season): array
    {
        return $this->get('fixtures', [
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    /**
     * Get last fixtures for a team in a season (bypassing 'last' parameter restrictions).
     */
    public function getLastFixtures(int $teamId, int $last = 5): array
    {
        $fixtures = $this->get('fixtures', [
            'team' => $teamId,
            'season' => $this->season,
        ]);

        if (empty($fixtures)) {
            return [];
        }

        // Filter completed games and sort descending by date
        $completed = array_filter($fixtures, function ($fixture) {
            $status = $fixture['fixture']['status']['short'] ?? '';
            return in_array($status, ['FT', 'AET', 'PEN']);
        });

        usort($completed, function ($a, $b) {
            return strtotime($b['fixture']['date']) <=> strtotime($a['fixture']['date']);
        });

        return array_slice($completed, 0, $last);
    }

    /**
     * Get H2H fixtures between two teams (bypassing 'last' parameter restrictions).
     */
    public function getHeadToHead(int $teamA, int $teamB, int $last = 5): array
    {
        $fixtures = $this->get('fixtures/headtohead', [
            'h2h' => "{$teamA}-{$teamB}",
        ]);

        if (empty($fixtures)) {
            return [];
        }

        // Sort descending by date and slice
        usort($fixtures, function ($a, $b) {
            return strtotime($b['fixture']['date'] ?? 0) <=> strtotime($a['fixture']['date'] ?? 0);
        });

        return array_slice($fixtures, 0, $last);
    }

    /**
     * Get team statistics for a season.
     */
    public function getTeamStatistics(int $leagueId, int $season, int $teamId): array
    {
        return $this->get('teams/statistics', [
            'league' => $leagueId,
            'season' => $season,
            'team' => $teamId,
        ]);
    }

    /**
     * Get standings for a league.
     */
    public function getStandings(int $leagueId, int $season): array
    {
        return $this->get('standings', [
            'league' => $leagueId,
            'season' => $season,
        ]);
    }

    /**
     * Get injuries for a fixture.
     */
    public function getInjuries(int $fixtureId): array
    {
        return $this->get('injuries', [
            'fixture' => $fixtureId,
        ]);
    }

    /**
     * Get statistics for a specific fixture.
     */
    public function getFixtureStatistics(int $fixtureId): array
    {
        return $this->get('fixtures/statistics', [
            'fixture' => $fixtureId,
        ]);
    }
}
