<script setup>
import { computed } from 'vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({ proposta: { type: Object, required: true } });
const moeda = (valor) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(valor) || 0);
const total = computed(() => Math.max(0, Number(props.proposta.preco_proposto || 0) - Number(props.proposta.desconto || 0)));
const imprimir = () => window.print();
</script>

<template>
    <Head :title="`Proposta ${proposta.protocolo} · ${proposta.empresa.nome_fantasia}`" />
    <v-app><v-main class="proposal-page"><v-container class="py-5" style="max-width: 1240px; width: 100%"><div class="d-flex justify-end mb-3 no-print"><v-btn color="primary" variant="tonal" prepend-icon="mdi-printer-outline" @click="imprimir">Imprimir / salvar PDF</v-btn></div><v-card class="proposal-card" border flat rounded="xl" overflow="hidden"><div class="proposal-header"><div class="d-flex align-start justify-space-between ga-5"><div><div class="proposal-kicker">PROPOSTA COMERCIAL</div><h1>{{ proposta.empresa.nome_fantasia }}</h1><div class="proposal-subtitle">Solução personalizada para o seu evento</div></div><v-avatar color="white" size="64"><v-icon icon="mdi-silverware-fork-knife" color="primary" size="34" /></v-avatar></div><div class="proposal-meta"><span>Protocolo {{ proposta.protocolo }}</span><span>Emitida em {{ new Date().toLocaleDateString('pt-BR') }}</span></div></div><v-card-text class="pa-8"><div class="d-flex align-start justify-space-between flex-wrap ga-4 mb-8"><div><div class="section-label">Olá, {{ proposta.cliente }}</div><div class="text-body-2 text-medium-emphasis mt-1">Preparamos esta proposta para análise do seu evento.</div></div><div class="text-right"><div class="section-label">Valor final</div><div class="proposal-price">{{ moeda(total) }}</div></div></div><v-row class="summary-grid mb-6"><v-col cols="12" sm="4"><div class="summary-label">Cardápio</div><div class="summary-value">{{ proposta.pacote }}</div></v-col><v-col cols="12" sm="4"><div class="summary-label">Data do evento</div><div class="summary-value">{{ proposta.data_evento }}</div></v-col><v-col cols="12" sm="4"><div class="summary-label">Convidados</div><div class="summary-value">{{ proposta.convidados }} pessoas</div></v-col><v-col cols="12"><div class="summary-label">Local</div><div class="summary-value">{{ proposta.local }}</div></v-col></v-row><v-divider class="mb-6" /><div class="section-label mb-3">Composição do cardápio</div><div class="proposal-table"><div v-for="item in proposta.itens" :key="`${item.nome}-${item.quantidade}`" class="proposal-row"><div><strong>{{ item.nome }}</strong><span v-if="item.quantidade > 1" class="item-quantity">Quantidade: {{ item.quantidade }}</span></div><div class="text-right"><span v-if="Number(item.subtotal) > 0">{{ moeda(item.subtotal) }}</span><span v-else class="text-medium-emphasis">Incluído</span></div></div></div><div v-if="Number(proposta.desconto) > 0" class="discount-row"><span>Desconto comercial</span><strong>- {{ moeda(proposta.desconto) }}</strong></div><div class="total-row"><span>Total da proposta</span><strong>{{ moeda(total) }}</strong></div><v-alert v-if="proposta.observacoes" class="mt-7" type="info" variant="tonal" icon="mdi-information-outline"><div class="section-label mb-1">Condições comerciais</div><div class="proposal-notes">{{ proposta.observacoes }}</div></v-alert><div v-if="proposta.validade" class="validity mt-5"><v-icon icon="mdi-calendar-clock-outline" size="18" /> Proposta válida até {{ proposta.validade }}</div><div class="contact-box mt-8"><div class="section-label">Dúvidas ou confirmação</div><div class="text-body-2 mt-1">Entre em contato com {{ proposta.empresa.nome_fantasia }} para confirmar disponibilidade e próximos passos.</div><div class="d-flex flex-wrap ga-2 mt-3 no-print"><v-btn v-if="proposta.empresa.telefone" :href="`tel:${proposta.empresa.telefone}`" size="small" variant="tonal" prepend-icon="mdi-phone-outline">{{ proposta.empresa.telefone }}</v-btn><v-btn v-if="proposta.empresa.telefone" :href="`https://wa.me/55${proposta.empresa.telefone.replace(/\D/g, '')}`" target="_blank" size="small" color="success" variant="tonal" prepend-icon="mdi-whatsapp">WhatsApp</v-btn><v-btn v-if="proposta.empresa.email" :href="`mailto:${proposta.empresa.email}`" size="small" variant="tonal" prepend-icon="mdi-email-outline">{{ proposta.empresa.email }}</v-btn></div></div></v-card-text><div class="proposal-footer">Documento gerado pelo sistema de gestão de eventos</div></v-card></v-container></v-main></v-app>
