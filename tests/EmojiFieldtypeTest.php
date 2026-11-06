<?php

namespace Convoy\Emojis\Tests;

use Convoy\Emojis\Fieldtypes\Emoji;
use Statamic\Fieldtypes\Text;

class EmojiFieldtypeTest extends TestCase
{
    public function test_it_extends_the_text_fieldtype(): void
    {
        $this->assertInstanceOf(Text::class, new Emoji);
    }

    public function test_it_has_the_emoji_handle(): void
    {
        $this->assertSame('emoji', Emoji::handle());
    }
}
