from fastapi import APIRouter, WebSocket, WebSocketDisconnect

from realtime.connection_manager import manager

router = APIRouter()


@router.websocket("/ws/channels/{channel_id}")
async def channel_socket(websocket: WebSocket, channel_id: int):
    await manager.connect(channel_id, websocket)
    try:
        while True:
            await websocket.receive_text()
    except WebSocketDisconnect:
        manager.disconnect(channel_id, websocket)