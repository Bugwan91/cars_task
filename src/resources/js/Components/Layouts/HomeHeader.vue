<script setup>
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
});
</script>

<template>
    <PageHeader title="Available Cars">
        <Link
            :href="route(ROUTES.cars.create)"
            class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
        >
            Add Car
        </Link>
        <nav v-if="props.user">
            <Link
                :href="route(ROUTES.dashboard)"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                Dashboard
            </Link>
        </nav>
        <nav v-else class="flex items-center gap-4">
            <Link
                :href="route(ROUTES.auth.login)"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                Log in
            </Link>
            <Link
                :href="route(ROUTES.auth.register)"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                Register
            </Link>
        </nav>
    </PageHeader>
</template>
