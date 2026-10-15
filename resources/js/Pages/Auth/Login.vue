<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ email: '', password: '', remember: false });
const submit = () => form.post('/login');
</script>

<template>
    <v-app>
        <v-main class="d-flex align-center bg-grey-lighten-4">
            <v-container style="max-width: 460px">
                <v-card rounded="lg" elevation="3" class="pa-6">
                    <div class="text-center mb-6">
                        <div class="text-overline text-red-darken-4">Gestão de Eventos</div>
                        <h1 class="text-h5 font-weight-bold">Acesse seu painel</h1>
                        <p class="text-body-2 text-medium-emphasis mt-2">Gerencie sua operação com mais clareza.</p>
                    </div>
                    <v-form @submit.prevent="submit">
                        <v-text-field v-model="form.email" label="E-mail" type="email" autocomplete="email" :error-messages="form.errors.email" prepend-inner-icon="mdi-email-outline" class="mb-2" />
                        <v-text-field v-model="form.password" label="Senha" type="password" autocomplete="current-password" :error-messages="form.errors.password" prepend-inner-icon="mdi-lock-outline" class="mb-2" />
                        <v-checkbox v-model="form.remember" label="Lembrar de mim" density="compact" hide-details />
                        <v-btn type="submit" block color="primary" size="large" :loading="form.processing" class="mt-4">Entrar</v-btn>
                    </v-form>
                    <div class="text-center text-body-2 mt-6">Ainda não possui uma conta? <a href="/cadastro" class="text-red-darken-4">Cadastre sua empresa</a></div>
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>
