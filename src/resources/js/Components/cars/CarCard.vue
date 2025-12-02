<script setup>
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';
import { normalizeCarForCard } from '@/utils/carDisplay';

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
</script>

<template>
    <component :is="props.href ? Link : 'div'" v-bind="wrapperAttrs" class="block h-full">
        <div class="overflow-hidden rounded-lg bg-white shadow transition hover:-translate-y-0.5 hover:shadow-lg dark:bg-gray-800">
            <div class="aspect-w-16 aspect-h-9 bg-gray-200 dark:bg-gray-700">
                <img
                    :src="displayCar.photoUrl"
                    :alt="displayCar.title"
                    class="h-48 w-full object-cover"
                    loading="lazy"
                />
            </div>
            <div class="p-6">
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

                <div v-if="displayCar.options.length" class="mt-3 flex flex-wrap gap-2">
                    <span
                        v-for="option in displayCar.options"
                        :key="`${displayCar.id}-${option.id ?? option.name ?? option}`"
                        class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                    >
                        {{ option.name ?? option }}
                    </span>
                </div>

                <p class="mt-4 text-sm text-gray-500 dark:text-gray-400 line-clamp-3">
                    {{ displayCar.description }}
                </p>
            </div>
        </div>
    </component>
</template>
