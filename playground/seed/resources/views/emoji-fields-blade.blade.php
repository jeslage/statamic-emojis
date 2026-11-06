{{-- Page template for the "Emoji Fields (Blade)" entry. Blade templates don't use the Antlers layout, so this is a full document. --}}
<!doctype html>
<html lang="{{ $site->shortLocale() }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $page->title }}</title>
        @vite(['resources/js/site.js', 'resources/css/site.css'])
    </head>
    <body class="antialiased bg-zinc-100 font-sans leading-normal text-zinc-800 dark:bg-zinc-900 dark:text-zinc-400">
        <x-emoji-fields :page="$page" />
    </body>
</html>
