<?php

namespace App\Filament\Pages;

use App\Models\SiteSetting;
use App\Services\SitePalette;
use BackedEnum;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;
use UnitEnum;

/**
 * Edits the single SiteSetting row: texts, photo, main color, contacts and
 * the scheduling rules of the public site.
 *
 * @property-read Schema $form
 */
class ManageSiteContent extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPencilSquare;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?string $navigationLabel = 'Conteúdo do site';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Conteúdo do site';

    protected static ?string $slug = 'conteudo';

    /**
     * @var array<string, mixed>|null
     */
    public ?array $data = [];

    public function mount(): void
    {
        $this->form->fill(SiteSetting::current()->attributesToArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Conteúdo')
                    ->persistTabInQueryString()
                    ->tabs([
                        $this->homeTab(),
                        $this->appearanceTab(),
                        $this->aboutTab(),
                        $this->sectionsTab(),
                        $this->contactTab(),
                        $this->scheduleTab(),
                        $this->seoTab(),
                    ]),
            ])
            ->model(SiteSetting::current())
            ->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema
            ->components([
                Form::make([EmbeddedSchema::make('form')])
                    ->id('form')
                    ->livewireSubmitHandler('save')
                    ->footer([
                        Actions::make([
                            Action::make('save')
                                ->label('Salvar alterações')
                                ->submit('save')
                                ->keyBindings(['mod+s']),
                        ]),
                    ]),
            ]);
    }

    public function save(): void
    {
        SiteSetting::current()->update($this->form->getState());

        Notification::make()
            ->success()
            ->title('Conteúdo salvo')
            ->body('As alterações já estão no site.')
            ->send();
    }

    private function homeTab(): Tab
    {
        return Tab::make('Início')
            ->icon(Heroicon::OutlinedHome)
            ->schema([
                TextInput::make('teacher_name')
                    ->label('Nome do professor')
                    ->required()
                    ->maxLength(120),
                TextInput::make('tagline')
                    ->label('Linha de apresentação')
                    ->helperText('Texto curto acima do título. Ex.: English Teacher · CELTA')
                    ->maxLength(160),
                TextInput::make('headline')
                    ->label('Título principal')
                    ->required()
                    ->maxLength(160),
                Textarea::make('subheadline')
                    ->label('Texto de apoio')
                    ->rows(3)
                    ->maxLength(600),
                FileUpload::make('photo_path')
                    ->label('Sua foto')
                    ->helperText('Prefira uma foto vertical, bem iluminada, com fundo neutro. Formatos: JPG, PNG ou WebP.')
                    ->disk('public')
                    ->directory('site')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->imageEditor()
                    ->imageEditorAspectRatioOptions(['4:5', '1:1'])
                    ->automaticallyResizeImagesMode('contain')
                    ->automaticallyResizeImagesToWidth('1400')
                    ->maxSize(5120),
                Repeater::make('highlights')
                    ->label('Destaques (credenciais e números)')
                    ->helperText('Aparecem logo abaixo do título. Ex.: "10+" / "anos ensinando inglês".')
                    ->schema([
                        TextInput::make('value')
                            ->label('Destaque')
                            ->required()
                            ->maxLength(20),
                        TextInput::make('label')
                            ->label('Descrição')
                            ->required()
                            ->maxLength(60),
                    ])
                    ->columns(2)
                    ->maxItems(4)
                    ->defaultItems(0)
                    ->addActionLabel('Adicionar destaque'),
            ]);
    }

    private function appearanceTab(): Tab
    {
        return Tab::make('Aparência')
            ->icon(Heroicon::OutlinedSwatch)
            ->schema([
                ColorPicker::make('primary_color')
                    ->label('Cor principal do site')
                    ->helperText('Aparece nos botões, títulos, na seção de agenda e no rodapé. Prefira tons escuros: os textos claros do site ficam por cima dela. Deixe em branco para usar o azul-marinho original.')
                    ->placeholder(SitePalette::DefaultPrimaryColor)
                    ->regex('/^#[0-9a-fA-F]{6}$/')
                    ->validationMessages([
                        'regex' => 'Informe a cor no formato #RRGGBB, por exemplo #1e2a44.',
                    ])
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if (! (new SitePalette($value))->hasLegibleContrast()) {
                                $fail('Essa cor é clara demais e deixaria os textos claros do site difíceis de ler. Escolha um tom mais escuro.');
                            }
                        },
                    ])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? Str::lower($state) : null)
                    ->hintAction(
                        Action::make('restoreDefaultPrimaryColor')
                            ->label('Restaurar cor original')
                            ->icon(Heroicon::OutlinedArrowUturnLeft)
                            ->action(fn (Set $set): mixed => $set('primary_color', null)),
                    ),
            ]);
    }

    private function aboutTab(): Tab
    {
        return Tab::make('Sobre')
            ->icon(Heroicon::OutlinedUser)
            ->schema([
                TextInput::make('about_title')
                    ->label('Título da seção')
                    ->maxLength(200),
                Textarea::make('about_text')
                    ->label('Sua história')
                    ->helperText('Deixe uma linha em branco para separar parágrafos.')
                    ->rows(10)
                    ->maxLength(5000),
            ]);
    }

    private function sectionsTab(): Tab
    {
        return Tab::make('Textos das seções')
            ->icon(Heroicon::OutlinedBars3BottomLeft)
            ->schema([
                Textarea::make('methodology_intro')
                    ->label('Introdução — Metodologia')
                    ->rows(2)
                    ->maxLength(400),
                Textarea::make('lessons_intro')
                    ->label('Introdução — Como são as aulas')
                    ->rows(2)
                    ->maxLength(400),
                Textarea::make('testimonials_intro')
                    ->label('Introdução — Depoimentos')
                    ->rows(2)
                    ->maxLength(400),
                Textarea::make('schedule_intro')
                    ->label('Introdução — Agenda')
                    ->rows(2)
                    ->maxLength(400),
            ]);
    }

    private function contactTab(): Tab
    {
        return Tab::make('Contato e WhatsApp')
            ->icon(Heroicon::OutlinedChatBubbleLeftRight)
            ->schema([
                TextInput::make('whatsapp_number')
                    ->label('Número do WhatsApp')
                    ->helperText('Com DDI e DDD. Ex.: 55 11 98765-4321')
                    ->tel()
                    ->telRegex('/^[\d\s()+.\-]+$/')
                    ->required()
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            $digitCount = strlen(preg_replace('/\D+/', '', (string) $value) ?? '');

                            if ($digitCount < 12 || $digitCount > 13) {
                                $fail('Informe o número com DDI e DDD, por exemplo 55 11 98765-4321.');
                            }
                        },
                    ])
                    ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? preg_replace('/\D+/', '', $state) : null),
                Textarea::make('whatsapp_general_message')
                    ->label('Mensagem do botão de WhatsApp')
                    ->helperText('Texto que já vem escrito quando o visitante clica em "Falar no WhatsApp".')
                    ->rows(2)
                    ->maxLength(500),
                Textarea::make('whatsapp_booking_message')
                    ->label('Mensagem de agendamento')
                    ->helperText('Enviada após o pedido de aula experimental. Use {nome}, {data} e {hora}.')
                    ->rows(3)
                    ->required()
                    ->maxLength(500),
                TextInput::make('instagram_url')
                    ->label('Instagram (link completo)')
                    ->url()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('E-mail de contato')
                    ->email()
                    ->maxLength(255),
            ]);
    }

    private function scheduleTab(): Tab
    {
        return Tab::make('Agenda')
            ->icon(Heroicon::OutlinedCalendarDays)
            ->schema([
                Section::make('Regras dos horários')
                    ->description('Os dias e faixas de horário são cadastrados em "Disponibilidade semanal".')
                    ->columns(2)
                    ->schema([
                        TextInput::make('slot_interval_minutes')
                            ->label('Intervalo entre horários')
                            ->helperText('Também é a duração da aula experimental.')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(15)
                            ->maxValue(240)
                            ->suffix('minutos'),
                        TextInput::make('booking_window_days')
                            ->label('Mostrar horários dos próximos')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(1)
                            ->maxValue(60)
                            ->suffix('dias'),
                        TextInput::make('minimum_notice_hours')
                            ->label('Antecedência mínima')
                            ->helperText('Horários mais próximos do que isso não aparecem.')
                            ->numeric()
                            ->integer()
                            ->required()
                            ->minValue(0)
                            ->maxValue(168)
                            ->suffix('horas'),
                        TextInput::make('pending_hold_hours')
                            ->label('Liberar pedidos pendentes após')
                            ->helperText('Deixe vazio para manter o horário reservado até você confirmar ou liberar.')
                            ->numeric()
                            ->integer()
                            ->minValue(1)
                            ->maxValue(720)
                            ->suffix('horas'),
                    ]),
            ]);
    }

    private function seoTab(): Tab
    {
        return Tab::make('Google e redes sociais')
            ->icon(Heroicon::OutlinedMagnifyingGlass)
            ->schema([
                TextInput::make('seo_title')
                    ->label('Título da página')
                    ->helperText('Aparece na aba do navegador e no Google. Ideal: até 60 caracteres.')
                    ->maxLength(70),
                Textarea::make('seo_description')
                    ->label('Descrição')
                    ->helperText('Resumo exibido no Google e ao compartilhar o link. Ideal: até 160 caracteres.')
                    ->rows(3)
                    ->maxLength(300),
            ]);
    }
}
