import { defineConfig } from "vitepress";

const repo = "https://github.com/jeslage/statamic-emojis";

// Served from https://jeslage.github.io/statamic-emojis/ for now.
// For a custom domain (e.g. emojis.johanneseslage.de): add docs/public/CNAME with the domain,
// change `base` to "/", point a DNS CNAME record at jeslage.github.io and set the domain
// under the repository's Settings → Pages.
const base = "/statamic-emojis/";

export default defineConfig({
    base,
    lang: "en-US",
    title: "Statamic Emojis",
    description:
        "An emoji picker for the Statamic 6 Control Panel: fieldtypes, a Bard button, shortcode suggestions, emoji sets and custom emojis.",
    cleanUrls: true,
    lastUpdated: true,

    head: [["link", { rel: "icon", type: "image/svg+xml", href: `${base}favicon.svg` }]],

    markdown: {
        // Shiki has no Antlers or Blade grammar
        languageAlias: {
            antlers: "html",
            blade: "php",
        },
    },

    themeConfig: {
        logo: { src: "/favicon.svg", alt: "" },

        nav: [
            { text: "Guide", link: "/guide/getting-started", activeMatch: "/guide/" },
            { text: "Reference", link: "/reference/configuration", activeMatch: "/reference/" },
            {
                text: "Links",
                items: [
                    { text: "Changelog", link: `${repo}/blob/main/CHANGELOG.md` },
                    { text: "Statamic Marketplace", link: "https://statamic.com/addons" },
                    { text: "Issues", link: `${repo}/issues` },
                ],
            },
        ],

        sidebar: [
            {
                text: "Getting started",
                items: [{ text: "Editions & installation", link: "/guide/getting-started" }],
            },
            {
                text: "Usage",
                items: [
                    { text: "Fieldtypes", link: "/guide/fieldtypes" },
                    { text: "Settings", link: "/guide/settings" },
                    { text: "Emoji sets", link: "/guide/emoji-sets" },
                    { text: "Templates", link: "/guide/templates" },
                    { text: "Custom emojis", link: "/guide/custom-emojis" },
                    { text: "Shortcode suggestions", link: "/guide/shortcode-suggestions" },
                    { text: "Translations", link: "/guide/translations" },
                ],
            },
            {
                text: "Reference",
                items: [
                    { text: "Configuration", link: "/reference/configuration" },
                    { text: "Commands", link: "/reference/commands" },
                    { text: "API (REST & GraphQL)", link: "/reference/api" },
                    { text: "Emoji data", link: "/reference/emoji-data" },
                ],
            },
            {
                text: "More",
                items: [
                    { text: "Notes", link: "/more/notes" },
                    { text: "Roadmap", link: "/more/roadmap" },
                    { text: "Contributing", link: "/more/contributing" },
                    { text: "License & credits", link: "/more/credits" },
                ],
            },
        ],

        socialLinks: [{ icon: "github", link: repo }],

        search: { provider: "local" },

        editLink: {
            pattern: `${repo}/edit/main/docs/:path`,
            text: "Edit this page on GitHub",
        },

        footer: {
            message:
                "Commercial software: the free edition is free of charge, Pro needs a license per site in production.",
            copyright: "© Johannes Eslage",
        },
    },
});
