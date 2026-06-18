<x-shell-layout>
    <div class="max-w-2xl mx-auto p-8">
        <h1 class="text-xl font-semibold mb-4">Поиск пространств</h1>

        <form method="GET" action="{{ route('workspaces.index') }}" class="flex gap-2 mb-6">
            <input type="text" name="q" value="{{ $query }}" placeholder="Название публичного пространства"
                   class="flex-1 rounded-lg bg-gray-700 border-gray-600 text-gray-100">
            <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500">Найти</button>
        </form>

        @if ($query !== '')
            <ul class="space-y-2">
                @forelse ($results as $workspace)
                    <li class="px-4 py-3 rounded-lg bg-gray-700 flex items-center justify-between">
                        <span>{{ $workspace->name }}</span>

                        @if ($myIds->contains($workspace->id))
                            <a href="{{ route('workspaces.show', $workspace) }}"
                               class="text-sm text-indigo-400 hover:underline">Открыть</a>
                        @else
                            <form method="POST" action="{{ route('workspaces.join', $workspace) }}">
                                @csrf
                                <button class="px-3 py-1.5 rounded-lg bg-indigo-600 hover:bg-indigo-500 text-sm">Вступить</button>
                            </form>
                        @endif
                    </li>
                @empty
                    <li class="text-gray-400 text-sm">Ничего не найдено.</li>
                @endforelse
            </ul>
        @else
            <p class="text-gray-400 text-sm">Введите название, чтобы найти публичные пространства, или
                <a href="{{ route('workspaces.create') }}" class="text-indigo-400 hover:underline">создайте своё</a>.
            </p>
        @endif
    </div>
</x-shell-layout>