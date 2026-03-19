import { closeModal, formatDate, href, equalClientData, openModal, setErrorState, showLoading, showNotification, formatDateTime } from './utils/utils.js'
import { useAjax } from './hooks/use-ajax.js'
import { validateEditClientForm, validateExportDateForm } from './utils/validation.js'

const { post, patch, del } = useAjax()

const CLOSE_SVG = `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>`

const EXPORT_LOADING_MODAL = 'export-loading-modal'
const SELECT_DATE_MODAL = 'select-date-export-modal'
const ADD_FILTER_MODAL = 'add-filter-modal'
const EDIT_APP_MODAL = 'edit-application-modal'
const CANCEL_EDIT_REQUEST_MODAL = 'cancel-edit-request-modal'
const VIEW_EDIT_REQUEST_MODAL = 'view-edit-request-modal'

const FILTER_COLUMNS = {
    last_name: "Lastname",
    first_name: "Firstname",
    middle_name: "Middlename",
    birthdate: "Birthdate",
    mobile_num: "Mobile Number",
    start_date: "Start Date",
    end_date: "End Date"
}

const container = $('#applications-container')

// pagination
const startBtn = $('#applications-start')
const prevBtn = $('#applications-prev')
const nextBtn = $('#applications-next')
const lastBtn = $('#applications-last')

const perPageSelect = $('#applications-per-page')
const pageInfo = $('#applications-page-info')

// filters
const filterContainer = $('.filter-container')
const filterBar = $('#filter-bar')

const filterColumns = $('#filter-column')
const filterValue = $('#filter-value')

const addFilterBtn = $('#add-filter-btn')
const confirmAddFilterBtn = $('#add-filter-confirm')

// export
const exportExcel = $('#export-excel')
const confirmExportBtn = $('.confirm-export-btn')

const rangeDateErrorCard = $('.range-date-error-card')
const rangeDateInfoCard = $('.range-date-info-card')
const startDateExport = $('#start-date')
const endDateExport = $('#end-date')

const exportModalContent = $('.export-loading-modal-content')
const exportBodyMessage = $('.export-message')
const exportBodyContent = $('.export-content')

const exportLoaderCard = exportBodyContent.find('.empty')
const exportDownloadCard = exportBodyContent.find('.download-link-container')

const exportDownloadLink = $('#export-download-link')

// Edit modal details
let EDIT_APP_ID = null
let EDIT_APP_ORIG_DATA = {}
let CURRENT_EDIT_CELL = null
let CURRENT_EDIT_ROW = null
const firstname = $('#ea-firstname')
const middlename = $('#ea-middlename')
const lastname = $('#ea-lastname')
const birthdate = $('#ea-birthdate')
const mobile = $('#ea-mobile')
const agent = $('#ea-agent')

const confirmEditAppBtn = $('#edit-application-confirm')
const applicationEditErrorCard = $('.application-edit-error-card')
const applicationEditInfoCard = $('.application-edit-info-card')

const confirmCancelEditRequestBtn = $('.confirm-edit-request-btn')

// -------------------------
//   STATE
// -------------------------

let currentPage = parseInt(container.data('page') || '1', 10)
let perPage = parseInt(container.data('per-page') || perPageSelect.val() || '25', 10)

let activeFilters = {}
const optionOrder = []

let exportBlobUrl = null
let IS_LOADING = false

// -------------------------
//  PAGINATION HELPERS
// -------------------------

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

// -------------------------
//  FILTER HELPERS
// -------------------------

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

// -------------------------
//  EXPORT HELPERS
// -------------------------

function attachDownloadLink(data, url) {
    exportBlobUrl = url

    const filename = `TRANSMITAL-${data.start_date}-TO-${data.end_date}.xlsx`
    exportBodyMessage.html(`Your exported data is now available for download.`)
    exportDownloadCard.find('p').html(filename)
    showExportLoading(false)

    const seperator = $(`<hr class='dynamic-hr'>`)
    const closeAction = $(`<button id='close-export-btn' class='outline sm modal-cancel-btn' data-modal='export-loading-modal'>Close</button>`)
    const modalActions = $(`<div class='modal-actions'></div>`)

    closeAction.on('click', function (e) {
        const modalId = $(e.currentTarget).data('modal')
        closeModal(modalId)

        exportModalContent.find('.dynamic-hr').remove()
        exportModalContent.find('.modal-actions').remove()
        exportBodyContent.find('.download-link').remove()

        if (exportBlobUrl) {
            window.URL.revokeObjectURL(exportBlobUrl)
            exportBlobUrl = null
        }
    })

    modalActions.append(closeAction)

    exportModalContent.append(seperator)
    exportModalContent.append(modalActions)
    exportDownloadLink.attr('href', url)
    exportDownloadLink.attr('download', filename)
}

