<script setup>
import { computed, ref } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ categorias: Array, itens: Array, adicionais: Array });

const tab = ref('itens');
const busca = ref('');
const itemDialog = ref(false);
const categoriaDialog = ref(false);
const adicionalDialog = ref(false);
const editandoItem = ref(null);
const editandoCategoria = ref(null);
const editandoAdicional = ref(null);

const itemForm = useForm({ categoria_ids: [], nome: '', descricao: '', unidade: 'unidade', tipo_item: 'comida', preco_unitario: null, referencia_consumo: '' });
const categoriaForm = useForm({ nome: '', ordem: 0 });
const adicionalForm = useForm({ nome: '', descricao: '', tipo_cobranca: 'por_pessoa', valor_unitario: 0, min_quantidade: 1, max_quantidade: null });

const itensFiltrados = computed(() => {
    const termo = busca.value.trim().toLowerCase();
    if (!termo) return props.itens;
    return props.itens.filter((item) => item.nome.toLowerCase().includes(termo) || item.categorias?.some((categoria) => categoria.nome.toLowerCase().includes(termo)));
});

function abrirNovoItem() {
    editandoItem.value = null;
    itemForm.reset();
    itemForm.categoria_ids = [];
    itemForm.unidade = 'unidade';
    itemForm.tipo_item = 'comida';
    itemForm.preco_unitario = null;
    itemForm.referencia_consumo = '';
    itemForm.clearErrors();
    itemDialog.value = true;
}

function abrirEdicaoItem(item) {
    editandoItem.value = item;
    itemForm.categoria_ids = item.categorias?.map((categoria) => categoria.id) ?? [];
    itemForm.nome = item.nome;
    itemForm.descricao = item.descricao ?? '';
    itemForm.unidade = item.unidade;
    itemForm.tipo_item = item.tipo_item ?? 'comida';
    itemForm.preco_unitario = item.preco_unitario ?? null;
    itemForm.referencia_consumo = item.referencia_consumo ?? '';
    itemForm.clearErrors();
    itemDialog.value = true;
}

function salvarItem() {
    const options = { onSuccess: () => { itemDialog.value = false; itemForm.reset(); } };
    editandoItem.value ? itemForm.put(`/app/catalogo/itens/${editandoItem.value.id}`, options) : itemForm.post('/app/catalogo/itens', options);
}

function abrirNovaCategoria() {
    editandoCategoria.value = null;
    categoriaForm.reset();
    categoriaForm.ordem = 0;
    categoriaForm.clearErrors();
    categoriaDialog.value = true;
}

function abrirEdicaoCategoria(categoria) {
    editandoCategoria.value = categoria;
    categoriaForm.nome = categoria.nome;
    categoriaForm.ordem = categoria.ordem;
    categoriaForm.clearErrors();
    categoriaDialog.value = true;
}

function salvarCategoria() {
    const options = { onSuccess: () => { categoriaDialog.value = false; categoriaForm.reset(); } };
    editandoCategoria.value ? categoriaForm.put(`/app/catalogo/categorias/${editandoCategoria.value.id}`, options) : categoriaForm.post('/app/catalogo/categorias', options);
}

function abrirNovoAdicional() {
    editandoAdicional.value = null;
    adicionalForm.reset();
    adicionalForm.tipo_cobranca = 'por_pessoa';
    adicionalForm.min_quantidade = 1;
    adicionalForm.clearErrors();
    adicionalDialog.value = true;
}

function abrirEdicaoAdicional(adicional) {
    editandoAdicional.value = adicional;
    Object.assign(adicionalForm, { nome: adicional.nome, descricao: adicional.descricao ?? '', tipo_cobranca: adicional.tipo_cobranca, valor_unitario: adicional.valor_unitario, min_quantidade: adicional.min_quantidade, max_quantidade: adicional.max_quantidade });
    adicionalForm.clearErrors();
    adicionalDialog.value = true;
}

function salvarAdicional() {
    const options = { onSuccess: () => { adicionalDialog.value = false; adicionalForm.reset(); } };
    editandoAdicional.value ? adicionalForm.put(`/app/catalogo/adicionais/${editandoAdicional.value.id}`, options) : adicionalForm.post('/app/catalogo/adicionais', options);
}

