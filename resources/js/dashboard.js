import { fetchClientTypesToday, fetchBankAppsToday, fetchClientTypeSeries, fetchBankAppsSeries, fetchAgentLeaderboards } from './services/dashboard-api.js'
import { renderClientTodayChart } from './charts/client-charts.js'
import { showLoading, showContent, showEmpty } from './ui/empty-state.js'
import { bindSelect, populateSelect } from './ui/select-utils.js'
import { normalizeBankSeries, renderBankSeries, renderBankTodayChart } from './charts/bank-charts.js'
import { renderClientSeries } from './charts/client-charts.js'
import { renderLeaderboards } from './components/leaderboards.js'
import { useAjax } from './hooks/use-ajax.js'
import { formatDate } from './utils/utils.js'

const { post } = useAjax()
const doc = $(document)

const clientTodayCanvas = $('#clients-type-chart')
const banksTodayCanvas = $('#bank-apps-type-chart')

const clientSeriesCanvas = $('#clients-chart-wrapper')
const clientSeriesTable = $('#clients-table-wrapper')

const bankSeriesCanvas = $('#bank-applications-type-line')
const bankSeriesTable = $('#banks-table-wrapper')

const leaderboardsContent = $('.leaderboards-table-content')
const bankCalendarTable = $('#bank-calendar-table')

const emptyClientsToday = $('.empty-clients-today')
const emptyBanksToday = $('.empty-banks-today')
const emptyClientsSeries = $('.empty-clients-series')
const emptyBanksSeries = $('.empty-banks-series')
const emptyLeaderboards = $('.empty-leaderboards')
const emptyBankCalendar = $('.empty-bank-calendar')

const clientsYearSelect = $('#clients-year-select')
const clientsRangeSelect = $('#clients-series-select')

const banksYearSelect = $('#bank-apps-year-select')
const banksRangeSelect = $('#bank-apps-series-select')

const bankCalendarRangeDisplay = $('#bank-calendar-range-display')
const bankCalendarWeekFilter = $("#bank-calendar-week-select")
const bankCalendarMonthFilter = $("#bank-calendar-month-select")
const bankCalendarYearFilter = $("#bank-calendar-year-select")

let clientSeriesCache = {}
let bankSeriesCache = {}

let currentClientScope = 'monthly'
let currentBankScope = 'monthly'

doc.ready(initDashboard)

function initDashboard() {
    // For today
    loadClientToday()
    loadBankToday()

    // Client Series
    bindSelect(clientsRangeSelect, handleClientScope)
    bindSelect(clientsYearSelect, loadClientSeries)
    loadClientSeries()

    // Banks Series
    bindSelect(banksRangeSelect, handleBankScope)
    bindSelect(banksYearSelect, loadBankSeries)
    loadBankSeries()

    // Leaderboards
    bindSelect($('#agent-leaderboards-select'), loadLeaderboards)
    loadLeaderboards()

    // Weekly bank table
    loadWeeklyBankTable()
}

// Filters
function handleClientScope() {
    currentClientScope = clientsRangeSelect.val()
    clientsYearSelect.attr('disabled', currentClientScope === 'yearly')
    loadClientSeries()
}

function handleBankScope() {
    currentBankScope = banksRangeSelect.val()
    banksYearSelect.attr('disabled', currentBankScope === 'yearly')
    loadBankSeries()
}

function renderClientSeriesFromCache() {
    const items = clientSeriesCache[currentClientScope] || []

    if (!items.length) {
        showEmpty(emptyClientsSeries,'No clients found.')
        return
    }

    const labels = items.map(i => i.label)

    const newDataset = {
        label:'New Client',
        data: items.map(i => i.new),
        borderColor:'rgb(75,192,192)',
        backgroundColor:'rgba(75,192,192,0.2)',
        tension:0.3
    }

    const oldDataset = {
        label:'Old Client',
        data: items.map(i => i.old),
        borderColor:'rgb(255,99,132)',
        backgroundColor:'rgba(255,99,132,0.2)',
        tension:0.3
    }

    renderClientSeries(
        clientSeriesCanvas.find('canvas'),
        labels,
        [newDataset, oldDataset]
    )
}

