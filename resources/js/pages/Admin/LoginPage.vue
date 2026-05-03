<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    email: '',
    password: '',
});

const submit = (): void => {
    form.post('/admin-panel/login');
};
</script>

<template>
    <v-app>
        <v-main class="d-flex align-center justify-center">
            <v-card width="420" class="pa-6">
                <v-card-title>Logowanie do panelu admina</v-card-title>
                <v-card-text>
                    <v-form @submit.prevent="submit">
                        <v-text-field v-model="form.email" label="Email" type="email" :error-messages="form.errors.email" />
                        <v-text-field v-model="form.password" label="Hasło" type="password" :error-messages="form.errors.password" />
                        <v-btn type="submit" color="primary" block :loading="form.processing">Zaloguj</v-btn>
                    </v-form>
                </v-card-text>
            </v-card>
        </v-main>
    </v-app>
</template>
