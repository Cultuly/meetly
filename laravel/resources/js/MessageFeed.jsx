import { useState, useEffect, useRef, useCallback } from "react";


const REACTIONS = [
  ["like", "👍"],
  ["love", "❤️"],
  ["laugh", "😂"],
  ["party", "🎉"],
];


function csrfToken() {
  return document.querySelector('meta[name="csrf-token"]')?.content ?? "";
}

function laravelPost(url, fields) {
  return fetch(url, {
    method: "POST",
    headers: {
      "X-CSRF-TOKEN": csrfToken(),
      "X-Requested-With": "XMLHttpRequest",
      "Content-Type": "application/x-www-form-urlencoded",
    },
    body: new URLSearchParams(fields),
  });
}

function hhmm(iso) {
  const d = new Date(iso);
  return d.toLocaleTimeString([], { hour: "2-digit", minute: "2-digit" });
}

export default function MessageFeed({ channelId, userId, apiBase, canModerate }) {
  const [messages, setMessages] = useState([]);
  const [draft, setDraft] = useState("");
  const [editingId, setEditingId] = useState(null);
  const [editDraft, setEditDraft] = useState("");
  const [wsStatus, setWsStatus] = useState("connecting");

  const bottomRef = useRef(null);
  const wsRef = useRef(null);

  useEffect(() => {
    let cancelled = false;
    fetch(`${apiBase}/api/channels/${channelId}/messages?limit=50`)
      .then((r) => r.json())
      .then((data) => {
        if (!cancelled) setMessages(data);
      })
      .catch((err) => console.error("История не загрузилась:", err));
    return () => {
      cancelled = true;
    };
  }, [channelId, apiBase]);

  const applyEvent = useCallback((ev) => {
    setMessages((prev) => {
      switch (ev.type) {
        case "message.created": {
          if (prev.some((m) => m.id === ev.message.id)) return prev;
          return [
            ...prev,
            {
              ...ev.message,
              edited_at: ev.message.edited_at ?? null,
              reactions: ev.message.reactions ?? [],
            },
          ];
        }

        case "message.updated":
          return prev.map((m) =>
            m.id === ev.message.id
              ? { ...m, body: ev.message.body, edited_at: ev.message.edited_at }
              : m
          );

        case "message.deleted":
          return prev.filter((m) => m.id !== ev.message_id);

        case "reaction.toggled":
          return prev.map((m) => {
            if (m.id !== ev.message_id) return m;
            const reactions = m.reactions ?? [];
            if (ev.action === "added") {
              return {
                ...m,
                reactions: [...reactions, { user_id: ev.user_id, emoji: ev.emoji }],
              };
            }
            return {
              ...m,
              reactions: reactions.filter(
                (r) => !(r.user_id === ev.user_id && r.emoji === ev.emoji)
              ),
            };
          });

        default:
          return prev;
      }
    });
  }, []);

  useEffect(() => {
    const wsBase = apiBase.replace(/^http/, "ws");
    const ws = new WebSocket(`${wsBase}/ws/channels/${channelId}`);
    wsRef.current = ws;

    ws.onopen = () => setWsStatus("open");
    ws.onclose = () => setWsStatus("closed");
    ws.onerror = () => setWsStatus("closed");
    ws.onmessage = (e) => {
      try {
        applyEvent(JSON.parse(e.data));
      } catch (err) {
        console.error("Битый WS-кадр:", e.data, err);
      }
    };

    return () => ws.close();
  }, [channelId, apiBase, applyEvent]);

  useEffect(() => {
    bottomRef.current?.scrollIntoView({ behavior: "smooth" });
  }, [messages]);


  async function sendMessage() {
    const body = draft.trim();
    if (!body) return;
    setDraft("");
    try {
      await laravelPost(`/channels/${channelId}/messages`, { body });
    } catch (err) {
      console.error("Не отправилось:", err);
      setDraft(body);
    }
  }

  async function toggleReaction(messageId, emojiKey) {
    try {
      await laravelPost(`/messages/${messageId}/reactions`, { emoji: emojiKey });
    } catch (err) {
      console.error("Реакция не прошла:", err);
    }
  }

  async function deleteMessage(messageId) {
    try {
      await laravelPost(`/messages/${messageId}`, { _method: "DELETE" });
    } catch (err) {
      console.error("Не удалилось:", err);
    }
  }

  async function saveEdit(messageId) {
    const body = editDraft.trim();
    if (!body) return;
    try {
      await laravelPost(`/messages/${messageId}`, { _method: "PUT", body });
      setEditingId(null);
      setEditDraft("");
    } catch (err) {
      console.error("Правка не сохранилась:", err);
    }
  }

  function renderMessage(m) {
    const name = m.user?.name ?? "Удалённый пользователь";
    const initial = (m.user?.name ?? "?").charAt(0).toUpperCase();
    const isMine = m.user?.id === userId;
    const canDelete = isMine || canModerate;
    const isEditing = editingId === m.id;

    const reactionState = REACTIONS.map(([key, emoji]) => {
      const group = (m.reactions ?? []).filter((r) => r.emoji === key);
      return {
        key,
        emoji,
        count: group.length,
        reacted: group.some((r) => r.user_id === userId),
      };
    });

    return (
      <div key={m.id} className="flex gap-3 group">
        <div className="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm shrink-0">
          {initial}
        </div>

        <div className="min-w-0">
          <div className="flex items-baseline gap-2">
            <span className="text-sm font-medium">{name}</span>
            <span className="text-xs text-gray-500">{hhmm(m.created_at)}</span>
            {m.edited_at && <span className="text-xs text-gray-500">(изменено)</span>}

            {isMine && !isEditing && (
              <button
                onClick={() => {
                  setEditingId(m.id);
                  setEditDraft(m.body);
                }}
                className="group-hover:opacity-100 transition text-sm text-white hover:text-indigo-400"
              >
                редактировать
              </button>
            )}

            {canDelete && (
              <button
                onClick={() => deleteMessage(m.id)}
                className="group-hover:opacity-100 transition text-sm text-white hover:text-red-400"
              >
                удалить
              </button>
            )}
          </div>

          {isEditing ? (
            <div className="flex gap-2 mt-1">
              <input
                type="text"
                value={editDraft}
                onChange={(e) => setEditDraft(e.target.value)}
                onKeyDown={(e) => e.key === "Enter" && saveEdit(m.id)}
                className="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100 text-sm"
                autoFocus
              />
              <button
                onClick={() => saveEdit(m.id)}
                className="px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm"
              >
                Сохранить
              </button>
              <button
                onClick={() => setEditingId(null)}
                className="px-3 py-1 rounded-lg bg-gray-600 hover:bg-gray-500 text-sm"
              >
                Отмена
              </button>
            </div>
          ) : (
            <p className="text-gray-200 break-words">{m.body}</p>
          )}

          <div className="flex gap-1 mt-1">
            {reactionState.map(({ key, emoji, count, reacted }) => (
              <button
                key={key}
                onClick={() => toggleReaction(m.id, key)}
                className={
                  "px-2 py-0.5 rounded-full text-sm border transition " +
                  (reacted
                    ? "bg-indigo-600/30 border-indigo-500"
                    : "bg-gray-700/40 border-transparent hover:bg-gray-700")
                }
              >
                {emoji}
                {count > 0 && <span className="text-xs text-gray-300 ml-1">{count}</span>}
              </button>
            ))}
          </div>
        </div>
      </div>
    );
  }

  return (
    <div className="flex flex-col h-full">
      <div className="flex-1 overflow-y-auto p-6 space-y-3">
        {messages.length === 0 ? (
          <p className="text-gray-500 text-sm">Сообщений пока нет. Напишите первое.</p>
        ) : (
          messages.map(renderMessage)
        )}
        <div ref={bottomRef} />
      </div>

      <div className="p-4 border-t border-black/30 shrink-0">
        <div className="flex gap-2">
          <input
            type="text"
            value={draft}
            onChange={(e) => setDraft(e.target.value)}
            onKeyDown={(e) => e.key === "Enter" && sendMessage()}
            placeholder="Сообщение"
            autoComplete="off"
            className="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100"
          />
          <button
            onClick={sendMessage}
            className="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500"
          >
            Отправить
          </button>
        </div>
        {wsStatus !== "open" && (
          <p className="text-xs text-gray-500 mt-1">
            {wsStatus === "connecting" ? "Подключение…" : "Соединение потеряно"}
          </p>
        )}
      </div>
    </div>
  );
}
