<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Gestion Médicale')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @stack('styles')
</head>
<body>
    <div class="wrapper">
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-brand">
                <a href="{{ route('dashboard') }}" class="sidebar-brand-link">
                    <img src="{{ asset('img/logo.png') }}" alt="Logo" class="sidebar-logo">
                    <span>Gestion Médicale</span>
                </a>
                <small>Système de gestion de cabinet</small>
            </div>

            <div class="sidebar-user">
                <div class="avatar-wrapper">
                    <div class="avatar">
                        @if(Auth::user()->avatar)
                        <img src="{{ asset('storage/avatars/' . Auth::user()->avatar) }}" alt="Photo">
                        @else
                        <span class="avatar-initials">{{ strtoupper(substr(Auth::user()->prenom, 0, 1)) }}{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        @endif
                    </div>
                    <span class="status-dot" id="sidebarStatusDot" title="En ligne"></span>
                </div>
                <div class="user-info">
                    <div class="name">{{ Auth::user()->prenom }} {{ Auth::user()->name }}</div>
                    <div class="role-badge role-{{ Auth::user()->role }}">
                        <i class="bi bi-{{ Auth::user()->role === 'admin' ? 'shield-lock' : (Auth::user()->role === 'medecin' ? 'person-badge' : 'person') }}"></i>
                        {{ Auth::user()->role === 'admin' ? 'Admin' : (Auth::user()->role === 'medecin' ? 'Médecin' : 'Récep.') }}
                    </div>
                </div>
                <div class="sidebar-user-menu">
                    <a href="{{ route('profile') }}" class="sidebar-user-action" title="Mon profil">
                        <i class="bi bi-gear"></i>
                    </a>
                </div>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">Principal</div>

                <div class="nav-item">
                    <a href="{{ route('dashboard') }}" class="nav-link">
                        <i class="bi bi-speedometer2"></i>
                        <span>Tableau de bord</span>
                    </a>
                </div>

                @if(Auth::user()->role === 'receptionniste')
                <div class="nav-section">Gestion</div>
                <div class="nav-item">
                    <a href="{{ route('patients.index') }}" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>Patients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('rendez-vous.index') }}" class="nav-link">
                        <i class="bi bi-calendar-check"></i>
                        <span>Rendez-vous</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('rendez-vous.calendar') }}" class="nav-link">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendrier</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('factures.index') }}" class="nav-link">
                        <i class="bi bi-receipt"></i>
                        <span>Factures</span>
                    </a>
                </div>
                @endif

                @if(Auth::user()->role === 'medecin')
                <div class="nav-section">Mon activité</div>
                <div class="nav-item">
                    <a href="{{ route('rendez-vous.index') }}" class="nav-link">
                        <i class="bi bi-calendar-check"></i>
                        <span>Mes rendez-vous</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('rendez-vous.calendar') }}" class="nav-link">
                        <i class="bi bi-calendar3"></i>
                        <span>Calendrier</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('consultations.index') }}" class="nav-link">
                        <i class="bi bi-clipboard2-pulse"></i>
                        <span>Consultations</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('search-patient-form').submit();">
                        <i class="bi bi-folder2-open"></i>
                        <span>Dossiers médicaux</span>
                    </a>
                    <form id="search-patient-form" method="GET" action="{{ route('patients.index') }}" class="d-none"></form>
                </div>
                @endif

                @if(Auth::user()->role === 'admin')
                <div class="nav-section">Administration</div>
                <div class="nav-item">
                    <a href="{{ route('patients.index') }}" class="nav-link">
                        <i class="bi bi-people"></i>
                        <span>Patients</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('users.index') }}" class="nav-link">
                        <i class="bi bi-person-badge"></i>
                        <span>Utilisateurs</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('users.index', ['role' => 'medecin']) }}" class="nav-link">
                        <i class="bi bi-heart-pulse"></i>
                        <span>Médecins</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('users.index', ['role' => 'receptionniste']) }}" class="nav-link">
                        <i class="bi bi-person"></i>
                        <span>Réceptionnistes</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('users.index', ['role' => 'admin']) }}" class="nav-link">
                        <i class="bi bi-shield-lock"></i>
                        <span>Administrateurs</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('specialites.index') }}" class="nav-link">
                        <i class="bi bi-tags"></i>
                        <span>Spécialités</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('tarifs.index') }}" class="nav-link">
                        <i class="bi bi-currency-euro"></i>
                        <span>Tarifs</span>
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('statistiques.index') }}" class="nav-link">
                        <i class="bi bi-graph-up"></i>
                        <span>Statistiques</span>
                    </a>
                </div>
                @endif

                <div class="nav-section">Système</div>
                <div class="nav-item">
                    <a href="{{ route('notifications.index') }}" class="nav-link">
                        <i class="bi bi-bell"></i>
                        <span>Notifications</span>
                        @if(Auth::user()->unreadNotifications->count() > 0)
                        <span class="badge bg-danger ms-auto">{{ Auth::user()->unreadNotifications->count() }}</span>
                        @endif
                    </a>
                </div>
                <div class="nav-item">
                    <a href="{{ route('activity-logs.index') }}" class="nav-link">
                        <i class="bi bi-activity"></i>
                        <span>Journal d'activité</span>
                    </a>
                </div>

                <div class="sidebar-footer">
                    <button class="theme-toggle" onclick="toggleTheme()" title="Mode sombre/clair">
                        <i class="bi bi-moon-stars" id="sidebarThemeIcon"></i>
                        <span id="theme-label">Mode sombre</span>
                    </button>
                    <button class="theme-toggle" onclick="toggleEyeComfort()" title="Activer Eye Comfort">
                        <i class="bi bi-shield-plus" id="sidebarEyeIcon"></i>
                        <span id="eye-label">Eye Comfort</span>
                    </button>
                    <a href="{{ route('profile') }}" class="nav-link">
                        <i class="bi bi-person"></i>
                        <span>Mon profil</span>
                    </a>
                    <a href="#" class="nav-link" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                        <i class="bi bi-box-arrow-right"></i>
                        <span>Déconnexion</span>
                    </a>
                </div>
            </nav>
        </aside>

        <main class="main-content">
            <div class="topbar">
                <div class="d-flex align-items-center gap-3">
                    <button class="sidebar-toggle" onclick="$('.sidebar').toggleClass('show')">
                        <i class="bi bi-list"></i>
                    </button>
                    <div class="page-title">
                        <h4>@yield('title', 'Tableau de bord')</h4>
                        <small>@yield('subtitle', '')</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-outline-secondary border-0" onclick="toggleTheme()" id="topbarThemeToggle" title="Changer de thème">
                        <i class="bi bi-moon-stars" id="topbarThemeIcon"></i>
                    </button>
                    <button class="btn btn-outline-secondary border-0" onclick="toggleEyeComfort()" id="topbarEyeToggle" title="Activer Eye Comfort">
                        <i class="bi bi-shield-plus" id="topbarEyeIcon"></i>
                    </button>
                    <div class="dropdown" id="notif-dropdown">
                        <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="dropdown" aria-expanded="false" id="notif-bell">
                            <i class="bi bi-bell"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" id="notif-badge" style="font-size: 10px; {{ Auth::user()->unreadNotifications->count() > 0 ? '' : 'display: none;' }}">{{ Auth::user()->unreadNotifications->count() }}</span>
                        </button>
                        <div class="dropdown-menu dropdown-menu-end notif-dropdown" id="notif-dropdown-menu">
                            <div class="notif-header d-flex justify-content-between align-items-center">
                                <strong>Notifications</strong>
                                <a href="{{ route('notifications.index') }}" class="small">Voir tout</a>
                            </div>
                            <div id="notif-list">
                                <div class="text-center text-muted py-3"><div class="spinner-border spinner-border-sm"></div> Chargement...</div>
                            </div>
                        </div>
                    </div>
                    <span class="text-muted small">
                        <i class="bi bi-calendar3"></i> <span id="currentDate">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
                        <span class="mx-1">|</span>
                        <i class="bi bi-clock"></i> <span id="currentTime">{{ now()->format('H:i:s') }}</span>
                    </span>
                </div>
            </div>

            @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show animate-fade-up">
                <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif
            @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show animate-fade-up">
                <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            @endif

            @yield('content')
        </main>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">@csrf</form>

    <!-- Confirmation Modal -->
    <div class="modal fade" id="confirmModal" tabindex="-1" aria-labelledby="confirmModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="confirmModalLabel"><i class="bi bi-exclamation-triangle text-warning"></i> Confirmation</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body" id="confirmModalMessage">Êtes-vous sûr ?</div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annuler</button>
                    <button type="button" class="btn btn-danger" id="confirmModalBtn">Confirmer</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('js/app.js') }}"></script>
    <script>
    var pendingForm = null;
    var pendingMsg = '';

    var pendingButton = null;
    $(document).on('click', 'form[data-confirm] button[type=submit]', function() {
        pendingButton = this;
    });
    $(document).on('submit', 'form[data-confirm]', function(e) {
        e.preventDefault();
        pendingMsg = $(this).data('confirm');
        pendingForm = this;
        $('#confirmModalMessage').text(pendingMsg);
        $('#confirmModal').modal('show');
    });

    $('#confirmModalBtn').on('click', function() {
        if (pendingForm) {
            if (pendingButton && pendingButton.name) {
                var h = document.createElement('input');
                h.type = 'hidden';
                h.name = pendingButton.name;
                h.value = pendingButton.value;
                pendingForm.appendChild(h);
            }
            pendingForm.submit();
        }
        $('#confirmModal').modal('hide');
    });

    $('#confirmModal').on('hidden.bs.modal', function() {
        pendingForm = null;
    });

    var html = document.documentElement;

    function toggleTheme() {
        var current = html.getAttribute('data-theme') || '';
        var next = current === 'dark' ? '' : 'dark';
        html.setAttribute('data-theme', next);
        localStorage.setItem('theme', next);
        updateThemeIcon(next);
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: next } }));
    }

    function updateThemeIcon(theme) {
        var topbarIcon = document.getElementById('topbarThemeIcon');
        var sidebarIcon = document.querySelector('.theme-toggle i');
        var sidebarLabel = document.getElementById('theme-label');
        var isDark = theme === 'dark';
        if (topbarIcon) topbarIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
        if (sidebarIcon) sidebarIcon.className = isDark ? 'bi bi-sun' : 'bi bi-moon-stars';
        if (sidebarLabel) sidebarLabel.textContent = isDark ? 'Mode clair' : 'Mode sombre';
    }

    function toggleEyeComfort() {
        var current = html.getAttribute('data-eye-comfort') || '';
        var next = current === 'true' ? '' : 'true';
        if (next === 'true') {
            html.setAttribute('data-eye-comfort', 'true');
        } else {
            html.removeAttribute('data-eye-comfort');
        }
        localStorage.setItem('eyeComfort', next === 'true' ? '1' : '');
        updateEyeIcon(next === 'true');
        document.dispatchEvent(new CustomEvent('themechange', { detail: { theme: html.getAttribute('data-theme') || '', eyeComfort: next === 'true' } }));
    }

    function updateEyeIcon(active) {
        var topbarIcon = document.getElementById('topbarEyeIcon');
        var sidebarIcon = document.getElementById('sidebarEyeIcon');
        var sidebarLabel = document.getElementById('eye-label');
        if (topbarIcon) topbarIcon.className = active ? 'bi bi-shield-fill-check' : 'bi bi-shield-plus';
        if (sidebarIcon) sidebarIcon.className = active ? 'bi bi-shield-fill-check' : 'bi bi-shield-plus';
        if (sidebarLabel) sidebarLabel.textContent = active ? 'Eye Comfort désactiver' : 'Eye Comfort';
    }

    (function() {
        var savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            html.setAttribute('data-theme', 'dark');
            updateThemeIcon('dark');
        } else if (!savedTheme && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            html.setAttribute('data-theme', 'dark');
            updateThemeIcon('dark');
        } else {
            updateThemeIcon('');
        }
        var savedEye = localStorage.getItem('eyeComfort');
        if (savedEye === '1') {
            html.setAttribute('data-eye-comfort', 'true');
            updateEyeIcon(true);
        } else {
            updateEyeIcon(false);
        }
    })();

    function loadNotifications() {
        $.get('{{ route("notifications.dropdown") }}')
            .done(function(data) {
                $('#notif-list').html(data.html);
                if (data.count > 0) {
                    $('#notif-badge').text(data.count).show();
                } else {
                    $('#notif-badge').hide();
                }
            })
            .fail(function() {
                $('#notif-list').html('<div class="text-center text-muted py-3"><i class="bi bi-exclamation-triangle"></i> Erreur</div>');
            });
    }
    function updateClock() {
        var now = new Date();
        var h = String(now.getHours()).padStart(2, '0');
        var m = String(now.getMinutes()).padStart(2, '0');
        var s = String(now.getSeconds()).padStart(2, '0');
        document.getElementById('currentTime').textContent = h + ':' + m + ':' + s;
    }
    $(document).ready(function() {
        loadNotifications();
        setInterval(loadNotifications, 30000);
        setInterval(updateClock, 1000);
    });

    document.addEventListener('contextmenu', function(e) { e.preventDefault(); });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'PrintScreen') {
            e.preventDefault();
            showScreenshotWarning();
        }
        if (e.ctrlKey && e.shiftKey && (e.key === 'I' || e.key === 'i')) { e.preventDefault(); }
        if (e.ctrlKey && e.shiftKey && (e.key === 'J' || e.key === 'j')) { e.preventDefault(); }
        if (e.ctrlKey && e.key === 'u') { e.preventDefault(); }
        if (e.key === 'F12') { e.preventDefault(); }
    });
    if (navigator.mediaDevices && navigator.mediaDevices.getDisplayMedia) {
        var origGetDisplayMedia = navigator.mediaDevices.getDisplayMedia;
        navigator.mediaDevices.getDisplayMedia = function() {
            showScreenshotWarning();
            return origGetDisplayMedia.apply(this, arguments).then(function(stream) {
                stream.getTracks().forEach(function(track) { track.stop(); });
                showScreenshotWarning();
                return stream;
            });
        };
    }
    var lastVisible = Date.now();
    document.addEventListener('visibilitychange', function() {
        if (document.visibilityState === 'hidden' && document.querySelector('.unlock-overlay')) return;
        var wrapper = document.querySelector('.wrapper');
        if (!wrapper) return;
        if (document.hidden) {
            if (Date.now() - lastVisible < 2000) return;
            wrapper.style.transition = 'filter 0.3s';
            wrapper.style.filter = 'blur(20px)';
            showUnlockOverlay();
        } else {
            lastVisible = Date.now();
        }
    });
    function showUnlockOverlay() {
        var existing = document.querySelector('.unlock-overlay');
        if (existing) return;
        var overlay = document.createElement('div');
        overlay.className = 'unlock-overlay';
        overlay.innerHTML = '<div style="position:fixed;inset:0;z-index:99999;background:rgba(0,0,0,0.85);display:flex;align-items:center;justify-content:center;flex-direction:column;gap:20px;font-family:Inter,sans-serif;"><i class="bi bi-shield-lock" style="font-size:4rem;color:#6366f1;"></i><h3 style="color:#fff;margin:0;font-weight:600;">Session interrompue</h3><p style="color:#94a3b8;margin:0;font-size:0.95rem;text-align:center;max-width:400px;">Contenu confidentiel. Cliquez ci-dessous pour accéder aux données.</p><button onclick="unlockPage()" style="background:#6366f1;color:#fff;border:none;padding:12px 32px;border-radius:10px;font-weight:600;font-size:1rem;cursor:pointer;box-shadow:0 4px 20px rgba(99,102,241,0.4);">Accéder au contenu</button></div>';
        document.body.appendChild(overlay);
    }
    function unlockPage() {
        var overlay = document.querySelector('.unlock-overlay');
        if (overlay) overlay.remove();
        var wrapper = document.querySelector('.wrapper');
        if (wrapper) { wrapper.style.transition = 'filter 0.5s'; wrapper.style.filter = 'none'; }
    }
    function showScreenshotWarning() {
        var existing = document.querySelector('.screenshot-alert');
        if (existing) existing.remove();
        var div = document.createElement('div');
        div.className = 'screenshot-alert';
        div.innerHTML = '<i class="bi bi-shield-exclamation"></i> Capture d\'écran interdite — Données médicales confidentielles';
        div.style.cssText = 'position:fixed;top:20px;left:50%;transform:translateX(-50%);z-index:99999;background:#dc2626;color:#fff;padding:14px 28px;border-radius:12px;font-weight:600;font-size:0.95rem;box-shadow:0 8px 30px rgba(220,38,38,0.4);display:flex;align-items:center;gap:10px;animation:fadeInUp 0.3s ease;font-family:Inter,sans-serif;';
        document.body.appendChild(div);
        setTimeout(function() { div.style.opacity = '0'; div.style.transition = 'opacity 0.5s'; setTimeout(function() { div.remove(); }, 500); }, 3000);
    }
    </script>
    @stack('scripts')
</body>
</html>
