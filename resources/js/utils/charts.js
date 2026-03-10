export function getChartTheme() {
    const css = getComputedStyle(document.documentElement)

    return {
        axisText: (css.getPropertyValue('--color-text') || '#e5e7eb').trim(),
        gridColor: (css.getPropertyValue('--color-neutral-700') || 'rgba(255,255,255,0.08)').trim()
    }
}

export function destroyChart(instance) {
    if (instance) instance.destroy()
}

export function generateColors(count) {
    const colors = []

    for (let i = 0; i < count; i++) {
        const hue = Math.round((360 / Math.max(count, 1)) * i)
        colors.push({
            border: `hsl(${hue},70%,50%)`,
            background: `hsla(${hue},70%,50%,0.2)`
        })
    }

    return colors
}