<script setup>
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    managedStaff: Object,
    roles: Array,
});

const form = useForm({
    name: props.managedStaff.name,
    email: props.managedStaff.email,
    role_id: props.managedStaff.roles[0]?.id || '',
});

const submit = () => {
    form.put(route('hive.staff.update', { staff: props.managedStaff.id }));
};
</script>

<template>
    <HiveLayout title="Edit Staff Member">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Edit Staff Member
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <div>
                                <InputLabel for="name" value="Name" />
                                <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="email" value="Email" />
                                <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" required />
                                <InputError class="mt-2" :message="form.errors.email" />
                            </div>

                            <div class="mt-4">
                                <InputLabel for="role" value="Role" />
                                <select id="role" class="mt-1 block w-full border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm" v-model="form.role_id" required>
                                    <option value="">Select a role</option>
                                    <option v-for="role in roles" :key="role.id" :value="role.id">{{ role.name.replace(/_/g, ' ').replace(/\b\w/g, l => l.toUpperCase()) }}</option>
                                </select>
                                <InputError class="mt-2" :message="form.errors.role_id" />
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Update
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>