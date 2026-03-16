import { href } from "./utils/utils.js"

const leaderboardsFilter = $('#leaderboards-filter')

leaderboardsFilter.on('change', function () {
    const filter = leaderboardsFilter.val() || 'today'

    const url = new URL(window.location.href)
    url.searchParams.set('filter', String(filter))
    href(url.toString())
})