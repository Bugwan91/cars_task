<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import CarForm from '@/Components/cars/CarForm.vue';
import { ROUTES } from '@/constants/routes';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
    availableOptions: {
        type: Array,
        default: () => [],
    },
});

const existingPhotos = ref([...(props.car.photos ?? [])]);

const initialOptions = computed(() => (props.car.options ?? []).map((option) => option.name ?? option));

const form = useForm({
    brand: props.car.brand ?? '',
    model: props.car.model ?? '',
    year: props.car.year ?? '',
    price: props.car.price ?? '',
    description: props.car.description ?? '',
    options: initialOptions.value,
    photos: [],
    primary_photo_index: null,
    primary_photo_id: props.car.photos?.find((photo) => photo.is_primary)?.id ?? null,
    removed_photo_ids: [],
});

const carFormRef = ref(null);

const breadcrumbs = computed(() => [
    {
        label: `${props.car.brand} ${props.car.model}`,
        href: route(ROUTES.cars.show, props.car.id),
    },
    {
        label: 'Edit',
    },
]);

const syncRemovedIds = (photoId) => {
    if (!photoId) {
        return;
    }
    if (!form.removed_photo_ids.includes(photoId)) {
        form.removed_photo_ids.push(photoId);
    }
};

const handleRemoveExisting = (photoId) => {
    syncRemovedIds(photoId);
    existingPhotos.value = existingPhotos.value.filter((photo) => photo.id !== photoId);

    if (form.primary_photo_id === photoId) {
        form.primary_photo_id = existingPhotos.value[0]?.id ?? null;
    }
};

const handleSetPrimaryExisting = (photoId) => {
    form.primary_photo_id = photoId;
};

watch(
    () => form.primary_photo_id,
    (value) => {
        if (value !== null) {
            form.primary_photo_index = null;
        }
    }
);

watch(
    () => form.primary_photo_index,
    (value) => {
        if (value !== null) {
            form.primary_photo_id = null;
        }
    }
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        _method: 'PUT',
    })).post(route(ROUTES.cars.updateWeb, props.car.id), {
        forceFormData: true,
        onSuccess: () => {
            form.photos = [];
            form.primary_photo_index = null;
            form.removed_photo_ids = [];
            carFormRef.value?.resetPhotos();
        },
    });
};
</script>

<template>
    <Head :title="`Edit ${props.car.brand} ${props.car.model}`" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <PageHeader :title="`Edit ${props.car.brand} ${props.car.model}`" :breadcrumbs="breadcrumbs" />

        <main>
            <div class="mx-auto max-w-5xl py-6 sm:px-6 lg:px-8 space-y-6">
                <CarForm
                    ref="carFormRef"
                    :form="form"
                    :available-options="availableOptions"
                    :existing-photos="existingPhotos"
                    :primary-photo-id="form.primary_photo_id"
                    submit-label="Update Car"
                    :processing="form.processing"
                    @submit="submit"
                    @remove-existing="handleRemoveExisting"
                    @set-primary-existing="handleSetPrimaryExisting"
                />
            </div>
        </main>
    </div>
</template>
