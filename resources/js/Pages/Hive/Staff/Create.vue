<script setup>
import { computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import MultiSelect from '@/Components/MultiSelect.vue';

const props = defineProps({
    roles: Array,
});

const roleOptions = computed(() => props.roles.map(r => ({ value: r.name, label: formatRole(r.name) })));

const form = useForm({
    name: '',
    email: '',
    roles: [],
});

const formatRole = (r) => r.replace(/-/g, ' ').replace(/\b\w/g, l => l.toUpperCase());

const submit = () => {
    form.post(route('hive.staff.store'));
};
</script>

<template>
    <HiveLayout title="Create Staff Member">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                Create Staff Member
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
                                <InputLabel for="roles" value="Roles" />
                                <MultiSelect
                                  id="roles"
                                  v-model="form.roles"
                                  :options="roleOptions"
                                  placeholder="Select roles..."
                                />
                                <InputError class="mt-2" :message="form.errors.roles" />
                                <p class="text-xs text-gray-500 mt-1">Hold Ctrl (Windows) or Cmd (Mac) to select multiple roles.</p>
                            </div>

                            <div class="flex items-center justify-end mt-4">
                                <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                                    Create
                                </PrimaryButton>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </HiveLayout>
</template>