"""
XGBoost model wrapper.

For the initial scaffold this uses a realistic heuristic based on xG,
form, and home advantage to produce plausible probabilities.
Replace `predict()` internals once a trained model is available.
"""

from __future__ import annotations

import numpy as np
from schemas import TeamStats


def _softmax(x: np.ndarray) -> np.ndarray:
    """Numerically-stable softmax."""
    e = np.exp(x - np.max(x))
    return e / e.sum()


def predict(home: TeamStats, away: TeamStats) -> dict[str, float]:
    """
    Return predicted probabilities for Home Win, Draw, and Away Win.

    Current implementation: weighted heuristic that mimics a trained model.
    Swap this with `xgboost.Booster.predict()` when a real model is ready.
    """

    # ── Strength signals ──────────────────────────────────────────
    home_xg_diff = home.xg_for - home.xg_against
    away_xg_diff = away.xg_for - away.xg_against

    home_strength = (
        home.recent_form_score * 0.20
        + home.h2h_win_rate * 0.15
        + home_xg_diff * 0.25
        + home.shots_on_target_ratio * 0.10
        + (1.0 / max(home.ppda, 1.0)) * 0.05  # lower ppda → better pressing
        + home.is_home_advantage * 0.30        # home advantage boost
        - home.key_player_absence * 0.08
        + min(home.rest_days, 7) / 7.0 * 0.05
    )

    away_strength = (
        away.recent_form_score * 0.20
        + away.h2h_win_rate * 0.15
        + away_xg_diff * 0.25
        + away.shots_on_target_ratio * 0.10
        + (1.0 / max(away.ppda, 1.0)) * 0.05
        + away.is_home_advantage * 0.30
        - away.key_player_absence * 0.08
        + min(away.rest_days, 7) / 7.0 * 0.05
        - away.distance_travelled_km / 5000.0 * 0.04  # travel fatigue
    )

    # ── Contextual adjustments ────────────────────────────────────
    stakes_boost = (home.match_stakes + away.match_stakes) / 6.0 * 0.05
    pts_factor = home.points_difference / 30.0 * 0.10  # positive → home stronger

    home_strength += pts_factor + stakes_boost
    away_strength -= pts_factor

    # ── Raw logits → probabilities ────────────────────────────────
    draw_logit = -abs(home_strength - away_strength) * 0.8  # closer → more draw
    logits = np.array([home_strength, draw_logit, away_strength])
    probs = _softmax(logits)

    return {
        "home_win": round(float(probs[0]), 4),
        "draw": round(float(probs[1]), 4),
        "away_win": round(float(probs[2]), 4),
    }
