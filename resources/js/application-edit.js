import { capitalizeWord, formatDate, href, scrollToBottom, showLoading, showNotification } from "./utils/utils.js"
import { validateEditAppForm } from "./utils/validation.js"
import { useAjax } from './hooks/use-ajax.js'

const { patch } = useAjax()

const doc = $(document)

const firstname = $('#firstname')
const middlename = $('#middlename')
const lastname = $('#lastname')
const birthdate = $('#birthdate')
const mobile = $('#mobile')

const applicationEditErrorCard = $('.application-edit-error-card')
const applicationEditInfoCard = $('.application-edit-info-card')

const saveBtn = $('.save-button')

const ICON_CHECK = '<svg class="icon-svg icon-check" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M5 13l4 4L19 7"/></svg>'

let IS_LOADING = false
let CLIENT_ORIG_DATA = null
let APP_ORIG_DATA = null

doc.ready(function () {
    CLIENT_ORIG_DATA = client
    APP_ORIG_DATA = application
    
    birthdate.datepicker({
        changeMonth: true,
        changeYear: true,
        yearRange: "-100:+0",
        dateFormat: 'mm/dd/yy'
    });

    doc.on('click', 'td.bank-select-cell', function () {
        const cell = $(this)
        
        if (cell.hasClass('disabled') || cell.closest('tr').hasClass('row-disabled')) return
        if (cell.hasClass('selected')) {
            cell.removeClass('selected').html('')
        } else {
            cell.addClass('selected').html(ICON_CHECK)
        }
    })

    saveBtn.on('click', async function () {  
        if (IS_LOADING) return
        IS_LOADING = true

        showLoading(saveBtn, true)
        const selectedBankIds = $('td.bank-select-cell.selected')
            .map((_, cell) => Number($(cell).attr('data-bank-id')))
            .get();

        const originalBankIds = JSON.parse(String(APP_ORIG_DATA.bank_submitted_id))

        const [data, errors] = validateEditAppForm(applicationEditErrorCard, {
            firstname: firstname,
            middlename: middlename,
            lastname: lastname,
            birthdate: birthdate,
            mobile: mobile,
            banks: selectedBankIds
        })

        const isSameBanks = eaualBankApplications(selectedBankIds, originalBankIds);
        const isSameClient = equalClientData(CLIENT_ORIG_DATA, data)

        if (isSameBanks && isSameClient) {
            applicationEditInfoCard.removeClass('hidden')
            applicationEditInfoCard.find('.status-title').html('Nothing to edit')
            applicationEditInfoCard.find('.status-message').html('All inputs are the same as the previous.')
            IS_LOADING = false
            scrollToBottom('.content')
            showLoading(saveBtn, false)
            return
        } else {
            applicationEditInfoCard.addClass('hidden')
        }

        if (errors.length > 0) {
            scrollToBottom('.content')
            showLoading(saveBtn, false)
            return
        }

        try {
            const response = await patch({
                url: 'bank-applications',
                data: {
                    update: {
                        app_id: APP_ORIG_DATA.id,
                        data: data
                    }
                }
            })

            console.log(response);
            
            href('/mia_encode/bank-applications?edit=success')
        } catch (error) {
            const response = error?.responseJSON ?? error
            console.log(response)
        }

        IS_LOADING = false
        showLoading(saveBtn, false)
    })
})

function eaualBankApplications(a, b) {
    if (a.length !== b.length) return false;

    const sortedA = [...a].sort((x, y) => x - y);
    const sortedB = [...b].sort((x, y) => x - y);

    return sortedA.every((val, i) => val === sortedB[i]);
}

function equalClientData(model, data) {
    if (model.first_name !== data.firstname) return false
    if (model.middle_name !== data.middlename) return false
    if (model.last_name !== data.lastname) return false
    if (model.mobile_num !== data.mobile) return false
    if (formatDate(model.birthdate) !== formatDate(data.birthdate)) return false

    return true
}