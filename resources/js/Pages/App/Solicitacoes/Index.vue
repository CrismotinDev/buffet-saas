<script setup>
import { computed, ref } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import { VDataTable } from 'vuetify/components';
import AppLayout from '../../../Layouts/AppLayout.vue';

defineOptions({ layout: AppLayout });
const props = defineProps({ solicitacoes: { type: Array, required: true }, itens_disponiveis: { type: Array, default: () => [] } });
const selecionada = ref(null);
const retornoEm = ref('');
const propostaForm = ref({ preco_proposto: 0, desconto_proposto: 0, validade_proposta: '', observacoes_proposta: '' });
const propostaSalvando = ref(false);
const itemParaAdicionar = ref(null);
const quantidadeItemAdicionar = ref(1);
const valorCardapioPessoa = ref(0);
const busca = ref('');
const cabecalhos = [
    { title: 'Cliente', key: 'nome_contato', sortable: true },
    { title: 'Pacote', key: 'pacote', sortable: true },
    { title: 'Evento', key: 'data_evento', sortable: true },
    { title: 'Proposta', key: 'preco_proposto', sortable: true },
    { title: 'Status', key: 'status', sortable: true },
    { title: 'Recebida em', key: 'created_at', sortable: true },
];

const abrir = (solicitacao) => {
    selecionada.value = solicitacao;
    retornoEm.value = solicitacao.retorno_em || '';
    propostaForm.value = { preco_proposto: solicitacao.preco_estimado ?? solicitacao.preco_proposto ?? 0, desconto_proposto: solicitacao.desconto_proposto ?? 0, validade_proposta: solicitacao.validade_proposta || '', observacoes_proposta: solicitacao.observacoes_proposta || '' };
    valorCardapioPessoa.value = Number(solicitacao.valor_cardapio_pessoa || 0);
};
const fechar = () => { selecionada.value = null; retornoEm.value = ''; };
const salvarProposta = () => {
    if (!selecionada.value) return;
    propostaSalvando.value = true;
    router.patch(`/app/solicitacoes/${selecionada.value.id}/proposta`, propostaForm.value, { preserveScroll: true, onFinish: () => { propostaSalvando.value = false; }, onSuccess: () => { const atualizada = props.solicitacoes.find((item) => item.id === selecionada.value?.id); if (atualizada) selecionada.value = atualizada; } });
};
const agendarRetorno = () => { if (!retornoEm.value || !selecionada.value) return; router.patch(`/app/solicitacoes/${selecionada.value.id}/retorno`, { retorno_em: retornoEm.value }, { preserveScroll: true, onSuccess: () => { selecionada.value.retorno_em = retornoEm.value; selecionada.value.status = 'retorno_agendado'; } }); };
const excluir = () => { if (!selecionada.value || !window.confirm(`Excluir a solicitação de ${selecionada.value.nome_contato}? Esta ação não pode ser desfeita.`)) return; router.delete(`/app/solicitacoes/${selecionada.value.id}`, { preserveScroll: true, onSuccess: fechar }); };
const valorExibicao = (solicitacao) => solicitacao.preco_proposto !== null && solicitacao.preco_proposto !== undefined ? `R$ ${solicitacao.preco_proposto}` : `R$ ${solicitacao.preco_estimado}`;
const moeda = (valor) => new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(Number(valor) || 0);
const totalProposta = computed(() => Math.max(0, Number(propostaForm.value.preco_proposto || 0) - Number(propostaForm.value.desconto_proposto || 0)));
const mensagemWhatsApp = computed(() => { if (!selecionada.value?.proposta_url) return ''; return `Olá, ${selecionada.value.nome_contato}! Preparamos sua proposta para o evento de ${selecionada.value.data_evento}. Confira todos os detalhes e valores: ${selecionada.value.proposta_url}`; });
const linkWhatsApp = computed(() => `https://wa.me/55${(selecionada.value?.telefone || '').replace(/\D/g, '')}?text=${encodeURIComponent(mensagemWhatsApp.value)}`);
const linkEmail = computed(() => `mailto:${selecionada.value?.email || ''}?subject=${encodeURIComponent(`Proposta comercial - ${selecionada.value?.protocolo || ''}`)}&body=${encodeURIComponent(mensagemWhatsApp.value)}`);
const marcarPropostaEnviada = () => { if (!selecionada.value?.proposta_url) return; router.patch(`/app/solicitacoes/${selecionada.value.id}/proposta/enviada`, {}, { preserveScroll: true, onSuccess: () => { selecionada.value.status = 'proposta_enviada'; selecionada.value.proposta_enviada_em = new Date().toISOString(); } }); };
const bebidas = computed(() => selecionada.value?.itens?.filter((item) => Number(item.preco_unitario) > 0) || []);
const itensParaAdicionar = computed(() => {
    const selecionados = new Set(selecionada.value?.itens?.map((item) => item.item_id) || []);
    return props.itens_disponiveis.filter((item) => !selecionados.has(item.id));
});
const adicionarItem = () => {
    if (!selecionada.value || !itemParaAdicionar.value) return;
    router.post(`/app/solicitacoes/${selecionada.value.id}/itens`, { item_id: itemParaAdicionar.value, quantidade: quantidadeItemAdicionar.value }, {
        preserveScroll: true,
        onSuccess: () => { itemParaAdicionar.value = null; quantidadeItemAdicionar.value = 1; },
    });
};
const atualizarValorCardapio = () => {
    if (!selecionada.value) return;
    const bebidasTotal = bebidas.value.reduce((total, item) => total + Number(item.quantidade) * Number(item.preco_unitario), 0);
    const novoTotal = Number(valorCardapioPessoa.value || 0) * Number(selecionada.value.convidados) + bebidasTotal;
    selecionada.value.preco_estimado = novoTotal.toFixed(2);
    selecionada.value.preco_proposto = novoTotal.toFixed(2);
    propostaForm.value.preco_proposto = novoTotal.toFixed(2);
    router.patch(`/app/solicitacoes/${selecionada.value.id}/valor-cardapio`, { valor_cardapio_pessoa: Number(valorCardapioPessoa.value || 0) }, { preserveScroll: true });
};
const atualizarQuantidadeBebida = (item) => {
    const subtotalAnterior = bebidas.value.reduce((total, bebida) => total + Number(bebida.subtotal || 0), 0);
    const subtotalAtual = bebidas.value.reduce((total, bebida) => total + Number(bebida.quantidade) * Number(bebida.preco_unitario), 0);
    const novaEstimativa = Math.max(0, Number(selecionada.value.preco_estimado || 0) - subtotalAnterior + subtotalAtual);
    item.subtotal = Number(item.quantidade) * Number(item.preco_unitario);
    selecionada.value.preco_estimado = novaEstimativa.toFixed(2);
    selecionada.value.preco_proposto = novaEstimativa.toFixed(2);
    propostaForm.value.preco_proposto = novaEstimativa.toFixed(2);
    router.patch(`/app/solicitacoes/${selecionada.value.id}/itens/${item.id}/quantidade`, { quantidade: Number(item.quantidade) }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Solicitações" />
    <div class="d-flex align-center justify-space-between mb-6"><div class="d-flex align-center ga-3"><div class="page-title-icon"><v-icon icon="mdi-file-document-outline" size="19" /></div><div><div class="text-caption text-medium-emphasis font-weight-medium">Atendimento comercial</div><h1 class="text-h4 font-weight-bold lh-tight">Solicitações</h1></div></div><v-chip color="primary" variant="tonal" prepend-icon="mdi-inbox-outline">{{ solicitacoes.length }} recebida(s)</v-chip></div>
    <v-card v-if="solicitacoes.length" border flat rounded="xl">
        <div class="d-flex flex-wrap align-center justify-space-between ga-3 pa-4"><div class="text-body-2 text-medium-emphasis">Clique em uma solicitação para abrir os detalhes.</div><v-text-field v-model="busca" label="Pesquisar solicitações" prepend-inner-icon="mdi-magnify" density="compact" variant="outlined" hide-details clearable style="max-width: 320px" /></div>
        <v-data-table :headers="cabecalhos" :items="solicitacoes" :search="busca" item-value="id" hover items-per-page="10" :items-per-page-options="[5, 10, 25, 50]" class="solicitacoes-table" @click:row="(_, { item }) => abrir(item)">
            <template #item.nome_contato="{ item }"><div class="font-weight-medium">{{ item.nome_contato }}</div><div class="text-caption text-medium-emphasis">{{ item.email }} · {{ item.telefone }}</div></template>
            <template #item.pacote="{ item }"><div>{{ item.pacote }}</div><div class="text-caption text-medium-emphasis">{{ item.itens.length }} item(ns)</div></template>
            <template #item.data_evento="{ item }"><div>{{ item.data_evento }}</div><div class="text-caption text-medium-emphasis">{{ item.convidados }} convidados</div></template>
            <template #item.preco_proposto="{ item }"><span class="font-weight-bold">{{ valorExibicao(item) }}</span></template>
            <template #item.status="{ item }"><v-chip size="small" :color="item.status === 'retorno_agendado' ? 'info' : 'warning'" variant="tonal">{{ item.status.replace('_', ' ') }}</v-chip><div v-if="item.retorno_em" class="text-caption text-medium-emphasis mt-1">Retorno: {{ item.retorno_em.split('-').reverse().join('/') }}</div></template>
            <template #item.created_at="{ item }"><span class="text-medium-emphasis">{{ item.created_at }}</span></template>
        </v-data-table>
    </v-card>
    <v-card v-else border flat rounded="xl" class="pa-12 text-center"><v-avatar color="primary" variant="tonal" size="64"><v-icon icon="mdi-inbox-outline" size="32" /></v-avatar><div class="text-h6 font-weight-bold mt-4">Nenhuma solicitação recebida</div><div class="text-body-2 text-medium-emphasis mt-2">Compartilhe o link do cardápio para começar a receber pedidos.</div></v-card>

    <v-dialog v-model="selecionada" max-width="900" scrollable><v-card v-if="selecionada" rounded="xl"><v-card-item class="pa-6"><template #prepend><v-avatar color="primary" variant="tonal" size="48"><v-icon icon="mdi-account-outline" /></v-avatar></template><v-card-title class="text-h5">{{ selecionada.nome_contato }}</v-card-title><v-card-subtitle>Protocolo {{ selecionada.protocolo }}</v-card-subtitle><template #append><v-btn icon="mdi-close" variant="text" aria-label="Fechar" @click="fechar" /></template></v-card-item><v-divider /><v-card-text class="pa-6">
        <div class="d-flex flex-wrap ga-2 mb-6"><v-btn :href="`tel:${selecionada.telefone}`" color="primary" variant="tonal" prepend-icon="mdi-phone-outline">Ligar</v-btn><v-btn :href="linkEmail" color="primary" variant="tonal" prepend-icon="mdi-email-outline" :disabled="!selecionada.proposta_url" @click="marcarPropostaEnviada">E-mail com proposta</v-btn><v-btn :href="linkWhatsApp" target="_blank" color="success" variant="tonal" prepend-icon="mdi-whatsapp" :disabled="!selecionada.proposta_url" @click="marcarPropostaEnviada">WhatsApp com proposta</v-btn></div>
        <v-row><v-col cols="12" sm="6"><div class="detail-label">E-mail</div><div class="detail-value">{{ selecionada.email }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Telefone</div><div class="detail-value">{{ selecionada.telefone }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Pacote escolhido</div><div class="detail-value">{{ selecionada.pacote }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Estimativa atualizada</div><div class="detail-value">{{ moeda(selecionada.preco_estimado) }}</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Data do evento</div><div class="detail-value">{{ selecionada.data_evento }} · {{ selecionada.convidados }} convidados</div></v-col><v-col cols="12" sm="6"><div class="detail-label">Local</div><div class="detail-value">{{ selecionada.tipo_local === 'externo' ? selecionada.endereco_local || 'Endereço não informado' : 'No estabelecimento' }}</div></v-col></v-row>
        <v-divider class="my-6" /><div class="text-subtitle-1 font-weight-bold mb-3">Formalizar proposta</div><v-alert v-if="!selecionada.proposta_url" type="info" variant="tonal" density="comfortable" class="mb-4">Configure e salve a proposta para liberar o link profissional e os botões de envio.</v-alert><v-row><v-col cols="12" sm="4"><v-text-field v-model="propostaForm.preco_proposto" label="Valor da proposta" type="number" min="0" step="0.01" prefix="R$" /></v-col><v-col cols="12" sm="4"><v-text-field v-model="propostaForm.desconto_proposto" label="Desconto" type="number" min="0" step="0.01" prefix="R$" /></v-col><v-col cols="12" sm="4"><v-text-field v-model="propostaForm.validade_proposta" label="Validade da proposta" type="date" /></v-col><v-col cols="12"><v-textarea v-model="propostaForm.observacoes_proposta" label="Observações e condições comerciais" rows="3" placeholder="Ex.: prazo de confirmação, forma de pagamento e condições." /></v-col></v-row>
        <v-divider class="my-6" /><div class="text-subtitle-1 font-weight-bold mb-2">Itens escolhidos</div><div class="d-flex flex-wrap ga-2"><v-chip v-for="item in selecionada.itens" :key="`${item.nome}-${item.quantidade}`" size="small" variant="tonal" color="primary">{{ item.nome }}{{ item.quantidade > 1 ? ` × ${item.quantidade}` : '' }}</v-chip></div><div v-if="bebidas.length" class="mt-6"><div class="text-subtitle-1 font-weight-bold mb-1">Ajustar bebidas</div><div class="text-body-2 text-medium-emphasis mb-3">Informe uma quantidade aproximada para calcular um orçamento mais próximo do consumo do evento.</div><v-list density="compact" class="rounded-lg border"><v-list-item v-for="item in bebidas" :key="item.nome"><template #prepend><v-icon icon="mdi-bottle-soda-outline" color="primary" /></template><v-list-item-title>{{ item.nome }}</v-list-item-title><template #append><div class="d-flex align-center ga-3"><v-text-field v-model.number="item.quantidade" type="number" min="1" max="9999" density="compact" hide-details variant="outlined" suffix="un." style="max-width: 125px" @change="atualizarQuantidadeBebida(item)" /><span class="font-weight-medium" style="min-width: 92px; text-align: right">{{ moeda(Number(item.quantidade) * Number(item.preco_unitario)) }}</span></div></template></v-list-item></v-list><div class="text-caption text-medium-emphasis mt-2">Subtotal das bebidas: {{ moeda(bebidas.reduce((total, item) => total + Number(item.quantidade) * Number(item.preco_unitario), 0)) }}</div></div><div v-if="selecionada.modo_cardapio === 'personalizado'" class="mt-6 pa-4 rounded-lg border"><div class="text-subtitle-1 font-weight-bold mb-1">Adicionar itens ao cardápio</div><div class="text-body-2 text-medium-emphasis mb-3">Inclua alimentos ou bebidas que o cliente não selecionou inicialmente.</div><div class="d-flex align-center flex-wrap ga-3"><v-select v-model="itemParaAdicionar" :items="itensParaAdicionar" item-title="nome" item-value="id" label="Item do catálogo" density="comfortable" variant="outlined" hide-details style="min-width: 280px; flex: 1"><template #item="{ props: itemProps, item }"><v-list-item v-bind="itemProps" :subtitle="`${item.raw.tipo_item === 'bebida' ? 'Bebida' : 'Comida'}${Number(item.raw.preco_unitario) > 0 ? ` · ${moeda(item.raw.preco_unitario)}` : ''}`" /></template></v-select><v-text-field v-model.number="quantidadeItemAdicionar" label="Quantidade" type="number" min="1" max="9999" density="comfortable" variant="outlined" hide-details style="max-width: 140px" /><v-btn color="primary" :disabled="!itemParaAdicionar" prepend-icon="mdi-plus" @click="adicionarItem">Adicionar</v-btn></div></div><div v-if="selecionada.observacoes" class="mt-6"><div class="text-subtitle-1 font-weight-bold mb-2">Observações do cliente</div><div class="detail-note">{{ selecionada.observacoes }}</div></div><div class="proposal-total d-flex align-center justify-space-between mt-6"><span>Total final da proposta</span><strong>{{ moeda(totalProposta) }}</strong></div><div class="d-flex flex-wrap ga-2 mt-4"><v-btn color="primary" :loading="propostaSalvando" prepend-icon="mdi-file-check-outline" @click="salvarProposta">Salvar proposta</v-btn><v-btn v-if="selecionada.proposta_url" :href="selecionada.proposta_url" target="_blank" variant="tonal" color="primary" prepend-icon="mdi-open-in-new">Abrir proposta</v-btn></div>
        <v-divider class="my-6" /><div class="text-subtitle-1 font-weight-bold mb-2">Acompanhamento</div><div class="d-flex align-center ga-3 flex-wrap"><v-text-field v-model="retornoEm" type="date" label="Lembrar de retornar em" density="comfortable" hide-details style="max-width: 230px" /><v-btn color="primary" variant="tonal" prepend-icon="mdi-calendar-check-outline" :disabled="!retornoEm" @click="agendarRetorno">Agendar retorno</v-btn></div><div v-if="selecionada.retorno_em" class="text-caption text-medium-emphasis mt-2">Retorno agendado para {{ selecionada.retorno_em.split('-').reverse().join('/') }}.</div>
        <div v-if="selecionada.modo_cardapio === 'personalizado'" class="mt-6 pa-4 rounded-lg border"><div class="text-subtitle-1 font-weight-bold mb-1">Valor do cardápio personalizado</div><div class="text-body-2 text-medium-emphasis mb-3">Informe o valor por pessoa dos alimentos selecionados. As bebidas continuam sendo somadas pela quantidade.</div><div class="d-flex align-center flex-wrap ga-3"><v-text-field v-model.number="valorCardapioPessoa" label="Valor por pessoa" type="number" min="0" step="0.01" prefix="R$" density="comfortable" variant="outlined" hide-details style="max-width: 230px" /><v-btn color="primary" prepend-icon="mdi-calculator-variant-outline" @click="atualizarValorCardapio">Atualizar orçamento</v-btn></div></div>
    </v-card-text><v-card-actions class="pa-5"><v-btn color="error" variant="text" prepend-icon="mdi-trash-can-outline" @click="excluir">Excluir solicitação</v-btn><v-spacer /><v-btn color="primary" rounded="lg" @click="fechar">Fechar</v-btn></v-card-actions></v-card></v-dialog>
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
.proposal-total { border: 1px solid rgba(var(--v-theme-primary), .25); border-radius: 12px; background: rgba(var(--v-theme-primary), .08); padding: 16px 18px; }
.proposal-total strong { color: rgb(var(--v-theme-primary)); font-size: 1.35rem; }
:deep(.v-card-text > .pa-4:has(.v-select)) { display: none !important; }
</style>
