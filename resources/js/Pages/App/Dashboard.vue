<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });

const props = defineProps({
    metricas: { type: Object, required: true },
    empresa: { type: Object, required: true },
});

const cards = computed(() => [
    { label: 'Solicitações novas', value: props.metricas.solicitacoes_novas, icon: 'mdi-inbox-arrow-down-outline', color: 'blue' },
    { label: 'Propostas enviadas', value: props.metricas.propostas_enviadas, icon: 'mdi-file-send-outline', color: 'orange' },
    { label: 'Aceites pendentes', value: props.metricas.aceites_pendentes, icon: 'mdi-clock-check-outline', color: 'purple' },
    { label: 'Próximos eventos', value: props.metricas.proximos_eventos, icon: 'mdi-calendar-star-outline', color: 'green' },
]);
</script>

<template>
    <Head title="Dashboard" />
    <div class="d-flex flex-wrap align-center justify-space-between mb-7 ga-3">
        <div>
            <h1 class="text-h4 font-weight-bold">Olá, seja bem-vindo!</h1>
            <p class="text-body-1 text-medium-emphasis mt-1">Aqui está um resumo da operação de {{ empresa.nome_fantasia }}.</p>
        </div>
        <v-chip color="success" variant="tonal" prepend-icon="mdi-check-circle-outline">Ambiente ativo</v-chip>
    </div>

    <v-row>
        <v-col v-for="card in cards" :key="card.label" cols="12" sm="6" lg="3">
            <v-card rounded="lg" border flat class="pa-4 h-100">
                <div class="d-flex align-center justify-space-between">
                    <v-avatar :color="card.color" variant="tonal" size="44"><v-icon :icon="card.icon" /></v-avatar>
                    <span class="text-h4 font-weight-bold">{{ card.value }}</span>
                </div>
                <div class="text-body-2 text-medium-emphasis mt-5">{{ card.label }}</div>
            </v-card>
        </v-col>
    </v-row>

    <v-row class="mt-2">
        <v-col cols="12" lg="8">
            <v-card rounded="lg" border flat class="pa-6">
                <div class="d-flex align-center justify-space-between mb-5">
                    <div>
                        <h2 class="text-h6 font-weight-bold">Próximos passos</h2>
                        <p class="text-body-2 text-medium-emphasis mt-1">Prepare seu ambiente para começar a receber solicitações.</p>
                    </div>
                    <v-icon icon="mdi-rocket-launch-outline" color="primary" size="32" />
                </div>
                <v-list lines="two" class="pa-0">
                    <v-list-item prepend-icon="mdi-check-circle-outline" title="Empresa configurada" subtitle="Os dados básicos do seu ambiente já estão prontos." />
                    <v-list-item prepend-icon="mdi-food-outline" title="Cadastre seu catálogo" subtitle="Em breve você poderá criar categorias e itens do buffet." />
                    <v-list-item prepend-icon="mdi-package-variant-closed" title="Monte seus pacotes" subtitle="Configure preços e regras para seus clientes." />
                </v-list>
            </v-card>
        </v-col>
        <v-col cols="12" lg="4">
            <v-card rounded="lg" border flat class="pa-6 h-100">
                <v-avatar color="primary" variant="tonal" size="52"><v-icon icon="mdi-storefront-outline" size="28" /></v-avatar>
                <h2 class="text-h6 font-weight-bold mt-5">Sua empresa</h2>
                <div class="text-body-2 text-medium-emphasis mt-2">{{ empresa.email }}</div>
                <div class="text-body-2 text-medium-emphasis">{{ empresa.usuarios_count }} usuário(s) ativo(s)</div>
                <v-btn href="/app/empresa" variant="tonal" color="primary" class="mt-5" append-icon="mdi-arrow-right">Configurar empresa</v-btn>
            </v-card>
        </v-col>
    </v-row>
</template>
