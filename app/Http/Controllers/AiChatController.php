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
                    'label' => 'Solicitar orçamento',
                    'href' => '#contato',
                ],
            ]);
        }

        $system = 'Você é um assistente de pré-vendas da MedidaTeck. Responda em pt-BR, com objetividade, sem promessas inventadas. Faça 1 pergunta por vez quando necessário e direcione para o próximo passo.';
        $user = $message;

        $payload = [
            'model' => $model,
            'messages' => [
                ['role' => 'system', 'content' => $system],
                ['role' => 'user', 'content' => $this->decorateWithContext($user, $context)],
            ],
            'temperature' => 0.4,
            'max_tokens' => 250,
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
                        'label' => 'Solicitar orçamento',
                        'href' => '#contato',
                    ],
                ], 200);
            }

            $json = $resp->json();
            $reply = (string) data_get($json, 'choices.0.message.content', '');
            $usage = (array) data_get($json, 'usage', []);

            AiAuditLog::create([
                'endpoint' => 'ai.chat',
                'session_id' => $sessionId,
                'model' => $model,
                'prompt_tokens' => Arr::get($usage, 'prompt_tokens'),
                'completion_tokens' => Arr::get($usage, 'completion_tokens'),
                'total_tokens' => Arr::get($usage, 'total_tokens'),
                'latency_ms' => $latencyMs,
                'status' => 'ok',
                'meta' => [
                    'context' => $context,
                ],
            ]);

            return response()->json([
                'reply' => $reply !== '' ? $reply : $this->stubReply($message),
                'cta' => [
                    'type' => 'form',
                    'label' => 'Solicitar orçamento',
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
                    'label' => 'Solicitar orçamento',
                    'href' => '#contato',
                ],
            ]);
        }
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
