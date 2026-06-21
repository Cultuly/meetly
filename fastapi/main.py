from fastapi import FastAPI
from api.rest import router as rest_router


app = FastAPI(title="Meetly API")

app.include_router(rest_router)


# health check
@app.get("/")
def root():
    return {"message": "Meetly API works"}