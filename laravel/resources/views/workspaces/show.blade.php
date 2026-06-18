<x-shell-layout>
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold">{{ $workspace->name }}</h1>
                <span class="text-xs text-gray-400">
                    {{ $workspace->visibility === 'public' ? 'Публичное' : 'Приватное' }}
                </span>
            </div>

            @can('update', $workspace)
                <div class="flex gap-2">
                    <a href="{{ route('workspaces.edit', $workspace) }}"
                       class="px-3 py-1.5 rounded-lg bg-gray-600 hover:bg-gray-500 text-sm">Редактировать</a>

                    <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}"
                          onsubmit="return confirm('Удалить пространство со всеми каналами?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-sm">Удалить</button>
                    </form>
                </div>
            @endcan
        </div>

        <div class="flex items-center justify-between mb-2">
            <h2 class="text-sm uppercase text-gray-400">Каналы</h2>
        </div>

        <ul class="space-y-1 mb-4">
            @forelse ($workspace->channels as $channel)
                <li class="px-3 py-2 rounded-lg bg-gray-700 flex items-center justify-between">
                    <a href="{{ route('channels.show', $channel) }}" class="hover:underline">
                        <span class="text-gray-400">#</span> {{ $channel->name }}
                    </a>

                    @can('update', $workspace)
                        <form method="POST" action="{{ route('channels.destroy', $channel) }}"
                              onsubmit="return confirm('Удалить канал со всеми сообщениями?')">
                            @csrf
                            @method('DELETE')
                            <button class="text-xs text-gray-400 hover:text-red-400">удалить</button>
                        </form>
                    @endcan
                </li>
            @empty
                <li class="text-sm text-gray-400">Каналов пока нет.</li>
            @endforelse
        </ul>

        @can('update', $workspace)
            <form method="POST" action="{{ route('channels.store', $workspace) }}" class="flex gap-2">
                @csrf
                <input type="text" name="name" placeholder="Новый канал"
                       class="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100 text-sm">
                <button class="px-3 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm">Добавить</button>
                @error('name')<p class="w-full text-sm text-red-400 mt-1">{{ $message }}</p>@enderror
            </form>
        @endcan
    </div>
</x-shell-layout>