<?php

namespace Convoy\Emojis\Http\Controllers;

use Convoy\Emojis\EmojiSettings;
use Statamic\CP\PublishForm;
use Statamic\Http\Controllers\CP\CpController;

/**
 * The Emojis CP page: the addon's settings form (saved through Statamic's addon settings route).
 */
class EmojisPageController extends CpController
{
    public function __invoke(EmojiSettings $settings)
    {
        $addon = $settings->addon();

        $this->authorize('editSettings', $addon);

        return PublishForm::make($addon->settingsBlueprint())
            ->asConfig()
            ->icon(file_get_contents(__DIR__.'/../../../resources/svg/smile.svg'))
            ->title(__('statamic-emojis::messages.custom.nav'))
            ->values($addon->settings()->raw())
            ->submittingTo(cp_route('addons.settings.update', $addon->slug()));
    }
}
