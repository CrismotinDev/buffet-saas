# SaaS de Gestão de Eventos e Buffets — Especificação para Codex

**Projeto piloto:** Cantinho da Costela  
**Objetivo:** criar um SaaS multiempresa para orçar, negociar, contratar, organizar e analisar eventos.  
**Stack alvo:** Laravel 12, PHP 8.3+, Vue 3, Inertia.js, Vuetify 3, PostgreSQL, Vite.  
**Ambiente:** WSL/Docker ou ambiente local; Laravel em `http://localhost:8075` com `php artisan serve --host=0.0.0.0 --port=8075`; Vite com `npm run dev`.

> **Instrução principal ao Codex:** Leia este documento, inspecione o repositório existente e implemente somente a fase solicitada em cada interação. Não reestruture indiscriminadamente código existente. Antes de alterar, identifique o starter kit, versões, autenticação, arquivos e convenções presentes. Ao final de cada fase, liste arquivos alterados, migrations, rotas, decisões de projeto e comandos de verificação; execute testes e build quando possível. Não declarar testes aprovados se não foram executados.

## 1. Produto e personas

- **Administrador da plataforma:** controla planos, empresas, contas bloqueadas, faturamento do SaaS futuramente e suporte, sem acesso indiscriminado aos dados dos clientes.
- **Administrador da empresa:** configura seu estabelecimento, espaços, catálogo, pacotes, preços, usuários, propostas e eventos.
- **Atendente/comercial:** cadastra leads, revisa solicitações, gera e envia propostas.
- **Financeiro:** acompanha sinais, parcelas e recebimentos, quando esse módulo existir.
- **Cliente público:** consulta cardápios, personaliza solicitação, recebe proposta e aprova/rejeita sem login administrativo.

**MVP obrigatório:** cadastro de empresas e usuários com isolamento, catálogo de itens, pacotes e restrições, formulário público, solicitação de orçamento, cálculo confiável, análise administrativa, emissão/visualização de proposta versionada e aceite eletrônico simples com trilha de auditoria. **Fora do MVP:** assinatura eletrônica qualificada, gateway de pagamento, WhatsApp API, emissão fiscal, ERP/PDV, compras e estoque, cálculo automático de insumos, contratos juridicamente sofisticados, aplicativo nativo, cálculo tributário.

## 2. Princípios inegociáveis

1. **Multiempresa:** cada dado operacional pertence a `empresa_id` (ou relação equivalente explicitamente checada). Toda leitura, escrita, download, job e ação Inertia deve respeitar o escopo da empresa; verificar autorizações por Policy. Nunca confiar em `empresa_id` enviado pelo browser. A escolha de empresa ativa deve ser feita no contexto autenticado e validada contra a participação do usuário.
2. **Valores calculados no servidor:** o frontend apenas apresenta estimativas. O backend valida regras e calcula usando preços do banco, nunca preços enviados pelo cliente. Valores monetários em `numeric(12,2)` e cálculos decimais seguros (centavos inteiros ou biblioteca decimal); nunca `float` para regras financeiras.
3. **Imutabilidade comercial:** propostas enviadas/aprovadas têm snapshot de itens, quantidades, valores e condições. Alterar cadastro de pacote ou reajustar preços não muda proposta antiga. Revisão cria nova versão.
4. **Separação de conceitos:** `solicitacao` é pedido do visitante; `orcamento/proposta` é oferta do estabelecimento; `evento` é reserva/execução e só é confirmado após fluxo apropriado. Um aceite sozinho não deve afirmar que houve pagamento ou assinatura legalmente válida.
5. **Dados e segurança:** validar entradas, escapar conteúdo, aplicar rate limit e proteção contra spam em endpoints públicos, tokens longos aleatórios armazenados por hash quando possível, expiração e revogação, não expor dados de outros clientes. LGPD: coletar dados mínimos e registrar consentimentos quando cabíveis.
6. **UX:** interfaces responsivas, em pt-BR, moeda BRL, datas no fuso da empresa (`America/Sao_Paulo` como padrão configurável). Acessibilidade em campos, erros e botões.
7. **Desenvolvimento por etapas:** nunca implementar módulos futuros sem autorização; criar extensibilidade adequada sem overengineering.

