<x-shell-layout>
    <div class="max-w-lg mx-auto p-8">
        <h1 class="text-xl font-semibold mb-6">Новое пространство</h1>

        <form method="POST" action="{{ route('workspaces.store') }}" class="space-y-5">
            @csrf

            <div>
                <label class="block text-sm mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name') }}"
                       class="w-full rounded-lg bg-gray-700 border-gray-600 text-gray-100">
                @error('name')
                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm mb-1">Видимость</label>
                <select name="visibility" class="w-full rounded-lg bg-gray-700 border-gray-600 text-gray-100">
                    <option value="public"  @selected(old('visibility') === 'public')>Публичное</option>
                    <option value="private" @selected(old('visibility') === 'private')>Приватное</option>
                </select>
                @error('visibility')
                    <p class="text-sm text-red-400 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-3">
                <button class="px-4 py-2 rounded-lg bg-indigo-600 hover:bg-indigo-500">Создать</button>
                <a href="{{ route('workspaces.index') }}" class="px-4 py-2 rounded-lg bg-gray-600 hover:bg-gray-500">Отмена</a>
            </div>
        </form>
    </div>
</x-shell-layout>