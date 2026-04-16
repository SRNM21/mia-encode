import { useAjax } from '../hooks/use-ajax.js'

const toggleBtn = $('#toggle-btn')
const sidebar = $('#sidebar')
const logoutBtn = $('#logout-btn')
const confirmLogoutBtn = $('.confirm-logout-btn')

const { post } = useAjax()

// -----------------
// Modal
// -----------------

export function closeModal(modalId) {
    const modal = $('#' + modalId)

    modal.removeClass('active').addClass('closing')

    setTimeout(() => {
        modal.removeClass('closing')
    }, 250)
}

export function openModal(modalId) {
    const modal = $('#' + modalId)
    modal.addClass('active')
}

$('.modal-close-btn, .modal-cancel-btn').on('click', (e) => {
    const modalId = $(e.currentTarget).data('modal')
    closeModal(modalId)
})

// -----------------
// Sidebar
// -----------------

toggleBtn.on('click', () => {
    if (sidebar.hasClass('collapsed')) {
        sidebar.removeClass('collapsed')
    } else {
        sidebar.addClass('collapsed')
    }
})

logoutBtn.on('click', () => {
    openModal('logout-confirm-modal')
})

confirmLogoutBtn.on('click', async () => {
    showLoading(confirmLogoutBtn, true)
        
    try {
        const response = await post({
            url: 'logout',
        })
        
        closeModal('logout-confirm-modal')
        window.location.href = response.data.redirect
    } catch (error) {
        console.log(error)
        showNotification(
            'Error Occured',
            'Error occured when logging out'
        )
    }
    
    showLoading(confirmLogoutBtn, false)
})

let wasOnline = navigator.onLine;
let lastSpeedType = null;
let currentPingMs = null;
let pingHistory = [];
const PING_HISTORY_LIMIT = 5;

const wifiIcons = {
    'wifi-zero': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-zero-icon lucide-wifi-zero"><path d="M12 20h.01"/></svg>',
    'wifi-low': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-low-icon lucide-wifi-low"><path d="M12 20h.01"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/></svg>',
    'wifi-high': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-high-icon lucide-wifi-high"><path d="M12 20h.01"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/></svg>',
    'wifi': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-icon lucide-wifi"><path d="M12 20h.01"/><path d="M2 8.82a15 15 0 0 1 20 0"/><path d="M5 12.859a10 10 0 0 1 14 0"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/></svg>',
    'wifi-off': '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-wifi-off-icon lucide-wifi-off"><path d="M12 20h.01"/><path d="M8.5 16.429a5 5 0 0 1 7 0"/><path d="M5 12.859a10 10 0 0 1 5.17-2.69"/><path d="M19 12.859a10 10 0 0 0-2.007-1.523"/><path d="M2 8.82a15 15 0 0 1 4.177-2.643"/><path d="M22 8.82a15 15 0 0 0-11.288-3.764"/><path d="m2 2 20 20"/></svg>'
}

function updateWifiIcon(iconKey, title, color = 'var(--foreground)', ping = null) {
    const indicator = $('#wifi-indicator');
    if (indicator.length) {
        let content = wifiIcons[iconKey];
        if (ping !== null) {
            content += `<span style="font-size: 12px; font-weight: 600; margin-left: 6px;">${ping}ms</span>`;
            indicator.css({ width: 'auto' });
        } else {
            indicator.css({ width: '' });
        }
        indicator.html(content);
        indicator.attr('title', title);
        
        indicator.css('color', color);
    }
}

async function fetchWithTimeout(url, options = {}, timeout = 3000) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);
    try {
        const response = await fetch(url, { ...options, signal: controller.signal });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        throw error;
    }
}

async function checkActualInternetConnection() {
    try {
        const start = performance.now();
        await fetchWithTimeout('https://www.google.com/favicon.ico?_=' + new Date().getTime(), {
            method: 'GET',
            mode: 'no-cors',
            cache: 'no-store'
        }, 3000)
        currentPingMs = Math.round(performance.now() - start);
        return true
    } catch (e1) {
        try {
            const start = performance.now();
            await fetchWithTimeout('https://github.githubassets.com/favicon.ico?_=' + new Date().getTime(), {
                method: 'GET',
                mode: 'no-cors',
                cache: 'no-store'
            }, 3000)
            currentPingMs = Math.round(performance.now() - start);
            return true;
        } catch (e2) {
            currentPingMs = null;
            return false
        }
    }
}

