<script setup>
import { Line } from 'vue-chartjs'
import { Chart as ChartJS, CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend } from 'chart.js'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend)

const props = defineProps({
    data: {
        type: Object,
        required: true
    },
    options: {
        type: Object,
        default: () => ({})
    }
})

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: {
            display: false
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
    datasets: [
        {
            label: props.data.label || 'Datos',
            data: props.data.values || [],
            borderColor: 'rgb(59, 130, 246)',
            backgroundColor: 'rgba(59, 130, 246, 0.1)',
            tension: 0.4,
            fill: true,
            pointBackgroundColor: 'rgb(59, 130, 246)',
            pointBorderColor: '#fff',
            pointBorderWidth: 2,
            pointRadius: 4,
            pointHoverRadius: 6
        }
    ]
}
</script>

<template>
    <div style="position: relative; height: 300px;">
        <Line :data="chartData" :options="chartOptions" />
    </div>
</template>

