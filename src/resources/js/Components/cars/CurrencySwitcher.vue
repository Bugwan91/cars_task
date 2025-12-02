<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    currency: {
        type: Object,
        default: () => ({}),
    },
});

const available = computed(() => props.currency?.available ?? []);
const selected = computed(() => props.currency?.selected ?? null);

const visitWithCurrency = (code) => {
    if (!code || code === selected.value) {
        return;
    }

    const current = new URL(window.location.href);
    current.searchParams.set('currency', code);

    router.visit(`${current.pathname}${current.search}`, {
        preserveScroll: true,
        preserveState: true,
    });
};
</script>

<template>
    <div v-if="available.length" class="flex items-center gap-2">
        <span class="text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            Currency
        </span>
        <div class="flex overflow-hidden rounded-lg border border-gray-200 dark:border-gray-700">
            <button
                v-for="code in available"
                :key="code"
                type="button"
                class="px-3 py-1 text-xs font-semibold uppercase tracking-wide transition"
                :class="[
                    selected === code
                        ? 'bg-indigo-600 text-white'
                        : 'bg-transparent text-gray-600 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800',
                ]"
                @click="visitWithCurrency(code)"
            >
                {{ code }}
            </button>
        </div>
    </div>
</template>
