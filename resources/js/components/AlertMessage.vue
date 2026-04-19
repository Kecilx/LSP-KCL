<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
  type?: 'success' | 'error' | 'warning' | 'info';
  message: string;
  dismissible?: boolean;
}>();

const visible = ref(true);

const alertClasses = computed(() => {
  const base = 'mb-4 px-4 py-3 rounded flex items-start justify-between shadow-sm';
  const styles: Record<string, string> = {
    success: 'bg-green-100 border border-green-400 text-green-700',
    error: 'bg-red-100 border border-red-400 text-red-700',
    warning: 'bg-yellow-100 border border-yellow-400 text-yellow-700',
    info: 'bg-blue-100 border border-blue-400 text-blue-700',
  };
  return `${base} ${styles[props.type ?? 'info']}`;
});

const close = () => {
  visible.value = false;
};
</script>

<template>
  <div v-if="visible" :class="alertClasses" role="alert">
    <span class="block sm:inline">{{ props.message }}</span>
    <button
      v-if="props.dismissible"
      @click="close"
      type="button"
      class="ml-4 text-lg font-bold leading-none hover:text-opacity-70 transition"
      aria-label="Tutup alert"
    >
      &times;
    </button>
  </div>
</template>
