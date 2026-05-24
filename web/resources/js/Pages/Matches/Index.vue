<template>
    <Head title="Match Predictions" />

    <div class="min-h-screen bg-gradient-to-br from-slate-950 via-slate-900 to-indigo-950">
        <!-- ── Floating Particles Background ──────────────────────── -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div v-for="n in 20" :key="n"
                class="absolute rounded-full bg-indigo-500/10 animate-float"
                :style="{
                    width: `${Math.random() * 6 + 2}px`,
                    height: `${Math.random() * 6 + 2}px`,
                    left: `${Math.random() * 100}%`,
                    top: `${Math.random() * 100}%`,
                    animationDelay: `${Math.random() * 8}s`,
                    animationDuration: `${Math.random() * 10 + 10}s`,
                }"
            />
        </div>

        <!-- ── Header ─────────────────────────────────────────────── -->
        <header class="relative z-10 pt-10 pb-6 px-6 text-center">
            <div class="inline-flex items-center gap-3 mb-4">
                <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center shadow-lg shadow-emerald-500/25">
                    <svg class="w-6 h-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M12 2 C12 2 14.5 6 14.5 12 C14.5 18 12 22 12 22" />
                        <path d="M12 2 C12 2 9.5 6 9.5 12 C9.5 18 12 22 12 22" />
                        <line x1="2" y1="12" x2="22" y2="12" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold bg-gradient-to-r from-white via-indigo-200 to-emerald-300 bg-clip-text text-transparent">
                    Match Predictor
                </h1>
            </div>
            <p class="text-slate-400 text-sm md:text-base max-w-lg mx-auto">
                AI-powered match outcome predictions using advanced tactical metrics & XGBoost
            </p>

            <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3 max-w-md mx-auto">
                <!-- API Connection Status -->
                <div class="flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/5 border border-white/10 text-xs text-slate-300">
                    <span class="w-2 h-2 rounded-full" :class="apiConfig.hasKey ? 'bg-emerald-500 animate-pulse' : 'bg-amber-500'" />
                    <span>{{ apiConfig.hasKey ? `Connected (Season ${apiConfig.season})` : 'Demo Mode (Simulated Data)' }}</span>
                </div>
                
                <!-- Sync Button -->
                <button
                    @click="syncFixtures"
                    :disabled="syncing"
                    class="flex items-center gap-2 px-4 py-1.5 rounded-full bg-gradient-to-r from-indigo-600 to-emerald-600 hover:from-indigo-500 hover:to-emerald-500 disabled:opacity-50 text-xs font-semibold text-white transition-all shadow-md active:scale-[0.98]"
                >
                    <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': syncing }" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                    </svg>
                    <span>{{ syncing ? 'Syncing Fixtures...' : 'Sync Live Fixtures' }}</span>
                </button>
            </div>
        </header>

        <!-- ── League Filter Pills ────────────────────────────────── -->
        <nav class="relative z-10 flex justify-center gap-2 px-6 mb-8 flex-wrap">
            <button
                v-for="league in leagues"
                :key="league"
                @click="activeLeague = league"
                class="px-4 py-1.5 rounded-full text-xs font-medium transition-all duration-300"
                :class="activeLeague === league
                    ? 'bg-gradient-to-r from-indigo-500 to-emerald-500 text-white shadow-lg shadow-indigo-500/25'
                    : 'bg-white/5 text-slate-400 hover:bg-white/10 hover:text-white border border-white/5'"
            >
                {{ league }}
            </button>
        </nav>

        <!-- ── Match Grid ─────────────────────────────────────────── -->
        <main class="relative z-10 max-w-7xl mx-auto px-4 md:px-6 pb-20">
            <TransitionGroup
                name="card"
                tag="div"
                class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6"
            >
                <div
                    v-for="match in filteredMatches"
                    :key="match.id"
                    class="group relative rounded-2xl border border-white/[0.06] bg-white/[0.03] backdrop-blur-xl overflow-hidden transition-all duration-500 hover:border-indigo-500/30 hover:shadow-xl hover:shadow-indigo-500/5 hover:-translate-y-1"
                >
                    <!-- Glow effect on hover -->
                    <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/0 to-emerald-500/0 group-hover:from-indigo-500/5 group-hover:to-emerald-500/5 transition-all duration-500" />

                    <!-- League badge -->
                    <div class="relative px-5 pt-4 pb-2 flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <img v-if="match.league_logo" :src="match.league_logo" :alt="match.league" class="w-4 h-4 object-contain" />
                            <span class="text-[10px] uppercase tracking-widest text-slate-500 font-semibold">
                                {{ match.league }}
                            </span>
                        </div>
                        <span class="text-[10px] text-slate-600">
                            {{ match.match_date }}
                        </span>
                    </div>

                    <!-- Teams row -->
                    <div class="relative px-5 py-4 flex items-center justify-between gap-4">
                        <!-- Home Team -->
                        <div class="flex-1 text-center">
                            <div v-if="match.home_logo" class="w-14 h-14 mx-auto mb-2 rounded-xl bg-white/5 p-1.5 border border-white/10 flex items-center justify-center shadow-lg">
                                <img :src="match.home_logo" :alt="match.home_team" class="max-w-full max-h-full object-contain" />
                            </div>
                            <div v-else class="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-white/10 flex items-center justify-center text-xl font-bold text-white/80 shadow-inner">
                                {{ match.home_team.charAt(0) }}
                            </div>
                            <p class="text-sm font-semibold text-white truncate">{{ match.home_team }}</p>
                            <span class="text-[10px] text-emerald-400/80 uppercase tracking-wider">Home</span>
                        </div>

                        <!-- VS badge -->
                        <div class="flex-shrink-0 w-10 h-10 rounded-full bg-white/5 border border-white/10 flex items-center justify-center">
                            <span class="text-xs font-bold text-slate-500">VS</span>
                        </div>

                        <!-- Away Team -->
                        <div class="flex-1 text-center">
                            <div v-if="match.away_logo" class="w-14 h-14 mx-auto mb-2 rounded-xl bg-white/5 p-1.5 border border-white/10 flex items-center justify-center shadow-lg">
                                <img :src="match.away_logo" :alt="match.away_team" class="max-w-full max-h-full object-contain" />
                            </div>
                            <div v-else class="w-14 h-14 mx-auto mb-2 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-white/10 flex items-center justify-center text-xl font-bold text-white/80 shadow-inner">
                                {{ match.away_team.charAt(0) }}
                            </div>
                            <p class="text-sm font-semibold text-white truncate">{{ match.away_team }}</p>
                            <span class="text-[10px] text-cyan-400/80 uppercase tracking-wider">Away</span>
                        </div>
                    </div>

                    <!-- Key stats preview -->
                    <div v-if="match.home_stat && match.away_stat" class="relative px-5 pb-3">
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="rounded-lg bg-white/[0.03] py-1.5">
                                <p class="text-[10px] text-slate-500 uppercase">xG</p>
                                <p class="text-xs font-semibold text-emerald-400">
                                    {{ match.home_stat.xg_for.toFixed(1) }} – {{ match.away_stat.xg_for.toFixed(1) }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-white/[0.03] py-1.5">
                                <p class="text-[10px] text-slate-500 uppercase">Form</p>
                                <p class="text-xs font-semibold text-indigo-400">
                                    {{ match.home_stat.recent_form_score.toFixed(1) }} – {{ match.away_stat.recent_form_score.toFixed(1) }}
                                </p>
                            </div>
                            <div class="rounded-lg bg-white/[0.03] py-1.5">
                                <p class="text-[10px] text-slate-500 uppercase">H2H</p>
                                <p class="text-xs font-semibold text-amber-400">
                                    {{ (match.home_stat.h2h_win_rate * 100).toFixed(0) }}%
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Prediction result or button -->
                    <div class="relative px-5 pb-5 pt-2">
                        <!-- Loading skeleton -->
                        <div v-if="loadingMatch === match.id" class="space-y-3 animate-pulse">
                            <div class="h-2 rounded-full bg-white/10 w-3/4 mx-auto" />
                            <div class="flex gap-3">
                                <div class="h-16 rounded-xl bg-white/5 flex-1" />
                                <div class="h-16 rounded-xl bg-white/5 flex-1" />
                                <div class="h-16 rounded-xl bg-white/5 flex-1" />
                            </div>
                            <div class="h-3 rounded-full bg-white/10 w-full" />
                        </div>

                        <!-- Prediction results -->
                        <MatchPrediction
                            v-else-if="predictions[match.id]"
                            :prediction="predictions[match.id]"
                            :home-team="match.home_team"
                            :away-team="match.away_team"
                        />

                        <!-- Predict button -->
                        <button
                            v-else
                            @click="runPrediction(match.id)"
                            class="w-full py-3 rounded-xl font-semibold text-sm relative overflow-hidden group/btn transition-all duration-300 bg-gradient-to-r from-indigo-600 to-emerald-600 text-white hover:shadow-lg hover:shadow-indigo-500/25 active:scale-[0.98]"
                        >
                            <span class="relative z-10 flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                                </svg>
                                Run AI Prediction
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-indigo-500 to-emerald-500 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300" />
                        </button>

                        <!-- Error state -->
                        <div v-if="errors[match.id]" class="mt-2 text-center">
                            <p class="text-xs text-red-400/80">{{ errors[match.id] }}</p>
                            <button @click="runPrediction(match.id)" class="text-xs text-indigo-400 hover:underline mt-1">
                                Retry
                            </button>
                        </div>
                    </div>
                </div>
            </TransitionGroup>

            <!-- Empty state -->
            <div v-if="filteredMatches.length === 0" class="text-center py-20">
                <div class="w-16 h-16 mx-auto mb-4 rounded-2xl bg-white/5 flex items-center justify-center">
                    <svg class="w-8 h-8 text-slate-600" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                </div>
                <p class="text-slate-500">No upcoming matches in this league.</p>
            </div>
        </main>
    </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import MatchPrediction from '@/Components/MatchPrediction.vue';

const props = defineProps({
    matches: {
        type: Array,
        required: true,
    },
    apiConfig: {
        type: Object,
        default: () => ({ hasKey: false, season: 2025 }),
    },
});

// ── State ────────────────────────────────────────────────────────
const activeLeague = ref('All');
const loadingMatch = ref(null);
const predictions  = ref({});
const errors       = ref({});
const syncing      = ref(false);

// ── Computed ─────────────────────────────────────────────────────
const leagues = computed(() => {
    const set = new Set(props.matches.map(m => m.league));
    return ['All', ...Array.from(set).sort()];
});

const filteredMatches = computed(() => {
    if (activeLeague.value === 'All') return props.matches;
    return props.matches.filter(m => m.league === activeLeague.value);
});

// ── Methods ──────────────────────────────────────────────────────
async function syncFixtures() {
    syncing.value = true;
    try {
        const response = await fetch('/matches/sync', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Synchronization failed');
        }

        // Reload the Inertia page
        router.reload();
    } catch (err) {
        alert(err.message || 'Failed to sync fixtures');
    } finally {
        syncing.value = false;
    }
}

async function runPrediction(matchId) {
    loadingMatch.value = matchId;
    errors.value[matchId] = null;

    try {
        const response = await fetch(`/matches/${matchId}/predict`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content,
                'Accept': 'application/json',
            },
        });

        const data = await response.json();

        if (!response.ok || !data.success) {
            throw new Error(data.message || 'Prediction failed');
        }

        predictions.value[matchId] = data.prediction;
    } catch (err) {
        errors.value[matchId] = err.message || 'Something went wrong. Is the ML service running?';
    } finally {
        loadingMatch.value = null;
    }
}
</script>

<style scoped>
/* Floating particles animation */
@keyframes float {
    0%, 100% { transform: translateY(0) translateX(0); opacity: 0.3; }
    25%      { transform: translateY(-30px) translateX(10px); opacity: 0.6; }
    50%      { transform: translateY(-15px) translateX(-10px); opacity: 0.4; }
    75%      { transform: translateY(-40px) translateX(15px); opacity: 0.5; }
}
.animate-float {
    animation: float 15s ease-in-out infinite;
}

/* Card transition group animations */
.card-enter-active,
.card-leave-active {
    transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.card-enter-from { opacity: 0; transform: translateY(20px) scale(0.95); }
.card-leave-to   { opacity: 0; transform: translateY(-10px) scale(0.95); }
</style>
