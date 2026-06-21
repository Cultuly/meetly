import { createRoot } from "react-dom/client";
import MessageFeed from "./MessageFeed.jsx";

const el = document.getElementById("message-feed");

if (el) {
  createRoot(el).render(
    <MessageFeed
      channelId={Number(el.dataset.channelId)}
      userId={Number(el.dataset.userId)}
      apiBase={el.dataset.apiBase}
      canModerate={el.dataset.canModerate === "1"}
    />
  );
}
