import { useAjax } from "../hooks/use-ajax.js"

const { post } = useAjax()

export function fetchClientTypesToday(success, error) {
    post({
        url: 'dashboard/chart/client-type-today',
        success,
        error
    })
}

export function fetchBankAppsToday(success, error) {
    post({
        url: 'dashboard/chart/bank-apps-today',
        success,
        error
    })
}

export function fetchClientTypeSeries(data, success, error) {
    post({
        url: 'dashboard/chart/client-type-series',
        data,
        success,
        error
    })
}

export function fetchBankAppsSeries(data, success, error) {
    post({
        url: 'dashboard/chart/bank-apps-series',
        data,
        success,
        error
    })
}

export function fetchAgentLeaderboards(data, success, error) {
    post({
        url: 'dashboard/chart/agents-leaderboards',
        data,
        success,
        error
    })
}