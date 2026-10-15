<script setup>
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ categorias: Array, itens: Array, adicionais: Array });
const categoria = useForm({ nome: '', ordem: 0 });
const item = useForm({ categoria_ids: [], nome: '', descricao: '', unidade: 'unidade' });
const adicional = useForm({ nome: '', descricao: '', tipo_cobranca: 'por_pessoa', valor_unitario: 0, min_quantidade: 1, max_quantidade: null });
const submit = (form, url) => form.post(url, { onSuccess: () => form.reset() });
const remove = (url) => { if (confirm('Deseja remover este registro?')) router.delete(url); };
</script>

<template>
    <Head title="Catálogo" />
    <div class="d-flex flex-wrap align-center justify-space-between mb-6 ga-3"><div><h1 class="text-h4 font-weight-bold">Catálogo</h1><p class="text-body-1 text-medium-emphasis mt-1">Organize itens e adicionais da sua operação.</p></div></div>
    <v-tabs color="primary" class="mb-5"><v-tab value="categorias">Categorias</v-tab><v-tab value="itens">Itens</v-tab><v-tab value="adicionais">Adicionais</v-tab></v-tabs>
    <v-row>
        <v-col cols="12" md="5"><v-card border flat rounded="lg" class="pa-5"><h2 class="text-h6 mb-4">Nova categoria</h2><v-form @submit.prevent="submit(categoria, '/app/catalogo/categorias')"><v-text-field v-model="categoria.nome" label="Nome" :error-messages="categoria.errors.nome" /><v-text-field v-model="categoria.ordem" type="number" label="Ordem" /><v-btn type="submit" color="primary" :loading="categoria.processing">Adicionar categoria</v-btn></v-form></v-card></v-col>
        <v-col cols="12" md="7"><v-card border flat rounded="lg"><v-card-title>Categorias cadastradas</v-card-title><v-list v-if="categorias.length"><v-list-item v-for="cat in categorias" :key="cat.id" :title="cat.nome" :subtitle="`${cat.itens_count} item(ns)`"><template #append><v-btn icon="mdi-delete-outline" variant="text" color="error" @click="remove(`/app/catalogo/categorias/${cat.id}`)" /></template></v-list-item></v-list><v-card-text v-else class="text-medium-emphasis">Nenhuma categoria cadastrada.</v-card-text></v-card></v-col>
    </v-row>
    <v-row class="mt-2">
        <v-col cols="12" md="5"><v-card border flat rounded="lg" class="pa-5"><h2 class="text-h6 mb-4">Novo item</h2><v-form @submit.prevent="submit(item, '/app/catalogo/itens')"><v-text-field v-model="item.nome" label="Nome" :error-messages="item.errors.nome" /><v-select v-model="item.categoria_ids" label="Categorias" :items="categorias" item-title="nome" item-value="id" multiple chips closable-chips :error-messages="item.errors.categoria_ids" hint="O mesmo item pode pertencer a várias categorias." persistent-hint /><v-textarea v-model="item.descricao" label="Descrição" rows="2" /><v-text-field v-model="item.unidade" label="Unidade" /><v-btn type="submit" color="primary" :loading="item.processing">Adicionar item</v-btn></v-form></v-card></v-col>
        <v-col cols="12" md="7"><v-card border flat rounded="lg"><v-card-title>Itens do catálogo</v-card-title><v-list v-if="itens.length"><v-list-item v-for="registro in itens" :key="registro.id" :title="registro.nome" :subtitle="`${registro.categorias?.map((categoria) => categoria.nome).join(', ') || 'Sem categoria'} · ${registro.unidade}`"><template #append><v-btn icon="mdi-delete-outline" variant="text" color="error" @click="remove(`/app/catalogo/itens/${registro.id}`)" /></template></v-list-item></v-list><v-card-text v-else class="text-medium-emphasis">Nenhum item cadastrado.</v-card-text></v-card></v-col>
    </v-row>
    <v-row class="mt-2">
        <v-col cols="12" md="5"><v-card border flat rounded="lg" class="pa-5"><h2 class="text-h6 mb-4">Novo adicional</h2><v-form @submit.prevent="submit(adicional, '/app/catalogo/adicionais')"><v-text-field v-model="adicional.nome" label="Nome" :error-messages="adicional.errors.nome" /><v-select v-model="adicional.tipo_cobranca" label="Tipo de cobrança" :items="[{title:'Por pessoa',value:'por_pessoa'},{title:'Por evento',value:'por_evento'},{title:'Por unidade',value:'por_unidade'}]" /><v-text-field v-model="adicional.valor_unitario" label="Valor unitário" type="number" min="0" step="0.01" prefix="R$" /><v-text-field v-model="adicional.min_quantidade" label="Quantidade mínima" type="number" min="1" /><v-btn type="submit" color="primary" :loading="adicional.processing">Adicionar adicional</v-btn></v-form></v-card></v-col>
        <v-col cols="12" md="7"><v-card border flat rounded="lg"><v-card-title>Adicionais cadastrados</v-card-title><v-list v-if="adicionais.length"><v-list-item v-for="registro in adicionais" :key="registro.id" :title="registro.nome" :subtitle="`${registro.tipo_cobranca} · R$ ${registro.valor_unitario}`"><template #append><v-btn icon="mdi-delete-outline" variant="text" color="error" @click="remove(`/app/catalogo/adicionais/${registro.id}`)" /></template></v-list-item></v-list><v-card-text v-else class="text-medium-emphasis">Nenhum adicional cadastrado.</v-card-text></v-card></v-col>
    </v-row>
</template>
