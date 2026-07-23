<template>
  <HiveLayout title="Calendar">
    <div class="h-full">
      <FullCalendar :options="calendarOptions" />
    </div>

    <!-- Add/Edit Event Modal -->
    <Modal :show="showModal" @close="showModal = false">
      <div class="p-6">
        <h2 class="text-lg font-medium text-gray-900">
          {{ form.id ? 'Edit Event' : 'Add Event' }}
        </h2>

        <form @submit.prevent="submit">
          <div class="mt-6">
            <label for="title" class="block text-sm font-medium text-gray-700">Title</label>
            <input v-model="form.title" type="text" name="title" id="title" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm" required>
          </div>

          <div class="mt-4">
            <label for="start" class="block text-sm font-medium text-gray-700">Start Date</label>
            <input v-model="form.start" type="datetime-local" name="start" id="start" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm" required>
          </div>

          <div class="mt-4">
            <label for="end" class="block text-sm font-medium text-gray-700">End Date</label>
            <input v-model="form.end" type="datetime-local" name="end" id="end" class="mt-1 block w-full border-gray-300 rounded-md shadow-sm focus:ring-amber-500 focus:border-amber-500 sm:text-sm">
          </div>

          <div class="mt-6 flex justify-end gap-4">
            <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
            <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              {{ form.id ? 'Update Event' : 'Create Event' }}
            </PrimaryButton>
            <DangerButton v-if="form.id" @click="destroy" class="ml-2" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
              Delete
            </DangerButton>
          </div>
        </form>
      </div>
    </Modal>
  </HiveLayout>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import FullCalendar from '@fullcalendar/vue3';
import dayGridPlugin from '@fullcalendar/daygrid';
import timeGridPlugin from '@fullcalendar/timegrid';
import interactionPlugin from '@fullcalendar/interaction';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
  events: Array,
});

const showModal = ref(false);

const form = useForm({
  id: null,
  title: '',
  start: '',
  end: '',
});

const calendarOptions = computed(() => ({
  plugins: [dayGridPlugin, timeGridPlugin, interactionPlugin],
  initialView: 'dayGridMonth',
  headerToolbar: {
    left: 'prev,next today',
    center: 'title',
    right: 'dayGridMonth,timeGridWeek,timeGridDay',
  },
  events: props.events,
  editable: true,
  selectable: true,
  select: handleDateSelect,
  eventClick: handleEventClick,
  eventDrop: handleEventDrop,
}));

function handleDateSelect(selectInfo) {
  form.reset();
  form.start = selectInfo.startStr.slice(0, 16); // Format for datetime-local input
  form.end = selectInfo.endStr.slice(0, 16);
  showModal.value = true;
}

function handleEventClick(clickInfo) {
  form.id = clickInfo.event.id;
  form.title = clickInfo.event.title;
  form.start = clickInfo.event.startStr.slice(0, 16);
  form.end = clickInfo.event.endStr ? clickInfo.event.endStr.slice(0, 16) : '';
  showModal.value = true;
}

function handleEventDrop(dropInfo) {
  const eventData = {
    id: dropInfo.event.id,
    title: dropInfo.event.title,
    start: dropInfo.event.startStr,
    end: dropInfo.event.endStr,
  };
  useForm(eventData).put(route('hive.events.update', { event: eventData.id }), {
    preserveScroll: true,
  });
}

function submit() {
  if (form.id) {
    form.put(route('hive.events.update', { event: form.id }), {
      onSuccess: () => {
        showModal.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('hive.events.store'), {
      onSuccess: () => {
        showModal.value = false;
        form.reset();
      },
    });
  }
}

function destroy() {
  form.delete(route('hive.events.destroy', { event: form.id }), {
    onSuccess: () => {
      showModal.value = false;
      form.reset();
    },
  });
}
</script>