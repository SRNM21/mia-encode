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
    // For today
    loadClientToday()
    loadBankToday()

    // Client Series
    bindSelect(clientsTypeFilter, handleClientTypeFilter)
    bindSelect(clientsRangeSelect, handleClientScope)
    bindSelect(clientsYearSelect, loadClientSeries)
    loadClientSeries()

    // Banks Series
    bindSelect(banksTypeFilter, handleBankTypeFilter)
    bindSelect(banksRangeSelect, handleBankScope)
    bindSelect(banksYearSelect, loadBankSeries)
    loadBankSeries()

    // Leaderboards
    bindSelect($('#agent-leaderboards-select'), loadLeaderboards)
    loadLeaderboards()
}

// Filters
function handleClientTypeFilter() {
    clientTypeFilter = clientsTypeFilter.val() || 'all'
    renderClientSeriesFromCache()
}

function handleClientScope() {
    currentClientScope = clientsRangeSelect.val()
    clientsYearSelect.attr('disabled', currentClientScope === 'yearly')
    loadClientSeries()
}

function handleBankTypeFilter() {
    renderBankSeriesFromCache()
}

function handleBankScope() {
    currentBankScope = banksRangeSelect.val()
    banksYearSelect.attr('disabled', currentBankScope === 'yearly')
    loadBankSeries()
}

function renderClientSeriesFromCache() {
    const items = clientSeriesCache[currentClientScope] || []

    if (!items.length) {
        showEmpty(emptyClientsSeries,'No Clients Found.')
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
        showEmpty(emptyBanksSeries,'No Banks Found.')
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
async function loadClientToday() {
    showLoading(emptyClientsToday, clientTodayCanvas)

    try {
        const response = await fetchClientTypesToday()

        const data = response.data || {}

        if (!data || (!data.new && !data.old)) {
            showEmpty(emptyClientsToday, 'No Clients Found.')
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

    try {
        const response = await fetchClientTypeSeries({
            scope: currentClientScope,
            year: clientsYearSelect.val()
        })

        const data = response.data || {}

        populateSelect(
            clientsYearSelect,
            data.years || [],
            data.selected_year
        )

        clientSeriesCache = data.series || {}

        const items = clientSeriesCache[currentClientScope] || []

        if (!items.length) {
            showEmpty(emptyClientsSeries,'No Clients Found.')
            return
        }

        showContent(emptyClientsSeries, clientSeriesCanvas)

        renderClientSeriesFromCache()
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
            showEmpty(emptyBanksToday, 'No Banks Found.')
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
            showEmpty(emptyBanksSeries,'No Banks Found.')
            return
        }

        showContent(emptyBanksSeries, bankSeriesCanvas)

        renderBankSeriesFromCache()
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

    fetchAgentLeaderboards(
        { scope },
        res => {

           
        },
        xhr => console.error(xhr.responseJSON)
    )
}