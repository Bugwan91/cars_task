<script setup>
const props = defineProps({
    for: {
        type: String,
    },
    value: {
        type: String,
    },
});
</script>

<template>
    <label
        class="block text-sm font-medium text-gray-700"
        :for="props.for"
    >
        <span v-if="props.value">{{ props.value }}</span>
        <span v-else><slot /></span>
    </label>
</template>
