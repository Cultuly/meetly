from datetime import datetime
from pydantic import BaseModel, ConfigDict


class UserOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    name: str


class ReactionOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    user_id: int
    emoji: str


class MessageOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    body: str
    edited_at: datetime | None
    created_at: datetime

    user: UserOut | None
    reactions: list[ReactionOut]


class ChannelOut(BaseModel):
    model_config = ConfigDict(from_attributes=True)

    id: int
    workspace_id: int
    name: str