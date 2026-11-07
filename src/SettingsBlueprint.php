<?php

namespace StatamicEmojis;

/**
 * Blueprint of the Emojis CP page (Statamic addon settings, saved to resources/addons/statamic-emojis.yaml).
 * Labels are translation keys, see EmojiFields.
 *
 * The free edition shows only the picker defaults; the emoji set and the Custom Emojis and Image Cache
 * tabs are part of Pro. The page's Pro hint is in resources/js/pages/EmojisPage.vue.
 */
class SettingsBlueprint
{
    public static function contents(): array
    {
        // Translation keys, not texts: this blueprint is built while the addon boots, before Statamic
        // switches to the user's locale. The Control Panel translates them when rendering the page.
        $t = fn(string $key) => "statamic-emojis::messages.settings.{$key}";
        $pro = app(EmojiSettings::class)->pro();

        $general = [
            // The emoji set is part of Pro
            ...($pro ? [[
                'display' => $t('section_set'),
                'fields' => [
                    ['handle' => 'emoji_set', 'field' => EmojiFields::emojiSet()],
                ],
            ]] : []),
            [
                'display' => $t('section_picker'),
                'instructions' => $t('section_picker_instructions'),
                'fields' => [
                    ['handle' => 'categories', 'field' => EmojiFields::categories(inherit: false)],
                    ['handle' => 'skin_tone', 'field' => EmojiFields::skinTone(inherit: false)],
                    ['handle' => 'skin_tone_selector', 'field' => EmojiFields::skinToneSelector(inherit: false)],
                    ['handle' => 'suggestions', 'field' => [
                        'type' => 'toggle',
                        'default' => true,
                        'display' => $t('suggestions'),
                        'instructions' => $t('suggestions_instructions'),
                        'width' => 50,
                    ]],
                    ['handle' => 'recent_emojis', 'field' => [
                        'type' => 'toggle',
                        'default' => true,
                        'display' => $t('recent_emojis'),
                        'instructions' => $t('recent_emojis_instructions'),
                        'width' => 50,
                    ]],
                    // Free edition: keep the Pro settings as they are when saving (Statamic only saves the
                    // blueprint's fields), so they still apply after switching back to Pro
                    ...($pro ? [] : [
                        ['handle' => 'emoji_set', 'field' => ['type' => 'hidden']],
                        ['handle' => 'custom_emojis', 'field' => ['type' => 'hidden']],
                    ]),
                ],
            ],
        ];

        $tabs = ['general' => ['display' => $t('tab_general'), 'sections' => $general]];

        if (! $pro) {
            return ['tabs' => $tabs];
        }

        $tabs['custom'] = [
            'display' => $t('tab_custom'),
            'sections' => [
                [
                    'fields' => [
                        ['handle' => 'custom_emojis', 'field' => EmojiFields::customEmojis(inherit: false)],
                        ['handle' => 'custom_emoji_grid', 'field' => [
                            'type' => 'statamic_emojis_custom_grid',
                            'display' => $t('custom_grid'),
                            'hide_display' => true,
                            'if' => ['custom_emojis' => 'equals true'],
                        ]],
                    ],
                ],
            ],
        ];

        $tabs['cache'] = [
            'display' => $t('tab_cache'),
            'sections' => [
                [
                    'display' => $t('cache'),
                    'instructions' => $t('cache_instructions'),
                    'fields' => [
                        ['handle' => 'image_cache', 'field' => [
                            'type' => 'statamic_emojis_image_cache',
                            'display' => $t('cache'),
                            'hide_display' => true,
                        ]],
                    ],
                ],
            ],
        ];

        return ['tabs' => $tabs];
    }
}
