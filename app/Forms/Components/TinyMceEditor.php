<?php

namespace App\Forms\Components;

use Filament\Forms\Components\Field;

class TinyMceEditor extends Field
{
    protected string $view = 'forms.components.tinymce-editor';
    
    protected string $profile = 'default';
    protected ?string $directory = null;
    protected int $height = 500;
    
    public function profile(string $profile): static
    {
        $this->profile = $profile;
        return $this;
    }
    
    public function getProfile(): string
    {
        return $this->profile;
    }
    
    public function fileAttachmentsDirectory(string $directory): static
    {
        $this->directory = $directory;
        return $this;
    }
    
    public function getFileAttachmentsDirectory(): ?string
    {
        return $this->directory;
    }
    
    public function height(int $height): static
    {
        $this->height = $height;
        return $this;
    }
    
    public function getHeight(): int
    {
        return $this->height;
    }
}