</template>

<style scoped>
.proposal-page { min-height: 100vh; background: #f4f6f8; }
.proposal-card { color: #1f2937 !important; background: #ffffff !important; color-scheme: light; }
.proposal-card :deep(.v-card-text), .proposal-card :deep(.v-card-title), .proposal-card :deep(.v-card-subtitle) { color: #1f2937 !important; }
.proposal-card :deep(.pa-8) { padding: 24px !important; }
.proposal-card :deep(.mb-8) { margin-bottom: 18px !important; }
.proposal-card :deep(.mb-6) { margin-bottom: 16px !important; }
.proposal-card :deep(.mt-8) { margin-top: 18px !important; }
.proposal-card :deep(.mt-7) { margin-top: 16px !important; }
.proposal-header { color: white; padding: 26px 32px 22px; background: linear-gradient(135deg, #7f1d1d, #991b1b 55%, #b45309); }
.proposal-kicker, .section-label, .summary-label { font-size: .72rem; font-weight: 800; letter-spacing: .12em; text-transform: uppercase; }
.proposal-header h1 { margin-top: 8px; font-size: 2rem; }
.proposal-subtitle { color: rgba(255,255,255,.82); margin-top: 5px; }
.proposal-meta { display: flex; justify-content: space-between; gap: 16px; margin-top: 18px; color: rgba(255,255,255,.8); font-size: .78rem; }
.section-label, .summary-label { color: #8f2020; }
.proposal-price { color: #8f2020; font-size: 1.7rem; font-weight: 800; margin-top: 3px; }
.summary-grid { background: #f8fafc; border-radius: 12px; padding: 10px 12px; }
.summary-value { margin-top: 4px; font-weight: 650; }
.proposal-table { border: 1px solid #e5e7eb; border-radius: 10px; overflow: hidden; }
.proposal-table { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
.proposal-row { display: flex; align-items: center; justify-content: space-between; gap: 20px; padding: 8px 14px; border-bottom: 1px solid #e5e7eb; background: #ffffff; }
.proposal-row:nth-child(even) { background: #f8fafc; }
.proposal-row:last-child { border-bottom: 0; }
.proposal-row strong { color: #1f2937 !important; font-size: .95rem; }
.proposal-row .text-right { color: #374151 !important; font-weight: 650; }
.proposal-row .text-medium-emphasis { color: #6b7280 !important; }
.item-quantity { display: block; color: #6b7280; font-size: .8rem; margin-top: 3px; }
.discount-row, .total-row { display: flex; justify-content: space-between; padding: 8px 4px; }
.discount-row { color: #b42318; }
.total-row { margin-top: 2px; border-top: 2px solid #8f2020; color: #8f2020; font-size: 1.15rem; font-weight: 800; }
.proposal-notes { white-space: pre-wrap; }
.validity, .contact-box { border-radius: 10px; padding: 10px 14px; background: #f8fafc; }
.validity { display: flex; align-items: center; gap: 8px; color: #374151; font-size: .9rem; }
.contact-box { border-left: 4px solid #8f2020; }
.proposal-footer { padding: 14px; color: #6b7280; background: #f8fafc; text-align: center; font-size: .75rem; }
@media (max-width: 700px) { .proposal-table { grid-template-columns: 1fr; } }
@media (max-width: 600px) { .proposal-header { padding: 22px 20px 18px; } .proposal-header h1 { font-size: 1.55rem; } .proposal-meta { flex-direction: column; margin-top: 14px; } .proposal-card :deep(.v-card-text) { padding: 18px !important; } }
@media print { .proposal-page { background: white !important; } .no-print { display: none !important; } .proposal-card { box-shadow: none !important; } }
</style>
