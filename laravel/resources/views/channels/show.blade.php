<x-shell-layout>
    <div class="flex flex-col h-full">
        {{-- Шапка канала --}}
        <div class="h-14 px-6 flex items-center border-b border-black/30 shrink-0">
            <a href="{{ route('workspaces.show', $channel->workspace) }}"
                class="text-gray-400 hover:text-white mr-3" title="Назад в пространство">
                    ← Назад в пространство
            </a>
            <span class="text-gray-400 mr-1">#</span>
            <span class="font-semibold">{{ $channel->name }}</span>
        </div>

        <div id="message-feed"
             class="flex-1 min-h-0"
             data-channel-id="{{ $channel->id }}"
             data-user-id="{{ auth()->id() }}"
             data-api-base="{{ config('app.api_base') }}"
             data-can-moderate="{{ auth()->user()->can('update', $channel->workspace) ? '1' : '0' }}">
        </div>
    </div>

    @vite('resources/js/app.jsx')
</x-shell-layout>