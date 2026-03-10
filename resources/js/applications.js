import { closeModal, formatDate, href, openModal, setErrorState } from './utils/utils.js'
import { useAjax } from './hooks/use-ajax.js'
import { validateExportDateForm } from './utils/validation.js'

const { post } = useAjax()

const CLOSE_SVG = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`

const startBtn = $('#applications-start')
const prevBtn = $('#applications-prev')
const nextBtn = $('#applications-next')
const lastBtn = $('#applications-last')

const perPageSelect = $('#applications-per-page')
const pageInfo = $('#applications-page-info')

const filterContainer = $('.filter-container')
const filterPopover = $('#filter-popover')
const filterColumns = $('#filter-column')
const filterValue = $('#filter-value')

const container = $('#applications-container')
const bankNameSelect = $('#applications-bank-filter')

const filterBar = $('#filter-bar')
const addFilterBtn = $('#add-filter-btn')
const filterCancel = $('#filter-cancel')
const filterAdd = $('#filter-add')

const exportExcel = $('#export-excel')
const confirmExportBtn = $('.confirm-export-btn')

const rangeDateErrorCard = $('.range-date-error-card')
const startDateExport = $('#start-date')
const endDateExport = $('#end-date')

const exportModalContent = $('.export-loading-modal-content')
const exportBodyContent = $('.export-content')
const exportBodyMessage = $('.export-message')

const EXPORT_LOADING_MODAL = 'export-loading-modal'
const SELECT_DATE_MODAL = 'select-date-export-modal'

var isCancelExport = false

let currentPage = parseInt(container.data('page') || '1', 10)
let perPage = parseInt(container.data('per-page') || perPageSelect.val() || '25', 10)

const FILTER_COLUMNS = {
    last_name: "Lastname",
    first_name: "Firstname",
    middle_name: "Middlename",
    birthdate: "Birthdate",
    mobile_num: "Mobile Number",
    start_date: "Start Date",
    end_date: "End Date"
}

let activeFilters = {}
const optionOrder = []

function totalPages() {
    const last = parseInt(container.data('last-page') || '1', 10)
    return last > 0 ? last : 1
}

function setPage(page) {
    const url = new URL(window.location.href)
    url.searchParams.set('page', page)
    href(url.toString())
}

function renderPaginationState() {
    const total = totalPages()
    const totalRows = parseInt(container.data('total') || '0', 10)

    pageInfo.text(`Page ${currentPage} of ${total} • ${totalRows} rows`)

    const isFirst = currentPage === 1
    const isLast = currentPage >= total

    startBtn.prop('disabled', isFirst)
    prevBtn.prop('disabled', isFirst)
    nextBtn.prop('disabled', isLast)
    lastBtn.prop('disabled', isLast)
}

function loadFiltersFromURL() {
    const params = new URLSearchParams(window.location.search)

    params.forEach((value, key) => {
        if (FILTER_COLUMNS[key]) {
            activeFilters[key] = value
            filterColumns.find(`option[value="${key}"]`).remove()
        }
    })

    renderFilters()
}

function renderFilters() {

    filterBar.find('.filter-chip').remove()

    Object.entries(activeFilters).forEach(([key, val]) => {

        const label = FILTER_COLUMNS[key]
        const isDate = ['Birthdate', 'Start Date', 'End Date'].includes(label)

        const chip = $(`
            <div class="filter-chip">
                ${label}: ${isDate ? formatDate(val) : val}
                <button data-key="${key}" class="ghost filter-chip-remove-btn">${CLOSE_SVG}</button>
            </div>
        `)

        filterBar.append(chip)
    })

    filterContainer.toggleClass(
        'hidden',
        Object.keys(activeFilters).length === 0
    )
}

function restoreOption(value) {

    const info = optionOrder.find(o => o.value === value)
    if (!info) return

    const option = $(`<option value="${info.value}">${info.text}</option>`)
    const options = filterColumns.find('option')

    if (info.index >= options.length) {
        filterColumns.append(option)
    } else {
        options.eq(info.index).before(option)
    }
}

function applyFilters() {

    const params = new URLSearchParams('page=1')

    Object.keys(FILTER_COLUMNS).forEach(k => params.delete(k))

    Object.entries(activeFilters).forEach(([k, v]) => {
        params.set(k, v)
    })

    window.location.search = params.toString()
}

let exportBlobUrl = null;

function attachDownloadLink(url) {

    exportBlobUrl = url;

    const seperator = $(`<hr class='dynamic-hr'>`);
    const closeAction = $(`<button id='close-export-btn' class='outline sm modal-cancel-btn' data-modal='export-loading-modal'>Close</button>`);
    const modalActions = $(`<div class='modal-actions'></div>`);

    closeAction.on('click', function (e) {

        const modalId = $(e.currentTarget).data('modal');
        closeModal(modalId);

        exportModalContent.find('.dynamic-hr').remove();
        exportModalContent.find('.modal-actions').remove();
        exportBodyContent.find('.download-link').remove();

        if (exportBlobUrl) {
            window.URL.revokeObjectURL(exportBlobUrl);
            exportBlobUrl = null;
        }
    });

    modalActions.append(closeAction);
    exportModalContent.append(seperator);
    exportModalContent.append(modalActions);

    const jqLink = $('<a>', {
        href: '#',
        class: 'download-link',
        text: 'bank_applications.xlsx'
    });

    jqLink.on('click', function (e) {
        e.preventDefault();

        if (!exportBlobUrl) return;

        const a = document.createElement('a');
        a.href = exportBlobUrl;
        a.download = 'bank_applications.xlsx';

        document.body.appendChild(a);
        a.click();
        document.body.removeChild(a);
    });

    exportBodyMessage.html('Your file is ready for download.');
    exportBodyContent.html(jqLink);
}

async function executeExport(data, result) {
    openModal(EXPORT_LOADING_MODAL);

    exportBodyMessage.html(`Exporting ${result.total} rows of data. This may take ${result.bestMinute} to ${result.worstMinute} minutes depending on the number of data.`)
    exportBodyContent.html('Please Wait...')

    try {
        const response = await fetch('bank-applications/export', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            },
            body: JSON.stringify(data)
        });

        if (!response.ok) throw new Error('Export failed');

        const blob = await response.blob();
        const url = window.URL.createObjectURL(blob);

        attachDownloadLink(url);

    } catch (err) {
        closeModal(EXPORT_LOADING_MODAL);
        console.error('Export error:', err);
    }
}

$(document).ready(function () {

    startDateExport.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    });

    endDateExport.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    });

    filterColumns.find('option').each(function (index) {

        const value = $(this).val()

        if (value) {
            optionOrder.push({
                value,
                text: $(this).text(),
                index
            })
        }
    })

    loadFiltersFromURL()

    const params = new URLSearchParams(window.location.search)
    const sort = params.get('sort')
    const order = params.get('order')

    if (sort) {
        const jqTh = $(`th[data-key="${sort}"]`)
        jqTh.removeClass('sort-asc sort-desc')

        jqTh.addClass(order === 'asc' ? 'sort-asc' : 'sort-desc')
    }

    perPageSelect.on('change', function () {

        perPage = parseInt(perPageSelect.val() || '25', 10)
        currentPage = 1

        const url = new URL(window.location.href)
        url.searchParams.set('per_page', String(perPage))
        url.searchParams.set('page', '1')

        href(url.toString())
    })

    startBtn.on('click', () => setPage('1'))

    prevBtn.on('click', () =>
        setPage(String(Math.max(1, currentPage - 1)))
    )

    nextBtn.on('click', () =>
        setPage(String(Math.min(totalPages(), currentPage + 1)))
    )

    lastBtn.on('click', () =>
        setPage(String(totalPages()))
    )

    bankNameSelect.on('change', function () {

        currentPage = 1
        applyFilters()
        renderPaginationState()
    })

    addFilterBtn.on('click', function () {

        const pos = addFilterBtn.offset()

        filterPopover.css({
            top: pos.top + addFilterBtn.outerHeight() + 8,
            left: pos.left
        })

        filterPopover.toggleClass('hidden')
    })

    filterCancel.on('click', () =>
        filterPopover.addClass('hidden')
    )

    filterAdd.on('click', function () {

        const column = filterColumns.val()
        const value = filterValue.val().trim()

        setErrorState(filterColumns, !column)
        setErrorState(filterValue, !value)

        if (!column || !value) return

        activeFilters[column] = value

        filterColumns.find(`option[value="${column}"]`).remove()

        renderFilters()
        applyFilters()

        filterPopover.addClass('hidden')
        filterColumns.val('')
        filterValue.val('')
    })

    filterColumns.on('change', function (e) {

        const column = $(e.currentTarget).val()

        filterValue.val('')

        const isDate = ['birthdate', 'start_date', 'end_date'].includes(column)

        if (isDate) {

            filterValue.attr('autocomplete', 'off')
            filterValue.datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                dateFormat: 'yy-mm-dd'
            })

        } else {

            filterValue.datepicker("destroy")
            filterValue.attr('autocomplete', 'on')
        }
    })

    $(document).on('click', '.filter-chip button', function () {

        const key = $(this).data('key')

        delete activeFilters[key]

        restoreOption(key)
        renderFilters()
        applyFilters()
    })

    $('.sortable').on('click', function () {

        const column = $(this).data('key')

        const url = new URL(window.location.href)
        const currentSort = url.searchParams.get('sort')
        const currentOrder = url.searchParams.get('order') || 'desc'

        let newOrder = 'asc'

        if (currentSort === column && currentOrder === 'asc') {
            newOrder = 'desc'
        }

        url.searchParams.set('sort', column)
        url.searchParams.set('order', newOrder)
        url.searchParams.set('page', '1')

        window.location.href = url.toString()
    })

    exportExcel.on('click', async function () {
        openModal(SELECT_DATE_MODAL);
    });

    confirmExportBtn.on('click', async function () {
        const [data, errors] = validateExportDateForm(rangeDateErrorCard, {
            start_date: startDateExport,
            end_date: endDateExport,
        })

        if (errors.length > 0) {
            return
        }
        
        closeModal(SELECT_DATE_MODAL);

        await post({
            url: 'bank-applications/pre-export',
            data: data,
            success: async function (response) {
                const result = response.data
                await executeExport(data, result)
            },
            error: function (xhr) {
                const response = xhr.responseJSON

                console.log(response);
            }
        })
    });
})
