<template>
  <HiveLayout title="Grade Management" :description="`${module.name} - Manage student grades`">
    <template #header-actions>
      <Link :href="route('hive.grades.index')"
        class="inline-flex items-center gap-2 text-gray-600 hover:text-gray-800 text-sm font-medium">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
        </svg>
        Back to Modules
      </Link>
    </template>

    <div v-if="module.gradables.length === 0" class="bg-white rounded-xl border border-gray-200 p-12 text-center">
      <h3 class="text-lg font-medium text-gray-900 mb-2">No Assessments Yet</h3>
      <p class="text-gray-500">This module has no gradable assessments.</p>
    </div>

    <div v-for="gradable in module.gradables" :key="gradable.id" class="mb-8">
      <div class="flex items-center justify-between mb-4">
        <div>
          <h2 class="text-xl font-semibold text-gray-800">{{ gradable.title }}</h2>
          <p class="text-sm text-gray-500">
            {{ formatType(gradable.type) }} • Due {{ formatDate(gradable.due_date) }} •
            Max: {{ gradable.max_marks || 100 }} marks
          </p>
        </div>
        <span class="px-3 py-1 text-sm font-semibold rounded-full" :class="getTypeClass(gradable.type)">
          {{ formatType(gradable.type) }}
        </span>
      </div>

      <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
        <table class="w-full text-sm">
          <thead class="bg-gray-50 border-b border-gray-200">
            <tr>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Student</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Submitted</th>
              <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
              <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Grade</th>
              <th class="px-6 py-3"></th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100">
            <tr v-if="!gradable.submissions || gradable.submissions.length === 0">
              <td colspan="5" class="px-6 py-8 text-center text-gray-400">No submissions yet</td>
            </tr>
            <tr v-for="submission in gradable.submissions" :key="submission.id" class="hover:bg-amber-50 transition-colors">
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <img :src="submission.student?.profile_photo_url || '/images/default-avatar.png'"
                    :alt="submission.student?.name"
                    class="w-8 h-8 rounded-full object-cover" />
                  <div>
                    <p class="font-medium text-gray-900">{{ submission.student?.name || 'Unknown' }}</p>
                    <p class="text-xs text-gray-400">{{ submission.student?.email }}</p>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 text-gray-500">
                {{ submission.submitted_at ? formatDate(submission.submitted_at) : '—' }}
              </td>
              <td class="px-6 py-4">
                <span v-if="submission.is_late" class="px-2 py-1 text-xs font-semibold rounded bg-red-100 text-red-800">Late</span>
                <span v-else-if="submission.grade !== null && submission.grade !== undefined" class="px-2 py-1 text-xs font-semibold rounded bg-green-100 text-green-800">Graded</span>
                <span v-else-if="submission.submitted_at" class="px-2 py-1 text-xs font-semibold rounded bg-yellow-100 text-yellow-800">Pending</span>
                <span v-else class="px-2 py-1 text-xs font-semibold rounded bg-gray-100 text-gray-800">Not Submitted</span>
              </td>
              <td class="px-6 py-4 text-right">
                <span v-if="submission.grade !== null && submission.grade !== undefined" class="font-semibold text-gray-900">
                  {{ submission.grade }}/{{ gradable.max_marks || 100 }}
                </span>
                <span v-else class="text-gray-400">—</span>
              </td>
              <td class="px-6 py-4">
                <button
                  v-if="submission.submitted_at"
                  @click="openGradingModal(submission, gradable)"
                  class="px-3 py-1.5 bg-amber-600 text-white text-xs font-medium rounded hover:bg-amber-700 transition"
                >
                  {{ submission.grade !== null && submission.grade !== undefined ? 'Edit Grade' : 'Grade' }}
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Grading Modal -->
    <div v-if="showGradingModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl p-6 w-full max-w-md shadow-2xl">
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Grade Submission</h3>

        <div v-if="selectedSubmission" class="space-y-4">
          <div class="flex items-center gap-3 mb-4 pb-4 border-b border-gray-200">
            <img :src="selectedSubmission.student?.profile_photo_url || '/images/default-avatar.png'"
              :alt="selectedSubmission.student?.name"
              class="w-10 h-10 rounded-full object-cover" />
            <div>
              <p class="font-medium text-gray-900">{{ selectedSubmission.student?.name }}</p>
              <p class="text-sm text-gray-500">{{ selectedSubmission.student?.email }}</p>
            </div>
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Grade (out of {{ selectedGradable?.max_marks || 100 }})</label>
            <input
              v-model.number="gradingForm.grade"
              type="number"
              min="0"
              :max="selectedGradable?.max_marks || 100"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
            />
          </div>

          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">Feedback (optional)</label>
            <textarea
              v-model="gradingForm.feedback"
              rows="3"
              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-amber-500 focus:border-amber-500"
              placeholder="Add feedback for the student..."
            ></textarea>
          </div>
        </div>

        <div class="flex justify-end gap-3 mt-6">
          <button @click="closeGradingModal"
            class="px-4 py-2 text-sm font-medium text-gray-700 hover:text-gray-800">
            Cancel
          </button>
          <button @click="submitGrade"
            :disabled="submitting"
            class="px-4 py-2 bg-amber-600 text-white text-sm font-medium rounded-lg hover:bg-amber-700 disabled:opacity-50">
            {{ submitting ? 'Saving...' : 'Save Grade' }}
          </button>
        </div>
      </div>
    </div>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import dayjs from 'dayjs';

const props = defineProps({
  module: { type: Object, required: true },
});

const showGradingModal = ref(false);
const selectedSubmission = ref(null);
const selectedGradable = ref(null);
const submitting = ref(false);
const gradingForm = ref({ grade: null, feedback: '' });

const formatDate = (date) => dayjs(date).format('MMM D, YYYY h:mm A');

const formatType = (type) => ({
  quiz: 'Quiz',
  test: 'Test',
  assignment: 'Assignment',
  mid_term_exam: 'Mid-Term',
  final_exam: 'Final',
}[type] || type);

const getTypeClass = (type) => ({
  quiz: 'bg-blue-100 text-blue-800',
  test: 'bg-green-100 text-green-800',
  assignment: 'bg-amber-100 text-amber-800',
  mid_term_exam: 'bg-purple-100 text-purple-800',
  final_exam: 'bg-red-100 text-red-800',
}[type] || 'bg-gray-100 text-gray-800');

const openGradingModal = (submission, gradable) => {
  selectedSubmission.value = submission;
  selectedGradable.value = gradable;
  gradingForm.value = {
    grade: submission.grade ?? null,
    feedback: submission.feedback ?? '',
  };
  showGradingModal.value = true;
};

const closeGradingModal = () => {
  showGradingModal.value = false;
  selectedSubmission.value = null;
  selectedGradable.value = null;
  gradingForm.value = { grade: null, feedback: '' };
};

const submitGrade = () => {
  if (!selectedSubmission.value) return;

  submitting.value = true;
  router.post(route('hive.submissions.grade', { submission: selectedSubmission.value.id }), {
    grade: gradingForm.value.grade,
    feedback: gradingForm.value.feedback,
  }, {
    onSuccess: () => {
      closeGradingModal();
    },
    onFinish: () => {
      submitting.value = false;
    },
  });
};
</script>