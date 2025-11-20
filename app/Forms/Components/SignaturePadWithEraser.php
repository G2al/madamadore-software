<?php

namespace App\Forms\Components;

use Saade\FilamentAutograph\Forms\Components\SignaturePad;

class SignaturePadWithEraser extends SignaturePad
{
    protected string $view = 'filament.forms.components.signature-pad-with-eraser';

    protected bool $hasEraser = true;

    public function eraser(bool $condition = true): static
    {
        $this->hasEraser = $condition;
        return $this;
    }

    public function hasEraser(): bool
    {
        return $this->hasEraser;
    }
}
