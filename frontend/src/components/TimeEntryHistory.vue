<script setup>
import { reactive, watch } from 'vue';
import { getTimeEntries } from '../api';

const props = defineProps({
  companies: Array
});

const emit = defineEmits(['edit']);

const history = ref([]);
const loading = reactive({ history: false });
const filters = reactive({
  search: '',
  company_id: '',
  sort_by: 'date',
  sort_order: 'desc',
  page: 1,
  per_page: 10
});

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 10
});

const fetchHistory = async (page = 1) => {
  loading.history = true;
  filters.page = page;
  try {
    const data = await getTimeEntries(filters);
    history.value = data.data.items;
    Object.assign(pagination, data.data.meta);
  } catch (error) {
    console.error('Error fetching history:', error);
  } finally {
    loading.history = false;
  }
};

const handleSort = (column) => {
  if (filters.sort_by === column) {
    filters.sort_order = filters.sort_order === 'asc' ? 'desc' : 'asc';
  } else {
    filters.sort_by = column;
    filters.sort_order = 'asc';
  }
  fetchHistory(1);
};

let searchTimeout;
watch(() => filters.search, () => {
  clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => fetchHistory(1), 500);
});

watch(() => filters.company_id, () => fetchHistory(1));

// Expose refresh method to parent
defineExpose({ refresh: () => fetchHistory(1) });

import { ref, onMounted } from 'vue';
onMounted(() => fetchHistory());
</script>

