<script setup>
import { useSlots } from 'vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
});

const slots = useSlots();
</script>

<template>
    <header class="bg-white shadow dark:bg-gray-800">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                {{ props.title }}
            </h1>
            <div v-if="slots.default" class="flex flex-wrap items-center gap-4">
                <slot />
            </div>
        </div>
    </header>
</template>
