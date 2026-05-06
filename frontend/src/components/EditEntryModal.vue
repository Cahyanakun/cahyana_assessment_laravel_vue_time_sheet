<script setup>
import { reactive, watch } from 'vue';
import { getInitData, updateTimeEntry } from '../api';

const props = defineProps({
  isOpen: Boolean,
  entry: Object,
  companies: Array
});

const emit = defineEmits(['close', 'updated', 'toast']);

const editForm = reactive({
  id: null,
  company_id: '',
  employee_id: '',
  project_id: '',
  task_id: '',
  hours: '',
  date: '',
  options: {
    employees: [],
    projects: [],
    tasks: [],
    loading: false
  }
});

const loading = reactive({
  submitting: false
});

const validationErrors = reactive({});

watch(() => props.isOpen, async (newVal) => {
  if (newVal && props.entry) {
    Object.assign(editForm, {
      id: props.entry.id,
      company_id: props.entry.company?.id || '',
      employee_id: props.entry.employee?.id || '',
      project_id: props.entry.project?.id || '',
      task_id: props.entry.task?.id || '',
      hours: props.entry.hours,
      date: props.entry.date
    });
    
    // Clear previous errors
    Object.keys(validationErrors).forEach(key => delete validationErrors[key]);
    
    if (editForm.company_id) {
      await fetchDependencies();
    }
  }
});

const fetchDependencies = async () => {
  editForm.options.loading = true;
  try {
    const data = await getInitData(editForm.company_id);
    editForm.options.employees = data.data.employees;
    editForm.options.projects = data.data.projects;
    editForm.options.tasks = data.data.tasks;
  } catch (error) {
    console.error('Error fetching modal dependencies:', error);
  } finally {
    editForm.options.loading = false;
  }
};

const handleCompanyChange = async () => {
  editForm.employee_id = '';
  editForm.project_id = '';
  editForm.task_id = '';
  editForm.options.employees = [];
  editForm.options.projects = [];
  editForm.options.tasks = [];
  if (editForm.company_id) {
    await fetchDependencies();
  }
};

const handleUpdate = async () => {
  if (loading.submitting) return;
  Object.keys(validationErrors).forEach(key => delete validationErrors[key]);
  
  loading.submitting = true;
  try {
    const payload = {
      company_id: editForm.company_id,
      employee_id: editForm.employee_id,
      project_id: editForm.project_id,
      task_id: editForm.task_id,
      hours: editForm.hours,
      date: editForm.date
    };
    
    await updateTimeEntry(editForm.id, payload);
    emit('updated');
    emit('close');
  } catch (error) {
    if (error.response?.status === 422) {
      Object.assign(validationErrors, error.response.data.errors);
    } else {
      emit('toast', { message: 'Error updating entry', type: 'error' });
    }
  } finally {
    loading.submitting = false;
  }
};
</script>

