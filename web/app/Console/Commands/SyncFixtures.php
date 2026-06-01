<?php

namespace App\Console\Commands;

use App\Models\FootballMatch;
use App\Models\MatchStat;
use App\Services\ApiFootballService;
use App\Services\FeatureEngineerService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncFixtures extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'fixtures:sync {--force : Force sync even if already synced} {--league= : The ID of the league to sync} {--limit=5 : The number of fixtures to process per league} {--demo : Force running in demo/mock mode}';

    /**
     * The console command description.
     */
    protected $description = 'Sync upcoming fixtures and calculate their ML features from API-Football';

    protected ApiFootballService $apiService;
    protected FeatureEngineerService $featureEngineer;

    public function __construct(ApiFootballService $apiService, FeatureEngineerService $featureEngineer)
    {
        parent::__construct();
        $this->apiService = $apiService;
        $this->featureEngineer = $featureEngineer;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info("Starting API-Football fixtures synchronization...");

        $demoMode = $this->option('demo');
        $key = config('services.api_football.key', '');
        if ($demoMode || empty($key) || $key === 'your_api_key_here') {
            $this->warn("Running in DEMO/MOCK mode...");
            $this->runDemoSync();
            return 0;
        }

        $season = $this->apiService->getSeason();
        $leagues = $this->apiService->getLeagues();
        $targetLeague = $this->option('league');
        if ($targetLeague) {
            $leagues = [intval($targetLeague)];
        }

        $syncedCount = 0;

        foreach ($leagues as $leagueId) {
            $this->info("Fetching data for League ID: {$leagueId} (Season: {$season})...");

            // 1. Fetch standings first to get stats for all teams
            $standings = $this->apiService->getStandings($leagueId, $season);
            if (empty($standings)) {
                $this->error("Failed to retrieve standings for league {$leagueId}. Skipping...");
                continue;
            }

            // 2. Fetch all fixtures for the league and season (complies with Free API plan)
            $allFixtures = $this->apiService->getLeagueFixtures($leagueId, $season);
            if (empty($allFixtures)) {
                $this->warn("No fixtures found for league {$leagueId}. Skipping...");
                continue;
            }

            // Filter upcoming matches (Not Started)
            $upcomingFixtures = array_filter($allFixtures, function ($fixture) {
                $status = $fixture['fixture']['status']['short'] ?? '';
                return in_array($status, ['NS', 'TBD']);
            });

            // Sort by date ascending
            usort($upcomingFixtures, function ($a, $b) {
                return strtotime($a['fixture']['date'] ?? 0) <=> strtotime($b['fixture']['date'] ?? 0);
            });

            $limit = intval($this->option('limit') ?? 5);
            $fixtures = array_slice($upcomingFixtures, 0, $limit);

            // Fallback: If no upcoming matches are found (e.g. historical season), get recent completed matches
            if (empty($fixtures)) {
                $this->info("No upcoming fixtures found for league {$leagueId} in season {$season}. Using recent completed fixtures as fallback...");
                
                $completedFixtures = array_filter($allFixtures, function ($fixture) {
                    $status = $fixture['fixture']['status']['short'] ?? '';
                    return in_array($status, ['FT', 'AET', 'PEN']);
                });

                // Sort by date descending
                usort($completedFixtures, function ($a, $b) {
                    return strtotime($b['fixture']['date'] ?? 0) <=> strtotime($a['fixture']['date'] ?? 0);
                });

                $fixtures = array_slice($completedFixtures, 0, $limit);
            }

            if (empty($fixtures)) {
                $this->warn("No suitable fixtures found for league {$leagueId}. Skipping...");
                continue;
            }

            $this->info("Found " . count($fixtures) . " fixtures. Processing...");

            foreach ($fixtures as $fixtureData) {
                $apiFixtureId = $fixtureData['fixture']['id'] ?? null;
                if (!$apiFixtureId) continue;

                // Check if match already exists
                $existingMatch = FootballMatch::where('api_fixture_id', $apiFixtureId)->first();
                if ($existingMatch && !$this->option('force')) {
                    $this->line("Fixture {$apiFixtureId} already exists in DB. Skipping (use --force to update).");
                    continue;
                }

                // Compute features
                $features = $this->featureEngineer->computeStats($fixtureData, $standings);

                DB::transaction(function () use ($fixtureData, $apiFixtureId, $leagueId, $season, $features) {
                    // Update or create match
                    $match = FootballMatch::updateOrCreate(
                        ['api_fixture_id' => $apiFixtureId],
                        [
                            'home_team'      => $fixtureData['teams']['home']['name'],
                            'away_team'      => $fixtureData['teams']['away']['name'],
                            'league'         => $fixtureData['league']['name'],
                            'match_date'     => \Carbon\Carbon::parse($fixtureData['fixture']['date']),
                            'status'         => 'upcoming',
                            'home_logo'      => $fixtureData['teams']['home']['logo'] ?? null,
                            'away_logo'      => $fixtureData['teams']['away']['logo'] ?? null,
                            'api_league_id'  => $leagueId,
                            'season'         => $season,
                            'league_logo'    => $fixtureData['league']['logo'] ?? null,
                        ]
                    );

                    // Re-create stats
                    $match->stats()->delete();

                    MatchStat::create(array_merge($features['home'], ['match_id' => $match->id]));
                    MatchStat::create(array_merge($features['away'], ['match_id' => $match->id]));
                });

                $syncedCount++;
                $this->line("✅ Synced: {$fixtureData['teams']['home']['name']} vs {$fixtureData['teams']['away']['name']}");
            }
        }

        $this->info("Sync completed! Synced {$syncedCount} new fixtures.");
        return 0;
    }

    /**
     * Run a simulation/demo sync if no API key is provided.
     */
    protected function runDemoSync()
    {
        $mockFixtures = [
            [
                'id' => 900001,
                'league_id' => 39,
                'league_name' => 'Premier League',
                'league_logo' => 'https://media.api-sports.io/football/leagues/39.png',
                'home_name' => 'Manchester United',
                'home_logo' => 'https://media.api-sports.io/football/teams/33.png',
                'home_id' => 33,
                'away_name' => 'Manchester City',
                'away_logo' => 'https://media.api-sports.io/football/teams/50.png',
                'away_id' => 50,
                'date' => now()->addDays(2)->setTime(17, 30)->toIso8601String(),
                'home_rank' => 5, 'home_pts' => 58, 'home_form' => 'WWDWL',
                'away_rank' => 2, 'away_pts' => 72, 'away_form' => 'WWWWW',
            ],
            [
                'id' => 900002,
                'league_id' => 140,
                'league_name' => 'La Liga',
                'league_logo' => 'https://media.api-sports.io/football/leagues/140.png',
                'home_name' => 'Atletico Madrid',
                'home_logo' => 'https://media.api-sports.io/football/teams/530.png',
                'home_id' => 530,
                'away_name' => 'Real Madrid',
                'away_logo' => 'https://media.api-sports.io/football/teams/541.png',
                'away_id' => 541,
                'date' => now()->addDays(3)->setTime(20, 0)->toIso8601String(),
                'home_rank' => 3, 'home_pts' => 64, 'home_form' => 'WDWDW',
                'away_rank' => 1, 'away_pts' => 78, 'away_form' => 'WWLWW',
            ],
            [
                'id' => 900003,
                'league_id' => 78,
                'league_name' => 'Bundesliga',
                'league_logo' => 'https://media.api-sports.io/football/leagues/78.png',
                'home_name' => 'Bayer Leverkusen',
                'home_logo' => 'https://media.api-sports.io/football/teams/168.png',
                'home_id' => 168,
                'away_name' => 'Bayern Munich',
                'away_logo' => 'https://media.api-sports.io/football/teams/157.png',
                'away_id' => 157,
                'date' => now()->addDays(4)->setTime(15, 30)->toIso8601String(),
                'home_rank' => 2, 'home_pts' => 68, 'home_form' => 'DWWDW',
                'away_rank' => 1, 'away_pts' => 71, 'away_form' => 'LWWWW',
            ]
        ];

        $synced = 0;
        foreach ($mockFixtures as $mock) {
            // Check if already exists
            $existing = FootballMatch::where('api_fixture_id', $mock['id'])->first();
            if ($existing && !$this->option('force')) {
                continue;
            }

            // Create fake standings & fixture data structures for feature engineer
            $standings = [
                [
                    'standings' => [
                        [
                            [
                                'team' => ['id' => $mock['home_id'], 'name' => $mock['home_name']],
                                'rank' => $mock['home_rank'],
                                'points' => $mock['home_pts'],
                                'form' => $mock['home_form'],
                                'all' => ['played' => 30, 'goals' => ['for' => 55, 'against' => 32]],
                            ],
                            [
                                'team' => ['id' => $mock['away_id'], 'name' => $mock['away_name']],
                                'rank' => $mock['away_rank'],
                                'points' => $mock['away_pts'],
                                'form' => $mock['away_form'],
                                'all' => ['played' => 30, 'goals' => ['for' => 74, 'against' => 22]],
                            ]
                        ]
                    ]
                ]
            ];

            $fixtureData = [
                'fixture' => [
                    'id' => $mock['id'],
                    'date' => $mock['date'],
                ],
                'league' => [
                    'name' => $mock['league_name'],
                    'logo' => $mock['league_logo'],
                ],
                'teams' => [
                    'home' => [
                        'id' => $mock['home_id'],
                        'name' => $mock['home_name'],
                        'logo' => $mock['home_logo'],
                    ],
                    'away' => [
                        'id' => $mock['away_id'],
                        'name' => $mock['away_name'],
                        'logo' => $mock['away_logo'],
                    ]
                ]
            ];

            // Use the feature engineer to compute stats from this fake structure
            $features = $this->featureEngineer->computeStats($fixtureData, $standings);

            DB::transaction(function () use ($mock, $fixtureData, $features) {
                $match = FootballMatch::updateOrCreate(
                    ['api_fixture_id' => $mock['id']],
                    [
                        'home_team'      => $mock['home_name'],
                        'away_team'      => $mock['away_name'],
                        'league'         => $mock['league_name'],
                        'match_date'     => \Carbon\Carbon::parse($mock['date']),
                        'status'         => 'upcoming',
                        'home_logo'      => $mock['home_logo'],
                        'away_logo'      => $mock['away_logo'],
                        'api_league_id'  => $mock['league_id'],
                        'season'         => 2025,
                        'league_logo'    => $mock['league_logo'],
                    ]
                );

                $match->stats()->delete();

                MatchStat::create(array_merge($features['home'], ['match_id' => $match->id]));
                MatchStat::create(array_merge($features['away'], ['match_id' => $match->id]));
            });

            $synced++;
            $this->line("✅ Synced Mock: {$mock['home_name']} vs {$mock['away_name']}");
        }

        $this->info("Simulated sync completed! Synced {$synced} mock fixtures.");
    }
}
