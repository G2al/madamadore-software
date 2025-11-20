@php
    use Filament\Support\Facades\FilamentView;
    use Saade\FilamentAutograph\Forms\Components\Enums\DownloadableFormat;
@endphp

<x-dynamic-component
    :component="$getFieldWrapperView()"
    :field="$field"
>
    @php
        $isDisabled = $isDisabled();
        $isClearable = $isClearable();
        $isDownloadable = $isDownloadable();
        $downloadableFormats = $getDownloadableFormats();
        $downloadActionDropdownPlacement = $getDownloadActionDropdownPlacement() ?? 'bottom-start';
        $isUndoable = $isUndoable();
        $isConfirmable = $isConfirmable();
        $loadStrategy = $getLoadStrategy();
        $hasEraser = $hasEraser();

        $clearAction = $getAction('clear');
        $downloadAction = $getAction('download');
        $undoAction = $getAction('undo');
        $doneAction = $getAction('done');
    @endphp

    <div
        @if (FilamentView::hasSpaMode())
            {{-- format-ignore-start --}}x-load="visible || event (ax-modal-opened)"{{-- format-ignore-end --}}
        @else
            x-load
        @endif
        x-load-src="{{ \Filament\Support\Facades\FilamentAsset::getAlpineComponentSrc('filament-autograph', 'saade/filament-autograph') }}"
        x-data="signaturePadFormComponent({
            backgroundColor: @js($getBackgroundColor()),
            backgroundColorOnDark: @js($getBackgroundColorOnDark()),
            confirmable: @js($isConfirmable),
            disabled: @js($isDisabled),
            dotSize: {{ $getDotSize() }},
            exportBackgroundColor: @js($getExportBackgroundColor()),
            exportPenColor: @js($getExportPenColor()),
            filename: '{{ $getFilename() }}',
            maxWidth: {{ $getLineMaxWidth() }},
            minDistance: {{ $getMinDistance() }},
            minWidth: {{ $getLineMinWidth() }},
            penColor: @js($getPenColor()),
            penColorOnDark: @js($getPenColorOnDark()),
            state: $wire.{{ $applyStateBindingModifiers("\$entangle('{$getStatePath()}')") }},
            throttle: {{ $getThrottle() }},
            velocityFilterWeight: {{ $getVelocityFilterWeight() }},
            eraserMode: false,
        })"
        x-init="$nextTick(() => {
            setTimeout(() => {
                initSignaturePad();
                @if ($hasEraser)
                    // Aggancia la gomma al canvas
                    const canvasEl = $refs.canvas;
                    canvasEl.addEventListener('mousemove', (e) => {
                        if ($data.eraserMode) {
                            const ctx = canvasEl.getContext('2d');
                            const rect = canvasEl.getBoundingClientRect();
                            const x = e.clientX - rect.left;
                            const y = e.clientY - rect.top;

                            if (e.buttons === 1) {
                                ctx.clearRect(x - 5, y - 5, 15, 15);
                            }
                        }
                    });
                @endif
            }, 200)
        }}"
        x-bind="eventListeners"
    >
        <canvas
            x-ref="canvas"
            wire:ignore
            @class([
                'w-full h-36 rounded-lg border border-gray-300',
                'dark:bg-gray-900 dark:border-white/10',
                'opacity-75 bg-gray-50' => $isDisabled,
            ])
        ></canvas>

        <div class="flex items-center justify-end m-1 space-x-2">
            @if ($isClearable)
                {{ $clearAction }}
            @endif

            @if ($isUndoable)
                {{ $undoAction }}
            @endif

            @if ($hasEraser)
                <button
                    type="button"
                    @click="eraserMode = !eraserMode"
                    @class([
                        'relative inline-flex items-center justify-center px-3 py-2 text-sm font-medium rounded-lg',
                        'transition-all duration-200',
                        'bg-gray-200 text-gray-700 hover:bg-gray-300 dark:bg-gray-700 dark:text-gray-200 dark:hover:bg-gray-600' => '!eraserMode',
                        'bg-red-500 text-white hover:bg-red-600 dark:bg-red-600 dark:hover:bg-red-700 shadow-lg' => 'eraserMode',
                        'disabled:opacity-50 disabled:cursor-not-allowed' => $isDisabled,
                    ])
                    :disabled="$isDisabled"
                    title="Attiva/disattiva gomma per cancellare"
                >
                    <span x-show="!eraserMode" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.303 1.697a4.995 4.995 0 00-7.07 0L2.697 12.303a4.995 4.995 0 007.07 7.07l9.606-9.606a5 5 0 000-7.07z"></path>
                        </svg>
                        Gomma
                    </span>
                    <span x-show="eraserMode" class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L9.172 12M9 12l6 6m0-6l-6-6"></path>
                        </svg>
                        Penna
                    </span>
                </button>
            @endif

            @if ($isDownloadable)
                <x-filament::dropdown placement="{{ $downloadActionDropdownPlacement }}">
                    <x-slot name="trigger">
                        {{ $downloadAction }}
                    </x-slot>

                    <x-filament::dropdown.list>
                        @if (in_array(DownloadableFormat::PNG, $downloadableFormats))
                            <x-filament::dropdown.list.item
                                x-on:click="downloadAs('{{ DownloadableFormat::PNG->getMime() }}', '{{ DownloadableFormat::PNG->getExtension() }}')"
                            >
                                {{ DownloadableFormat::PNG->getLabel() }}
                            </x-filament::dropdown.list.item>
                        @endif

                        @if (in_array(DownloadableFormat::JPG, $downloadableFormats))
                            <x-filament::dropdown.list.item
                                x-on:click="downloadAs('{{ DownloadableFormat::JPG->getMime() }}', '{{ DownloadableFormat::JPG->getExtension() }}')"
                            >
                                {{ DownloadableFormat::JPG->getLabel() }}
                            </x-filament::dropdown.list.item>
                        @endif

                        @if (in_array(DownloadableFormat::SVG, $downloadableFormats))
                            <x-filament::dropdown.list.item
                                x-on:click="downloadAs('{{ DownloadableFormat::SVG->getMime() }}', '{{ DownloadableFormat::SVG->getExtension() }}')"
                            >
                                {{ DownloadableFormat::SVG->getLabel() }}
                            </x-filament::dropdown.list.item>
                        @endif
                    </x-filament::dropdown.list>
                </x-filament::dropdown>
            @endif

            @if ($isConfirmable)
                {{ $doneAction }}
            @endif
        </div>
    </div>
</x-dynamic-component>
