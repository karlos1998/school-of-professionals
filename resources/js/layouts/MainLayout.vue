<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import brandLogo from '@/assets/szkolazawodowcow-logo.svg';
</script>

<template>
    <v-app class="main-shell">
        <v-main>
            <div class="hero-backdrop" />
            <header class="topbar">
                <v-container class="px-4 px-md-8 layout-container" fluid>
                    <div class="d-flex align-center justify-space-between ga-3 py-2">
                        <div class="d-flex align-center ga-2">
                            <a
                                class="brand-logo-link"
                                href="https://szkolazawodowcow.pl/"
                                target="_blank"
                                rel="noopener noreferrer"
                            >
                                <img
                                    alt="Szkola Zawodowcow"
                                    class="brand-logo"
                                    :src="brandLogo"
                                >
                            </a>
                            <div>
                                <p class="brand-overline">Platforma Egzaminacyjna</p>
                                <h1 class="brand-title">Testy UDT i WIT</h1>
                            </div>
                        </div>
                        <Link
                            class="producer-link"
                            href="/"
                        >
                            Strona główna
                        </Link>
                    </div>
                </v-container>
            </header>
            <v-container class="py-6 px-4 px-md-8 layout-container content-container" fluid>
                <slot />
            </v-container>
            <footer class="site-footer">
                <v-container class="px-4 px-md-8 layout-container" fluid>
                    <a class="footer-link" href="https://letscode.it" target="_blank" rel="noopener noreferrer">
                        Let's Code It! - Karol Sójka
                    </a>
                </v-container>
            </footer>
        </v-main>
    </v-app>
</template>

<style scoped>
.main-shell {
    min-height: 100vh;
    position: relative;
}

.hero-backdrop {
    position: fixed;
    inset: 0;
    z-index: -1;
    background:
        radial-gradient(circle at 10% 12%, rgba(244, 199, 90, 0.2), transparent 34%),
        radial-gradient(circle at 84% 18%, rgba(64, 66, 72, 0.18), transparent 33%),
        linear-gradient(167deg, #f4f4f5 0%, #efeff1 44%, #e8e8eb 100%);
}

.hero-backdrop::before,
.hero-backdrop::after {
    content: '';
    position: absolute;
    border-radius: 999px;
    border: 2px solid rgba(227, 185, 79, 0.7);
    opacity: 0.72;
}

.hero-backdrop::before {
    width: 170px;
    height: 170px;
    left: 6%;
    top: 26%;
    clip-path: inset(0 0 44% 44%);
}

.hero-backdrop::after {
    width: 150px;
    height: 150px;
    right: 8%;
    top: 58%;
    clip-path: inset(42% 42% 0 0);
}

.layout-container {
    max-width: 1380px;
    margin: 0 auto;
}

.content-container {
    min-height: calc(100vh - 64px);
}

.topbar {
    background: linear-gradient(145deg, #3a3c41 0%, #4a4d53 52%, #5a5d64 100%);
    border-bottom: 1px solid rgba(227, 185, 79, 0.48);
    box-shadow: 0 10px 25px rgba(38, 40, 44, 0.3);
}

.brand-logo {
    width: 84px;
    max-width: 45vw;
    filter: drop-shadow(0 4px 10px rgba(0, 0, 0, 0.2));
}

.brand-logo-link {
    display: inline-flex;
}

.brand-overline {
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: rgba(236, 198, 98, 0.9);
    font-size: 0.52rem;
    margin-bottom: 1px;
}

.brand-title {
    color: #f6f6f7;
    font-size: clamp(0.76rem, 1.2vw, 1rem);
    line-height: 1.1;
}

.producer-link {
    color: #f2d487;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.7rem;
    border: 1px solid rgba(227, 185, 79, 0.55);
    border-radius: 999px;
    padding: 6px 10px;
    transition: all 0.2s ease;
    white-space: nowrap;
}

.producer-link:hover {
    color: #ffe4a3;
    border-color: rgba(240, 207, 122, 0.85);
    background: rgba(255, 255, 255, 0.08);
}

@media (max-width: 600px) {
    .producer-link {
        font-size: 0.62rem;
        padding: 5px 8px;
    }
}

.site-footer {
    border-top: 1px solid rgba(226, 182, 75, 0.35);
    background: linear-gradient(145deg, rgba(58, 60, 65, 0.96), rgba(78, 81, 88, 0.96));
    backdrop-filter: blur(3px);
}

.footer-link {
    display: block;
    padding: 14px 0;
    text-align: center;
    color: #f0d188;
    font-weight: 600;
    font-size: 0.92rem;
    text-decoration: none;
    transition: color 0.2s ease;
}

.footer-link:hover {
    color: #ffdd8b;
}
</style>
