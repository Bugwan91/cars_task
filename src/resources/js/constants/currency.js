const BASE_FROM_WINDOW = typeof window !== 'undefined' ? window.__APP_BASE_CURRENCY__ : undefined;
const DEFAULT_FROM_WINDOW = typeof window !== 'undefined' ? window.__APP_DEFAULT_CURRENCY__ : undefined;

export const BASE_CURRENCY = (BASE_FROM_WINDOW || 'USD').toUpperCase();
export const DEFAULT_DISPLAY_CURRENCY = (DEFAULT_FROM_WINDOW || BASE_CURRENCY).toUpperCase();
