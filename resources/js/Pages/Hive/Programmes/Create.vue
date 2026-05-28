<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';

const form = useForm({
    name: '',
    description: '',
    duration: '',
    department_id: '',
});

const submit = () => {
    form.post(route('hive.programmes.store'));
};
</script>

<template>
    <Head title="Create Programme" />

    <HiveLayout title="Create Programme">
        <div class="max-w-2xl mx-auto">
            <form @submit.prevent="submit" class="bg-white rounded-lg shadow p-6 space-y-6">
                <div>
                    <InputLabel for="name" value="Programme Name" />
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" required autofocus />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="department_id" value="Department" />
                    <SelectInput id="department_id" class="mt-1 block w-full" v-model="form.department_id" required>
                        <option value="">Select a department</option>
                        <option v-for="dept in $page.props.departments" :key="dept.id" :value="dept.id">
                            {{ dept.name }}
                        </option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.department_id" />
                </div>

                <div>
                    <InputLabel for="duration" value="Duration" />
                    <TextInput id="duration" type="text" class="mt-1 block w-full" v-model="form.duration" placeholder="e.g. 2 years" required />
                    <InputError class="mt-2" :message="form.errors.duration" />
                </div>

                <div>
                    <InputLabel for="description" value="Description" />
                    <textarea id="description" v-model="form.description" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500" rows="3"></textarea>
                    <InputError class="mt-2" :message="form.errors.description" />
                </div>

                <div class="flex justify-end">
                    <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                        Create Programme
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </HiveLayout>
</template>