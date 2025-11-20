<div class="flex flex-col items-center justify-center w-full h-full min-h-screen bg-gray-900 dark:bg-gray-950 p-0 m-0">
    @if ($imagePath)
        <div class="relative w-full h-full flex items-center justify-center p-8">
            <img
                src="{{ Storage::url($imagePath) }}"
                alt="{{ $title }}"
                class="max-w-full max-h-full object-contain shadow-2xl"
            >
        </div>
    @else
        <div class="text-center text-gray-400 dark:text-gray-500 flex flex-col items-center justify-center h-full">
            <svg class="w-24 h-24 mb-6 opacity-40" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
            </svg>
            <p class="text-lg">Nessuna immagine disponibile</p>
        </div>
    @endif
</div>
