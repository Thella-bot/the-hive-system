<template>
  <div>
    <label class="block text-sm font-medium text-gray-700 mb-1.5">
      <slot />
    </label>
    <div class="relative">
      <div
        class="w-full border border-gray-300 rounded-lg px-3.5 py-2.5 text-sm focus:ring-2 focus:ring-amber-500 focus:border-amber-500 outline-none transition bg-white min-h-[42px] cursor-pointer"
        :class="{ 'border-red-400': error }"
        @click="open = !open"
      >
        <div v-if="selected.length === 0" class="text-gray-400">
          {{ placeholder }}
        </div>
        <div v-else class="flex flex-wrap gap-1.5">
          <span
            v-for="item in selected"
            :key="item.value"
            class="inline-flex items-center gap-1 px-2 py-0.5 bg-amber-50 text-amber-700 rounded-md text-xs font-medium border border-amber-200"
          >
            {{ item.label }}
            <button
              type="button"
              class="hover:text-amber-900 transition-colors"
              @click.stop="remove(item.value)"
            >
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </button>
          </span>
        </div>
      </div>

      <div
        v-if="open"
        class="absolute z-50 w-full mt-1 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-auto"
      >
        <div
          v-for="option in options"
          :key="option.value"
          class="flex items-center gap-2 px-3 py-2 cursor-pointer hover:bg-amber-50 transition-colors"
          :class="{ 'bg-amber-50': isSelected(option.value) }"
          @click="toggleOption(option.value)"
        >
          <div
            class="w-4 h-4 rounded border flex items-center justify-center transition-colors"
            :class="isSelected(option.value) ? 'bg-amber-600 border-amber-600' : 'border-gray-300'"
          >
            <svg v-if="isSelected(option.value)" class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
            </svg>
          </div>
          <span class="text-sm text-gray-700">{{ option.label }}</span>
        </div>
        <div v-if="options.length === 0" class="px-3 py-2 text-sm text-gray-400">
          No options available
        </div>
      </div>
    </div>
    <p v-if="error" class="text-red-500 text-xs mt-1">{{ error }}</p>
    <p v-if="hint" class="text-xs text-gray-500 mt-1">{{ hint }}</p>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  modelValue: {
    type: Array,
    default: () => [],
  },
  options: {
    type: Array,
    default: () => [],
  },
  placeholder: {
    type: String,
    default: 'Select...',
  },
  error: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
})

const emit = defineEmits(['update:modelValue'])

const open = ref(false)

const selected = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
})

const isSelected = (value) => {
  return selected.value.includes(value)
}

const toggleOption = (value) => {
  if (isSelected(value)) {
    selected.value = selected.value.filter((v) => v !== value)
  } else {
    selected.value = [...selected.value, value]
  }
}

const remove = (value) => {
  selected.value = selected.value.filter((v) => v !== value)
}

const close = (e) => {
  if (!e.target.closest('.relative')) {
    open.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', close)
})

onUnmounted(() => {
  document.removeEventListener('click', close)
})
</script>
