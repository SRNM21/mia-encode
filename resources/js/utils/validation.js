export function setErrorMessage(
    errorContainer, 
    errors,
    appendError = false,
    title = 'Invalid input'
) {
    errorContainer.removeClass('hidden')
    const errorHeaderContainer = errorContainer.find('.error-title')
    const errorMessageContainer = errorContainer.find('.error-message')
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


export function validateExportDateForm(errorContainer, input) { 
    const errors = []
    const data = {}

    input.start_date.removeClass('error')
    const startDate = input.start_date.val().trim()
    data.startDate = startDate

    if (!startDate) {
        errors.push({
            field: 'startDate',
            el: input.start_date,
            message: 'Start date is required.'
        })
    }

    input.end_date.removeClass('error')
    const endDate = input.end_date.val().trim()
    data.endDate = endDate

    if (!endDate) {
        errors.push({
            field: 'endDate',
            el: input.end_date,
            message: 'End date is required.'
        })
    }

    if (errors.length > 0) {
        renderError(errorContainer, errors)
    } else {
        errorContainer.addClass('hidden')
    }

    return [data, errors]
}