function showExportLoading(show) {
    if (show) {
        exportLoaderCard.removeClass('hidden')
        exportDownloadCard.addClass('hidden')
    } else {
        exportLoaderCard.addClass('hidden')
        exportDownloadCard.removeClass('hidden')
    }
}

async function executeExport(data, result) {
    openModal(EXPORT_LOADING_MODAL)

    exportBodyMessage.html(`Exporting ${result.total} rows of data. This may take ${result.bestMinute} to ${result.worstMinute} minutes depending on the number of data.`)
    showExportLoading(true)

    try {
        const response = await fetch('bank-applications/export', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            },
            body: JSON.stringify(data)
        })

        if (!response.ok) throw new Error('Export failed')

        const blob = await response.blob()
        const url = window.URL.createObjectURL(blob)

        attachDownloadLink(data, url)
    } catch (err) {
        closeModal(EXPORT_LOADING_MODAL)
        console.error('Export error:', err)

    }
}

// -------------------------
//  EDIT APPLICATION HELPERS
// -------------------------

function hideCards() {
    rangeDateErrorCard.addClass('hidden')
    rangeDateInfoCard.addClass('hidden')
}

function clearRangeDate() {
    startDateExport.removeClass('error')
    endDateExport.removeClass('error')
    startDateExport.val('')
    endDateExport.val('')
    hideCards()
}

function showInfo(title, message) {
    rangeDateInfoCard.removeClass('hidden')
    rangeDateInfoCard.find('.status-title').html(title)
    rangeDateInfoCard.find('.status-message').html(message)
}

function clearEditModal() {
    EDIT_APP_ID = null
    EDIT_APP_ORIG_DATA = {}
    CURRENT_EDIT_CELL = null
    CURRENT_EDIT_ROW = null
    applicationEditInfoCard.addClass('hidden')
    applicationEditErrorCard.addClass('hidden')
    firstname.removeClass('error')
    middlename.removeClass('error')
    lastname.removeClass('error')
    birthdate.removeClass('error')
    mobile.removeClass('error')
    agent.removeClass('error')

    firstname.val('')
    middlename.val('')
    lastname.val('')
    birthdate.val('')
    mobile.val('')
    agent.val('')
}

function hydrateEditModal(data) {
    EDIT_APP_ORIG_DATA = data

    EDIT_APP_ID = data.id
    firstname.val(data.firstname)
    middlename.val(data.middlename)
    lastname.val(data.lastname)
    birthdate.val(data.birthdate)
    mobile.val(data.mobile)
    agent.val(data.agent)
}

function changeToAvailable(isAvailable) {  

    const pendingEditCell = $(`
        <div class="flex-row gap-8">
            <button 
                data-request-edit-id='<?= $application->request_edit_id ?>' 
                class="outline sm cancel-edit-application-btn"
            >
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                Cancel Request
            </button>
            <button class="outline sm view-edit-application-btn">
                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-eye-icon lucide-eye"><path d="M2.062 12.348a1 1 0 0 1 0-.696 10.75 10.75 0 0 1 19.876 0 1 1 0 0 1 0 .696 10.75 10.75 0 0 1-19.876 0"/><circle cx="12" cy="12" r="3"/></svg>
                View
            </button>
        </div>
    `)

    const availableEditCell = $(`
        <button class="outline sm edit-application-btn">
            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"/><path d="m15 5 4 4"/></svg>
            Request Edit
        </button>
    `)
    
    CURRENT_EDIT_CELL.html(isAvailable ? availableEditCell : pendingEditCell)
}

