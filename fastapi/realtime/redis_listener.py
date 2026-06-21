import os

import redis.asyncio as redis

from realtime.connection_manager import manager

REDIS_HOST = os.getenv("REDIS_HOST", "redis")
REDIS_PORT = int(os.getenv("REDIS_PORT", "6379"))


async def redis_listener() -> None:
    client = redis.Redis(host=REDIS_HOST, port=REDIS_PORT, decode_responses=True)
    pubsub = client.pubsub()

    await pubsub.psubscribe("channel.*")

    async for message in pubsub.listen():
        if message["type"] != "pmessage":
            continue

        raw_channel = message["channel"]
        try:
            channel_id = int(raw_channel.split(".")[1])
        except (IndexError, ValueError):
            continue

        await manager.broadcast(channel_id, message["data"])