<template>
  <Transition name="modal">
    <div v-if="isOpen" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
      <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="emit('close')"></div>
      
      <div class="bg-white dark:bg-slate-900 w-full max-w-2xl rounded-3xl shadow-2xl relative overflow-hidden border border-slate-200 dark:border-slate-800 animate-modal-enter">
        <div class="p-8 border-b border-slate-100 dark:border-slate-800 flex items-center justify-between bg-slate-50/50 dark:bg-slate-950/20">
          <div>
            <h3 class="text-xl font-black text-slate-800 dark:text-slate-100">Edit Time Entry</h3>
            <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Modify the details of this specific entry.</p>
          </div>
          <button @click="emit('close')" class="p-2 text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
          </button>
        </div>

        <div class="p-8 space-y-6">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Company -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Company</label>
              <select 
                v-model="editForm.company_id"
                @change="handleCompanyChange"
                :class="[
                  'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                  validationErrors.company_id ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                ]"
              >
                <option value="">Select Company</option>
                <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
              </select>
              <p v-if="validationErrors.company_id" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.company_id[0] }}</p>
            </div>

            <!-- Employee -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Employee</label>
              <div class="relative">
                <select 
                  v-model="editForm.employee_id"
                  :disabled="!editForm.company_id || editForm.options.loading"
                  :class="[
                    'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                    validationErrors.employee_id ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                  ]"
                >
                  <option value="">Select Employee</option>
                  <option v-for="e in editForm.options.employees" :key="e.id" :value="e.id">{{ e.name }}</option>
                </select>
                <div v-if="editForm.options.loading" class="absolute right-4 top-1/2 -translate-y-1/2">
                  <div class="animate-spin h-4 w-4 border-2 border-indigo-500 border-t-transparent rounded-full"></div>
                </div>
              </div>
              <p v-if="validationErrors.employee_id" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.employee_id[0] }}</p>
            </div>

            <!-- Project -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Project</label>
              <select 
                v-model="editForm.project_id"
                :disabled="!editForm.company_id || editForm.options.loading"
                :class="[
                  'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                  validationErrors.project_id ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                ]"
              >
                <option value="">Select Project</option>
                <option v-for="p in editForm.options.projects" :key="p.id" :value="p.id">{{ p.name }}</option>
              </select>
              <p v-if="validationErrors.project_id" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.project_id[0] }}</p>
            </div>

            <!-- Task -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Task</label>
              <select 
                v-model="editForm.task_id"
                :disabled="!editForm.company_id || editForm.options.loading"
                :class="[
                  'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all disabled:opacity-40 dark:text-slate-200',
                  validationErrors.task_id ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                ]"
              >
                <option value="">Select Task</option>
                <option v-for="t in editForm.options.tasks" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <p v-if="validationErrors.task_id" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.task_id[0] }}</p>
            </div>

            <!-- Hours -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Hours</label>
              <input 
                type="number" 
                v-model="editForm.hours" 
                step="0.5"
                :class="[
                  'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                  validationErrors.hours ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                ]"
              >
              <p v-if="validationErrors.hours" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.hours[0] }}</p>
            </div>

            <!-- Date -->
            <div class="space-y-2">
              <label class="text-[11px] font-black uppercase tracking-widest text-slate-400 px-1">Date</label>
              <input 
                type="date" 
                v-model="editForm.date"
                :class="[
                  'w-full bg-slate-50 dark:bg-slate-800 border rounded-xl px-5 py-4 text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200',
                  validationErrors.date ? 'border-red-400 ring-4 ring-red-500/10' : 'border-slate-200 dark:border-slate-700'
                ]"
              >
              <p v-if="validationErrors.date" class="text-red-500 text-[10px] font-bold px-1">{{ validationErrors.date[0] }}</p>
            </div>
          </div>
        </div>

        <div class="p-8 bg-slate-50 dark:bg-slate-950/40 border-t border-slate-100 dark:border-slate-800 flex justify-end gap-3">
          <button 
            @click="emit('close')"
            class="px-6 py-3 text-slate-500 hover:text-slate-700 font-black text-sm transition-all"
          >
            Cancel
          </button>
          <button 
            @click="handleUpdate"
            :disabled="loading.submitting || editForm.options.loading"
            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl font-black text-sm transition-all shadow-lg hover:shadow-indigo-200 dark:shadow-none disabled:opacity-50 flex items-center gap-2"
          >
            <span v-if="loading.submitting" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full"></span>
            {{ loading.submitting ? 'Updating...' : 'Update Entry' }}
          </button>
        </div>
      </div>
    </div>
  </Transition>
</template>

<style scoped>
.modal-enter-active, .modal-leave-active {
  transition: opacity 0.3s ease;
}
.modal-enter-from, .modal-leave-to {
  opacity: 0;
}
.animate-modal-enter {
  animation: modal-slide-up 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
@keyframes modal-slide-up {
  from { transform: translateY(20px) scale(0.95); opacity: 0; }
  to { transform: translateY(0) scale(1); opacity: 1; }
}
</style>
