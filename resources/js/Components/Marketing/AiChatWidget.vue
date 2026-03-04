<script setup lang="ts">
import { computed, nextTick, ref } from 'vue';
import axios from 'axios';

type ChatRole = 'user' | 'assistant';

type ChatMessage = {
    role: ChatRole;
    content: string;
};

const isOpen = ref(false);
const isSending = ref(false);
const input = ref('');
const messages = ref<ChatMessage[]>([
    {
        role: 'assistant',
        content: 'Oi! Eu sou o assistente da MedidaTeck. Qual é o seu desafio principal hoje?',
    },
]);

const sessionId = ref<string>(
    typeof crypto !== 'undefined' && 'randomUUID' in crypto
        ? crypto.randomUUID()
        : `${Date.now()}-${Math.random().toString(16).slice(2)}`,
);

const canSend = computed(() => input.value.trim().length > 0 && !isSending.value);

async function send() {
    if (!canSend.value) return;

    const text = input.value.trim();
    input.value = '';
    messages.value.push({ role: 'user', content: text });
    isSending.value = true;

    await nextTick();

    try {
        const resp = await axios.post(route('ai.chat'), {
            session_id: sessionId.value,
            message: text,
            context: {
                path: window.location.pathname,
                referrer: document.referrer || null,
                utm: Object.fromEntries(new URLSearchParams(window.location.search)),
            },
        });

        const reply = String(resp.data?.reply ?? '').trim();
        messages.value.push({
            role: 'assistant',
            content: reply !== '' ? reply : 'Consigo te ajudar. Qual é a sua prioridade agora?',
        });
    } catch {
        messages.value.push({
            role: 'assistant',
            content: 'Tive um problema para responder agora. Você pode me dizer em 1 frase o que precisa?',
        });
    } finally {
        isSending.value = false;
        await nextTick();
        const el = document.getElementById('ai-chat-scroll');
        if (el) el.scrollTop = el.scrollHeight;
    }
}
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50">
        <div
            v-if="isOpen"
            class="w-[340px] overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 text-white shadow-2xl backdrop-blur"
        >
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold">MedidaTeck IA</div>
                    <div class="truncate text-xs text-white/60">Pré-vendas e recomendação</div>
                </div>
                <button
                    type="button"
                    class="rounded-lg px-2 py-1 text-xs text-white/70 hover:bg-white/10 hover:text-white"
                    @click="isOpen = false"
                >
                    Fechar
                </button>
            </div>

            <div id="ai-chat-scroll" class="max-h-[360px] space-y-3 overflow-auto px-4 py-4">
                <div
                    v-for="(m, idx) in messages"
                    :key="idx"
                    class="flex"
                    :class="m.role === 'user' ? 'justify-end' : 'justify-start'"
                >
                    <div
                        class="max-w-[85%] rounded-2xl px-3 py-2 text-sm leading-relaxed"
                        :class="
                            m.role === 'user'
                                ? 'bg-indigo-500 text-white'
                                : 'bg-white/10 text-white'
                        "
                    >
                        {{ m.content }}
                    </div>
                </div>
                <div v-if="isSending" class="text-xs text-white/60">Digitando...</div>
            </div>

            <form class="flex gap-2 border-t border-white/10 p-3" @submit.prevent="send">
                <input
                    v-model="input"
                    type="text"
                    class="w-full rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm text-white placeholder:text-white/40 focus:border-indigo-400 focus:outline-none focus:ring-2 focus:ring-indigo-400/30"
                    placeholder="Escreva sua pergunta..."
                />
                <button
                    type="submit"
                    class="rounded-xl bg-indigo-500 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                    :disabled="!canSend"
                >
                    Enviar
                </button>
            </form>
        </div>

        <button
            v-else
            type="button"
            class="group flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-xl hover:bg-indigo-500"
            @click="isOpen = true"
        >
            <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">IA</span>
            <span>Falar agora</span>
        </button>
    </div>
</template>

