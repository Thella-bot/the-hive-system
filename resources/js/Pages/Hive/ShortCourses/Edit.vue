<script setup>
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import SelectInput from '@/Components/SelectInput.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import Checkbox from '@/Components/Checkbox.vue';

const props = defineProps({
  shortCourse: { type: Object, required: true },
  departments: { type: Array, required: true },
  programmes: { type: Array, required: true },
});

const form = useForm({
  title: props.shortCourse.title,
  description: props.shortCourse.description ?? '',
  type: props.shortCourse.type,
  duration: props.shortCourse.duration,
  price: props.shortCourse.price ?? '',
  start_date: props.shortCourse.start_date ?? '',
  end_date: props.shortCourse.end_date ?? '',
  is_active: props.shortCourse.is_active,
  accepting_applications: props.shortCourse.accepting_applications,
  courseable_type: props.shortCourse.courseable_type,
  courseable_id: props.shortCourse.courseable_id,
});

const courseTypes = [
  { value: 'workshop', label: 'Workshop' },
  { value: 'training', label: 'Training' },
  { value: 'short-course', label: 'Short Course' },
];

const submit = () => {
  form.patch(route('hive.short-courses.update', { short_course: props.shortCourse.id }), {
    onSuccess: () => form.reset(),
  });
};
</script>

<template>
  <Head title="Edit Short Course" />

  <HiveLayout :title="shortCourse.title" description="Edit workshop or short course details">
    <div class="max-w-2xl mx-auto">
      <form @submit.prevent="submit" class="bg-white rounded-xl shadow p-6 space-y-6">

        <!-- Title -->
        <div>
          <InputLabel for="title" value="Title" />
          <TextInput id="title" type="text" class="mt-1 block w-full" v-model="form.title" required />
          <InputError class="mt-2" :message="form.errors.title" />
        </div>

        <!-- Type -->
        <div>
          <InputLabel for="type" value="Type" />
          <SelectInput id="type" class="mt-1 block w-full" v-model="form.type" required>
            <option v-for="t in courseTypes" :key="t.value" :value="t.value">{{ t.label }}</option>
          </SelectInput>
          <InputError class="mt-2" :message="form.errors.type" />
        </div>

        <!-- Owner -->
        <div>
          <InputLabel for="courseable_type" value="Associated With" />
          <SelectInput id="courseable_type" class="mt-1 block w-full" v-model="form.courseable_type" required>
            <option value="App\\Models\\Department">Department</option>
            <option value="App\\Models\\Programme">Programme</option>
          </SelectInput>
          <InputError class="mt-2" :message="form.errors.courseable_type" />
        </div>

        <div>
          <InputLabel for="courseable_id" value="Select Department or Programme" />
          <SelectInput v-if="form.courseable_type === 'App\\Models\\Department'" id="courseable_id" class="mt-1 block w-full" v-model="form.courseable_id" required>
            <option value="">Select a department</option>
            <option v-for="dept in departments" :key="dept.id" :value="dept.id">{{ dept.name }}</option>
          </SelectInput>
          <SelectInput v-else id="courseable_id" class="mt-1 block w-full" v-model="form.courseable_id" required>
            <option value="">Select a programme</option>
            <option v-for="prog in programmes" :key="prog.id" :value="prog.id">{{ prog.name }} ({{ prog.department?.name }})</option>
          </SelectInput>
          <InputError class="mt-2" :message="form.errors.courseable_id" />
        </div>

        <!-- Duration -->
        <div>
          <InputLabel for="duration" value="Duration" />
          <TextInput id="duration" type="text" class="mt-1 block w-full" v-model="form.duration" required
            placeholder="e.g., 3 days, 1 week, 2 weeks" />
          <InputError class="mt-2" :message="form.errors.duration" />
        </div>

        <!-- Description -->
        <div>
          <InputLabel for="description" value="Description" />
          <textarea id="description" v-model="form.description"
            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-amber-500 focus:ring-amber-500"
            rows="3"></textarea>
          <InputError class="mt-2" :message="form.errors.description" />
        </div>

        <!-- Price -->
        <div>
          <InputLabel for="price" value="Price (LSL)" />
          <TextInput id="price" type="number" min="0" step="0.01" class="mt-1 block w-full" v-model="form.price"
            placeholder="0 for free" />
          <InputError class="mt-2" :message="form.errors.price" />
        </div>

        <!-- Dates -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <InputLabel for="start_date" value="Start Date" />
            <TextInput id="start_date" type="date" class="mt-1 block w-full" v-model="form.start_date" />
            <InputError class="mt-2" :message="form.errors.start_date" />
          </div>
          <div>
            <InputLabel for="end_date" value="End Date" />
            <TextInput id="end_date" type="date" class="mt-1 block w-full" v-model="form.end_date" />
            <InputError class="mt-2" :message="form.errors.end_date" />
          </div>
        </div>

        <!-- Toggles -->
        <div class="flex flex-col gap-3">
          <label class="flex items-center gap-3">
            <Checkbox v-model:checked="form.is_active" />
            <span class="text-sm text-gray-700">Active</span>
          </label>
          <label class="flex items-center gap-3">
            <Checkbox v-model:checked="form.accepting_applications" />
            <span class="text-sm text-gray-700">Accepting Applications</span>
          </label>
        </div>

        <!-- Submit -->
        <div class="flex justify-end">
          <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
            Update Short Course
          </PrimaryButton>
        </div>
      </form>
    </div>
  </HiveLayout>
</template>
