import { useAjax } from './hooks/use-ajax.js'
import { capitalizeWord, formatDate, normalizedServerErrors, scrollToBottom, setErrorState, showNotification } from './utils/utils.js'
import { setErrorMessage, validateCheckClientForm, validateStoreClientForm } from './utils/validation.js'

const doc = $(document)

const { post } = useAjax()

const oldClientBadge = $('.old-client')
const newClientBadge = $('.new-client')
const bankApplicationsContainer = $('#bank-applications-container')
const submitContent = $('.submit-content')
const checkClientErrorCard = $('.check-client-error-card')
const storeClientErrorCard = $('.store-client-error-card')

const clearClientBtn = $('#clear-client-button')
const checkClientBtn = $('#check-client-button')
const tbody = $('#bank-table-body')
const submitBtn = $('.submit-button')

var client_id = null
const firstname = $('#firstname')
const middlename = $('#middlename')
const lastname = $('#lastname')
const birthdate = $('#birthdate')
const mobile = $('#mobile')
const agent = $('#agent')

const ICON_CHECK = '<svg class="icon-svg icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>'
const ICON_UNAVAILABLE = '<svg class="icon-svg icon-unavailable" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"></circle><line x1="7" y1="7" x2="17" y2="17"></line></svg>'

const clientDetails = [
    firstname, 
    middlename, 
    lastname,
    birthdate,
    mobile
]

doc.ready(function () {
    birthdate.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    });

    mobile.on('input', function () {
        this.value = this.value.replace(/\D/g, '');

        if (this.value.length > 11) {
            this.value = this.value.slice(0, 11);
        }
    });

    [firstname, middlename, lastname].forEach(input => {
        input.on('input', function () {
            let value = this.value

            value = value.replace(/[^a-zA-Z\s]/g, '')
            value = value.replace(/\s{2,}/g, ' ')
            value = value.replace(/^\s+/, '')

            this.value = value
        })
    })

    clearClientBtn.on('click', (e) => {
        e.preventDefault()
        clear()
    })

    // Toggle check on cell click
    tbody.on('click', 'td.bank-select-cell', function () {
        const cell = $(this)

        if (cell.hasClass('disabled') || cell.closest('tr').hasClass('row-disabled')) return
        if (cell.hasClass('selected')) {
            cell.removeClass('selected').html('')
        } else {
            cell.addClass('selected').html(ICON_CHECK)
        }
    })

    checkClientBtn.on('click', (e) => {
        e.preventDefault()

        const [data, errors] = validateCheckClientForm(checkClientErrorCard, {
            firstname: firstname,
            middlename: middlename,
            lastname: lastname,
            birthdate: birthdate,
            mobile: mobile,
        })

        if (errors.length > 0) {
            scrollToBottom('.content')
            return
        }

        client_id = null
        tbody.empty()

        post({
            url: 'encode-check',
            data: data,
            success: function (response) {
                renderClientBankApplication(response.data)
                scrollToBottom('.content')
            },
            error: function (xhr) {
                const response = xhr.responseJSON

                setErrorMessage(
                    checkClientErrorCard,
                    normalizedServerErrors(response.data.errors)
                )
            }
        })
    })

    submitBtn.on('click', (e) => {
        e.preventDefault()

        const selectedBanks = $('td.bank-select-cell.selected').map((_, cell) => $(cell).attr('data-bank-id')).get() || []

        const [data, errors] = validateStoreClientForm(storeClientErrorCard, {
            firstname: firstname,
            middlename: middlename,
            lastname: lastname,
            birthdate: birthdate,
            mobile: mobile,
            banks: selectedBanks,
            agent: agent,
        })

        if (errors.length > 0) {
            scrollToBottom('.content')
            return
        }
        
        post({
            url: 'encode',
            data: {client_id, ...data},
            success: function (response) {
                console.log(response);
                clear()
                showNotification('Saved Successfully', 'Client\'s bank application successfully submitted.')
            },
            error: function (xhr) {
                const reponse = xhr.responseJSON

                setErrorMessage(
                    storeClientErrorCard,
                    normalizedServerErrors(reponse.data.errors)
                )

                scrollToBottom('.content')
            }
        })
    })
})

function clear() {
    clientDetails.forEach(input => {
        input.val('')
        input.removeClass('error')
    })

    agent.val('');
    agent.removeClass('error');

    checkClientErrorCard.addClass('hidden')
    storeClientErrorCard.addClass('hidden')

    hideBadge()
    showBankApplicationsContent(false)
    showSubmitContent(false)
}

function showBankApplicationsContent(show) {  
    if (show) {
        bankApplicationsContainer.removeClass('hidden')
    } else {
        bankApplicationsContainer.addClass('hidden')
    }
}

function showSubmitContent(show) {
    submitContent.removeClass(show ? 'hidden' : 'flex').addClass(show ? 'flex' : 'hidden')
}

function hideBadge() {
    oldClientBadge.addClass('hidden')
    newClientBadge.addClass('hidden')
}

function isExpired(dateSubmitted, expirationMonths) {
    const submissionDate = new Date(dateSubmitted);

    // clone the submission date to avoid mutating the original
    const expirationDate = new Date(submissionDate);
    
    expirationDate.setMonth(expirationDate.getMonth() + expirationMonths);
    const currentDate = new Date();

    return currentDate > expirationDate;
}

function renderClientBankApplication(data) {
    const client = data.client
    const banks = Array.isArray(data.banks) ? data.banks : []
    const applications = Array.isArray(data.applications) ? data.applications : []

    const hasClient = client != null

    oldClientBadge.addClass('hidden')
    newClientBadge.addClass('hidden')

    if (hasClient) {
        oldClientBadge.removeClass('hidden')
        client_id = client.id
    } else {
        newClientBadge.removeClass('hidden')
    }

    const appsById = {}

    if (hasClient) {
        applications.forEach(app => {
            appsById[String(app.bank_submitted_id)] = {
                date_submitted: app.date_submitted,
                agent: app.agent,
            }
        })
    }

    banks.forEach(bank => {
        if (!bank.is_active) return

        const app = hasClient ? appsById[bank.id] : null
        const isExpiredApplication = app ? isExpired(app.date_submitted, bank.expiry_months) : false

        const row = $('<tr></tr>')

        // Data
        const date = app ? formatDate(app.date_submitted) : '—'
        const statusText = app && !isExpiredApplication ? 'Unavailable' : 'Available' 
        const statusClass = app && !isExpiredApplication ? 'status-unavailable' : 'status-available'
        const agent = app ? app.agent : '—'

        // Cells
        const bankCell = $('<td></td>').text(bank.name)
        const dateCell = $('<td></td>').text(date)
        const agentCell = $('<td></td>').text(agent)
        const statusCell = $('<td></td>')
            .addClass(statusClass)
            .text(capitalizeWord(statusText))
            
        const actionCell = $('<td></td>')
            .addClass('bank-select-cell')
            .attr('data-bank-name', bank.name)
            .attr('data-bank-id', bank.id)
            .text('')

        if (app && !isExpiredApplication) {
            row.addClass('row-disabled')
            actionCell.addClass('disabled unavailable').html(ICON_UNAVAILABLE)
        }

        row.append(bankCell, dateCell, agentCell, statusCell, actionCell)
        tbody.append(row)
    })

    showBankApplicationsContent(true)
    showSubmitContent(true)
}