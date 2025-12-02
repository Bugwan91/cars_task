<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import CarGallery from '@/Components/cars/CarGallery.vue';
import CarDetails from '@/Components/cars/CarDetails.vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
});

const displayTitle = computed(() => `${props.car.brand ?? ''} ${props.car.model ?? ''}`.trim() || 'Car Details');
const photos = computed(() => props.car.photos ?? []);
const activePhotoIndex = ref(null);
</script>

<template>
    <Head :title="displayTitle" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <PageHeader :title="displayTitle">
            <Link
                :href="route(ROUTES.home)"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
            >
                Back to listings
            </Link>
        </PageHeader>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8 space-y-6">
                <section class="grid gap-6 lg:grid-cols-2">
                    <CarGallery v-model:index="activePhotoIndex" :photos="photos" :title="displayTitle" />

                    <CarDetails :car="props.car" />
                </section>
            </div>
        </main>
    </div>
</template>
