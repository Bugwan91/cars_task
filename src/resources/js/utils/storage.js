export const storageUrl = (path) => {
    if (!path) {
        return '';
    }
    return `/storage/${path.replace(/^\/+/, '')}`;
};

export const photoUrlOrFallback = (photo, fallback) => {
    if (photo?.photo_path) {
        return storageUrl(photo.photo_path);
    }
    return fallback;
};
