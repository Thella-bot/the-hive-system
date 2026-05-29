<template>
  <div class="bg-white dark:bg-gray-800 rounded-xl shadow-sm border border-gray-200 dark:border-gray-700 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-semibold text-gray-800 dark:text-white">My Tasks</h3>
      <button
        @click="showAddForm = !showAddForm"
        class="text-amber-600 hover:text-amber-700 dark:text-amber-400 text-sm font-medium"
      >
        + Add
      </button>
    </div>

    <!-- Add Task Form -->
    <div v-if="showAddForm" class="mb-4 p-3 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
      <form @submit.prevent="addTask" class="space-y-2">
        <input
          v-model="newTask.title"
          type="text"
          placeholder="Task title..."
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
        />
        <input
          v-model="newTask.due_date"
          type="date"
          class="w-full px-3 py-2 text-sm border border-gray-300 dark:border-gray-600 rounded-lg bg-white dark:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-amber-500"
        />
        <div class="flex space-x-2">
          <button type="submit" class="px-3 py-1 bg-amber-600 text-white text-xs rounded-lg hover:bg-amber-700">Add</button>
          <button type="button" @click="showAddForm = false" class="px-3 py-1 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-xs rounded-lg">Cancel</button>
        </div>
      </form>
    </div>

    <!-- Tasks List -->
    <div class="space-y-2 max-h-64 overflow-y-auto">
      <div v-if="tasks.length === 0" class="text-center py-6">
        <CheckCircleIcon class="w-10 h-10 mx-auto text-gray-300 dark:text-gray-600 mb-2" />
        <p class="text-sm text-gray-500 dark:text-gray-400">No tasks yet</p>
      </div>

      <div
        v-for="task in tasks"
        :key="task.id"
        class="flex items-center space-x-3 p-3 rounded-lg hover:bg-gray-50 dark:hover:bg-gray-700/50 transition-colors"
        :class="{ 'opacity-50': task.completed }"
      >
        <button
          @click="toggleTask(task)"
          class="flex-shrink-0 w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
          :class="task.completed ? 'bg-green-500 border-green-500' : 'border-gray-300 dark:border-gray-600 hover:border-amber-500'"
        >
          <CheckIcon v-if="task.completed" class="w-3 h-3 text-white" />
        </button>
        <div class="flex-1 min-w-0">
          <p class="text-sm font-medium text-gray-800 dark:text-white" :class="{ 'line-through': task.completed }">
            {{ task.title }}
          </p>
          <p v-if="task.due_date" class="text-xs" :class="getDueDateClass(task.due_date)">
            Due {{ formatDate(task.due_date) }}
          </p>
        </div>
        <button @click="deleteTask(task)" class="text-gray-400 hover:text-red-500">
          <XMarkIcon class="w-4 h-4" />
        </button>
      </div>
    </div>

    <!-- Completed Count -->
    <div v-if="tasks.length > 0" class="mt-4 pt-3 border-t border-gray-100 dark:border-gray-700">
      <div class="flex items-center justify-between">
        <span class="text-xs text-gray-500 dark:text-gray-400">{{ completedCount }} of {{ tasks.length }} completed</span>
        <div class="w-20 bg-gray-200 dark:bg-gray-700 rounded-full h-1.5">
          <div class="bg-green-500 h-1.5 rounded-full transition-all" :style="{ width: completionPercentage + '%' }" />
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { CheckCircleIcon, CheckIcon, XMarkIcon } from '@heroicons/vue/24/outline';
import dayjs from 'dayjs';

const props = defineProps({
  initialTasks: {
    type: Array,
    default: () => []
  }
});

const tasks = ref(props.initialTasks);
const showAddForm = ref(false);
const newTask = ref({ title: '', due_date: '' });

const completedCount = computed(() => tasks.value.filter(t => t.completed).length);
const completionPercentage = computed(() => {
  if (tasks.value.length === 0) return 0;
  return Math.round((completedCount.value / tasks.value.length) * 100);
});

const addTask = async () => {
  if (!newTask.value.title.trim()) return;

  try {
    const res = await fetch('/api/tasks', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
      body: JSON.stringify({ title: newTask.value.title, due_date: newTask.value.due_date }),
      credentials: 'include',
    });
    if (res.ok) {
      const task = await res.json();
      tasks.value.unshift(task);
    }
  } catch (e) {
    // fail silently
  }

  newTask.value = { title: '', due_date: '' };
  showAddForm.value = false;
};

const toggleTask = async (task) => {
  const wasCompleted = task.completed;
  task.completed = !wasCompleted; // optimistic update

  try {
    const res = await fetch(`/api/tasks/${task.id}`, {
      method: 'PATCH',
      headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
      body: JSON.stringify({ completed: task.completed }),
      credentials: 'include',
    });
    if (!res.ok) {
      task.completed = wasCompleted; // revert on failure
    }
  } catch (e) {
    task.completed = wasCompleted; // revert on failure
  }
};

const deleteTask = async (task) => {
  const original = [...tasks.value];
  tasks.value = tasks.value.filter(t => t.id !== task.id);

  try {
    const res = await fetch(`/api/tasks/${task.id}`, {
      method: 'DELETE',
      headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
      credentials: 'include',
    });
    if (!res.ok) {
      tasks.value = original; // revert on failure
    }
  } catch (e) {
    tasks.value = original; // revert on failure
  }
};

const formatDate = (date) => dayjs(date).format('MMM D');

const getDueDateClass = (date) => {
  const now = dayjs();
  const due = dayjs(date);
  const diff = due.diff(now, 'day');

  if (diff < 0) return 'text-red-500 dark:text-red-400 font-medium';
  if (diff <= 2) return 'text-orange-500 dark:text-orange-400 font-medium';
  return 'text-gray-500 dark:text-gray-400';
};
</script>