## 3. Exemplo oficial de aceitação

Empresa piloto: Cantinho da Costela. Pacote **Tradicional** com preço **R$ 75,00 por pessoa**. Solicitação pública com **100 convidados** e itens **Costela fogo de chão**, **Porco fogo de chão**, **Risoto** e **Maionese**. O total base calculado deve ser **R$ 7.500,00**, desde que seleção respeite restrições do pacote. Nenhum adicional nesse cenário. Este é um **cenário de demonstração/teste**, não implica que o pacote real tenha exatamente essas regras e preços em produção.

### Regras ilustrativas configuráveis do piloto

- Categoria Carnes: escolha exatamente 2 dentre itens permitidos.
- Categoria Acompanhamentos: escolha exatamente 2 dentre itens permitidos.
- Mínimo de 30 convidados (configurável); máximo opcional, condicionado à capacidade.
- Bebidas não inclusas por padrão; adicionais opcionais.
- Data/hora, local (no estabelecimento ou externo), dados de contato e observações da solicitação.
- Diferenciar **preço estimado** de **preço final aprovado pelo estabelecimento**.

## 4. Telas

### Área administrativa (autenticada)
- Dashboard: solicitações novas, propostas enviadas, aceites pendentes, próximos eventos; métricas com base nos dados da empresa.
- Empresa e equipe: dados, logo, endereço, contato, fuso, participação de usuários e papéis.
- Catálogo: categorias (carne, acompanhamento, bebida, sobremesa, serviço), itens, ativo/inativo, descrição, unidade e imagem opcional.
- Pacotes: listagem, criação, duplicação, edição, publicação; preço base por pessoa ou preço fixo; mínimo/máximo de convidados; categorias elegíveis, quantidades mínimas/máximas e itens permitidos; adicionais.
- Solicitações: fila com status, filtros e detalhamento; ações para revisar, atribuir valor final e criar proposta.
- Propostas: editor, prévia, envio via link, histórico das versões, status e aprovação.
- Eventos: apenas eventos derivados das propostas aprovadas e confirmadas conforme regra; calendário simples na fase adequada.

### Área pública (sem login)
- `/e/{slugEmpresa}`: página pública da empresa com seus pacotes publicados, informações e branding.
- `/e/{slugEmpresa}/pacotes/{slugPacote}`: detalhes, itens e formulário de personalização; etapas de data/local, convidados, seleção, adicionais, contato e resumo.
- `/solicitacoes/confirmacao/{token}` ou tela equivalente: confirmação da **recepção da solicitação**, sem divulgação de dados sensíveis em token adivinhável.
- `/p/{token}`: proposta com resumo versionado, valor, condições, prazo de validade; aceitar ou pedir alterações; não mostrar dados sigilosos desnecessários.

## 5. Modelo de dados sugerido

Adaptar nomes à convenção real do projeto, usar migrations e índices. Considerar UUID/ULID para tokens/IDs públicos e IDs internos tradicionais conforme padrão do starter.

### Identidade e tenancy
- `empresas`: id, nome_fantasia, razao_social opcional, documento opcional, slug único, email, telefone, logo_path, timezone, ativo, timestamps.
- `empresa_user`: empresa_id, user_id, papel (`owner`, `admin`, `comercial`, `financeiro`), ativo, timestamps, índice único (empresa_id,user_id).
- `users`: usar o starter existente; não substituir autenticação sem necessidade.

