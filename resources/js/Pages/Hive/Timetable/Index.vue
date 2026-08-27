<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    slots: Array,
    filters: Object,
    modules: Array,
    rooms: Array,
    days: Array,
});

const dayColors = {
    Monday: 'bg-blue-100 text-blue-800',
    Tuesday: 'bg-green-100 text-green-800',
    Wednesday: 'bg-yellow-100 text-yellow-800',
    Thursday: 'bg-purple-100 text-purple-800',
    Friday: 'bg-pink-100 text-pink-800',
    Saturday: 'bg-orange-100 text-orange-800',
};
</script>

<template>
    <Head title="Timetable" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Timetable</h2>
                <a
                    v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'registrar'].includes(r.name))"
                    :href="route('hive.timetable.create')"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-gray-700"
                >
                    Add Slot
                </a>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Filters -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-4">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Module</label>
                            <select
                                :value="filters.module_id"
                                @change="$inertia.get(route('hive.timetable.index'), { ...filters, module_id: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Modules</option>
                                <option v-for="mod in modules" :key="mod.id" :value="mod.id">
                                    {{ mod.code }} - {{ mod.name }}
                                </option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Day</label>
                            <select
                                :value="filters.day"
                                @change="$inertia.get(route('hive.timetable.index'), { ...filters, day: $event.target.value })"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            >
                                <option value="">All Days</option>
                                <option v-for="day in days" :key="day" :value="day">{{ day }}</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Academic Year</label>
                            <input
                                type="text"
                                :value="filters.academic_year"
                                @change="$inertia.get(route('hive.timetable.index'), { ...filters, academic_year: $event.target.value })"
                                placeholder="2026/2027"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            />
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Semester</label>
                            <input
                                type="text"
                                :value="filters.semester"
                                @change="$inertia.get(route('hive.timetable.index'), { ...filters, semester: $event.target.value })"
                                placeholder="1"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm"
                            />
                        </div>
                    </div>
                </div>

                <!-- Timetable Grid -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div v-if="slots.length === 0" class="text-center text-gray-500 py-8">
                            No timetable slots found.
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="slot in slots"
                                :key="slot.id"
                                class="border rounded-lg p-4 hover:shadow-md transition-shadow"
                            >
                                <div class="flex justify-between items-start">
                                    <div>
                                        <span :class="dayColors[slot.day_of_week]" class="px-2 py-1 rounded text-xs font-medium">
                                            {{ slot.day_of_week }}
                                        </span>
                                        <h3 class="text-lg font-semibold mt-2">
                                            {{ slot.module?.code }} - {{ slot.module?.name }}
                                        </h3>
                                        <p class="text-sm text-gray-600">
                                            {{ slot.start_time }} - {{ slot.end_time }}
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            Instructor: {{ slot.instructor?.name }}
                                        </p>
                                        <p v-if="slot.room" class="text-sm text-gray-600">
                                            Room: {{ slot.room.name }} ({{ slot.room.building || 'N/A' }})
                                        </p>
                                    </div>
                                    <div
                                        v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'it-support', 'academic-director', 'program-coordinator', 'registrar'].includes(r.name))"
                                        class="flex gap-2"
                                    >
                                        <a
                                            :href="route('hive.timetable.edit', slot.id)"
                                            class="text-indigo-600 hover:text-indigo-900 text-sm"
                                        >
                                            Edit
                                        </a>
                                        <button
                                            @click="$inertia.delete(route('hive.timetable.destroy', slot.id))"
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
