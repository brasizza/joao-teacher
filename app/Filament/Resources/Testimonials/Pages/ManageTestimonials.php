<?php

namespace App\Filament\Resources\Testimonials\Pages;

use App\Filament\Resources\Testimonials\TestimonialResource;
use App\Models\Testimonial;
use App\Services\WhatsAppLink;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Pages\ManageRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Builder;

class ManageTestimonials extends ManageRecords
{
    protected static string $resource = TestimonialResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('requestTestimonial')
                ->label('Pedir depoimento')
                ->icon(Heroicon::OutlinedLink)
                ->color('gray')
                ->modalHeading('Pedir depoimento a um aluno')
                ->modalDescription('Envie este link ao aluno. O depoimento chega aqui oculto e só aparece no site quando você ativar "No site".')
                ->schema([
                    TextInput::make('link')
                        ->label('Link da página de depoimento')
                        ->default(route('testimonials.create'))
                        ->readOnly()
                        ->copyable(copyMessage: 'Link copiado'),
                ])
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Fechar')
                ->extraModalFooterActions([
                    Action::make('sendByWhatsApp')
                        ->label('Enviar pelo WhatsApp')
                        ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                        ->color('success')
                        ->url(fn (WhatsAppLink $whatsAppLink): string => $whatsAppLink->testimonialRequest())
                        ->openUrlInNewTab(),
                ]),
            CreateAction::make(),
        ];
    }

    /**
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make('Todos'),
            'awaiting' => Tab::make('Aguardando aprovação')
                ->badge(fn (): int => Testimonial::query()->awaitingReview()->count())
                ->badgeColor('warning')
                ->modifyQueryUsing(fn (Builder $query): Builder => $query->awaitingReview()),
        ];
    }
}