### Catálogo e pacotes
- `categorias_itens`: id, empresa_id, nome, slug, ordem, ativo.
- `itens_catalogo`: id, empresa_id, categoria_id, nome, descricao, unidade, ativo, imagem_path opcional.
- `pacotes`: id, empresa_id, nome, slug, descricao, tipo_preco (`por_pessoa`,`fixo`), preco_base decimal, min_convidados, max_convidados nullable, publicado, ativo, ordem.
- `pacote_categorias`: id, empresa_id, pacote_id, categoria_id, min_escolhas, max_escolhas, obrigatoria; índice único (pacote_id,categoria_id).
- `pacote_itens`: id, empresa_id, pacote_id, categoria_id, item_id, permitido, incluido, eventual_sobretaxa decimal nullable; validar correspondência de categoria e empresa.
- `adicionais`: id, empresa_id, nome, descricao, tipo_cobranca (`por_pessoa`,`por_evento`,`por_unidade`), valor_unitario decimal, ativo, min_quantidade, max_quantidade nullable.
- `pacote_adicionais`: id, empresa_id, pacote_id, adicional_id, ativo, eventualmente obrigatorio.

### Comercial
- `solicitacoes_orcamento`: id, empresa_id, pacote_id, protocolo público não sequencial, nome_contato, email, telefone, data_evento, hora_inicio, hora_fim nullable, tipo_local (`interno`,`externo`), endereco_local nullable, convidados, observacoes, status (`recebida`,`em_analise`,`convertida`,`recusada`,`cancelada`), preco_estimado decimal, calculo_snapshot jsonb, criado_em, timestamps. Considerar informações sobre LGPD e origem.
- `solicitacao_itens`: id, solicitacao_id, categoria_id, item_id, nome_snapshot, categoria_snapshot, quantidade opcional, valor_adicional_snapshot decimal.
- `solicitacao_adicionais`: id, solicitacao_id, adicional_id, nome_snapshot, tipo_snapshot, quantidade, preco_unitario_snapshot, subtotal_snapshot.
- `propostas`: id, empresa_id, solicitacao_id, status (`rascunho`,`enviada`,`aprovada`,`rejeitada`,`expirada`,`cancelada`), versao_atual, timestamps.
- `proposta_versoes`: id, proposta_id, numero, snapshot jsonb, valor_total decimal, validade_em, observacoes, condicoes_pagamento, token_hash (ou token dedicado associado à versão), enviada_em, aprovada_em nullable, rejeitada_em nullable, timestamps. Índice único (proposta_id,numero).
- `proposta_acoes`: id, proposta_versao_id, acao (`enviada`,`visualizada`,`aprovada`,`solicitada_alteracao`,`rejeitada`), ip_hash opcional, user_agent resumido opcional, nome_declarado nullable, email_declarado nullable, ocorrido_em. Não armazenar dados excessivos.
- `eventos`: id, empresa_id, proposta_versao_id, titulo, data_evento, inicio, fim nullable, convidados, status (`pre_reserva`,`confirmado`,`realizado`,`cancelado`), local, timestamps. Impedir duplicação acidental do mesmo aceite (índice único apropriado).

**Importante:** se for complexo para o MVP, `propostas` pode começar com versão única no banco, MAS manter estrutura de snapshots/versões desde o início é preferível para garantir histórico comercial. Não usar JSON como substituto de tudo: campos consultáveis devem ser colunas.

## 6. Motor de precificação

Implementar em **service/action** testável, por exemplo `CalcularOrcamento` com DTO validado. Não concentrar regras no controller ou componentes Vue.

