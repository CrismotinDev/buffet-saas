<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ pacotes: { type: Array, required: true }, empresaSlug: { type: String, required: true } });

const pacoteSelecionado = ref(null);
const modalAberto = ref(false);

const abrirPratos = (pacote) => {
    pacoteSelecionado.value = pacote;
    modalAberto.value = true;
};

const toggle = (id) => router.post(`/app/pacotes/${id}/alternar`);
const editar = (id) => { window.location.href = `/app/pacotes/${id}/editar`; };
const excluir = (id) => { if (confirm('Deseja excluir este pacote? Esta ação não pode ser desfeita.')) router.delete(`/app/pacotes/${id}`); };
const compartilhar = async () => { const link = `${window.location.origin}/e/${props.empresaSlug}`; await navigator.clipboard.writeText(link); window.alert('Link do cardápio copiado.'); };
</script>

<template>
    <Head title="Pacotes" />

    <div class="packages-page-header d-flex align-center justify-space-between mb-3">
        <div class="d-flex align-center ga-3">
            <div class="page-title-icon"><v-icon icon="mdi-package-variant-closed" size="19" /></div>
            <div>
                <div class="text-caption text-medium-emphasis font-weight-medium">Catálogo comercial</div>
                <h1 class="text-h4 font-weight-bold lh-tight">Pacotes</h1>
            </div>
        </div>
        <v-btn href="/app/pacotes/criar" color="primary" size="small" height="36" rounded="lg" class="text-none px-3" prepend-icon="mdi-plus">Novo pacote</v-btn>
    </div>

    <v-row v-if="pacotes.length" class="package-grid">
        <v-col v-for="pacote in pacotes" :key="pacote.id" cols="12">
            <v-card border flat rounded="xl" class="package-card h-100 d-flex flex-column">
                <div class="package-card__accent" />
                <div class="package-card__actions">
                    <v-tooltip text="Copiar link do cardápio" location="top"><template #activator="{ props: tooltipProps }"><v-btn v-bind="tooltipProps" icon="mdi-link-variant" variant="text" size="small" color="primary" aria-label="Copiar link do cardápio" @click="compartilhar" /></template></v-tooltip>
                    <v-tooltip text="Ver composição" location="top"><template #activator="{ props: tooltipProps }"><v-btn v-bind="tooltipProps" icon="mdi-eye-outline" variant="text" size="small" color="primary" aria-label="Ver composição do pacote" @click="abrirPratos(pacote)" /></template></v-tooltip>
                    <v-tooltip text="Editar pacote" location="top"><template #activator="{ props: tooltipProps }"><v-btn v-bind="tooltipProps" icon="mdi-pencil-outline" variant="text" size="small" color="primary" aria-label="Editar pacote" @click="editar(pacote.id)" /></template></v-tooltip>
                    <v-tooltip :text="pacote.ativo ? 'Desativar pacote' : 'Ativar pacote'" location="top"><template #activator="{ props: tooltipProps }"><v-btn v-bind="tooltipProps" :icon="pacote.ativo ? 'mdi-pause-circle-outline' : 'mdi-play-circle-outline'" variant="text" size="small" color="medium-emphasis" :aria-label="pacote.ativo ? 'Desativar pacote' : 'Ativar pacote'" @click="toggle(pacote.id)" /></template></v-tooltip>
                    <v-tooltip text="Excluir pacote" location="top"><template #activator="{ props: tooltipProps }"><v-btn v-bind="tooltipProps" icon="mdi-delete-outline" variant="text" size="small" color="error" aria-label="Excluir pacote" @click="excluir(pacote.id)" /></template></v-tooltip>
                </div>
                <v-card-item class="pa-6 pb-3 pe-12">
                    <template #prepend><v-avatar color="primary" variant="tonal" size="48"><v-icon icon="mdi-silverware-fork-knife" /></v-avatar></template>
                    <v-card-title class="px-0 pt-4 text-h6 font-weight-bold">{{ pacote.nome }}</v-card-title>
                    <v-card-subtitle class="px-0">{{ pacote.tipo_preco === 'por_pessoa' ? 'Preço por pessoa' : 'Preço por evento' }}</v-card-subtitle>
                    <v-chip class="mt-2" :color="pacote.ativo ? 'success' : 'grey'" variant="tonal" size="small" :prepend-icon="pacote.ativo ? 'mdi-check-circle-outline' : 'mdi-pause-circle-outline'">{{ pacote.ativo ? 'Ativo' : 'Inativo' }}</v-chip>
                </v-card-item>
                <v-card-text class="px-6 pt-2 flex-grow-1">
                    <div class="text-caption text-medium-emphasis">A partir de</div>
                    <div class="d-flex align-baseline ga-2 mt-1"><span class="text-h4 font-weight-bold text-grey-darken-4">R$ {{ pacote.preco_base }}</span><span v-if="pacote.tipo_preco === 'por_pessoa'" class="text-body-2 text-medium-emphasis">/ pessoa</span></div>
                    <v-divider class="my-5" />
                    <div class="d-flex flex-wrap ga-2"><v-chip size="small" variant="outlined" prepend-icon="mdi-account-group-outline">{{ pacote.min_convidados }}{{ pacote.max_convidados ? `–${pacote.max_convidados}` : '+' }} convidados</v-chip><v-chip size="small" variant="outlined" prepend-icon="mdi-food-outline">{{ pacote.itens_count }} pratos</v-chip></div>
                </v-card-text>
            </v-card>
        </v-col>
    </v-row>

    <v-card v-else border flat rounded="xl" class="pa-12 text-center">
        <v-avatar color="primary" variant="tonal" size="72"><v-icon icon="mdi-package-variant-closed" size="36" /></v-avatar>
        <div class="text-h6 font-weight-bold mt-5">Nenhum pacote criado</div>
        <div class="text-body-2 text-medium-emphasis mt-2">Monte seu primeiro pacote comercial para começar.</div>
        <v-btn href="/app/pacotes/criar" color="primary" rounded="lg" class="mt-6">Criar pacote</v-btn>
    </v-card>

    <v-dialog v-model="modalAberto" max-width="1080" scrollable>
        <v-card v-if="pacoteSelecionado" rounded="xl" class="composition-modal">
            <div class="composition-modal__header pa-5 pa-md-7">
                <div class="d-flex align-start ga-4">
                    <v-avatar color="white" variant="flat" size="52"><v-icon icon="mdi-silverware-fork-knife" color="primary" size="28" /></v-avatar>
                    <div class="flex-grow-1 min-width-0"><div class="text-caption text-white opacity-80 text-uppercase font-weight-bold">Composição do pacote</div><div class="text-h5 text-white font-weight-bold text-truncate">{{ pacoteSelecionado.nome }}</div><div class="text-body-2 text-white opacity-80 mt-1">Confira todos os pratos incluídos e as regras de escolha.</div></div>
                    <v-btn icon="mdi-close" variant="text" color="white" aria-label="Fechar" @click="modalAberto = false" />
                </div>
                <div class="modal-summary mt-5">
                    <div><div class="modal-summary__label">Preço base</div><div class="modal-summary__value">R$ {{ pacoteSelecionado.preco_base }} <span v-if="pacoteSelecionado.tipo_preco === 'por_pessoa'">/ pessoa</span></div></div>
                    <div><div class="modal-summary__label">Convidados</div><div class="modal-summary__value">{{ pacoteSelecionado.min_convidados }}{{ pacoteSelecionado.max_convidados ? `–${pacoteSelecionado.max_convidados}` : '+' }}</div></div>
                    <div><div class="modal-summary__label">Categorias</div><div class="modal-summary__value">{{ pacoteSelecionado.categorias.length }}</div></div>
                    <div><div class="modal-summary__label">Pratos</div><div class="modal-summary__value">{{ pacoteSelecionado.itens_count }}</div></div>
                </div>
            </div>
            <v-card-text class="pa-5 pa-md-7 bg-grey-lighten-5">
                <v-row>
                    <v-col v-for="categoria in pacoteSelecionado.categorias" :key="categoria.id" cols="12" :md="pacoteSelecionado.categorias.length > 1 ? 6 : 12">
                        <v-card border flat rounded="lg" class="category-card h-100">
                            <div class="category-card__header pa-4">
                                <div class="d-flex align-center ga-3"><v-avatar color="primary" variant="tonal" size="40"><v-icon icon="mdi-food-outline" /></v-avatar><div class="min-width-0"><div class="text-subtitle-1 font-weight-bold text-truncate">{{ categoria.nome }}</div><div class="text-caption text-medium-emphasis">{{ categoria.obrigatoria ? 'Escolha obrigatória' : 'Escolha opcional' }} · {{ categoria.min_escolhas }}–{{ categoria.max_escolhas }}</div></div><v-spacer /><v-chip size="small" variant="outlined">{{ categoria.itens.length }}</v-chip></div>
                            </div>
                            <v-divider />
                            <div v-if="categoria.itens.length" class="items-grid pa-3">
                                <div v-for="item in categoria.itens" :key="item.id" class="item-row"><v-icon icon="mdi-check-circle" color="success" size="18" /><div class="min-width-0"><div class="text-body-2 font-weight-medium text-truncate">{{ item.nome }}</div><div class="text-caption text-medium-emphasis text-truncate">{{ item.descricao || item.unidade }}</div></div></div>
                            </div>
                            <div v-else class="pa-5 text-body-2 text-medium-emphasis">Nenhum prato vinculado.</div>
                        </v-card>
                    </v-col>
                </v-row>
            </v-card-text>
            <v-card-actions class="pa-4 pa-md-5"><v-spacer /><v-btn color="primary" variant="flat" rounded="lg" @click="modalAberto = false">Fechar</v-btn></v-card-actions>
        </v-card>
    </v-dialog>
