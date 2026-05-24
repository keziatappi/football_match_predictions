"""
Soccer Match Prediction — FastAPI Microservice

Accepts match statistics for both teams and returns
predicted probabilities (Home Win, Draw, Away Win).

Run:
    uvicorn main:app --reload --port 8001
"""

from __future__ import annotations

from fastapi import FastAPI
from fastapi.middleware.cors import CORSMiddleware

from model import predict
from schemas import PredictionRequest, PredictionResponse

app = FastAPI(
    title="Soccer Match Prediction API",
    version="1.0.0",
    description="XGBoost-powered match outcome prediction microservice.",
)

# Allow Laravel backend to call this service
app.add_middleware(
    CORSMiddleware,
    allow_origins=["*"],
    allow_methods=["POST"],
    allow_headers=["*"],
)


@app.get("/health")
def health_check() -> dict[str, str]:
    """Liveness probe."""
    return {"status": "ok"}


@app.post("/predict", response_model=PredictionResponse)
def get_prediction(payload: PredictionRequest) -> PredictionResponse:
    """
    Accept home & away team stats, return predicted outcome probabilities.

    The JSON body must conform to the `PredictionRequest` schema
    (see schemas.py for full field definitions and validation rules).
    """
    result = predict(home=payload.home, away=payload.away)
    return PredictionResponse(**result)
