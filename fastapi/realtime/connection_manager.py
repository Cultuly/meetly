from fastapi import WebSocket


class ConnectionManager:
    def __init__(self) -> None:
        self.channels: dict[int, set[WebSocket]] = {}

    async def connect(self, channel_id: int, websocket: WebSocket) -> None:
        await websocket.accept()
        self.channels.setdefault(channel_id, set()).add(websocket)

    def disconnect(self, channel_id: int, websocket: WebSocket) -> None:
        conns = self.channels.get(channel_id)
        if conns:
            conns.discard(websocket)
            if not conns:
                del self.channels[channel_id]

    async def broadcast(self, channel_id: int, message: str) -> None:
        conns = self.channels.get(channel_id)
        if not conns:
            return
        dead = []
        for ws in list(conns):
            try:
                await ws.send_text(message)
            except Exception:
                dead.append(ws)
        for ws in dead:
            self.disconnect(channel_id, ws)

manager = ConnectionManager()