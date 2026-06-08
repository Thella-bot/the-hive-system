<template>
  <HiveLayout title="Leave Requests" :description="`Leave Balance: ${balance} days`">
    <template #header-actions>
      <Link v-if="!isAdmin" :href="route('hive.leaves.create')"
        class="inline-flex items-center gap-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium px-4 py-2 rounded-lg transition-colors">
        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
        </svg>
        Request Leave
      </Link>
    </template>

    <div class="bg-white rounded-xl border border-gray-200 overflow-hidden">
      <table class="w-full text-sm">
        <thead class="bg-gray-50 border-b border-gray-200">
          <tr>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Start Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">End Date</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Type</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide hidden md:table-cell">Days</th>
            <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wide">Status</th>
            <th v-if="isAdmin" class="px-6 py-3 text-left text-xs font-semibold text-gray-500 uppercase tracking-wide">Action</th>
            <th v-else class="px-6 py-3"></th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-100">
          <tr v-if="leaves.data.length === 0">
            <td colspan="6" class="px-6 py-12 text-center text-gray-400">No leave requests found.</td>
          </tr>
          <tr v-for="leave in leaves.data" :key="leave.id" class="hover:bg-amber-50 transition-colors">
            <td class="px-6 py-4 text-gray-700">
              <span v-if="leave.half_day" class="text-amber-600 mr-1 font-bold" title="Half Day">½</span>
              {{ formatDate(leave.start_date) }}
            </td>
            <td class="px-6 py-4 text-gray-700">{{ formatDate(leave.end_date) }}</td>
            <td class="px-6 py-4">
              <span class="px-2.5 py-1 text-xs font-semibold rounded-full bg-amber-100 text-amber-800">
                {{ formatLeaveType(leave.type) }}
              </span>
            </td>
            <td class="px-6 py-4 text-gray-700 hidden md:table-cell">{{ leave.days }}</td>
            <td class="px-6 py-4">
              <span v-if="leave.is_cancelled" class="px-2.5 py-1 text-xs font-semibold rounded-full bg-gray-200 text-gray-500">Cancelled</span>
              <span v-else class="px-2.5 py-1 text-xs font-semibold rounded-full" :class="statusClass(leave.status)">
                {{ statusLabels[leave.status] ?? leave.status }}
              </span>
            </td>
            <!-- Admin approve/reject actions -->
            <td v-if="isAdmin && leave.status === 'pending' && !leave.is_cancelled" class="px-6 py-4">
              <div class="flex items-center gap-3">
                <button @click="approve(leave.id)" class="text-green-600 hover:text-green-700 font-medium text-xs">Approve</button>
                <button @click="openRejectModal(leave.id)" class="text-red-600 hover:text-red-700 font-medium text-xs">Reject</button>
              </div>
            </td>
            <!-- Show rejection reason link for admins -->
            <td v-else-if="isAdmin && leave.rejection_reason" class="px-6 py-4">
              <button @click="showRejectReason(leave.rejection_reason)" class="text-gray-500 hover:text-gray-700 text-xs underline">View reason</button>
            </td>
            <!-- Staff can cancel their own pending requests -->
            <td v-else-if="!isAdmin && leave.status === 'pending' && !leave.is_cancelled" class="px-6 py-4">
              <button @click="cancel(leave.id)" class="text-red-500 hover:text-red-700 font-medium text-xs">Cancel</button>
            </td>
            <td v-else class="px-6 py-4">
              <span class="text-gray-400 text-xs">—</span>
            </td>
          </tr>
        </tbody>
      </table>

      <!-- Pagination -->
      <div v-if="leaves.total > leaves.per_page" class="px-6 py-3 border-t border-gray-200 flex justify-between items-center text-sm text-gray-600">
        <span>Showing {{ leaves.from }} to {{ leaves.to }} of {{ leaves.total }}</span>
        <div class="flex gap-2">
          <Link v-if="leaves.prev_page_url" :href="leaves.prev_page_url" class="px-3 py-1 border rounded hover:bg-gray-50">&laquo; Prev</Link>
          <Link v-if="leaves.next_page_url" :href="leaves.next_page_url" class="px-3 py-1 border rounded hover:bg-gray-50">Next &raquo;</Link>
        </div>
      </div>
    </div>

    <!-- Reject Modal -->
    <DialogModal :show="showRejectModal" @close="showRejectModal = false">
      <template #title>Reject Leave Request</template>
      <template #content>
        <p class="text-sm text-gray-600 mb-3">Optionally provide a reason for the rejection.</p>
        <textarea v-model="rejectReason" rows="3" class="w-full border-gray-300 rounded-md shadow-sm" placeholder="Reason for rejection..."></textarea>
      </template>
      <template #footer>
        <button @click="showRejectModal = false" class="mr-3 text-sm text-gray-600 hover:text-gray-800">Cancel</button>
        <button @click="confirmReject" class="bg-red-600 text-white px-4 py-2 rounded text-sm hover:bg-red-700">Reject Request</button>
      </template>
    </DialogModal>
  </HiveLayout>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Link, router, usePage } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import DialogModal from '@/Components/DialogModal.vue';
import dayjs from 'dayjs';

const props = defineProps({ leaves: Object, balance: Number });
const isAdmin = computed(() => usePage().props.auth?.user?.roles?.some(r => ['super-admin', 'school-admin'].includes(r)));

const showRejectModal = ref(false);
const rejectReason = ref('');
const pendingRejectId = ref(null);

const formatDate = (d) => d ? dayjs(d).format('MMM D, YYYY') : '—';

const leaveTypeLabels = {
  sick_leave: 'Sick Leave',
  annual_leave: 'Annual Leave',
  personal_leave: 'Personal Leave',
  parental_leave: 'Parental Leave',
  bereavement_leave: 'Bereavement Leave',
};

const formatLeaveType = (type) => leaveTypeLabels[type] ?? type?.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) ?? '';

const statusLabels = {
  pending: 'Pending',
  approved: 'Approved',
  rejected: 'Rejected',
};

const approve = (leaveId) => {
  if (confirm('Approve this leave request?')) {
    router.patch(route('hive.leaves.update', { leave: leaveId }), { status: 'approved' });
  }
};

const openRejectModal = (leaveId) => {
  pendingRejectId.value = leaveId;
  rejectReason.value = '';
  showRejectModal.value = true;
};

const confirmReject = () => {
  router.patch(route('hive.leaves.update', { leave: pendingRejectId.value }), { status: 'rejected', rejection_reason: rejectReason.value });
  showRejectModal.value = false;
};

const cancel = (leaveId) => {
  if (confirm('Cancel this leave request?')) {
    router.delete(route('hive.leaves.destroy', { leave: leaveId }));
  }
};

const showRejectReason = (reason) => {
  alert('Rejection reason: ' + reason);
};

const statusClass = (status) => {
  switch (status) {
    case 'pending': return 'bg-yellow-100 text-yellow-800';
    case 'approved': return 'bg-green-100 text-green-800';
    case 'rejected': return 'bg-red-100 text-red-800';
    default: return 'bg-gray-100 text-gray-800';
  }
};
</script>