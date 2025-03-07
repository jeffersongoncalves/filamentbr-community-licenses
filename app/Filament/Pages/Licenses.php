<?php

namespace App\Filament\Pages;

use App\Enums\LicenseType;
use App\Models\License;
use App\Models\Team;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Pages\Page;
use Livewire\Attributes\Locked;

use function App\Support\enum_equals;
use function App\Support\tenant;

class Licenses extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.licenses';

    #[Locked]
    public ?Team $record = null;

    public array $data = [];

    public function mount()
    {
        $this->record = tenant(Team::class);
        $this->form->fill();
    }

    public function form(Form $form): Form
    {
        return $form
            ->model($this->record)
            ->schema([
                Forms\Components\Section::make('Adicionar Licença')
                    ->columns(2)
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nome')
                            ->required(),

                        Forms\Components\ToggleButtons::make('type')
                            ->label('Tipo')
                            ->live()
                            ->options(LicenseType::class)
                            ->default(LicenseType::Composer)
                            ->required(),

                        Forms\Components\Fieldset::make()
                            ->columns(3)
                            ->schema([
                                Forms\Components\Placeholder::make('composer-instructions')
                                    ->label('Configurações do Composer')
                                    ->content('Para adicionar uma licença do tipo Composer, você deve informar as credenciais de acesso ao repositório privado. O produto será sub-licenciado usando o Satis. Cada membro do time receberá uma credencial de acesso individual. Não compartilhe essas credenciais com ninguém.')
                                    ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), LicenseType::Composer)),

                                Forms\Components\Placeholder::make('individual-instructions')
                                    ->label('Configurações Individuais')
                                    ->content('Para adicionar uma licença do tipo Individual, você deve informar as credenciais de acesso ao produto. Cada membro do time receberá uma credencial de acesso individual. Não compartilhe essas credenciais com ninguém.')
                                    ->visible(fn (Forms\Get $get): bool => enum_equals($get('type'), LicenseType::Individual)),

                                Forms\Components\TextInput::make('url')
                                    ->label(
                                        fn (Forms\Get $get) => match ($get('type')) {
                                            LicenseType::Composer => 'URL do Repositório Composer',
                                            LicenseType::Individual => 'URL do Produto',
                                        }
                                    )
                                    ->url()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('username')
                                    ->label(
                                        fn (Forms\Get $get) => match ($get('type')) {
                                            LicenseType::Composer => 'Username do Composer',
                                            LicenseType::Individual => 'Email de Acesso',
                                        }
                                    )
                                    ->required()
                                    ->columnStart(1),

                                Forms\Components\TextInput::make('password')
                                    ->label(
                                        fn (Forms\Get $get) => match ($get('type')) {
                                            LicenseType::Composer => 'Password do Composer',
                                            LicenseType::Individual => 'Senha de Acesso',
                                        }
                                    )
                                    ->password()
                                    ->revealable()
                                    ->required(),
                            ]),
                    ])
                    ->footerActions([
                        Forms\Components\Actions\Action::make('create')
                            ->label('Adicionar')
                            ->action(
                                fn (Team $record, Forms\Get $get) => $record->licenses()->create($get('../data'))
                            ),
                    ]),
            ])
            ->statePath('data');
    }

    public function licensesInfolist(Infolist $infolist): Infolist
    {
        return $infolist
            ->record($this->record)
            ->schema([
                Infolists\Components\RepeatableEntry::make('licenses')
                    ->schema([
                        Infolists\Components\Section::make()
                            ->heading(fn (License $record) => $record->name)
                            ->description(fn (License $record) => $record->type->getLabel())
                            ->icon(fn (License $record) => $record->type->getIcon())
                            ->columns(3)
                            ->schema([
                                Infolists\Components\TextEntry::make('composer_url')
                                    ->label(
                                        fn (License $record) => match ($record->type) {
                                            LicenseType::Composer => 'URL do Repositório',
                                            LicenseType::Individual => 'URL do Produto',
                                        }
                                    ),

                                Infolists\Components\TextEntry::make('composer_username')
                                    ->label(
                                        fn (License $record) => match ($record->type) {
                                            LicenseType::Composer => 'Username',
                                            LicenseType::Individual => 'Email',
                                        }
                                    ),

                                Infolists\Components\TextEntry::make('composer_password')
                                    ->label(
                                        fn (License $record) => match ($record->type) {
                                            LicenseType::Composer => 'Password',
                                            LicenseType::Individual => 'Senha',
                                        }
                                    ),
                            ]),
                    ]),
            ]);
    }
}
