<?php

namespace Convoy\Emojis\Tests;

use Convoy\Emojis\Fieldtypes\Emoji;
use Convoy\Emojis\Fieldtypes\EmojiText;
use Statamic\Fields\Fieldtype;
use Statamic\Fieldtypes\Text;

class EmojiFieldtypeTest extends TestCase
{
    public function test_emoji_is_a_picker_only_fieldtype(): void
    {
        $this->assertSame('emoji', Emoji::handle());
        $this->assertInstanceOf(Fieldtype::class, new Emoji);
        $this->assertNotInstanceOf(Text::class, new Emoji);
    }

    public function test_emoji_text_extends_the_text_fieldtype(): void
    {
        $this->assertSame('emoji_text', EmojiText::handle());
        $this->assertInstanceOf(Text::class, new EmojiText);
    }

    public function test_emoji_stores_a_trimmed_string_or_null(): void
    {
        $emoji = new Emoji;

        $this->assertSame('🎉', $emoji->process(' 🎉 '));
        $this->assertNull($emoji->process(''));
        $this->assertNull($emoji->process(null));
    }

    public function test_both_fieldtypes_have_the_picker_settings(): void
    {
        foreach ([new Emoji, new EmojiText] as $fieldtype) {
            $handles = collect($fieldtype->configFields()->all())->keys();

            foreach (['emoji_set', 'categories', 'skin_tone', 'skin_tone_selector'] as $handle) {
                $this->assertTrue($handles->contains($handle), get_class($fieldtype)." is missing {$handle}");
            }
        }
    }
}
