<?php

namespace App\Filament\Resources\AvailabilityWindows;

use App\Enums\Weekday;
use App\Filament\Resources\AvailabilityWindows\Pages\ManageAvailabilityWindows;
use App\Models\AvailabilityWindow;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class AvailabilityWindowResource extends Resource
{
    protected static ?string $model = AvailabilityWindow::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClock;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'faixa de horário';

    protected static ?string $pluralModelLabel = 'Disponibilidade semanal';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('weekday')
                    ->label('Dia da semana')
                    ->options(Weekday::class)
                    ->required()
                    ->columnSpanFull(),
                TimePicker::make('start_time')
                    ->label('Das')
                    ->seconds(false)
                    ->required(),
                TimePicker::make('end_time')
                    ->label('Até')
                    ->seconds(false)
                    ->required()
                    ->after('start_time')
                    ->validationMessages(['after' => 'O horário final precisa ser depois do inicial.']),
                Toggle::make('is_active')
                    ->label('Ativa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query): Builder => $query->orderBy('weekday')->orderBy('start_time'))
            ->paginated(false)
            ->description('Os horários do site são gerados dentro destas faixas, no intervalo definido em "Conteúdo do site › Agenda".')
            ->columns([
                TextColumn::make('weekday')
                    ->label('Dia'),
                TextColumn::make('start_time')
                    ->label('Das')
                    ->time('H:i'),
                TextColumn::make('end_time')
                    ->label('Até')
                    ->time('H:i'),
                ToggleColumn::make('is_active')
                    ->label('Ativa'),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhuma faixa de horário cadastrada')
            ->emptyStateDescription('Cadastre os dias e horários em que você pode dar aulas experimentais.');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageAvailabilityWindows::route('/'),
        ];
    }
}
