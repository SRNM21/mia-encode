import { useAjax } from "../hooks/use-ajax.js"

const { post } = useAjax()

export function fetchClientTypesToday() {
    return post({
        url: 'dashboard/chart/client-type-today'
    })
}

export function fetchBankAppsToday() {
    return post({
        url: 'dashboard/chart/bank-apps-today'
    })
}

export function fetchClientTypeSeries(data) {
    return post({
        url: 'dashboard/chart/client-type-series',
        data
    })
}

export function fetchBankAppsSeries(data) {
    return post({
        url: 'dashboard/chart/bank-apps-series',
        data
    })
}

export function fetchAgentLeaderboards(data) {
    return post({
        url: 'dashboard/chart/agents-leaderboards',
        data
    })
}