function setField(originalSelector, editSelector, originalValue, editValue) {
    const originalEl = $(originalSelector);
    const editEl = $(editSelector);

    originalEl.html(originalValue);
    editEl.html(editValue);

    originalEl.removeClass('old-data');
    editEl.removeClass('new-data');

    if (String(originalValue ?? '').trim() !== String(editValue ?? '').trim()) {
        originalEl.addClass('old-data');
        editEl.addClass('new-data');
    }
}

$(document).ready(function () {
    renderPaginationState()

    startDateExport.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    })

    endDateExport.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    })

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

    // ---------------------
    // Filters
    // ---------------------

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

    addFilterBtn.on('click', function () {
        openModal(ADD_FILTER_MODAL)
    })

    confirmAddFilterBtn.on('click', function () {
        const column = filterColumns.val()
        const value = filterValue.val().trim()

        setErrorState(filterColumns, !column)
        setErrorState(filterValue, !value)

        if (!column || !value) return

        activeFilters[column] = value

        filterColumns.find(`option[value="${column}"]`).remove()

        closeModal(ADD_FILTER_MODAL)

        renderFilters()
        applyFilters()

        filterColumns.val('')
        filterValue.val('')
    })

    filterColumns.on('change', function (e) {
        const column = $(e.currentTarget).val()

        filterValue.val('')
        filterValue.off('input')
        filterValue.datepicker("destroy")

        filterValue.attr('autocomplete', 'on')

        const isDate = ['birthdate', 'start_date', 'end_date'].includes(column)
        const isName = ['first_name', 'middle_name', 'last_name'].includes(column)
        const isMobile = column === 'mobile_num'

        if (isName) {
            filterValue.on('input', function () {
                let value = this.value

                value = value.replace(/[^a-zA-Z\s]/g, '')
                value = value.replace(/\s{2,}/g, ' ')
                value = value.replace(/^\s+/, '')

                this.value = value
            })

        } else if (isMobile) {
            filterValue.on('input', function () {
                this.value = this.value.replace(/\D/g, '')

                if (this.value.length > 11) {
                    this.value = this.value.slice(0, 11)
                }
            })

        } else if (isDate) {
            filterValue.attr('autocomplete', 'off')

            filterValue.datepicker({
                changeMonth: true,
                changeYear: true,
                yearRange: "-100:+0",
                dateFormat: 'yy-mm-dd'
            })
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

    // ---------------------
    // Export
    // ---------------------

    exportExcel.on('click', async function () {
        clearRangeDate()
        openModal(SELECT_DATE_MODAL)
    })

    confirmExportBtn.on('click', async function () {
        hideCards()

        if (IS_LOADING) return
        IS_LOADING = true

        const [data, errors] = validateExportDateForm(rangeDateErrorCard, {
            start_date: startDateExport,
            end_date: endDateExport,
        })

        if (errors.length > 0) return

        try {
            const response = await post({
                url: 'bank-applications/pre-export',
                data: data
            })
            
            IS_LOADING = false

            const result = response.data

            // Prevent export if no data
            if (result.total <= 0) {
                showInfo(
                    'No data found', 
                    `${result.total} data found within ${data.start_date} and ${data.end_date}`
                )

                return
            }

            closeModal(SELECT_DATE_MODAL)
            await executeExport(data, result)
        } catch (error) {
            const response = xhr.responseJSON
            console.log(response)
            IS_LOADING = false
        }
    })
    
    // ---------------------
    // Edit Application
    // ---------------------

    birthdate.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    });

    $(document).on('click', '.edit-application-btn', function () {
        const row = $(this).closest('tr')
        const data = row.data()
        
        clearEditModal()

        const date = new Date(data.birthdate)
        const month = String(date.getMonth() + 1).padStart(2, '0')
        const day = String(date.getDate()).padStart(2, '0')
        const year = String(date.getFullYear())
        const formattedBirthdate = `${month}/${day}/${year}`

        data.birthdate = formattedBirthdate
        EDIT_APP_ORIG_DATA = data

        hydrateEditModal(EDIT_APP_ORIG_DATA)

        CURRENT_EDIT_ROW = row
        CURRENT_EDIT_CELL = $(this).closest('td')
        openModal(EDIT_APP_MODAL)
    })

    confirmEditAppBtn.on('click', async function () {  
        if (IS_LOADING) return
        IS_LOADING = true

        const [data, errors] = validateEditClientForm(applicationEditErrorCard, {
            firstname: firstname,
            middlename: middlename,
            lastname: lastname,
            birthdate: birthdate,
            mobile: mobile,
            agent: agent,
        })

        const eq = equalClientData(EDIT_APP_ORIG_DATA, data)
        
        if (eq) {
            applicationEditInfoCard.removeClass('hidden')
            applicationEditInfoCard.find('.status-title').html('Nothing to edit')
            applicationEditInfoCard.find('.status-message').html('All inputs are the same as the previous.')
            IS_LOADING = false
            return
        } else {
            applicationEditInfoCard.addClass('hidden')
        }

        if (errors.length > 0) return
        
        try {
            const response = await post({
                url: 'request-edit',
                data: {
                    edit_id: EDIT_APP_ID,
                    old_data: EDIT_APP_ORIG_DATA,
                    new_data: data
                }
            })
            
            const result = response.data
            console.log(result);
            
            showNotification(
                'Success', 
                'Edit request was successfully sent.'
            )

            changeToAvailable(false)

            CURRENT_EDIT_ROW.attr({
                'data-request-edit-id': result.id,
                'data-request-encoder': result.encoder,
                'data-request-new-content': result.new_content,
                'data-request-status': result.status,
                'data-request-datetime': result.datetime_request,
            })

        } catch (error) {
            const response = error.responseJSON
            console.log(response)
        }

        IS_LOADING = false
        closeModal(EDIT_APP_MODAL)
    })

    let REQUEST_EDIT_ID_TO_DELETE = null

    $(document).on('click', '.cancel-edit-application-btn', function () {
        const btn = $(this)
        const requestEditId = btn.data('requestEditId')
        REQUEST_EDIT_ID_TO_DELETE = requestEditId
        
        CURRENT_EDIT_CELL = $(this).closest('td')
        openModal(CANCEL_EDIT_REQUEST_MODAL)
    })

    confirmCancelEditRequestBtn.on('click', async function () {  
        if (IS_LOADING) return
        IS_LOADING = true 

        showLoading(confirmCancelEditRequestBtn, true)

        try {
            const response = await del({
                url: 'request-edit',
                data: {id: REQUEST_EDIT_ID_TO_DELETE}
            })
            
            const result = response.data
            console.log(result);
            
            showNotification(result.title, result.message)
            changeToAvailable(true)
        } catch (error) {
            const response = error.responseJSON
            console.log(response)
        }
        
        closeModal(CANCEL_EDIT_REQUEST_MODAL)
        showLoading(confirmCancelEditRequestBtn, false)
        IS_LOADING = false 
    })

    $(document).on('click', '.view-edit-application-btn', function () {
        try {
            const row = $(this).closest('tr')
            const data = row.data()
            const requestDatetime = row.attr('data-request-datetime')
            const requestUpdateContent = JSON.parse(row.attr('data-request-new-content'))

            data.birthdate = formatDate(data.birthdate)
            requestUpdateContent.birthdate = formatDate(requestUpdateContent.birthdate)

            $('.edit-request-datetime').html(`Edit requested at: ${formatDateTime(requestDatetime)}`)
            
            setField('#original-firstname', '#edit-firstname', data.firstname, requestUpdateContent.first_name);
            setField('#original-middlename', '#edit-middlename', data.middlename, requestUpdateContent.middle_name);
            setField('#original-lastname', '#edit-lastname', data.lastname, requestUpdateContent.last_name);
            setField('#original-birthdate', '#edit-birthdate', data.birthdate, requestUpdateContent.birthdate);
            setField('#original-mobile', '#edit-mobile', data.mobile, requestUpdateContent.mobile);
            setField('#original-agent', '#edit-agent', data.agent, requestUpdateContent.agent);
                    
            openModal(VIEW_EDIT_REQUEST_MODAL)
        } catch (e) {
            showNotification('Failed to View', 'Something went wrong, please try again later.', 'error')
            console.log(e);
            
        }
    })

})