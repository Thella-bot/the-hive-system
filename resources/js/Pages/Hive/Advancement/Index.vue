<script setup>
import { Head } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';

defineProps({
    eligibility: Object,
    history: Object,
    academicYears: Array,
});

const statusColors = {
    enrolled: 'bg-blue-100 text-blue-800',
    promoted: 'bg-green-100 text-green-800',
    repeated: 'bg-yellow-100 text-yellow-800',
    graduated: 'bg-purple-100 text-purple-800',
    withdrawn: 'bg-red-100 text-red-800',
};
</script>

<template>
    <Head title="Student Advancement" />
    <HiveLayout>
        <template #header>
            <div class="flex justify-between items-center">
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">Student Advancement</h2>
                <form
                    v-if="$page.props.auth.user.roles.some(r => ['super-admin', 'academic-director'].includes(r.name))"
                    @submit.prevent="$inertia.post(route('hive.advancement.promote-all'))"
                >
                    <button
                        type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-green-500"
                        onclick="return confirm('Promote all eligible students?')"
                    >
                        Promote All Eligible
                    </button>
                </form>
            </div>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <!-- Summary Cards -->
                <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-600">Total Students</p>
                        <p class="text-2xl font-bold">{{ eligibility.total_students || 0 }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-600">Eligible for Promotion</p>
                        <p class="text-2xl font-bold text-green-600">{{ eligibility.eligible?.length || 0 }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-600">Ready to Graduate</p>
                        <p class="text-2xl font-bold text-purple-600">{{ eligibility.graduands?.length || 0 }}</p>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <p class="text-sm text-gray-600">Not Eligible</p>
                        <p class="text-2xl font-bold text-red-600">{{ eligibility.not_eligible?.length || 0 }}</p>
                    </div>
                </div>

                <!-- Eligible for Promotion -->
                <div v-if="eligibility.eligible?.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4 text-green-600">Eligible for Promotion</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Level</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modules</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="data in eligibility.eligible" :key="data.student.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a :href="route('hive.advancement.show', data.student.id)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ data.student.name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Year {{ data.year_level }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ data.modules_passed }}/{{ data.total_required_modules }} passed
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form @submit.prevent="$inertia.post(route('hive.advancement.promote', data.student.id))">
                                                <button type="submit" class="text-green-600 hover:text-green-900 text-sm font-medium">
                                                    Promote
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ready to Graduate -->
                <div v-if="eligibility.graduands?.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4 text-purple-600">Ready to Graduate</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Programme</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Modules</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Action</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="data in eligibility.graduands" :key="data.student.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a :href="route('hive.advancement.show', data.student.id)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ data.student.name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ data.programme?.name }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ data.modules_passed }}/{{ data.total_required_modules }} passed
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <form @submit.prevent="$inertia.post(route('hive.advancement.promote', data.student.id))">
                                                <button type="submit" class="text-purple-600 hover:text-purple-900 text-sm font-medium">
                                                    Graduate
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Not Eligible -->
                <div v-if="eligibility.not_eligible?.length > 0" class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4 text-red-600">Not Eligible</h3>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Level</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Reason</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="data in eligibility.not_eligible" :key="data.student.id">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <a :href="route('hive.advancement.show', data.student.id)" class="text-indigo-600 hover:text-indigo-900">
                                                {{ data.student.name }}
                                            </a>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Year {{ data.year_level }}</td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ data.reason }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Academic History -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="font-semibold text-lg mb-4">Academic History</h3>
                        <div v-if="history.data.length === 0" class="text-center text-gray-500 py-8">
                            No academic history records.
                        </div>
                        <div v-else class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Student</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Academic Year</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Year Level</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Date</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <tr v-for="record in history.data" :key="record.id">
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ record.user?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ record.academicYear?.name }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            Year {{ record.year_level }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span :class="statusColors[record.status]" class="px-2 py-1 rounded text-xs font-medium capitalize">
                                                {{ record.status }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                            {{ new Date(record.created_at).toLocaleDateString() }}
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>
