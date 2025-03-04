<script setup lang="ts" generic="T">
import type { Table } from '@tanstack/vue-table'
import { Button } from '@/components/ui/button'

import { Input } from '@/components/ui/input'
import { computed } from 'vue'

import DataTableViewOptions from '@/components/datatable/DataTableViewOptions.vue'
import DataTableFacetedFilter from '@/components/datatable/DataTableFacetedFilter.vue'
import { X } from 'lucide-vue-next'

interface DataTableToolbarProps {
  table: Table<T>
  filters: {}[]
}

const props = defineProps<DataTableToolbarProps>()

const isFiltered = computed(() => props.table.getState().columnFilters.length > 0)
console.log(props.filters);

</script>

<template>
  <div class="flex items-center justify-between">
    <div class="flex flex-1 items-center space-x-2">
      <DataTableViewOptions :table="table" />
      <Input
        placeholder="Search..."
        :model-value="(table.getColumn('name')?.getFilterValue() as string) ?? ''"
        class="h-8 w-[150px] lg:w-[250px]"
        @input="table.getColumn('name')?.setFilterValue($event.target.value)"
      />

      <DataTableFacetedFilter
        v-for="filter in props.filters"
        :column="table.getColumn(filter.column)"
        :title="filter.title"
        :options="filter.options"
      />
      <Button
        v-if="isFiltered"
        variant="ghost"
        class="h-8 px-2 lg:px-3"
        @click="table.resetColumnFilters()"
      >
        Reset
        <X class="ml-2 h-4 w-4" />
      </Button>
    </div>
  </div>
</template>
