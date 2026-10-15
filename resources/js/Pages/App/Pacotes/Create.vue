<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';
defineOptions({ layout: AppLayout });
const props = defineProps({ categorias: Array });
const form = useForm({ nome: '', descricao: '', tipo_preco: 'por_pessoa', preco_base: 0, min_convidados: 1, max_convidados: null, publicado: false, categoria_ids: [], item_ids: [] });
const submit = () => form.post('/app/pacotes');
</script>
<template>
    <Head title="Novo pacote" />
    <div class="mb-6"><h1 class="text-h4 font-weight-bold">Novo pacote</h1><p class="text-body-1 text-medium-emphasis mt-1">Defina a oferta inicial e os itens permitidos.</p></div>
    <v-form @submit.prevent="submit"><v-card border flat rounded="lg"><v-card-text class="pa-6"><v-row><v-col cols="12" md="6"><v-text-field v-model="form.nome" label="Nome do pacote" :error-messages="form.errors.nome" /></v-col><v-col cols="12" md="6"><v-select v-model="form.tipo_preco" label="Tipo de preço" :items="[{title:'Por pessoa',value:'por_pessoa'},{title:'Preço fixo',value:'fixo'}]" /></v-col><v-col cols="12"><v-textarea v-model="form.descricao" label="Descrição" rows="2" /></v-col><v-col cols="12" md="4"><v-text-field v-model="form.preco_base" label="Preço base" type="number" min="0" step="0.01" prefix="R$" /></v-col><v-col cols="6" md="4"><v-text-field v-model="form.min_convidados" label="Mínimo de convidados" type="number" min="1" /></v-col><v-col cols="6" md="4"><v-text-field v-model="form.max_convidados" label="Máximo de convidados" type="number" min="1" /></v-col></v-row><v-divider class="my-5" /><div class="text-subtitle-1 font-weight-bold mb-2">Categorias participantes</div><v-checkbox v-for="cat in props.categorias" :key="cat.id" v-model="form.categoria_ids" :value="cat.id" :label="cat.nome" hide-details density="compact" /><div class="text-subtitle-1 font-weight-bold mt-5 mb-2">Itens permitidos</div><v-checkbox v-for="item in props.categorias.flatMap((cat) => cat.itens.map((registro) => ({ ...registro, categoria: cat.nome })))" :key="item.id" v-model="form.item_ids" :value="item.id" :label="`${item.nome} · ${item.categoria}`" hide-details density="compact" /><v-checkbox v-model="form.publicado" label="Publicar imediatamente" class="mt-4" /></v-card-text><v-divider /><v-card-actions class="pa-6"><v-btn href="/app/pacotes" variant="text">Cancelar</v-btn><v-spacer /><v-btn type="submit" color="primary" :loading="form.processing">Salvar pacote</v-btn></v-card-actions></v-card></v-form>
</template>
