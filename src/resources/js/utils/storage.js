export const storageUrl = (path) => {
    if (!path) {
        return '';
    }
    return `/storage/${path.replace(/^\/+/, '')}`;
};

export const photoUrlOrFallback = (photo, fallback, options = {}) => {
    const { preferThumbnail = false } = options;

    if (preferThumbnail && photo?.thumbnail_path) {
        return storageUrl(photo.thumbnail_path);
    }

    if (photo?.photo_path) {
        return storageUrl(photo.photo_path);
    }

    return fallback;
};
