<script setup>
import { computed } from 'vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import TextInput from '@/Components/TextInput.vue';

defineOptions({ inheritAttrs: false });

const props = defineProps({
    id: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: true,
    },
    modelValue: {
        type: [String, Number],
        default: '',
    },
    type: {
        type: String,
        default: 'text',
    },
    error: {
        type: String,
        default: '',
    },
    textarea: {
        type: Boolean,
        default: false,
    },
    rows: {
        type: Number,
        default: 4,
    },
});

const emit = defineEmits(['update:modelValue']);

const fieldClasses = computed(() =>
    'mt-1 block w-full border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm dark:bg-gray-900 dark:text-gray-300 dark:border-gray-700'
);

const updateValue = (eventOrValue) => {
    const value = eventOrValue?.target ? eventOrValue.target.value : eventOrValue;
    emit('update:modelValue', value);
};
</script>

<template>
    <div>
        <InputLabel :for="props.id" :value="props.label" />

        <component
            v-if="props.textarea"
            :is="'textarea'"
            :id="props.id"
            :rows="props.rows"
            :class="fieldClasses"
            :value="props.modelValue"
            @input="updateValue"
            v-bind="$attrs"
        />
        <TextInput
            v-else
            :id="props.id"
            :type="props.type"
            class="mt-1 block w-full"
            :model-value="props.modelValue"
            @update:model-value="updateValue"
            v-bind="$attrs"
        />

        <InputError class="mt-2" :message="props.error" />
    </div>
</template>
