<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { FormControl, FormField, FormItem, FormLabel, FormMessage } from '@/components/ui/form'
import { Button } from '@/components/ui/button'
import { Input } from '@/components/ui/input'
import {
    Select,
    SelectContent,
    SelectGroup,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select'
import { Separator } from '@/components/ui/separator'

import { toast } from '@/components/ui/toast'
import { toTypedSchema } from '@vee-validate/zod'
import { useForm } from 'vee-validate'
import { h, ref } from 'vue'
import * as z from 'zod'
import { Head, router } from '@inertiajs/vue3'
import { ChevronLeft } from 'lucide-vue-next';

const props = defineProps(['user']);
const roles = ref(['Administrator', 'User'])

const formSchema = toTypedSchema(z.object({
    name: z.string(),
    username: z.string()
        .min(3, { message: 'Username must be at least 3 characters.' })
        .max(16, { message: 'Username must not be longer than 16 characters.' }),
    role: z.string({ required_error: 'Please select a role.' }),

    // Password is optional, but if present, must follow rules
    password: z.string().optional().refine((value) => {
        if (value) {
            return value.length >= 6 && value.length <= 16;
        }
        return true; // allow empty
    }, {
        message: 'Password must be between 6 to 16 characters.',
    }),

    password_confirmation: z.string().optional(),
}).superRefine(({ password, password_confirmation }, ctx) => {
    // Only validate confirmation if password is being set
    if (password && password_confirmation !== password) {
        ctx.addIssue({
            code: 'custom',
            message: 'The passwords did not match',
            path: ['password_confirmation'],
        });
    }
}))

const { handleSubmit, resetForm } = useForm({
    validationSchema: formSchema,
    initialValues: {
        name: props.user.name,
        username: props.user.username,
        role: props.user.role,
        password: '',
        password_confirmation: '',
    },
})


const onSubmit = handleSubmit((values) => {
    router.put(route('user.update', props.user.id), values, {
        onSuccess: () => {
            toast({
                title: 'User created',
                description: h('div', { class: 'mt-2 w-[340px] rounded-md bg-green-600 p-4' }, 'The user has been updated successfully.'),
            })
        },
    })
})

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Users',
        href: '/users',
    },
    {
        title: 'New User',
        href: '/users/new',
    },
];
</script>

<template>

    <Head title="Users" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-10">
            <div class="flex space-x-4 items-center">
                <ChevronLeft @click="router.visit(route('user.index'))"
                    class="w-6 h-6 pr-0.5 inline-block hover:bg-slate-900 hover:text-muted rounded-md cursor-pointer" />
                <h3 class="text-lg font-medium">
                    Update user
                </h3>
            </div>
            <Separator />
            <form class="space-y-4" @submit="onSubmit">
                <FormField v-slot="{ componentField }" name="name">
                    <FormItem>
                        <FormLabel>Name</FormLabel>
                        <FormControl>
                            <Input type="text" placeholder="Name" v-bind="componentField" autofocus />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
                <FormField v-slot="{ componentField }" name="username">
                    <FormItem>
                        <FormLabel>Username</FormLabel>
                        <FormControl>
                            <Input type="text" placeholder="Username" v-bind="componentField" autofocus />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="role">
                    <FormItem>
                        <FormLabel>Role</FormLabel>

                        <Select v-bind="componentField">
                            <FormControl>
                                <SelectTrigger>
                                    <SelectValue placeholder="Select role" />
                                </SelectTrigger>
                            </FormControl>
                            <SelectContent>
                                <SelectGroup>
                                    <SelectItem v-for="role in roles" :key="role" :value="role">
                                        {{ role }}
                                    </SelectItem>
                                </SelectGroup>
                            </SelectContent>
                        </Select>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <FormField v-slot="{ componentField }" name="password">
                    <FormItem>
                        <FormLabel>Password <span class="text-red-400">(Leave blank if you do not want to change the
                                password)</span></FormLabel>
                        <FormControl>
                            <Input type="password" v-bind="componentField" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>
                <FormField v-slot="{ componentField }" name="password_confirmation">
                    <FormItem>
                        <FormLabel>Confirm password</FormLabel>
                        <FormControl>
                            <Input type="password" v-bind="componentField" />
                        </FormControl>
                        <FormMessage />
                    </FormItem>
                </FormField>

                <div class="flex gap-2 justify-start pt-6">
                    <Button type="submit">
                        Update user
                    </Button>

                    <Button type="button" variant="outline" @click="resetForm">
                        Reset form
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
