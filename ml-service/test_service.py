from main import app
from fastapi.testclient import TestClient

client = TestClient(app)

def test_health():
    response = client.get("/health")
    assert response.status_code == 200
    assert response.json() == {"status": "ok"}

def test_predict():
    payload = {
        "home": {
            "recent_form_score": 2.4,
            "h2h_win_rate": 0.6,
            "is_home_advantage": 1,
            "xg_for": 2.1,
            "xg_against": 0.8,
            "shots_on_target_ratio": 0.42,
            "ppda": 8.5,
            "rest_days": 5,
            "key_player_absence": 1,
            "distance_travelled_km": 0,
            "points_difference": 12,
            "match_stakes": 3
        },
        "away": {
            "recent_form_score": 1.8,
            "h2h_win_rate": 0.2,
            "is_home_advantage": 0,
            "xg_for": 1.6,
            "xg_against": 1.2,
            "shots_on_target_ratio": 0.35,
            "ppda": 10.2,
            "rest_days": 4,
            "key_player_absence": 2,
            "distance_travelled_km": 15,
            "points_difference": -12,
            "match_stakes": 3
        }
    }
    response = client.post("/predict", json=payload)
    print("Prediction response:", response.json())
    assert response.status_code == 200
    data = response.json()
    assert "home_win" in data
    assert "draw" in data
    assert "away_win" in data
    assert abs(data["home_win"] + data["draw"] + data["away_win"] - 1.0) < 1e-4

if __name__ == "__main__":
    test_health()
    test_predict()
    print("All ML service tests passed successfully!")
