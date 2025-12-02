<script setup>
import { ref, computed, watch } from 'vue';
import FieldWrapper from '@/Components/forms/FieldWrapper.vue';

const props = defineProps({
    modelValue: {
        type: Array,
        default: () => [],
    },
    suggestions: {
        type: Array,
        default: () => [],
    },
    error: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue']);

const inputValue = ref('');
const selected = ref([...props.modelValue]);

watch(
    () => props.modelValue,
    (next) => {
        selected.value = [...next];
    }
);

const normalizedSuggestions = computed(() => {
    const search = inputValue.value.trim().toLowerCase();
    return props.suggestions
        .filter((option) => {
            const alreadySelected = selected.value.some(
                (sel) => sel.toLowerCase() === option.toLowerCase()
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

const sync = () => emit('update:modelValue', [...selected.value]);

const addOption = (value) => {
    const normalized = value.trim();
    if (!normalized) {
        inputValue.value = '';
        return;
    }
    const exists = selected.value.some(
        (option) => option.toLowerCase() === normalized.toLowerCase()
    );
    if (!exists) {
        selected.value.push(normalized);
        sync();
    }
    inputValue.value = '';
};

const removeOption = (value) => {
    selected.value = selected.value.filter((option) => option !== value);
    sync();
};

const handleKeydown = (event) => {
    if (['Enter', 'Tab', ','].includes(event.key)) {
        event.preventDefault();
        addOption(inputValue.value);
    } else if (event.key === 'Backspace' && inputValue.value === '' && selected.value.length) {
        selected.value.pop();
        sync();
    }
};
</script>

<template>
    <FieldWrapper label="Options" for="options" :message="props.error">
        <div
            class="mt-1 flex flex-wrap items-center gap-2 rounded-md border border-gray-300 bg-white p-2 focus-within:border-indigo-500 focus-within:ring-1 focus-within:ring-indigo-500 dark:border-gray-700 dark:bg-gray-900"
        >
            <span
                v-for="option in selected"
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
                v-model="inputValue"
                @keydown="handleKeydown"
                type="text"
                class="flex-1 border-0 bg-transparent p-2 text-sm text-gray-900 placeholder-gray-400 focus:ring-0 dark:text-gray-200 dark:placeholder-gray-500"
                placeholder="Type an option and press Enter"
            />
        </div>

        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">
            Press Enter (or comma) to add. Click a suggested option to reuse an existing one.
        </p>

        <div v-if="normalizedSuggestions.length" class="mt-2 flex flex-wrap gap-2">
            <button
                v-for="option in normalizedSuggestions"
                :key="option"
                type="button"
                class="rounded-full border border-gray-200 px-3 py-1 text-xs text-gray-600 hover:border-indigo-300 hover:text-indigo-600 dark:border-gray-700 dark:text-gray-300"
                @click="addOption(option)"
            >
                {{ option }}
            </button>
        </div>
    </FieldWrapper>
</template>
