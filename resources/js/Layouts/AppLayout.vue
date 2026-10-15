<script setup>
import { computed, onMounted } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import { useTheme } from 'vuetify';

defineProps({ title: { type: String, default: 'Painel' } });

const page = usePage();
const theme = useTheme();
const user = computed(() => page.props.auth?.user);
const empresaAtual = computed(() => page.props.empresaAtual);
const empresas = computed(() => page.props.empresasDisponiveis ?? []);
const success = computed(() => page.props.flash?.success);
const darkMode = computed(() => theme.global.name.value === 'dark');

onMounted(() => {
    if (localStorage.getItem('gestao-eventos-theme') === 'dark') theme.global.name.value = 'dark';
});

function alternarTema() {
    theme.global.name.value = darkMode.value ? 'light' : 'dark';
    localStorage.setItem('gestao-eventos-theme', theme.global.name.value);
}

function trocarEmpresa(id) {
    if (id && id !== empresaAtual.value?.id) {
        router.post(`/app/empresas/${id}/ativar`, {}, { preserveScroll: true });
    }
}

function sair() {
    router.post('/logout');
}
</script>

<template>
    <v-app>
        <v-navigation-drawer permanent elevation="1" color="surface">
            <div class="pa-5">
                <div class="text-overline text-red-darken-4">Cantinho da Costela</div>
                <div class="text-h6 font-weight-bold">Gestão de Eventos</div>
            </div>
            <v-divider />
            <v-list nav density="comfortable" class="pa-3">
                <v-list-item prepend-icon="mdi-view-dashboard-outline" title="Dashboard" href="/app/dashboard" />
                <v-list-item prepend-icon="mdi-storefront-outline" title="Minha empresa" href="/app/empresa" />
                <v-list-item prepend-icon="mdi-food-outline" title="Catálogo" href="/app/catalogo" />
                <v-list-item prepend-icon="mdi-package-variant-closed" title="Pacotes" href="/app/pacotes" />
                <v-list-item prepend-icon="mdi-file-document-outline" title="Solicitações" href="/app/solicitacoes" />
            </v-list>
        </v-navigation-drawer>

        <v-app-bar flat border="b">
            <v-app-bar-title class="text-subtitle-1 font-weight-medium">{{ title }}</v-app-bar-title>
            <v-spacer />
            <v-btn :icon="darkMode ? 'mdi-weather-sunny' : 'mdi-weather-night'" variant="text" :aria-label="darkMode ? 'Ativar modo claro' : 'Ativar modo escuro'" :title="darkMode ? 'Modo claro' : 'Modo escuro'" @click="alternarTema" />
            <v-select
                :model-value="empresaAtual?.id"
                :items="empresas"
                item-title="nome_fantasia"
                item-value="id"
                density="compact"
                variant="outlined"
                hide-details
                class="mr-4 mt-1"
                style="max-width: 230px"
                aria-label="Empresa ativa"
                @update:model-value="trocarEmpresa"
            />
            <v-menu>
                <template #activator="{ props }">
                    <v-btn v-bind="props" icon="mdi-account-circle-outline" variant="text" class="mr-2" />
                </template>
                <v-list min-width="220">
                    <v-list-item :title="user?.name" :subtitle="user?.email" />
                    <v-divider />
                    <v-list-item title="Sair" prepend-icon="mdi-logout" @click="sair" />
                </v-list>
            </v-menu>
        </v-app-bar>

        <v-main>
            <v-container fluid class="pa-6 pa-md-8">
                <v-alert v-if="success" type="success" variant="tonal" closable class="mb-5">{{ success }}</v-alert>
                <slot />
            </v-container>
        </v-main>
    </v-app>
</template>
