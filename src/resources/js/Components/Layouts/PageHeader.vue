<script setup>
import { Link } from '@inertiajs/vue3';
import { useSlots } from 'vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    breadcrumbs: {
        type: Array,
        default: () => [],
    },
});

const slots = useSlots();
</script>

<template>
    <header class="bg-white shadow-sm dark:bg-gray-900">
        <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <nav class="flex flex-wrap items-center gap-3 text-sm font-medium text-gray-500 dark:text-gray-400" aria-label="Breadcrumb">
                    <Link
                        :href="route(ROUTES.home)"
                        class="text-indigo-600 transition hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-200"
                    >
                        Home
                    </Link>
                    <template v-if="props.breadcrumbs.length" v-for="(crumb, index) in props.breadcrumbs" :key="`crumb-${index}`">
                        <span class="text-gray-300 dark:text-gray-600">/</span>
                        <component
                            :is="crumb.href ? Link : 'span'"
                            v-bind="crumb.href ? { href: crumb.href } : {}"
                            class="truncate"
                            :class="crumb.href
                                ? 'text-indigo-600 transition hover:text-indigo-800 dark:text-indigo-300 dark:hover:text-indigo-200'
                                : 'text-gray-600 dark:text-gray-300'"
                        >
                            {{ crumb.label }}
                        </component>
                    </template>
                    <template v-else>
                        <span class="text-gray-300 dark:text-gray-600">/</span>
                        <span class="truncate text-gray-600 dark:text-gray-300">{{ props.title }}</span>
                    </template>
                </nav>
                <div v-if="slots.default" class="flex flex-wrap items-center gap-3">
                    <slot />
                </div>
            </div>

            <div class="mt-4 flex flex-wrap items-end justify-between gap-4">
                <h1 class="text-3xl font-semibold tracking-tight text-gray-900 dark:text-white">
                    {{ props.title }}
                </h1>
            </div>
        </div>
    </header>
</template>
