<script setup>
import { computed } from 'vue';
import { formatCarPrice } from '@/utils/carDisplay';

const props = defineProps({
    car: {
        type: Object,
        required: true,
    },
    descriptionFallback: {
        type: String,
        default: 'No description provided for this car yet.',
    },
    sectionTitle: {
        type: String,
        default: 'Vehicle Details',
    },
});

const specs = computed(() => [
    { label: 'Brand', value: props.car.brand ?? '—' },
    { label: 'Model', value: props.car.model ?? '—' },
    { label: 'Year', value: props.car.year ?? '—' },
    { label: 'Price', value: formatCarPrice(props.car.price) },
]);

const descriptionText = computed(
    () => props.car.description?.trim() || props.descriptionFallback
);

const options = computed(() => props.car.options ?? []);
</script>

<template>
    <div class="bg-white p-6 shadow sm:rounded-lg dark:bg-gray-800">
        <h2 class="text-2xl font-semibold text-gray-900 dark:text-white">
            {{ props.sectionTitle }}
        </h2>
        <dl class="mt-4 space-y-4">
            <div
                v-for="spec in specs"
                :key="spec.label"
                class="flex justify-between border-b border-gray-100 pb-3 text-sm dark:border-gray-700"
            >
                <dt class="text-gray-500 dark:text-gray-400">{{ spec.label }}</dt>
                <dd class="font-medium text-gray-900 dark:text-gray-200">{{ spec.value }}</dd>
            </div>
        </dl>

        <div v-if="options.length" class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Options & Features
            </h3>
            <div class="mt-3 flex flex-wrap gap-2">
                <span
                    v-for="option in options"
                    :key="option.id ?? option.name ?? option"
                    class="rounded-full bg-gray-100 px-3 py-1 text-xs font-medium text-gray-700 dark:bg-gray-700 dark:text-gray-200"
                >
                    {{ option.name ?? option }}
                </span>
            </div>
        </div>

        <div class="mt-6">
            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                Description
            </h3>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300 whitespace-pre-line">
                {{ descriptionText }}
            </p>
        </div>
    </div>
</template>