function renderBankSeriesFromCache() {
    const series = bankSeriesCache[currentBankScope] || {}

    if (!series.labels?.length) {
        showEmpty(emptyBanksSeries,'No banks found.')
        return
    }

    renderBankSeries(
        bankSeriesCanvas,
        series.labels,
        series.datasets
    )
}

function renderBankTableFromCache() {
    const $tableHead = $('#banks-table-head');
    const $tableBody = $('#banks-table-body');
    const series = bankSeriesCache[currentBankScope] || {};

    $tableHead.empty();
    $tableBody.empty();

    if (!series.labels?.length) {
        $tableBody.append('<tr><td class="text-center p-15">No bank applications found.</td></tr>');
        return;
    }

    let headRow = '<tr><th>Bank</th>';
    
    series.labels.forEach(label => {
        headRow += `<th>${label}</th>`;
    });
    headRow += '<th>Total</th></tr>'; 
    $tableHead.append(headRow);

    let periodTotals = new Array(series.labels.length).fill(0);
    let grandTotal = 0;

    series.datasets.forEach(dataset => {
        const $row = $('<tr>');
        $row.append($('<td>').text(dataset.label));

        let bankTotal = 0;

        series.labels.forEach((_, index) => {
            const val = dataset.data[index] || 0;
            
            bankTotal += val;
            periodTotals[index] += val;

            $row.append($('<td>').addClass(val === 0 ? 'zero' : '').text(val));
        });

        grandTotal += bankTotal;

        $row.append($('<td>').addClass(bankTotal === 0 ? 'zero' : '').html(`<strong>${bankTotal}</strong>`));
        
        $tableBody.append($row);
    });

    const $totalRow = $('<tr>').append($('<td>').html('<strong>Total</strong>'));
    
    periodTotals.forEach(total => {
        $totalRow.append($('<td>').addClass(total === 0 ? 'zero' : '').html(`<strong>${total}</strong>`));
    });
    
    $totalRow.append($('<td>').addClass(grandTotal === 0 ? 'zero' : '').html(`<strong>${grandTotal}</strong>`));

    $tableBody.append($totalRow);
}

// Clients charts
async function loadClientToday() {
    showLoading(emptyClientsToday, clientTodayCanvas)

    try {
        const response = await fetchClientTypesToday()

        const data = response.data || {}

        if (!data || (!data.new && !data.old)) {
            showEmpty(emptyClientsToday, 'No clients today.')
            return
        }

        showContent(emptyClientsToday, clientTodayCanvas)

        renderClientTodayChart(
            clientTodayCanvas,
            data.new,
            data.old
        )
    } catch (error) {
        showEmpty(emptyClientsToday, 'Error Occured.')
        console.error(error)
    }
}

async function loadClientSeries() {
    showLoading(emptyClientsSeries, clientSeriesCanvas)
    showLoading(emptyClientsSeries, clientSeriesTable)

    try {
        const response = await fetchClientTypeSeries({
            scope: currentClientScope,
            year: clientsYearSelect.val()
        })

        const data = response.data || {}
        console.log(data);
        

        populateSelect(
            clientsYearSelect,
            data.years || [],
            data.selected_year
        )

        clientSeriesCache = data.series || {}

        const items = clientSeriesCache[currentClientScope] || []

        if (!items.length) {
            showEmpty(emptyClientsSeries,'No clients found.')
            return
        }

        showContent(emptyClientsSeries, clientSeriesCanvas)
        showContent(emptyClientsSeries, clientSeriesTable)

        renderClientSeriesFromCache()
        renderClientTableFromCache()
    } catch (error) {
        showEmpty(emptyClientsSeries, 'Error Occured.')
        console.error(error)
    }
}

async function loadBankToday() {
    showLoading(emptyBanksToday, banksTodayCanvas)

    try {
        const response = await fetchBankAppsToday()

        const payload = response.data || {}

        if (!payload.labels?.length) {
            showEmpty(emptyBanksToday, 'No bank applications today.')
            return
        }

        showContent(emptyBanksToday, banksTodayCanvas)

        renderBankTodayChart(
            banksTodayCanvas,
            payload.labels,
            payload.counts
        )
    } catch (error) {
        showEmpty(emptyBanksToday, 'Error Occured.')
        console.error(error)
    }
}

