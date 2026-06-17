<x-shell-layout>
    <div class="h-full flex flex-col items-center justify-center text-center text-gray-400">
        <p class="text-lg">Выберите пространство</p>
        <p class="text-sm">или
            <a href="{{ route('workspaces.create') }}" class="text-indigo-400 hover:underline">создайте новое</a>
        </p>
    </div>
</x-shell-layout>