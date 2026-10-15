<script setup>
import { ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ solicitacoes: { type: Array, required: true } });
const selecionada = ref(null);
const retornoEm = ref('');

const abrir = (solicitacao) => {
    selecionada.value = solicitacao;
    retornoEm.value = solicitacao.retorno_em || '';
};
const fechar = () => { selecionada.value = null; retornoEm.value = ''; };
const agendarRetorno = () => {
    if (!retornoEm.value || !selecionada.value) return;
    router.patch(`/app/solicitacoes/${selecionada.value.id}/retorno`, { retorno_em: retornoEm.value }, { preserveScroll: true, onSuccess: () => { selecionada.value.retorno_em = retornoEm.value; selecionada.value.status = 'retorno_agendado'; } });
};
const excluir = () => {
    if (!selecionada.value || !window.confirm(`Excluir a solicitação de ${selecionada.value.nome_contato}? Esta ação não pode ser desfeita.`)) return;
    router.delete(`/app/solicitacoes/${selecionada.value.id}`, { preserveScroll: true, onSuccess: fechar });
};
const valorExibicao = (solicitacao) => solicitacao.modo_cardapio === 'personalizado' ? 'A combinar' : `R$ ${solicitacao.preco_estimado}`;
</script>

<template>
    <Head title="Solicitações" />
    <div class="d-flex align-center justify-space-between mb-6"><div class="d-flex align-center ga-3"><div class="page-title-icon"><v-icon icon="mdi-file-document-outline" size="19" /></div><div><div class="text-caption text-medium-emphasis font-weight-medium">Atendimento comercial</div><h1 class="text-h4 font-weight-bold lh-tight">Solicitações</h1></div></div><v-chip color="primary" variant="tonal" prepend-icon="mdi-inbox-outline">{{ solicitacoes.length }} recebida(s)</v-chip></div>
    <v-card v-if="solicitacoes.length" border flat rounded="xl"><v-table hover><thead><tr><th>Cliente</th><th>Pacote</th><th>Evento</th><th>Estimativa</th><th>Status</th><th>Recebida em</th></tr></thead><tbody><tr v-for="solicitacao in solicitacoes" :key="solicitacao.id" class="solicitacao-row" @click="abrir(solicitacao)"><td><div class="font-weight-medium">{{ solicitacao.nome_contato }}</div><div class="text-caption text-medium-emphasis">{{ solicitacao.email }} · {{ solicitacao.telefone }}</div></td><td><div>{{ solicitacao.pacote }}</div><div class="text-caption text-medium-emphasis">{{ solicitacao.itens.length }} prato(s) escolhido(s)</div></td><td><div>{{ solicitacao.data_evento }}</div><div class="text-caption text-medium-emphasis">{{ solicitacao.convidados }} convidados</div></td><td class="font-weight-bold">R$ {{ solicitacao.preco_estimado }}</td><td><v-chip size="small" :color="solicitacao.status === 'retorno_agendado' ? 'info' : 'warning'" variant="tonal">{{ solicitacao.status.replace('_', ' ') }}</v-chip><div v-if="solicitacao.retorno_em" class="text-caption text-medium-emphasis mt-1">Retorno: {{ solicitacao.retorno_em.split('-').reverse().join('/') }}</div></td><td class="text-medium-emphasis">{{ solicitacao.created_at }}</td></tr></tbody></v-table></v-card>
    <v-card v-else border flat rounded="xl" class="pa-12 text-center"><v-avatar color="primary" variant="tonal" size="64"><v-icon icon="mdi-inbox-outline" size="32" /></v-avatar><div class="text-h6 font-weight-bold mt-4">Nenhuma solicitação recebida</div><div class="text-body-2 text-medium-emphasis mt-2">Compartilhe o link do cardápio para começar a receber pedidos.</div></v-card>
    <v-dialog v-model="selecionada" max-width="760" scrollable><v-card v-if="selecionada" rounded="xl"><v-card-item class="pa-6"><template #prepend><v-avatar color="primary" variant="tonal" size="48"><v-icon icon="mdi-account-outline" /></v-avatar></template><v-card-title class="text-h5">{{ selecionada.nome_contato }}</v-card-title><v-card-subtitle>Protocolo {{ selecionada.protocolo }}</v-card-subtitle><template #append><v-btn icon="mdi-close" variant="text" aria-label="Fechar" @click="fechar" /></template></v-card-item><v-divider /><v-card-text class="pa-6"><div class="d-flex flex-wrap ga-2 mb-6"><v-btn :href="`tel:${selecionada.telefone}`" color="primary" variant="tonal" prepend-icon="mdi-phone-outline">Ligar</v-btn><v-btn :href="`mailto:${selecionada.email}`" color="primary" variant="tonal" prepend-icon="mdi-email-outline">E-mail</v-btn><v-btn :href="`https://wa.me/55${selecionada.telefone.replace(/\D/g, '')}`" target="_blank" color="success" variant="tonal" prepend-icon="mdi-whatsapp">WhatsApp</v-btn></div><v-row><v-col cols="12" sm="6"><div class="detail-label">E-mail</div><div class="detail-value">{{ selecionada.email }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Telefone</div><div class="detail-value">{{ selecionada.telefone }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Pacote escolhido</div><div class="detail-value">{{ selecionada.pacote }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Estimativa</div><div class="detail-value">R$ {{ selecionada.preco_estimado }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Data do evento</div><div class="detail-value">{{ selecionada.data_evento }} · {{ selecionada.convidados }} convidados</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Local</div><div class="detail-value">{{ selecionada.tipo_local === 'externo' ? selecionada.endereco_local || 'Endereço não informado' : 'No estabelecimento' }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Contato autorizado</div><div class="detail-value"><v-chip size="small" :color="selecionada.aceita_contato ? 'success' : 'grey'" variant="tonal">{{ selecionada.aceita_contato ? 'Sim' : 'Não' }}</v-chip></div></v-col></v-row><v-divider class="my-5" /><div class="text-subtitle-1 font-weight-bold mb-2">Pratos escolhidos</div><div class="d-flex flex-wrap ga-2"><v-chip v-for="item in selecionada.itens" :key="item" size="small" variant="tonal" color="primary">{{ item }}</v-chip></div><div v-if="selecionada.observacoes" class="mt-6"><div class="text-subtitle-1 font-weight-bold mb-2">Observações</div><div class="detail-note">{{ selecionada.observacoes }}</div></div><v-divider class="my-6" /><div class="text-subtitle-1 font-weight-bold mb-2">Acompanhamento</div><div class="d-flex align-center ga-3 flex-wrap"><v-text-field v-model="retornoEm" type="date" label="Lembrar de retornar em" density="comfortable" hide-details style="max-width: 230px" /><v-btn color="primary" variant="tonal" prepend-icon="mdi-calendar-check-outline" :disabled="!retornoEm" @click="agendarRetorno">Agendar retorno</v-btn></div><div v-if="selecionada.retorno_em" class="text-caption text-medium-emphasis mt-2">Retorno agendado para {{ selecionada.retorno_em.split('-').reverse().join('/') }}.</div></v-card-text><v-card-actions class="pa-5"><v-btn color="error" variant="text" prepend-icon="mdi-trash-can-outline" @click="excluir">Excluir solicitação</v-btn><v-spacer /><v-btn color="primary" rounded="lg" @click="fechar">Fechar</v-btn></v-card-actions></v-card></v-dialog>
</template>

<style scoped>
.page-title-icon { width: 36px; height: 36px; display: grid; place-items: center; color: rgb(var(--v-theme-primary)); background: rgba(var(--v-theme-primary), .10); border: 1px solid rgba(var(--v-theme-primary), .16); border-radius: 10px; }
:deep(.v-table th) { color: rgb(var(--v-theme-on-surface)); font-size: .75rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; }
:deep(.v-table td) { height: 72px; }
.solicitacao-row { cursor: pointer; }
.solicitacao-row:hover { background: rgba(var(--v-theme-primary), .05); }
.detail-label { color: rgba(var(--v-theme-on-surface), .60); font-size: .75rem; font-weight: 600; text-transform: uppercase; letter-spacing: .04em; }
.detail-value { font-size: 1rem; font-weight: 500; margin-top: 4px; }
.detail-note { border-radius: 8px; background: rgba(var(--v-theme-primary), .06); padding: 14px; white-space: pre-wrap; }
</style>
