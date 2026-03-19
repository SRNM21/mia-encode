import { useAjax } from './hooks/use-ajax.js'
import { showLoading } from './utils/utils.js';

const { post } = useAjax()

const passwordToggle = $('.password-toggle')
const loginForm = $('#login-form')
const errorCard = $('.status-card')
const errorTitle = $('.status-title')
const errorMessage = $('.status-message')

passwordToggle.on('click', function () {
    const input = $('#password')
    const isPassword = input.attr('type') === 'password'

    input.attr('type', isPassword ? 'text' : 'password')

    $(this).find('.icon-eye').toggleClass('hidden')
    $(this).find('.icon-eye-slash').toggleClass('hidden')
})

loginForm.on('submit', async (e) => {
    e.preventDefault()

    showLoading($(".login-button"), true)

    try {
        const response = await post({
            url: $(this).attr('action'),
            data: {
                username: $('#username').val().trim(),
                password: $('#password').val().trim()
            }
        })
        
        window.location.href = response.data.redirect
    } catch (error) {
        showError(error.responseJSON.data)
    }
    
    showLoading($(".login-button"), false)
})

function showError(error) {
    console.log(error);
    
    errorTitle.text(error.title ?? 'Something Went Wrong')
    errorMessage.text(error.message)
    errorCard.removeClass('hidden')
}