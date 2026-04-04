import { capitalizeWord, closeModal, formatDate, formatDateTime, getCurrentDateTime, href, openModal, showNotification } from "./utils/utils.js";
import { useAjax } from './hooks/use-ajax.js'

const { patch } = useAjax()

let detachedContainer = null

const requestChoiceContainer = $('.request-choice')
const rejectEditBtn = $('#reject-edit-request-btn')
const approveEditBtn = $('#approve-edit-request-btn')

const requestOrder = $('#request-order-select')
const requestFilter = $('#request-filter-select')

const oldDataLabel = $('#data-old')
const newDataLabel = $('#data-new')

const modalFirstname = $('#original-firstname')
const modalMiddlename = $('#original-middlename')
const modalLastname = $('#original-lastname')
const modalBirthdate = $('#original-birthdate')
const modalMobile = $('#original-mobile')
const modalBanks = $('#original-banks')
const modalBankDateSubmitted = $('#client-bank-date-submitted')

const VIEW_EDIT_REQUEST_MODAL = 'view-edit-request-modal'

let IS_LOADING = false
let CURRENT_EDIT_REQUEST_ID = null
let CURRENT_EDIT_REQUEST_CONTAINER = null
let CURRENT_EDIT_REQUEST_DATA = null

function clearEditRequestData() {
    CURRENT_EDIT_REQUEST_ID = null
    CURRENT_EDIT_REQUEST_CONTAINER = null
    CURRENT_EDIT_REQUEST_DATA = null
}

async function withLoading(fn) {
    if (IS_LOADING) return
    IS_LOADING = true

    try {
        await fn()
    } catch (error) {
        const response = error.responseJSON ?? error
        console.log(response)
    } finally {
        IS_LOADING = false
    }
}

function updateRequestStatusUI(status) {
    const el = CURRENT_EDIT_REQUEST_CONTAINER

    el.removeClass('approved rejected pending')
    el.addClass(status)
    el.attr('data-request-status', status)
    el.attr('data-action-datetime', getCurrentDateTime())
}

async function handleEditAction(type) {
    await withLoading(async () => {
        const response = await patch({
            url: `requests/${type}`,
            data: {
                id: CURRENT_EDIT_REQUEST_ID,
                data: {
                    app_id: CURRENT_EDIT_REQUEST_DATA.requestAppId,
                    new_data: CURRENT_EDIT_REQUEST_DATA.requestNew
                }
            }
        })

        const result = response.data

        updateRequestStatusUI(type === 'approve' ? 'approved' : 'rejected')

        closeModal(VIEW_EDIT_REQUEST_MODAL)
        showNotification(result.title, result.message)

        clearEditRequestData()
    })
}

function hydrateModal() {
    const status = CURRENT_EDIT_REQUEST_DATA.requestStatus

    if (status !== 'pending') {
        detachedContainer = requestChoiceContainer.detach()
        $('.action-request-datetime').removeClass('hidden')
        $('.action-request-datetime').html(`<strong>${capitalizeWord(status)}</strong> at: ${formatDateTime(CURRENT_EDIT_REQUEST_DATA.actionDatetime)}`)
    } else if (detachedContainer) {
        $('.action-request-datetime').addClass('hidden')
        $('#view-edit-request-modal .modal-body').append(detachedContainer)
    }

    $('.edit-request-datetime')
        .html(`Edit requested at: ${formatDateTime(CURRENT_EDIT_REQUEST_DATA.requestDatetime)} by ${CURRENT_EDIT_REQUEST_DATA.requestEncoder}`)
    
    const banks = CURRENT_EDIT_REQUEST_DATA.banks

    oldDataLabel.text(CURRENT_EDIT_REQUEST_DATA.requestOld)
    newDataLabel.text(CURRENT_EDIT_REQUEST_DATA.requestNew)

    modalFirstname.text(CURRENT_EDIT_REQUEST_DATA.clientFirstName)
    modalMiddlename.text(CURRENT_EDIT_REQUEST_DATA.clientMiddleName)
    modalLastname.text(CURRENT_EDIT_REQUEST_DATA.clientLastName)
    modalBirthdate.text(CURRENT_EDIT_REQUEST_DATA.clientBirthdate)
    modalMobile.text(CURRENT_EDIT_REQUEST_DATA.clientMobile)
    modalBanks.html(
        banks.length
            ? banks.map(bank => `<span class="badge">${bank}</span>`)
            : 'No banks'
    )

    modalBankDateSubmitted.text(formatDate(CURRENT_EDIT_REQUEST_DATA.dateSubmitted))
}

$(document).on('click', '.request-container', async function () {
    await withLoading(async () => {
        clearEditRequestData()

        const $container = $(this)
        const data = $container.data()

        CURRENT_EDIT_REQUEST_CONTAINER = $container
        CURRENT_EDIT_REQUEST_ID = data.requestEditId
        CURRENT_EDIT_REQUEST_DATA = data
        CURRENT_EDIT_REQUEST_DATA.requestStatus = $container.attr('data-request-status')

        if ($container.attr('data-request-is-read') == '0') {
            $container.attr('data-request-is-read', '1')

            await patch({
                url: 'requests/read',
                data: { id: CURRENT_EDIT_REQUEST_ID }
            })
        }

        hydrateModal()
        openModal(VIEW_EDIT_REQUEST_MODAL)
        $container.removeClass('unread')
    })
})

rejectEditBtn.on('click', () => handleEditAction('reject'))
approveEditBtn.on('click', () => handleEditAction('approve'))

requestOrder.on('change', () => {
    const value = requestOrder.val()
    setParams('order', String(value))
})

requestFilter.on('change', () => {
    const value = requestFilter.val()
    setParams('filter', String(value))
})

function setParams(key, value) {
    const url = new URL(window.location.href)
    url.searchParams.set(key, String(value))
    href(url.toString())
}