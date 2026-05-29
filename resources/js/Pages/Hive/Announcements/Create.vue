<template>
  <HiveLayout>
    <h1 class="text-2xl font-bold mb-4">New Announcement</h1>
    <form @submit.prevent="submit" class="max-w-2xl space-y-5" enctype="multipart/form-data">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
        <input v-model="form.title" class="w-full border-gray-300 rounded-md shadow-sm" required />
        <InputError :message="form.errors.title" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Body</label>
        <!-- Simple rich text toolbar -->
        <div class="border border-gray-300 rounded-md overflow-hidden mb-1">
          <div class="flex items-center gap-1 px-2 py-1 bg-gray-50 border-b border-gray-200">
            <button type="button" @click="execCmd('bold')" class="px-2 py-1 text-sm rounded hover:bg-gray-200 font-bold" title="Bold">B</button>
            <button type="button" @click="execCmd('italic')" class="px-2 py-1 text-sm rounded hover:bg-gray-200 italic" title="Italic">I</button>
            <button type="button" @click="execCmd('underline')" class="px-2 py-1 text-sm rounded hover:bg-gray-200 underline" title="Underline">U</button>
            <span class="w-px h-4 bg-gray-300 mx-1"></span>
            <button type="button" @click="execCmd('insertUnorderedList')" class="px-2 py-1 text-sm rounded hover:bg-gray-200" title="Bullet List">• List</button>
            <button type="button" @click="execCmd('insertOrderedList')" class="px-2 py-1 text-sm rounded hover:bg-gray-200" title="Numbered List">1. List</button>
            <span class="w-px h-4 bg-gray-300 mx-1"></span>
            <button type="button" @click="execCmd('undo')" class="px-2 py-1 text-xs rounded hover:bg-gray-200">↩ Undo</button>
            <button type="button" @click="execCmd('redo')" class="px-2 py-1 text-xs rounded hover:bg-gray-200">↪ Redo</button>
          </div>
          <div
            ref="editorEl"
            contenteditable="true"
            class="w-full min-h-[150px] p-3 outline-none text-sm text-gray-800"
            @input="onEditorInput"
            @paste="onPaste"
          ></div>
        </div>
        <!-- Hidden inputs for plain body (fallback) and html body -->
        <input type="hidden" name="body" :value="form.body" />
        <input type="hidden" name="body_html" :value="form.body_html" />
        <InputError :message="form.errors.body" />
        <p class="text-xs text-gray-500 mt-1">Tip: Select text and use the toolbar to format, or paste from a document.</p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Attachments <span class="text-gray-400">(optional)</span></label>
        <input type="file" multiple @change="onFilesChanged" class="w-full text-sm text-gray-600 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-amber-50 file:text-amber-700 hover:file:bg-amber-100" />
        <div v-if="selectedFiles.length" class="mt-1 text-xs text-gray-500">
          <span v-for="(f, i) in selectedFiles" :key="i" class="mr-2">{{ f.name }}</span>
        </div>
        <InputError :message="form.errors.attachments" />
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Category</label>
        <select v-model="form.category" class="w-full border-gray-300 rounded-md shadow-sm">
          <option value="general">General</option>
          <option value="academic">Academic</option>
          <option value="event">Event</option>
          <option value="module">Module</option>
          <option value="staff">Staff</option>
          <option value="hr">HR</option>
          <option value="administrative">Administrative</option>
          <option value="financial">Financial</option>
          <option value="health_safety">Health & Safety</option>
        </select>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Priority</label>
        <select v-model="form.priority" class="w-full border-gray-300 rounded-md shadow-sm">
          <option value="normal">Normal</option>
          <option value="urgent">Urgent</option>
          <option value="emergency" class="bg-red-600 text-white">🚨 Emergency — Broadcast to All Now</option>
        </select>
        <p class="text-xs text-red-500 mt-1" v-if="form.priority === 'emergency'">
          ⚠️ Emergency alerts will appear as a full-screen notification on all active devices immediately.
        </p>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Target Roles <span class="text-gray-400">(leave empty for all)</span></label>
        <div class="flex flex-wrap gap-2">
          <label v-for="role in availableRoles" :key="role.value" class="flex items-center gap-1.5 text-sm text-gray-700">
            <input type="checkbox" :value="role.value" v-model="form.target_roles" class="rounded border-gray-300 text-amber-600 shadow-sm" />
            {{ role.label }}
          </label>
        </div>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Target Modules <span class="text-gray-400">(optional)</span></label>
        <div class="border border-gray-200 rounded-md p-2 max-h-40 overflow-y-auto">
          <label v-for="mod in modules" :key="mod.id" class="flex items-center gap-2 py-1 text-sm text-gray-700">
            <input type="checkbox" :value="mod.id" v-model="form.target_modules" class="rounded border-gray-300 text-amber-600 shadow-sm" />
            <span>{{ mod.code }} - {{ mod.name }}</span>
          </label>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <input type="checkbox" v-model="form.is_pinned" id="is_pinned" class="rounded border-gray-300 text-amber-600 shadow-sm" />
        <label for="is_pinned" class="text-sm text-gray-700">Pin this announcement</label>
      </div>

      <div>
        <label class="block text-sm font-medium text-gray-700 mb-1">Expires at <span class="text-gray-400">(optional)</span></label>
        <input type="datetime-local" v-model="form.expires_at" class="w-full border-gray-300 rounded-md shadow-sm" />
      </div>

      <div>
        <PrimaryButton :disabled="form.processing" class="bg-amber-600 hover:bg-amber-700">Publish Announcement</PrimaryButton>
      </div>
    </form>
  </HiveLayout>
