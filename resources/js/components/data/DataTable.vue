<template>
  <div>
    <div v-if="searchable" class="flex justify-end mb-4">
      <input
        v-model="globalFilter"
        type="text"
        placeholder="Search..."
        class="block w-full max-w-xs rounded-lg border-gray-300 shadow-sm sm:text-sm focus:ring-run-blue focus:border-run-blue"
      />
    </div>

    <div class="overflow-x-auto rounded-lg border border-gray-200">
      <table class="w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th
              v-for="header in table.getHeaderGroups()[0]?.headers"
              :key="header.id"
              class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider cursor-pointer select-none"
              @click="header.column.getToggleSortingHandler()?.($event)"
            >
              <div class="flex items-center gap-1">
                <FlexRender :render="header.column.columnDef.header" :props="header.getContext()" />
                <span v-if="header.column.getIsSorted() === 'asc'" class="text-run-blue">&#9650;</span>
                <span v-else-if="header.column.getIsSorted() === 'desc'" class="text-run-blue">&#9660;</span>
              </div>
            </th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <template v-if="loading">
            <tr v-for="i in pageSize" :key="'skeleton-' + i">
              <td v-for="col in columns.length" :key="'skeleton-col-' + col" class="px-6 py-4">
                <div class="h-4 bg-gray-200 rounded animate-pulse" />
              </td>
            </tr>
          </template>
          <template v-else-if="table.getRowModel().rows.length === 0">
            <tr>
              <td :colspan="columns.length" class="px-6 py-12 text-center text-sm text-gray-500">
                No data found
              </td>
            </tr>
          </template>
          <template v-else>
            <tr
              v-for="row in table.getRowModel().rows"
              :key="row.id"
              class="hover:bg-gray-50 transition-colors"
            >
              <td v-for="cell in row.getVisibleCells()" :key="cell.id" class="px-6 py-4 text-sm text-gray-900">
                <FlexRender :render="cell.column.columnDef.cell" :props="cell.getContext()" />
              </td>
            </tr>
          </template>
        </tbody>
      </table>
    </div>

    <AppPagination
      v-if="paginated && table.getPageCount() > 1"
      :current-page="table.getState().pagination.pageIndex + 1"
      :total-pages="table.getPageCount()"
      :total-items="data.length"
      class="mt-4"
      @page-change="(page) => table.setPageIndex(page - 1)"
    />
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import {
  useVueTable,
  getCoreRowModel,
  getFilteredRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  FlexRender,
} from '@tanstack/vue-table';
import AppPagination from '@/components/common/AppPagination.vue';

const props = defineProps({
  columns: {
    type: Array,
    required: true,
  },
  data: {
    type: Array,
    required: true,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  searchable: {
    type: Boolean,
    default: true,
  },
  paginated: {
    type: Boolean,
    default: true,
  },
  pageSize: {
    type: Number,
    default: 10,
  },
});

const globalFilter = ref('');
const sorting = ref([]);

const table = useVueTable({
  get data() {
    return props.data;
  },
  get columns() {
    return props.columns;
  },
  state: {
    get globalFilter() {
      return globalFilter.value;
    },
    get sorting() {
      return sorting.value;
    },
  },
  onGlobalFilterChange: (updater) => {
    globalFilter.value = typeof updater === 'function' ? updater(globalFilter.value) : updater;
  },
  onSortingChange: (updater) => {
    sorting.value = typeof updater === 'function' ? updater(sorting.value) : updater;
  },
  getCoreRowModel: getCoreRowModel(),
  getFilteredRowModel: getFilteredRowModel(),
  getPaginationRowModel: props.paginated ? getPaginationRowModel() : undefined,
  getSortedRowModel: getSortedRowModel(),
  initialState: {
    pagination: {
      pageSize: props.pageSize,
    },
  },
});
</script>
