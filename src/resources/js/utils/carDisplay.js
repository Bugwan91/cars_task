import { photoUrlOrFallback } from '@/utils/storage';

const priceFormatter = new Intl.NumberFormat('en-US', {
    style: 'currency',
    currency: 'USD',
    minimumFractionDigits: 0,
    maximumFractionDigits: 0,
});

export const getPrimaryPhotoUrl = (car, fallbackImage) => {
    if (car?.photos?.length) {
        return photoUrlOrFallback(car.photos[0], fallbackImage);
    }
    return fallbackImage;
};

export const formatCarPrice = (price) => {
    if (price === null || price === undefined || price === '') {
        return 'N/A';
    }
    return priceFormatter.format(price);
};

export const normalizeCarForCard = (car, fallbackImage) => ({
    id: car.id,
    title: `${car.brand} ${car.model}`.trim(),
    price: formatCarPrice(car.price),
    year: car.year ?? '—',
    options: car.options ?? [],
    description: car.description ?? '',
    photoUrl: getPrimaryPhotoUrl(car, fallbackImage),
});
