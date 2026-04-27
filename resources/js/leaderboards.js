import { href, showLoading, openModal, closeModal } from "./utils/utils.js"
import { validateExportDateForm } from './utils/validation.js'
const leaderboardsFilter = $('#leaderboards-filter')
const bankTypeLeaderboardsFilter = $('#bank-type-leaderboard-filter')

let activeCustomFilter = null;
const startDateInput = $('#leaderboard-start-date');
const endDateInput = $('#leaderboard-end-date');

startDateInput.datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: "-10:+0",
    dateFormat: 'mm/dd/yy'
});

endDateInput.datepicker({
    changeMonth: true,
    changeYear: true,
    yearRange: "-100:+0",
    dateFormat: 'mm/dd/yy'
});

const searchAgentInput = $('#search-agent');
const searchAgentBtn = $('#search-agent-button');
const clearAgentBtn = $('#clear-agent-button');

let IS_LOADING = false;

function executeSearch(btn) {
    if (IS_LOADING) return;

    IS_LOADING = true;
    showLoading(btn, true);

    const url = new URL(window.location.href);
    const search = searchAgentInput.val().trim();
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    href(url.toString());
}

searchAgentBtn.on('click', function () {
    const search = searchAgentInput.val().trim();
    if (!search) return;

    const url = new URL(window.location.href);
    if (url.searchParams.get('search') === search) return;

    executeSearch(searchAgentBtn);
});

searchAgentInput.on('keypress', function (e) {
    const search = searchAgentInput.val().trim();

    if (e.which === 13 && search) {
        executeSearch(searchAgentBtn);
    }
});

clearAgentBtn.on('click', function () {
    searchAgentInput.val('');

    const url = new URL(window.location.href);
    if (!url.searchParams.get('search')) return;

    executeSearch(clearAgentBtn);
});

$(document).on('click', '.edit-custom-date', function () {
    activeCustomFilter = $(this).data('filter');

    const url = new URL(window.location.href);
    if (url.searchParams.has('custom_from')) startDateInput.val(url.searchParams.get('custom_from'));
    if (url.searchParams.has('custom_to')) endDateInput.val(url.searchParams.get('custom_to'));

    openModal('select-date-leaderboards-modal');
});

leaderboardsFilter.on('change', function () {
    const filter = leaderboardsFilter.val() || 'today'

    if (filter === 'custom') {
        activeCustomFilter = 'filter-submission';
        openModal('select-date-leaderboards-modal');
        return;
    }

    const url = new URL(window.location.href)
    url.searchParams.set('filter-submission', String(filter))
    url.searchParams.delete('custom_from')
    url.searchParams.delete('custom_to')
    href(url.toString())
})

bankTypeLeaderboardsFilter.on('change', function () {
    const filter = bankTypeLeaderboardsFilter.val() || 'today'

    if (filter === 'custom') {
        activeCustomFilter = 'filter-banks';
        openModal('select-date-leaderboards-modal');
        return;
    }

    const url = new URL(window.location.href)
    url.searchParams.set('filter-banks', String(filter))
    url.searchParams.delete('custom_from')
    url.searchParams.delete('custom_to')
    href(url.toString())
})

$('#confirm-leaderboard-date-btn').on('click', function () {
    const errorCard = $('.range-date-error-card');

    const [data, errors] = validateExportDateForm(errorCard, {
        start_date: startDateInput,
        end_date: endDateInput,
    });

    if (errors.length > 0) return;

    const start = startDateInput.val();
    const end = endDateInput.val();

    const url = new URL(window.location.href);
    if (activeCustomFilter) {
        url.searchParams.set(activeCustomFilter, 'custom');
    }
    url.searchParams.set('custom_from', start);
    url.searchParams.set('custom_to', end);

    showLoading($(this), true);
    href(url.toString());
});

function revertCustomFilter() {
    const url = new URL(window.location.href);
    const prevSub = url.searchParams.get('filter-submission') || 'today';
    const prevBank = url.searchParams.get('filter-banks') || 'today';

    leaderboardsFilter.val(prevSub);
    bankTypeLeaderboardsFilter.val(prevBank);
}

$('#cancel-leaderboard-date-btn, #select-date-leaderboards-modal .modal-close-btn').on('click', function () {
    revertCustomFilter();
    closeModal('select-date-leaderboards-modal');
});

$(document).ready(function () {
    const $tabs = $('.tabs');
    const $indicator = $('.tab-indicator');
    const $tabContents = $('.tab-content .card');

    function setActiveTab($clickedTab, isInitial = false) {
        if (!$clickedTab.length) return;

        $tabs.removeClass('active');
        $clickedTab.addClass('active');

        const tabWidth = $clickedTab.outerWidth();
        const tabLeftPosition = $clickedTab.position().left;

        if (isInitial) {
            $indicator.css('transition', 'none');

            $indicator.css({
                'width': tabWidth + 'px',
                'left': tabLeftPosition + 'px'
            });

            $indicator[0].offsetHeight;

            $indicator.css('transition', '');
        } else {
            $indicator.css({
                'width': tabWidth + 'px',
                'left': tabLeftPosition + 'px'
            });
        }

        const targetClass = $clickedTab.data('tab');

        if (isInitial) {
            $tabContents.hide();
            $('.' + targetClass).show();
        } else {
            $tabContents.hide();
            $('.' + targetClass).fadeIn(200);
        }
    }

    function handleHash(isInitial = false) {
        const hash = window.location.hash;
        let $targetTab = $tabs.filter(`[href="${hash}"]`);

        if ($targetTab.length === 0) {
            $targetTab = $tabs.first();
        }

        setActiveTab($targetTab, isInitial);
    }

    handleHash(true);

    $(window).on('hashchange', function () {
        handleHash(false);
    });

    $tabs.on('click', function (e) {
        const $clickedTab = $(this);
        if ($clickedTab.hasClass('active')) {
            e.preventDefault();
        }
    });

    $(window).on('resize', function () {
        setActiveTab($('.tabs.active'), true);
    });
});