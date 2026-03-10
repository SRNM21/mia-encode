export function showLoading(emptyEl, canvasEl, message = 'Fetching data...') {
    emptyEl.removeClass('hidden')
    canvasEl?.addClass('hidden')
    emptyEl.find('p').text(message)
}

export function showEmpty(emptyEl, message) {
    emptyEl.removeClass('load')
    emptyEl.find('p').text(message)
}

export function showContent(emptyEl, canvasEl) {
    emptyEl.addClass('hidden')
    canvasEl?.removeClass('hidden')
}
