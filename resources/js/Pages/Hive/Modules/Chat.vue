<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { ChatBubbleLeftRightIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import dayjs from 'dayjs';
import relativeTime from 'dayjs/plugin/relativeTime';

dayjs.extend(relativeTime);

const props = defineProps({
    module: { type: Object, default: null },
    channel: { type: Object, required: true },
});

const messages = ref([]);
const newMessage = ref('');
const formError = ref(null);
const messageContainer = ref(null);

const channelTitle = computed(() => {
    if (props.module) return `Module: ${props.module.name}`;
    if (props.channel.channel_type === 'general') return 'All Staff Chat';
    if (props.channel.channel_type === 'department') return `${props.channel.name} Department`;
    if (props.channel.channel_type === 'direct') return 'Direct Message';
    return props.channel.name;
});

const scrollToBottom = () => {
    nextTick(() => {
        if (messageContainer.value) {
            messageContainer.value.scrollTop = messageContainer.value.scrollHeight;
        }
    });
};

const echoChannelName = computed(() => {
    if (props.module) return `chat.module.${props.module.id}`;
    if (props.channel.channel_type === 'module') return `chat.module.${props.channel.channel_id}`;
    if (props.channel.channel_type === 'department') return `chat.department.${props.channel.channel_id}`;
    if (props.channel.channel_type === 'general') return 'chat.general';
    if (props.channel.channel_type === 'direct') return `chat.direct.${props.channel.id}`;
    return `chat.module.${props.module?.id}`;
});

const fetchMessages = async () => {
    try {
        let url;
        if (props.module) {
            url = `/api/modules/${props.module.id}/messages`;
        } else {
            url = `/api/channels/${props.channel.id}/messages`;
        }
        const response = await axios.get(url);
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
        let url;
        if (props.module) {
            url = `/api/modules/${props.module.id}/messages`;
        } else {
            url = `/api/channels/${props.channel.id}/messages`;
        }
        await axios.post(url, { message: newMessage.value });
        newMessage.value = '';
        // Re-fetch messages to ensure we have the latest
        await fetchMessages();
    } catch (error) {
        console.error('Error sending message:', error);
        if (error.response?.data?.errors?.message) {
            formError.value = error.response.data.errors.message[0];
        } else {
            formError.value = 'An unexpected error occurred.';
        }
    }
};

let echoChannel = null;

onMounted(() => {
    fetchMessages();

    if (window.Echo) {
        echoChannel = window.Echo.private(echoChannelName.value)
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
    <HiveLayout :title="channelTitle">
        <div class="max-w-4xl mx-auto">
            <div class="bg-white overflow-hidden shadow-sm rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <!-- Channel Header -->
                    <div class="mb-4 pb-4 border-b border-gray-200">
                        <h2 class="text-xl font-semibold text-gray-800">{{ channelTitle }}</h2>
                        <p class="text-sm text-gray-500 mt-1" v-if="module">{{ module.code }} | Discuss with your classmates and instructors</p>
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
                            <ChatBubbleLeftRightIcon class="w-12 h-12 mx-auto text-gray-400 mb-3" />
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
                            <PrimaryButton class="px-6" :disabled="!newMessage.trim()">Send</PrimaryButton>
                        </div>
                        <InputError :message="formError" class="mt-2" />
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>