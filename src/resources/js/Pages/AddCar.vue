<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import OptionPicker from '@/Components/cars/OptionPicker.vue';
import PhotoUploader from '@/Components/cars/PhotoUploader.vue';
import PageHeader from '@/Components/Layouts/PageHeader.vue';
import FormField from '@/Components/forms/FormField.vue';
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
});

const photoUploaderRef = ref(null);

const submit = () => {
    form.post(route(ROUTES.cars.storeWeb), {
        forceFormData: true,
        onSuccess: () => {
            form.reset();
            photoUploaderRef.value?.reset();
        },
    });
};
</script>

<template>
    <Head title="Add Car" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <PageHeader title="Add New Car">
            <Link
                :href="route(ROUTES.home)"
                class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300"
            >
                Back to Home
            </Link>
        </PageHeader>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6 max-w-xl">
                        <FormField
                            id="brand"
                            label="Brand"
                            v-model="form.brand"
                            :error="form.errors.brand"
                            required
                            autofocus
                        />

                        <FormField
                            id="model"
                            label="Model"
                            v-model="form.model"
                            :error="form.errors.model"
                            required
                        />

                        <FormField
                            id="year"
                            label="Year"
                            type="number"
                            v-model="form.year"
                            :error="form.errors.year"
                            required
                        />

                        <FormField
                            id="price"
                            label="Price"
                            type="number"
                            step="0.01"
                            v-model="form.price"
                            :error="form.errors.price"
                        />

                        <FormField
                            id="description"
                            label="Description"
                            v-model="form.description"
                            :error="form.errors.description"
                            textarea
                            :rows="4"
                        />

                        <OptionPicker
                            v-model="form.options"
                            :suggestions="props.availableOptions"
                            :error="form.errors.options"
                        />

                        <PhotoUploader
                            ref="photoUploaderRef"
                            v-model="form.photos"
                            v-model:primaryIndex="form.primary_photo_index"
                            :errors="form.errors"
                        />

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Save Car
                            </PrimaryButton>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
