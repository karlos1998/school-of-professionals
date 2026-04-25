<script setup lang="ts">
import brandLogo from '@/assets/szkolazawodowcow-logo.svg';
</script>

<template>
    <v-app class="main-shell">
        <v-main>
            <div class="hero-backdrop" />
            <header class="topbar">
                <v-container class="px-4 px-md-8 layout-container" fluid>
                    <div class="d-flex align-center ga-4 py-4">
                        <img
                            alt="Szkola Zawodowcow"
                            class="brand-logo"
                            :src="brandLogo"
                        >
                        <div>
                            <p class="brand-overline">Platforma Egzaminacyjna</p>
                            <h1 class="brand-title">School Of Professionals</h1>
                        </div>
                    </div>
                </v-container>
            </header>
            <v-container class="py-6 px-4 px-md-8 layout-container content-container" fluid>
                <slot />
            </v-container>
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
        radial-gradient(circle at 12% 15%, rgba(47, 127, 196, 0.2), transparent 45%),
        radial-gradient(circle at 85% 10%, rgba(248, 180, 0, 0.15), transparent 35%),
        linear-gradient(160deg, #eff4ff 0%, #f6f9ff 42%, #fdf6e7 100%);
}

.layout-container {
    max-width: 1380px;
    margin: 0 auto;
}

.content-container {
    min-height: calc(100vh - 100px);
}

.topbar {
    background: linear-gradient(145deg, #10243c 0%, #143456 60%, #1a4a78 100%);
    border-bottom: 1px solid rgba(255, 255, 255, 0.14);
    box-shadow: 0 10px 25px rgba(9, 23, 38, 0.2);
}

.brand-logo {
    width: 180px;
    max-width: 45vw;
    filter: drop-shadow(0 8px 20px rgba(0, 0, 0, 0.25));
}

.brand-overline {
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: rgba(233, 241, 252, 0.8);
    font-size: 0.74rem;
    margin-bottom: 4px;
}

.brand-title {
    color: #f4f8ff;
    font-size: clamp(1.2rem, 2vw, 1.9rem);
    line-height: 1.1;
}
</style>
