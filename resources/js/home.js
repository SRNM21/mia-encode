const toggleBtn = $('#toggle-btn');
const sidebar = $('#sidebar');

toggleBtn.on('click', () => {
    if (sidebar.hasClass('collapsed')) {
        sidebar.removeClass('collapsed')
    } else {
        sidebar.addClass('collapsed')
    }
});