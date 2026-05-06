<script setup>
import { ref, reactive } from 'vue';
import { getInitData, storeTimeEntry } from '../api';

const props = defineProps({
  companies: Array
});

const emit = defineEmits(['submitted', 'toast']);

const createNewEntry = () => ({
  company_id: '',
  employee_id: '',
  project_id: '',
  task_id: '',
  hours: '',
  date: new Date().toISOString().split('T')[0],
  options: {
    employees: [],
    projects: [],
    tasks: [],
    loading: false
  }
});

const entries = ref([createNewEntry()]);
const loading = reactive({ submitting: false });
const validationErrors = ref({});

const addRow = () => {
  entries.value.push(createNewEntry());
};

const removeRow = (index) => {
  if (entries.value.length > 1) {
    entries.value.splice(index, 1);
  } else {
    entries.value[0] = createNewEntry();
  }
  clearRowErrors(index);
};

const clearRowErrors = (index) => {
  const prefix = `entries.${index}.`;
  Object.keys(validationErrors.value).forEach(key => {
    if (key.startsWith(prefix)) {
      delete validationErrors.value[key];
    }
  });
};

const handleCompanyChange = async (index) => {
  const entry = entries.value[index];
  entry.employee_id = '';
  entry.project_id = '';
  entry.task_id = '';
  entry.options.employees = [];
  entry.options.projects = [];
  entry.options.tasks = [];
  
  if (entry.company_id) {
    entry.options.loading = true;
    try {
      const data = await getInitData(entry.company_id);
      entry.options.employees = data.data.employees;
      entry.options.projects = data.data.projects;
      entry.options.tasks = data.data.tasks;
    } catch (error) {
      console.error('Error fetching row dependencies:', error);
    } finally {
      entry.options.loading = false;
    }
  }
  clearRowErrors(index);
};

const handleSubmit = async () => {
  if (loading.submitting) return;
  validationErrors.value = {};

  loading.submitting = true;
  try {
    const payload = entries.value.map(e => ({
      company_id: e.company_id ? parseInt(e.company_id) : null,
      employee_id: e.employee_id ? parseInt(e.employee_id) : null,
      project_id: e.project_id ? parseInt(e.project_id) : null,
      task_id: e.task_id ? parseInt(e.task_id) : null,
      hours: e.hours ? parseFloat(e.hours) : null,
      date: e.date
    }));

    await storeTimeEntry(payload);
    emit('toast', { message: 'Successfully saved all time entries!' });
    entries.value = [createNewEntry()];
    emit('submitted');
  } catch (error) {
    if (error.response && error.response.status === 422) {
      validationErrors.value = error.response.data.errors || {};
      emit('toast', { message: 'Validation failed. Please check fields.', type: 'error' });
    } else {
      emit('toast', { message: 'A server error occurred.', type: 'error' });
    }
  } finally {
    loading.submitting = false;
  }
};
</script>

