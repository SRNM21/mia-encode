import { fetchClientTypesToday, fetchBankAppsToday, fetchClientTypeSeries, fetchBankAppsSeries, fetchAgentLeaderboards } from './services/dashboard-api.js'
import { renderClientTodayChart } from './charts/client-charts.js'
import { showLoading, showContent, showEmpty } from './ui/empty-state.js'
import { bindSelect, populateSelect } from './ui/select-utils.js'
import { normalizeBankSeries, renderBankSeries, renderBankTodayChart } from './charts/bank-charts.js'
import { renderClientSeries } from './charts/client-charts.js'
import { renderLeaderboards } from './components/leaderboards.js'

const doc = $(document)

const clientTodayCanvas = $('#clients-type-chart')
const banksTodayCanvas = $('#bank-apps-type-chart')

const clientSeriesCanvas = $('#clients-type-line')
const bankSeriesCanvas = $('#bank-applications-type-line')

const leaderboardsContent = $('.leaderboards-table-content')

const emptyClientsToday = $('.empty-clients-today')
const emptyBanksToday = $('.empty-banks-today')
const emptyClientsSeries = $('.empty-clients-series')
const emptyBanksSeries = $('.empty-banks-series')
const emptyLeaderboards = $('.empty-leaderboards')

const clientsTypeFilter = $('#clients-type-filter')
const clientsYearSelect = $('#clients-year-select')
const clientsRangeSelect = $('#clients-series-select')

const banksTypeFilter = $('#bank-apps-type-filter')
const banksYearSelect = $('#bank-apps-year-select')
const banksRangeSelect = $('#bank-apps-series-select')

let clientSeriesCache = {}
let bankSeriesCache = {}

let currentClientScope = 'monthly'
let currentBankScope = 'monthly'

let clientTypeFilter = 'all'

doc.ready(initDashboard)

function initDashboard() {
    loadClientToday()
    loadBankToday()

    bindSelect(clientsTypeFilter, handleClientTypeFilter)
    bindSelect(clientsRangeSelect, handleClientScope)
    bindSelect(clientsYearSelect, loadClientSeries)

    bindSelect(banksTypeFilter, handleBankTypeFilter)
    bindSelect(banksRangeSelect, handleBankScope)
    bindSelect(banksYearSelect, loadBankSeries)

    bindSelect($('#agent-leaderboards-select'), loadLeaderboards)

    loadClientSeries()
    loadBankSeries()
    loadLeaderboards()
}

// Filters
function handleClientTypeFilter() {
    clientTypeFilter = clientsTypeFilter.val() || 'all'
    renderClientSeriesFromCache()
}

function handleClientScope() {
    currentClientScope = clientsRangeSelect.val()
    loadClientSeries()
}

function handleBankTypeFilter() {
    renderBankSeriesFromCache()
}

function handleBankScope() {
    currentBankScope = banksRangeSelect.val()
    loadBankSeries()
}

function renderClientSeriesFromCache() {
    const items = clientSeriesCache[currentClientScope] || []

    if (!items.length) {
        showEmpty(emptyClientsSeries,'No Clients Found')
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

    let datasets

    if (clientTypeFilter === 'new') datasets = [newDataset]
    else if (clientTypeFilter === 'old') datasets = [oldDataset]
    else datasets = [newDataset, oldDataset]

    renderClientSeries(
        clientSeriesCanvas,
        labels,
        datasets
    )
}

function renderBankSeriesFromCache() {

    const series = bankSeriesCache[currentBankScope] || {}

    if (!series.labels?.length) {
        showEmpty(emptyBanksSeries,'No Banks Found')
        return
    }

    const selected = (banksTypeFilter.val() || 'all banks').toLowerCase()

    let datasets = series.datasets

    if (selected !== 'all banks') {
        datasets = datasets.filter(
            ds => ds.label.toLowerCase() === selected
        )
    }

    renderBankSeries(
        bankSeriesCanvas,
        series.labels,
        datasets
    )
}

// Clients charts
function loadClientToday() {
    showLoading(emptyClientsToday)

    fetchClientTypesToday(
        res => {

            const data = res.data || {}

            if (!data.new && !data.old) {
                showEmpty(emptyClientsToday, 'No Clients Found')
                return
            }

            showContent(emptyClientsToday)

            renderClientTodayChart(
                clientTodayCanvas,
                data.new,
                data.old
            )
        },
        xhr => console.error(xhr.responseJSON)
    )
}

function loadClientSeries() {

    showLoading(emptyClientsSeries, clientSeriesCanvas)

    fetchClientTypeSeries(
        {
            scope: currentClientScope,
            year: clientsYearSelect.val()
        },
        res => {

            const data = res.data || {}

            populateSelect(
                clientsYearSelect,
                data.years || [],
                data.selected_year
            )

            clientSeriesCache = data.series || {}

            const items = clientSeriesCache[currentClientScope] || []

            if (!items.length) {
                showEmpty(emptyClientsSeries,'No Clients Found')
                return
            }

            showContent(emptyClientsSeries, clientSeriesCanvas)

            renderClientSeriesFromCache()
        },
        xhr => console.error(xhr.responseJSON)
    )
}

function loadBankToday() {
    showLoading(emptyBanksToday)

    fetchBankAppsToday(
        res => {
            const payload = res.data || {}

            if (!payload.labels?.length) {
                showEmpty(emptyBanksToday, 'No Banks Found')
                return
            }

            showContent(emptyBanksToday)

            renderBankTodayChart(
                banksTodayCanvas,
                payload.labels,
                payload.counts
            )
        },
        xhr => console.error(xhr.responseJSON)
    )
}

function loadBankSeries() {
    showLoading(emptyBanksSeries, bankSeriesCanvas)

    fetchBankAppsSeries(
        {
            scope: currentBankScope,
            year: banksYearSelect.val()
        },
        res => {

            const data = res.data || {}

            populateSelect(
                banksYearSelect,
                data.years || [],
                data.selected_year
            )

            bankSeriesCache = normalizeBankSeries(data.series || {})

            const series = bankSeriesCache[currentBankScope] || {}

            if (banksTypeFilter.children().length <= 1) {
                const banks = (series.datasets || []).map(ds => ds.label)
                banks.unshift('All Banks')

                populateSelect(
                    banksTypeFilter,
                    banks,
                    'All Banks'
                )
            }

            if (!series.labels?.length) {
                showEmpty(emptyBanksSeries,'No Banks Found')
                return
            }

            showContent(emptyBanksSeries, bankSeriesCanvas)

            renderBankSeriesFromCache()
        },
        xhr => console.error(xhr.responseJSON)
    )
}

function loadLeaderboards() {

    showLoading(emptyLeaderboards, leaderboardsContent)

    const scope = $('#agent-leaderboards-select').val() || 'today'

    fetchAgentLeaderboards(
        { scope },
        res => {

            const rows = res.data || []

            if (!rows.length) {
                showEmpty(emptyLeaderboards,'No submissions as of now.')
                return
            }

            showContent(emptyLeaderboards, leaderboardsContent)

            renderLeaderboards(
                leaderboardsContent,
                rows
            )
        },
        xhr => console.error(xhr.responseJSON)
    )
}