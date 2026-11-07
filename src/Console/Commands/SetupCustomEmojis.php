<?php

namespace StatamicEmojis\Console\Commands;

use StatamicEmojis\CustomEmojis;
use StatamicEmojis\EmojiSettings;
use Illuminate\Console\Command;
use Statamic\Console\RunsInPlease;
use Statamic\Facades\AssetContainer;
use Statamic\Facades\Blueprint;

/**
 * Creates the asset container and blueprint for custom emojis. Safe to run again.
 */
class SetupCustomEmojis extends Command
{
    use RunsInPlease;

    protected $signature = 'emojis:custom-setup';

    protected $description = 'Create the asset container and blueprint for custom emojis';

    public function handle(CustomEmojis $custom): int
    {
        if (! app(EmojiSettings::class)->pro()) {
            $this->components->error('Custom emojis are part of Statamic Emojis Pro.');

            return self::FAILURE;
        }

        $handle = $custom->containerHandle();

        if ($container = AssetContainer::find($handle)) {
            $this->components->info("Asset container [{$handle}] already exists.");
        } else {
            AssetContainer::make($handle)
                ->title('Emojis')
                ->disk(config('statamic-emojis.custom.disk', 'statamic-emojis'))
                // Laravel's "image" rule excludes SVG unless allow_svg is set. Statamic sanitizes uploaded SVGs.
                ->validationRules(['image:allow_svg', 'mimes:png,gif,webp,svg', 'max:1024'])
                ->save();

            $this->components->info("Created asset container [{$handle}].");
        }

        if (Blueprint::find("assets/{$handle}")) {
            $this->components->info("Blueprint [assets/{$handle}] already exists.");
        } else {
            // Labels are translation keys: the Control Panel translates field display and instructions
            $key = fn (string $name) => "statamic-emojis::messages.blueprint.{$name}";

            Blueprint::make($handle)->setNamespace('assets')->setContents([
                'title' => 'Emoji',
                'tabs' => [
                    'main' => [
                        'sections' => [[
                            'fields' => [
                                ['handle' => 'alt', 'field' => [
                                    'type' => 'text',
                                    'display' => $key('name'),
                                    'instructions' => $key('name_instructions'),
                                ]],
                                ['handle' => 'shortcode', 'field' => [
                                    'type' => 'text',
                                    'display' => $key('shortcode'),
                                    'instructions' => $key('shortcode_instructions'),
                                    'validate' => ['nullable', 'regex:/^[a-z0-9_+-]+$/'],
                                ]],
                                ['handle' => 'keywords', 'field' => [
                                    'type' => 'taggable',
                                    'display' => $key('keywords'),
                                    'instructions' => $key('keywords_instructions'),
                                ]],
                            ],
                        ]],
                    ],
                ],
            ])->save();

            $this->components->info("Created blueprint [assets/{$handle}].");
        }

        $custom->clearCache();
        $this->components->info('Upload images under Settings → Emojis → Custom Emojis in the Control Panel.');

        return self::SUCCESS;
    }
}