</template>

<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import HiveLayout from '@/Layouts/HiveLayout.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';

defineProps({ modules: Array });

const form = useForm({
  title: '',
  body: '',
  body_html: '',
  category: 'general',
  target_roles: [],
  target_modules: [],
  is_pinned: false,
  priority: 'normal',
  expires_at: '',
});

const editorEl = ref(null);
const selectedFiles = ref([]);

const availableRoles = [
  { value: 'student', label: 'Student' },
  { value: 'academic_staff', label: 'Academic Staff' },
  { value: 'non_academic_staff', label: 'Non-Academic Staff' },
  { value: 'school-admin', label: 'School Admin' },
  { value: 'super-admin', label: 'Super Admin' },
];

const execCmd = (command) => {
  document.execCommand(command, false, null);
  editorEl.value?.focus();
};

const onEditorInput = () => {
  if (editorEl.value) {
    form.body_html = editorEl.value.innerHTML;
    form.body = editorEl.value.innerText?.trim() || '';
  }
};

const onPaste = (e) => {
  e.preventDefault();
  const text = e.clipboardData.getData('text/plain');
  document.execCommand('insertText', false, text);
};

const onFilesChanged = (e) => {
  selectedFiles.value = Array.from(e.target.files);
};

const submit = () => {
  if (editorEl.value) {
    form.body_html = editorEl.value.innerHTML;
    form.body = editorEl.value.innerText?.trim() || '';
  }

  const data = new FormData();
  Object.entries(form.data()).forEach(([key, val]) => {
    if (key === 'target_roles' || key === 'target_modules') {
      val.forEach(v => data.append(key + '[]', v));
    } else if (val !== null && val !== undefined) {
      data.append(key, val);
    }
  });
  selectedFiles.value.forEach(f => data.append('attachments[]', f));

  data.append('_method', 'POST');

  fetch(route('hive.announcements.store'), {
    method: 'POST',
    headers: { 'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]')?.content },
    body: data,
  }).then(res => {
    if (res.ok) {
      window.location.href = route('hive.announcements.index');
    }
  });
};
</script>