<script setup>
import { Link } from '@inertiajs/vue3';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import CurrencySwitcher from '@/Components/cars/CurrencySwitcher.vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    user: {
        type: Object,
        default: null,
    },
    currency: {
        type: Object,
        default: () => ({}),
    },
});
</script>

<template>
    <PageHeader title="Available Cars">
        <div class="flex flex-wrap items-center gap-4">
            <CurrencySwitcher :currency="props.currency" />
            <Link
                :href="route(ROUTES.cars.create)"
                class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600"
            >
                Add Car
            </Link>
        </div>
        <nav v-if="props.user">
            <Link
                :href="route(ROUTES.dashboard)"
                class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white"
            >
                Dashboard
            </Link>
        </nav>
    </PageHeader>
</template>
