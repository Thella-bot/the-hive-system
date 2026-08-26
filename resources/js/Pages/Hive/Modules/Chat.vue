<script setup>
import { ref, onMounted, onUnmounted, nextTick, computed } from 'vue';
import { ChatBubbleLeftRightIcon, PaperClipIcon, XMarkIcon } from '@heroicons/vue/24/outline';
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
const attachments = ref([]);
const uploadError = ref(null);
const isUploading = ref(false);

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
        // Handle both paginated and non-paginated responses
        messages.value = response.data.data || response.data;
        scrollToBottom();
    } catch (error) {
        console.error('Error fetching messages:', error);
    }
};

const handleFileSelect = async (event) => {
    const files = Array.from(event.target.files);
    if (files.length === 0) return;

    // Check total attachments limit
    if (attachments.value.length + files.length > 5) {
        uploadError.value = 'Maximum 5 attachments per message.';
        event.target.value = '';
        return;
    }

    isUploading.value = true;
    uploadError.value = null;

    for (const file of files) {
        // Check file size (10MB limit)
        if (file.size > 10 * 1024 * 1024) {
            uploadError.value = `File "${file.name}" exceeds 10MB limit.`;
            continue;
        }

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await axios.post('/api/chat/attachments', formData, {
                headers: { 'Content-Type': 'multipart/form-data' },
            });

            attachments.value.push(response.data);
        } catch (error) {
            console.error('Upload error:', error);
            uploadError.value = error.response?.data?.message || `Failed to upload "${file.name}".`;
        }
    }

    isUploading.value = false;
    event.target.value = '';
};

const removeAttachment = (index) => {
    attachments.value.splice(index, 1);
};

const sendMessage = async () => {
    const messageText = newMessage.value.trim();
    if (!messageText && attachments.value.length === 0) {
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

        const payload = {
            message: messageText || '',
        };

        if (attachments.value.length > 0) {
            payload.attachments = attachments.value;
        }

        // Optimistic update: clear input immediately
        newMessage.value = '';
        const savedAttachments = [...attachments.value];
        attachments.value = [];

        const response = await axios.post(url, payload);

        // Append the new message directly instead of re-fetching all
        messages.value.push(response.data);
        scrollToBottom();
    } catch (error) {
        console.error('Error sending message:', error);
        // Restore the message on failure
        newMessage.value = messageText;
        if (error.response?.data?.errors?.message) {
            formError.value = error.response.data.errors.message[0];
        } else {
            formError.value = 'An unexpected error occurred.';
        }
    }
};

const formatFileSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
};

const isImage = (mimeType) => {
    return mimeType && mimeType.startsWith('image/');
};

let echoChannel = null;
let reconnectAttempts = 0;
const maxReconnectAttempts = 5;

const subscribeToEcho = () => {
    if (!window.Echo) return;

    echoChannel = window.Echo.private(echoChannelName.value)
        .listen('ChatMessageSent', (e) => {
            // Avoid duplicating messages we already added optimistically
            const exists = messages.value.some(m => m.id === e.message.id);
            if (!exists) {
                messages.value.push(e.message);
                scrollToBottom();
            }
        })
        .error((error) => {
            console.error('Echo channel error:', error);
        });

    // Handle connection drops
    window.Echo.connector.pusher.connection.bind('state_change', (states) => {
        if (states.current === 'connecting') {
            reconnectAttempts++;
            if (reconnectAttempts > maxReconnectAttempts) {
                console.warn('Max reconnection attempts reached, falling back to polling');
                startPolling();
            }
        } else if (states.current === 'connected') {
            reconnectAttempts = 0;
            stopPolling();
        }
    });
};

let pollInterval = null;

const startPolling = () => {
    if (pollInterval) return;
    pollInterval = setInterval(() => {
        fetchMessages();
    }, 5000);
};

const stopPolling = () => {
    if (pollInterval) {
        clearInterval(pollInterval);
        pollInterval = null;
    }
};

onMounted(() => {
    fetchMessages();
    subscribeToEcho();
});