async function loadBankSeries() {
    showLoading(emptyBanksSeries, bankSeriesCanvas)
    showLoading(emptyBanksSeries, bankSeriesTable)

    try {
        const response = await fetchBankAppsSeries({
            scope: currentBankScope,
            year: banksYearSelect.val()
        })

        const data = response.data || {}

        populateSelect(
            banksYearSelect,
            data.years || [],
            data.selected_year
        )

        bankSeriesCache = normalizeBankSeries(data.series || {})

        const series = bankSeriesCache[currentBankScope] || {}

        if (!series.labels?.length) {
            showEmpty(emptyBanksSeries,'No banks found.')
            return
        }

        showContent(emptyBanksSeries, bankSeriesCanvas)
        showContent(emptyBanksSeries, bankSeriesTable)

        renderBankSeriesFromCache()
        renderBankTableFromCache()
    } catch (error) {
        showEmpty(emptyBanksToday, 'Error Occured.')
        console.error(error)
    }
}

async function loadLeaderboards() {
    showLoading(emptyLeaderboards, leaderboardsContent)

    const scope = $('#agent-leaderboards-select').val() || 'today'

    try {
        const response = await fetchAgentLeaderboards({scope})

        const rows = response.data || []

        if (!rows.length) {
            showEmpty(emptyLeaderboards,'No submissions as of now.')
            return
        }

        showContent(emptyLeaderboards, leaderboardsContent)

        renderLeaderboards(
            leaderboardsContent,
            rows
        )
    } catch (error) {
        showEmpty(emptyBanksToday, 'Error Occured.')
        console.error(error)
    }
}

const state = {
    year: null,
    month: null,
    selectedWeek: 0
};

let weekMap = new Map(); 

async function loadWeeklyBankTable() {
    
    showLoading(emptyBankCalendar, bankCalendarTable)

    try {
        const now = new Date();

        const response = await post({
            url: 'dashboard/chart/weekly-bank-table',
            data: {
                year: state.year ?? now.getFullYear(),
                month: state.month ?? now.getMonth() + 1
            }
        });

        const data = response.data || {};
        const weeks = data.weeks || [];

        hydrateYears(data.years || []);
        hydrateMonths()
        hydrateWeeks(weeks);

        if (isMonthEmpty(weeks)) {
            showEmpty(emptyBankCalendar, 'No submissions for this month.');
            bankCalendarRangeDisplay.text('Banks Calendar');
            bankCalendarWeekFilter.addClass('hidden')
            return;
        }

        bankCalendarWeekFilter.removeClass('hidden')
        showContent(emptyBankCalendar, bankCalendarTable)

        weekMap.clear();
        (data.weeks || []).forEach(w => {
            weekMap.set(w.week, w);
        });

        if (!weekMap.has(state.selectedWeek)) {
            state.selectedWeek = [...weekMap.keys()][0] ?? 0;
        }

        updateWeekUI();
        renderWeek(state.selectedWeek);

    } catch (error) {
        console.error(error);
        showEmpty(emptyBankCalendar, 'Error Occured.');
    }
}

function hydrateYears(years) {
    bankCalendarYearFilter.empty();

    years.forEach(y => {
        bankCalendarYearFilter.append(`<option value="${y.year}">${y.year}</option>`);
    });

    const latestYear = years.at(-1)?.year;

    if (state.year === null) {
        state.year = latestYear;
    }

    bankCalendarYearFilter.val(state.year);
}

function hydrateMonths() {
    const months = [
        'January','February','March','April','May','June',
        'July','August','September','October','November','December'
    ];

    const currentMonth = new Date().getMonth() + 1;

    bankCalendarMonthFilter.empty();

    $.each(months, function (index, name) {
        const value = index + 1;

        bankCalendarMonthFilter.append(`<option value="${value}">${name}</option>`);
    });

    if (state.month === null) {
        state.month = currentMonth;
    }

    bankCalendarMonthFilter.val(state.month);
}

function hydrateWeeks(weeks) {
    bankCalendarWeekFilter.empty();

    weeks.forEach(w => {
        bankCalendarWeekFilter.append(
            `<option value="${w.week}">Week ${w.week + 1}</option>`
        );
    });

}

function renderWeek(weekIndex) {
    if (!weekMap.has(weekIndex)) return;

    const weekData = weekMap.get(weekIndex);

    bankCalendarRangeDisplay.text(
        `${formatDate(weekData.range.start)} — ${formatDate(weekData.range.end)}`
    );

    renderTable(weekData.data, weekData.range);
}

