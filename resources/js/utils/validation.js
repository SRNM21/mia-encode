export function setErrorMessage(
    errorContainer, 
    errors,
    appendError = false,
    title = 'Invalid input'
) {
    errorContainer.removeClass('hidden')
    const errorHeaderContainer = errorContainer.find('.status-title')
    const errorMessageContainer = errorContainer.find('.status-message')
    errorHeaderContainer.html('')

    if (!appendError) errorMessageContainer.html('')

    errorHeaderContainer.text(title)
    errors.forEach(error => {
        const errorItem = $('<p></p>').text(`${error.message}`)
        errorMessageContainer.append(errorItem)
    })
}

function renderError(errorContainer, errors) {
    errors.forEach(err => {
        if (err.el) {
            err.el.addClass('error')
        }
    })

    setErrorMessage(errorContainer, errors)
    return
}

export function validateEditAppForm(errorContainer, input) {
    const [data, clientErrors] = validateCheckClientForm(errorContainer, input)

    const errors = [...clientErrors]
    
    const banks = input.banks
    data.banks = banks

    if (!banks || banks.length <= 0) {
        errors.push({
            field: 'banks',
            message: 'Please select at least one bank.'
        })
    }
    
    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}

export function validateCheckClientForm(errorContainer, input) {
    const errors = []
    const data = {}

    const fields = [
        {
            key: 'firstname',
            el: input.firstname,
            regex: /^[A-Za-z]+( [A-Za-z]+)*$/,
            message: {
                required: 'First name is required.',
                invalid: 'First name must contain only letters and spaces.'
            },
            required: true
        },
        {
            key: 'middlename',
            el: input.middlename,
            regex: /^[A-Za-z]+( [A-Za-z]+)*$/,
            message: {
                invalid: 'Middle name must contain only letters and spaces.'
            },
            required: false
        },
        {
            key: 'lastname',
            el: input.lastname,
            regex: /^[A-Za-z]+( [A-Za-z]+)*$/,
            message: {
                required: 'Last name is required.',
                invalid: 'Last name must contain only letters and spaces.'
            },
            required: true
        },
        {
            key: 'mobile',
            el: input.mobile,
            regex: /^09\d{9}$/,
            message: {
                required: 'Mobile number is required.',
                invalid: 'Mobile number must be 11 digits and start with 09.'
            },
            required: true
        },
        {
            key: 'birthdate',
            el: input.birthdate,
            regex: /^\d{2}\/\d{2}\/\d{4}$/,
            message: {
                required: 'Birthdate is required.',
                invalid: 'Invalid birthdate format.'
            },
            required: true
        }
    ]

    fields.forEach(field => {
        field.el.removeClass('error')
        var value = field.el.val().trim()

        if (field.key !== 'mobile' && field.key !== 'birthdate') {
            value = value.toUpperCase()
        }

        data[field.key] = value

        const isEmpty = !value
        const isInvalid = !field.regex?.test(value)

        if ((field.required && isEmpty) || (!isEmpty && isInvalid)) {
   
            errors.push({
                field: field.key,
                el: field.el,
                message: field.message[isEmpty ? 'required' : 'invalid']
            })

        }
    })

    // Render error
    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}

export function validateStoreClientForm(errorContainer, input) { 
    const [data, clientErrors] = validateCheckClientForm(errorContainer, input)

    const errors = [...clientErrors]

    // Agent
    const agent = input.agent.val().trim().toUpperCase()
    input.agent.removeClass('error')
    data.agent = agent

    if (!agent) {
        errors.push({
            field: 'agent',
            el: input.agent,
            message: 'Agent is required.'
        })
    }

    // Banks
    const banks = input.banks
    data.banks = banks

    if (!banks || banks.length <= 0) {
        errors.push({
            field: 'banks',
            message: 'Please select at least one bank.'
        })
    }
    
    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}


export function validateEditClientForm(errorContainer, input) { 
    const [data, clientErrors] = validateCheckClientForm(errorContainer, input)

    const errors = [...clientErrors]

    // Agent
    const agent = input.agent.val().trim().toUpperCase()
    input.agent.removeClass('error')
    data.agent = agent

    if (!agent) {
        errors.push({
            field: 'agent',
            el: input.agent,
            message: 'Agent is required.'
        })
    }

    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}

function parseDate(dateStr) {
    const [month, day, year] = dateStr.split('/').map(Number)
    return new Date(year, month - 1, day)
}

export function validateExportDateForm(errorContainer, input) { 
    const errors = []
    const data = {}

    const DATE_REGEX = /^\d{2}\/\d{2}\/\d{4}$/

    const inpStartDate = input.start_date
    inpStartDate.removeClass('error')

    const startDate = inpStartDate.val().trim()
    data.start_date = startDate

    // Validate start date
    if (!startDate) {
        errors.push({
            field: 'startDate',
            el: inpStartDate,
            message: 'Start date is required.'
        })
    } else if (!DATE_REGEX.test(startDate)) {
        errors.push({
            field: 'startDate',
            el: inpStartDate,
            message: 'Invalid start date format.'
        })
    }
    
    const inpEndDate = input.end_date
    inpEndDate.removeClass('error')

    const endDate = inpEndDate.val().trim()
    data.end_date = endDate

    // Validate end date
    if (!endDate) {
        errors.push({
            field: 'endDate',
            el: inpEndDate,
            message: 'End date is required.'
        })
    } else if (!DATE_REGEX.test(endDate)) {
        errors.push({
            field: 'endDate',
            el: inpEndDate,
            message: 'Invalid end date format.'
        })
    }

    const today = new Date()
    today.setHours(0,0,0,0)

    if (
        DATE_REGEX.test(startDate) &&
        DATE_REGEX.test(endDate)
    ) {
        const start = parseDate(startDate)
        const end = parseDate(endDate)

        if (start > today || end > today) {
            errors.push({
                el: inpEndDate,
                message: 'Start and end date must be earlier than today.'
            })
        }

        if (start > end) {
            errors.push({
                el: inpStartDate,
                message: 'Start date must be earlier than end date.'
            })
        }
    }

    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}

export function validateBankDetails(errorContainer, input) {
    const errors = []
    const data = {}

    const pushError = (el, message) => {
        errors.push({ el, message })
    }

    const getValue = (el) => el.val()?.trim().toUpperCase() ?? ''

    const validateRequired = (el, value, message) => {
        el.removeClass('error')
        if (!value) pushError(el, message)
    }

    const bankName = getValue(input.bank_name)
    data.bank_name = bankName
    validateRequired(input.bank_name, bankName, 'Bank name is required.')

    const shortBankName = getValue(input.bank_short_name)
    data.short_bank_name = shortBankName
    validateRequired(input.bank_short_name, shortBankName, 'Short bank name is required.')

    if (shortBankName.length > 5) {
        pushError(input.bank_short_name, 'Short name must be less than 5 characters.')
    }

    const expiryContainer = input.expiry_months.container
    const expiryInput = input.expiry_months.input

    expiryContainer.removeClass('error')

    const expiryMonths = Number(getValue(expiryInput))
    data.expiry_months = expiryMonths

    if (!expiryMonths) {
        pushError(expiryContainer, 'Expiry months is required.')
    } else if (expiryMonths <= 0 || expiryMonths > 60) {
        pushError(expiryContainer, 'Expiry months must be between 1 and 60.')
    }

    const bankStatus = getValue(input.bank_status)
    data.bank_status = bankStatus
    validateRequired(input.bank_status, bankStatus, 'Bank status is required.')

    if (errors.length) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}