<?php

namespace App\Filament\Helpers;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Actions;
use Filament\Actions\Action;

class SeoFields
{
    /**
     * Get the reusable SEO section for forms
     * 
     * @return Section
     */
    public static function make(): Section
    {
        return Section::make('Search Engine Optimization')
            ->description('Help search engines understand this content')
            ->schema(self::getFields())
            ->columns(1);
    }
    
    /**
     * Get just the fields without the section wrapper
     * Use this if you want to add SEO fields to an existing section/tab
     * 
     * @return array
     */
    public static function getFields(): array
    {
        return [
            TextInput::make('meta_title')
                ->label('Meta Title')
                ->maxLength(60)
                ->helperText('Recommended: 50-60 characters. Leave empty to use the page title.')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set) {
                    if ($state && strlen($state) > 60) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Meta title is too long')
                            ->body('Current: ' . strlen($state) . ' characters. Recommended: 50-60 characters.')
                            ->send();
                    }
                }),
            
            Textarea::make('meta_description')
                ->label('Meta Description')
                ->rows(3)
                ->maxLength(160)
                ->helperText('Recommended: 150-160 characters. Appears in search results.')
                ->live(onBlur: true)
                ->afterStateUpdated(function ($state, $set) {
                    if ($state && strlen($state) > 160) {
                        \Filament\Notifications\Notification::make()
                            ->warning()
                            ->title('Meta description is too long')
                            ->body('Current: ' . strlen($state) . ' characters. Recommended: 150-160 characters.')
                            ->send();
                    }
                }),
            
            Toggle::make('index')
                ->label('Allow search engines to index this page')
                ->default(true)
                ->helperText('Turn off to hide this page from search results')
                ->inline(false),
        ];
    }
}