1. Resolver pacote publicado e ativo no escopo da empresa.
2. Validar convidados inteiro positivo dentro dos limites do pacote.
3. Validar que todos os itens selecionados pertencem ao catálogo da empresa, ao pacote e à categoria correta; impedir itens repetidos; respeitar mínimo/máximo por categoria.
4. Validar adicionais permitidos no pacote e quantidades mínimas/máximas.
5. Calcular `base = (tipo por_pessoa ? convidados × preco_base : preco_base)`.
6. Calcular cobranças adicionais configuradas: `por_pessoa = convidados × preco_unitario × quantidade`, `por_evento = preco_unitario × quantidade`, `por_unidade = preco_unitario × quantidade`. **Definir explicitamente** como sobretaxas de itens escolhidos funcionam; padrão sugerido: sobretaxa **por pessoa** e configurável na futura versão. Não aplicar cobrança duplicada por itens incluídos.
7. `total_estimado = base + somatório adicionais + somatório sobretaxas`; arredondamento comercial de centavos bem definido.
8. Gerar **snapshot da regra efetivamente aplicada**: nomes, identificadores, valores, cálculo de cada linha, quantidade de convidados e versão/atualização do pacote. Persistir em transação junto da solicitação.
9. Preços e condições não devem ser alterados implicitamente por modificações futuras no catálogo.

**Segurança de concorrência:** em requisições públicas, usar regras e preços atuais dentro do mesmo contexto transacional consistente, evitando divergência entre cálculo e salvamento. Frontend pode consultar estimativa por endpoint com rate limit e a criação sempre recalcule antes de salvar.

## 7. Fluxos e status

### Solicitação
1. Cliente abre pacote publicado e informa dados.
2. Interface apresenta resumo e estimativa, com aviso "Sujeito à confirmação".
3. Cliente confirma; o backend valida e **recalcula**; registra solicitação e itens; retorna protocolo e estado `recebida`.
4. Administração recebe nova solicitação na fila e altera para `em_analise`.
5. Comercial confere disponibilidade, revisa custos/condições e cria proposta.

### Proposta
1. Usuário autorizado gera versão em `rascunho` a partir do snapshot da solicitação, ajustando valores e condições sob registro.
2. Ao `enviar`, gerar token seguro e validade; congelar conteúdo da versão enviada; registrar log.
3. Cliente abre URL única. O backend controla expiração/revogação; não expõe IDs sequenciais como credencial.
4. Cliente pode `aceitar`, `rejeitar` ou `solicitar alterações` (comentário). Aceite faz transição atômica para `aprovada` e registra nome/data/versão/IP minimizado conforme política de privacidade.
5. Nova revisão invalida a possibilidade de aceitar versões superadas e gera versão sequencial.
6. Após aceite, criar `pre_reserva` ou `aguardando assinatura/sinal` em estágio futuro. **Não converter diretamente para confirmado** sem as condições operacionais configuradas.

### Regras de estados
- Não aprovar proposta `rascunho`, `expirada`, `cancelada`, `rejeitada` ou versão superada.
- Proibir dupla aprovação e dupla criação de evento em requisições concorrentes.
- Expirar propostas após `validade_em` mesmo que job agendado ainda não tenha executado.
- Apenas usuários autorizados podem modificar propostas.
- Não permitir duas reservas confirmadas para o mesmo espaço/horário quando o módulo de espaços/capacidade estiver implementado. No MVP, deixar claro que datas precisam ser verificadas manualmente se ainda não existe controle de disponibilidade.

## 8. API/rotas (ajustar a web.php/rotas existentes)

**Públicas:**
- `GET /e/{empresa:slug}` → vitrine publicada.
- `GET /e/{empresa:slug}/pacotes/{pacote:slug}` → formulário.
- `POST /e/{empresa:slug}/pacotes/{pacote:slug}/simular` → cálculo, sem persistir solicitação; throttle.
- `POST /e/{empresa:slug}/pacotes/{pacote:slug}/solicitacoes` → criar solicitação; throttle e anti-spam.
- `GET /p/{token}` → versão de proposta liberada por token.
- `POST /p/{token}/aceitar` → aceite idempotente e auditado.
- `POST /p/{token}/alteracoes` → solicitar alteração.

