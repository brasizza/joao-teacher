<section id="sobre">
    <div class="mx-auto grid max-w-6xl gap-12 px-5 py-24 sm:px-8 lg:grid-cols-12 lg:py-32">
        <x-site.section-heading
            class="lg:col-span-5"
            :number="$number"
            eyebrow="Sobre"
            :title="$settings->about_title ?: 'Sobre o professor'"
        />

        <div class="space-y-6 text-lg leading-relaxed text-ink-soft lg:col-span-6 lg:col-start-7 lg:pt-12">
            <x-site.paragraphs :text="$settings->about_text" class="first-of-type:text-xl first-of-type:text-ink" />

            <p class="pt-2 font-serif text-2xl text-ink italic">— {{ $settings->teacher_name }}</p>
        </div>
    </div>
</section>
