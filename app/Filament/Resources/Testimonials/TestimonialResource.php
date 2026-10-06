<?php

namespace App\Filament\Resources\Testimonials;

use App\Filament\Resources\Testimonials\Pages\ManageTestimonials;
use App\Models\Testimonial;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;
use UnitEnum;

class TestimonialResource extends Resource
{
    protected static ?string $model = Testimonial::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleBottomCenterText;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 4;

    protected static ?string $modelLabel = 'depoimento';

    protected static ?string $pluralModelLabel = 'Depoimentos';

    protected static ?string $recordTitleAttribute = 'student_name';

    protected static string|Htmlable|null $navigationBadgeTooltip = 'Depoimentos enviados por alunos aguardando aprovação';

    public static function getNavigationBadge(): ?string
    {
        $awaitingCount = Testimonial::query()->awaitingReview()->count();

        return $awaitingCount > 0 ? (string) $awaitingCount : null;
    }

    public static function getNavigationBadgeColor(): string
    {
        return 'warning';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('student_name')
                    ->label('Nome do aluno')
                    ->required()
                    ->maxLength(120),
                TextInput::make('student_context')
                    ->label('Contexto')
                    ->helperText('Ex.: Gerente de projetos · aluna há 2 anos')
                    ->maxLength(120),
                Textarea::make('content')
                    ->label('Depoimento')
                    ->required()
                    ->rows(5)
                    ->maxLength(1200)
                    ->columnSpanFull(),
                FileUpload::make('photo_path')
                    ->label('Foto do aluno')
                    ->helperText('Opcional. Sem foto, exibimos as iniciais.')
                    ->disk('public')
                    ->directory('testimonials')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->avatar()
                    ->imageEditor()
                    ->circleCropper()
                    ->maxSize(2048),
                Toggle::make('is_active')
                    ->label('Exibir no site')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->reorderable('sort_order')
            ->defaultSort('sort_order')
            ->paginated(false)
            ->columns([
                ImageColumn::make('photo_path')
                    ->label('Foto')
                    ->disk('public')
                    ->circular(),
                TextColumn::make('student_name')
                    ->label('Aluno')
                    ->description(fn (Testimonial $record): ?string => $record->student_context),
                TextColumn::make('submitted_at')
                    ->label('Origem')
                    ->badge()
                    ->color('info')
                    ->formatStateUsing(fn (): string => 'Enviado pelo aluno')
                    ->tooltip(fn (Testimonial $record): ?string => $record->submitted_at?->format('d/m/Y H:i'))
                    ->placeholder('Cadastrado por você'),
                TextColumn::make('content')
                    ->label('Depoimento')
                    ->limit(80)
                    ->wrap(),
                ToggleColumn::make('is_active')
                    ->label('No site'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhum depoimento cadastrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageTestimonials::route('/'),
        ];
    }
}
