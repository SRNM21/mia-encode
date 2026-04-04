import { useAjax } from './hooks/use-ajax.js'
import { normalizedServerErrors, showLoading, showNotification } from './utils/utils.js'
import { setErrorMessage, validateEditPasswordForm, validateEditProfileForm } from './utils/validation.js'

const { patch } = useAjax()

const profileEditBtn = $('.profile-edit-btn')
const profileSaveBtn = $('.profile-save-btn')
const profileCancelBtn = $('.profile-cancel-btn')

const securityEditBtn = $('.security-edit-btn')
const securitySaveBtn = $('.security-save-btn')
const securityCancelBtn = $('.security-cancel-btn')

const profileErrorCard = $('.profile-error-card')
const securityErrorCard = $('.security-error-card')

let IS_LOADING = false

async function withLoading(fn, onError) {
    if (IS_LOADING) return
    IS_LOADING = true

    try {
        await fn()
    } catch (error) {
        const response = error?.responseJSON?.data ?? error
        showNotification(
            response.title ?? 'Error Occured',
            response.message ?? 'An error occured when saving changes.',
            'error'
        )

        onError(response)
    } finally {
        IS_LOADING = false
    }
}

function applyTheme(theme) {
    $('html').removeClass('dark light');

    if (theme === 'system') {
        const isDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
        $('html').addClass(isDark ? 'dark' : 'light');
    } else {
        $('html').addClass(theme);
    }

    $('.themes-selection-container .card').removeClass('active');
    $(`.themes-selection-container .card[data-theme="${theme}"]`).addClass('active');
}

async function saveTheme(theme) {
    await withLoading(async () => { 
        const response = await patch({
            url: 'settings/theme',
            data: { theme: theme }
        });

        const result = response.data;
        showNotification(
            result.title,
            result.message,
        );
    }, (error) => {
        console.error('Failed to save theme:', error);
    });
}

async function saveProfile(section) {
    let success = false

    const username = section.find('#username')
    const email = section.find('#email')

    await withLoading(async () => { 
        showLoading(profileSaveBtn, true)

        const [data, errors] = validateEditProfileForm(profileErrorCard, {
            username: username,
            email: email,
        })

        if (errors.length > 0) {
            showLoading(profileSaveBtn, false)
            success = false
            return
        }

        const response = await patch({
            url: 'settings/profile',
            data: data
        })

        const result = response.data

        showLoading(profileSaveBtn, false)
        showNotification(
            result.title,
            result.message
        )

        success = true
    }, (error) => {
        console.log(error)
        setErrorMessage(profileErrorCard, normalizedServerErrors(error.errors))
        showLoading(profileSaveBtn, false)
        success = false
    })

    return success
}

async function savePassword(section) {  
    let success = false

    const currentPassword = section.find('#current-password')
    const newPassword = section.find('#new-password')
    const confirmPassword = section.find('#confirm-password')

    await withLoading(async () => { 
        showLoading(securitySaveBtn, true)

        const [data, errors] = validateEditPasswordForm(securityErrorCard, {
            currentPassword: currentPassword,
            newPassword: newPassword,
            confirmPassword: confirmPassword,
        })

        if (errors.length > 0) {
            showLoading(securitySaveBtn, false)
            success = false
            return
        }

        const response = await patch({
            url: 'settings/password',
            data: data
        })

        const result = response.data

        showLoading(securitySaveBtn, false)
        showNotification(
            result.title,
            result.message
        )

        success = true
    }, (error) => {
        console.log(error)
        showLoading(securitySaveBtn, false)
        success = false
    })
    
    return success
}

function toggleEditState($section, isEditing) {
    const isSecurity = $section.hasClass('security-section')
    const $editBtn = isSecurity ? securityEditBtn : profileEditBtn
    const $inputs = $section.find('input')

    $editBtn.toggleClass('hidden', isEditing)
    $section.find('.action-buttons').toggleClass('hidden', !isEditing)

    if (!isSecurity) {
        $inputs.attr('readonly', !isEditing)
        $inputs.attr('disabled', !isEditing)
    }

    if (isSecurity) {
        $section.find('.password-info').toggleClass('hidden', isEditing)
        $section.find('.profile-form-wrapper').toggleClass('hidden', !isEditing)
        
        if (!isEditing) {
            $inputs.val('')
            $inputs.removeClass('error')
        }
    }

    if (isEditing) {
        $inputs.first().focus()
    }
}

$(document).ready(function() {

    const $links = $('.settings-link')
    const $containers = $('.setting-container')

    $links.on('click', function(e) {
        e.preventDefault()

        const tab = $(this).data('tab').replace('#', '')
        window.location.hash = tab
    })

    function activateTab(tab, isInitial = false) {
        const $targetLink = $links.filter(`[data-tab="${tab}"]`)
        const $targetContainer = $containers.filter(`[data-tab="${tab}"]`)

        if (!$targetLink.length || !$targetContainer.length) return

        $links.removeClass('active')
        $targetLink.addClass('active')

        if (isInitial) {
            $containers.addClass('hide').removeClass('active').hide()
            $targetContainer.removeClass('hide').addClass('active').show()
            return
        }

        const $currentContainer = $containers.filter('.active')
        if ($currentContainer.is($targetContainer)) return

        $containers.not($targetContainer).hide().addClass('hide').removeClass('active')

        $targetContainer
            .removeClass('hide') 
            .stop(true, true) 
            .fadeIn(150)
            .addClass('active')
    }

    function handleHash(isInitial = false) {
        const hash = window.location.hash.replace('#', '')

        let tab = hash || 'account'

        activateTab(tab, isInitial)
    }

    handleHash(true)

    $(window).on('hashchange', function() {
        handleHash(false)
    })

    profileEditBtn.on('click', function() {
        toggleEditState($(this).closest('.section'), true)
    })

    profileCancelBtn.on('click', function() {
        if (typeof IS_LOADING !== 'undefined' && IS_LOADING) return
        toggleEditState($(this).closest('.section'), false)
        profileErrorCard.addClass('hidden')
    })

    profileSaveBtn.on('click', async function() {
        const $section = $(this).closest('.section')
        if (await saveProfile($section)) {
            toggleEditState($section, false)
            profileErrorCard.addClass('hidden')
        }
    })

    securityEditBtn.on('click', function() {
        toggleEditState($(this).closest('.security-section'), true)
    })

    securityCancelBtn.on('click', function() {
        if (typeof IS_LOADING !== 'undefined' && IS_LOADING) return
        toggleEditState($(this).closest('.security-section'), false)
        securityErrorCard.addClass('hidden')
    })

    securitySaveBtn.on('click', async function() {
        const $section = $(this).closest('.security-section')
        if (await savePassword($section)) {
            toggleEditState($section, false)
            securityErrorCard.addClass('hidden')
        }
    })

    $('.themes-selection-container .card').on('click', function() {
        const selectedTheme = $(this).data('theme');

        applyTheme(selectedTheme);
        saveTheme(selectedTheme);
    });
})