onUnmounted(() => {
    if (echoChannel) {
        echoChannel.stopListening('ChatMessageSent');
    }
    stopPolling();
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
                                    :alt="message.user?.name || 'Deleted User'"
                                    class="w-8 h-8 rounded-full mr-2"
                                />
                                <p class="font-bold" :class="message.user ? 'text-gray-900' : 'text-gray-500 italic'">
                                    {{ message.user?.name || 'Deleted User' }}
                                </p>
                                <span class="text-xs text-gray-500 ml-2">{{ dayjs(message.created_at).fromNow() }}</span>
                            </div>
                            <div class="ml-10">
                                <p v-if="message.message" class="text-gray-700 bg-white p-3 rounded-lg shadow-sm inline-block max-w-xl whitespace-pre-wrap">
                                    {{ message.message }}
                                </p>
                                <!-- Attachments -->
                                <div v-if="message.attachments && message.attachments.length > 0" class="mt-2 space-y-2">
                                    <div v-for="(attachment, idx) in message.attachments" :key="idx" class="flex items-center gap-2">
                                        <template v-if="isImage(attachment.mime_type)">
                                            <img
                                                :src="attachment.url"
                                                :alt="attachment.name"
                                                class="max-w-xs max-h-48 rounded-lg border border-gray-200"
                                            />
                                        </template>
                                        <template v-else>
                                            <a
                                                :href="attachment.url"
                                                target="_blank"
                                                class="flex items-center gap-2 bg-white p-2 rounded-lg border border-gray-200 hover:bg-gray-50 text-sm"
                                            >
                                                <PaperClipIcon class="w-4 h-4 text-gray-500" />
                                                <span class="text-gray-700">{{ attachment.name }}</span>
                                                <span class="text-gray-400 text-xs">{{ formatFileSize(attachment.size) }}</span>
                                            </a>
                                        </template>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-if="messages.length === 0" class="text-center text-gray-500 py-8">
                            <ChatBubbleLeftRightIcon class="w-12 h-12 mx-auto text-gray-400 mb-3" />
                            <p>No messages yet. Be the first to say something!</p>
                        </div>
                    </div>

                    <!-- Message Input Form -->
                    <form @submit.prevent="sendMessage">
                        <!-- Attachment Preview -->
                        <div v-if="attachments.length > 0" class="mb-3 flex flex-wrap gap-2">
                            <div
                                v-for="(attachment, index) in attachments"
                                :key="index"
                                class="flex items-center gap-2 bg-gray-100 px-3 py-1 rounded-full text-sm"
                            >
                                <PaperClipIcon class="w-4 h-4 text-gray-500" />
                                <span class="text-gray-700 truncate max-w-[150px]">{{ attachment.name }}</span>
                                <button
                                    type="button"
                                    @click="removeAttachment(index)"
                                    class="text-gray-400 hover:text-gray-600"
                                >
                                    <XMarkIcon class="w-4 h-4" />
                                </button>
                            </div>
                        </div>

                        <!-- Upload Error -->
                        <div v-if="uploadError" class="mb-2 text-sm text-red-600">
                            {{ uploadError }}
                        </div>

                        <div class="flex items-center gap-3">
                            <!-- File Upload Button -->
                            <label class="cursor-pointer p-2 text-gray-500 hover:text-amber-600 hover:bg-amber-50 rounded-md transition-colors" :class="{ 'opacity-50 pointer-events-none': isUploading }">
                                <PaperClipIcon class="w-5 h-5" />
                                <input
                                    type="file"
                                    class="hidden"
                                    multiple
                                    accept="image/*,.pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt,.zip"
                                    @change="handleFileSelect"
                                />
                            </label>
                            <input
                                v-model="newMessage"
                                type="text"
                                class="flex-grow border-gray-300 focus:border-amber-500 focus:ring-amber-500 rounded-md shadow-sm"
                                placeholder="Type your message..."
                                autocomplete="off"
                            />
                            <PrimaryButton class="px-6" :disabled="isUploading || (!newMessage.trim() && attachments.length === 0)">
                                {{ isUploading ? 'Uploading...' : 'Send' }}
                            </PrimaryButton>
                        </div>
                        <InputError :message="formError" class="mt-2" />
                    </form>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
