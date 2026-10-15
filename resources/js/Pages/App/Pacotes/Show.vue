<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps({ pacote: { type: Object, required: true } });
</script>

<template>
    <Head :title="pacote.nome" />
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3">
        <div><v-btn href="/app/pacotes" variant="text" prepend-icon="mdi-arrow-left" class="mb-2 px-0">Voltar para pacotes</v-btn><h1 class="text-h4 font-weight-bold">{{ pacote.nome }}</h1><p class="text-body-1 text-medium-emphasis mt-1">{{ pacote.descricao || 'Detalhes da composição deste pacote.' }}</p></div>
        <v-chip :color="pacote.ativo ? 'success' : 'grey'" size="large">{{ pacote.ativo ? 'Ativo' : 'Inativo' }}</v-chip>
    </div>

    <v-row class="mb-2">
        <v-col cols="12" sm="4"><v-card border flat rounded="lg" class="pa-5"><div class="text-body-2 text-medium-emphasis">Preço base</div><div class="text-h5 font-weight-bold mt-1">R$ {{ pacote.preco_base }} {{ pacote.tipo_preco === 'por_pessoa' ? '/ pessoa' : '' }}</div></v-card></v-col>
        <v-col cols="12" sm="4"><v-card border flat rounded="lg" class="pa-5"><div class="text-body-2 text-medium-emphasis">Convidados</div><div class="text-h5 font-weight-bold mt-1">{{ pacote.min_convidados }}{{ pacote.max_convidados ? ` a ${pacote.max_convidados}` : '+' }}</div></v-card></v-col>
        <v-col cols="12" sm="4"><v-card border flat rounded="lg" class="pa-5"><div class="text-body-2 text-medium-emphasis">Publicação</div><div class="text-h5 font-weight-bold mt-1">{{ pacote.publicado ? 'Publicado' : 'Rascunho' }}</div></v-card></v-col>
    </v-row>

    <div class="text-h6 font-weight-bold mt-5 mb-4">Pratos do pacote</div>
    <v-row v-if="pacote.categorias.length"><v-col v-for="categoria in pacote.categorias" :key="categoria.id" cols="12" md="6"><v-card border flat rounded="lg" class="h-100"><v-card-item><template #prepend><v-avatar color="primary" variant="tonal"><v-icon icon="mdi-food-outline" /></v-avatar></template><v-card-title>{{ categoria.nome }}</v-card-title><v-card-subtitle>{{ categoria.obrigatoria ? 'Seleção obrigatória' : 'Seleção opcional' }} · {{ categoria.min_escolhas }} a {{ categoria.max_escolhas }} prato(s)</v-card-subtitle></v-card-item><v-divider /><v-list v-if="categoria.itens.length" density="comfortable"><v-list-item v-for="item in categoria.itens" :key="item.id" :title="item.nome" :subtitle="item.descricao || item.unidade"><template #append><v-icon icon="mdi-check-circle-outline" color="success" /></template></v-list-item></v-list><v-card-text v-else class="text-medium-emphasis">Nenhum prato vinculado a esta categoria.</v-card-text></v-card></v-col></v-row><v-card v-else border flat rounded="lg" class="pa-8 text-center text-medium-emphasis">Este pacote ainda não possui categorias configuradas.</v-card>
</template>
