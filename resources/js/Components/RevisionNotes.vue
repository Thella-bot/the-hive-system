<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">
        <svg class="w-5 h-5 inline mr-2 text-amber-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
        </svg>
        Revision Notes
      </h3>
      <button
        @click="showForm = !showForm"
        class="text-amber-600 hover:text-amber-700 dark:text-amber-400 text-sm font-medium"
      >
        + Add
      </button>
    </div>

    <!-- Add Note Form -->
    <div v-if="showForm" class="mb-4 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg space-y-3">
      <select
        v-model="newNote.module_id"
        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700"
      >
        <option value="">Select module</option>
        <option v-for="m in modules" :key="m.id" :value="m.id">{{ m.name }}</option>
      </select>
      <input
        v-model="newNote.title"
        type="text"
        placeholder="Note title..."
        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700"
      />
      <textarea
        v-model="newNote.content"
        placeholder="Write your notes..."
        rows="3"
        class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700"
      />
      <select v-model="newNote.type" class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700">
        <option value="general">General</option>
        <option value="formula">Formula</option>
        <option value="tip">Tip</option>
        <option value="warning">Warning</option>
      </select>
      <div class="flex space-x-2">
        <button @click="saveNote" class="px-4 py-2 bg-amber-600 text-white text-sm rounded-lg hover:bg-amber-700">Save</button>
        <button @click="showForm = false" class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm rounded-lg">Cancel</button>
      </div>
    </div>

    <!-- Notes List -->
    <div class="space-y-3 max-h-80 overflow-y-auto">
      <div v-if="notes.length === 0" class="text-center py-8">
        <PencilIcon class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No notes yet</p>
        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">Create notes to help with revision</p>
      </div>

      <div
        v-for="note in notes"
        :key="note.id"
        class="p-4 rounded-lg border-l-4"
        :class="getNoteClass(note.type)"
      >
        <div class="flex items-start justify-between">
          <div class="flex-1">
            <div class="flex items-center space-x-2">
              <span class="px-2 py-0.5 text-xs rounded-full" :class="getTypeBadge(note.type)">{{ note.type }}</span>
              <span class="text-xs text-gray-500 dark:text-gray-400">{{ note.module?.name }}</span>
            </div>
            <p class="text-sm font-medium text-gray-800 dark:text-white mt-1">{{ note.title }}</p>
            <p class="text-sm text-gray-600 dark:text-gray-300 mt-1">{{ note.content }}</p>
          </div>
          <div class="flex items-center space-x-1">
            <button @click="editNote(note)" class="text-gray-400 hover:text-amber-600">
              <PencilIcon class="w-4 h-4" />
            </button>
            <button @click="deleteNote(note)" class="text-gray-400 hover:text-red-500">
              <TrashIcon class="w-4 h-4" />
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { PencilIcon, TrashIcon } from '@heroicons/vue/24/outline';

const props = defineProps({
  notes: {
    type: Array,
    default: () => []
  },
  modules: {
    type: Array,
    default: () => []
  }
});

const emit = defineEmits(['save', 'delete']);

const showForm = ref(false);
const newNote = ref({ module_id: '', title: '', content: '', type: 'general' });

const getNoteClass = (type) => ({
  'general': 'bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600',
  'formula': 'bg-blue-50 dark:bg-blue-900/20 border-blue-400 dark:border-blue-500',
  'tip': 'bg-green-50 dark:bg-green-900/20 border-green-400 dark:border-green-500',
  'warning': 'bg-red-50 dark:bg-red-900/20 border-red-400 dark:border-red-500',
}[type] || 'bg-gray-50 dark:bg-gray-700/50 border-gray-300 dark:border-gray-600');

const getTypeBadge = (type) => ({
  'general': 'bg-gray-200 text-gray-700 dark:bg-gray-600 dark:text-gray-300',
  'formula': 'bg-blue-200 text-blue-700 dark:bg-blue-800 dark:text-blue-200',
  'tip': 'bg-green-200 text-green-700 dark:bg-green-800 dark:text-green-200',
  'warning': 'bg-red-200 text-red-700 dark:bg-red-800 dark:text-red-200',
}[type] || 'bg-gray-200 text-gray-700');

const saveNote = () => {
  emit('save', newNote.value);
  newNote.value = { module_id: '', title: '', content: '', type: 'general' };
  showForm.value = false;
};

const editNote = (note) => {
  newNote.value = { ...note };
  showForm.value = true;
};

const deleteNote = (note) => {
  emit('delete', note);
};
</script>