import { useAjax } from '../hooks/use-ajax.js'

const toggleBtn = $('#toggle-btn');
const sidebar = $('#sidebar');
const logoutBtn = $('#logout-btn');
const confirmLogoutBtn = $('.confirm-logout-btn')

const { post } = useAjax()

// -----------------
// Modal
// -----------------

export function closeModal(modalId) {
    const modal = $('#' + modalId);

    modal.removeClass('active').addClass('closing');

    setTimeout(() => {
        modal.removeClass('closing');
    }, 250);
}

export function openModal(modalId) {
    const modal = $('#' + modalId);
    modal.addClass('active');
}

$('.modal-close-btn, .modal-cancel-btn').on('click', (e) => {
    const modalId = $(e.currentTarget).data('modal');
    closeModal(modalId);
});

// -----------------
// Sidebar
// -----------------

toggleBtn.on('click', () => {
    sidebar.toggleClass('pinned');
    
    if (sidebar.hasClass('pinned')) {
        sidebar.removeClass('collapsed');
    } else {
        sidebar.addClass('collapsed');
    }
});

sidebar.on('mouseenter', () => {
    sidebar.removeClass('collapsed');
});

sidebar.on('mouseleave', () => {
    if (!sidebar.hasClass('pinned')) {
        sidebar.addClass('collapsed');
    }
});

logoutBtn.on('click', () => {
    openModal('logout-confirm-modal');
});

confirmLogoutBtn.on('click', async () => {
    showLoading(confirmLogoutBtn, true)
        
    try {
        const response = await post({
            url: 'logout',
        })
        
        console.log(response);
        
        closeModal('logout-confirm-modal');
        window.location.href = response.data.redirect;
    } catch (error) {
        console.log(error);
        showNotification(
            'Error Occured',
            'Error occured when logging out'
        )
    }
    
    showLoading(confirmLogoutBtn, false)
});

// -----------------
// Utilities
// -----------------

export function showLoading(button, show) {
    const toRemove = show ? 'p' : '.loader'
    const toShow = show ? '.loader' : 'p'
    
    button.find(toRemove).addClass('hidden')
    button.find(toShow).removeClass('hidden')
}

export function scrollToBottom(selector) {
    $(selector).scrollTop($(selector).height())
}

export function normalizedServerErrors(errors) {
    return Object.entries(errors).flatMap(([field, messages]) =>
        messages.map(message => ({
            field,
            message
        }))
    )
}

export function setErrorState(input, isError) {
    if (isError) {
        input.addClass('error')
    } else {
        input.removeClass('error')
    }
}

export function href(url) {
    window.location.href = url
}

export function getSearchParams(params) {
    const url = new URL(window.location.href)
    return params ? url.searchParams.get(params) : url.searchParams.getAll()
}

export function formatDate(dateString) {
    const date = new Date(dateString.replace(' ', 'T'))

    const formatted = date.toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
    })

    return formatted
}

export function formatDateTime(dateString) {    
    const date = new Date(dateString.replace(' ', 'T'))

    const formatted = date.toLocaleString('en-US', {
        month: 'short',
        day: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true
    })

    return formatted
}

export function getCurrentDateTime() {
    const now = new Date()

    const pad = (n) => String(n).padStart(2, '0')

    return (
        now.getFullYear() + '-' +
        pad(now.getMonth() + 1) + '-' +
        pad(now.getDate()) + ' ' +
        pad(now.getHours()) + ':' +
        pad(now.getMinutes()) + ':' +
        pad(now.getSeconds())
    )
}

export function capitalizeWord(word) {
  return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase();
}

export function showNotification(title, message, type = 'success') {

    const DURATION = 3000

    // create container if missing
    let container = $('#notification-container')

    if (!container.length) {
        $('body').append('<div id="notification-container"></div>')
        container = $('#notification-container')
    }

    const notification = $(`
        <div class="notification">
            <div class="notification-progress ${type}"></div>

            <div class="notification-header">
                <div class="notification-title"></div>
                <button class="notification-close">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x-icon lucide-x"><path d="M18 6 6 18"/><path d="m6 6 12 12"/></svg>
                </button>
            </div>

            <div class="notification-message"></div>
        </div>
    `)

    notification.find('.notification-title').text(title)
    notification.find('.notification-message').text(message)

    // prepare animation state
    notification.css({
        height: 0,
        opacity: 0,
        overflow: 'hidden'
    })

    container.append(notification)

    // measure natural height
    const fullHeight = notification.get(0).scrollHeight

    // slide animation
    notification.animate(
        {
            height: fullHeight,
            opacity: 1
        },
        200,
        function () {
            notification.css({
                height: 'auto',
                overflow: ''
            })
        }
    )

    const progress = notification.find('.notification-progress')

    // progress countdown
    progress.animate(
        { width: "0%" },
        {
            duration: DURATION,
            easing: "linear"
        }
    )

    // auto remove
    const timeout = setTimeout(removeNotification, DURATION)

    function removeNotification() {

        clearTimeout(timeout)

        notification.animate(
            {
                height: 0,
                opacity: 0
            },
            200,
            function () {
                notification.remove()
            }
        )
    }

    // close button
    notification.find('.notification-close').on('click', removeNotification)
}

export function debounce(fn, delay = 500) {
    let timer = null

    return function (...args) {
        const context = this

        clearTimeout(timer)

        timer = setTimeout(() => {
            fn.apply(context, args)
        }, delay)
    }
}
