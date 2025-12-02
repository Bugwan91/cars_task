<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import CarForm from '@/Components/cars/CarForm.vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    availableOptions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    brand: '',
    model: '',
    year: '',
    price: '',
    description: '',
    options: [],
    photos: [],
    primary_photo_index: null,
    primary_photo_id: null,
});

const carFormRef = ref(null);

const submit = () => {
    form.post(route(ROUTES.cars.storeWeb), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            form.primary_photo_index = null;
            form.primary_photo_id = null;
            carFormRef.value?.resetPhotos();
        },
    });
};
</script>

<template>
    <Head title="Add Car" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <PageHeader title="Add New Car" />

        <main>
            <div class="mx-auto max-w-5xl py-6 sm:px-6 lg:px-8">
                <CarForm
                    ref="carFormRef"
                    :form="form"
                    :available-options="props.availableOptions"
                    submit-label="Save Car"
                    :processing="form.processing"
                    @submit="submit"
                />
            </div>
        </main>
    </div>
</template>
