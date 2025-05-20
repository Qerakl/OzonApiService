<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import axios from 'axios';

// Пропсы
const props = defineProps<{
    marketplaces: { id: number; name: string; client_id: string | null }[],
    forecasts: {
        id: number;
        article: string;
        name: string;
        current_stock: number;
        forecast: number;
        recommendations: string;
    }[]
}>();

// Хлебные крошки
const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Прогнозы', href: '/forecasts' }
];

// Переменные
const marketplaces = ref(props.marketplaces);
const forecasts = ref(props.forecasts);

const deliveryDate = ref('');
const stockDays = ref(1);
const dataSource = ref<'csv' | 'marketplace' | null>(null);
const csvFile = ref<File | null>(null);
const selectedMarketplaceId = ref<number | null>(null);
const loading = ref(false);

// Обработчик прогноза
async function calculateForecast() {
    if (!dataSource.value) {
        alert('Выберите источник данных');
        return;
    }

    const formData = new FormData();

    formData.append('data_source', dataSource.value);
    if (deliveryDate.value) formData.append('delivery_date', deliveryDate.value);
    if (stockDays.value) formData.append('stock_days', stockDays.value.toString());

    if (dataSource.value === 'csv') {
        if (!csvFile.value) {
            alert('Загрузите CSV-файл');
            return;
        }

        formData.append('csv_file', csvFile.value);
    }

    if (dataSource.value === 'marketplace') {
        if (!selectedMarketplaceId.value) {
            alert('Выберите площадку');
            return;
        }

        if (!deliveryDate.value) {
            alert('Введите дату поставки');
            return;
        }

        formData.append('marketplace_id', selectedMarketplaceId.value.toString());
    }

    loading.value = true;
    try {
        const response = await axios.post('/marketplace/forecasts/calculate', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });

        // Вариант обновления страницы или возврата новых данных
        window.location.reload();
    } catch (error) {
        console.error('Ошибка при расчёте прогноза:', error);
        alert('Произошла ошибка при отправке формы');
    } finally {
        loading.value = false;
    }
}
</script>


<template>
    <Head title="Прогнозы по площадкам" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="p-6 max-w-4xl mx-auto bg-white rounded-lg shadow-md space-y-8">

            <!-- Выбор источника данных -->
            <section class="space-y-3">
                <h2 class="text-xl font-semibold text-gray-800">1. Выберите источник данных</h2>
                <div class="flex gap-6">
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" value="csv" v-model="dataSource" class="accent-indigo-600" />
                        <span>Загрузить CSV-файл</span>
                    </label>
                    <label class="cursor-pointer flex items-center gap-2">
                        <input type="radio" value="marketplace" v-model="dataSource" class="accent-indigo-600" />
                        <span>Выбрать площадку из маркетплейсов</span>
                    </label>
                </div>
            </section>

            <!-- Если выбран CSV -->
            <section v-if="dataSource === 'csv'" class="space-y-3">
                <h2 class="text-xl font-semibold text-gray-800">2. Загрузите CSV-файл</h2>
                <input type="file" accept=".csv,text/csv" @change="e => csvFile = e.target.files ? e.target.files[0] : null"
                       class="block w-full text-sm text-gray-700
            file:mr-4 file:py-2 file:px-4
            file:rounded file:border-0
            file:text-sm file:font-semibold
            file:bg-indigo-50 file:text-indigo-700
            hover:file:bg-indigo-100
          " />
                <p v-if="csvFile" class="text-sm text-gray-600 mt-1">Выбран файл: <strong>{{ csvFile.name }}</strong></p>
            </section>

            <!-- Если выбран маркетплейс -->
            <section v-if="dataSource === 'marketplace'" class="space-y-3">
                <h2 class="text-xl font-semibold text-gray-800">2. Выберите площадку</h2>
                <select v-model="selectedMarketplaceId"
                        class="w-full border border-gray-300 rounded px-3 py-2 text-gray-700
            focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                    <option value="" disabled>Выберите площадку</option>
                    <option v-for="mp in marketplaces" :key="mp.id" :value="mp.id">
                        {{ mp.name }} ({{ mp.client_id || '—' }})
                    </option>
                </select>
            </section>

            <!-- Дата поставки и дней загрузки (только если НЕ выбран CSV) -->
            <section v-if="dataSource === 'marketplace'" class="grid grid-cols-1 md:grid-cols-2 gap-6 max-w-md">
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Дата поставки</label>
                    <input type="date" v-model="deliveryDate"
                           class="w-full border border-gray-300 rounded px-3 py-2
              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
                <div>
                    <label class="block mb-1 font-medium text-gray-700">Дней загрузки склада</label>
                    <input type="number" v-model.number="stockDays" min="1"
                           class="w-full border border-gray-300 rounded px-3 py-2
              focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500" />
                </div>
            </section>

            <div>
                <button @click="calculateForecast" :disabled="loading"
                        class="inline-flex items-center justify-center bg-indigo-600 text-white px-6 py-3 rounded-md
            font-semibold hover:bg-indigo-700 disabled:bg-indigo-400 disabled:cursor-not-allowed
            transition-colors duration-200 ease-in-out">
                    <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none"
                         viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path>
                    </svg>
                    {{ loading ? 'Рассчитываем...' : 'Рассчитать' }}
                </button>
            </div>

            <!-- Таблица прогнозов -->
            <table class="min-w-full border-collapse border border-gray-300 mt-8">
                <thead>
                <tr class="bg-gray-50">
                    <th class="border border-gray-300 px-4 py-2 text-left text-gray-700">Артикул</th>
                    <th class="border border-gray-300 px-4 py-2 text-left text-gray-700">Название</th>
                    <th class="border border-gray-300 px-4 py-2 text-right text-gray-700">Текущий остаток</th>
                    <th class="border border-gray-300 px-4 py-2 text-right text-gray-700">Прогноз</th>
                    <th class="border border-gray-300 px-4 py-2 text-left text-gray-700">Рекомендации</th>
                </tr>
                </thead>
                <tbody>
                <tr v-if="forecasts.length === 0">
                    <td colspan="5" class="text-center py-6 text-gray-500">Нет данных для отображения</td>
                </tr>
                <tr v-for="item in forecasts" :key="item.id" class="hover:bg-gray-100 transition-colors">
                    <td class="border border-gray-300 px-4 py-2">{{ item.article }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ item.name }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-right">{{ item.current_stock }}</td>
                    <td class="border border-gray-300 px-4 py-2 text-right">{{ item.forecast }}</td>
                    <td class="border border-gray-300 px-4 py-2">{{ item.recommendations }}</td>
                </tr>
                </tbody>
            </table>

        </div>
    </AppLayout>
</template>
