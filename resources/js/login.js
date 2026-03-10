import { useAjax } from './hooks/use-ajax.js'

const { post } = useAjax()

var isLoggingIn = false;

const passwordToggle = $('.password-toggle')
const loginForm = $('#login-form')
const errorCard = $('.error-card')
const errorTitle = $('.error-title')
const errorMessage = $('.error-message')

passwordToggle.on('click', function () {
    const input = $('#password')
    const isPassword = input.attr('type') === 'password'

    input.attr('type', isPassword ? 'text' : 'password')

    $(this).find('.icon-eye').toggleClass('hidden')
    $(this).find('.icon-eye-slash').toggleClass('hidden')
})


loginForm.on('submit', async (e) => {
    e.preventDefault()

    const $form = $(this)
    const url = $form.attr('action')

    $(".login-button").attr('disabled', true)

    await post({
        url: url,
        data: {
            username: $('#username').val().trim(),
            password: $('#password').val().trim()
        },
        success: function (response) {
            window.location.href = response.data.redirect
        },
        error: function (xhr) {
            const error = xhr.responseJSON
            console.log(error);
            showError(error)
        }
    })
    
    $(".login-button").attr('disabled', false)
})

function showError(error) {
    errorCard.removeClass('hidden')
    errorTitle.text(error.data.title ?? 'Something Went Wrong')
    errorMessage.text(error.data.message)
}