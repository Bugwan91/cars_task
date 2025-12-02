<script setup>
import { watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import { DEFAULT_CAR_IMAGE } from '@/constants/media';
import { ROUTES } from '@/constants/routes';
import CarCard from '@/Components/cars/CarCard.vue';
import HomeHeader from '@/Components/Layouts/HomeHeader.vue';
import useInfiniteCars from '@/composables/useInfiniteCars';

const props = defineProps({
    cars: {
        type: Object,
        required: true,
    },
    currency: {
        type: Object,
        default: () => ({})
    }
});

const defaultCarImage = DEFAULT_CAR_IMAGE;
const {
    cars: allCars,
    nextPageUrl,
    loading,
    loadMoreTrigger,
    reset: resetCars,
} = useInfiniteCars(props.cars.data, props.cars.links?.next ?? props.cars.next_page_url);

watch(
    () => props.cars,
    (updated) => {
        if (!updated) {
            return;
        }
        resetCars(updated.data ?? [], updated.links?.next ?? updated.next_page_url ?? null);
    }
);
</script>

<template>
    <Head title="Car Listing" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <HomeHeader :user="$page.props.auth.user" :currency="props.currency" />

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <CarCard
                        v-for="car in allCars"
                        :key="car.id"
                        :car="car"
                        :fallback-image="defaultCarImage"
                        :href="route(ROUTES.cars.show, car.id)"
                    />
                </div>

                <!-- Infinite Scroll Trigger -->
                <div ref="loadMoreTrigger" class="mt-8 flex justify-center py-4">
                    <div v-if="loading" class="h-8 w-8 animate-spin rounded-full border-4 border-indigo-500 border-t-transparent"></div>
                    <div v-else-if="!nextPageUrl" class="text-gray-500 dark:text-gray-400">
                        No more cars to load.
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
