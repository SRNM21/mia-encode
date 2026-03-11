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

const vibrantColors = [
    { r: 52, g: 152, b: 219,  hex: '#3498db' }, // Vibrant Blue
    { r: 255, g: 82, b: 116,  hex: '#ff5274' }, // Coral Red
    { r: 72, g: 189, b: 184,  hex: '#48bdb8' }, // Teal
    { r: 255, g: 159, b: 64,  hex: '#ff9f40' }, // Orange
    { r: 155, g: 89, b: 182,  hex: '#9b59b6' }, // Purple
    { r: 255, g: 205, b: 86,  hex: '#ffcd56' }, // Golden Yellow
    { r: 189, g: 195, b: 199, hex: '#bdc3c7' }  // Silver/Grey
];

export function generateColors(count) {
    const colorPalette = [];

    for (let i = 0; i < count; i++) {
        const colorIndex = i % vibrantColors.length;
        const color = vibrantColors[colorIndex];

        colorPalette.push({
            border: color.hex,
            background: `rgba(${color.r}, ${color.g}, ${color.b}, 0.2)`
        });
    }

    return colorPalette;
}