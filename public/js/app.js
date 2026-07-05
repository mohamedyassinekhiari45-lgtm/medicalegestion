$(document).ready(function () {
    // sidebar toggle
    $('.sidebar-toggle').on('click', function () {
        $('.sidebar').toggleClass('show');
    });

    // close sidebar on outside click (mobile)
    $(document).on('click', function (e) {
        if ($(window).width() <= 768) {
            if (!$('.sidebar').is(e.target) && !$('.sidebar').has(e.target).length && !$('.sidebar-toggle').is(e.target)) {
                $('.sidebar').removeClass('show');
            }
        }
    });

    // active nav link
    $('.sidebar-nav .nav-link').each(function () {
        if ($(this).attr('href') === window.location.pathname) {
            $(this).addClass('active');
        }
    });

    // auto-hide alerts
    $('.alert').not('.alert-permanent').delay(4000).fadeOut(500);

    // tooltips
    if (typeof bootstrap !== 'undefined') {
        $('[data-bs-toggle="tooltip"]').each(function () {
            new bootstrap.Tooltip(this);
        });
    }

    // confetti-like effect on success
    if ($('.alert-success').length && typeof confetti !== 'undefined') {
        confetti({ particleCount: 80, spread: 60, origin: { y: 0.3 } });
    }
});

function confirmDelete(message) {
    return confirm(message || 'Êtes-vous sûr de vouloir supprimer cet élément ?');
}
