<x-filament-widgets::widget class="fi-wi-stats-overview">
    <div class="mb-4 flex items-center justify-between">
        <h2 class="text-lg font-semibold">Economica</h2>
        <button 
            wire:click="toggleValues"
            class="text-gray-500 hover:text-gray-700 transition-colors"
            title="{{ $showValues ? 'Nascondi valori' : 'Mostra valori' }}"
        >
            @if($showValues)
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 4.5C7 4.5 2.73 7.61 1 12c1.73 4.39 6 7.5 11 7.5s9.27-3.11 11-7.5c-1.73-4.39-6-7.5-11-7.5zM12 17c-2.76 0-5-2.24-5-5s2.24-5 5-5 5 2.24 5 5-2.24 5-5 5zm0-8c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"/>
                </svg>
            @else
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M11.83 9L15.23 12.39c.75-.52 1.25-1.37 1.25-2.39 0-2.21-1.79-4-4-4-.99 0-1.87.35-2.39 1.03zM19.08 15.54c1.71-1.25 2.92-3.13 2.92-5.54 0-3.97-3.03-7-7-7-2.41 0-4.53 1.23-5.78 3.07l2.73 2.73c.5-.97 1.48-1.8 2.73-1.8 2.21 0 4 1.79 4 4 0 1.02-.4 1.94-1.07 2.61l2.47 2.93zM2.1 3.51l1.73 1.73 2.36 2.36C5.09 8.22 4 10.18 4 12c0 3.97 3.03 7 7 7 2.36 0 4.41-1.13 5.75-2.86l2.73 2.73 1.46-1.46L3.54 2.05 2.1 3.51zM12 4c-4.42 0-8 3.58-8 8 0 1.04.2 2.04.54 3.01L9.46 15c-.33-.32-.52-.77-.52-1.27 0-1.66 1.34-3 3-3 .5 0 .95.19 1.27.52l2.07 2.07c.97-.27 1.88-.82 2.61-1.57-1.25-1.25-2-2.97-2-4.75 0-3.87 3.13-7 7-7-1.78 0-3.5-.75-4.75-2z"/>
                </svg>
            @endif
        </button>
    </div>

    <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-5">
        @foreach($this->getStats() as $stat)
            {{ $stat }}
        @endforeach
    </div>
</x-filament-widgets::widget>