</template>

<style scoped>
.package-card { position: relative; overflow: hidden; transition: transform 180ms ease, box-shadow 180ms ease; }
.package-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin: 0; }
.package-grid > .v-col { width: auto; max-width: none; flex: none; padding: 0; }
.packages-page-header { min-height: 44px; }
.page-title-icon { width: 36px; height: 36px; display: grid; place-items: center; color: rgb(var(--v-theme-primary)); background: rgba(var(--v-theme-primary), .10); border: 1px solid rgba(var(--v-theme-primary), .16); border-radius: 10px; }
.package-card__actions { position: absolute; top: 14px; right: 14px; z-index: 2; display: flex; gap: 2px; }
.package-card:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(30, 41, 59, .10) !important; }
.package-card__accent { height: 4px; background: linear-gradient(90deg, rgb(var(--v-theme-primary)), #d97706); }
.composition-modal__header { background: linear-gradient(135deg, #7f1d1d 0%, #991b1b 55%, #b45309 100%); }
.modal-summary { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 12px; }
.modal-summary > div { border-top: 1px solid rgba(255,255,255,.25); padding-top: 10px; }
.modal-summary__label { color: rgba(255,255,255,.75); font-size: .75rem; }
.modal-summary__value { color: white; font-size: 1.1rem; font-weight: 700; margin-top: 3px; }
.modal-summary__value span { font-size: .75rem; font-weight: 400; }
.category-card__header { min-height: 80px; }
.items-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); gap: 6px; }
.item-row { min-height: 48px; display: flex; align-items: center; gap: 9px; padding: 7px 9px; border-radius: 8px; }
.item-row:hover { background: rgba(var(--v-theme-primary), .05); }
.modal-stat { border-top: 1px solid rgba(255,255,255,.25); padding-top: 12px; }
.category-card { transition: box-shadow 180ms ease; }
.category-card:hover { box-shadow: 0 8px 20px rgba(30, 41, 59, .08) !important; }
@media (max-width: 600px) {
    .package-grid { grid-template-columns: 1fr; }
    .modal-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .items-grid { grid-template-columns: 1fr; }
}
@media (min-width: 601px) and (max-width: 900px) {
    .package-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .items-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
}
@media (min-width: 901px) and (max-width: 1200px) {
    .package-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); }
}
</style>
