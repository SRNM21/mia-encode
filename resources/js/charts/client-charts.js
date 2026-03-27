import { getChartTheme, destroyChart } from "../utils/charts.js"

export function renderClientTodayChart(canvas, newClient, oldClient) {
    if (!canvas || canvas.length === 0) return

    const { axisText, gridColor } = getChartTheme()

    const ctx = canvas[0].getContext('2d')

    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['New Client', 'Old Client'],
            datasets: [{
                data: [newClient || 0, oldClient || 0],
                backgroundColor: [
                    'rgba(75,192,192,0.2)',
                    'rgba(255,99,132,0.2)'
                ],
                borderColor: [
                    'rgb(75,192,192)',
                    'rgb(255,99,132)'
                ],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
            },
            scales: {
                y: { ticks: { color: axisText }, grid: { color: gridColor } },
                x: { ticks: { color: axisText }, grid: { color: gridColor } }
            }
        }
    })
}

let clientSeriesChart

export function renderClientSeries(canvas, labels, datasets) {

    const { axisText, gridColor } = getChartTheme()

    destroyChart(clientSeriesChart)

    clientSeriesChart = new Chart(
        canvas[0].getContext('2d'),
        {
            type: 'line',
            data: { labels, datasets },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                elements: { line: { fill: true } },
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: axisText }
                    },
                },
                scales: {
                    y: { ticks: { color: axisText }, grid: { color: gridColor } },
                    x: { ticks: { color: axisText }, grid: { color: gridColor } }
                }
            }
        }
    )
}