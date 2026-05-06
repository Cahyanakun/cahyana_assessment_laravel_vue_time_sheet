<script setup>
import { ref, reactive, onMounted } from 'vue';
import { getInitData } from './api';
import TimeEntryForm from './components/TimeEntryForm.vue';
import TimeEntryHistory from './components/TimeEntryHistory.vue';
import EditEntryModal from './components/EditEntryModal.vue';

const companies = ref([]);
const historyRef = ref(null);
const isDark = ref(localStorage.getItem('theme') === 'dark');
const toast = reactive({ show: false, message: '', type: 'success' });

// Modal State
const isEditModalOpen = ref(false);
const selectedEntry = ref(null);

const showToast = ({ message, type = 'success' }) => {
  toast.message = message;
  toast.type = type;
  toast.show = true;
  setTimeout(() => toast.show = false, 5000);
};

const toggleDark = () => {
  isDark.value = !isDark.value;
  localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  document.documentElement.classList.toggle('dark', isDark.value);
};

const openEdit = (entry) => {
  selectedEntry.value = entry;
  isEditModalOpen.value = true;
};

const refreshHistory = () => {
  historyRef.value?.refresh();
};

onMounted(async () => {
  try {
    const data = await getInitData();
    companies.value = data.data.companies;
  } catch (error) {
    showToast({ message: 'Failed to load initial data.', type: 'error' });
  }
  if (isDark.value) document.documentElement.classList.add('dark');
});
</script>

<template>
  <div :class="{'dark': isDark}" class="min-h-screen bg-slate-50 text-slate-900 font-sans p-4 md:p-8 transition-colors duration-500 dark:bg-slate-950 dark:text-slate-100">
    <div class="max-w-7xl mx-auto">
      <header class="mb-10 flex flex-col items-center">
        <div class="w-full flex justify-end mb-4">
          <button @click="toggleDark" class="p-3 rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm hover:shadow-md transition-all group">
            <svg v-if="isDark" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-yellow-400 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 5a7 7 0 100 14 7 7 0 000-14z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-indigo-600 group-hover:-rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
            </svg>
          </button>
        </div>
        <h1 class="text-5xl font-black text-indigo-950 dark:text-indigo-400 tracking-tight mb-3">Time Tracker</h1>
        <p class="text-slate-500 dark:text-slate-400 text-lg">Efficiently manage bulk time entries with ease.</p>
      </header>

      <TimeEntryForm :companies="companies" @submitted="refreshHistory" @toast="showToast" />
      <TimeEntryHistory ref="historyRef" :companies="companies" @edit="openEdit" />
    </div>

    <EditEntryModal 
      :isOpen="isEditModalOpen" 
      :entry="selectedEntry" 
      :companies="companies" 
      @close="isEditModalOpen = false" 
      @updated="refreshHistory" 
      @toast="showToast"
    />

    <!-- Global Toast -->
    <Transition name="toast">
      <div v-if="toast.show" class="fixed bottom-10 right-10 px-8 py-5 rounded-2xl shadow-2xl flex items-center gap-4 z-[200] transition-all border-l-[6px] bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800" :class="toast.type === 'success' ? 'border-emerald-500' : 'border-red-500'">
        <div class="flex-shrink-0">
          <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-emerald-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-8 w-8 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <div class="flex flex-col">
          <span class="font-black text-sm uppercase tracking-wider dark:text-slate-100">{{ toast.type === 'success' ? 'Success' : 'Attention' }}</span>
          <span class="text-slate-500 dark:text-slate-400 text-sm">{{ toast.message }}</span>
        </div>
        <button @click="toast.show = false" class="ml-4 text-slate-300 hover:text-slate-500 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </Transition>
  </div>
</template>

<style>
@import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap');
body { font-family: 'Plus Jakarta Sans', sans-serif; overflow-x: hidden; }
.toast-enter-active, .toast-leave-active { transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275); }
.toast-enter-from, .toast-leave-to { opacity: 0; transform: translateX(50px) scale(0.9); }
table { border-spacing: 0 12px !important; }
</style>
