<section id="agenda" class="relative overflow-hidden bg-ink text-paper">
    <div aria-hidden="true" class="pointer-events-none absolute -top-40 -right-40 size-[32rem] rounded-full border border-paper/5"></div>
    <div aria-hidden="true" class="pointer-events-none absolute -top-20 -right-20 size-[32rem] rounded-full border border-paper/5"></div>

    <div class="relative mx-auto grid max-w-6xl gap-14 px-5 py-24 sm:px-8 lg:grid-cols-12 lg:gap-12 lg:py-32">
        <div class="lg:col-span-5">
            <x-site.section-heading
                :number="$number"
                eyebrow="Agenda"
                title="Agende sua aula experimental."
                :intro="$settings->schedule_intro"
                :inverted="true"
            />

            <ol class="mt-12 space-y-6">
                @foreach (['Escolha o dia e o horário que preferir.', 'Informe seu nome e seu WhatsApp.', 'Envie a mensagem pronta e aguarde a confirmação.'] as $step)
                    <li class="flex items-start gap-4">
                        <span class="grid size-8 shrink-0 place-items-center rounded-full border border-brass-light/60 font-serif text-sm text-brass-light">{{ $loop->iteration }}</span>
                        <span class="pt-1 text-paper/80">{{ $step }}</span>
                    </li>
                @endforeach
            </ol>
        </div>

        <div class="min-w-0 lg:col-span-7">
            <livewire:booking-scheduler />
        </div>
    </div>
</section>
