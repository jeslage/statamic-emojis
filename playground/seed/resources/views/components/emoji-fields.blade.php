{{--
    Outputs every test field of the pages blueprint.
    Usage: <x-emoji-fields :page="$page" />

    $page->field returns the augmented value: emoji fields are strings, Bard is HTML,
    replicator and grid are arrays of Statamic\Fields\Values.

    emoji_images: pass a Statamic\Fields\Value ($page->augmentedValue('handle'), or
    $values->getProxiedInstance()->get('handle') in grids/replicators) so the modifier
    can use the field's emoji_set; a plain string uses the site-wide set.
--}}
@props(['page'])

@php
    $images = fn ($value, $set = null) => Statamic::modify($value)->emoji_images($set);
    $sample = '🇩🇪 ❤️ 🧑‍🚀 👍🏽';
@endphp

<style>
    img.emoji { display: inline-block; width: 1.15em; height: 1.15em; vertical-align: -0.2em; }
</style>
<div {{ $attributes->merge(['class' => 'max-w-2xl mx-auto py-12 px-4 space-y-10']) }}>
    <h1 class="text-3xl font-bold">{{ $page->title }}</h1>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Emoji fieldtype</h2>
        <dl class="grid grid-cols-[max-content_1fr_1fr] gap-x-6 gap-y-2">
            <dt></dt><dd class="text-sm text-zinc-500">stored</dd><dd class="text-sm text-zinc-500">emoji_images</dd>
            @foreach (['emoji', 'emoji_empty', 'emoji_configured', 'emoji_read_only', 'emoji_required'] as $handle)
                <dt class="font-mono text-sm">{{ $handle }}</dt>
                <dd class="text-2xl">{{ $page->$handle ?? '–' }}</dd>
                <dd class="text-2xl">{!! $images($page->augmentedValue($handle)) !!}</dd>
            @endforeach
        </dl>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">All sets <span class="font-mono text-sm">emoji_images('…')</span></h2>
        <dl class="grid grid-cols-[max-content_1fr] gap-x-6 gap-y-2 text-2xl">
            @foreach (['native', 'noto', 'fluent', 'openmoji'] as $set)
                <dt class="font-mono text-sm">{{ $set }}</dt>
                <dd>{!! $images($page->emoji_text, $set) !!} {!! $images($sample, $set) !!}</dd>
            @endforeach
        </dl>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Emoji Text fieldtype</h2>
        <dl class="grid grid-cols-[max-content_1fr_1fr] gap-x-6 gap-y-2">
            @foreach (['emoji_text', 'emoji_text_configured', 'emoji_text_required'] as $handle)
                <dt class="font-mono text-sm">{{ $handle }}</dt>
                <dd>{{ $page->$handle ?? '–' }}</dd>
                <dd>{!! $images($page->augmentedValue($handle)) !!}</dd>
            @endforeach
        </dl>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Bard</h2>
        <h3 class="font-mono text-sm">content</h3>
        <div class="prose dark:prose-invert">{!! $page->content !!}</div>
        <h3 class="font-mono text-sm">content | emoji_images('noto')</h3>
        <div class="prose dark:prose-invert">{!! $images($page->content, 'noto') !!}</div>
        <h3 class="font-mono text-sm">content_without_emoji</h3>
        <div class="prose dark:prose-invert">{!! $page->content_without_emoji !!}</div>
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Replicator <span class="font-mono text-sm">blocks</span></h2>
        @forelse ($page->blocks ?? [] as $block)
            @if ($block->type === 'callout')
                <aside class="flex gap-3 rounded-lg bg-white p-4 dark:bg-zinc-800">
                    <span class="text-2xl">{!! $images($block->getProxiedInstance()->get('icon')) !!}</span>
                    <div class="prose dark:prose-invert">{!! $images($block->text) !!}</div>
                </aside>
            @endif
        @empty
            <p>–</p>
        @endforelse
    </section>

    <section class="space-y-3">
        <h2 class="text-xl font-semibold">Grid <span class="font-mono text-sm">reactions (noto)</span></h2>
        <ul class="flex flex-wrap gap-2">
            @forelse ($page->reactions ?? [] as $reaction)
                <li class="rounded-full bg-white px-3 py-1 dark:bg-zinc-800">{!! $images($reaction->getProxiedInstance()->get('emoji')) !!} {{ $reaction->label }}</li>
            @empty
                <li>–</li>
            @endforelse
        </ul>
    </section>
</div>
