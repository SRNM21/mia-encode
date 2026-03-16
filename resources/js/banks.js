import { useAjax } from './hooks/use-ajax.js'
import { closeModal, formatDate, href, openModal, showNotification } from "./utils/utils.js";
import { validateBankDetails } from "./utils/validation.js";

const { post, patch } = useAjax()

const addBankBtn = $('#add-bank-btn')

const bankErrorCard = $('.bank-error-card')
const bankNameInput = $('#bank-name')
const bankShortNameInput = $('#bank-short-name')

const expiryMonthInputContainer = $('.number-input')
const expiryMonthInput = expiryMonthInputContainer.find('input')
const expiryMonthInc = expiryMonthInputContainer.find('.increment')
const expiryMonthdec = expiryMonthInputContainer.find('.decrement')

const container = $('#banks-container')
const bankStatus = $('#bank-status')
const bankTableBody = $('#banks-table-body')

const startBtn = $('#banks-start')
const prevBtn = $('#banks-prev')
const nextBtn = $('#banks-next')
const lastBtn = $('#banks-last')

const perPageSelect = $('#banks-per-page')
const pageInfo = $('#banks-page-info')

const addModalTitle = $('.add-bank-modal-title')
const confirmAddBank = $('#add-bank-confirm')
const lastUpdateNoteContainer = $('.last-update-note-container')
const lastUpdateNote = $('.last-update-note')

let currentPage = parseInt(container.data('page') || '1', 10)
let perPage = parseInt(container.data('per-page') || perPageSelect.val() || '25', 10)

const ADD_BANK_MODAL = 'add-bank-modal'
var EDIT_MODE = false
var ID_TO_EDIT = null
var IS_LOADING = false

function clearAddBankModal() {
    bankNameInput.removeClass('error')
    bankShortNameInput.removeClass('error')
    expiryMonthInputContainer.removeClass('error')
    bankStatus.removeClass('error')
    bankErrorCard.addClass('hidden')

    bankNameInput.val('')
    bankShortNameInput.val('')
    expiryMonthInput.val(1)
    bankStatus.val('')
    updateButtons()
}

function showAddModal(editMode) {
    if (editMode) {
        addModalTitle.html('Edit Bank Details')
        confirmAddBank.html('Save')
        lastUpdateNoteContainer.removeClass('hidden')
    } else {
        addModalTitle.html('Bank Details')
        confirmAddBank.html('Add Bank')
        lastUpdateNoteContainer.addClass('hidden')
    }

    EDIT_MODE = editMode
    openModal(ADD_BANK_MODAL)
}

addBankBtn.on('click', function (e) {  
    clearAddBankModal()
    showAddModal(false)
})

function updateButtons() {
    const value = Number(expiryMonthInput.val())
    const min = expiryMonthInput.attr('min') ? Number(expiryMonthInput.attr('min')) : -Infinity
    const max = expiryMonthInput.attr('max') ? Number(expiryMonthInput.attr('max')) : Infinity

    expiryMonthdec.prop('disabled', value <= min)
    expiryMonthInc.prop('disabled', value >= max)
}

expiryMonthInc.on('click', function () {
    expiryMonthInput[0].stepUp()
    updateButtons()
})

expiryMonthdec.on('click', function () {
    expiryMonthInput[0].stepDown()
    updateButtons()
})

expiryMonthInput.on('input', updateButtons)

updateButtons()

function createBankRow(bank) {
    console.log(bank);
    
    return `
        <tr 
            data-id="${bank.id}"
            data-name=${bank.name}"
            data-short-name="${bank.short_name}"
            data-expiry-months="${bank.expiry_months}"
            data-is-active="${bank.is_active}"
            data-total="0"
            data-last-update="${bank.updated_at}"
        >
            <td>${bank.name}</td>
            <td>${bank.short_name}</td>
            <td>${bank.expiry_months}</td>
            <td>${bank.is_active ? 'Active' : 'Inactive'}</td>
            <td>0</td>
            <td>${formatDate(bank.created_at)}</td>
            <td>${formatDate(bank.updated_at)}</td>
            <td>
                <button data-row-id='${bank.id}' class="edit-bank-btn outline sm">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-pencil-icon lucide-pencil"><path d="M21.174 6.812a1 1 0 0 0-3.986-3.987L3.842 16.174a2 2 0 0 0-.5.83l-1.321 4.352a.5.5 0 0 0 .623.622l4.353-1.32a2 2 0 0 0 .83-.497z"></path><path d="m15 5 4 4"></path></svg>
                    Edit
                </button>
            </td>
        </tr>
    `
}

function showBankAddNotification(response) {
    if (response === null || response === undefined) return

    const notifData = response.data
    showNotification(notifData.title, notifData.message)
}

confirmAddBank.on('click', async function (e) {  
    e.preventDefault()

    if (IS_LOADING) return
    IS_LOADING = true

    const [data, errors] = validateBankDetails(bankErrorCard, {
        bank_name: bankNameInput,
        bank_short_name: bankShortNameInput,
        expiry_months: {
            container: expiryMonthInputContainer,
            input: expiryMonthInput
        },
        bank_status: bankStatus
    })

    if (errors.length > 0) return

    await new Promise(resolve => setTimeout(resolve, 4000))

    try {
        let response = null
        const payload = {
            url: 'banks',
            data: data,
        }
        
        if (EDIT_MODE) {
            payload.data.id = ID_TO_EDIT
            response = await patch(payload)
        } else {
            response = await post(payload)
            bankTableBody.append(createBankRow(response.data.bank))
        }

        console.log(response);
        
        showBankAddNotification(response)
    } catch (error) {
        const response = error.responseJSON
        console.log(response);
        
        showBankAddNotification(response)
    }

    IS_LOADING = false
    closeModal(ADD_BANK_MODAL)
})

// Re-use the ADD_BANK_MODAL and sets the value of the bank
bankTableBody.on('click', '.edit-bank-btn', function () {
    const row = $(this).closest('tr')
    const data = row.data()

    clearAddBankModal()
    showAddModal(true)
    ID_TO_EDIT = data.id
    
    bankNameInput.val(data.name)
    bankShortNameInput.val(data.shortName)
    expiryMonthInput.val(data.expiryMonths)
    bankStatus.val(data.isActive ? 'active' : 'inactive')
    lastUpdateNote.html(formatDate(data.lastUpdate))
    updateButtons()

    console.log(data)
})

const params = new URLSearchParams(window.location.search)
const sort = params.get('sort')
const order = params.get('order')

if (sort) {
    const jqTh = $(`th[data-key="${sort}"]`)
    jqTh.removeClass('sort-asc sort-desc')

    jqTh.addClass(order === 'asc' ? 'sort-asc' : 'sort-desc')
}

renderPaginationState()

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