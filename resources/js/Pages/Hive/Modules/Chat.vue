<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue';
import { usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    module: Object,
});

const page = usePage();
const user = page.props.auth.user;
const messages = ref([]);
const newMessage = ref('');
const formError = ref(null);
const messageContainer = ref(null);

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const fetchMessages = async () => {
    try {
        const response = await axios.get(route('messages.index', { module: props.module.id }));
        messages.value = response.data;
        scrollToBottom();
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
};

const sendMessage = async () => {
    if (!newMessage.value.trim()) {
        formError.value = 'Message cannot be empty.';
        return;
    }

    try {
        formError.value = null;
        await axios.post(route('messages.store', { module: props.module.id }), {
            message: newMessage.value,
        });
        newMessage.value = '';
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
        if (error.response && error.response.data.errors) {
            formError.value = error.response.data.errors.message[0];
        } else {
            formError.value = 'An unexpected error occurred.';
        }
    }
};

let echoChannel = null;

onMounted(() => {
    fetchMessages();

    // Listen for new messages via Echo (Laravel Broadcasting)
    if (window.Echo) {
        echoChannel = window.Echo.private(`module.${props.module.id}`)
            .listen('ChatMessageSent', (e) => {
                messages.value.push(e.message);
                scrollToBottom();
            });
    }
});

onUnmounted(() => {
    if (echoChannel) {
        echoChannel.stopListening('ChatMessageSent');
    }
});
</script>

<template>
    <HiveLayout title="Chat" :description="`Module: ${module.name}`">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Module Header -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">
                            Chat Room: {{ module.name }}
                        </h2>
                        <p class="text-sm text-gray-500 mt-1">
                            Code: {{ module.code }} | Discuss with your classmates and instructors
                        </p>
                    </div>

                    <!-- Messages Container -->
                    <div
                        ref="messageContainer"
                        class="h-96 overflow-y-auto mb-4 p-4 border border-gray-200 rounded-lg bg-gray-50"
                    >
                        <div v-for="message in messages" :key="message.id" class="mb-4">
                            <div class="flex items-center mb-1">
                                <img
                                    :src="message.user?.profile_photo_url || '/images/default-avatar.png'"
                                    :alt="message.user?.name"
                                    class="w-8 h-8 rounded-full mr-2"
                                />
                                <p class="font-bold text-gray-900 mr-2">{{ message.user?.name || 'Unknown' }}</p>
                                <span class="text-xs text-gray-500">{{ dayjs(message.created_at).fromNow() }}</span>
                            </div>
                            <div class="ml-10">
                                <p class="text-gray-700 bg-white p-3 rounded-lg shadow-sm inline-block max-w-xl">
                                    {{ message.message }}
                                </p>
                            </div>
                        </div>
                        <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                            <svg class="w-12 h-12 mx-auto text-gray-400 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
                            </svg>
                            <p>No messages yet. Be the first to say something!</p>
                        </div>
                    </div>

                    <!-- Message Input Form -->
                    <form @submit.prevent="sendMessage">
                        <div class="flex items-center gap-3">
                            <input
                                v-model="newMessage"
                                type="text"
                                class="flex-grow border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm"
                                placeholder="Type your message..."
                                autocomplete="off"
                            />
                            <PrimaryButton
                                class="px-6"
                                :disabled="!newMessage.trim()"
                            >
                                Send
                            </PrimaryButton>
                        </div>
                        <InputError :message="formError" class="mt-2" />
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>