function remover(url) {
    if (confirm('Deseja remover este registro?')) router.delete(url);
}
</script>

<template>
    <Head title="Catálogo" />

    <div class="catalog-page-header d-flex align-center justify-space-between mb-3">
        <div class="d-flex align-center ga-3">
            <div class="page-title-icon"><v-icon icon="mdi-food-outline" size="19" /></div>
            <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Gestão da operação</div>
                <h1 class="text-h4 font-weight-bold lh-tight">Catálogo</h1>
            </div>
        </div>
        <v-btn v-if="tab === 'itens'" color="primary" size="small" height="36" rounded="lg" class="text-none px-3" prepend-icon="mdi-plus" @click="abrirNovoItem">Novo item</v-btn>
        <v-btn v-else-if="tab === 'categorias'" color="primary" size="small" height="36" rounded="lg" class="text-none px-3" prepend-icon="mdi-plus" @click="abrirNovaCategoria">Nova categoria</v-btn>
        <v-btn v-else color="primary" size="small" height="36" rounded="lg" class="text-none px-3" prepend-icon="mdi-plus" @click="abrirNovoAdicional">Novo adicional</v-btn>
    </div>

    <v-row class="mb-2"><v-col cols="12" sm="4"><v-card border flat rounded="xl" class="pa-5"><div class="d-flex align-center ga-4"><v-avatar color="primary" variant="tonal" size="44"><v-icon icon="mdi-food-outline" /></v-avatar><div><div class="text-h5 font-weight-bold">{{ itens.length }}</div><div class="text-body-2 text-medium-emphasis">Itens cadastrados</div></div></div></v-card></v-col><v-col cols="12" sm="4"><v-card border flat rounded="xl" class="pa-5"><div class="d-flex align-center ga-4"><v-avatar color="orange" variant="tonal" size="44"><v-icon icon="mdi-shape-outline" /></v-avatar><div><div class="text-h5 font-weight-bold">{{ categorias.length }}</div><div class="text-body-2 text-medium-emphasis">Categorias</div></div></div></v-card></v-col><v-col cols="12" sm="4"><v-card border flat rounded="xl" class="pa-5"><div class="d-flex align-center ga-4"><v-avatar color="green" variant="tonal" size="44"><v-icon icon="mdi-plus-circle-outline" /></v-avatar><div><div class="text-h5 font-weight-bold">{{ adicionais.length }}</div><div class="text-body-2 text-medium-emphasis">Adicionais</div></div></div></v-card></v-col></v-row>

    <v-card border flat rounded="xl" class="mt-4">
        <v-tabs v-model="tab" color="primary" class="px-4" height="64"><v-tab value="itens" prepend-icon="mdi-food-outline">Itens</v-tab><v-tab value="categorias" prepend-icon="mdi-shape-outline">Categorias</v-tab><v-tab value="adicionais" prepend-icon="mdi-plus-circle-outline">Adicionais</v-tab></v-tabs>
        <v-divider />

        <v-window v-model="tab">
            <v-window-item value="itens">
                <div class="d-flex flex-wrap align-center justify-space-between pa-5 ga-3"><div><div class="text-h6 font-weight-bold">Itens do catálogo</div><div class="text-body-2 text-medium-emphasis mt-1">Pratos e serviços oferecidos pelo estabelecimento.</div></div><v-text-field v-model="busca" label="Buscar item ou categoria" prepend-inner-icon="mdi-magnify" density="compact" variant="outlined" hide-details clearable style="max-width: 300px" /></div>
                <v-divider />
                <v-table v-if="itensFiltrados.length" hover><thead><tr><th class="text-left">Item</th><th class="text-left">Categorias</th><th class="text-left">Unidade</th><th class="text-right">Ações</th></tr></thead><tbody><tr v-for="item in itensFiltrados" :key="item.id"><td><div class="font-weight-medium">{{ item.nome }}</div><div v-if="item.descricao" class="text-caption text-medium-emphasis text-truncate" style="max-width: 280px">{{ item.descricao }}</div></td><td><div class="d-flex flex-wrap ga-1"><v-chip v-for="categoria in item.categorias" :key="categoria.id" size="x-small" color="primary" variant="tonal">{{ categoria.nome }}</v-chip></div></td><td class="text-medium-emphasis">{{ item.unidade }}</td><td class="text-right"><v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Editar item" @click="abrirEdicaoItem(item)" /><v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" aria-label="Excluir item" @click="remover(`/app/catalogo/itens/${item.id}`)" /></td></tr></tbody></v-table><div v-else class="pa-12 text-center"><v-avatar color="primary" variant="tonal" size="64"><v-icon icon="mdi-food-off-outline" size="32" /></v-avatar><div class="text-h6 font-weight-bold mt-4">Nenhum item encontrado</div><div class="text-body-2 text-medium-emphasis mt-2">Cadastre o primeiro item do seu catálogo.</div><v-btn color="primary" rounded="lg" class="mt-5" @click="abrirNovoItem">Cadastrar item</v-btn></div>
            </v-window-item>
            <v-window-item value="categorias"><v-list v-if="categorias.length" lines="two" class="py-2"><v-list-item v-for="categoria in categorias" :key="categoria.id" :title="categoria.nome" :subtitle="`${categoria.itens_count} item(ns) · Ordem ${categoria.ordem}`" class="px-5"><template #prepend><v-avatar color="orange" variant="tonal"><v-icon icon="mdi-shape-outline" /></v-avatar></template><template #append><v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Editar categoria" @click="abrirEdicaoCategoria(categoria)" /><v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" aria-label="Excluir categoria" @click="remover(`/app/catalogo/categorias/${categoria.id}`)" /></template></v-list-item></v-list><div v-else class="pa-12 text-center text-medium-emphasis">Nenhuma categoria cadastrada.</div></v-window-item>
            <v-window-item value="adicionais"><v-table v-if="adicionais.length" hover><thead><tr><th class="text-left">Adicional</th><th class="text-left">Cobrança</th><th class="text-left">Valor</th><th class="text-right">Ações</th></tr></thead><tbody><tr v-for="adicional in adicionais" :key="adicional.id"><td><div class="font-weight-medium">{{ adicional.nome }}</div><div v-if="adicional.descricao" class="text-caption text-medium-emphasis">{{ adicional.descricao }}</div></td><td><v-chip size="small" variant="tonal">{{ adicional.tipo_cobranca.replace('_', ' ') }}</v-chip></td><td class="font-weight-medium">R$ {{ adicional.valor_unitario }}</td><td class="text-right"><v-btn icon="mdi-pencil-outline" variant="text" size="small" aria-label="Editar adicional" @click="abrirEdicaoAdicional(adicional)" /><v-btn icon="mdi-delete-outline" variant="text" size="small" color="error" aria-label="Excluir adicional" @click="remover(`/app/catalogo/adicionais/${adicional.id}`)" /></td></tr></tbody></v-table><div v-else class="pa-12 text-center text-medium-emphasis">Nenhum adicional cadastrado.</div></v-window-item>
        </v-window>
    </v-card>

    <v-dialog v-model="itemDialog" max-width="620"><v-card rounded="xl"><v-card-title class="pa-6 pb-2">{{ editandoItem ? 'Editar item' : 'Novo item' }}</v-card-title><v-card-subtitle class="px-6">Associe o item a uma ou mais categorias.</v-card-subtitle><v-card-text class="pa-6"><v-form @submit.prevent="salvarItem"><v-text-field v-model="itemForm.nome" label="Nome do item" :error-messages="itemForm.errors.nome" autofocus /><v-select v-model="itemForm.tipo_item" label="Tipo do item" :items="[{ title: 'Comida / prato', value: 'comida' }, { title: 'Bebida', value: 'bebida' }]" :error-messages="itemForm.errors.tipo_item" /><v-text-field v-if="itemForm.tipo_item === 'bebida'" v-model="itemForm.preco_unitario" label="Preço unitário" type="number" min="0" step="0.01" prefix="R$" :error-messages="itemForm.errors.preco_unitario" /><v-text-field v-if="itemForm.tipo_item === 'bebida'" v-model="itemForm.referencia_consumo" label="Referência de consumo por pessoa" placeholder="Ex.: 600 ml por pessoa ou 3 garrafinhas de 300 ml por pessoa" hint="Exibida para orientar o cliente no cardápio público." persistent-hint :error-messages="itemForm.errors.referencia_consumo" /><v-select v-model="itemForm.categoria_ids" label="Categorias" :items="categorias" item-title="nome" item-value="id" multiple chips closable-chips :error-messages="itemForm.errors.categoria_ids" hint="O mesmo item pode pertencer a várias categorias." persistent-hint /><v-textarea v-model="itemForm.descricao" label="Descrição" rows="3" :error-messages="itemForm.errors.descricao" /><v-text-field v-model="itemForm.unidade" label="Unidade de apresentação" :error-messages="itemForm.errors.unidade" /></v-form></v-card-text><v-divider /><v-card-actions class="pa-5"><v-btn variant="text" @click="itemDialog = false">Cancelar</v-btn><v-spacer /><v-btn color="primary" rounded="lg" :loading="itemForm.processing" @click="salvarItem">{{ editandoItem ? 'Salvar alterações' : 'Cadastrar item' }}</v-btn></v-card-actions></v-card></v-dialog>
    <v-dialog v-model="categoriaDialog" max-width="500"><v-card rounded="xl"><v-card-title class="pa-6 pb-2">{{ editandoCategoria ? 'Editar categoria' : 'Nova categoria' }}</v-card-title><v-card-text class="pa-6"><v-text-field v-model="categoriaForm.nome" label="Nome da categoria" :error-messages="categoriaForm.errors.nome" autofocus /><v-text-field v-model="categoriaForm.ordem" label="Ordem de exibição" type="number" min="0" :error-messages="categoriaForm.errors.ordem" /></v-card-text><v-divider /><v-card-actions class="pa-5"><v-btn variant="text" @click="categoriaDialog = false">Cancelar</v-btn><v-spacer /><v-btn color="primary" rounded="lg" :loading="categoriaForm.processing" @click="salvarCategoria">Salvar categoria</v-btn></v-card-actions></v-card></v-dialog>
    <v-dialog v-model="adicionalDialog" max-width="620"><v-card rounded="xl"><v-card-title class="pa-6 pb-2">{{ editandoAdicional ? 'Editar adicional' : 'Novo adicional' }}</v-card-title><v-card-text class="pa-6"><v-text-field v-model="adicionalForm.nome" label="Nome do adicional" :error-messages="adicionalForm.errors.nome" autofocus /><v-textarea v-model="adicionalForm.descricao" label="Descrição" rows="2" /><v-select v-model="adicionalForm.tipo_cobranca" label="Tipo de cobrança" :items="[{title:'Por pessoa',value:'por_pessoa'},{title:'Por evento',value:'por_evento'},{title:'Por unidade',value:'por_unidade'}]" /><v-text-field v-model="adicionalForm.valor_unitario" label="Valor unitário" type="number" min="0" step="0.01" prefix="R$" :error-messages="adicionalForm.errors.valor_unitario" /><v-row><v-col cols="6"><v-text-field v-model="adicionalForm.min_quantidade" label="Mínimo" type="number" min="1" /></v-col><v-col cols="6"><v-text-field v-model="adicionalForm.max_quantidade" label="Máximo" type="number" min="1" /></v-col></v-row></v-card-text><v-divider /><v-card-actions class="pa-5"><v-btn variant="text" @click="adicionalDialog = false">Cancelar</v-btn><v-spacer /><v-btn color="primary" rounded="lg" :loading="adicionalForm.processing" @click="salvarAdicional">Salvar adicional</v-btn></v-card-actions></v-card></v-dialog>
</template>

<style scoped>
.catalog-page-header { min-height: 44px; }
.page-title-icon { width: 36px; height: 36px; display: grid; place-items: center; color: rgb(var(--v-theme-primary)); background: rgba(var(--v-theme-primary), .10); border: 1px solid rgba(var(--v-theme-primary), .16); border-radius: 10px; }
:deep(.v-table th) { color: rgb(var(--v-theme-on-surface)); font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
:deep(.v-table td) { height: 68px; }
</style>
