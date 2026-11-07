<?php

namespace StatamicEmojis\Http\Controllers;

use StatamicEmojis\EmojiSettings;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Statamic\Http\Controllers\CP\CpController;

/**
 * The Emojis CP page: the addon's settings form (saved through Statamic's addon settings route).
 *
 * Rendered with the addon's own Inertia page (resources/js/pages/EmojisPage.vue), the same form as
 * Statamic's PublishForm plus, in the free edition, a hint above the form with a "Get Pro" button.
 */
class EmojisPageController extends CpController
{
    public function __invoke(Request $request, EmojiSettings $settings)
    {
        $addon = $settings->addon();

        $this->authorize('editSettings', $addon);

        $blueprint = $addon->settingsBlueprint();
        $fields = $blueprint->fields()->addValues($addon->settings()->raw())->preProcess();

        $data = [
            'blueprint' => $blueprint->toPublishArray(),
            'icon' => file_get_contents(__DIR__.'/../../../resources/svg/smile.svg'),
            'title' => __('statamic-emojis::messages.custom.nav'),
            'values' => $fields->values(),
            'meta' => $fields->meta(),
            'submitUrl' => cp_route('addons.settings.update', $addon->slug()),
            'submitMethod' => 'PATCH',
            // Free edition: link to buy Pro next to the Save button
            'proUrl' => $settings->pro() ? null : EmojiSettings::PRO_URL,
        ];

        if ($request->wantsJson()) {
            return $data;
        }

        return Inertia::render('statamic-emojis::EmojisPage', $data);
    }
}
