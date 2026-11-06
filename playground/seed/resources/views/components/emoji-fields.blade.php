{{--
    Outputs every test field of the pages blueprint.
    Usage: <x-emoji-fields :page="$page" />

    $page->field returns the augmented value: emoji fields are strings, Bard is HTML,
    replicator and grid are arrays of Statamic\Fields\Values.
--}}
@props(['page'])

<div {{ $attributes->merge(['class' => 'max-w-2xl mx-auto py-12 px-4 space-y-10']) }}>
    <h1 class="text-3xl font-bold">{{ $page->title }}</h1>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Emoji fieldtype</h2>
        <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2">
            @foreach (['emoji', 'emoji_configured', 'emoji_read_only', 'emoji_required'] as $handle)
                <dt class="font-mono text-sm">{{ $handle }}</dt>
                <dd class="text-2xl">{{ $page->$handle ?? '–' }}</dd>
            @endforeach
        </dl>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Bard</h2>
        <h3 class="font-mono text-sm">content</h3>
        <div class="prose dark:prose-invert">{!! $page->content !!}</div>
        <h3 class="font-mono text-sm">content_without_emoji</h3>
        <div class="prose dark:prose-invert">{!! $page->content_without_emoji !!}</div>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Replicator <span class="font-mono text-sm">blocks</span></h2>
        @forelse ($page->blocks ?? [] as $block)
            @if ($block->type === 'callout')
                <aside class="flex gap-3 rounded-lg bg-white p-4 dark:bg-zinc-800">
                    <span class="text-2xl">{{ $block->icon }}</span>
                    <div class="prose dark:prose-invert">{!! $block->text !!}</div>
                </aside>
            @endif
        @empty
            <p>–</p>
        @endforelse
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Grid <span class="font-mono text-sm">reactions</span></h2>
        <ul class="flex flex-wrap gap-2">
            @forelse ($page->reactions ?? [] as $reaction)
                <li class="rounded-full bg-white px-3 py-1 dark:bg-zinc-800">{{ $reaction->emoji }} {{ $reaction->label }}</li>
            @empty
                <li>–</li>
            @endforelse
        </ul>
    </section>
</div>
