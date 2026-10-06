<?php

namespace App\Filament\Resources\ScheduleBlocks;

use App\Filament\Resources\ScheduleBlocks\Pages\ManageScheduleBlocks;
use App\Models\ScheduleBlock;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class ScheduleBlockResource extends Resource
{
    protected static ?string $model = ScheduleBlock::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNoSymbol;

    protected static string|UnitEnum|null $navigationGroup = 'Agenda';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'bloqueio';

    protected static ?string $pluralModelLabel = 'Bloqueios de agenda';

    protected static ?string $recordTitleAttribute = 'reason';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                DateTimePicker::make('starts_at')
                    ->label('Início')
                    ->seconds(false)
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Fim')
                    ->seconds(false)
                    ->required()
                    ->after('starts_at')
                    ->validationMessages(['after' => 'O fim precisa ser depois do início.']),
                TextInput::make('reason')
                    ->label('Motivo (só você vê)')
                    ->placeholder('Ex.: férias, consulta médica')
                    ->maxLength(255)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('starts_at')
            ->description('Períodos em que nenhum horário deve aparecer no site, como férias e compromissos.')
            ->columns([
                TextColumn::make('starts_at')
                    ->label('Início')
                    ->dateTime('D, d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Fim')
                    ->dateTime('D, d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->placeholder('—'),
            ])
            ->filters([
                Filter::make('upcoming')
                    ->label('Somente atuais e futuros')
                    ->query(fn (Builder $query): Builder => $query->where('ends_at', '>=', now()))
                    ->default(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateHeading('Nenhum bloqueio cadastrado');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageScheduleBlocks::route('/'),
        ];
    }
}
