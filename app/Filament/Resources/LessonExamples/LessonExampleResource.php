<?php

namespace App\Filament\Resources\LessonExamples;

use App\Filament\Resources\LessonExamples\Pages\ManageLessonExamples;
use App\Models\LessonExample;
use BackedEnum;
use Closure;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class LessonExampleResource extends Resource
{
    protected static ?string $model = LessonExample::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPresentationChartLine;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'exemplo de aula';

    protected static ?string $pluralModelLabel = 'Exemplos de aula';

    protected static ?string $recordTitleAttribute = 'title';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->label('Título')
                    ->required()
                    ->maxLength(120)
                    ->columnSpanFull(),
                Textarea::make('description')
                    ->label('Descrição')
                    ->required()
                    ->rows(4)
                    ->maxLength(800)
                    ->columnSpanFull(),
                FileUpload::make('image_path')
                    ->label('Imagem')
                    ->helperText('Opcional. Se houver vídeo, a imagem é usada como capa.')
                    ->disk('public')
                    ->directory('lessons')
                    ->image()
                    ->acceptedFileTypes(['image/jpeg', 'image/png', 'image/webp'])
                    ->imageEditor()
                    ->imageEditorAspectRatioOptions(['16:9', '4:3'])
                    ->automaticallyResizeImagesMode('contain')
                    ->automaticallyResizeImagesToWidth('1600')
                    ->maxSize(5120)
                    ->columnSpanFull(),
                TextInput::make('youtube_url')
                    ->label('Vídeo do YouTube')
                    ->helperText('Opcional. Cole o link do vídeo, ex.: https://youtu.be/...')
                    ->url()
                    ->maxLength(255)
                    ->rules([
                        fn (): Closure => function (string $attribute, mixed $value, Closure $fail): void {
                            if (filled($value) && LessonExample::parseYoutubeVideoId($value) === null) {
                                $fail('Não reconhecemos este link do YouTube.');
                            }
                        },
                    ])
                    ->columnSpanFull(),
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
                ImageColumn::make('image_path')
                    ->label('Imagem')
                    ->disk('public')
                    ->square(),
                TextColumn::make('title')
                    ->label('Título')
                    ->description(fn (LessonExample $record): string => str($record->description)->limit(90))
                    ->wrap(),
                IconColumn::make('youtube_url')
                    ->label('Vídeo')
                    ->icon(Heroicon::OutlinedPlayCircle)
                    ->color('danger'),
                ToggleColumn::make('is_active')
                    ->label('No site'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhum exemplo cadastrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageLessonExamples::route('/'),
        ];
    }
}
