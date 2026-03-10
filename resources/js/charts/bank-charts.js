import { destroyChart, generateColors, getChartTheme } from "../utils/charts.js"

const bankColorMap = {}
const BANK_COLORS = [
    'rgb(54,162,235)',
    'rgb(255,99,132)',
    'rgb(75,192,192)',
    'rgb(255,206,86)',
    'rgb(153,102,255)',
    'rgb(255,159,64)'
]
const bankColorRegistry = new Map()

export function renderBankTodayChart(canvas, labels, counts) {

    const colors = generateColors(labels.length)

    new Chart(canvas[0].getContext('2d'), {
        type: 'doughnut',
        data: {
            labels,
            datasets: [{
                data: counts,
                backgroundColor: colors.background,
                borderColor: colors.border,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false }
            }
        }
    })
}

let bankSeriesChart

export function renderBankSeries(canvas, labels, datasets) {

    const { axisText, gridColor } = getChartTheme()

    destroyChart(bankSeriesChart)

    bankSeriesChart = new Chart(
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
                    tooltip: {
                        titleColor: axisText,
                        bodyColor: axisText
                    }
                },
                scales: {
                    y: { ticks: { color: axisText }, grid: { color: gridColor } },
                    x: { ticks: { color: axisText }, grid: { color: gridColor } }
                }
            }
        }
    )
}

export function prepareBankDatasets(datasetsRaw) {
    return datasetsRaw.map(ds => {
        if (!bankColorMap[ds.label]) {
            const index = Object.keys(bankColorMap).length % BANK_COLORS.length
            bankColorMap[ds.label] = BANK_COLORS[index]
        }

        const color = bankColorMap[ds.label]

        return {
            ...ds,
            borderColor: color,
            backgroundColor: color.replace('rgb','rgba').replace(')',',0.2)'),
            tension: 0.3
        }
    })
}

function getBankColor(label) {

    if (!bankColorRegistry.has(label)) {
        const index = bankColorRegistry.size % BANK_COLORS.length
        bankColorRegistry.set(label, BANK_COLORS[index])
    }

    return bankColorRegistry.get(label)
}

export function normalizeBankSeries(series) {

    const normalized = {}

    Object.entries(series).forEach(([scope, data]) => {

        const datasets = (data.datasets || []).map(ds => {

            const color = getBankColor(ds.label)

            return {
                ...ds,
                borderColor: color,
                backgroundColor: color.replace('rgb','rgba').replace(')',',0.2)'),
                tension: 0.3
            }
        })

        normalized[scope] = {
            labels: data.labels,
            datasets
        }

    })

    return normalized
}