async function updateConnectionStatus(forceCheck = false) {
    if (!navigator.onLine) {
        handleOfflineState();
        return;
    }

    const isActuallyOnline = await checkActualInternetConnection();

    if (isActuallyOnline) {
        handleOnlineState();
    } else {
        handleOfflineState();
    }
}

function handleOnlineState() {
    if (!wasOnline) {
        showNotification('Connection Restored', 'You are back online.', 'success');
        wasOnline = true;
    }
    checkNetworkSpeed(); 
}

function handleOfflineState() {
    if (wasOnline) {
        showNotification('Connection Lost', 'You are currently offline.', 'error');
        wasOnline = false;
        currentPingMs = null;
        pingHistory = [];
    }
    updateWifiIcon('wifi-off', 'Offline', 'var(--color-error)');
}

function checkNetworkSpeed() {
    if (!wasOnline) return;

    let connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
    let type = connection ? connection.effectiveType : null;
    
    let iconKey = 'wifi';
    let title = 'Connected';
    let color = 'var(--foreground)';
    
    if (currentPingMs !== null) {
        pingHistory.push(currentPingMs);
        if (pingHistory.length > PING_HISTORY_LIMIT) {
            pingHistory.shift();
        }
    }

    let isUnstable = false;
    if (pingHistory.length >= 3) {
        let minPing = Math.min(...pingHistory);
        let maxPing = Math.max(...pingHistory);
        if (maxPing - minPing > 400) {
            isUnstable = true;
        }
    }

    if (currentPingMs !== null) {
        title = `Ping: ${currentPingMs}ms`;
        
        if (isUnstable) {
            iconKey = 'wifi-low';
            color = 'var(--color-danger)';
            title += ' (Unstable)';
        } else if (currentPingMs < 300) {
            iconKey = 'wifi';
            color = 'var(--color-success)';
        } else if (currentPingMs < 800) {
            iconKey = 'wifi-low';
            color = 'var(--color-danger)';
        } else {
            iconKey = 'wifi-zero';
            color = 'var(--color-error)';
        }
    } 
    else if (type) {
        if (type === 'slow-2g') {
            iconKey = 'wifi-zero';
            color = 'var(--color-error)';
            title = 'Slow Connection (' + type + ')';
        } else if (type === '2g') {
            iconKey = 'wifi-low';
            color = 'var(--color-error)';
            title = 'Slow Connection (' + type + ')';
        } else if (type === '3g') {
            iconKey = 'wifi-high';
            color = 'var(--color-danger)';
            title = 'Good Connection (' + type + ')';
        } else {
            iconKey = 'wifi';
            color = 'var(--color-success)';
            title = 'Fast Connection (' + type + ')';
        }
    }

    if (connection && type) {
        if (type !== lastSpeedType) {
            if (type === 'slow-2g' || type === '2g' || type === '3g') {
                showNotification('Slow Internet Connection', 'Your internet connection is slow (' + type + ').', 'warning');
            }
            lastSpeedType = type;
        }
    }

    updateWifiIcon(iconKey, title, color, currentPingMs);
}

window.addEventListener('online', () => updateConnectionStatus(true));
window.addEventListener('offline', handleOfflineState);

setInterval(() => updateConnectionStatus(), 5000);

let connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;
if (connection) {
    connection.addEventListener('change', checkNetworkSpeed);
}

updateConnectionStatus();

// -----------------
// Navigation
// -----------------

export function handleNavigationLoader(e, element) {
    const el = $(element)
    const href = el.attr('href') || el.data('href')
    const target = el.attr('target')
    
    if (href && (href.startsWith('#') || href.startsWith('javascript:'))) return
    if (e && (e.ctrlKey || e.metaKey || e.shiftKey)) return
    if (target === '_blank' || el.attr('download') !== undefined || el.hasClass('no-loader')) return

    openModal('page-loader-modal')
}

$(document).on('click', 'a', function(e) {
    handleNavigationLoader(e, this)
})

// -----------------
// Utilities
// -----------------

export function showLoading(button, show) {
    const toRemove = show ? 'div' : '.loader'
    const toShow = show ? '.loader' : 'div'
    
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
  return word.charAt(0).toUpperCase() + word.slice(1).toLowerCase()
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
