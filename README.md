# ================ Meetly ================
## Веб-приложение для командного общения в пространствах в реальном времени.

**Аналоги: Slack / Discord / Пачка / Zulip**

## Схема архитектуры

```
                       ┌─────────────────────────────┐
                       │           Браузер           │
                       │  Blade-страницы (SSR)       │
                       │  + React-компонент (канал)  │
                       └─────────────┬───────────────┘
                                     │ HTTPS (443)
                       ┌─────────────▼───────────────┐
                       │            Nginx            │
                       │   маршрутизация по Host:    │
                       └──────┬───────────────┬──────┘
            Host: meetly.ru   │               │   Host: api.meetly.ru
                              ▼               ▼
                    ┌──────────────┐    ┌──────────────┐
                    │   Laravel    │    │   FastAPI    │
                    │  (php-fpm)   │    │  (uvicorn)   │
                    │ SSR + ЗАПИСЬ │    │  REST + WS   │
                    └───┬──────┬───┘    └───┬──────┬───┘
                 запись │      │ publish    │чтение│ subscribe
                        ▼      ▼            ▼      ▼
                   ┌────────┐ ┌───────────────┐ ┌────────┐
                   │ MySQL  │ │     Redis     │ │ MySQL  │
                   │(запись)│ │    Pub/Sub    │ │(чтение)│
                   └────────┘ └───────────────┘ └────────┘
                        └──────── одна БД ──────────┘
```

**Кто за что отвечает:**

- **Nginx** принимает HTTPS и маршрутизирует по заголовку `Host`: `meetly.ru` → Laravel, `api.meetly.ru` → FastAPI. WebSocket-соединения проксируются с заголовками `Upgrade`/`Connection`.
- **Laravel** рендерит страницы на сервере и пишет в MySQL. При значимых действиях публикует событие в Redis.
- **FastAPI** читает MySQL (read-only), слушает Redis и пушит события подключённым WebSocket клиентам.
- **Redis** — связка между Laravel и FastAPI (без прямых запросов).

---

## Структура БД

| Таблица | Назначение | Ключевые поля и связи |
|---|---|---|
| `users` | Пользователи | `id`, `name`, `email`, `password`, `github_id`, `avatar` |
| `workspaces` | Рабочие пространства | `id`, **`owner_id` → users**, `name`, `visibility` (`public`/`private`), `invite_token` |
| `channels` | Каналы внутри пространства | `id`, **`workspace_id` → workspaces**, `name` |
| `workspace_user` | Участники (M:N) | **`workspace_id` → workspaces**, **`user_id` → users**, уникальная пара |
| `messages` | Сообщения в канале | `id`, **`channel_id` → channels**, **`user_id` → users** (nullable), `body`, `edited_at` |
| `reactions` | Реакции на сообщения | `id`, **`message_id` → messages**, **`user_id` → users**, `emoji`, уникальная тройка |

**Связи:** `users oneToMany workspaces` (владелец) · `users manyToMany workspaces` (участники через `workspace_user`) · `workspaces oneToMany channels` · `channels oneToMany messages` · `messages oneToMany reactions`.

**Индексы:** на всех внешних ключах (создаются автоматически), составной индекс `messages(channel_id, created_at)` под запрос ленты, уникальные ограничения на `workspace_user(workspace_id, user_id)` и `reactions(message_id, user_id, emoji)`.

---


# ============ ПОДГОТОВКА ============

## Подготовка машины (разово)

Проект работает на двух локальных доменах по HTTPS. Чтобы они резолвились и браузер доверял самоподписанному сертификату — три шага.

### 1. Добавить домены в `hosts`

**Windows** (PowerShell **от администратора**):

```powershell
Add-Content "$env:WINDIR\System32\drivers\etc\hosts" "127.0.0.1 meetly.ru api.meetly.ru"
```

**Linux / macOS:**

```bash
echo "127.0.0.1 meetly.ru api.meetly.ru" | sudo tee -a /etc/hosts
```

### 2. Сгенерировать сертификат

Из корня репозитория (WSL / Git Bash / Linux / macOS — нужен `openssl`):

```bash
bash nginx/gen-certs.sh
```

Скрипт создаёт `nginx/certs/meetly.crt` и `meetly.key` с SAN на оба домена.

### 3. Добавить сертификат в доверенные

### Без них React не сможет ходить с `meetly.ru` на `api.meetly.ru` (браузер режет cross-origin запросы к недоверенному сертификату).

**Windows** (PowerShell **от администратора**), затем **полностью перезапустить браузер**:

### Вариант для WSL
```powershell
Import-Certificate -FilePath "путь до директории meetly\nginx\certs\meetly.crt" -CertStoreLocation Cert:\LocalMachine\Root
```

Пример правильной команды:
```powershell
Import-Certificate -FilePath ""\\wsl.localhost\Debian\home\cultuly\test\meetly\nginx\certs\meetly.crt"" -CertStoreLocation Cert:\LocalMachine\Root
```

**Linux:**

```bash
sudo cp nginx/certs/meetly.crt /usr/local/share/ca-certificates/meetly.crt
sudo update-ca-certificates
```

**macOS:** открыть `meetly.crt` в Keychain Access → добавить в System → выставить «Always Trust».

---

# ============== ЗАПУСК ==============

```bash
# 1. Клонировать
git clone https://github.com/Cultuly/meetly.git
cd meetly

# 2. Создать корневой .env (значения для MySQL уже рабочие)
cp .env.example .env

# 3. Поднять стек (--build обязателен при первом запуске)
docker compose up -d --build

# 4. Прогнать миграции и запустить сидер
docker compose exec laravel php artisan migrate --seed

# 5. Открыть в браузере
https://meetly.ru
```

---

**Данные для теста**:

- Email: `alice@meetly.test` · Пароль: `password`
- Email: `bob@meetly.test` · Пароль: `password`

---
