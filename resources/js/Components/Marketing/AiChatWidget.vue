<script setup lang="ts">
import { computed, nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import axios from 'axios';

type ChatRole = 'user' | 'assistant';

type ChatMessage = {
    role: ChatRole;
    content: string;
};

type ChatCta = {
    type: string;
    label: string;
    href?: string;
};

type SavedChat = {
    expiresAt: number;
    sessionId: string;
    messages: ChatMessage[];
    lastCta: ChatCta | null;
};

const CHAT_STORAGE_KEY = 'ai_chat_saved_v3';
const CHAT_TTL_MS = 1000 * 60 * 60 * 24;
const CHAT_MAX_MESSAGES = 40;

const isOpen = ref(false);
const isSending = ref(false);
const input = ref('');
const activeSection = ref<string | null>(null);
const teaser = ref<{ visible: boolean; text: string }>({ visible: false, text: '' });
const lastCta = ref<ChatCta | null>(null);
const hasUserInteracted = ref(false);
const messages = ref<ChatMessage[]>([
    {
        role: 'assistant',
        content: 'Oi! Eu sou o agente da MedidaTek. Em que posso te ajudar? Se precisar, estou à disposição.',
    },
]);

const sessionId = ref<string>(
    typeof crypto !== 'undefined' && 'randomUUID' in crypto
        ? crypto.randomUUID()
        : `${Date.now()}-${Math.random().toString(16).slice(2)}`,
);

const canSend = computed(() => input.value.trim().length > 0 && !isSending.value);

function saveChat() {
    if (typeof window === 'undefined') return;
    if (!('localStorage' in window)) return;

    const trimmedMessages = messages.value
        .slice(-CHAT_MAX_MESSAGES)
        .map((m) => ({ role: m.role, content: String(m.content ?? '').slice(0, 8000) }));

    const payload: SavedChat = {
        expiresAt: Date.now() + CHAT_TTL_MS,
        sessionId: sessionId.value,
        messages: trimmedMessages,
        lastCta: lastCta.value,
    };

    try {
        window.localStorage.setItem(CHAT_STORAGE_KEY, JSON.stringify(payload));
    } catch {
    }
}

function loadChat() {
    if (typeof window === 'undefined') return;
    if (!('localStorage' in window)) return;

    try {
        const raw = window.localStorage.getItem(CHAT_STORAGE_KEY);
        if (!raw) return;
        const parsed = JSON.parse(raw) as Partial<SavedChat>;

        if (!parsed || typeof parsed !== 'object') return;
        if (typeof parsed.expiresAt !== 'number' || parsed.expiresAt <= Date.now()) {
            window.localStorage.removeItem(CHAT_STORAGE_KEY);
            return;
        }

        if (typeof parsed.sessionId === 'string' && parsed.sessionId.trim() !== '') {
            sessionId.value = parsed.sessionId;
        }

        if (Array.isArray(parsed.messages) && parsed.messages.length > 0) {
            const restored = parsed.messages
                .filter((m: any) => m && (m.role === 'user' || m.role === 'assistant') && typeof m.content === 'string')
                .slice(-CHAT_MAX_MESSAGES)
                .map((m: any) => ({ role: m.role as ChatRole, content: m.content as string }));
            if (restored.length > 0) {
                messages.value = restored;
            }
        }

        if (parsed.lastCta && typeof parsed.lastCta === 'object' && typeof (parsed.lastCta as any).label === 'string') {
            lastCta.value = {
                type: String((parsed.lastCta as any).type ?? ''),
                label: String((parsed.lastCta as any).label ?? ''),
                href: (parsed.lastCta as any).href ? String((parsed.lastCta as any).href) : undefined,
            };
        }
    } catch {
    }
}

function manualOpenChat(message?: string) {
    hasUserInteracted.value = true;
    sessionStorage.setItem('ai_chat_manual_opened', '1');
    openChat({ auto: false, message });
}

function scrollToBottom() {
    const el = document.getElementById('ai-chat-scroll');
    if (el) el.scrollTop = el.scrollHeight;
}

function scrollToMessageStart(index: number) {
    const el = document.getElementById(`ai-msg-${index}`);
    if (!el) return;
    el.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function openChat(options?: { reason?: string; message?: string; auto?: boolean }) {
    teaser.value.visible = false;
    isOpen.value = true;

    if (options?.message) {
        const last = messages.value[messages.value.length - 1];
        if (!last || last.content !== options.message) {
            messages.value.push({ role: 'assistant', content: options.message });
        }
    }

    nextTick().then(() => {
        if (options?.message) {
            scrollToMessageStart(messages.value.length - 1);
            return;
        }
        scrollToBottom();
    });
}

function nudge(text: string) {
    if (isOpen.value) return;
    teaser.value = { visible: true, text };
    window.setTimeout(() => {
        teaser.value.visible = false;
    }, 9000);
}

function goToContact() {
    isOpen.value = false;
    const el = document.getElementById('contato');
    if (el) {
        el.scrollIntoView({ behavior: 'smooth', block: 'start' });
    } else {
        window.location.hash = '#contato';
    }
}

async function send() {
    if (!canSend.value) return;

    hasUserInteracted.value = true;
    const text = input.value.trim();
    input.value = '';
    messages.value.push({ role: 'user', content: text });
    isSending.value = true;

    await nextTick();
    scrollToBottom();

    try {
        const resp = await axios.post(route('ai.chat'), {
            session_id: sessionId.value,
            message: text,
            context: {
                path: window.location.pathname,
                section: activeSection.value,
                referrer: document.referrer || null,
                utm: Object.fromEntries(new URLSearchParams(window.location.search)),
            },
        });

        const reply = String(resp.data?.reply ?? '').trim();
        const cta = resp.data?.cta;
        if (cta && typeof cta === 'object' && typeof cta.label === 'string' && typeof cta.type === 'string') {
            lastCta.value = { type: String(cta.type), label: String(cta.label), href: cta.href ? String(cta.href) : undefined };
        }
        const assistantIndex = messages.value.length;
        messages.value.push({
            role: 'assistant',
            content: reply !== '' ? reply : 'Consigo te ajudar. Qual é a sua prioridade agora?',
        });
        await nextTick();
        scrollToMessageStart(assistantIndex);
    } catch {
        const assistantIndex = messages.value.length;
        messages.value.push({
            role: 'assistant',
            content: 'Tive um problema para responder agora. Você pode me dizer em 1 frase o que precisa?',
        });
        await nextTick();
        scrollToMessageStart(assistantIndex);
    } finally {
        isSending.value = false;
        await nextTick();
    }
}

let io: IntersectionObserver | null = null;
let openHandler: ((e: Event) => void) | null = null;
let nudgeHandler: ((e: Event) => void) | null = null;

onMounted(() => {
    loadChat();

    openHandler = (e: Event) => {
        const ev = e as CustomEvent<{ message?: string }>;
        manualOpenChat(ev.detail?.message);
    };
    nudgeHandler = (e: Event) => {
        const ev = e as CustomEvent<{ text?: string }>;
        if (ev.detail?.text) nudge(ev.detail.text);
    };

    window.addEventListener('ai-chat-open', openHandler as EventListener);
    window.addEventListener('ai-chat-nudge', nudgeHandler as EventListener);

    const sections = Array.from(document.querySelectorAll<HTMLElement>('main section[id]'));
    if (sections.length > 0 && 'IntersectionObserver' in window) {
        io = new IntersectionObserver(
            (entries) => {
                const visible = entries
                    .filter((x) => x.isIntersecting)
                    .sort((a, b) => (b.intersectionRatio ?? 0) - (a.intersectionRatio ?? 0))[0];
                if (!visible?.target) return;
                const id = (visible.target as HTMLElement).id || null;
                if (id) activeSection.value = id;
            },
            { threshold: [0.25, 0.4, 0.55] },
        );
        sections.forEach((s) => io?.observe(s));
    }
});

watch(
    messages,
    () => {
        saveChat();
    },
    { deep: true },
);

watch(lastCta, () => {
    saveChat();
});

watch(isOpen, (open) => {
    if (open) {
        teaser.value.visible = false;
    }
});

onBeforeUnmount(() => {
    if (io) io.disconnect();
    if (openHandler) window.removeEventListener('ai-chat-open', openHandler as EventListener);
    if (nudgeHandler) window.removeEventListener('ai-chat-nudge', nudgeHandler as EventListener);
});
</script>

<template>
    <div class="fixed bottom-5 right-5 z-50">
        <div
            v-if="isOpen"
            class="w-[340px] overflow-hidden rounded-2xl border border-white/10 bg-zinc-950/95 text-white shadow-2xl backdrop-blur"
        >
            <div class="flex items-center justify-between gap-3 border-b border-white/10 px-4 py-3">
                <div class="min-w-0">
                    <div class="truncate text-sm font-semibold">MedidaTek IA</div>
                    <div class="truncate text-xs text-white/60">Guia Especialista</div>
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
                        :id="`ai-msg-${idx}`"
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
                    @focus="hasUserInteracted = true"
                />
                <button
                    type="submit"
                    class="rounded-xl bg-indigo-500 px-3 py-2 text-sm font-semibold text-white hover:bg-indigo-400 disabled:opacity-50"
                    :disabled="!canSend"
                >
                    Enviar
                </button>
            </form>

            <div v-if="lastCta?.label" class="border-t border-white/10 px-3 py-3">
                <button
                    type="button"
                    class="w-full rounded-xl bg-white px-3 py-2 text-sm font-semibold text-black hover:bg-zinc-200 transition-colors"
                    @click="goToContact"
                >
                    {{ lastCta.label }}
                </button>
                <div class="mt-2 text-[11px] text-white/45">
                    Vamos construir? Leva 30 segundos para enviar e eu retorno com perguntas certeiras.
                </div>
            </div>
        </div>

        <div v-else class="flex flex-col items-end gap-2">
            <div
                v-if="teaser.visible"
                class="max-w-[260px] rounded-2xl border border-white/10 bg-zinc-950/90 px-3 py-2 text-xs text-white/80 shadow-2xl backdrop-blur"
            >
                {{ teaser.text }}
            </div>
            <button
                type="button"
                class="group flex items-center gap-2 rounded-full bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-xl hover:bg-indigo-500"
                @click="manualOpenChat()"
            >
                <span class="rounded-full bg-white/15 px-2 py-0.5 text-xs">IA</span>
                <span>Falar agora</span>
            </button>
        </div>
    </div>
</template>
