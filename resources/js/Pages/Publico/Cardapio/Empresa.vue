<script setup>
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ empresa: Object, pacotes: Array });
const pacoteAberto = ref(null);
const rotaPacote = (pacote) => `/e/${props.empresa.slug}/pacotes/${pacote.slug}`;
</script>

<template>
    <Head :title="`Cardápio · ${empresa.nome_fantasia}`" />
    <v-app>
        <v-main class="public-page">
            <v-container class="py-8 py-md-12" style="max-width: 1180px">
                <div class="public-brand mb-10"><v-avatar color="primary" variant="tonal" size="52"><v-icon icon="mdi-silverware-fork-knife" size="28" /></v-avatar><div><div class="text-overline text-primary font-weight-bold">Cardápio comercial</div><h1 class="text-h4 font-weight-bold">{{ empresa.nome_fantasia }}</h1><div class="text-body-1 text-medium-emphasis mt-1">Escolha a melhor opção para o seu evento.</div></div></div>
                <div class="d-flex align-center justify-space-between flex-wrap ga-4 mb-6"><div><div class="text-h5 font-weight-bold mb-1">Pacotes disponíveis</div><div class="text-body-1 text-medium-emphasis">Conheça nossas opções e personalize sua escolha.</div></div><v-btn color="primary" rounded="lg" prepend-icon="mdi-tune-variant" :href="`/e/${empresa.slug}/cardapio-personalizado`">Montar cardápio</v-btn></div>
                <v-row v-if="pacotes.length" class="public-package-grid"><v-col v-for="pacote in pacotes" :key="pacote.id" cols="12"><v-card class="public-package h-100 d-flex flex-column" border flat rounded="xl"><div class="public-package__accent" /><v-card-text class="pa-6 flex-grow-1"><div class="d-flex align-start justify-space-between ga-3"><div><div class="text-h6 font-weight-bold">{{ pacote.nome }}</div><div class="text-body-2 text-medium-emphasis mt-1">{{ pacote.tipo_preco === 'por_pessoa' ? 'Preço por pessoa' : 'Preço por evento' }}</div></div><v-chip size="small" color="success" variant="tonal">Disponível</v-chip></div><div class="text-caption text-medium-emphasis mt-8">A partir de</div><div class="text-h4 font-weight-bold mt-1">R$ {{ pacote.preco_base }} <span v-if="pacote.tipo_preco === 'por_pessoa'" class="text-body-2 font-weight-regular text-medium-emphasis">/ pessoa</span></div><div class="d-flex flex-wrap ga-2 mt-5"><v-chip size="small" variant="outlined" prepend-icon="mdi-account-group-outline">{{ pacote.min_convidados }}{{ pacote.max_convidados ? `–${pacote.max_convidados}` : '+' }} convidados</v-chip><v-chip size="small" variant="outlined" prepend-icon="mdi-food-outline">{{ pacote.itens_count }} pratos</v-chip></div></v-card-text><v-card-actions class="pa-4 pt-0 d-flex ga-2"><v-btn class="package-action" color="primary" rounded="lg" :href="rotaPacote(pacote)" prepend-icon="mdi-check-circle-outline">Selecionar</v-btn><v-btn class="package-action" color="primary" variant="text" rounded="lg" @click="pacoteAberto = pacote" prepend-icon="mdi-eye-outline">Composição</v-btn></v-card-actions></v-card></v-col></v-row>
                <v-card v-else border flat rounded="xl" class="pa-10 text-center"><v-icon icon="mdi-food-off-outline" size="48" color="primary" /><div class="text-h6 font-weight-bold mt-4">Nenhum pacote disponível</div><div class="text-body-2 text-medium-emphasis mt-2">A empresa ainda não publicou opções de cardápio.</div></v-card>
            </v-container>
        </v-main>
        <v-dialog v-model="pacoteAberto" max-width="620" scrollable><v-card v-if="pacoteAberto" rounded="xl"><v-card-item class="pa-5"><template #prepend><v-avatar color="primary" variant="tonal"><v-icon icon="mdi-silverware-fork-knife" /></v-avatar></template><v-card-title>{{ pacoteAberto.nome }}</v-card-title><v-card-subtitle>Itens incluídos no pacote</v-card-subtitle><template #append><v-btn icon="mdi-close" variant="text" aria-label="Fechar" @click="pacoteAberto = null" /></template></v-card-item><v-divider /><v-card-text class="pa-5"><div class="d-flex flex-wrap ga-2 mb-5"><v-chip size="small" variant="tonal" prepend-icon="mdi-cash">R$ {{ pacoteAberto.preco_base }}{{ pacoteAberto.tipo_preco === 'por_pessoa' ? ' / pessoa' : ' / evento' }}</v-chip><v-chip size="small" variant="tonal" prepend-icon="mdi-account-group-outline">{{ pacoteAberto.min_convidados }}{{ pacoteAberto.max_convidados ? `–${pacoteAberto.max_convidados}` : '+' }} convidados</v-chip></div><v-row dense><v-col v-for="item in pacoteAberto.itens" :key="item.id" cols="12" sm="6"><div class="included-item"><v-icon icon="mdi-check-circle" color="success" size="18" /> <span>{{ item.nome }}</span></div></v-col></v-row></v-card-text><v-card-actions class="pa-5 pt-0"><v-btn color="primary" rounded="lg" :href="rotaPacote(pacoteAberto)" prepend-icon="mdi-check-circle-outline">Selecionar pacote</v-btn><v-spacer /><v-btn color="primary" variant="text" rounded="lg" @click="pacoteAberto = null">Fechar</v-btn></v-card-actions></v-card></v-dialog>
    </v-app>
</template>

<style scoped>
.public-page { background: rgb(var(--v-theme-background)); min-height: 100vh; }
.public-package-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 18px; margin: 0; }
.public-package-grid > .v-col { width: auto; max-width: none; flex: none; padding: 0; }
.public-brand { display: flex; align-items: center; gap: 14px; }
.public-package { overflow: hidden; transition: transform 180ms ease, box-shadow 180ms ease; }
.public-package:hover { transform: translateY(-3px); box-shadow: 0 12px 28px rgba(30, 41, 59, .10) !important; }
.public-package__accent { height: 4px; background: linear-gradient(90deg, #7f1d1d, #d97706); }
.included-item { display: flex; align-items: center; gap: 9px; min-height: 34px; font-weight: 500; }
.package-action { flex: 1 1 0; min-width: 0; padding-inline: 8px !important; font-size: 12px !important; }
@media (max-width: 1200px) { .public-package-grid { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
@media (max-width: 900px) { .public-package-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
@media (max-width: 600px) { .public-package-grid { grid-template-columns: 1fr; } }
</style>
