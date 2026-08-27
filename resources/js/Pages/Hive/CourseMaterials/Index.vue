<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    module: Object,
    materials: Array,
    categories: Array,
    filters: Object,
});

const getIcon = (fileType) => {
    if (fileType.includes('pdf')) return '📄';
    if (fileType.includes('image')) return '🖼️';
    if (fileType.includes('video')) return '🎥';
    if (fileType.includes('audio')) return '🎵';
    if (fileType.includes('word') || fileType.includes('document')) return '📝';
    if (fileType.includes('sheet') || fileType.includes('excel')) return '📊';
    return '📁';
};

const formatSize = (bytes) => {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1048576) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / 1048576).toFixed(1) + ' MB';
};
</script>

<template>
    <Head title="Course Materials" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Course Materials</h2>
                    <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
                </div>
                <a
                    v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                    :href="route('hive.modules.courses.create', module)"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Upload Material
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Category Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="$inertia.get(route('hive.modules.courses.index', module))"
                            :class="!filters.category ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-full text-sm"
                        >
                            All
                        </button>
                        <button
                            v-for="(label, key) in categories"
                            :key="key"
                            @click="$inertia.get(route('hive.modules.courses.index', module), { category: key })"
                            :class="filters.category === key ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-full text-sm"
                        >
                            {{ label }}
                        </button>
                    </div>
                </div>

                <!-- Materials List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="materials.length === 0" class="text-center text-gray-500 py-8">
                            No course materials found.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="material in materials"
                                :key="material.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex justify-between items-start">
                                    <div class="flex gap-3">
                                        <span class="text-2xl">{{ getIcon(material.file_type) }}</span>
                                        <div>
                                            <h3 class="font-semibold">{{ material.title }}</h3>
                                            <p v-if="material.description" class="text-sm text-gray-600 mt-1">
                                                {{ material.description }}
                                            </p>
                                            <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                                <span>{{ material.category }}</span>
                                                <span>{{ formatSize(material.file_size) }}</span>
                                                <span>By {{ material.uploader?.name }}</span>
                                                <span>{{ new Date(material.created_at).toLocaleDateString() }}</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a
                                            :href="route('hive.modules.courses.download', [module, material])"
                                            class="text-blue-600 hover:text-blue-900 text-sm"
                                        >
                                            Download
                                        </a>
                                        <a
                                            v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                                            :href="route('hive.modules.courses.edit', [module, material])"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        >
                                            Edit
                                        </a>
                                        <button
                                            v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                                            @click="$inertia.delete(route('hive.modules.courses.destroy', [module, material]))"
                                            class="text-red-600 hover:text-red-900 text-sm"
                                        >
                                            Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
