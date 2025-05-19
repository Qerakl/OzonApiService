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

// Create form state
const createForm = useForm({
    name: '',
    client_id: '',
    api_key: '',
});
const showCreateModal = ref(false);
const submitting = ref(false);

function submitCreate() {
    submitting.value = true;
    createForm.post(route('marketplace.api-keys.store'), {
        preserveScroll: true,
        onSuccess: () => {
            createForm.reset();
            showCreateModal.value = false;
        },
        onFinish: () => (submitting.value = false),
    });
}

// Edit form state
const editingService = ref(null as null | typeof props.services[0]);
const showEditModal = ref(false);
const editForm = useForm({
    name: '',
    client_id: '',
    api_key: '',
});

function startEdit(service: typeof props.services[0]) {
    editingService.value = service;
    editForm.name = service.name;
    editForm.client_id = service.client_id ?? '';
    editForm.api_key = service.api_key;
    showEditModal.value = true;
}

function cancelEdit() {
    editingService.value = null;
    editForm.reset();
    showEditModal.value = false;
}

function submitEdit() {
    if (!editingService.value) return;
    editForm.put(route('marketplace.api-keys.update', editingService.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingService.value = null;
            editForm.reset();
            showEditModal.value = false;
        },
    });
}

function deleteService(serviceId: number) {
    if (confirm('Удалить этот сервис?')) {
        useForm({}).delete(route('marketplace.api-keys.destroy', serviceId), {
            preserveScroll: true,
        });
    }
}
</script>

<template>
    <Head title="API Ключи" />
    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-4 space-y-6">
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold">Подключенные сервисы</h1>
                <button @click="showCreateModal = true" class="bg-primary text-white px-4 py-2 rounded text-sm hover:bg-primary/90">
                    + Добавить сервис
                </button>
            </div>

            <!-- Таблица -->
            <div class="overflow-auto rounded-xl border border-sidebar-border/70 dark:border-sidebar-border">
                <table class="min-w-full divide-y divide-muted">
                    <thead class="bg-muted/40">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-medium">Площадка</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Client ID</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">API Key</th>
                        <th class="px-4 py-2 text-left text-sm font-medium">Добавлено</th>
                        <th class="px-4 py-2 text-sm">Действия</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y divide-muted">
                    <tr v-for="service in props.services" :key="service.id" class="hover:bg-muted/20">
                        <td class="px-4 py-2 text-sm">{{ service.name }}</td>
                        <td class="px-4 py-2 text-sm">{{ service.client_id || '—' }}</td>
                        <td class="px-4 py-2 text-sm font-mono text-xs truncate max-w-[150px]">{{ service.api_key }}</td>
                        <td class="px-4 py-2 text-sm">{{ format(new Date(service.created_at), 'dd.MM.yyyy HH:mm') }}</td>
                        <td class="px-4 py-2 text-sm space-x-2">
                            <button @click="startEdit(service)" class="text-blue-600 hover:underline text-sm">Изменить</button>
                            <button @click="deleteService(service.id)" class="text-red-600 hover:underline text-sm">Удалить</button>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Модальное окно: Добавление -->
        <div v-if="showCreateModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Добавить новый сервис</h2>
                <form @submit.prevent="submitCreate" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Название *</label>
                        <input v-model="createForm.name" type="text" class="w-full rounded border px-3 py-2 text-sm"
                               :class="{ 'border-red-500': createForm.errors.name }" />
                        <p v-if="createForm.errors.name" class="text-sm text-red-500">{{ createForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Client ID</label>
                        <input v-model="createForm.client_id" type="text" class="w-full rounded border px-3 py-2 text-sm"
                               :class="{ 'border-red-500': createForm.errors.client_id }" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">API Key *</label>
                        <input v-model="createForm.api_key" type="text"
                               class="w-full rounded border px-3 py-2 text-sm font-mono"
                               :class="{ 'border-red-500': createForm.errors.api_key }" />
                        <p v-if="createForm.errors.api_key" class="text-sm text-red-500">{{ createForm.errors.api_key }}</p>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="showCreateModal = false" class="text-sm text-gray-600 hover:underline">
                            Отмена
                        </button>
                        <button type="submit" class="bg-primary text-white px-4 py-2 rounded text-sm hover:bg-primary/90" :disabled="submitting">
                            {{ submitting ? 'Добавление...' : 'Добавить' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Модальное окно: Редактирование -->
        <div v-if="showEditModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
            <div class="bg-white dark:bg-gray-900 p-6 rounded-lg shadow-lg w-full max-w-md">
                <h2 class="text-lg font-semibold mb-4">Редактировать сервис</h2>
                <form @submit.prevent="submitEdit" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium mb-1">Название *</label>
                        <input v-model="editForm.name" type="text" class="w-full rounded border px-3 py-2 text-sm"
                               :class="{ 'border-red-500': editForm.errors.name }" />
                        <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">Client ID</label>
                        <input v-model="editForm.client_id" type="text" class="w-full rounded border px-3 py-2 text-sm"
                               :class="{ 'border-red-500': editForm.errors.client_id }" />
                    </div>

                    <div>
                        <label class="block text-sm font-medium mb-1">API Key *</label>
                        <input v-model="editForm.api_key" type="text"
                               class="w-full rounded border px-3 py-2 text-sm font-mono"
                               :class="{ 'border-red-500': editForm.errors.api_key }" />
                        <p v-if="editForm.errors.api_key" class="text-sm text-red-500">{{ editForm.errors.api_key }}</p>
                    </div>

                    <div class="flex justify-end space-x-2">
                        <button type="button" @click="cancelEdit" class="text-sm text-gray-600 hover:underline">
                            Отмена
                        </button>
                        <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700 text-sm">
                            Сохранить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
