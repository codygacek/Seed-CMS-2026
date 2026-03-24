<?php

namespace App\Filament\Pages;

use App\Models\Page as PageModel;
use App\Models\Setting;
use App\Models\SocialMedia;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Builder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class SettingsPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static BackedEnum|string|null $navigationIcon = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel = 'Settings';
    protected static ?string $title = 'Settings';
    protected static ?int $navigationSort = 20;

    protected string $view = 'filament.pages.settings';

    public array $data = [];

    public function mount(): void
    {
        $this->data = [
            'general' => [
                'site_title' => Setting::getValue('site_title', ''),
                'site_description' => Setting::getValue('site_description', ''),
                'theme' => Setting::getValue('theme', 'default'),
                'home_page' => (int) Setting::getValue('home_page', 0) ?: null, // your DB stores ID
            ],
            'fraternity' => [
                'fraternity_name' => Setting::getValue('fraternity_name', ''),
                'chapter_name' => Setting::getValue('chapter_name', ''),
                'school_name' => Setting::getValue('school_name', ''),
                'initiation_or_graduation' => Setting::getValue('initiation_or_graduation', 'graduation'),
            ],
            'theme' => [
                'special_banner_image' => Setting::getValue('special_banner_image', ''),
                'special_banner_link' => Setting::getValue('special_banner_link', ''),
            ],
            'members' => [
                'prospective_members_page_title' => Setting::getValue('prospective_members_page_title', ''),
                'current_members_page_title' => Setting::getValue('current_members_page_title', ''),
                'current_ec_members_label' => Setting::getValue('current_ec_members_label', ''),
                'previous_ec_members_label' => Setting::getValue('previous_ec_members_label', ''),
            ],
            'footer' => [
                'footer_copyright_company' => Setting::getValue('footer_copyright_company', ''),
            ],
            'integrations' => [
                'google_tag_manager_id' => Setting::getValue('google_tag_manager_id', ''),
                'head_html' => Setting::getValue('head_html', ''),
                'body_open_html' => Setting::getValue('body_open_html', ''),
            ],
            'campaign' => [
                'campaign_goal' => Setting::getValue('campaign_goal', ''),
                'raised_to_date' => Setting::getValue('raised_to_date', ''),
            ],
        ];

        $this->data['social_links'] = SocialMedia::query()
            ->orderBy('position')
            ->orderBy('id')
            ->get()
            ->map(function (SocialMedia $row) {
                [$library, $name] = $this->splitIcon($row->icon);

                return [
                    'id' => $row->id,
                    'label' => $row->label,
                    'link' => $row->link,
                    'icon_library' => $library,
                    'icon_name' => $name,
                ];
            })
            ->values()
            ->all();

            
    }

    /**
     * This is what creates $this->form
     */
    public function form(Schema $schema): Schema
    {
        return $schema
        ->statePath('data')
            ->components([
                Tabs::make('settings')
                    ->tabs([
                        Tab::make('General')
                            ->schema([
                                TextInput::make('general.site_title')
                                    ->label('Site Title')
                                    ->maxLength(191),

                                Textarea::make('general.site_description')
                                    ->label('Site Description')
                                    ->rows(3),

                                Select::make('general.theme')
                                    ->label('Theme')
                                    ->options($this->getThemeOptions())
                                    ->default('default'),

                                Select::make('general.home_page')
                                    ->label('Home Page')
                                    ->options(fn () => PageModel::query()->orderBy('title')->pluck('title', 'id'))
                                    ->searchable()
                                    ->preload()
                                    ->nullable(),
                            ]),

                        Tab::make('Fraternity')
                            ->schema([
                                TextInput::make('fraternity.fraternity_name')->label('Fraternity Name')->maxLength(191),
                                TextInput::make('fraternity.chapter_name')->label('Chapter Name')->maxLength(191),
                                TextInput::make('fraternity.school_name')->label('School Name')->maxLength(191),

                                Select::make('fraternity.initiation_or_graduation')
                                    ->label('Goes by Initiation or Graduation Date?')
                                    ->options([
                                        'initiation' => 'Initiation Date',
                                        'graduation' => 'Graduation Date',
                                    ])
                                    ->default('graduation'),
                            ]),

                        Tab::make('Theme')
                            ->schema([
                                TextInput::make('theme.special_banner_image')
                                    ->label('Special Banner Image')
                                    ->maxLength(255),

                                TextInput::make('theme.special_banner_link')
                                    ->label('Special Banner Link')
                                    ->maxLength(255),
                            ]),

                        Tab::make('Members')
                            ->schema([
                                TextInput::make('members.prospective_members_page_title')->label('Prospective Members Page Title')->maxLength(191),
                                TextInput::make('members.current_members_page_title')->label('Current Members Page Title')->maxLength(191),
                                TextInput::make('members.current_ec_members_label')->label('Current Executive Committee Members Label')->maxLength(191),
                                TextInput::make('members.previous_ec_members_label')->label('Previous Executive Committee Members Label')->maxLength(191),
                            ]),

                        Tab::make('Footer')
                            ->schema([
                                TextInput::make('footer.footer_copyright_company')
                                    ->label('Footer Copyright Company')
                                    ->maxLength(191),
                            ]),

                        Tab::make('Integrations')
                            ->schema([
                                TextInput::make('integrations.google_tag_manager_id')
                                    ->label('Google Tag Manager Container ID')
                                    ->helperText('Example: GTM-XXXXXXX. Leave blank to disable.')
                                    ->maxLength(32)
                                    ->regex('/^$|^GTM-[A-Z0-9]+$/'),

                                Textarea::make('integrations.head_html')
                                    ->label('Custom <head> HTML')
                                    ->helperText('Optional. For small snippets like site verification meta tags.')
                                    ->rows(6),

                                Textarea::make('integrations.body_open_html')
                                    ->label('Custom HTML after <body>')
                                    ->helperText('Optional. For noscript tags or scripts that must appear right after the opening <body>.')
                                    ->rows(6),
                            ]),

                        Tab::make('Campaign')
                            ->visible(fn () => (bool) config('app.in_campaign', false))
                            ->schema([
                                TextInput::make('campaign.campaign_goal')->label('Campaign Goal')->maxLength(191),
                                TextInput::make('campaign.raised_to_date')->label('Raised to Date')->maxLength(191),
                            ]),
                        Tab::make('Social Media')
                            ->schema([
                                Repeater::make('social_links')
                                    ->label('Social Media Links')
                                    ->default([])
                                    ->collapsible()
                                    ->collapsed()
                                    ->reorderableWithButtons()
                                    ->reorderableWithDragAndDrop(false)
                                    ->itemLabel(fn (array $state): string => $state['label'] ?? 'Social Link')
                                    ->schema([
                                        Hidden::make('id'),

                                        TextInput::make('label')
                                            ->required()
                                            ->maxLength(191),

                                        TextInput::make('link')
                                            ->label('Link')
                                            ->required()
                                            ->maxLength(255),

                                        Select::make('icon_library')
                                            ->label('Icon Library')
                                            ->options([
                                                'fa' => 'Font Awesome',
                                                'hero' => 'Heroicons',
                                                'custom' => 'Custom / Theme',
                                            ])
                                            ->default('fa')
                                            ->required(),

                                        Select::make('icon_name')
                                            ->label('Icon')
                                            ->options([
                                                'facebook-f' => 'Facebook',
                                                'facebook-square' => 'Facebook (Boxed)',
                                                'twitter' => 'Twitter',
                                                'youtube' => 'YouTube',
                                                'linkedin' => 'LinkedIn',
                                                'instagram' => 'Instagram',
                                                'pinterest' => 'Pinterest',
                                                'envelope' => 'Email',
                                                'desktop' => 'Website',
                                            ])
                                            ->searchable()
                                            ->required(),
                                    ])
                                    ->statePath('social_links')
                                    // This makes sure the repeater state is always a clean array after drag-reorder.
                                    ->mutateDehydratedStateUsing(fn ($state) => array_values($state ?? [])),
                            ]),
                    ]),
            ]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('save')
                ->label('Save settings')
                ->action('save')
                ->button(),
        ];
    }

    public function save(): void
    {
        $data = $this->form->getState();

        Setting::setValue('site_title', Arr::get($data, 'general.site_title', ''));
        Setting::setValue('site_description', Arr::get($data, 'general.site_description', ''));
        Setting::setValue('theme', Arr::get($data, 'general.theme', 'default'));
        Setting::setValue('home_page', Arr::get($data, 'general.home_page', ''));

        Setting::setValue('fraternity_name', Arr::get($data, 'fraternity.fraternity_name', ''));
        Setting::setValue('chapter_name', Arr::get($data, 'fraternity.chapter_name', ''));
        Setting::setValue('school_name', Arr::get($data, 'fraternity.school_name', ''));
        Setting::setValue('initiation_or_graduation', Arr::get($data, 'fraternity.initiation_or_graduation', 'graduation'));

        Setting::setValue('special_banner_image', Arr::get($data, 'theme.special_banner_image', ''));
        Setting::setValue('special_banner_link', Arr::get($data, 'theme.special_banner_link', ''));

        Setting::setValue('prospective_members_page_title', Arr::get($data, 'members.prospective_members_page_title', ''));
        Setting::setValue('current_members_page_title', Arr::get($data, 'members.current_members_page_title', ''));
        Setting::setValue('current_ec_members_label', Arr::get($data, 'members.current_ec_members_label', ''));
        Setting::setValue('previous_ec_members_label', Arr::get($data, 'members.previous_ec_members_label', ''));

        Setting::setValue('footer_copyright_company', Arr::get($data, 'footer.footer_copyright_company', ''));

        Setting::setValue('google_tag_manager_id', Arr::get($data, 'integrations.google_tag_manager_id', ''));
        Setting::setValue('head_html', Arr::get($data, 'integrations.head_html', ''));
        Setting::setValue('body_open_html', Arr::get($data, 'integrations.body_open_html', ''));

        Setting::setValue('campaign_goal', Arr::get($data, 'campaign.campaign_goal', ''));
        Setting::setValue('raised_to_date', Arr::get($data, 'campaign.raised_to_date', ''));

        $links = Arr::get($data, 'social_links', []);

        DB::transaction(function () use ($links) {
            $keepIds = [];

            foreach (array_values($links) as $index => $item) {
                $payload = [
                    'label' => (string) ($item['label'] ?? ''),
                    'link' => (string) ($item['link'] ?? ''),
                    'icon' => (string) (($item['icon_library'] ?? 'fa') . ':' . ($item['icon_name'] ?? '')),
                    'position' => $index + 1,
                ];

                if (! empty($item['id']) && ($row = SocialMedia::query()->find($item['id']))) {
                    $row->update($payload);
                    $keepIds[] = $row->id;
                    continue;
                }

                $row = SocialMedia::query()->create($payload);
                $keepIds[] = $row->id;
            }

            SocialMedia::query()
                ->when(count($keepIds), fn ($q) => $q->whereNotIn('id', $keepIds))
                ->delete();
        });

        Notification::make()
            ->title('Settings saved')
            ->success()
            ->send();
    }

    private function getThemeOptions(): array
    {
        if (! config('filesystems.disks.themes')) {
            return ['default' => 'Default'];
        }

        $dirs = Storage::disk('themes')->directories();
        $options = ['default' => 'Default'];

        foreach ($dirs as $dir) {
            $slug = trim($dir, '/');
            if ($slug === '') {
                continue;
            }

            $options[$slug] = Str::of($slug)->replace(['-', '_'], ' ')->title()->toString();
        }

        ksort($options);

        return $options;
    }

    private function splitIcon(?string $icon): array
    {
        $icon = (string) ($icon ?? '');

        // Legacy values like "facebook-f" become "fa:facebook-f"
        if ($icon !== '' && ! str_contains($icon, ':')) {
            return ['fa', $icon];
        }

        if ($icon === '') {
            return ['fa', ''];
        }

        [$library, $name] = explode(':', $icon, 2);

        return [$library ?: 'fa', $name ?: ''];
    }
}