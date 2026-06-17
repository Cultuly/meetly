<x-shell-layout>
    <div class="p-8">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h1 class="text-xl font-semibold">{{ $workspace->name }}</h1>
                <span class="text-xs text-gray-400">
                    {{ $workspace->visibility === 'public' ? 'Публичное' : 'Приватное' }}
                </span>
            </div>

            {{-- @can('update', $workspace) спрашивает ТУ ЖЕ WorkspacePolicy::update.
                 То есть кнопки правки/удаления видит только владелец — политика
                 управляет не только доступом на сервере, но и тем, что в UI. --}}
            @can('update', $workspace)
                <div class="flex gap-2">
                    <a href="{{ route('workspaces.edit', $workspace) }}"
                       class="px-3 py-1.5 rounded-lg bg-gray-600 hover:bg-gray-500 text-sm">Редактировать</a>

                    {{-- Удаление = DELETE-запрос. В HTML-форме нет метода DELETE,
                         поэтому форму шлём POST, а @method('DELETE') подменяет глагол
                         (Laravel прочитает скрытое поле _method). --}}
                    <form method="POST" action="{{ route('workspaces.destroy', $workspace) }}"
                          onsubmit="return confirm('Удалить пространство со всеми каналами?')">
                        @csrf
                        @method('DELETE')
                        <button class="px-3 py-1.5 rounded-lg bg-red-600 hover:bg-red-500 text-sm">Удалить</button>
                    </form>
                </div>
            @endcan
        </div>

        <h2 class="text-sm uppercase text-gray-400 mb-2">Каналы</h2>
        <ul class="space-y-1">
            {{-- $workspace->channels подгружены в контроллере через ->load('channels').
                 Сейчас список пуст — каналы появятся на шаге 2 (ChannelController). --}}
            @forelse ($workspace->channels as $channel)
                <li class="px-3 py-2 rounded-lg bg-gray-700">
                    <span class="text-gray-400">#</span> {{ $channel->name }}
                </li>
            @empty
                <li class="text-sm text-gray-400">Каналов пока нет</li>
            @endforelse
        </ul>
    </div>
</x-shell-layout>