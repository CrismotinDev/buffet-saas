<script setup>
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
defineOptions({ layout: AppLayout });
defineProps({ pacotes: Array });
const toggle = (id) => router.post(`/app/pacotes/${id}/alternar`);
</script>
<template>
    <Head title="Pacotes" />
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3"><div><h1 class="text-h4 font-weight-bold">Pacotes</h1><p class="text-body-1 text-medium-emphasis mt-1">Configure ofertas, preços e regras do seu buffet.</p></div><v-btn color="primary" href="/app/pacotes/criar" prepend-icon="mdi-plus">Novo pacote</v-btn></div>
    <v-row v-if="pacotes.length"><v-col v-for="pacote in pacotes" :key="pacote.id" cols="12" md="6" lg="4"><v-card border flat rounded="lg" class="h-100"><v-card-item><template #append><v-chip :color="pacote.ativo ? 'success' : 'grey'" size="small">{{ pacote.ativo ? 'Ativo' : 'Inativo' }}</v-chip></template><v-card-title>{{ pacote.nome }}</v-card-title><v-card-subtitle>{{ pacote.tipo_preco === 'por_pessoa' ? 'Por pessoa' : 'Preço fixo' }}</v-card-subtitle></v-card-item><v-card-text><div class="text-h5 font-weight-bold">R$ {{ pacote.preco_base }}</div><div class="text-body-2 text-medium-emphasis mt-2">{{ pacote.min_convidados }}{{ pacote.max_convidados ? ` a ${pacote.max_convidados}` : '+' }} convidados · {{ pacote.categorias_count }} categoria(s) · {{ pacote.itens_count }} item(ns)</div></v-card-text><v-card-actions><v-btn variant="text" @click="toggle(pacote.id)">{{ pacote.ativo ? 'Desativar' : 'Ativar' }}</v-btn></v-card-actions></v-card></v-col></v-row><v-card v-else border flat rounded="lg" class="pa-10 text-center"><v-icon icon="mdi-package-variant-closed" size="48" color="primary" /><div class="text-h6 mt-4">Nenhum pacote criado</div><div class="text-body-2 text-medium-emphasis mt-2">Crie seu primeiro pacote comercial.</div><v-btn href="/app/pacotes/criar" color="primary" class="mt-5">Criar pacote</v-btn></v-card>
</template>
