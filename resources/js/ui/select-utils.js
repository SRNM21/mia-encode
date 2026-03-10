export function populateSelect(select, values, selected) {
    if (!select || select.length === 0) return

    const prev = select.val()
    select.empty()

    values.forEach(v => {
        select.append($('<option>').attr('value', v).text(v))
    })

    const toSelect = selected || prev || values[values.length - 1]
    if (toSelect) select.val(toSelect)
}

export function bindSelect(select, handler) {
    select.off('change').on('change', handler)
}