**Administrativas:** usar prefixo e convenção de rotas atual do starter. Ex.: `/app/empresas/{empresa}/...`, protegido por `auth`, verificação de associação e Policies.
- CRUD de catálogo/categorias, pacotes, vínculos e adicionais.
- GET/visualização da fila e detalhe de solicitações.
- Ações controladas de análise, criação/envio/revisão/cancelamento de propostas.
- Visualização de eventos e relatórios básicos depois.

**Inertia:** páginas administrativas em Vue; formulários públicos também podem usar Inertia. Usar Form Requests para validação, recursos/DTOs para shape de respostas, flash messages e tratamento consistente de erros.

## 9. Organização sugerida

```text
app/
  Actions/Orcamentos/CalcularOrcamento.php
  Actions/Orcamentos/CriarSolicitacao.php
  Actions/Propostas/EnviarProposta.php
  Actions/Propostas/AceitarProposta.php
  Http/Controllers/Publico/...
  Http/Controllers/App/...
  Http/Requests/...
  Models/...
  Policies/...
  Services/...
resources/js/
  Pages/Publico/Empresa/...
  Pages/Publico/Orcamentos/...
  Pages/Publico/Propostas/...
  Pages/App/Catalogo/...
  Pages/App/Pacotes/...
  Pages/App/Solicitacoes/...
  Pages/App/Propostas/...
  Components/...
database/migrations/
database/factories/
database/seeders/
tests/Feature/
tests/Unit/
```

Respeitar o padrão real do starter (TypeScript/JavaScript, alias, layouts e convenções). Verificar Vuetify instalado/configurado antes de compor componentes. Não instalar libs adicionais sem justificativa.

## 10. UX detalhada da página pública

Passo 1: dados do evento (data, horário, quantidade, local interno/externo).  
Passo 2: seleção de pacote e cardápio com contador `2 de 2 carnes`, mensagens de restrição, imagens opcionais.  
Passo 3: adicionais com tipo/quantidade e preço visível.  
Passo 4: contato (nome, WhatsApp, e-mail) e observações; aviso de privacidade e consentimentos apropriados.  
Passo 5: revisão com memória do pacote, escolhas, valores discriminados e total estimado; envio; protocolo de confirmação.

**Requisitos:** validação inline, loading, empty state, erro de pacote inativo, acessibilidade por teclado, responsividade, máscara opcional de telefone sem depender dela no servidor, BRL formatado em pt-BR. Sem botão de pagamento/contrato definitivo na criação da solicitação.

## 11. Segurança e qualidade

- Policies e middleware para isolamento por empresa; testes explícitos de invasão cruzada (empresa A não vê/muda registros da B).
- CSRF nas rotas web; rate limiting público; honeypot ou antispam proporcional; campos validados no Form Request.
- Tokens de proposta aleatórios e longos; hash em repouso quando aplicável; expiração, revogação e proteção contra enumeração; evitar registrar tokens em logs.
- Não carregar scripts externos sem necessidade. Upload de logo/imagens com whitelist MIME/tamanho e armazenamento adequado.
- Transações para criar solicitação, gerar versão de proposta e aceitar; estado verificado atomicamente.
- Logs sem dados pessoais em excesso; autorização de downloads/PDF; backups em implantação.
- Separar testes unitários da precificação e testes de feature dos fluxos HTTP. Criar factories.

## 12. Testes de aceitação mínimos

1. Pacote Tradicional, 100 convidados, 2 carnes + 2 acompanhamentos permitidos: base e total R$ 7.500,00.
2. Alterar convidados para 120 gera R$ 9.000,00, sem adicionais.
3. Selecionar 3 carnes quando máximo 2: bloqueio no Vue **e** resposta de validação no Laravel.
4. Enviar item inexistente ou de outra empresa: rejeição.
5. Enviar preço forjado no request: servidor ignora e recalcula.
6. Adicional de R$ 12 por pessoa para 100 convidados: +R$ 1.200; adicional de R$ 250 por evento: +R$ 250; adicional por unidade com quantidade 3 e preço R$ 20: +R$ 60.
7. Desativar pacote impede novas solicitações, mas histórico permanece consultável.
8. Alterar preços após criação não altera snapshot existente.
9. Proposta enviada mantém preço original; revisão gera nova versão e invalida token antigo.
10. Proposta expirada não pode ser aprovada, mesmo sem job de expiração executado.
11. Dois POSTs simultâneos de aceite não criam eventos duplicados.
12. Dados de empresa A não podem ser lidos/alterados por usuário da empresa B.
13. Solicitação pública retorna protocolo e **não** marca evento confirmado.
14. Tests e build (`php artisan test`, `npm run build`) sem erros relevantes.

