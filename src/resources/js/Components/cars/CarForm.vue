<script setup>
import { ref, Transition } from 'vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import OptionPicker from '@/Components/cars/OptionPicker.vue';
import PhotoUploader from '@/Components/cars/PhotoUploader.vue';
import FormField from '@/Components/forms/FormField.vue';
import { BASE_CURRENCY } from '@/constants/currency';

const props = defineProps({
    form: {
        type: Object,
        required: true,
    },
    availableOptions: {
        type: Array,
        default: () => [],
    },
    submitLabel: {
        type: String,
        default: 'Save',
    },
    processing: {
        type: Boolean,
        default: false,
    },
    existingPhotos: {
        type: Array,
        default: () => [],
    },
    primaryPhotoId: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['submit', 'remove-existing', 'set-primary-existing']);

const photoUploaderRef = ref(null);
const priceHelperText = `Enter the amount in ${BASE_CURRENCY}; prices are stored in ${BASE_CURRENCY}.`;

const handleSubmit = () => emit('submit');
const handleRemoveExisting = (photoId) => emit('remove-existing', photoId);
const handleSetPrimaryExisting = (photoId) => emit('set-primary-existing', photoId);

const resetPhotos = () => photoUploaderRef.value?.reset();

defineExpose({ resetPhotos });
</script>

<template>
    <div class="bg-white p-6 shadow sm:rounded-lg dark:bg-gray-800">
        <form @submit.prevent="handleSubmit" class="space-y-6">
            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="brand"
                    label="Brand"
                    v-model="props.form.brand"
                    :error="props.form.errors.brand"
                    required
                />
                <FormField
                    id="model"
                    label="Model"
                    v-model="props.form.model"
                    :error="props.form.errors.model"
                    required
                />
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <FormField
                    id="year"
                    label="Year"
                    type="number"
                    v-model="props.form.year"
                    :error="props.form.errors.year"
                    required
                />
                <FormField
                    id="price"
                    label="Price"
                    type="number"
                    step="0.01"
                    v-model="props.form.price"
                    :error="props.form.errors.price"
                    :helper="priceHelperText"
                    required
                />
            </div>

            <FormField
                id="description"
                label="Description"
                v-model="props.form.description"
                :error="props.form.errors.description"
                textarea
                :rows="4"
            />

            <OptionPicker
                v-model="props.form.options"
                :suggestions="props.availableOptions"
                :error="props.form.errors.options"
            />

            <PhotoUploader
                ref="photoUploaderRef"
                v-model="props.form.photos"
                v-model:primaryIndex="props.form.primary_photo_index"
                :existing-photos="props.existingPhotos"
                :primary-photo-id="props.primaryPhotoId"
                :errors="props.form.errors"
                @remove-existing="handleRemoveExisting"
                @set-primary-existing="handleSetPrimaryExisting"
            />

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="props.processing">
                    {{ props.submitLabel }}
                </PrimaryButton>

                <Transition
                    enter-active-class="transition ease-in-out"
                    enter-from-class="opacity-0"
                    leave-active-class="transition ease-in-out"
                    leave-to-class="opacity-0"
                >
                    <p v-if="props.form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">
                        Saved.
                    </p>
                </Transition>
            </div>
        </form>
    </div>
</template>
