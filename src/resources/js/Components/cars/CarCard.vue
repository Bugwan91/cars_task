<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { normalizeCarForCard } from '@/utils/carDisplay';

const MAX_OPTIONS_DISPLAY = 6;

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
    fallbackImage: {
        type: String,
        required: true,
    },
    href: {
        type: String,
        default: '',
    },
});

const displayCar = computed(() => normalizeCarForCard(props.car, props.fallbackImage));
const wrapperAttrs = computed(() => (props.href ? { href: props.href } : {}));
const limitedOptions = computed(() => {
    const opts = displayCar.value.options || [];
    return opts.length > MAX_OPTIONS_DISPLAY
        ? opts.slice(0, MAX_OPTIONS_DISPLAY)
        : opts;
});
const hasMoreOptions = computed(() => (displayCar.value.options?.length || 0) > MAX_OPTIONS_DISPLAY);
</script>

<template>
    <component :is="props.href ? Link : 'div'" v-bind="wrapperAttrs" class="block h-full">
        <div class="group flex flex-col h-full overflow-hidden rounded-lg bg-white shadow transition-all duration-200 hover:-translate-y-0.5 hover:shadow-2xl hover:bg-gray-50 dark:bg-gray-800 dark:hover:bg-gray-700">
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
                <img
                    :src="displayCar.photoUrl"
                    :alt="displayCar.title"
                    class="h-48 w-full object-cover"
                    loading="lazy"
                />
            </div>
            <div class="flex-1 flex flex-col p-6">
                <div class="flex items-start justify-between gap-3">
                    <h2 class="text-xl font-semibold text-gray-900 dark:text-white">
                        {{ displayCar.title }}
                    </h2>
                    <p class="text-lg font-bold text-indigo-600 dark:text-indigo-400 whitespace-nowrap">
                        {{ displayCar.price }}
                    </p>
                </div>

                <p class="mt-2 text-gray-600 dark:text-gray-300">
                    Year: {{ displayCar.year }}
                </p>

                <div v-if="limitedOptions.length" class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="option in limitedOptions"
                        :key="`${displayCar.id}-${option.id ?? option.name ?? option}`"
                        class="rounded-full bg-gray-100 group-hover:bg-gray-300 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:group-hover:bg-gray-600 dark:text-gray-200"
                    >
                        {{ option.name ?? option }}
                    </span>
                    <span v-if="hasMoreOptions" class="rounded-full bg-gray-200 group-hover:bg-gray-400 px-3 py-1 text-xs font-medium text-gray-500 dark:bg-gray-700 dark:group-hover:bg-gray-600 dark:text-gray-400">
                        +{{ displayCar.options.length - MAX_OPTIONS_DISPLAY }} more
                    </span>
                </div>

                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 line-clamp-3 flex-1">
                    {{ displayCar.description }}
                </p>
            </div>
        </div>
    </component>
</template>
