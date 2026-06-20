<x-shell-layout>
    <div class="flex flex-col h-full">
        {{-- Шапка канала --}}
        <div class="h-14 px-6 flex items-center border-b border-black/30 shrink-0">
            <span class="text-gray-400 mr-1">#</span>
            <span class="font-semibold">{{ $channel->name }}</span>
        </div>

        {{-- Лента сообщений --}}
        <div class="flex-1 overflow-y-auto p-6 space-y-3">
            @forelse ($channel->messages as $message)
                <div class="flex gap-3 group">
                    {{-- Иконка --}}
                    <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm shrink-0">
                        {{ mb_strtoupper(mb_substr($message->user?->name ?? '?', 0, 1)) }}
                    </div>

                    <div class="min-w-0" x-data="{ editing: false }">
                        <div class="flex items-baseline gap-2">
                            <span class="text-sm font-medium">{{ $message->user?->name ?? 'Удалённый пользователь' }}</span>
                            <span class="text-xs text-gray-500">{{ $message->created_at->format('H:i') }}</span>

                            @if ($message->edited_at)
                                <span class="text-xs text-gray-500">(изменено)</span>
                            @endif

                            @if ($message->user_id === auth()->id())
                                <button @click="editing = true"
                                        x-show="!editing"
                                        class="opacity-0 group-hover:opacity-100 transition text-sm text-white hover:text-indigo-400">
                                    редактировать
                                </button>
                            @endif

                            {{-- Кнопка удаления (автор/владелец) --}}
                            @if ($message->user_id === auth()->id() || auth()->user()->can('update', $channel->workspace))
                                <form method="POST" action="{{ route('messages.destroy', $message) }}"
                                      class="opacity-0 group-hover:opacity-100 transition">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-white hover:text-red-400">удалить</button>
                                </form>
                            @endif
                        </div>

                        <p x-show="!editing" class="text-gray-200 break-words">{{ $message->body }}</p>

                        {{-- Форма редактирования --}}
                        <form x-show="editing" x-cloak method="POST" action="{{ route('messages.update', $message) }}"
                              class="flex gap-2 mt-1">
                            @csrf
                            @method('PUT')
                            <input type="text" name="body" value="{{ $message->body }}"
                                   class="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100 text-sm">
                            <button class="px-3 py-1 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm">Сохранить</button>
                            <button type="button" @click="editing = false"
                                    class="px-3 py-1 rounded-lg bg-gray-600 hover:bg-gray-500 text-sm">Отмена</button>
                        </form>
                        @php
                            $reactionMap = [
                                'like'  => '👍',
                                'love'  => '❤️',
                                'laugh' => '😂',
                                'party' => '🎉',
                            ];

                            $grouped = $message->reactions->groupBy('emoji');
                        @endphp

                        <div class="flex gap-1 mt-1">
                            @foreach ($reactionMap as $key => $emoji)
                                @php
                                    $group   = $grouped->get($key);
                                    $count   = $group?->count() ?? 0;
                                    $reacted = $group?->contains('user_id', auth()->id()) ?? false;
                                @endphp

                                <form method="POST" action="{{ route('reactions.toggle', $message) }}">
                                    @csrf
                                    <input type="hidden" name="emoji" value="{{ $key }}">
                                    <button @class([
                                        'px-2 py-0.5 rounded-full text-sm border transition',
                                        'bg-indigo-600/30 border-indigo-500' => $reacted,
                                        'bg-gray-700/40 border-transparent hover:bg-gray-700' => ! $reacted,
                                    ])>
                                        {{ $emoji }}@if ($count > 0)<span class="text-xs text-gray-300 ml-1">{{ $count }}</span>@endif
                                    </button>
                                </form>
                            @endforeach
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-sm">Сообщений пока нет. Напишите первое.</p>
            @endforelse
        </div>

        {{-- Форма отправки --}}
        <div class="p-4 border-t border-black/30 shrink-0">
            <form method="POST" action="{{ route('messages.store', $channel) }}" class="flex gap-2">
                @csrf
                <input type="text" name="body" placeholder="Сообщение в #{{ $channel->name }}" autocomplete="off"
                       class="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500">Отправить</button>
            </form>
            @error('body')<p class="text-sm text-red-400 mt-1">{{ $message }}</p>@enderror
        </div>
    </div>
</x-shell-layout>