<script setup lang="ts" generic="T">
import type { Row } from '@tanstack/vue-table'
import { router } from '@inertiajs/vue3';

import { Button } from '@/components/ui/button'
import {
  DropdownMenu,
  DropdownMenuContent,
  DropdownMenuItem,
  DropdownMenuSeparator,
  DropdownMenuShortcut,
  DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu'

import { Ellipsis } from 'lucide-vue-next'

interface DataTableRowActionsProps {
  row: Row<T>,
  isApproved: Boolean,
}
const props = defineProps<DataTableRowActionsProps>()

const approveAccount = () => {
  router.visit(route('user.approve',props.row.getValue('id')),{
    method: 'put',
  })
}
const copyId = () => {
  navigator.clipboard.writeText(props.row.getValue('id'))
}
</script>

<template>
  <DropdownMenu>
    <DropdownMenuTrigger as-child>
      <Button
        variant="ghost"
        class="flex h-8 w-8 p-0 data-[state=open]:bg-muted"
      >
        <Ellipsis class="h-4 w-4" />
        <span class="sr-only">Open menu</span>
      </Button>
    </DropdownMenuTrigger>
    <DropdownMenuContent align="end" class="w-[160px]">
      <DropdownMenuItem @click="approveAccount" :data-isApproved="isApproved" class="bg-green-500 data-[isApproved=true]:bg-red-500">{{ isApproved ? 'Set to Pending':'Approve' }}</DropdownMenuItem>
      <DropdownMenuItem @click="copyId">Copy ID</DropdownMenuItem>
      <DropdownMenuSeparator />
      <DropdownMenuSeparator />
      <DropdownMenuItem>
        Delete
        <DropdownMenuShortcut>⌘⌫</DropdownMenuShortcut>
      </DropdownMenuItem>
    </DropdownMenuContent>
  </DropdownMenu>
</template>
