(function () {
    window.addEventListener('pageshow', function (event) {
        var nav = window.performance && window.performance.getEntriesByType
            ? window.performance.getEntriesByType('navigation')[0]
            : null;
        var fromHistory = event.persisted || (nav && nav.type === 'back_forward');
        if (fromHistory) {
            window.location.reload();
        }
    });
})();
