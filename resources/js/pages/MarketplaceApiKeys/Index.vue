<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { format } from 'date-fns';

const props = defineProps<{
    services: {
        id: number;
        name: string;
        client_id: string | null;
        api_key: string;
        created_at: string;
    }[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'API Ключи',
        href: '/marketplace/api-keys',
    },
];
</script>

<template>
    <Head title="API Ключи" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4">
            <h1 class="text-2xl font-bold mb-4">Подключенные сервисы</h1>

            <div class="overflow-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-muted">
                    <thead class="bg-muted/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Площадка</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Client ID</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">API Key</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Добавлено</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-muted">
                    <tr v-for="service in services" :key="service.id" class="hover:bg-muted/20">
                        <td class="px-4 py-2 text-sm">{{ service.name }}</td>
                        <td class="px-4 py-2 text-sm">{{ service.client_id || '—' }}</td>
                        <td class="px-4 py-2 text-sm font-mono text-xs truncate max-w-[200px]">{{ service.api_key }}</td>
                        <td class="px-4 py-2 text-sm">{{ format(new Date(service.created_at), 'dd.MM.yyyy HH:mm') }}</td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AppLayout>
</template>
