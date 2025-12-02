import { ref, watch, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

export default function useInfiniteCars(initialCars = [], initialNextPageUrl = null, options = {}) {
    const cars = ref([...initialCars]);
    const nextPageUrl = ref(initialNextPageUrl);
    const loading = ref(false);
    const loadMoreTrigger = ref(null);
    const observer = ref(null);
    const rootMargin = options.rootMargin ?? '100px';

    const loadMoreCars = async () => {
        if (loading.value || !nextPageUrl.value) {
            return;
        }

        loading.value = true;

        try {
            const response = await axios.get(nextPageUrl.value);
            cars.value = [...cars.value, ...response.data.data];
            nextPageUrl.value = response.data.next_page_url;
        } catch (error) {
            console.error('Error loading more cars:', error);
        } finally {
            loading.value = false;
        }
    };

    const reset = (newCars = [], newNextPageUrl = null) => {
        cars.value = [...newCars];
        nextPageUrl.value = newNextPageUrl;
    };

    const observeTarget = () => {
        if (!observer.value || !loadMoreTrigger.value) {
            return;
        }
        observer.value.observe(loadMoreTrigger.value);
    };

    const disconnectObserver = () => {
        if (observer.value) {
            observer.value.disconnect();
            observer.value = null;
        }
    };

    onMounted(() => {
        observer.value = new IntersectionObserver((entries) => {
            if (entries[0].isIntersecting) {
                loadMoreCars();
            }
        }, { rootMargin });

        observeTarget();
    });

    onUnmounted(() => {
        disconnectObserver();
    });

    watch(loadMoreTrigger, () => {
        if (!observer.value) {
            return;
        }
        observer.value.disconnect();
        observeTarget();
    });

    return {
        cars,
        nextPageUrl,
        loading,
        loadMoreCars,
        loadMoreTrigger,
        reset,
    };
}
