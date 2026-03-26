import { href } from "./utils/utils.js"

const leaderboardsFilter = $('#leaderboards-filter')
const bankTypeLeaderboardsFilter = $('#bank-type-leaderboard-filter')

leaderboardsFilter.on('change', function () {
    const filter = leaderboardsFilter.val() || 'today'

    const url = new URL(window.location.href)
    url.searchParams.set('filter-submission', String(filter))
    href(url.toString())
})

bankTypeLeaderboardsFilter.on('change', function () {
    const filter = bankTypeLeaderboardsFilter.val() || 'today'

    const url = new URL(window.location.href)
    url.searchParams.set('filter-banks', String(filter))
    href(url.toString())
})

$(document).ready(function() {
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

    $(window).on('hashchange', function() {
        handleHash(false);
    });

    $tabs.on('click', function(e) {
        const $clickedTab = $(this);
        if ($clickedTab.hasClass('active')) {
            e.preventDefault();
        }
    });

    $(window).on('resize', function() {
        setActiveTab($('.tabs.active'), true);
    });
});