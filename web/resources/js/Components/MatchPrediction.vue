<template>
    <div class="space-y-4">
        <!-- Probability Header -->
        <p class="text-[10px] uppercase tracking-widest text-slate-500 text-center font-semibold">
            AI Prediction
        </p>

        <!-- Donut Chart + Labels -->
        <div class="flex items-center justify-center gap-5">
            <!-- Donut Chart (Canvas) -->
            <div class="relative w-20 h-20 flex-shrink-0">
                <canvas ref="donutCanvas" width="80" height="80" class="w-full h-full" />
                <div class="absolute inset-0 flex items-center justify-center">
                    <span class="text-[10px] font-bold text-white/60">{{ winnerLabel }}</span>
                </div>
            </div>

            <!-- Probability Bars -->
            <div class="flex-1 space-y-2 min-w-0">
                <ProbBar
                    :label="homeTeam"
                    :value="prediction.home_win"
                    color-from="#6366f1"
                    color-to="#818cf8"
                    :is-highest="winner === 'home'"
                />
                <ProbBar
                    label="Draw"
                    :value="prediction.draw"
                    color-from="#64748b"
                    color-to="#94a3b8"
                    :is-highest="winner === 'draw'"
                />
                <ProbBar
                    :label="awayTeam"
                    :value="prediction.away_win"
                    color-from="#10b981"
                    color-to="#34d399"
                    :is-highest="winner === 'away'"
                />
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, h } from 'vue';

const props = defineProps({
    prediction: { type: Object, required: true },
    homeTeam:   { type: String, required: true },
    awayTeam:   { type: String, required: true },
});

const donutCanvas = ref(null);

// ── Derived ──────────────────────────────────────────────────────
const winner = computed(() => {
    const { home_win, draw, away_win } = props.prediction;
    if (home_win >= draw && home_win >= away_win) return 'home';
    if (away_win >= draw && away_win >= home_win) return 'away';
    return 'draw';
});

const winnerLabel = computed(() => {
    const map = { home: 'H', draw: 'D', away: 'A' };
    return map[winner.value];
});

// ── Donut Chart Drawing ──────────────────────────────────────────
function drawDonut() {
    const canvas = donutCanvas.value;
    if (!canvas) return;

    const ctx = canvas.getContext('2d');
    const { width, height } = canvas;
    const cx = width / 2;
    const cy = height / 2;
    const radius = Math.min(cx, cy) - 4;
    const lineWidth = 8;

    ctx.clearRect(0, 0, width, height);

    const segments = [
        { value: props.prediction.home_win, color: '#6366f1' },
        { value: props.prediction.draw,     color: '#64748b' },
        { value: props.prediction.away_win, color: '#10b981' },
    ];

    let startAngle = -Math.PI / 2; // Start from top

    segments.forEach(({ value, color }) => {
        const sweepAngle = value * Math.PI * 2;

        ctx.beginPath();
        ctx.arc(cx, cy, radius, startAngle, startAngle + sweepAngle);
        ctx.strokeStyle = color;
        ctx.lineWidth = lineWidth;
        ctx.lineCap = 'round';
        ctx.stroke();

        startAngle += sweepAngle + 0.04; // Small gap between segments
    });
}

onMounted(() => drawDonut());
watch(() => props.prediction, () => drawDonut(), { deep: true });

// ── Sub-component: Probability Bar (render function) ─────────────
const ProbBar = {
    props: {
        label:     { type: String, required: true },
        value:     { type: Number, required: true },
        colorFrom: { type: String, required: true },
        colorTo:   { type: String, required: true },
        isHighest: { type: Boolean, default: false },
    },
    setup(props) {
        return () => h('div', { class: 'flex items-center gap-2' }, [
            h('span', {
                class: [
                    'text-[10px] font-medium w-16 truncate text-right',
                    props.isHighest ? 'text-white' : 'text-slate-500',
                ],
            }, props.label),
            h('div', { class: 'flex-1 h-2 rounded-full bg-white/5 overflow-hidden' }, [
                h('div', {
                    class: 'h-full rounded-full transition-all duration-1000 ease-out',
                    style: {
                        width: (props.value * 100) + '%',
                        background: `linear-gradient(to right, ${props.colorFrom}, ${props.colorTo})`,
                    },
                }),
            ]),
            h('span', {
                class: [
                    'text-xs font-bold tabular-nums w-10 text-right',
                    props.isHighest ? 'text-white' : 'text-slate-500',
                ],
            }, (props.value * 100).toFixed(1) + '%'),
        ]);
    },
};
</script>