bankCalendarWeekFilter.on('change', function () {
    state.selectedWeek = parseInt($(this).val());
    renderWeek(state.selectedWeek);
});

bankCalendarMonthFilter.on('change', async function () {
    state.month = parseInt($(this).val());
    state.selectedWeek = 0; 
    await loadWeeklyBankTable();
});

bankCalendarYearFilter.on('change', async function () {
    state.year = parseInt($(this).val());
    state.selectedWeek = 0; 
    await loadWeeklyBankTable();
});

function updateWeekUI() {
    bankCalendarWeekFilter.val(state.selectedWeek)
    renderWeek(state.selectedWeek);
}

function renderTable(data, range) {
    const $thead = bankCalendarTable.find('thead');
    const $tbody = bankCalendarTable.find('tbody');
    
    $thead.empty();
    $tbody.empty();

    function parseDateString(dateStr) {
        const [year, month, day] = dateStr.split('-');
        return new Date(year, month - 1, day);
    }

    const startDate = parseDateString(range.start);
    const endDate = parseDateString(range.end);

    const startDayOfWeek = startDate.getDay() || 7; 
    const mondayDate = new Date(startDate);
    mondayDate.setDate(startDate.getDate() - (startDayOfWeek - 1));

    const weekDates = {};
    const days = ['mon', 'tue', 'wed', 'thu', 'fri'];
    
    days.forEach((day, index) => {
        const date = new Date(mondayDate);
        date.setDate(mondayDate.getDate() + index);
        weekDates[day] = date;
    });

    const startTimestamp = startDate.getTime();
    const endTimestamp = endDate.getTime();
    const isActive = {};

    days.forEach(day => {
        const dayTime = weekDates[day].getTime();
        isActive[day] = dayTime >= startTimestamp && dayTime <= endTimestamp;
    });

    $thead.append(`
        <tr>
            <th>Bank</th>
            <th>Mon (${weekDates.mon.getDate()})</th>
            <th>Tue (${weekDates.tue.getDate()})</th>
            <th>Wed (${weekDates.wed.getDate()})</th>
            <th>Thu (${weekDates.thu.getDate()})</th>
            <th>Fri (${weekDates.fri.getDate()})</th>
            <th>Total</th>
        </tr>
    `);

    let totals = { mon: 0, tue: 0, wed: 0, thu: 0, fri: 0, total: 0 };

    data.forEach(row => {
        totals.mon += Number(row.mon) || 0;
        totals.tue += Number(row.tue) || 0;
        totals.wed += Number(row.wed) || 0;
        totals.thu += Number(row.thu) || 0;
        totals.fri += Number(row.fri) || 0;
        totals.total += Number(row.total) || 0;

        $tbody.append(`
            <tr>
                <td>${row.bank}</td>
                <td class="bank-calendar-td ${!isActive.mon ? 'disabled' : ''} ${row.mon <= 0 ? 'zero' : ''}">${row.mon}</td>
                <td class="bank-calendar-td ${!isActive.tue ? 'disabled' : ''} ${row.tue <= 0 ? 'zero' : ''}">${row.tue}</td>
                <td class="bank-calendar-td ${!isActive.wed ? 'disabled' : ''} ${row.wed <= 0 ? 'zero' : ''}">${row.wed}</td>
                <td class="bank-calendar-td ${!isActive.thu ? 'disabled' : ''} ${row.thu <= 0 ? 'zero' : ''}">${row.thu}</td>
                <td class="bank-calendar-td ${!isActive.fri ? 'disabled' : ''} ${row.fri <= 0 ? 'zero' : ''}">${row.fri}</td>
                <td class="bank-calendar-td ${row.total <= 0 ? 'zero' : ''}"><strong>${row.total}</strong></td>
            </tr>
        `);
    });

    $tbody.append(`
        <tr>
            <td><strong>Total</strong></td>
            <td class="bank-calendar-td ${!isActive.mon ? 'disabled' : ''} ${totals.mon <= 0 ? 'zero' : ''}">${totals.mon}</td>
            <td class="bank-calendar-td ${!isActive.tue ? 'disabled' : ''} ${totals.tue <= 0 ? 'zero' : ''}">${totals.tue}</td>
            <td class="bank-calendar-td ${!isActive.wed ? 'disabled' : ''} ${totals.wed <= 0 ? 'zero' : ''}">${totals.wed}</td>
            <td class="bank-calendar-td ${!isActive.thu ? 'disabled' : ''} ${totals.thu <= 0 ? 'zero' : ''}">${totals.thu}</td>
            <td class="bank-calendar-td ${!isActive.fri ? 'disabled' : ''} ${totals.fri <= 0 ? 'zero' : ''}">${totals.fri}</td>
            <td class="bank-calendar-td ${totals.total <= 0 ? 'zero' : ''}">${totals.total}</td>
        </tr>
    `);
}

