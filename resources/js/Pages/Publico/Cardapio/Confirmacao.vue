<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ relatorio: { type: Object, required: true } });
const valorEstimado = computed(() => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(props.relatorio.preco_estimado)));
const valorBase = computed(() => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(props.relatorio.preco_base)));
const valorBebidas = computed(() => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(props.relatorio.bebidas_total || 0)));
const valorExibido = computed(() => props.relatorio.modo_cardapio === 'personalizado' ? (Number(props.relatorio.bebidas_total || 0) > 0 ? valorBebidas.value : 'A combinar') : valorEstimado.value);
</script>

<template>
    <Head title="Orçamento recebido" />
    <v-app>
        <v-main class="confirmation-page py-8 py-md-12">
            <v-container style="max-width: 900px">
                <v-card border flat rounded="xl" overflow="hidden">
                    <div class="confirmation-hero pa-6 pa-md-10 text-center">
                        <v-avatar color="white" size="68"><v-icon icon="mdi-check" color="success" size="38" /></v-avatar>
                        <h1 class="text-h4 font-weight-bold mt-5">Orçamento recebido!</h1>
                        <p class="text-body-1 mt-3">Obrigado, {{ relatorio.nome }}. A equipe da {{ relatorio.empresa }} analisará sua solicitação e entrará em contato.</p>
                        <v-chip class="mt-5" color="white" variant="flat">Protocolo: {{ relatorio.protocolo }}</v-chip>
                    </div>
                    <v-card-text class="pa-6 pa-md-10">
                        <div class="d-flex align-center justify-space-between flex-wrap ga-3 mb-6">
                            <div><div class="text-overline text-primary">Resumo do orçamento</div><div class="text-h5 font-weight-bold">{{ relatorio.pacote }}</div></div>
                            <div class="text-right"><div class="text-caption text-medium-emphasis">{{ relatorio.modo_cardapio === 'personalizado' && Number(relatorio.bebidas_total || 0) > 0 ? 'Subtotal das bebidas' : (relatorio.modo_cardapio === 'personalizado' ? 'Valor' : 'Valor estimado') }}</div><div class="text-h4 font-weight-bold text-primary">{{ valorExibido }}</div></div>
                        </div>
                        <v-row>
                            <v-col cols="12" sm="4"><div class="summary-label">Data do evento</div><div class="summary-value">{{ relatorio.data_evento }}</div></v-col>
                            <v-col cols="12" sm="4"><div class="summary-label">Convidados</div><div class="summary-value">{{ relatorio.convidados }} pessoas</div></v-col>
                            <v-col cols="12" sm="4"><div class="summary-label">Local</div><div class="summary-value">{{ relatorio.tipo_local === 'externo' ? relatorio.endereco_local || 'Endereço não informado' : 'No estabelecimento' }}</div></v-col>
                        </v-row>
                        <v-divider class="my-6" />
                        <div class="text-subtitle-1 font-weight-bold mb-3">Composição escolhida</div>
                        <div class="d-flex flex-wrap ga-2"><v-chip v-for="item in relatorio.itens" :key="`${item.categoria}-${item.nome}`" color="primary" variant="tonal" prepend-icon="mdi-check">{{ item.nome }}{{ item.quantidade > 1 ? ` × ${item.quantidade}` : '' }}</v-chip></div>
                        <v-alert class="mt-6" type="info" variant="tonal" icon="mdi-information-outline">{{ relatorio.modo_cardapio === 'personalizado' ? 'Sua seleção será analisada pela equipe, que retornará com uma proposta personalizada.' : `O cálculo considera ${valorBase} ${relatorio.tipo_preco === 'por_pessoa' ? 'por pessoa' : 'por evento'}. O valor apresentado é uma estimativa e será confirmado pela equipe após a análise.` }}</v-alert>
                    </v-card-text>
                </v-card>
            </v-container>
        </v-main>
    </v-app>
</template>

<style scoped>
.confirmation-page { height: 100vh; min-height: 100vh; overflow: hidden; background: rgb(var(--v-theme-background)); }
.confirmation-page :deep(.v-container) { padding-top: 12px !important; padding-bottom: 12px !important; }
.confirmation-page :deep(.v-card-text) { padding: 24px !important; }
.confirmation-hero { color: white; background: linear-gradient(135deg, #166534, #15803d 55%, #0f766e); }
.confirmation-hero { padding: 20px 28px !important; }
.confirmation-hero h1 { margin-top: 8px !important; }
.confirmation-hero p { margin-top: 6px !important; }
.confirmation-hero .mt-5 { margin-top: 8px !important; }
.confirmation-hero p { color: rgba(255,255,255,.86); }
.confirmation-page :deep(.my-6) { margin-top: 16px !important; margin-bottom: 16px !important; }
.confirmation-page :deep(.mt-6) { margin-top: 16px !important; }
.confirmation-page :deep(.v-chip) { height: 30px; }
.summary-label { color: rgba(var(--v-theme-on-surface), .6); font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
.summary-value { margin-top: 4px; font-weight: 600; }
</style>
