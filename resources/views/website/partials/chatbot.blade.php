<div x-data="raSchoolChat()" x-init="init()" class="fixed bottom-5 right-5 z-[60]">
    {{-- Floating button --}}
    <button @click="open = !open"
            class="w-14 h-14 rounded-full shadow-lg flex items-center justify-center text-2xl text-white transition-transform hover:scale-105"
            style="background:var(--primary)">
        <span x-show="!open">💬</span>
        <span x-show="open" x-cloak>✕</span>
    </button>

    {{-- Chat window --}}
    <div x-show="open" x-cloak x-transition
         class="absolute bottom-16 right-0 w-[90vw] max-w-sm h-[70vh] max-h-[520px] bg-white rounded-2xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden">
        <div class="px-4 py-3 text-white flex items-center gap-2" style="background:var(--primary)">
            <span class="text-lg">🦅</span>
            <div>
                <p class="font-bold text-sm leading-tight">{{ $school->school_name }}</p>
                <p class="text-xs text-white/70 leading-tight">Ask us anything</p>
            </div>
        </div>
        <div class="flex-1 overflow-y-auto px-4 py-3 space-y-3" x-ref="messageList">
            <template x-for="(m, i) in messages" :key="i">
                <div :class="m.role === 'user' ? 'text-right' : 'text-left'">
                    <span class="inline-block px-3 py-2 rounded-xl text-sm max-w-[85%]"
                          :class="m.role === 'user' ? 'text-white' : 'bg-gray-100 text-gray-800'"
                          :style="m.role === 'user' ? 'background:var(--primary)' : ''"
                          x-text="m.content"></span>
                </div>
            </template>
            <div x-show="loading" x-cloak class="text-left">
                <span class="inline-block px-3 py-2 rounded-xl text-sm bg-gray-100 text-gray-400">Typing…</span>
            </div>
        </div>
        <form @submit.prevent="send" class="border-t border-gray-100 p-3 flex gap-2">
            <input type="text" x-model="input" placeholder="Type your question…"
                   class="flex-1 px-3 py-2 border border-gray-200 rounded-full text-sm focus:outline-none focus:ring-2"
                   :disabled="loading">
            <button type="submit" class="w-9 h-9 rounded-full text-white flex items-center justify-center flex-shrink-0"
                    style="background:var(--primary)" :disabled="loading || !input.trim()">➤</button>
        </form>
    </div>
</div>

<script>
function raSchoolChat() {
    return {
        open: false,
        loading: false,
        input: '',
        sessionId: null,
        messages: [
            { role: 'assistant', content: "Hi! I'm the {{ $school->school_name }} assistant. Ask me about admissions, programs, or anything else about the school." }
        ],
        init() {
            this.sessionId = localStorage.getItem('ra_chat_session') || null;
            // Force a clean, closed state on every page show — including a
            // browser back-forward-cache restore, which freezes and later
            // replays the exact JS state a page was in when the visitor left
            // (e.g. window still open, a request still marked "loading"),
            // without re-running any script.
            window.addEventListener('pageshow', () => {
                this.open = false;
                this.loading = false;
            });
        },
        async send() {
            const text = this.input.trim();
            if (!text || this.loading) return;
            this.messages.push({ role: 'user', content: text });
            this.input = '';
            this.loading = true;
            this.scrollToBottom();
            try {
                const res = await fetch("{{ route('website.chat') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-XSRF-TOKEN': decodeURIComponent((document.cookie.match(/XSRF-TOKEN=([^;]+)/) || [])[1] || ''),
                    },
                    body: JSON.stringify({ message: text, session_id: this.sessionId }),
                });
                const data = await res.json();
                if (data.session_id) {
                    this.sessionId = data.session_id;
                    localStorage.setItem('ra_chat_session', data.session_id);
                }
                this.messages.push({ role: 'assistant', content: data.reply || "Sorry, something went wrong." });
            } catch (e) {
                this.messages.push({ role: 'assistant', content: "Sorry, I couldn't reach the server. Please try again." });
            }
            this.loading = false;
            this.scrollToBottom();
        },
        scrollToBottom() {
            this.$nextTick(() => {
                if (this.$refs.messageList) this.$refs.messageList.scrollTop = this.$refs.messageList.scrollHeight;
            });
        }
    };
}
</script>
