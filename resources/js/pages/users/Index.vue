<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Checkbox } from '@/components/ui/checkbox'
import DataTable from '@/components/datatable/DataTable.vue';
import { onMounted, ref, h } from 'vue';
import DataTableColumnHeader from '@/components/datatable/DataTableColumnHeader.vue';
import DataTableRowActions from '@/components/datatable/users/DataTableRowActions.vue';

const props = defineProps(['users'])
const data = ref(props.users)
const filters = [
    {
        column: 'status',
        title: 'Status',
        options: [
            {
                label: 'Approved',
                value: 'Approved',
            },
            {
                label: 'Pending',
                value: 'Pending',
            },
        ],
    },
    {
        column: 'role',
        title: 'Role',
        options: [
            {
                label: 'Administrator',
                value: 'Administrator',
            },
            {
                label: 'User',
                value: 'User',
            },
        ],
    }
]
// <!-- <DataTableFacetedFilter
//         v-if="table.getColumn('status')"
//         :column="table.getColumn('status')"
//         title="Status"
//         :options="statuses"
//       /> -->
const columns = [
    {
        id: 'select',
        header: ({ table }) => h(Checkbox, {
            'modelValue': table.getIsAllPageRowsSelected(),
            'onUpdate:modelValue': (value: boolean) => table.toggleAllPageRowsSelected(!!value),
            'ariaLabel': 'Select all',
        }),
        cell: ({ row }) => h(Checkbox, {
            'modelValue': row.getIsSelected(),
            'onUpdate:modelValue': (value: boolean) => row.toggleSelected(!!value),
            'ariaLabel': 'Select row',
        }),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'id',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'ID' }),
        cell: ({ row }) => h('div', { class: 'w-20' }, row.getValue('id')),
        enableSorting: false,
        enableHiding: false,
    },
    {
        accessorKey: 'name',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Name' }),

        cell: ({ row }) => {
            return h('div', { class: 'flex space-x-2' }, [
                h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('name')),
            ])
        },
    },
    {
        accessorKey: 'username',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Username' }),

        cell: ({ row }) => {
            return h('div', { class: 'flex space-x-2' }, [
                h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('username')),
            ])
        },
    },
    {
        accessorKey: 'role',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Role' }),

        cell: ({ row }) => {
            return h('div', { class: 'flex space-x-2' }, [
                h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('role')),
            ])
        },
    },
    {
        accessorKey: 'status',
        header: ({ column }) => h(DataTableColumnHeader, { column, title: 'Status' }),

        cell: ({ row }) => {
            return h('div', { class: 'flex space-x-2' }, [
                h('span', { class: 'max-w-[500px] truncate font-medium' }, row.getValue('status')),
            ])
        },
    },
    {
        id: 'actions',
        cell: ({ row }) => h(DataTableRowActions, { row, isApproved: row.getValue('status') === 'Approved' }),
    },
];
async function getData() {
    // Fetch data from your API here.
    return props.users
}

onMounted(async () => {
    data.value = await getData()
})
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
];

</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-10">
            <DataTable :columns="columns" :data="data ?? []" :filters="filters ?? []" />
        </div>
    </AppLayout>
</template>
