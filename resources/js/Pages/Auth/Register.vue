<script setup>
import { useForm } from '@inertiajs/vue3';

const form = useForm({ name: '', email: '', empresa: '', password: '', password_confirmation: '' });
const submit = () => form.post('/cadastro');
</script>

<template>
    <v-app>
        <v-main class="d-flex align-center bg-grey-lighten-4">
            <v-container style="max-width: 500px">
                <v-card rounded="lg" elevation="3" class="pa-6">
                    <div class="text-center mb-6">
                        <div class="text-overline text-red-darken-4">Primeiro passo</div>
                        <h1 class="text-h5 font-weight-bold">Crie sua empresa</h1>
                        <p class="text-body-2 text-medium-emphasis mt-2">Você será o administrador principal do ambiente.</p>
                    </div>
                    <v-form @submit.prevent="submit">
                        <v-text-field v-model="form.empresa" label="Nome da empresa" :error-messages="form.errors.empresa" prepend-inner-icon="mdi-storefront-outline" />
                        <v-text-field v-model="form.name" label="Seu nome" :error-messages="form.errors.name" prepend-inner-icon="mdi-account-outline" />
                        <v-text-field v-model="form.email" label="E-mail" type="email" :error-messages="form.errors.email" prepend-inner-icon="mdi-email-outline" />
                        <v-text-field v-model="form.password" label="Senha" type="password" :error-messages="form.errors.password" prepend-inner-icon="mdi-lock-outline" />
                        <v-text-field v-model="form.password_confirmation" label="Confirme a senha" type="password" prepend-inner-icon="mdi-lock-check-outline" />
                        <v-btn type="submit" block color="primary" size="large" :loading="form.processing" class="mt-3">Criar ambiente</v-btn>
                    </v-form>
                    <div class="text-center text-body-2 mt-6">Já possui uma conta? <a href="/login" class="text-red-darken-4">Entrar</a></div>
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>
