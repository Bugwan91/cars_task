<script setup>
import { ref, computed } from 'vue';
import FieldWrapper from '@/Components/forms/FieldWrapper.vue';
import InputError from '@/Components/InputError.vue';
import usePhotoUploader from '@/composables/usePhotoUploader';
import { DEFAULT_CAR_IMAGE } from '@/constants/media';
import { photoUrlOrFallback } from '@/utils/storage';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    primaryIndex: {
        type: Number,
        default: null,
    },
    existingPhotos: {
        type: Array,
        default: () => [],
    },
    primaryPhotoId: {
        type: Number,
        default: null,
    },
    errors: {
        type: Object,
        default: () => ({}),
    },
});

const emit = defineEmits([
    'update:modelValue',
    'update:primaryIndex',
    'remove-existing',
    'set-primary-existing',
]);

const fileInput = ref(null);

const { previews, cleanupPreviews, setPrimary, removePhoto } = usePhotoUploader(
    () => props.modelValue,
    () => props.primaryIndex,
    emit
);

const hasExistingPhotos = computed(() => (props.existingPhotos ?? []).length > 0);

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

    if (!files.length) {
        emit('update:primaryIndex', null);
    } else if (!hasExistingPhotos.value && props.primaryPhotoId === null) {
        emit('update:primaryIndex', 0);
    }

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

const combinedPhotos = computed(() => {
    const existing = (props.existingPhotos ?? []).map((photo) => ({
        key: `existing-${photo.id}`,
        type: 'existing',
        id: photo.id,
        url: photoUrlOrFallback(photo, DEFAULT_CAR_IMAGE, { preferThumbnail: true }),
        name: photo.photo_path ?? `Photo-${photo.id}`,
        isPrimary: props.primaryPhotoId === photo.id,
    }));

    const fresh = previews.value.map((preview, index) => ({
        key: `new-${preview.id}`,
        type: 'new',
        index,
        url: preview.url,
        name: preview.name,
        isPrimary: props.primaryPhotoId === null && props.primaryIndex === index,
    }));

    return [...existing, ...fresh];
});

const handleSetPrimary = (photo) => {
    if (photo.type === 'existing') {
        emit('set-primary-existing', photo.id);
        return;
    }
    setPrimary(photo.index);
};

const handleRemove = (photo) => {
    if (photo.type === 'existing') {
        emit('remove-existing', photo.id);
        return;
    }
    removePhoto(photo.index);
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
        <div v-if="combinedPhotos.length" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3">
            <div
                v-for="photo in combinedPhotos"
                :key="photo.key"
                class="rounded-md border border-gray-200 p-2 dark:border-gray-700"
            >
                <img :src="photo.url" :alt="photo.name" class="h-24 w-full rounded object-cover" loading="lazy" decoding="async" />
                <div class="mt-2 flex items-center justify-between text-xs">
                    <p class="truncate text-gray-500 dark:text-gray-400">
                        {{ photo.name }}
                    </p>
                    <span
                        v-if="photo.type === 'new' && hasExistingPhotos"
                        class="ml-2 rounded-full bg-emerald-100 px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wide text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200"
                    >
                        New
                    </span>
                </div>
                <div class="mt-2 flex items-center justify-between text-xs">
                    <button
                        type="button"
                        class="rounded-full px-2 py-1"
                        :class="photo.isPrimary ? 'bg-indigo-600 text-white' : 'bg-gray-100 text-gray-700 dark:bg-gray-700 dark:text-gray-200'"
                        @click="handleSetPrimary(photo)"
                    >
                        {{ photo.isPrimary ? 'Main photo' : 'Set as main' }}
                    </button>
                    <button type="button" class="text-red-500 hover:text-red-600" @click="handleRemove(photo)">
                        Remove
                    </button>
                </div>
            </div>
        </div>
        <p v-else class="mt-2 text-sm text-gray-500 dark:text-gray-400">No photos yet. Start by uploading one above.</p>
    </FieldWrapper>
</template>
