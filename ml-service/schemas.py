"""
Pydantic schemas for the Soccer Match Prediction API.
Defines strong typing for all 12 ML features per team.
"""

from pydantic import BaseModel, Field


class TeamStats(BaseModel):
    """Statistical features for a single team in a match."""

    # ── Basic Form & H2H ──────────────────────────────────────────
    recent_form_score: float = Field(
        ..., ge=0.0, le=3.0,
        description="Average points over the last 5 games (0-3 scale).",
    )
    h2h_win_rate: float = Field(
        ..., ge=0.0, le=1.0,
        description="Win percentage against this opponent in last 5 meetings.",
    )
    is_home_advantage: int = Field(
        ..., ge=0, le=1,
        description="1 if playing at home, 0 if away.",
    )

    # ── Advanced Tactical Metrics ─────────────────────────────────
    xg_for: float = Field(
        ..., ge=0.0,
        description="Expected Goals generated per game.",
    )
    xg_against: float = Field(
        ..., ge=0.0,
        description="Expected Goals conceded per game.",
    )
    shots_on_target_ratio: float = Field(
        ..., ge=0.0, le=1.0,
        description="Ratio of shots on target to total shots.",
    )
    ppda: float = Field(
        ..., ge=0.0,
        description="Passes Allowed Per Defensive Action (pressing intensity).",
    )

    # ── Squad Availability & Fatigue ──────────────────────────────
    rest_days: int = Field(
        ..., ge=0,
        description="Days since the last competitive match.",
    )
    key_player_absence: int = Field(
        ..., ge=0,
        description="Number of key players missing (injury/suspension).",
    )
    distance_travelled_km: int = Field(
        ..., ge=0,
        description="Distance travelled for away team (0 for home).",
    )

    # ── Contextual & Psychological ────────────────────────────────
    points_difference: int = Field(
        ...,
        description="Points gap in the league standings (positive = higher).",
    )
    match_stakes: int = Field(
        ..., ge=1, le=3,
        description="1: Normal, 2: Relegation battle, 3: Title decider / Derby.",
    )


class PredictionRequest(BaseModel):
    """Payload sent from Laravel to FastAPI for a single match prediction."""

    home: TeamStats
    away: TeamStats


class PredictionResponse(BaseModel):
    """Predicted outcome probabilities for the match."""

    home_win: float = Field(..., ge=0.0, le=1.0)
    draw: float = Field(..., ge=0.0, le=1.0)
    away_win: float = Field(..., ge=0.0, le=1.0)
