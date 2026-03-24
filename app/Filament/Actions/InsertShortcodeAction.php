<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;

class InsertShortcodeAction
{
    public static function make(): Action
    {
        return Action::make('insert_shortcode')
            ->label('Insert Shortcode')
            ->icon('heroicon-o-code-bracket')
            ->modalHeading('Insert Embed Shortcode')
            ->modalWidth('2xl')
            ->form([
                Select::make('type')
                    ->label('Embed Type')
                    ->options([
                        'wufoo' => 'Wufoo Form',
                        'iframe' => 'iframe',
                        'script' => 'External Script',
                        'embed' => 'Custom HTML/Embed',
                    ])
                    ->required()
                    ->reactive(),

                // Wufoo fields
                TextInput::make('wufoo_form')
                    ->label('Wufoo Form ID')
                    ->helperText('Example: z7x4a3')
                    ->visible(fn ($get) => $get('type') === 'wufoo'),

                Toggle::make('wufoo_header')
                    ->label('Show Form Header')
                    ->default(true)
                    ->visible(fn ($get) => $get('type') === 'wufoo'),

                // iframe fields
                TextInput::make('iframe_url')
                    ->label('iframe URL')
                    ->url()
                    ->visible(fn ($get) => $get('type') === 'iframe'),

                TextInput::make('iframe_width')
                    ->label('Width')
                    ->default('100%')
                    ->visible(fn ($get) => $get('type') === 'iframe'),

                TextInput::make('iframe_height')
                    ->label('Height (pixels)')
                    ->default('400')
                    ->numeric()
                    ->visible(fn ($get) => $get('type') === 'iframe'),

                // Script fields
                TextInput::make('script_src')
                    ->label('Script URL')
                    ->url()
                    ->visible(fn ($get) => $get('type') === 'script'),

                // Custom embed fields
                Textarea::make('custom_code')
                    ->label('Custom HTML/Embed Code')
                    ->rows(10)
                    ->helperText('Paste your embed code here. It will be base64 encoded for safe storage.')
                    ->visible(fn ($get) => $get('type') === 'embed'),
            ])
            ->action(function (array $data) {
                $shortcode = self::generateShortcode($data);
                
                // This would normally inject into the editor
                // For now, just show it
                \Filament\Notifications\Notification::make()
                    ->title('Shortcode Generated')
                    ->body("Copy this shortcode:\n\n{$shortcode}")
                    ->success()
                    ->persistent()
                    ->send();
                    
                return $shortcode;
            });
    }

    protected static function generateShortcode(array $data): string
    {
        $type = $data['type'];

        return match($type) {
            'wufoo' => sprintf(
                '[wufoo form="%s"%s]',
                $data['wufoo_form'],
                isset($data['wufoo_header']) && !$data['wufoo_header'] ? ' header="false"' : ''
            ),
            
            'iframe' => sprintf(
                '[iframe url="%s" width="%s" height="%s"]',
                $data['iframe_url'],
                $data['iframe_width'] ?? '100%',
                $data['iframe_height'] ?? '400'
            ),
            
            'script' => sprintf(
                '[script src="%s"]',
                $data['script_src']
            ),
            
            'embed' => sprintf(
                '[embed code="%s" encoded="true"]',
                base64_encode($data['custom_code'])
            ),
            
            default => '',
        };
    }
}