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
    delivery_mode: 'in_person',
    meeting_platform: '',
    meeting_link: '',
    location: '',
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
            <form @submit.prevent="submit" class="bg-white rounded-xl border border-gray-200 dark:border-gray-700 p-6 space-y-6">
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
                    <InputLabel for="delivery_mode" value="Delivery Mode" />
                    <SelectInput id="delivery_mode" class="mt-1 block w-full" v-model="form.delivery_mode" required>
                        <option value="in_person">In Person</option>
                        <option value="online">Online</option>
                        <option value="hybrid">Hybrid</option>
                    </SelectInput>
                    <InputError class="mt-2" :message="form.errors.delivery_mode" />
                </div>

                <div class="grid md:grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="meeting_platform" value="Meeting Platform" />
                        <TextInput id="meeting_platform" type="text" class="mt-1 block w-full" v-model="form.meeting_platform" placeholder="Zoom, Google Meet, Teams" />
                        <InputError class="mt-2" :message="form.errors.meeting_platform" />
                    </div>
                    <div>
                        <InputLabel for="meeting_link" value="Meeting Link" />
                        <TextInput id="meeting_link" type="url" class="mt-1 block w-full" v-model="form.meeting_link" placeholder="https://..." />
                        <InputError class="mt-2" :message="form.errors.meeting_link" />
                    </div>
                </div>

                <div>
                    <InputLabel for="location" value="Venue / Location" />
                    <TextInput id="location" type="text" class="mt-1 block w-full" v-model="form.location" placeholder="Physical venue or classroom name" />
                    <InputError class="mt-2" :message="form.errors.location" />
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