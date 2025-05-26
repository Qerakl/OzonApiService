<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import { Line, Bar } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
} from 'chart.js';

ChartJS.register(
    Title,
    Tooltip,
    Legend,
    LineElement,
    BarElement,
    CategoryScale,
    LinearScale,
    PointElement,
);

const page = usePage();
const forecastsRaw = computed(() => page.props.forecasts);
const hasMore = computed(() => page.props.hasMore);
const limit = ref(page.props.limit);

// 🔍 Фильтрация и сокращение
const search = ref('');
const visibleCount = ref(20); // по умолчанию 20 строк

const filteredForecasts = computed(() => {
    const q = search.value.toLowerCase();
    return forecastsRaw.value
        .filter(f => f.name.toLowerCase().includes(q) || f.article.toLowerCase().includes(q))
        .slice(0, visibleCount.value);
});

const loadMore = () => {
    visibleCount.value += 20;
    if (visibleCount.value > forecastsRaw.value.length && hasMore.value) {
        router.get('/dashboard', { limit: visibleCount.value }, { preserveState: true });
    }
};

const labels = computed(() =>
    filteredForecasts.value.map(f => f.name.length > 25 ? f.name.slice(0, 25) + '…' : f.name),
);

const chartOptions = (title: string) => ({
    responsive: true,
    plugins: {
        legend: { position: 'top' },
        title: { display: true, text: title },
    },
    maintainAspectRatio: false,
});

const stockChart = computed(() => ({
    labels: labels.value,
    datasets: [{
        label: 'Остаток',
        data: filteredForecasts.value.map(f => f.current_stock),
        backgroundColor: '#3b82f6',
    }],
}));

const forecastChart = computed(() => ({
    labels: labels.value,
    datasets: [{
        label: 'Прогноз',
        data: filteredForecasts.value.map(f => f.forecast),
        backgroundColor: '#10b981',
    }],
}));

const compareChart = computed(() => ({
    labels: labels.value,
    datasets: [
        {
            label: 'Остаток',
            data: filteredForecasts.value.map(f => f.current_stock),
            borderColor: '#3b82f6',
            fill: false,
            tension: 0.4,
        },
        {
            label: 'Прогноз',
            data: filteredForecasts.value.map(f => f.forecast),
            borderColor: '#10b981',
            fill: false,
            tension: 0.4,
        },
    ],
}));

const recommendedChart = computed(() => ({
    labels: labels.value,
    datasets: [{
        label: 'Рекомендуемое пополнение',
        data: filteredForecasts.value.map(f => f.forecast - f.current_stock),
        backgroundColor: '#f59e0b',
    }],
}));

const lowStockChart = computed(() => {
    const sorted = [...filteredForecasts.value].sort((a, b) => a.current_stock - b.current_stock).slice(0, 10);
    return {
        labels: sorted.map(f => f.name.length > 25 ? f.name.slice(0, 25) + '…' : f.name),
        datasets: [{
            label: 'Топ 10 наименьших остатков',
            data: sorted.map(f => f.current_stock),
            backgroundColor: '#ef4444',
        }],
    };
});
</script>
<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="[{ title: 'Dashboard', href: '/dashboard' }]">
        <div class="p-6 space-y-6">
            <!-- 🔍 Фильтр и лимит -->
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Поиск по названию или артикулу"
                    class="w-full md:w-1/2 rounded-md border px-3 py-2"
                />
                <select v-model="visibleCount" class="rounded-md border px-3 py-2">
                    <option :value="10">Показать 10</option>
                    <option :value="20">Показать 20</option>
                    <option :value="50">Показать 50</option>
                    <option :value="100">Показать 100</option>
                    <option :value="9999">Показать всё</option>
                </select>
            </div>

            <!-- Графики -->
            <div class="grid md:grid-cols-2 gap-6">
                <div class="h-[400px] rounded-xl border p-4 shadow-sm">
                    <Bar :data="stockChart" :options="chartOptions('Остатки по товарам')" />
                </div>
                <div class="h-[400px] rounded-xl border p-4 shadow-sm">
                    <Bar :data="forecastChart" :options="chartOptions('Прогноз по товарам')" />
                </div>
                <div class="md:col-span-2 h-[400px] rounded-xl border p-4 shadow-sm">
                    <Line :data="compareChart" :options="chartOptions('Остаток против прогноза')" />
                </div>
                <div class="h-[400px] rounded-xl border p-4 shadow-sm">
                    <Bar :data="recommendedChart" :options="chartOptions('Рекомендуемое пополнение')" />
                </div>
                <div class="h-[400px] rounded-xl border p-4 shadow-sm">
                    <Bar :data="lowStockChart" :options="chartOptions('Топ 10 наименьших остатков')" />
                </div>
            </div>

            <!-- Кнопка "Загрузить больше" -->
            <div class="text-center">
                <button
                    v-if="hasMore"
                    @click="loadMore"
                    class="mt-6 rounded-lg bg-blue-600 px-4 py-2 text-white hover:bg-blue-700 transition"
                >
                    Загрузить больше данных
                </button>
            </div>
        </div>
    </AppLayout>
</template>
