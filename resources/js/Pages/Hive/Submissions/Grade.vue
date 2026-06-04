<template>
  <HiveLayout title="Grade Submission">
    <div class="container mx-auto px-4 py-8">
      <div class="bg-white shadow-lg rounded-lg px-8 pt-6 pb-8 mb-4">
        <h1 class="text-3xl font-bold mb-4">{{ submission.gradable.title }}</h1>
        <p class="text-gray-700 text-lg mb-2">
          <span class="font-semibold">Module:</span> {{ submission.gradable.module.name }}
        </p>
        <p class="text-gray-700 text-lg mb-2">
          <span class="font-semibold">Student:</span> {{ submission.student.name }}
        </p>
        <p class="text-gray-700 text-lg mb-2">
          <span class="font-semibold">Submitted At:</span> {{ formatDate(submission.submitted_at) }}
        </p>
        <div class="mt-6">
          <a :href="`/storage/${submission.file_path}`" target="_blank" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
            Download Submission
          </a>
        </div>
        <form @submit.prevent="submitGrade" class="mt-6">
          <div class="mb-4">
            <label for="grade" class="block text-gray-700 text-sm font-bold mb-2">Grade</label>
            <input
              id="grade"
              v-model="form.grade"
              type="number"
              min="0"
              max="100"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
            />
          </div>
          <div class="mb-4">
            <label for="feedback" class="block text-gray-700 text-sm font-bold mb-2">Feedback</label>
            <textarea
              id="feedback"
              v-model="form.feedback"
              class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
              rows="5"
            ></textarea>
          </div>
          <div class="flex items-center justify-between">
            <button
              type="submit"
              class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
              :disabled="form.processing"
            >
              Submit Grade
            </button>
          </div>
        </form>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useForm } from '@inertiajs/vue3';
import dayjs from 'dayjs';

const props = defineProps({
  submission: Object,
});

const form = useForm({
  grade: props.submission.grade,
  feedback: props.submission.feedback,
});

const formatDate = (date) => dayjs(date).format('MMMM D, YYYY h:mm A');

const submitGrade = () => {
  form.post(route('hive.submissions.grade.store', { submission: props.submission.id }), {
    preserveScroll: true,
  });
};
</script>