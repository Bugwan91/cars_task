import { ref, watch, onBeforeUnmount } from 'vue';

export default function usePhotoUploader(filesSource, primaryIndexSource, emit) {
    const previews = ref([]);
    const localPrimary = ref(primaryIndexSource());

    const cleanupPreviews = () => {
        previews.value.forEach((preview) => URL.revokeObjectURL(preview.url));
        previews.value = [];
    };

    const syncPreviews = (files = []) => {
        cleanupPreviews();
        previews.value = files.map((file, index) => ({
            id: `${file.name}-${file.lastModified}-${index}`,
            url: URL.createObjectURL(file),
            name: file.name,
            size: file.size,
        }));
    };

    watch(filesSource, (files) => {
        syncPreviews(files || []);
    }, { immediate: true });

    watch(primaryIndexSource, (next) => {
        localPrimary.value = next;
    });

    const setPrimary = (index) => {
        if (!previews.value.length) {
            return;
        }
        emit('update:primaryIndex', index);
    };

    const removePhoto = (index) => {
        const files = Array.from(filesSource() || []);
        files.splice(index, 1);
        emit('update:modelValue', files);

        if (!files.length) {
            emit('update:primaryIndex', null);
        } else if (localPrimary.value === index) {
            emit('update:primaryIndex', 0);
        } else if ((localPrimary.value ?? 0) > index) {
            emit('update:primaryIndex', Math.max((localPrimary.value ?? 0) - 1, 0));
        }
    };

    onBeforeUnmount(() => cleanupPreviews());

    return {
        previews,
        cleanupPreviews,
        setPrimary,
        removePhoto,
    };
}
