<template>
  <HiveLayout>
    <div class="bg-white p-6 shadow rounded">
      <h1 class="text-xl font-bold">{{ assignment.title }}</h1>
      <p class="text-gray-600">{{ assignment.description }}</p>
      <p>Due: {{ new Date(assignment.due_date).toLocaleString() }}</p>

      <!-- Student section: submission form -->
      <div v-if="isStudent && !existingSubmission" class="mt-4">
        <form @submit.prevent="submitFile">
          <input type="file" @input="submissionForm.file = $event.target.files[0]" class="block mb-2" />
          <button class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Upload Submission</button>
        </form>
      </div>
      <div v-else-if="isStudent && existingSubmission" class="mt-4 p-2 bg-gray-100">
        <p>Your submission ({{ new Date(existingSubmission.submitted_at).toLocaleString() }})</p>
        <a :href="route('hive.submissions.download', { submission: existingSubmission.id })" class="text-amber-600 underline hover:text-amber-700">Download</a>
        <span v-if="existingSubmission.grade"> | Grade: {{ existingSubmission.grade }}%</span>
        <p v-if="existingSubmission.feedback" class="mt-2"><strong>Feedback:</strong> {{ existingSubmission.feedback }}</p>
      </div>

      <!-- Instructor view: table of submissions -->
      <div v-if="isInstructor" class="mt-6">
        <h2 class="font-semibold">Submissions</h2>
        <table class="w-full">
          <thead><tr><th>Student</th><th>Submitted</th><th>Late</th><th>File</th><th>Grade</th><th>Feedback</th><th>Action</th></tr></thead>
          <tbody>
            <tr v-for="sub in assignment.submissions" :key="sub.id">
              <td>{{ sub.student.name }}</td>
              <td>{{ new Date(sub.submitted_at).toLocaleString() }}</td>
              <td>{{ sub.is_late ? 'Yes' : 'No' }}</td>
              <td><a :href="route('hive.submissions.download', { submission: sub.id })" class="text-amber-600 hover:text-amber-700">Download</a></td>
              <td>{{ sub.grade ?? '-' }}</td>
              <td>{{ sub.feedback ?? '-' }}</td>
              <td><button @click="gradeModal(sub)" class="text-amber-600 hover:text-amber-700">Grade</button></td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
    <!-- Grade modal (simplified inline for example) -->
    <div v-if="grading" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center">
      <div class="bg-white p-4 rounded">
        <h3>Grade for {{ grading.student.name }}</h3>
        <form @submit.prevent="submitGrade">
          <input type="number" v-model="gradingForm.grade" step="0.01" class="border p-2" />
          <textarea v-model="gradingForm.feedback" class="border p-2 w-full" placeholder="Feedback"></textarea>
          <button type="submit" class="bg-amber-600 text-white px-4 py-2 rounded hover:bg-amber-700">Submit Grade</button>
          <button @click="grading=null" class="ml-2">Cancel</button>
        </form>
      </div>
    </div>
  </HiveLayout>
</template>
<script setup>
import { computed, ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import { useUser } from '@/composables/useUser';

const props = defineProps({ assignment: Object });
const { currentUser } = useUser();
const isStudent = computed(() => currentUser.value?.roles?.some(r => r.name === 'student') ?? false);
const isInstructor = computed(() => {
  const user = currentUser.value;
  if (!user) return false;
  return user.roles?.some(r => ['super-admin', 'school-admin', 'academic_staff'].includes(r.name)) &&
    (user.id === props.assignment.instructor_id || user.roles?.some(r => ['super-admin', 'school-admin'].includes(r.name)));
});

const existingSubmission = computed(() => props.assignment.submissions?.find(s => s.student_id === currentUser.value?.id) ?? null);

const submissionForm = useForm({ file: null });
const submitFile = () => {
  submissionForm.post(route('hive.submissions.store', { assignment: props.assignment.id }));
};

const grading = ref(null);
const gradingForm = useForm({ grade: '', feedback: '' });
const gradeModal = (sub) => {
  grading.value = sub;
  gradingForm.grade = sub.grade ?? '';
  gradingForm.feedback = sub.feedback ?? '';
};
const submitGrade = () => {
  gradingForm.post(route('hive.submissions.grade', { submission: grading.value.id }), {
    onSuccess: () => grading.value = null,
  });
};
</script>