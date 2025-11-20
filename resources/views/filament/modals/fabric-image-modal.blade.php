<div class="flex flex-col items-center gap-4">
    @if ($record->image)
        <img
            src="{{ Storage::url($record->image) }}"
            alt="{{ $record->name }}"
            class="max-w-full h-auto max-h-96 rounded-lg shadow-lg"
        >

        <div class="text-center">
            <h3 class="text-lg font-bold text-gray-900 dark:text-white">{{ $record->name }}</h3>
            <p class="text-sm text-gray-600 dark:text-gray-400">{{ $record->type ?? 'N/A' }}</p>
            @if ($record->color_code)
                <p class="text-sm text-gray-600 dark:text-gray-400">Codice Colore: {{ $record->color_code }}</p>
            @endif
        </div>

        <a
            href="{{ Storage::url($record->image) }}"
            target="_blank"
            class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition"
        >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
            </svg>
            Scarica Foto
        </a>
    @else
        <div class="text-center text-gray-500 dark:text-gray-400 py-8">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p>Nessuna foto disponibile</p>
        </div>
    @endif
</div>
