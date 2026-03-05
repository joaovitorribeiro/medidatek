<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\AiAuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Http;

class AiChatController extends Controller
{
    public function chat(Request $request)
    {
        $validated = $request->validate([
            'session_id' => ['nullable', 'string', 'max:64'],
            'message' => ['required', 'string', 'max:1000'],
            'context' => ['nullable', 'array'],
        ]);

        $start = microtime(true);
        $apiKey = env('OPENAI_API_KEY');
        $model = env('OPENAI_MODEL', 'gpt-4o-mini');
        $message = $validated['message'];
        $sessionId = $validated['session_id'] ?? null;
        $context = $validated['context'] ?? [];

        if (!is_string($apiKey) || $apiKey === '') {
            AiAuditLog::create([
                'endpoint' => 'ai.chat',
                'session_id' => $sessionId,
                'model' => $model,
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                'status' => 'stub',
                'meta' => [
                    'context' => $context,
                ],
            ]);

            return response()->json([
                'reply' => $this->stubReply($message),
                'cta' => [
                    'type' => 'form',
                    'label' => 'Vamos construir?',
                    'href' => '#contato',
                ],
            ]);
        }

        $system = implode("\n", [
            'Você é o agente de pré-vendas da MedidaTek, com performance de vendedor consultivo top-tier.',
            'Você só fala do que está ligado à MedidaTek e ao conteúdo do site. Não invente features, cases, números, certificações, parcerias ou garantias.',
            'Responda em pt-BR, com clareza, segurança e foco em conversão ética.',
            'Importante: não se apresente e não repita saudação (o widget já faz isso). Vá direto ao ponto.',
            '',
            'Objetivo principal:',
            '- Qualificar o lead com poucas perguntas e recomendar o próximo passo (MVP vs versão 1.0 vs enterprise).',
            '- Reduzir fricção e direcionar para o formulário de contato (#contato) quando houver fit.',
            '',
            'Conhecimento do site (use como verdade do que oferecemos):',
            '- Proposta: tecnologia sob medida para crescer; método com execução + processo + clareza de escopo + padrão de engenharia.',
            '- Método (etapas): Diagnóstico; Design & UX; Build; Evolução.',
            '- Infraestrutura (pilares): Arquitetura Escalável; Velocidade Real (99/100 Google PSI); IA Nativa; Design System; Mobile-First Real (iOS & Android, PWA Ready); Segurança Enterprise (Blindado, LGPD, backups automáticos).',
            '- O que criamos/integramos: site/landing, e-commerce/loja, marketplace, portal do cliente, área de membros, SaaS, app/PWA, pedidos, orçamentos/propostas, CRM/ERP, BI, WhatsApp, automações, APIs/webhooks, notificações.',
            '- Pagamentos/checkout: PIX, cartão, boleto, assinaturas, split; gateways como STRIPE, MERCADO PAGO, ASAAS, PAGSEGURO, IUGU, PAYPAL.',
            '',
            'Limites e segurança:',
            '- Não peça nem aceite dados sensíveis (senhas, chaves, tokens, dados bancários, documentos pessoais).',
            '- Se o usuário pedir algo fora do escopo do site, redirecione educadamente para o que a MedidaTek entrega.',
            '- Em temas regulatórios (fintech), não dê aconselhamento jurídico/contábil; foque em arquitetura e produto.',
            '',
            'Técnica de vendas (aplique sem citar nomes):',
            '- Comece validando e enquadrando o objetivo do usuário (1 frase).',
            '- Em seguida, dê 2–3 opções objetivas (ex.: MVP vs V1 vs enterprise) conectadas ao que temos na página.',
            '- Faça 1 pergunta de alta alavancagem por vez (dor, volume, integrações, prazo, faixa de investimento).',
            '- Use linguagem de benefício (resultado) e prova de processo (método/infra), não hype.',
            '- Quando houver fit (o usuário descreveu o objetivo ou pediu solução/preço/integrações), convide para clicar em "Vamos construir?" e preencher o formulário para orçamento.',
            '',
            'Formato:',
            '- 3–7 linhas no máximo.',
            '- Termine sempre com 1 pergunta específica.',
        ]);
        $user = $message;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $this->decorateWithContext($user, $context)],
            ],
            'temperature' => 0.4,
            'max_tokens' => 300,
        ];

        try {
            $resp = Http::timeout(20)
                ->withToken($apiKey)
                ->post('https://api.openai.com/v1/chat/completions', $payload);

            $latencyMs = (int) round((microtime(true) - $start) * 1000);

            if (!$resp->ok()) {
                AiAuditLog::create([
                    'endpoint' => 'ai.chat',
                    'session_id' => $sessionId,
                    'model' => $model,
                    'latency_ms' => $latencyMs,
                    'status' => 'error',
                    'error_message' => (string) $resp->body(),
                    'meta' => [
                        'http_status' => $resp->status(),
                        'context' => $context,
                    ],
                ]);

                return response()->json([
                    'reply' => $this->stubReply($message),
                    'cta' => [
                        'type' => 'form',
                        'label' => 'Vamos construir?',
                        'href' => '#contato',
                    ],
                ], 200);
            }

            $json = $resp->json();
            $reply = (string) data_get($json, 'choices.0.message.content', '');
            $usage = (array) data_get($json, 'usage', []);
            $costUsd = $this->calculateUsdCostFromUsage($usage);

            AiAuditLog::create([
                'endpoint' => 'ai.chat',
                'session_id' => $sessionId,
                'model' => $model,
                'prompt_tokens' => Arr::get($usage, 'prompt_tokens'),
                'completion_tokens' => Arr::get($usage, 'completion_tokens'),
                'total_tokens' => Arr::get($usage, 'total_tokens'),
                'latency_ms' => $latencyMs,
                'status' => 'ok',
                'cost_usd' => $costUsd,
                'meta' => [
                    'context' => $context,
                ],
            ]);

            return response()->json([
                'reply' => $reply !== '' ? $reply : $this->stubReply($message),
                'cta' => [
                    'type' => 'form',
                    'label' => 'Vamos construir?',
                    'href' => '#contato',
                ],
            ]);
        } catch (\Throwable $e) {
            AiAuditLog::create([
                'endpoint' => 'ai.chat',
                'session_id' => $sessionId,
                'model' => $model,
                'latency_ms' => (int) round((microtime(true) - $start) * 1000),
                'status' => 'exception',
                'error_message' => $e->getMessage(),
                'meta' => [
                    'context' => $context,
                ],
            ]);

            return response()->json([
                'reply' => $this->stubReply($message),
                'cta' => [
                    'type' => 'form',
                    'label' => 'Vamos construir?',
                    'href' => '#contato',
                ],
            ]);
        }
    }

    private function calculateUsdCostFromUsage(array $usage): ?float
    {
        $promptTokens = (int) (Arr::get($usage, 'prompt_tokens') ?? 0);
        $completionTokens = (int) (Arr::get($usage, 'completion_tokens') ?? 0);
        if ($promptTokens <= 0 && $completionTokens <= 0) {
            return null;
        }

        $inputUsdPerToken = ((float) config('ai.pricing.input_usd_per_million', 0.15)) / 1_000_000;
        $outputUsdPerToken = ((float) config('ai.pricing.output_usd_per_million', 0.60)) / 1_000_000;

        $cost = ($promptTokens * $inputUsdPerToken) + ($completionTokens * $outputUsdPerToken);
        return round($cost, 6);
    }

    private function decorateWithContext(string $message, array $context): string
    {
        $ctx = Arr::only($context, ['utm', 'path', 'section', 'referrer']);
        if ($ctx === []) {
            return $message;
        }

        return $message."\n\nContexto (não repetir literalmente): ".json_encode($ctx, JSON_UNESCAPED_UNICODE);
    }

    private function stubReply(string $message): string
    {
        $m = mb_strtolower($message);

        if (str_contains($m, 'preço') || str_contains($m, 'valor') || str_contains($m, 'orçamento')) {
            return 'Consigo te ajudar. Para estimar com precisão, me diga: qual processo você quer automatizar e qual resultado você quer melhorar (vendas, produtividade, controle)?';
        }

        if (str_contains($m, 'crm') || str_contains($m, 'hubspot') || str_contains($m, 'mailchimp')) {
            return 'Sim, fazemos integrações com CRMs e automação. Você usa qual ferramenta hoje e qual dado precisa sincronizar (leads, clientes, pagamentos, pipeline)?';
        }

        if (str_contains($m, 'ia') || str_contains($m, 'chat') || str_contains($m, 'gpt')) {
            return 'A IA entra para triagem, recomendações e automações com segurança (sem dados sensíveis). Qual é o seu caso de uso: atendimento, recomendações ou análise de comportamento?';
        }

        return 'Perfeito. Em 1 frase: qual é o seu desafio principal hoje (vendas, processo interno, integração, atendimento)?';
    }
}
