<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { format } from 'date-fns';
import { ref } from 'vue';

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
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'API Ключи', href: '/marketplace/api-keys' },
];

const form = useForm({
    name: '',
    client_id: '',
    api_key: '',
});

const submitting = ref(false);

function submit() {
    submitting.value = true;
    form.post(route('marketplace.api-keys.store'), {
        preserveScroll: true,
        onFinish: () => (submitting.value = false),
    });
}
</script>

<template>
    <Head title="API Ключи" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-6">
            <h1 class="text-2xl font-bold">Подключенные сервисы</h1>

            <!-- ✅ Форма добавления -->
            <form @submit.prevent="submit" class="space-y-4 max-w-xl">
                <div>
                    <label class="block text-sm font-medium mb-1">Название *</label>
                    <input
                        v-model="form.name"
                        type="text"
                        class="w-full rounded border px-3 py-2 text-sm"
                        :class="{ 'border-red-500': form.errors.name }"
                    />
                    <p v-if="form.errors.name" class="text-sm text-red-500 mt-1">{{ form.errors.name }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Client ID</label>
                    <input
                        v-model="form.client_id"
                        type="text"
                        class="w-full rounded border px-3 py-2 text-sm"
                        :class="{ 'border-red-500': form.errors.client_id }"
                    />
                    <p v-if="form.errors.client_id" class="text-sm text-red-500 mt-1">{{ form.errors.client_id }}</p>
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">API Key *</label>
                    <input
                        v-model="form.api_key"
                        type="text"
                        class="w-full rounded border px-3 py-2 text-sm font-mono"
                        :class="{ 'border-red-500': form.errors.api_key }"
                    />
                    <p v-if="form.errors.api_key" class="text-sm text-red-500 mt-1">{{ form.errors.api_key }}</p>
                </div>

                <button
                    type="submit"
                    class="bg-primary text-white px-4 py-2 rounded text-sm hover:bg-primary/90"
                    :disabled="submitting"
                >
                    {{ submitting ? 'Добавление...' : 'Добавить сервис' }}
                </button>
            </form>

            <!-- ✅ Таблица -->
            <div class="overflow-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border mt-6">
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
                    <tr v-for="service in props.services" :key="service.id" class="hover:bg-muted/20">
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
