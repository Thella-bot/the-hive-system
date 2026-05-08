<template>
  <IntranetLayout>
    <div class="bg-white p-6 shadow rounded">
      <h1 class="text-xl font-bold">{{ assignment.title }}</h1>
      <p class="text-gray-600">{{ assignment.description }}</p>
      <p>Due: {{ new Date(assignment.due_date).toLocaleString() }}</p>

      <!-- Student section: submission form -->
      <div v-if="isStudent && !existingSubmission" class="mt-4">
        <form @submit.prevent="submitFile">
          <input type="file" @input="submissionForm.file = $event.target.files[0]" class="block mb-2" />
          <button class="bg-green-600 text-white px-4 py-2 rounded">Upload Submission</button>
        </form>
      </div>
      <div v-else-if="isStudent && existingSubmission" class="mt-4 p-2 bg-gray-100">
        <p>Your submission ({{ new Date(existingSubmission.submitted_at).toLocaleString() }})</p>
        <a :href="route('submissions.download', existingSubmission.id)" class="text-blue-600 underline">Download</a>
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
              <td><a :href="route('submissions.download', sub.id)" class="text-blue-600">Download</a></td>
              <td>{{ sub.grade ?? '-' }}</td>
              <td>{{ sub.feedback ?? '-' }}</td>
              <td><button @click="gradeModal(sub)" class="text-indigo-600">Grade</button></td>
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
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded">Submit Grade</button>
          <button @click="grading=null" class="ml-2">Cancel</button>
        </form>
      </div>
    </div>
  </IntranetLayout>
</template>
<script setup>
import { computed, ref } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import IntranetLayout from '@/Layouts/IntranetLayout.vue';

const props = defineProps({ assignment: Object });
const user = usePage().props.user;
const isStudent = user.roles.some(r => r.name === 'student');
const isInstructor = user.roles.some(r => ['admin','instructor'].includes(r.name)) && (user.id === props.assignment.instructor_id || user.roles.some(r=>r.name==='admin'));

const existingSubmission = computed(() => props.assignment.submissions?.find(s => s.student_id === user.id) ?? null);

const submissionForm = useForm({ file: null });
const submitFile = () => {
  submissionForm.post(route('submissions.store', props.assignment.id));
};

const grading = ref(null);
const gradingForm = useForm({ grade: '', feedback: '' });
const gradeModal = (sub) => {
  grading.value = sub;
  gradingForm.grade = sub.grade ?? '';
  gradingForm.feedback = sub.feedback ?? '';
};
const submitGrade = () => {
  gradingForm.post(route('submissions.grade', grading.value.id), {
    onSuccess: () => grading.value = null,
  });
};
</script>
