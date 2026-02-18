<script setup>
import { Line } from 'vue-chartjs';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js';

ChartJS.register(
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler
);

const props = defineProps({
    records: {
        type: Array,
        default: () => [],
    },
});

// Ordenar por fecha ascendente para visualización temporal
const sortedRecords = () => {
    if (!props.records?.length) return [];
    return [...props.records].sort(
        (a, b) => new Date(a.created_at) - new Date(b.created_at)
    );
};

const formatLabel = (dateStr) => {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: 'short',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: 'top',
            labels: {
                boxWidth: 12,
                font: { size: 11 },
            },
        },
        tooltip: {
            mode: 'index',
            intersect: false,
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 10,
        },
    },
    scales: {
        y: {
            beginAtZero: false,
            grid: { color: 'rgba(0, 0, 0, 0.06)' },
            ticks: { font: { size: 10 } },
        },
        x: {
            grid: { display: false },
            ticks: {
                maxRotation: 45,
                minRotation: 45,
                font: { size: 9 },
            },
        },
    },
};

const batteryData = () => {
    const recs = sortedRecords();
    if (recs.length === 0) return null;

    const values = recs.map((r) =>
        r.battery_percentage != null ? Number(r.battery_percentage) : null
    );
    const hasAny = values.some((v) => v != null);
    if (!hasAny) return null;

    return {
        labels: recs.map((r) => formatLabel(r.created_at)),
        datasets: [
            {
                label: 'Batería (%)',
                data: values,
                borderColor: 'rgb(34, 197, 94)',
                backgroundColor: 'rgba(34, 197, 94, 0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 5,
            },
        ],
    };
};

const voltagesData = () => {
    const recs = sortedRecords();
    if (recs.length === 0) return null;

    const input = recs.map((r) =>
        r.input_voltage != null ? Number(r.input_voltage) : null
    );
    const output = recs.map((r) =>
        r.output_voltage != null ? Number(r.output_voltage) : null
    );
    const battery = recs.map((r) =>
        r.battery_voltage != null ? Number(r.battery_voltage) : null
    );

    const hasAny =
        input.some((v) => v != null) ||
        output.some((v) => v != null) ||
        battery.some((v) => v != null);
    if (!hasAny) return null;

    const datasets = [];

    if (input.some((v) => v != null)) {
        datasets.push({
            label: 'Input (V)',
            data: input,
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
        });
    }
    if (output.some((v) => v != null)) {
        datasets.push({
            label: 'Output (V)',
            data: output,
            borderColor: 'rgb(234, 179, 8)',
            backgroundColor: 'rgba(234, 179, 8, 0.1)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
        });
    }
    if (battery.some((v) => v != null)) {
        datasets.push({
            label: 'Batería (V)',
            data: battery,
            borderColor: 'rgb(168, 85, 247)',
            backgroundColor: 'rgba(168, 85, 247, 0.1)',
            fill: false,
            tension: 0.3,
            pointRadius: 2,
            pointHoverRadius: 4,
        });
    }

    if (datasets.length === 0) return null;

    return {
        labels: recs.map((r) => formatLabel(r.created_at)),
        datasets,
    };
};

const temperatureData = () => {
    const recs = sortedRecords();
    if (recs.length === 0) return null;

    const values = recs.map((r) =>
        r.temperatura != null ? Number(r.temperatura) : null
    );
    const hasAny = values.some((v) => v != null);
    if (!hasAny) return null;

    return {
        labels: recs.map((r) => formatLabel(r.created_at)),
        datasets: [
            {
                label: 'Temperatura (°C)',
                data: values,
                borderColor: 'rgb(239, 68, 68)',
                backgroundColor: 'rgba(239, 68, 68, 0.15)',
                fill: true,
                tension: 0.3,
                pointRadius: 3,
                pointHoverRadius: 5,
            },
        ],
    };
};

const showCharts = () => {
    const recs = props.records;
    if (!recs?.length || recs.length < 2) return false;
    return (
        batteryData() != null ||
        voltagesData() != null ||
        temperatureData() != null
    );
};
</script>

<template>
    <div v-if="showCharts()" class="space-y-4">
        <h2 class="text-lg font-semibold text-slate-900 dark:text-slate-100">
            Resumen visual ({{ records?.length || 0 }} registros)
        </h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-4">
            <!-- Batería % -->
            <div
                v-if="batteryData()"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200"
            >
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    Batería (%)
                </h3>
                <div style="position: relative; height: 200px">
                    <Line :data="batteryData()" :options="chartOptions" />
                </div>
            </div>

            <!-- Voltajes -->
            <div
                v-if="voltagesData()"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200"
            >
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    Voltajes (V)
                </h3>
                <div style="position: relative; height: 200px">
                    <Line :data="voltagesData()" :options="chartOptions" />
                </div>
            </div>

            <!-- Temperatura -->
            <div
                v-if="temperatureData()"
                class="bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl p-4 transition-colors duration-200"
            >
                <h3 class="text-sm font-medium text-slate-700 dark:text-slate-300 mb-3">
                    Temperatura (°C)
                </h3>
                <div style="position: relative; height: 200px">
                    <Line :data="temperatureData()" :options="chartOptions" />
                </div>
            </div>
        </div>
    </div>
</template>
