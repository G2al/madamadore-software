<div class="flex flex-col items-center gap-4 py-4">
    @if ($imagePath)
        <img
            src="{{ Storage::url($imagePath) }}"
            alt="{{ $title }}"
            class="max-w-full h-auto max-h-96 rounded-lg shadow-lg cursor-pointer hover:opacity-90 transition-opacity"
        >
        <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
            {{ $title }} - Clicca per ingrandire
        </p>
    @else
        <div class="text-center text-gray-500 dark:text-gray-400 py-12">
            <svg class="w-16 h-16 mx-auto mb-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p>Nessuna immagine disponibile</p>
        </div>
    @endif
</div>
