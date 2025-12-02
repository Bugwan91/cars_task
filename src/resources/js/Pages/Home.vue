<script setup>
import { Head } from '@inertiajs/vue3';
import { DEFAULT_CAR_IMAGE } from '@/constants/media';
import CarCard from '@/Components/cars/CarCard.vue';
import HomeHeader from '@/Components/Layouts/HomeHeader.vue';
import useInfiniteCars from '@/composables/useInfiniteCars';

const props = defineProps({
    cars: {
        type: Object,
        required: true,
    },
});

const defaultCarImage = DEFAULT_CAR_IMAGE;
const {
    cars: allCars,
    nextPageUrl,
    loading,
    loadMoreTrigger,
} = useInfiniteCars(props.cars.data, props.cars.next_page_url);
</script>

<template>
    <Head title="Car Listing" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <HomeHeader :user="$page.props.auth.user" />

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <CarCard v-for="car in allCars" :key="car.id" :car="car" :fallback-image="defaultCarImage" />
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
