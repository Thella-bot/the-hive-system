<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    module: Object,
    progress: Array,
    gradables: Array,
    completedCount: Number,
    totalItems: Number,
    progressPercent: Number,
});

const getProgressStatus = (gradableId) => {
    const item = progress.find(p => p.item_type === 'gradable' && p.item_id == gradableId);
    return item ? item.status : 'not_started';
};

const getProgressScore = (gradableId) => {
    const item = progress.find(p => p.item_type === 'gradable' && p.item_id == gradableId);
    return item?.score ?? null;
};
</script>

<template>
    <Head title="My Progress" />
    <HiveLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">My Progress</h2>
            <p class="text-sm text-gray-600">{{ module.code }} - {{ module.name }}</p>
        </template>

        <div class="py-12">
            <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
                <!-- Progress Overview -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                    <h3 class="font-semibold text-lg mb-4">Overall Progress</h3>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <div class="bg-gray-200 rounded-full h-4">
                                <div
                                    class="bg-green-500 h-4 rounded-full transition-all"
                                    :style="{ width: progressPercent + '%' }"
                                ></div>
                            </div>
                        </div>
                        <span class="text-lg font-semibold">{{ progressPercent }}%</span>
                    </div>
                    <p class="text-sm text-gray-600 mt-2">
                        {{ completedCount }} of {{ totalItems }} items completed
                    </p>
                </div>

                <!-- Gradables Progress -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4">Assessments</h3>
                        <div v-if="gradables.length === 0" class="text-center text-gray-500 py-8">
                            No assessments found.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="gradable in gradables"
                                :key="gradable.id"
                                class="border rounded-lg p-4"
                            >
                                <div class="flex justify-between items-center">
                                    <div>
                                        <h4 class="font-medium">{{ gradable.title }}</h4>
                                        <p class="text-sm text-gray-600 capitalize">{{ gradable.type.replace('_', ' ') }}</p>
                                    </div>
                                    <div class="text-right">
                                        <span
                                            :class="{
                                                'bg-gray-100 text-gray-800': getProgressStatus(gradable.id) === 'not_started',
                                                'bg-yellow-100 text-yellow-800': getProgressStatus(gradable.id) === 'in_progress',
                                                'bg-green-100 text-green-800': getProgressStatus(gradable.id) === 'completed',
                                            }"
                                            class="px-2 py-1 rounded text-xs font-medium capitalize"
                                        >
                                            {{ getProgressStatus(gradable.id).replace('_', ' ') }}
                                        </span>
                                        <p v-if="getProgressScore(gradable.id)" class="text-sm text-gray-600 mt-1">
                                            Score: {{ getProgressScore(gradable.id) }}%
                                        </p>
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
