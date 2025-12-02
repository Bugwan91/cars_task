<script setup>
import { ref, computed } from 'vue';
import FieldWrapper from '@/Components/forms/FieldWrapper.vue';
import InputError from '@/Components/InputError.vue';
import usePhotoUploader from '@/composables/usePhotoUploader';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    primaryIndex: {
        type: Number,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits(['update:modelValue', 'update:primaryIndex']);

const fileInput = ref(null);

const { previews, cleanupPreviews, setPrimary, removePhoto } = usePhotoUploader(
    () => props.modelValue,
    () => props.primaryIndex,
    emit
);

const photoError = computed(() => {
    if (props.errors.photos) {
        return props.errors.photos;
    }
    const specificKey = Object.keys(props.errors).find((key) => key.startsWith('photos.'));
    return specificKey ? props.errors[specificKey] : '';
});

const handleChange = (event) => {
    const files = Array.from(event.target.files || []);
    emit('update:modelValue', files);
    emit('update:primaryIndex', files.length ? 0 : null);
    if (event.target) {
        event.target.value = '';
    }
};

const reset = () => {
    cleanupPreviews();
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

defineExpose({ reset, removePhoto, setPrimary });
</script>

<template>
    <FieldWrapper label="Photos" for="photos">
        <input
            id="photos"
            ref="fileInput"
            type="file"
            accept="image/*"
            multiple
            class="mt-1 block w-full text-sm text-gray-900 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-indigo-700 hover:file:bg-indigo-100 dark:text-gray-200 dark:file:bg-gray-700 dark:file:text-gray-200"
            @change="handleChange"
        />
        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Upload up to 10 images. Choose one as the main photo for the listing.
        </p>
        <InputError class="mt-2" :message="photoError" />
        <InputError class="mt-1" :message="props.errors.primary_photo_index" />

        <div v-if="previews.length" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div
                v-for="(preview, index) in previews"
                :key="preview.id"
                class="rounded-md border border-gray-200 p-2 dark:border-gray-700"
            >
                <img :src="preview.url" :alt="preview.name" class="h-24 w-full rounded object-cover" />
                <p class="mt-2 truncate text-xs text-gray-500 dark:text-gray-400">
                    {{ preview.name }}
                </p>
                <div class="mt-2 flex items-center justify-between text-xs">
                    <button
                        type="button"
                        class="rounded-full px-2 py-1"
                        :class="props.primaryIndex === index ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        @click="setPrimary(index)"
                    >
                        {{ props.primaryIndex === index ? 'Main photo' : 'Set as main' }}
                    </button>
                    <button
                        type="button"
                        class="text-red-500 hover:text-red-600"
                        @click="removePhoto(index)"
                    >
                        Remove
                    </button>
                </div>
            </div>
        </div>
    </FieldWrapper>
</template>
