import os

from sqlalchemy.ext.asyncio import create_async_engine, async_sessionmaker, AsyncSession
from sqlalchemy.orm import declarative_base

# Конфиг БД
DB_USER     = os.getenv("MYSQL_USER", "meetly")
DB_PASSWORD = os.getenv("MYSQL_PASSWORD", "meetly")
DB_NAME     = os.getenv("MYSQL_DATABASE", "meetly")
DB_HOST     = os.getenv("DB_HOST", "mysql")
DB_PORT     = os.getenv("DB_PORT", "3306")

DATABASE_URL = f"mysql+aiomysql://{DB_USER}:{DB_PASSWORD}@{DB_HOST}:{DB_PORT}/{DB_NAME}"
engine = create_async_engine(DATABASE_URL, echo=False, pool_pre_ping=True)

# Фабрика сессий
AsyncSessionLocal = async_sessionmaker(engine, class_=AsyncSession, expire_on_commit=False)

Base = declarative_base()

async def get_session() -> AsyncSession:
    async with AsyncSessionLocal() as session:
        yield session