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

    checkClientBtn.on('click', async (e) => {        
        e.preventDefault()

        const [data, errors] = validateCheckClientForm(checkClientErrorCard, {
            firstname: firstname,
            middlename: middlename,
            lastname: lastname,
            birthdate: birthdate,
            mobile: mobile,
        })

        if (errors.length > 0) {
            hideApplicationDetails()
            storeClientErrorCard.addClass('hidden')
            scrollToBottom('.content')
            return
        }

        client_id = null
        tbody.empty()
        console.log(data);
        
        try {
            const response = await post({
                url: 'encode-check',
                data: data,
            })
            
            renderClientBankApplication(response.data)
        } catch (error) {
            const response = error.responseJSON

            console.log(error);
            

            setErrorMessage(
                checkClientErrorCard,
                normalizedServerErrors(response?.data?.errors ?? [])
            )
        }
    })

    submitBtn.on('click', async (e) => {
        console.log('submit');
        
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

        try {
            const response = await post({
                url: 'encode',
                data: {client_id, ...data},
            })
            
            clear()
            showNotification('Saved Successfully', 'Client\'s bank application successfully submitted.')
            lastname.focus()
        } catch (error) {
            const response = error.responseJSON

            setErrorMessage(
                storeClientErrorCard,
                normalizedServerErrors(response.data.errors)
            )

            scrollToBottom('.content')
        }
    })

    submitBtn.on('keydown', function(e) {
        if (e.key === 'Tab' && !e.shiftKey) {
            e.preventDefault();
            firstname.focus()
        }
    })
})

function clear() {
    clientDetails.forEach(input => {
        input.val('')
        input.removeClass('error')
    })

    agent.val('');
    agent.removeClass('error');

    hideErrors()
    hideApplicationDetails()
}

function hideErrors() {
    checkClientErrorCard.addClass('hidden')
    storeClientErrorCard.addClass('hidden')
}

function hideApplicationDetails() {
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
    const expirationDate = new Date(submissionDate);
    
    expirationDate.setMonth(expirationDate.getMonth() + parseInt(expirationMonths, 10));
    const currentDate = new Date();

    return currentDate > expirationDate;
}

function renderClientBankApplication(data) {
    console.log(data);
    
    const client = data.client
    const banks = Array.isArray(data.banks) ? data.banks : []
    const applications = Array.isArray(data.applications)
        ? data.applications
        : []

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
            let bankIds = []

            try {
                bankIds = JSON.parse(app.bank_submitted_id)
            } catch (e) {
                bankIds = []
            }

            if (!Array.isArray(bankIds)) return

            bankIds.forEach(id => {
                appsById[String(id)] = {
                    date_submitted: app.date_submitted,
                    agent: app.agent,
                }
            })
        })
    }
        
    // Toggle check on cell click
    function checkBank(element) {
        const cell = $(element)

        if (cell.hasClass('disabled') || cell.closest('tr').hasClass('row-disabled')) return
        if (cell.hasClass('selected')) {
            cell.removeClass('selected').html('')
        } else {
            cell.addClass('selected').html(ICON_CHECK)
        }
    }

    let tabIndex = 9 // next for submit

    banks.forEach(bank => {
        if (!bank.is_active) return

        const app = hasClient ? appsById[String(bank.id)] : null
        const isExpiredApplication = app 
            ? isExpired(app.date_submitted, bank.expiry_months) 
            : false

        console.log(app);
        

        const row = $('<tr></tr>')

        const date = app ? formatDate(app.date_submitted) : '—'
        const statusText = app && !isExpiredApplication ? 'Unavailable' : 'Available'
        const statusClass = app && !isExpiredApplication 
            ? 'status-unavailable' 
            : 'status-available'

        const agent = app ? app.agent : '—'

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
            .attr('tabindex', `${tabIndex++}`)
            .text('')

        const isDisabled = app && !isExpiredApplication;

        if (isDisabled) {
            row.addClass('row-disabled');
            actionCell.addClass('disabled unavailable').html(ICON_UNAVAILABLE);
            actionCell.removeAttr('tabindex'); 
            actionCell.off('click keydown'); 

        } else {
            actionCell.attr('tabindex', `${tabIndex++}`);
            actionCell.on('click', function() {
                checkBank(this); 
            });
            
            actionCell.on('keydown', function(event) {
                console.log(event);

                if (event.key === 'Enter' || event.key === ' ') {
                    event.preventDefault();
                    $(this).trigger('click');
                }
            });
        }

        row.append(bankCell, dateCell, agentCell, statusCell, actionCell)
        tbody.append(row)
    })

    submitBtn.attr('tabindex', tabIndex++)
    showBankApplicationsContent(true)
    showSubmitContent(true)
}