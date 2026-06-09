<script setup>
import { Link, useForm } from '@inertiajs/vue3';
import { PlusIcon } from '@heroicons/vue/24/outline';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({
    staff: Array,
});

const { isAdmin } = useUser();

const form = useForm({});

const deleteStaff = (id) => {
    if (confirm('Are you sure you want to delete this staff member?')) {
        form.delete(route('hive.staff.destroy', { staff: id }));
    }
};

const getRoleName = (roles) => {
    if (!roles || roles.length === 0) {
        return 'N/A';
    }
    return roles[0].name.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());
};
</script>

<template>
    <HiveLayout title="Staff" description="Manage staff members">
        <template #header-actions>
            <Link v-if="isAdmin" :href="route('hive.staff.create')"
                class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
                <PlusIcon class="w-4 h-4" />
                Create Staff
            </Link>
        </template>

        <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table class="w-full text-sm">
                <thead class="bg-gray-50 border-b border-gray-200">
                    <tr>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Name</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Email</th>
                        <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Role</th>
                        <th class="px-6 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-if="staff.length === 0">
                        <td colspan="4" class="px-6 py-12 text-center text-gray-400">No staff members found.</td>
                    </tr>
                    <tr v-for="staffMember in staff" :key="staffMember.id" class="hover:bg-amber-50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img :src="staffMember.profile_photo_url" :alt="staffMember.name"
                                    class="w-9 h-9 rounded-full object-cover flex-shrink-0" />
                                <div>
                                    <p class="font-medium text-gray-900">{{ staffMember.name }}</p>
                                    <p class="text-xs text-gray-400">{{ staffMember.email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 text-gray-700 hidden md:table-cell">{{ getRoleName(staffMember.roles) }}</td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-3">
                                <Link :href="route('hive.staff.edit', { staff: staffMember.id })"
                                    class="text-amber-600 hover:text-amber-700 font-medium text-xs">Edit</Link>
                                <button v-if="isAdmin" @click="deleteStaff(staffMember.id)"
                                    class="text-red-600 hover:text-red-700 font-medium text-xs">Delete</button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </HiveLayout>
</template>