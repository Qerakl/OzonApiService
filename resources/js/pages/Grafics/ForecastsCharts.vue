<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    LineElement,
    CategoryScale,
    LinearScale,
    PointElement,
    Filler,
} from 'chart.js';
import { ref, computed, watch } from 'vue';

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler);

const page = usePage();
const rawForecasts = ref([...page.props.forecasts]);
const pagination = computed(() => page.props.pagination);

const perPage = ref(20);
watch(perPage, (newVal) => {
    router.get('/marketplace/forecasts/charts', { per_page: newVal }, {
        preserveState: true,
        preserveScroll: true,
        only: ['forecasts', 'pagination'],
        replace: true,
    });
});

watch(() => page.props.forecasts, (newVal) => {
    rawForecasts.value = [...newVal];
});

const loadMore = () => {
    if (!pagination.value?.next_page_url) return;

    router.get(pagination.value.next_page_url, {}, {
        preserveState: true,
        preserveScroll: true,
        only: ['forecasts', 'pagination'],
    });
};

const chartData = computed(() => {
    const forecasts = rawForecasts.value;
    return {
        labels: forecasts.map(f => f.name),
        datasets: [
            {
                label: 'Остаток',
                data: forecasts.map(f => f.current_stock),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointRadius: 3,
            },
            {
                label: 'Прогноз',
                data: forecasts.map(f => f.forecast),
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                pointRadius: 3,
            },
        ],
    };
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'bottom',
            labels: {
                color: '#374151',
                font: {
                    size: 14,
                },
            },
        },
        title: {
            display: true,
            text: 'Прогноз и остатки товаров',
            color: '#1f2937',
            font: {
                size: 20,
            },
        },
        tooltip: {
            mode: 'index',
            intersect: false,
        },
    },
    scales: {
        x: {
            ticks: {
                color: '#4b5563',
                callback: function(value, index, ticks) {
                    const label = this.getLabelForValue(value);
                    return label.length > 20 ? label.substring(0, 20) + '…' : label;
                },
                maxRotation: 45,
                minRotation: 30,
            },
            grid: { display: false },
        },
        y: {
            ticks: { color: '#4b5563' },
            grid: {
                color: 'rgba(203, 213, 225, 0.2)',
            },
        },
    },
};

const breadcrumbs = [
    { title: 'Dashboard', href: '/dashboard' },
    { title: 'Графики', href: '/marketplace/forecasts/charts' },
];
</script>

<template>
    <Head title="Графики прогнозов" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between mb-4">
                <label class="text-sm text-gray-700 dark:text-gray-200">
                    Показывать:
                    <select v-model="perPage" class="ml-2 p-1 border rounded">
                        <option :value="10">10</option>
                        <option :value="20">20</option>
                        <option :value="50">50</option>
                        <option :value="75">75</option>
                        <option :value="100">100</option>
                        <option :value="500">500</option>
                    </select>
                </label>
            </div>

            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-background dark:border-sidebar-border shadow-sm">
                <div class="p-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">График прогнозов и остатков</h1>
                    <div class="relative h-[400px] w-full">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>
            </div>

            <div class="text-center mt-6">
                <button
                    v-if="pagination?.next_page_url"
                    @click="loadMore"
                    class="rounded-md bg-blue-600 text-white px-4 py-2 hover:bg-blue-700 transition"
                >
                    Загрузить ещё данные
                </button>
            </div>
        </div>
    </AppLayout>
</template>