## 13. Plano de implementação para Codex — executar fase por fase

### Fase 0 — Diagnóstico do repositório
- Verificar framework, packages, Inertia, Vue, Vuetify, TypeScript, DB, autenticação, migrations existentes, rotas e testes.
- Informar lacunas e plano de alterações. Não deletar/recriar projeto.

### Fase 1 — Base multiempresa
- Migrations, modelos, relacionamentos, escopo/Policies, participação de usuários e empresa ativa validada.
- CRUD inicial e testes de isolamento.

### Fase 2 — Catálogo e pacotes
- CRUD de categorias/itens/adicionais/pacotes e regras mínimas/máximas.
- Formulários com Vuetify e validações no backend.
- Seeder de demonstração separado para o pacote Tradicional de R$ 75, 2 carnes e 2 acompanhamentos (somente ambientes não produtivos).

### Fase 3 — Página pública e solicitações
- Vitrine pública, personalização, endpoint de simulação, motor de preços, persistência transacional, painel de solicitações e testes do cenário de R$ 7.500.

### Fase 4 — Propostas e aprovação
- Editor de proposta, snapshots, versões, link com token, revisão/aceite idempotente, trilha de auditoria e testes.

### Fase 5 — Agenda e pré-reservas
- Calendário, fluxo de pré-reserva, regras de disponibilidade de espaço/horário e confirmação controlada.

### Fase 6 — Financeiro e contratos (futuro)
- Sinal, parcelas, despesas, geração de contrato via template, integração com assinatura eletrônica e cobrança. Analisar requisitos legais e de integração antes da implementação.

### Fase 7 — Custos e operação (futuro)
- Fichas técnicas, insumos, equipes, quantidades para compras, custos previstos versus reais e margem do evento.

## 14. Definição de pronto por fase

Uma fase só está pronta quando: migrations executam e podem ser revertidas; CRUD/fluxo correspondente funciona; autorização e validação cobertas por testes; frontend responsivo e com feedback de erros; seed demo controlado; build sem falhas; nenhuma regressão conhecida; documentação de comandos/rotas atualizada.

## 15. Prompt inicial para colar no Codex

> Leia integralmente `SAAS_GESTAO_EVENTOS_CODEX.md`. Estamos construindo um SaaS de gestão de eventos/buffets com Laravel 12, Vue 3, Inertia, Vuetify e PostgreSQL. **Execute apenas a Fase 0 agora:** inspecione a estrutura atual, identifique versões e lacunas, apresente plano de arquivos, migrations, modelos, Policies, telas e testes para a Fase 1. Não instale dependências nem altere arquivos nesta primeira interação. Não assuma que o Vuetify ou a autenticação já estão configurados; verifique. Depois aguarde minha instrução para implementar a Fase 1.

## 16. Prompt para iniciar a implementação após revisar o diagnóstico

> Usando `SAAS_GESTAO_EVENTOS_CODEX.md` e o diagnóstico realizado, implemente **somente a Fase 1 — Base multiempresa**. Siga as convenções existentes, crie migrations, modelos, associação usuário/empresa, verificação de empresa ativa e Policies, além de testes de isolamento. Evite alterações fora do escopo. Rode os testes e o build disponíveis; reporte os resultados reais e os comandos necessários para eu testar no VS Code.

