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
                backgroundColor: colors.map((c) => c.background),
                borderColor: colors.map((c) => c.border),
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

    renderBankAppsLegend(labels, colors)
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

export function normalizeBankSeries(series) {
    const normalized = {}
    
    Object.entries(series).forEach(([scope, data]) => {
        const color = generateColors(data.datasets.length)

        const datasets = (data.datasets || []).map((ds, i) => {
            return {
                ...ds,
                borderColor: color[i].border,
                backgroundColor: color[i].background,
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

function renderBankAppsLegend(labels, colors) {    
    const legend = $('#bank-apps-type-legend')
    if (!legend || legend.length === 0) return

    legend.empty()
    legend.css({ marginLeft: '24px' })

    labels.forEach((label, i) => {
        const item = $('<span>')
            .addClass('legend-item')
            .css({
                fontSize: '14px',
                display: 'flex',
                flexDirection: 'row',
                alignItems: 'center',
                justifyContent: 'flex-start',
                flexWrap: 'nowrap'
            })

        const dot = $('<span>')
            .addClass('dot')
            .css({
                display: 'inline-block',
                width: '12px',
                height: '12px',
                borderRadius: '50%',
                marginRight: '6px',
                border: `1px solid ${colors[i].border}`,
                backgroundColor: colors[i].background || 'rgba(200,200,200,0.6)'
            })

        item.append(dot).append(`<p>${label}</p>`)
        legend.append(item)
    })
}