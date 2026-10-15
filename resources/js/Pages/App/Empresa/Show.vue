<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({ empresa: { type: Object, required: true } });
const form = useForm({ ...props.empresa });
const submit = () => form.put('/app/empresa');
</script>

<template>
    <Head title="Minha empresa" />
    <div class="mb-7">
        <h1 class="text-h4 font-weight-bold">Minha empresa</h1>
        <p class="text-body-1 text-medium-emphasis mt-1">Mantenha os dados públicos e operacionais do estabelecimento atualizados.</p>
    </div>

    <v-card rounded="lg" border flat>
        <v-card-item class="pa-6 pb-2">
            <v-card-title class="pa-0 text-h6">Dados básicos</v-card-title>
            <v-card-subtitle class="pa-0 mt-1">Essas informações serão usadas na vitrine pública futuramente.</v-card-subtitle>
        </v-card-item>
        <v-form @submit.prevent="submit">
            <v-card-text class="pa-6">
                <v-row>
                    <v-col cols="12" md="6"><v-text-field v-model="form.nome_fantasia" label="Nome fantasia" :error-messages="form.errors.nome_fantasia" /></v-col>
                    <v-col cols="12" md="6"><v-text-field v-model="form.razao_social" label="Razão social" :error-messages="form.errors.razao_social" /></v-col>
                    <v-col cols="12" md="6"><v-text-field v-model="form.documento" label="CNPJ ou documento" :error-messages="form.errors.documento" /></v-col>
                    <v-col cols="12" md="6"><v-text-field v-model="form.slug" label="Identificador público" :error-messages="form.errors.slug" hint="Use apenas letras, números, hífen e underscore." persistent-hint /></v-col>
                    <v-col cols="12" md="6"><v-text-field v-model="form.email" label="E-mail" type="email" :error-messages="form.errors.email" /></v-col>
                    <v-col cols="12" md="6"><v-text-field v-model="form.telefone" label="Telefone" :error-messages="form.errors.telefone" /></v-col>
                    <v-col cols="12" md="6"><v-select v-model="form.timezone" label="Fuso horário" :items="['America/Sao_Paulo', 'America/Fortaleza', 'America/Recife', 'America/Manaus']" :error-messages="form.errors.timezone" /></v-col>
                </v-row>
            </v-card-text>
            <v-divider />
            <v-card-actions class="pa-6">
                <v-spacer />
                <v-btn type="submit" color="primary" :loading="form.processing" prepend-icon="mdi-content-save-outline">Salvar alterações</v-btn>
            </v-card-actions>
        </v-form>
    </v-card>
</template>
