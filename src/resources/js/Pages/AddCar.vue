<script setup>
import { Head, useForm, Link } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

const form = useForm({
    brand: '',
    model: '',
    year: '',
    price: '',
    description: '',
});

const submit = () => {
    form.post(route('cars.store.web'), {
        onSuccess: () => form.reset(),
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
