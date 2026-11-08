<script setup lang="ts">
import { withBase } from "vitepress";

// Emojis floating around the icon: [emoji, top %, left %, size rem, delay s]
const floaters: [string, number, number, number, number][] = [
    ["🎉", 4, 10, 2.6, 0],
    ["🦜", 12, 82, 2.4, 1.2],
    ["🚀", 70, 2, 2.2, 0.6],
    ["❤️", 82, 78, 2.2, 1.8],
    ["👍", 42, 94, 1.8, 2.4],
    ["✨", 38, -6, 1.8, 3],
];
</script>

<template>
    <div class="hero-art" aria-hidden="true">
        <div class="hero-glow" />
        <img class="hero-icon" :src="withBase('/logo.png')" alt="" />
        <span
            v-for="([emoji, top, left, size, delay], i) in floaters"
            :key="i"
            class="hero-floater"
            :style="{
                top: `${top}%`,
                left: `${left}%`,
                fontSize: `${size}rem`,
                animationDelay: `${delay}s`,
            }"
            >{{ emoji }}</span
        >
    </div>
</template>

<style scoped>
.hero-art {
    position: relative;
    width: 240px;
    height: 240px;
}

.hero-glow {
    position: absolute;
    inset: -15%;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--sme-blue) 30%, var(--sme-yellow) 70%);
    filter: blur(56px);
    opacity: 0.45;
}

.hero-icon {
    position: absolute;
    inset: 15%;
    width: 70%;
    height: 70%;
    border-radius: 22%;
    box-shadow:
        0 20px 40px -12px rgb(30 60 130 / 0.45),
        0 0 0 1px rgb(255 255 255 / 0.08) inset;
    transform: rotate(-6deg);
    transition: transform 0.4s ease;
}

.hero-art:hover .hero-icon {
    transform: rotate(0deg) scale(1.04);
}

.hero-floater {
    position: absolute;
    line-height: 1;
    filter: drop-shadow(0 6px 8px rgb(0 0 0 / 0.15));
    animation: float 6s ease-in-out infinite;
    user-select: none;
}

@keyframes float {
    0%,
    100% {
        transform: translateY(0) rotate(-4deg);
    }
    50% {
        transform: translateY(-12px) rotate(4deg);
    }
}

@media (prefers-reduced-motion: reduce) {
    .hero-floater {
        animation: none;
    }
}

@media (min-width: 640px) {
    .hero-art {
        width: 300px;
        height: 300px;
    }
}

@media (min-width: 960px) {
    .hero-art {
        width: 340px;
        height: 340px;
    }
}
</style>