<template>
  <section class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-indigo-100/50 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden mb-12">
    <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex justify-between items-center bg-indigo-50/20 dark:bg-indigo-950/20">
      <div>
        <h2 class="text-2xl font-bold text-indigo-900 dark:text-indigo-300">Batch Entry</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Add multiple rows and submit them all at once.</p>
      </div>
      <button 
        @click="addRow" 
        class="px-6 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl text-sm font-bold transition-all shadow-md hover:shadow-indigo-200 flex items-center gap-2 group"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 group-hover:scale-110 transition-transform" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z" clip-rule="evenodd" />
        </svg>
        Add Another Row
      </button>
    </div>

    <div class="p-8">
      <div class="overflow-x-auto -mx-8 px-8">
        <table class="w-full text-left border-separate border-spacing-y-3">
          <thead>
            <tr class="text-slate-400 dark:text-slate-500 text-[11px] uppercase tracking-widest font-black">
              <th class="px-5 pb-1">Company</th>
              <th class="px-5 pb-1">Employee</th>
              <th class="px-5 pb-1">Project</th>
              <th class="px-5 pb-1">Task</th>
              <th class="px-5 pb-1 w-28">Hours</th>
              <th class="px-5 pb-1 w-48">Date</th>
              <th class="px-5 pb-1 w-10"></th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(entry, index) in entries" :key="index" class="group transition-all">
              <td class="p-0 align-top">
                <div class="flex flex-col gap-1">
                  <select 
                    v-model="entry.company_id" 
                    @change="handleCompanyChange(index)"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                      validationErrors[`entries.${index}.company_id`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                    <option value="" disabled>Select Company</option>
                    <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
                  </select>
                  <span v-if="validationErrors[`entries.${index}.company_id`]" class="text-[10px] font-bold text-red-500 px-2">Required</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2">
                <div class="flex flex-col gap-1">
                  <select 
                    v-model="entry.employee_id" 
                    :disabled="!entry.company_id || entry.options.loading"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                      validationErrors[`entries.${index}.employee_id`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                    <option value="" disabled>{{ entry.options.loading ? 'Fetching...' : 'Select Employee' }}</option>
                    <option v-for="e in entry.options.employees" :key="e.id" :value="e.id">{{ e.name }}</option>
                  </select>
                  <span v-if="validationErrors[`entries.${index}.employee_id`]" class="text-[10px] font-bold text-red-500 px-2">Required</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2">
                <div class="flex flex-col gap-1">
                  <select 
                    v-model="entry.project_id" 
                    :disabled="!entry.company_id || entry.options.loading"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                      validationErrors[`entries.${index}.project_id`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                    <option value="" disabled>{{ entry.options.loading ? 'Fetching...' : 'Select Project' }}</option>
                    <option v-for="p in entry.options.projects" :key="p.id" :value="p.id">{{ p.name }}</option>
                  </select>
                  <span v-if="validationErrors[`entries.${index}.project_id`]" class="text-[10px] font-bold text-red-500 px-2 line-clamp-1">{{ validationErrors[`entries.${index}.project_id`][0] }}</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2">
                <div class="flex flex-col gap-1">
                  <select 
                    v-model="entry.task_id" 
                    :disabled="!entry.company_id || entry.options.loading"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                      validationErrors[`entries.${index}.task_id`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                    <option value="" disabled>{{ entry.options.loading ? 'Fetching...' : 'Select Task' }}</option>
                    <option v-for="t in entry.options.tasks" :key="t.id" :value="t.id">{{ t.name }}</option>
                  </select>
                  <span v-if="validationErrors[`entries.${index}.task_id`]" class="text-[10px] font-bold text-red-500 px-2 line-clamp-1">{{ validationErrors[`entries.${index}.task_id`][0] }}</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2">
                <div class="flex flex-col gap-1">
                  <input 
                    type="number" 
                    v-model="entry.hours" 
                    step="0.5" 
                    placeholder="0.0"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                      validationErrors[`entries.${index}.hours`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                  <span v-if="validationErrors[`entries.${index}.hours`]" class="text-[10px] font-bold text-red-500 px-2">Invalid</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2">
                <div class="flex flex-col gap-1">
                  <input 
                    type="date" 
                    v-model="entry.date"
                    :class="[
                      'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                      validationErrors[`entries.${index}.date`] ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                    ]"
                  >
                  <span v-if="validationErrors[`entries.${index}.date`]" class="text-[10px] font-bold text-red-500 px-2">Invalid</span>
                </div>
              </td>
              <td class="p-0 align-top pl-2 pt-3 text-center">
                <button 
                  @click="removeRow(index)"
                  class="text-slate-300 hover:text-red-500 transition-colors p-1"
                  title="Remove Row"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                  </svg>
                </button>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div class="mt-12 flex justify-end">
        <button 
          @click="handleSubmit" 
          :disabled="loading.submitting"
          class="px-10 py-4 bg-emerald-600 hover:bg-emerald-700 text-white rounded-2xl font-black text-lg transition-all shadow-lg hover:shadow-emerald-200 disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-3"
        >
          <span v-if="loading.submitting" class="animate-spin h-5 w-5 border-4 border-white border-t-transparent rounded-full"></span>
          {{ loading.submitting ? 'Processing...' : 'Submit All Entries' }}
        </button>
      </div>
    </div>
  </section>
</template>
