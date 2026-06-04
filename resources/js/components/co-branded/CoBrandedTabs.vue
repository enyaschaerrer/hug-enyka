<script setup lang="ts" generic="K extends string">
defineProps<{
    tabs: { key: K; label: string }[];
    active: K;
    primaryColor?: string | null;
}>();

defineEmits<{
    change: [key: K];
}>();
</script>

<template>
    <nav class="flex flex-wrap items-center justify-center gap-2 px-4 py-6 lg:gap-3" role="tablist">
        <template v-for="(tab, idx) in tabs" :key="tab.key">
            <button
                type="button"
                role="tab"
                :aria-selected="active === tab.key"
                :class="[
                    'rounded-full px-4 py-2 text-body font-semibold transition lg:px-6 lg:py-3',
                    active === tab.key
                        ? 'bg-fuzzywuzzybrown-600 text-white'
                        : 'bg-merino-200 text-catskillwhite-800 hover:bg-merino-300',
                ]"
                :style="active === tab.key && primaryColor ? { backgroundColor: primaryColor } : undefined"
                @click="$emit('change', tab.key)"
            >
                {{ tab.label }}
            </button>
            <span
                v-if="idx < tabs.length - 1"
                class="hidden h-0.5 w-4 bg-merino-300 lg:block lg:w-6"
                aria-hidden="true"
            ></span>
        </template>
    </nav>
</template>
