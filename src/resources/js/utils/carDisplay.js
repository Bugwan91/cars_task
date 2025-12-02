import { DEFAULT_DISPLAY_CURRENCY } from '@/constants/currency';
import { photoUrlOrFallback, storageUrl } from '@/utils/storage';
const FORMATTER_LOCALE = 'en-US';
const formatterCache = new Map();

const getFormatter = (currency = DEFAULT_DISPLAY_CURRENCY) => {
    const normalizedCurrency = (currency || DEFAULT_DISPLAY_CURRENCY).toUpperCase();

    if (!formatterCache.has(normalizedCurrency)) {
        formatterCache.set(
            normalizedCurrency,
            new Intl.NumberFormat(FORMATTER_LOCALE, {
                style: 'currency',
                currency: normalizedCurrency,
                minimumFractionDigits: 0,
                maximumFractionDigits: 0,
            })
        );
    }

    return formatterCache.get(normalizedCurrency);
};

export const getPrimaryPhotoUrl = (car, fallbackImage) => {
    if (car?.photos?.length) {
        const [primaryPhoto] = car.photos;
        return photoUrlOrFallback(primaryPhoto, fallbackImage, { preferThumbnail: true });
    }
    return fallbackImage;
};

export const formatCarPrice = (price, currency = DEFAULT_DISPLAY_CURRENCY) => {
    if (price === null || price === undefined || price === '') {
        return 'N/A';
    }

    const numericPrice = Number(price);

    if (Number.isNaN(numericPrice)) {
        return 'N/A';
    }

    try {
        return getFormatter(currency).format(numericPrice);
    } catch (error) {
        return getFormatter(DEFAULT_DISPLAY_CURRENCY).format(numericPrice);
    }
};

export const normalizeCarForCard = (car, fallbackImage) => {
    const photoUrl = (() => {
        if (car.primary_thumbnail_path) {
            return storageUrl(car.primary_thumbnail_path);
        }
        if (car.primary_photo_path) {
            return storageUrl(car.primary_photo_path);
        }
        return getPrimaryPhotoUrl(car, fallbackImage);
    })();

    return {
        id: car.id,
        title: `${car.brand} ${car.model}`.trim(),
        price: formatCarPrice(
            car.display_price ?? car.price,
            car.display_currency ?? DEFAULT_DISPLAY_CURRENCY
        ),
        year: car.year ?? '—',
        options: car.options ?? [],
        description: car.description ?? '',
        photoUrl,
    };
};
