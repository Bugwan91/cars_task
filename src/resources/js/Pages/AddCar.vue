<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    availableOptions: {
        type: Array,
        default: () => [],
    },
});

const form = useForm({
    brand: '',
    model: '',
    year: '',
    price: '',
    description: '',
    options: [],
});

const optionInput = ref('');
const selectedOptions = ref([]);

const addOption = (value) => {
    const normalized = value.trim();

    if (!normalized) {
        optionInput.value = '';
        return;
    }

    const exists = selectedOptions.value.some(
        (option) => option.toLowerCase() === normalized.toLowerCase()
    );

    if (!exists) {
        selectedOptions.value.push(normalized);
    }

    optionInput.value = '';
};

const removeOption = (value) => {
    selectedOptions.value = selectedOptions.value.filter((option) => option !== value);
};

const handleOptionKeydown = (event) => {
    if (['Enter', 'Tab', ','].includes(event.key)) {
        event.preventDefault();
        addOption(optionInput.value);
    } else if (event.key === 'Backspace' && optionInput.value === '' && selectedOptions.value.length) {
        selectedOptions.value.pop();
    }
};

const suggestedOptions = computed(() => {
    const search = optionInput.value.trim().toLowerCase();

    return props.availableOptions
        .filter((option) => {
            const alreadySelected = selectedOptions.value.some(
                (selected) => selected.toLowerCase() === option.toLowerCase()
            );

            if (alreadySelected) {
                return false;
            }

            if (!search) {
                return true;
            }

            return option.toLowerCase().includes(search);
        })
        .slice(0, 8);
});

watch(
    selectedOptions,
    (value) => {
        form.options = value;
    },
    { deep: true }
);

const resetOptions = () => {
    selectedOptions.value = [];
    optionInput.value = '';
};

const submit = () => {
    form.post(route('cars.store.web'), {
        onSuccess: () => {
            form.reset();
            resetOptions();
        },
    });
};
</script>

<template>
    <Head title="Add Car" />

    <div class="min-h-screen bg-gray-100 dark:bg-gray-900">
        <header class="bg-white shadow dark:bg-gray-800">
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 flex justify-between items-center">
                <h1 class="text-3xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Add New Car
                </h1>
                <Link :href="route('home')" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">
                    Back to Home
                </Link>
            </div>
        </header>

        <main>
            <div class="mx-auto max-w-7xl py-6 sm:px-6 lg:px-8">
                <div class="bg-white p-6 shadow sm:rounded-lg dark:bg-gray-800">
                    <form @submit.prevent="submit" class="space-y-6 max-w-xl">
                        <div>
                            <InputLabel for="brand" value="Brand" />
                            <TextInput
                                id="brand"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.brand"
                                required
                                autofocus
                            />
                            <InputError class="mt-2" :message="form.errors.brand" />
                        </div>

                        <div>
                            <InputLabel for="model" value="Model" />
                            <TextInput
                                id="model"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="form.model"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.model" />
                        </div>

                        <div>
                            <InputLabel for="year" value="Year" />
                            <TextInput
                                id="year"
                                type="number"
                                class="mt-1 block w-full"
                                v-model="form.year"
                                required
                            />
                            <InputError class="mt-2" :message="form.errors.year" />
                        </div>

                        <div>
                            <InputLabel for="price" value="Price" />
                            <TextInput
                                id="price"
                                type="number"
                                step="0.01"
                                class="mt-1 block w-full"
                                v-model="form.price"
                            />
                            <InputError class="mt-2" :message="form.errors.price" />
                        </div>

                        <div>
                            <InputLabel for="description" value="Description" />
                            <textarea
                                id="description"
                                class="mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700"
                                v-model="form.description"
                                rows="4"
                            ></textarea>
                            <InputError class="mt-2" :message="form.errors.description" />
                        </div>

                        <div>
                            <InputLabel for="options" value="Options" />
                            <div
                                class="mt-1 flex flex-wrap items-center gap-2 rounded-md border border-gray-300 bg-white p-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
                            >
                                <span
                                    v-for="option in selectedOptions"
                                    :key="option"
                                    class="inline-flex items-center gap-1 rounded-full bg-indigo-100 px-3 py-1 text-sm font-medium text-indigo-700 dark:bg-indigo-900/40 dark:text-indigo-200"
                                >
                                    {{ option }}
                                    <button
                                        type="button"
                                        class="text-indigo-500 hover:text-indigo-700 dark:text-indigo-300"
                                        @click="removeOption(option)"
                                        aria-label="Remove option"
                                    >
                                        ×
                                    </button>
                                </span>

                                <input
                                    id="options"
                                    v-model="optionInput"
                                    @keydown="handleOptionKeydown"
                                    type="text"
                                    class="flex-1 border-0 bg-transparent p-2 text-sm text-gray-900 placeholder-gray-400 focus:ring-0 dark:text-gray-200 dark:placeholder-gray-500"
                                    placeholder="Type an option and press Enter"
                                />
                            </div>
                            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                Press Enter (or comma) to add. Click a suggested option to reuse an existing one.
                            </p>
                            <div v-if="suggestedOptions.length" class="mt-2 flex flex-wrap gap-2">
                                <button
                                    v-for="option in suggestedOptions"
                                    :key="option"
                                    type="button"
                                    class="rounded-full border border-gray-200 px-3 py-1 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-300"
                                    @click="addOption(option)"
                                >
                                    {{ option }}
                                </button>
                            </div>
                            <InputError class="mt-2" :message="form.errors.options" />
                        </div>

                        <div class="flex items-center gap-4">
                            <PrimaryButton :disabled="form.processing">
                                Save Car
                            </PrimaryButton>

                            <Transition
                                enter-active-class="transition ease-in-out"
                                enter-from-class="opacity-0"
                                leave-active-class="transition ease-in-out"
                                leave-to-class="opacity-0"
                            >
                                <p v-if="form.recentlySuccessful" class="text-sm text-gray-600 dark:text-gray-400">Saved.</p>
                            </Transition>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
