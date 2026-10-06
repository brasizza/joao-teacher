<div class="rounded-2xl bg-white/60 p-6 ring-1 ring-line sm:p-10">
    @if ($submitted)
        <div class="py-6 text-center">
            <span class="mx-auto grid size-14 place-items-center rounded-full bg-brass-light/20 text-brass">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="size-7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m5 13 4 4L19 7"/></svg>
            </span>

            <h2 class="mt-6 font-serif text-3xl font-medium">Muito obrigado!</h2>

            <p class="mx-auto mt-3 max-w-md leading-relaxed text-ink-soft">
                Recebi o seu depoimento. Ele vai aparecer no site assim que eu fizer a revisão.
            </p>

            <a href="{{ route('home') }}" class="mt-8 inline-flex items-center gap-2 rounded-full bg-ink px-6 py-3 font-medium text-paper transition hover:bg-ink-deep">
                Visitar o site
            </a>
        </div>
    @else
        <form wire:submit="submit" class="relative space-y-6">
            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="testimonial-name" class="text-sm font-medium">Seu nome</label>
                    <input
                        id="testimonial-name"
                        type="text"
                        wire:model="name"
                        autocomplete="name"
                        required
                        class="mt-2 block w-full rounded-lg border border-line bg-white px-4 py-3 text-ink placeholder:text-ink-soft/60 focus:border-ink focus:ring-2 focus:ring-ink/15 focus:outline-none"
                    >
                    @error('name')
                        <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="testimonial-context" class="text-sm font-medium">Sobre você <span class="font-normal text-ink-soft">(opcional)</span></label>
                    <input
                        id="testimonial-context"
                        type="text"
                        wire:model="context"
                        placeholder="Ex.: Engenheira · aluna há 6 meses"
                        maxlength="120"
                        class="mt-2 block w-full rounded-lg border border-line bg-white px-4 py-3 text-ink placeholder:text-ink-soft/60 focus:border-ink focus:ring-2 focus:ring-ink/15 focus:outline-none"
                    >
                    @error('context')
                        <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div x-data="{ count: 0 }">
                <div class="flex items-baseline justify-between gap-4">
                    <label for="testimonial-content" class="text-sm font-medium">Seu depoimento</label>
                    <span class="text-xs text-ink-soft tabular-nums" x-text="`${count}/1200`">0/1200</span>
                </div>
                <textarea
                    id="testimonial-content"
                    wire:model="content"
                    x-on:input="count = $event.target.value.length"
                    rows="6"
                    maxlength="1200"
                    required
                    placeholder="O que mudou no seu inglês? Como são as aulas?"
                    class="mt-2 block w-full rounded-lg border border-line bg-white px-4 py-3 leading-relaxed text-ink placeholder:text-ink-soft/60 focus:border-ink focus:ring-2 focus:ring-ink/15 focus:outline-none"
                ></textarea>
                @error('content')
                    <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="flex items-start gap-3 text-sm leading-relaxed text-ink-soft">
                    <input type="checkbox" wire:model="consent" class="mt-1 size-4 shrink-0 rounded border-line accent-ink">
                    <span>Autorizo a publicação deste depoimento, com o meu nome, no site do professor.</span>
                </label>
                @error('consent')
                    <p class="mt-2 text-sm font-medium text-red-700">{{ $message }}</p>
                @enderror
            </div>

            <div aria-hidden="true" class="absolute -left-[9999px] size-px overflow-hidden">
                <label for="testimonial-website">Website</label>
                <input id="testimonial-website" type="text" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <p class="text-sm font-medium text-red-700">{{ $message }}</p>
            @enderror

            <button
                type="submit"
                wire:loading.attr="disabled"
                wire:target="submit"
                class="inline-flex w-full items-center justify-center rounded-full bg-ink px-6 py-4 text-base font-medium text-paper transition hover:bg-ink-deep disabled:opacity-60"
            >
                <span wire:loading.remove wire:target="submit">Enviar depoimento</span>
                <span wire:loading wire:target="submit">Enviando…</span>
            </button>
        </form>
    @endif
</div>
