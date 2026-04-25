<script setup lang="ts">
import brandLogo from '@/assets/szkolazawodowcow-logo.svg';
</script>

<template>
    <v-app class="main-shell">
        <v-main>
            <div class="hero-backdrop" />
            <v-container class="py-8 px-4 px-md-8 layout-container" fluid>
                <header class="d-flex flex-column flex-md-row align-start align-md-center justify-space-between ga-4 mb-8">
                    <div class="d-flex align-center ga-4">
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
                    <v-chip color="secondary" prepend-icon="mdi-shield-check" size="large" variant="flat">
                        Tryb bez logowania
                    </v-chip>
                </header>

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

.brand-logo {
    width: 180px;
    max-width: 45vw;
    filter: drop-shadow(0 8px 20px rgba(15, 76, 129, 0.18));
}

.brand-overline {
    letter-spacing: 0.13em;
    text-transform: uppercase;
    color: #4d6a88;
    font-size: 0.74rem;
    margin-bottom: 4px;
}

.brand-title {
    color: #0f3557;
    font-size: clamp(1.2rem, 2vw, 1.9rem);
    line-height: 1.1;
}
</style>
