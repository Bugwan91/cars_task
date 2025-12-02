<script setup>
import { ref, computed, watch } from 'vue';
import { DEFAULT_CAR_IMAGE } from '@/constants/media';
import { photoUrlOrFallback } from '@/utils/storage';

const props = defineProps({
    photos: {
        type: Array,
        default: () => [],
    },
    title: {
        type: String,
        default: 'Car photo',
    },
    fallback: {
        type: String,
        default: DEFAULT_CAR_IMAGE,
    },
    index: {
        type: Number,
        default: null,
    },
});

const emit = defineEmits(['update:index']);

const photoList = computed(() => props.photos ?? []);
const hasPhotos = computed(() => photoList.value.length > 0);
const showGalleryControls = computed(() => photoList.value.length > 1);

const photoUrl = (photo) => photoUrlOrFallback(photo, props.fallback);

const resolveInitialIndex = () => {
    if (!hasPhotos.value) {
        return null;
    }
    const primaryIndex = photoList.value.findIndex((photo) => photo.is_primary);
    return primaryIndex >= 0 ? primaryIndex : 0;
};

const resolveStartIndex = () => {
    if (props.index !== null && props.index !== undefined) {
        return props.index;
    }
    return resolveInitialIndex();
};

const activePhotoIndex = ref(resolveStartIndex());

const syncWithPhotos = () => {
    const fallbackIndex = resolveInitialIndex();
    if (fallbackIndex === null) {
        if (activePhotoIndex.value !== null) {
            activePhotoIndex.value = null;
            emit('update:index', null);
        }
        return;
    }
    if (activePhotoIndex.value === null || activePhotoIndex.value >= photoList.value.length) {
        activePhotoIndex.value = fallbackIndex;
        emit('update:index', activePhotoIndex.value);
    }
};

watch(photoList, () => {
    syncWithPhotos();
});

watch(
    () => props.index,
    (next) => {
        if (next === null || next === undefined) {
            activePhotoIndex.value = resolveInitialIndex();
        } else if (next >= 0 && next < photoList.value.length) {
            activePhotoIndex.value = next;
        }
    }
);

const activePhotoUrl = computed(() => {
    if (activePhotoIndex.value === null || !photoList.value[activePhotoIndex.value]) {
        return props.fallback;
    }
    return photoUrl(photoList.value[activePhotoIndex.value]);
});

const updateIndex = (index) => {
    activePhotoIndex.value = index;
    emit('update:index', index);
};

const setActivePhoto = (index) => {
    if (!photoList.value[index]) {
        return;
    }
    updateIndex(index);
};

const cyclePhoto = (direction) => {
    if (!showGalleryControls.value || activePhotoIndex.value === null) {
        return;
    }
    const count = photoList.value.length;
    updateIndex((activePhotoIndex.value + direction + count) % count);
};
</script>

<template>
    <div class="bg-white p-4 shadow sm:rounded-lg dark:bg-gray-800">
        <div class="relative">
            <img
                :src="activePhotoUrl"
                :alt="`${props.title} photo ${activePhotoIndex !== null ? activePhotoIndex + 1 : ''}`"
                class="h-96 w-full rounded-md object-cover"
            />
            <button
                v-if="showGalleryControls"
                type="button"
                class="absolute left-4 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-700 shadow hover:bg-white dark:bg-gray-900/80 dark:text-gray-200"
                @click="cyclePhoto(-1)"
                aria-label="Previous photo"
            >
                ‹
            </button>
            <button
                v-if="showGalleryControls"
                type="button"
                class="absolute right-4 top-1/2 -translate-y-1/2 rounded-full bg-white/80 p-2 text-gray-700 shadow hover:bg-white dark:bg-gray-900/80 dark:text-gray-200"
                @click="cyclePhoto(1)"
                aria-label="Next photo"
            >
                ›
            </button>
        </div>

        <p v-if="!hasPhotos" class="mt-4 text-sm text-gray-500 dark:text-gray-400">
            No photos uploaded for this car yet.
        </p>

        <div
            v-if="photoList.length > 1"
            class="mt-4 grid grid-cols-4 gap-3 sm:grid-cols-6"
        >
            <button
                v-for="(photo, index) in photoList"
                :key="photo.id ?? `${photo.photo_path}-${index}`"
                type="button"
                class="rounded-md border p-1"
                :class="activePhotoIndex === index ? 'border-indigo-500 ring-2 ring-indigo-500' : 'border-gray-200 dark:border-gray-700'"
                @click="setActivePhoto(index)"
            >
                <img :src="photoUrl(photo)" :alt="`Thumbnail ${index + 1}`" class="h-16 w-full rounded object-cover" />
            </button>
        </div>
    </div>
</template>
