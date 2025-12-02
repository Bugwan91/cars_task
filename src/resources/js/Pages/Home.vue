<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted } from 'vue';
import axios from 'axios';
import { DEFAULT_CAR_IMAGE } from '@/constants/media';

const props = defineProps({
    cars: {
        type: Object,
        required: true,
    },
});

const allCars = ref(props.cars.data);
const nextPageUrl = ref(props.cars.next_page_url);
const loading = ref(false);
const observer = ref(null);
const loadMoreTrigger = ref(null);
const defaultCarImage = DEFAULT_CAR_IMAGE;

const loadMoreCars = async () => {
    if (loading.value || !nextPageUrl.value) return;

    loading.value = true;

    try {
        const response = await axios.get(nextPageUrl.value);
        allCars.value = [...allCars.value, ...response.data.data];
        nextPageUrl.value = response.data.next_page_url;
    } catch (error) {
        console.error('Error loading more cars:', error);
    } finally {
        loading.value = false;
    }
};

onMounted(() => {
    observer.value = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting) {
            loadMoreCars();
        }
    }, {
        rootMargin: '100px',
    });

    if (loadMoreTrigger.value) {
        observer.value.observe(loadMoreTrigger.value);
    }
});

onUnmounted(() => {
    if (observer.value) {
        observer.value.disconnect();
    }
});
</script>

<template>
    <Head title="Car Listing" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Available Cars
                </h1>
                <div class="flex items-center gap-4">
                    <Link :href="route('cars.create')" class="rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                        Add Car
                    </Link>
                    <nav v-if="$page.props.auth.user">
                    <Link :href="route('dashboard')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Dashboard
                    </Link>
                </nav>
                <nav v-else>
                    <Link :href="route('login')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white mr-4">
                        Log in
                    </Link>
                    <Link :href="route('register')" class="text-gray-600 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Register
                    </Link>
                </nav>
            </div>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-3">
                    <div v-for="car in allCars" :key="car.id" class="overflow-hidden rounded-lg bg-white shadow dark:bg-gray-800">
                        <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
                            <img
                                :src="car.photos && car.photos.length ? `/storage/${car.photos[0].photo_path}` : defaultCarImage"
                                :alt="car.brand + ' ' + car.model"
                                class="h-48 w-full object-cover"
                                loading="lazy"
                            />
                        </div>
                        <div class="p-6">
                            <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                                {{ car.brand }} {{ car.model }}
                            </h2>
                            <p class="mt-2 text-gray-600 dark:text-gray-300">
                                Year: {{ car.year }}
                            </p>
                            <p class="mt-1 text-lg font-bold text-indigo-600 dark:text-indigo-400">
                                ${{ car.price }}
                            </p>
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 line-clamp-3">
                                {{ car.description }}
                            </p>
                            <div v-if="car.options && car.options.length" class="mt-4 flex flex-wrap gap-2">
                                <span
                                    v-for="option in car.options"
                                    :key="`${car.id}-${option.id ?? option.name}`"
                                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                                >
                                    {{ option.name }}
                                </span>
                            </div>
                        </div>
                    </div>
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