<template>
  <section class="bg-white dark:bg-slate-900 rounded-3xl shadow-xl shadow-slate-200/50 dark:shadow-none border border-slate-200 dark:border-slate-800 overflow-hidden">
    <div class="p-8 border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/20">
      <div class="flex items-center justify-between mb-6">
        <h2 class="text-2xl font-bold text-slate-800 dark:text-slate-200">Recent History</h2>
        <button @click="fetchHistory(1)" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 text-sm font-bold flex items-center gap-1 transition-colors">
          <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" :class="{'animate-spin': loading.history}">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
          </svg>
          Refresh List
        </button>
      </div>
      
      <div class="flex flex-col md:flex-row gap-4">
        <div class="flex-1 relative">
          <span class="absolute inset-y-0 left-0 pl-4 flex items-center text-slate-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
          </span>
          <input 
            v-model="filters.search"
            type="text" 
            placeholder="Search by employee or company..."
            class="w-full pl-12 pr-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200"
          >
        </div>
        <div class="w-full md:w-64">
          <select 
            v-model="filters.company_id"
            class="w-full px-4 py-3 bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl text-sm focus:ring-4 focus:ring-indigo-500/10 focus:border-indigo-500 outline-none transition-all dark:text-slate-200"
          >
            <option value="">All Companies</option>
            <option v-for="c in companies" :key="c.id" :value="c.id">{{ c.name }}</option>
          </select>
        </div>
      </div>
    </div>

    <div class="p-0">
      <div v-if="loading.history" class="p-20 flex flex-col items-center gap-4 text-slate-400">
        <div class="animate-spin h-10 w-10 border-4 border-indigo-500 border-t-transparent rounded-full"></div>
        <span class="font-bold tracking-widest text-xs uppercase">Fetching Records</span>
      </div>

      <div v-else class="overflow-x-auto">
        <table class="w-full text-left">
          <thead>
            <tr class="text-slate-400 dark:text-slate-500 text-[11px] uppercase tracking-widest font-black border-b border-slate-100 dark:border-slate-800 bg-slate-50/50 dark:bg-slate-950/30">
              <th class="px-8 py-5 text-center w-16">No</th>
              <th @click="handleSort('date')" class="px-8 py-5 cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors group/th">
                <div class="flex items-center gap-1">
                  Date
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-opacity" :class="filters.sort_by === 'date' ? 'opacity-100' : 'opacity-0 group-hover/th:opacity-50'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="filters.sort_order === 'asc' && filters.sort_by === 'date'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-8 py-5">Employee</th>
              <th class="px-8 py-5">Company</th>
              <th class="px-8 py-5">Project</th>
              <th class="px-8 py-5">Task</th>
              <th @click="handleSort('hours')" class="px-8 py-5 text-right cursor-pointer hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors group/th">
                <div class="flex items-center justify-end gap-1">
                  Hours
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 transition-opacity" :class="filters.sort_by === 'hours' ? 'opacity-100' : 'opacity-0 group-hover/th:opacity-50'" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path v-if="filters.sort_order === 'asc' && filters.sort_by === 'hours'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                    <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                  </svg>
                </div>
              </th>
              <th class="px-8 py-5 text-center w-20">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50 dark:divide-slate-800">
            <tr v-for="(entry, index) in history" :key="entry.id" class="hover:bg-indigo-50/30 dark:hover:bg-indigo-950/20 transition-colors group">
              <td class="px-8 py-5 text-center text-xs font-black text-slate-400">{{ ((pagination.current_page - 1) * pagination.per_page) + index + 1 }}</td>
              <td class="px-8 py-5 text-sm text-slate-500 dark:text-slate-400 font-medium">{{ entry.date }}</td>
              <td class="px-8 py-5">
                <div class="text-sm font-black text-slate-900 dark:text-slate-200">{{ entry.employee?.name }}</div>
              </td>
              <td class="px-8 py-5 text-sm text-slate-500 dark:text-slate-400">{{ entry.company?.name }}</td>
              <td class="px-8 py-5 text-sm text-slate-500 dark:text-slate-400">{{ entry.project?.name }}</td>
              <td class="px-8 py-5">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-[10px] font-black bg-indigo-100 dark:bg-indigo-900/40 text-indigo-700 dark:text-indigo-300 border border-indigo-200 dark:border-indigo-800 uppercase tracking-tighter">
                  {{ entry.task?.name }}
                </span>
              </td>
              <td class="px-8 py-5 text-right">
                <span class="text-sm font-black text-indigo-600 dark:text-indigo-400">{{ Number(entry.hours || 0).toFixed(2) }}</span>
              </td>
              <td class="px-8 py-5 text-center">
                <button 
                  @click="emit('edit', entry)"
                  class="p-2 text-slate-400 hover:text-indigo-600 hover:bg-indigo-50 dark:hover:bg-indigo-900/30 rounded-lg transition-all"
                  title="Edit Entry"
                >
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                  </svg>
                </button>
              </td>
            </tr>
            <tr v-if="history.length === 0">
              <td colspan="8" class="px-8 py-20 text-center">
                <div class="flex flex-col items-center gap-3 text-slate-300">
                  <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                  </svg>
                  <span class="font-bold uppercase tracking-widest text-xs">No Records Found</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination Controls -->
      <div v-if="history.length > 0" class="px-8 py-6 border-t border-slate-100 dark:border-slate-800 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/30 dark:bg-slate-950/10">
        <div class="text-xs font-bold text-slate-400 uppercase tracking-widest">
          Showing <span class="text-slate-600 dark:text-slate-300">{{ history.length }}</span> of <span class="text-slate-600 dark:text-slate-300">{{ pagination.total }}</span> entries
        </div>
        
        <div class="flex items-center gap-2">
          <button 
            @click="fetchHistory(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
            </svg>
          </button>
          
          <div class="flex items-center gap-1">
            <button 
              v-for="page in pagination.last_page" 
              :key="page"
              @click="fetchHistory(page)"
              :class="[
                'w-9 h-9 rounded-lg text-sm font-bold transition-all',
                pagination.current_page === page 
                  ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-200 dark:shadow-none' 
                  : 'text-slate-500 hover:bg-white dark:hover:bg-slate-800'
              ]"
            >
              {{ page }}
            </button>
          </div>

          <button 
            @click="fetchHistory(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="p-2 rounded-lg border border-slate-200 dark:border-slate-700 hover:bg-white dark:hover:bg-slate-800 disabled:opacity-30 disabled:cursor-not-allowed transition-all"
          >
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-slate-600 dark:text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </button>
        </div>
      </div>
    </div>
  </section>
</template>
