<script setup>
import Modal from './Modal.vue';

const emit = defineEmits(['close']);

defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    maxWidth: {
        type: String,
        default: '2xl',
    },
    closeable: {
        type: Boolean,
        default: true,
    },
});

const close = () => {
    emit('close');
};
</script>

<template>
    <Modal
        :show="show"
        :max-width="maxWidth"
        :closeable="closeable"
        @close="close"
    >
        <div class="px-6 pt-6 pb-4">
            <div class="text-lg font-semibold text-gray-900 dark:text-white">
                <slot name="title" />
            </div>

            <div class="mt-3 text-sm text-gray-600 dark:text-gray-400">
                <slot name="content" />
            </div>
        </div>

        <div class="flex flex-row justify-end gap-3 px-6 py-4 bg-gray-50 border-t border-gray-100 rounded-b-xl">
            <slot name="footer" />
        </div>
    </Modal>
</template>
