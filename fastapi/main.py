import asyncio
from contextlib import asynccontextmanager

from fastapi import FastAPI

from api.rest import router as rest_router
from realtime.ws import router as ws_router
from realtime.redis_listener import redis_listener
from fastapi.middleware.cors import CORSMiddleware


@asynccontextmanager
async def lifespan(app: FastAPI):
    task = asyncio.create_task(redis_listener())
    yield
    task.cancel()


app = FastAPI(title="Meetly API", lifespan=lifespan)

app.include_router(rest_router)
app.include_router(ws_router)

app.add_middleware(
    CORSMiddleware,
    allow_origins=["https://meetly.ru"],
    allow_methods=["GET"],
    allow_headers=["*"],
)

# health check
@app.get("/")
def root():
    return {"message": "Meetly API works"}