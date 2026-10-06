<?php

namespace App\Filament\Resources\MethodologySteps;

use App\Filament\Resources\MethodologySteps\Pages\ManageMethodologySteps;
use App\Models\MethodologyStep;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use UnitEnum;

class MethodologyStepResource extends Resource
{
    protected static ?string $model = MethodologyStep::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|UnitEnum|null $navigationGroup = 'Site';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'etapa da metodologia';

    protected static ?string $pluralModelLabel = 'Metodologia';

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
                TextColumn::make('title')
                    ->label('Título')
                    ->description(fn (MethodologyStep $record): string => str($record->description)->limit(90))
                    ->wrap(),
                ToggleColumn::make('is_active')
                    ->label('No site'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhuma etapa cadastrada');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageMethodologySteps::route('/'),
        ];
    }
}
