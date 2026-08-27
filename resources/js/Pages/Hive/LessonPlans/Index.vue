<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    module: Object,
    lessonPlans: Object,
    filters: Object,
});

const statusColors = {
    draft: 'bg-gray-100 text-gray-800',
    published: 'bg-green-100 text-green-800',
    completed: 'bg-blue-100 text-blue-800',
    cancelled: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="Lesson Plans" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <div>
                    <h2 class="font-semibold text-xl text-gray-800 leading-tight">Lesson Plans</h2>
                    <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
                </div>
                <a
                    v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                    :href="route('hive.modules.lesson-plans.create', module)"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Create Lesson Plan
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Status Filter -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="flex flex-wrap gap-2">
                        <button
                            @click="$inertia.get(route('hive.modules.lesson-plans.index', module))"
                            :class="!filters.status ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-full text-sm"
                        >
                            All
                        </button>
                        <button
                            v-for="status in ['draft', 'published', 'completed', 'cancelled']"
                            :key="status"
                            @click="$inertia.get(route('hive.modules.lesson-plans.index', module), { status })"
                            :class="filters.status === status ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-700'"
                            class="px-3 py-1 rounded-full text-sm capitalize"
                        >
                            {{ status }}
                        </button>
                    </div>
                </div>

                <!-- Lesson Plans List -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="lessonPlans.data.length === 0" class="text-center text-gray-500 py-8">
                            No lesson plans found.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="plan in lessonPlans.data"
                                :key="plan.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span :class="statusColors[plan.status]" class="px-2 py-1 rounded text-xs font-medium capitalize">
                                                {{ plan.status }}
                                            </span>
                                            <span class="text-sm text-gray-500">
                                                {{ new Date(plan.lesson_date).toLocaleDateString() }}
                                            </span>
                                        </div>
                                        <h3 class="font-semibold mt-2">
                                            <a :href="route('hive.modules.lesson-plans.show', [module, plan])" class="hover:text-indigo-600">
                                                {{ plan.title }}
                                            </a>
                                        </h3>
                                        <p v-if="plan.description" class="text-sm text-gray-600 mt-1">
                                            {{ plan.description }}
                                        </p>
                                        <div class="flex gap-4 mt-2 text-xs text-gray-500">
                                            <span v-if="plan.start_time">{{ plan.start_time }} - {{ plan.end_time }}</span>
                                            <span>By {{ plan.creator?.name }}</span>
                                        </div>
                                    </div>
                                    <div class="flex gap-2">
                                        <a
                                            v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                                            :href="route('hive.modules.lesson-plans.edit', [module, plan])"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        >
                                            Edit
                                        </a>
                                        <button
                                            v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'chef-instructor', 'pastry-instructor', 'sous-chef'].includes(r.name))"
                                            @click="$inertia.delete(route('hive.modules.lesson-plans.destroy', [module, plan]))"
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
