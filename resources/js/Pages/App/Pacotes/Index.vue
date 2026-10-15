<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
defineProps({ pacotes: { type: Array, required: true } });

const pacoteSelecionado = ref(null);
const modalAberto = ref(false);

const abrirPratos = (pacote) => {
    pacoteSelecionado.value = pacote;
    modalAberto.value = true;
};

const toggle = (id) => router.post(`/app/pacotes/${id}/alternar`);
const editar = (id) => { window.location.href = `/app/pacotes/${id}/editar`; };
const excluir = (id) => { if (confirm('Deseja excluir este pacote? Esta ação não pode ser desfeita.')) router.delete(`/app/pacotes/${id}`); };
</script>

<template>
    <Head title="Pacotes" />

    <div class="d-flex flex-wrap align-center justify-space-between mb-8 ga-4">
        <div>
            <div class="text-overline text-primary font-weight-bold">Cardápio comercial</div>
            <h1 class="text-h4 font-weight-bold mt-1">Pacotes</h1>
            <p class="text-body-1 text-medium-emphasis mt-2">Crie ofertas claras e prontas para seus clientes escolherem.</p>
        </div>
        <v-btn href="/app/pacotes/criar" color="primary" size="large" rounded="lg" prepend-icon="mdi-plus">Novo pacote</v-btn>
    </div>

    <v-row v-if="pacotes.length">
        <v-col v-for="pacote in pacotes" :key="pacote.id" cols="12" md="6" lg="4">
            <v-card border flat rounded="xl" class="package-card h-100 d-flex flex-column">
                <div class="package-card__accent" />
                <v-card-item class="pa-6 pb-3">
                    <template #prepend><v-avatar color="primary" variant="tonal" size="48"><v-icon icon="mdi-silverware-fork-knife" /></v-avatar></template>
                    <template #append><v-chip :color="pacote.ativo ? 'success' : 'grey'" variant="tonal" size="small" :prepend-icon="pacote.ativo ? 'mdi-check-circle-outline' : 'mdi-pause-circle-outline'">{{ pacote.ativo ? 'Ativo' : 'Inativo' }}</v-chip></template>
                    <v-card-title class="px-0 pt-4 text-h6 font-weight-bold">{{ pacote.nome }}</v-card-title>
                    <v-card-subtitle class="px-0">{{ pacote.tipo_preco === 'por_pessoa' ? 'Preço por pessoa' : 'Preço por evento' }}</v-card-subtitle>
                </v-card-item>
                <v-card-text class="px-6 pt-2 flex-grow-1">
                    <div class="text-caption text-medium-emphasis">A partir de</div>
                    <div class="d-flex align-baseline ga-2 mt-1"><span class="text-h4 font-weight-bold text-grey-darken-4">R$ {{ pacote.preco_base }}</span><span v-if="pacote.tipo_preco === 'por_pessoa'" class="text-body-2 text-medium-emphasis">/ pessoa</span></div>
                    <v-divider class="my-5" />
                    <div class="d-flex flex-wrap ga-2"><v-chip size="small" variant="outlined" prepend-icon="mdi-account-group-outline">{{ pacote.min_convidados }}{{ pacote.max_convidados ? `–${pacote.max_convidados}` : '+' }} convidados</v-chip><v-chip size="small" variant="outlined" prepend-icon="mdi-food-outline">{{ pacote.itens_count }} pratos</v-chip></div>
                </v-card-text>
                <v-card-actions class="pa-6 pt-2"><v-btn block color="primary" variant="tonal" size="large" rounded="lg" prepend-icon="mdi-eye-outline" @click="abrirPratos(pacote)">Ver composição do pacote</v-btn><v-menu location="bottom end"><template #activator="{ props: menuProps }"><v-btn v-bind="menuProps" icon="mdi-dots-vertical" variant="text" aria-label="Mais ações" /></template><v-list density="compact" min-width="180"><v-list-item prepend-icon="mdi-pencil-outline" title="Editar pacote" @click="editar(pacote.id)" /><v-list-item :prepend-icon="pacote.ativo ? 'mdi-pause-circle-outline' : 'mdi-play-circle-outline'" :title="pacote.ativo ? 'Desativar pacote' : 'Ativar pacote'" @click="toggle(pacote.id)" /><v-list-item prepend-icon="mdi-delete-outline" title="Excluir pacote" class="text-error" @click="excluir(pacote.id)" /></v-list></v-menu></v-card-actions>
            </v-card>
        </v-col>
    </v-row>

    <v-card v-else border flat rounded="xl" class="pa-12 text-center">
        <v-avatar color="primary" variant="tonal" size="72"><v-icon icon="mdi-package-variant-closed" size="36" /></v-avatar>
        <div class="text-h6 font-weight-bold mt-5">Nenhum pacote criado</div>
        <div class="text-body-2 text-medium-emphasis mt-2">Monte seu primeiro pacote comercial para começar.</div>
        <v-btn href="/app/pacotes/criar" color="primary" rounded="lg" class="mt-6">Criar pacote</v-btn>
    </v-card>

    <v-dialog v-model="modalAberto" max-width="900" scrollable>
        <v-card v-if="pacoteSelecionado" rounded="xl" class="composition-modal">
            <div class="composition-modal__header pa-6 pa-md-8">
                <div class="d-flex align-start ga-4">
                    <v-avatar color="white" variant="flat" size="56"><v-icon icon="mdi-silverware-fork-knife" color="primary" size="30" /></v-avatar>
                    <div class="flex-grow-1"><div class="text-overline text-white opacity-80">Composição do pacote</div><div class="text-h5 text-white font-weight-bold">{{ pacoteSelecionado.nome }}</div><div class="text-body-2 text-white opacity-80 mt-1">Confira os pratos e as regras de escolha.</div></div>
                    <v-btn icon="mdi-close" variant="text" color="white" aria-label="Fechar" @click="modalAberto = false" />
                </div>
                <v-row class="mt-5" density="comfortable"><v-col cols="12" sm="4"><div class="modal-stat"><div class="text-caption text-white opacity-80">Preço base</div><div class="text-h6 text-white font-weight-bold mt-1">R$ {{ pacoteSelecionado.preco_base }}<span v-if="pacoteSelecionado.tipo_preco === 'por_pessoa'" class="text-body-2 font-weight-regular"> / pessoa</span></div></div></v-col><v-col cols="12" sm="4"><div class="modal-stat"><div class="text-caption text-white opacity-80">Faixa de convidados</div><div class="text-h6 text-white font-weight-bold mt-1">{{ pacoteSelecionado.min_convidados }}{{ pacoteSelecionado.max_convidados ? ` a ${pacoteSelecionado.max_convidados}` : '+' }}</div></div></v-col><v-col cols="12" sm="4"><div class="modal-stat"><div class="text-caption text-white opacity-80">Categorias</div><div class="text-h6 text-white font-weight-bold mt-1">{{ pacoteSelecionado.categorias.length }}</div></div></v-col></v-row>
            </div>
            <v-card-text class="pa-6 pa-md-8 bg-grey-lighten-5">
                <div class="text-subtitle-1 font-weight-bold mb-4">Pratos disponíveis</div>
                <v-row><v-col v-for="categoria in pacoteSelecionado.categorias" :key="categoria.id" cols="12" md="6"><v-card border flat rounded="lg" class="category-card h-100"><v-card-item class="pa-5"><template #prepend><v-avatar color="primary" variant="tonal" size="40"><v-icon icon="mdi-food-outline" /></v-avatar></template><v-card-title class="text-subtitle-1 font-weight-bold">{{ categoria.nome }}</v-card-title><v-card-subtitle>{{ categoria.obrigatoria ? 'Escolha obrigatória' : 'Escolha opcional' }} · {{ categoria.min_escolhas }}–{{ categoria.max_escolhas }}</v-card-subtitle></v-card-item><v-divider /><v-list v-if="categoria.itens.length" bg-color="transparent" density="compact" class="py-2"><v-list-item v-for="item in categoria.itens" :key="item.id" :title="item.nome" :subtitle="item.descricao || item.unidade" class="px-5"><template #prepend><v-icon icon="mdi-check-circle" color="success" size="18" class="mr-3" /></template></v-list-item></v-list><v-card-text v-else class="text-body-2 text-medium-emphasis">Nenhum prato vinculado.</v-card-text></v-card></v-col></v-row>
            </v-card-text>
            <v-card-actions class="pa-5 pa-md-6"><v-spacer /><v-btn color="primary" variant="flat" rounded="lg" size="large" @click="modalAberto = false">Fechar</v-btn></v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>
.package-card { position: relative; overflow: hidden; transition: transform 180ms ease, box-shadow 180ms ease; }
.package-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(30, 41, 59, .10) !important; }
.package-card__accent { height: 4px; background: linear-gradient(90deg, rgb(var(--v-theme-primary)), #d97706); }
.composition-modal__header { background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 55%, #b45309 100%); }
.modal-stat { border-top: 1px solid rgba(255,255,255,.25); padding-top: 12px; }
.category-card { transition: box-shadow 180ms ease; }
.category-card:hover { box-shadow: 0 8px 20px rgba(30, 41, 59, .08) !important; }
</style>
