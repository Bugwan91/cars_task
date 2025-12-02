<script setup>
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    label: String,
    for: String,
    message: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div>
        <InputLabel v-if="label" :for="for" :value="label" />
        <slot />
        <InputError v-if="message" class="mt-2" :message="message" />
    </div>
</template>
