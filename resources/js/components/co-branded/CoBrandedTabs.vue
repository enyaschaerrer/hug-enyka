<script setup lang="ts" generic="K extends string">
import { nextTick, ref, watch } from 'vue';

const props = defineProps<{
    tabs: { key: K; label: string }[];
    active: K;
    primaryColor?: string | null;
    secondaryColor?: string | null;
}>();

const emit = defineEmits<{
    change: [key: K];
}>();

const scroller = ref<HTMLElement | null>(null);
const tabButtons = ref<HTMLElement[]>([]);

function setTabButtonRef(el: Element | any, idx: number) {
    if (el instanceof HTMLElement) tabButtons.value[idx] = el;
}

// Scroll horizontal manuel sur le conteneur — n'affecte pas le scroll du body
function scrollToTab(idx: number) {
    const btn = tabButtons.value[idx];
    const el = scroller.value;
    if (!btn || !el) return;

    const btnCenter = btn.offsetLeft + btn.offsetWidth / 2;
    const containerCenter = el.clientWidth / 2;
    const maxScroll = el.scrollWidth - el.clientWidth;
    const target = Math.max(0, Math.min(maxScroll, btnCenter - containerCenter));

    el.scrollTo({ left: target, behavior: 'smooth' });
}

function handleTabClick(_event: MouseEvent, key: K, idx: number) {
    scrollToTab(idx);
    emit('change', key);
}

// Quand `active` change depuis l'extérieur, on recentre le tab dans le scroll
watch(() => props.active, async () => {
    await nextTick();
    const idx = props.tabs.findIndex(t => t.key === props.active);
    scrollToTab(idx);
});
</script>

<template>
    <nav class="px-2 pt-6 pb-2 lg:px-4" role="tablist">
        <div
            ref="scroller"
            class="flex items-center gap-2 overflow-x-auto scroll-smooth px-2 lg:justify-center lg:gap-3 lg:px-0 [scrollbar-width:none] [&amp;::-webkit-scrollbar]:hidden"
        >
            <template v-for="(tab, idx) in tabs" :key="tab.key">
                <button
                    :ref="(el) => setTabButtonRef(el, idx)"
                    type="button"
                    role="tab"
                    :aria-selected="active === tab.key"
                    :class="[
                        'shrink-0 rounded-full px-4 py-2 text-body font-medium lg:text-heading-t3 transition lg:px-6 lg:py-3',
                        active === tab.key
                            ? 'text-white'
                            : 'text-black',
                    ]"
                    :style="active === tab.key
                        ? { backgroundColor: primaryColor ?? 'var(--color-razzmatazz-700)' }
                        : { backgroundColor: secondaryColor ?? 'var(--color-catskillwhite-200)' }"
                    @click="handleTabClick($event, tab.key, idx)"
                >
                    {{ tab.label }}
                </button>
                <span
                    v-if="idx < tabs.length - 1"
                    class="h-0.5 w-4 shrink-0 bg-catskillwhite-300 lg:w-6"
                    aria-hidden="true"
                ></span>
            </template>
        </div>
    </nav>
</template>
