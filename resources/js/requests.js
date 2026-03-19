import { capitalizeWord, closeModal, formatDate, formatDateTime, getCurrentDateTime, href, openModal, showNotification } from "./utils/utils.js";
import { useAjax } from './hooks/use-ajax.js'

const { patch } = useAjax()

let detachedContainer = null

const requestChoiceContainer = $('.request-choice')
const rejectEditBtn = $('#reject-edit-request-btn')
const approveEditBtn = $('#approve-edit-request-btn')

const requestOrder = $('#request-order-select')
const requestFilter = $('#request-filter-select')

const VIEW_EDIT_REQUEST_MODAL = 'view-edit-request-modal'

let IS_LOADING = false
let CURRENT_EDIT_REQUEST_ID = null
let CURRENT_EDIT_REQUEST_DATA = null
let CURRENT_EDIT_REQUEST_CONTAINER = null

function clearEditRequestData() {
    CURRENT_EDIT_REQUEST_ID = null
    CURRENT_EDIT_REQUEST_DATA = null
    CURRENT_EDIT_REQUEST_CONTAINER = null
}

function setField(originalSelector, editSelector, originalValue, editValue) {
    const originalEl = $(originalSelector)
    const editEl = $(editSelector)

    originalEl.html(originalValue)
    editEl.html(editValue)

    originalEl.removeClass('old-data')
    editEl.removeClass('new-data')

    if (String(originalValue ?? '').trim() !== String(editValue ?? '').trim()) {
        originalEl.addClass('old-data')
        editEl.addClass('new-data')
    }
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
                update: CURRENT_EDIT_REQUEST_DATA
            }
        })

        const result = response.data

        updateRequestStatusUI(type === 'approve' ? 'approved' : 'rejected')

        closeModal(VIEW_EDIT_REQUEST_MODAL)
        showNotification(result.title, result.message)

        clearEditRequestData()
    })
}

$(document).on('click', '.request-container', async function () {
    await withLoading(async () => {
        clearEditRequestData()

        const $container = $(this)
        const data = $container.data()

        const oldData = data.requestOldContent
        const newData = data.requestNewContent

        newData.app_id = data.requestAppId

        if ($container.attr('data-request-is-read') == '0') {
            $container.attr('data-request-is-read', '1')

            await patch({
                url: 'requests/read',
                data: { id: data.requestEditId }
            })
        }

        CURRENT_EDIT_REQUEST_CONTAINER = $container
        CURRENT_EDIT_REQUEST_ID = data.requestEditId
        CURRENT_EDIT_REQUEST_DATA = newData

        console.log(data);
        
        const status = $container.attr('data-request-status')
        if (status !== 'pending') {
            detachedContainer = requestChoiceContainer.detach()
            $('.action-request-datetime').removeClass('hidden')
            $('.action-request-datetime').html(`<strong>${capitalizeWord(status)}</strong> at: ${formatDateTime($container.attr('data-action-datetime'))}`)
        } else if (detachedContainer) {
            $('.action-request-datetime').addClass('hidden')
            $('#view-edit-request-modal .modal-body').append(detachedContainer)
        }

        $('.edit-request-datetime')
            .html(`Edit requested at: ${formatDateTime(data.requestDatetime)} by ${data.requestEncoder}`)

        const fields = [
            ['firstname', 'first_name'],
            ['middlename', 'middle_name'],
            ['lastname', 'last_name'],
            ['birthdate', 'birthdate', formatDate],
            ['mobile', 'mobile'],
            ['agent', 'agent']
        ]

        fields.forEach(([key, dataKey, formatter]) => {
            const originalVal = oldData[dataKey]
            const newVal = newData[dataKey]

            setField(
                `#original-${key}`,
                `#edit-${key}`,
                formatter ? formatter(originalVal) : originalVal,
                formatter ? formatter(newVal) : newVal
            )
        })

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