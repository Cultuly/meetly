from sqlalchemy import Column, BigInteger, String, Text, DateTime, ForeignKey
from sqlalchemy.orm import relationship

from database.database import Base 


class User(Base):
    __tablename__ = "users"

    id     = Column(BigInteger, primary_key=True)
    name   = Column(String)
    email  = Column(String)
    avatar = Column(String, nullable=True)


class Channel(Base):
    __tablename__ = "channels"

    id           = Column(BigInteger, primary_key=True)
    workspace_id = Column(BigInteger)
    name         = Column(String)


class Message(Base):
    __tablename__ = "messages"

    id         = Column(BigInteger, primary_key=True)
    channel_id = Column(BigInteger)
    user_id    = Column(BigInteger, ForeignKey("users.id"), nullable=True)
    body       = Column(Text)
    edited_at  = Column(DateTime, nullable=True)
    created_at = Column(DateTime)

    user      = relationship("User")
    reactions = relationship("Reaction", back_populates="message")


class Reaction(Base):
    __tablename__ = "reactions"

    id         = Column(BigInteger, primary_key=True)
    message_id = Column(BigInteger, ForeignKey("messages.id"))
    user_id    = Column(BigInteger)
    emoji      = Column(String)

    message = relationship("Message", back_populates="reactions")