<?php

namespace StatamicEmojis;

/**
 * Field definitions for the picker settings, shared by the fieldtypes' config ("Emoji Picker" section)
 * and the site-wide settings on the Emojis CP page.
 *
 * In fields, empty values mean "use the site-wide setting"; on the settings page they have real defaults.
 *
 * Texts: fields are built during the request (in the user's locale) and get translated texts, as their
 * "Default (…)" placeholders need the site-wide value filled in. The settings page blueprint is built while
 * the addon boots, before Statamic switches to the user's locale, so it gets translation keys instead;
 * the Control Panel translates displays, instructions, placeholders and option labels when rendering.
 */
class EmojiFields
{
    /** The site-wide emoji set (settings page only: fields have no set of their own). */
    public static function emojiSet(): array
    {
        $t = self::translator(false);
        $sets = app(EmojiSets::class);

        return [
            'display' => $t('config.emoji_set'),
            'instructions' => $t('settings.emoji_set_instructions'),
            'type' => 'statamic_emojis_set',
            'options' => collect($sets->sets())
                ->mapWithKeys(fn ($set) => [$set => $sets->label($set, translate: false)])
                ->all(),
            // The settings page must not read the settings (its blueprint is built while they load)
            'default' => config('statamic-emojis.set', EmojiSets::NATIVE),
        ];
    }

    public static function categories(bool $inherit): array
    {
        $t = self::translator($inherit);

        return [
            'display' => $t('config.categories'),
            'type' => 'checkboxes',
            'inline' => true,
            'options' => collect(Emojibase::CATEGORIES)
                ->keys()
                ->mapWithKeys(fn ($key) => [$key => $t("categories.{$key}")])
                ->all(),
            // Fields: empty = site-wide setting. Settings page: all selected by default, at least one required.
            ...($inherit
                ? ['instructions' => $t('config.categories_instructions')]
                : ['default' => array_keys(Emojibase::CATEGORIES), 'validate' => ['required']]),
        ];
    }

    public static function skinTone(bool $inherit): array
    {
        $t = self::translator($inherit);

        return [
            'display' => $t('config.skin_tone'),
            'type' => 'statamic_emojis_skin_tone',
            'width' => 50,
            ...($inherit
                ? ['default_label' => __('statamic-emojis::messages.options.default', [
                    'value' => __('statamic-emojis::messages.skin_tones.'.app(EmojiSettings::class)->skinTone()),
                ])]
                : ['default' => 'none']),
        ];
    }

    public static function skinToneSelector(bool $inherit): array
    {
        return self::showHide($inherit, 'skin_tone_selector', fn () => app(EmojiSettings::class)->skinToneSelector(), true);
    }

    public static function customEmojis(bool $inherit): array
    {
        return self::showHide(
            $inherit,
            'custom_emojis',
            fn () => app(EmojiSettings::class)->customEmojis(),
            (bool) config('statamic-emojis.custom.enabled', true),
        );
    }

    /** A translation key of this addon: the translated text for fields, the key itself for the settings page. */
    public static function translator(bool $translate): \Closure
    {
        return fn (string $key) => $translate
            ? __("statamic-emojis::messages.{$key}")
            : "statamic-emojis::messages.{$key}";
    }

    /** On the settings page a toggle; in fields a select with "Default (…)", "Show" and "Hide". */
    protected static function showHide(bool $inherit, string $key, \Closure $siteDefault, bool $configDefault): array
    {
        $t = self::translator($inherit);

        $field = [
            'display' => $t("config.{$key}"),
            'width' => 50,
        ];

        if (! $inherit) {
            return $field + ['type' => 'toggle', 'default' => $configDefault];
        }

        return $field + [
            'type' => 'select',
            'clearable' => true,
            'placeholder' => __('statamic-emojis::messages.options.default', [
                'value' => __('statamic-emojis::messages.options.'.($siteDefault() ? 'show' : 'hide')),
            ]),
            'options' => [
                'show' => $t('options.show'),
                'hide' => $t('options.hide'),
            ],
        ];
    }
}
