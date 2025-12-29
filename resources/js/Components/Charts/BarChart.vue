<script setup>
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    },
    horizontal: {
        type: Boolean,
        default: false
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    indexAxis: props.horizontal ? 'y' : 'x',
    plugins: {
        legend: {
            display: props.data.datasets && props.data.datasets.length > 1,
            position: 'top'
        },
        tooltip: {
            backgroundColor: 'rgba(0, 0, 0, 0.8)',
            padding: 12,
            titleFont: {
                size: 14
            },
            bodyFont: {
                size: 13
            }
        }
    },
    scales: {
        y: {
            beginAtZero: true,
            ticks: {
                stepSize: 1
            },
            grid: {
                color: 'rgba(0, 0, 0, 0.05)'
            }
        },
        x: {
            grid: {
                display: false
            }
        }
    },
    ...props.options
}

const chartData = {
    labels: props.data.labels || [],
    datasets: props.data.datasets || [{
        label: props.data.label || 'Datos',
        data: props.data.values || [],
        backgroundColor: props.data.colors || [
            'rgba(59, 130, 246, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(251, 146, 60, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(139, 92, 246, 0.8)'
        ],
        borderColor: props.data.borderColors || [
            'rgb(59, 130, 246)',
            'rgb(16, 185, 129)',
            'rgb(251, 146, 60)',
            'rgb(236, 72, 153)',
            'rgb(139, 92, 246)'
        ],
        borderWidth: 1,
        borderRadius: 4
    }]
}
</script>

<template>
    <div style="position: relative; height: 300px;">
        <Bar :data="chartData" :options="chartOptions" />
    </div>
</template>

