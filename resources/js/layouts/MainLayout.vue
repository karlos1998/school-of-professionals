<script setup lang="ts">
import brandLogo from '@/assets/szkolazawodowcow-logo.svg';
</script>

<template>
    <v-app class="main-shell">
        <v-main>
            <div class="hero-backdrop" />
            <header class="topbar">
                <v-container class="px-4 px-md-8 layout-container" fluid>
                    <div class="d-flex align-center ga-3 py-3">
                        <img
                            alt="Szkola Zawodowcow"
                            class="brand-logo"
                            :src="brandLogo"
                        >
                        <div>
                            <p class="brand-overline">Platforma Egzaminacyjna</p>
                            <h1 class="brand-title">Testy UDT i WIT</h1>
                        </div>
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
    min-height: calc(100vh - 84px);
}

.topbar {
    background: linear-gradient(145deg, #3a3c41 0%, #4a4d53 52%, #5a5d64 100%);
    border-bottom: 1px solid rgba(227, 185, 79, 0.48);
    box-shadow: 0 10px 25px rgba(38, 40, 44, 0.3);
}

.brand-logo {
    width: 144px;
    max-width: 45vw;
    filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.25));
}

.brand-overline {
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: rgba(236, 198, 98, 0.9);
    font-size: 0.66rem;
    margin-bottom: 2px;
}

.brand-title {
    color: #f6f6f7;
    font-size: clamp(1.02rem, 1.7vw, 1.5rem);
    line-height: 1.1;
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