function isMonthEmpty(week) {
    let total = 0

    week.forEach(w => w.data.forEach(d => total += d.total))
    
    return total <= 0
}

$(document).ready(function() {
    const $tabGroups = $('.title-tabs-group');

    function setChartTabActive($clickedTab, $container, isInitial = false) {
        if (!$clickedTab.length) return;

        const $tabs = $container.find('.tabs');
        const $indicator = $container.find('.tab-indicator');

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

        const targetView = $clickedTab.data('target');
        const $targetEl = $('#' + targetView);
        
        const $siblings = $targetEl.siblings('.clients-chart-wrapper, .clients-table-wrapper');

        if (isInitial) {
            $siblings.hide();
            $targetEl.show();
        } else {
            $siblings.hide();
            $targetEl.fadeIn(200);
        }
    }

    $tabGroups.each(function() {
        const $group = $(this);
        const $activeTab = $group.find('.tabs.active');
        setChartTabActive($activeTab, $group, true);
    });

    $('.title-tabs-group').on('click', '.tabs', function(e) {
        e.preventDefault();
        const $clickedTab = $(this);
        
        if ($clickedTab.hasClass('active')) return; 
        
        const $container = $clickedTab.closest('.title-tabs-group');
        setChartTabActive($clickedTab, $container, false);
    });

    $(window).on('resize', function() {
        $('.title-tabs-group').each(function() {
            const $group = $(this);
            const $activeTab = $group.find('.tabs.active');
            setChartTabActive($activeTab, $group, true);
        });
    });
});

function renderClientTableFromCache() {
    const $tableHead = $('#clients-table-head');
    const $tableBody = $('#clients-table-body');
    const items = clientSeriesCache[currentClientScope] || [];

    $tableHead.empty();
    $tableBody.empty();

    if (!items.length) {
        $tableBody.append('<tr><td class="text-center p-15">No clients found.</td></tr>');
        return;
    }

    let headRow = '<tr><th>Client Type</th>'; 
    
    items.forEach(item => {
        headRow += `<th>${item.label}</th>`;
    });
    headRow += '<th>Total</th></tr>';
    $tableHead.append(headRow);

    let totalNew = 0;
    let totalOld = 0;
    let grandTotal = 0;

    const $newRow = $('<tr>').append($('<td>').text('New Clients'));
    const $oldRow = $('<tr>').append($('<td>').text('Old Clients'));
    const $totalRow = $('<tr>').append($('<td>').html('<strong>Total</strong>'));

    items.forEach(item => {
        const periodTotal = item.new + item.old; 
        
        totalNew += item.new;
        totalOld += item.old;
        grandTotal += periodTotal;
        
        $newRow.append($('<td>').addClass(item.new == 0 ? 'zero' : '').text(item.new));
        $oldRow.append($('<td>').addClass(item.old == 0 ? 'zero' : '').text(item.old));
        $totalRow.append($('<td>').addClass(periodTotal == 0 ? 'zero' : '').html(`<strong>${periodTotal}</strong>`));
    });

    $newRow.append($('<td>').addClass(totalNew == 0 ? 'zero' : '').html(`<strong>${totalNew}</strong>`));
    $oldRow.append($('<td>').addClass(totalOld == 0 ? 'zero' : '').html(`<strong>${totalOld}</strong>`));
    $totalRow.append($('<td>').addClass(grandTotal == 0 ? 'zero' : '').html(`<strong>${grandTotal}</strong>`));

    $tableBody.append($newRow, $oldRow, $totalRow);
}