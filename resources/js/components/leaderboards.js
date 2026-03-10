export function renderLeaderboards(container, rows) {

    const ranks = ['1st','2nd','3rd','4th','5th']
    const data = Array.isArray(rows) ? rows : []

    const table = $(`
        <table class="leaderboards-table">
            <thead>
                <tr>
                    <th>Rank</th>
                    <th>Agent</th>
                    <th>Submissions</th>
                </tr>
            </thead>
        </table>
    `)

    const tbody = $('<tbody>')

    for (let i = 0; i < 5; i++) {

        const row = data[i]

        const tr = $('<tr>')

        $('<td>').text(ranks[i]).appendTo(tr)
        $('<td>').text(row?.agent ?? '—').appendTo(tr)
        $('<td>').text(row?.count ?? '—').appendTo(tr)

        tbody.append(tr)
    }

    table.append(tbody)

    container.empty().append(table)
}