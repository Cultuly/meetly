from fastapi import APIRouter, Depends, HTTPException, Query
from sqlalchemy import select, func
from sqlalchemy.ext.asyncio import AsyncSession
from sqlalchemy.orm import selectinload

from database.database import get_session
from database.models import Message, Channel
from database.schemas import MessageOut, ChannelOut


router = APIRouter(prefix="/api", tags=["rest"])

@router.get("/channels/{channel_id}/messages", response_model=list[MessageOut])
async def list_messages(
    channel_id: int,
    limit: int = Query(50, ge=1, le=100),
    offset: int = Query(0, ge=0),
    session: AsyncSession = Depends(get_session),
):

    stmt = (
        select(Message)
        .where(Message.channel_id == channel_id)

        .options(selectinload(Message.user), selectinload(Message.reactions))
        .order_by(Message.created_at) 
        .limit(limit)
        .offset(offset)
    )

    result = await session.execute(stmt)
    return result.scalars().all()


@router.get("/messages/{message_id}", response_model=MessageOut)
async def get_message(
    message_id: int,
    session: AsyncSession = Depends(get_session),
):
    stmt = (
        select(Message)
        .where(Message.id == message_id)
        .options(selectinload(Message.user), selectinload(Message.reactions))
    )
    result = await session.execute(stmt)
    message = result.scalar_one_or_none()
    if message is None:
        raise HTTPException(status_code=404, detail="Message not found")
    return message


@router.get("/workspaces/{workspace_id}/channels", response_model=list[ChannelOut])
async def list_channels(
    workspace_id: int,
    session: AsyncSession = Depends(get_session),
):
    stmt = (
        select(Channel)
        .where(Channel.workspace_id == workspace_id)
        .order_by(Channel.name)
    )
    result = await session.execute(stmt)
    return result.scalars().all()