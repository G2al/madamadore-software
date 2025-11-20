<div class="flex flex-col items-center justify-center w-full h-full min-h-screen bg-gray-900 dark:bg-gray-950 p-0 m-0">
    @if ($record->image)
        <div class="relative w-full h-full flex flex-col items-center justify-center p-8">
            <img
                src="{{ Storage::url($record->image) }}"
                alt="{{ $record->name }}"
                class="max-w-full max-h-[80vh] object-contain shadow-2xl"
            >

            <div class="text-center mt-6 bg-gray-800 dark:bg-gray-900 p-4 rounded-lg max-w-2xl">
                <h3 class="text-xl font-bold text-white">{{ $record->name }}</h3>
                <p class="text-sm text-gray-300">{{ $record->type ?? 'N/A' }}</p>
                @if ($record->color_code)
                    <p class="text-sm text-gray-300">Codice Colore: {{ $record->color_code }}</p>
                @endif
            </div>

            <a
                href="{{ Storage::url($record->image) }}"
                target="_blank"
                class="mt-4 inline-flex items-center gap-2 px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white rounded-lg transition text-lg"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
                Scarica Foto
            </a>
        </div>
    @else
        <div class="text-center text-gray-400 dark:text-gray-500 flex flex-col items-center justify-center h-full">
            <svg class="w-24 h-24 mb-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-lg">Nessuna foto disponibile</p>
        </div>
    @endif
</div>
