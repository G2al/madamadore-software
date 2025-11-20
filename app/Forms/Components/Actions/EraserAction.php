<?php

namespace App\Forms\Components\Actions;

use Filament\Forms\Components\Actions\Action;

class EraserAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'eraser';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('🧹 Gomma')
            ->color('secondary')
            ->icon('heroicon-o-trash-2')
            ->size('md');
    }
}
