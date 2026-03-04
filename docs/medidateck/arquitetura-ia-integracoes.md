# MedidaTeck — Blueprint Técnico (IA + Integrações)

## Objetivos técnicos
- Integrar IA de forma segura, rastreável e com custo controlado.
- Transformar interações (chat, formulário, navegação) em dados acionáveis para conversão.
- Conectar captação de lead a CRM/automação e permitir evolução contínua.

## Princípios
- IA como “copiloto” e automação: sempre ligada a benefício mensurável.
- Segurança por padrão: minimização de dados, rate limit, auditoria e consentimento.
- Observabilidade: métricas de uso, custo por funcionalidade e taxa de conversão.
- Modularidade: trocar provedor de IA e integrações sem refatorar o produto inteiro.

## Funcionalidades de IA (escopo recomendado)

### 1) Chat de pré-vendas (site)
Finalidade: triagem do visitante, entendimento de contexto e direcionamento para CTA/formulário.
- Entradas:
  - Mensagem do visitante.
  - Contexto da página (seção atual, origem UTM, idioma).
  - “Cardápio” de serviços (para o chat sugerir corretamente).
- Saídas:
  - Resposta curta e objetiva.
  - Sugestão de próximo passo: CTA, formulário, WhatsApp.
  - (Opcional) resumo estruturado para o time comercial.

### 2) Recomendação de serviço/escopo (site)
Finalidade: sugerir o serviço mais adequado com base em respostas rápidas.
- Entradas:
  - Segmento, tamanho da operação, ferramentas atuais, dor principal.
- Saídas:
  - Recomendação com justificativa e microcopy do CTA.

### 3) Personalização de copy (controlada)
Finalidade: adaptar microcopy do hero/CTA conforme contexto (UTM, segmento, dor), sem quebrar consistência.
- Regras:
  - Catálogo de variações aprovadas (human-in-the-loop).
  - IA só escolhe ou ajusta dentro de limites; não inventa promessas.

### 4) Insights de comportamento (analytics → ações)
Finalidade: detectar atritos (ex.: usuários abandonando no formulário) e sugerir melhorias.
- Importante:
  - Separar “tracking” (GA/Hotjar) de “insight” (IA em lote).
  - Processar agregados, não dados pessoais sempre que possível.

## Arquitetura sugerida (Laravel)

### Camadas
- Controllers: endpoints HTTP para chat/personalização/leads.
- Services:
  - `AiProvider` (interface) + implementações (OpenAI/compatíveis).
  - `PromptCatalog` (prompts versionados e parametrizáveis).
  - `LeadRouter` (CRM/automação: HubSpot/Mailchimp).
- Jobs/Queue:
  - Envio de lead para CRM.
  - Geração de resumo do chat para comercial.
  - Geração de insights em lote.
- Storage:
  - `leads` (dados do formulário, consentimentos, origem).
  - `chat_sessions` (id anônimo, timestamps, resumo, custo estimado).
  - `ai_audit_logs` (evento, modelo, tokens, status, latência, custo).

### Endpoints (contratos mínimos)

#### POST /ai/chat
Uso: widget de chat.
- Request (exemplo)
  - `session_id`: string (uuid)
  - `message`: string
  - `context`: object (utm, path, locale, current_section)
- Response (exemplo)
  - `reply`: string
  - `cta`: { `type`: "form"|"whatsapp"|"link", `label`: string, `href`?: string }
  - `summary`?: { `need`: string, `industry`?: string, `urgency`?: string }

#### POST /ai/recommendation
Uso: “2–4 perguntas” para recomendar serviço.
- Request: respostas estruturadas.
- Response: recomendação + próximos passos.

#### GET /ai/copy?variant=hero
Uso: retornar microcopy com base em contexto (somente variações aprovadas).
- Response: `{ headline, subheadline, primaryCtaLabel, proofHint }`

#### POST /leads
Uso: formulário principal da landing.
- Requisitos:
  - Validação forte (spam/honeypot, rate limit, reCAPTCHA opcional).
  - Persistência local + enfileirar integração com CRM.

## Segurança e compliance (mínimo necessário)
- Consentimento:
  - Banner de cookies/analytics.
  - Consentimento explícito para contato via WhatsApp/e-mail.
- Minimização:
  - Não enviar dados sensíveis para IA.
  - No chat: solicitar apenas contexto necessário.
- Rate limit:
  - Por IP e por session_id.
  - Proteção de endpoint público.
- Auditoria:
  - Registrar evento, latência, tokens/custo estimado e status.
  - Retenção com política (ex.: 30–90 dias para conteúdo do chat).
- Segredos:
  - Chaves do provedor de IA e CRMs em variáveis de ambiente.
  - Rotação de tokens com confirmação forte no painel administrativo.

## Painel administrativo (visibilidade obrigatória)
- IA:
  - Uso por dia (requisições, tokens, custo estimado, latência).
  - Taxa de erro por endpoint/modelo.
  - “Top intents” do chat e conversão pós-chat.
- Integrações:
  - Status de conexão (CRM/automação).
  - Últimas falhas e retries de jobs.
  - Ação de rotacionar token com confirmação explícita.

## Observabilidade e métricas
- Técnicas:
  - Logs estruturados por request_id.
  - Métricas: p95 latência, erro %, custo por 1000 sessões, cache hit.
- Negócio:
  - Conversão por origem (UTM).
  - Conversão por versão de copy (quando houver experimento).
  - Efeito do chat: % que clica em CTA após conversa.

## Integrações (CRM/Automação)
- Padrão:
  - Persistir lead localmente.
  - Enfileirar envio ao CRM (retry/backoff).
  - Confirmar sucesso e gravar `external_id`.
- Mapeamento:
  - Campos: nome, e-mail, WhatsApp, empresa, dor principal, mensagem, origem (utm), consentimento.
- Alertas:
  - Notificar falhas consecutivas (Slack/e-mail) após N tentativas.

## Custos e controle
- Estratégias:
  - Mensagens curtas e respostas objetivas.
  - Cache para copy personalizada por contexto.
  - Modelos diferentes por tarefa (chat vs copy vs resumo).
  - Limites por sessão e por dia (budget guardrails).

