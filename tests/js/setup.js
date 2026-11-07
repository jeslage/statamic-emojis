// The Control Panel's globals, with config tests can change (Statamic.$config.get('statamicEmojis') etc.)
import { afterEach, vi } from 'vitest';

export const config = {};

export function resetConfig(statamicEmojis = {}) {
    Object.keys(config).forEach((key) => delete config[key]);
    Object.assign(config, {
        cpUrl: '/cp',
        locale: 'en',
        statamicEmojis: { set: 'native', imageUrl: '/!/statamic-emojis/sets', custom: false, ...statamicEmojis },
    });
}

resetConfig();

// jsdom has no layout: ranges have no client rects
Range.prototype.getBoundingClientRect ??= () => new DOMRect(0, 0, 0, 0);

globalThis.Statamic = { $config: { get: (key) => config[key] } };
globalThis.__ = (key) => key;

afterEach(() => {
    vi.unstubAllGlobals();
    localStorage.clear();
    sessionStorage.clear();
});
