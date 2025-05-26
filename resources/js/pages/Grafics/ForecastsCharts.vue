<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
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

ChartJS.register(Title, Tooltip, Legend, LineElement, CategoryScale, LinearScale, PointElement, Filler);

const page = usePage();
const forecasts = computed(() => page.props.forecasts);

const chartData = computed(() => {
    return {
        labels: forecasts.value.map(f => f.name), // заменили article на name
        datasets: [
            {
                label: 'Остаток',
                data: forecasts.value.map(f => f.current_stock),
                borderColor: 'rgba(59, 130, 246, 1)',
                backgroundColor: 'rgba(59, 130, 246, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                pointRadius: 5,
            },
            {
                label: 'Прогноз',
                data: forecasts.value.map(f => f.forecast),
                borderColor: 'rgba(34, 197, 94, 1)',
                backgroundColor: 'rgba(34, 197, 94, 0.2)',
                fill: true,
                tension: 0.4,
                pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                pointRadius: 5,
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
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
    {
        title: 'Графики',
        href: '/marketplace/forecasts/charts',
    },
];
</script>

<template>
    <Head title="Графики прогнозов" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="rounded-xl border border-sidebar-border/70 bg-white dark:bg-background dark:border-sidebar-border shadow-sm">
                <div class="p-4">
                    <h1 class="text-2xl font-bold text-gray-800 dark:text-white mb-4">График прогнозов и остатков</h1>
                    <div class="relative h-[400px] w-full">
                        <Line :data="chartData" :options="chartOptions" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
