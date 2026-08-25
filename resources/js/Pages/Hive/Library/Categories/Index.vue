<template>
    <HiveLayout title="Book Categories" description="Manage library book categories">
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h1 class="text-2xl font-bold mb-6">Book Categories</h1>
                        <div v-if="categories.length === 0" class="text-gray-500">No categories found.</div>
                        <ul v-else class="space-y-2">
                            <li v-for="category in categories" :key="category.id" class="border-b pb-2">
                                {{ category.name }} <span class="text-gray-400 text-sm">({{ category.books_count }} books)</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import HiveLayout from '@/Layouts/HiveLayout.vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});
</script>
