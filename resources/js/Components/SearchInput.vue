<template>
  <form @submit.prevent="submit" class="relative w-full max-w-sm">
    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
      <svg class="h-5 w-5 text-gray-400" viewBox="0 0 20 20" fill="currentColor">
        <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd" />
      </svg>
    </div>
    <input
      ref="input"
      v-model="form.query"
      type="search"
      name="query"
      id="search"
      autocomplete="off"
      class="block w-full rounded-lg border-gray-200 pl-10 pr-9 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-amber-500 focus:outline-none focus:ring-2 focus:ring-amber-500/20 bg-white dark:bg-gray-800 dark:border-gray-600 dark:text-white dark:placeholder-gray-500"
      :placeholder="placeholder"
    />
    <button
      v-if="form.query"
      type="button"
      @click="clear"
      class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
      aria-label="Clear search"
    >
      <XMarkIcon class="h-4 w-4" />
    </button>
  </form>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { useForm, usePage } from '@inertiajs/vue3';
import { XMarkIcon } from '@heroicons/vue/24/outline';

const page = usePage();
const input = ref(null);

const props = defineProps({
  // Lets a page drive the value instead of letting the input own it.
  modelValue: { type: String, default: undefined },
  placeholder: { type: String, default: 'Search...' },
});

const emit = defineEmits(['update:modelValue', 'search']);

// The controller reads `query`; a mismatched name here silently searched for an
// empty string.
const form = useForm({
  query: props.modelValue ?? page.props.query ?? '',
});

if (props.modelValue !== undefined) {
  watch(() => props.modelValue, (value) => {
    form.query = value ?? '';
  });
}

watch(() => form.query, (value) => {
  emit('update:modelValue', value);
});

const clear = () => {
  form.query = '';
  input.value?.focus();
};

const submit = () => {
  emit('search', form.query);

  // When used standalone in the header, navigate. Pages that pass a v-model
  // handle their own submission and can ignore this.
  if (props.modelValue === undefined) {
    form.get(route('hive.search'), {
      preserveState: true,
      preserveScroll: true,
    });
  }
};

// "/" focuses search from anywhere except while already typing.
const onKeydown = (event) => {
  if (event.key !== '/' || event.metaKey || event.ctrlKey || event.altKey) return;

  const tag = document.activeElement?.tagName?.toLowerCase();
  const typing = ['input', 'textarea', 'select'].includes(tag) || document.activeElement?.isContentEditable;

  if (typing) return;

  event.preventDefault();
  input.value?.focus();
};

onMounted(() => window.addEventListener('keydown', onKeydown));
onBeforeUnmount(() => window.removeEventListener('keydown', onKeydown));
</script>
