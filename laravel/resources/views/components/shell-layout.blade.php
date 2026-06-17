@php
    $activeWorkspaceId = request()->route('workspace')?->id;
@endphp
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Meetly') }}</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased">
    <div class="flex h-screen text-gray-100">

        {{-- Сайдбар --}}
        <aside class="w-72 bg-gray-900 flex flex-col">

            {{-- Иконка --}}
            <div class="h-14 flex items-center gap-2 px-4 border-b border-black/30">
                <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center font-semibold">
                    M
                </div>
                <span class="font-semibold">Meetly</span>
            </div>

            {{-- Список всех пространств --}}
            <nav class="flex-1 overflow-y-auto p-2 space-y-1">
                @forelse ($sidebarWorkspaces as $workspace)
                    <a href="{{ route('workspaces.show', $workspace) }}"
                       @class([
                           'flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-gray-700/60',
                           'bg-gray-700' => $workspace->id === $activeWorkspaceId,
                       ])>
                        {{-- Иконка конкретного пространства --}}
                        <span class="w-8 h-8 shrink-0 rounded-lg bg-gray-700 flex items-center justify-center text-sm">
                            {{ mb_strtoupper(mb_substr($workspace->name, 0, 1)) }}
                        </span>
                        <span class="truncate">{{ $workspace->name }}</span>
                    </a>
                @empty
                    <p class="px-3 py-2 text-sm text-gray-400">Пока нет пространств</p>
                @endforelse

                {{-- Кнопка создания --}}
                <a href="{{ route('workspaces.create') }}"
                   class="flex items-center gap-3 px-3 py-2 rounded-lg text-gray-300 hover:bg-gray-700/60">
                    <span class="w-8 h-8 shrink-0 rounded-lg bg-gray-700 flex items-center justify-center text-lg">+</span>
                    <span>Создать пространство</span>
                </a>
            </nav>

            {{-- Профиль пользователя + кнопка выхода --}}
            <div class="h-16 px-3 flex items-center gap-3 bg-gray-950">
                <div class="w-9 h-9 rounded-full bg-indigo-500 flex items-center justify-center text-sm shrink-0">
                    {{ mb_strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium truncate">{{ auth()->user()->name }}</p>
                    <a href="{{ route('profile.edit') }}" class="text-xs text-gray-400 hover:underline">Профиль</a>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="text-xs text-gray-400 hover:text-white">Выйти</button>
                </form>
            </div>
        </aside>

        {{-- Содержимое страницы --}}
        <main class="flex-1 bg-gray-800 overflow-y-auto">
            {{ $slot }}
        </main>
    </div>
</body>
</html>