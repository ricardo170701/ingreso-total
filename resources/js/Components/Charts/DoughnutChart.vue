<script setup>
import { Doughnut } from "vue-chartjs";
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from "chart.js";

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    data: {
        type: Object,
        required: true,
    },
    options: {
        type: Object,
        default: () => ({}),
    },
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            position: "bottom",
            labels: {
                padding: 15,
                usePointStyle: true,
                font: {
                    size: 12,
                },
            },
        },
        tooltip: {
            backgroundColor: "rgba(0, 0, 0, 0.8)",
            padding: 12,
            titleFont: {
                size: 14,
            },
            bodyFont: {
                size: 13,
            },
            callbacks: {
                label: function (context) {
                    const label = context.label || "";
                    const value = context.parsed || 0;
                    const total = context.dataset.data.reduce(
                        (a, b) => a + b,
                        0,
                    );
                    const percentage =
                        total > 0 ? ((value / total) * 100).toFixed(1) : 0;
                    return `${label}: ${value} (${percentage}%)`;
                },
            },
        },
    },
    ...props.options,
};

const chartData = {
    labels: props.data.labels || [],
    datasets: [
        {
            data: props.data.values || [],
            backgroundColor: props.data.colors || [
                "rgba(59, 130, 246, 0.8)",
                "rgba(16, 185, 129, 0.8)",
                "rgba(251, 146, 60, 0.8)",
                "rgba(236, 72, 153, 0.8)",
                "rgba(139, 92, 246, 0.8)",
            ],
            borderColor: props.data.borderColors || [
                "#fff",
                "#fff",
                "#fff",
                "#fff",
                "#fff",
            ],
            borderWidth: 2,
        },
    ],
};
</script>

<template>
    <div style="position: relative; height: 300px">
        <Doughnut :data="chartData" :options="chartOptions" />
    </div>
</template>
