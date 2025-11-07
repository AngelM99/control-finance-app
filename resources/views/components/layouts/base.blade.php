<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <link rel="icon" type="image/png" href="/assets/img/favicon.png">
    <title>{{ $title ?? 'Control Finance' }}</title>

    <!-- Fonts and icons -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:300,400,600,700" rel="stylesheet" />

    <!-- Nucleo Icons -->
    <link href="/assets/css/nucleo-icons.css" rel="stylesheet" />
    <link href="/assets/css/nucleo-svg.css" rel="stylesheet" />

    <!-- Font Awesome Icons - CDN público -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
          integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
          crossorigin="anonymous" referrerpolicy="no-referrer" />

    <!-- CSS Files -->
    <link id="pagestyle" href="/assets/css/soft-ui-dashboard.css?v=1" rel="stylesheet" />

    <!-- Custom CSS for mobile menu -->
    <style>
        /* Botón hamburguesa mejorado */
        .navbar-toggler {
            border: none;
            padding: 0.5rem 0.75rem;
            background: transparent;
            border-radius: 0.5rem;
            transition: all 0.15s ease;
        }

        .navbar-toggler:hover {
            background: rgba(0, 0, 0, 0.05);
        }

        .navbar-toggler-icon {
            display: flex;
            flex-direction: column;
            justify-content: space-around;
            width: 22px;
            height: 18px;
        }

        .navbar-toggler-bar {
            display: block;
            position: relative;
            width: 100%;
            height: 2px;
            border-radius: 2px;
            background: #344767;
            transition: all 0.3s ease;
        }

        /* Animación del botón hamburguesa */
        .navbar-toggler.active .navbar-toggler-bar:nth-child(1) {
            transform: rotate(45deg) translate(5px, 5px);
        }

        .navbar-toggler.active .navbar-toggler-bar:nth-child(2) {
            opacity: 0;
        }

        .navbar-toggler.active .navbar-toggler-bar:nth-child(3) {
            transform: rotate(-45deg) translate(7px, -6px);
        }

        /* Sidebar mobile con fondo */
        @media (max-width: 1199.98px) {
            .sidenav {
                transform: translateX(-100%);
                transition: transform 0.3s ease;
                z-index: 1050;
                background: #fff !important;
                box-shadow: 0 20px 27px rgba(0, 0, 0, 0.05);
            }

            .sidenav.show {
                transform: translateX(0);
            }

            .g-sidenav-show .sidenav {
                transform: translateX(0);
            }

            /* Overlay oscuro detrás del menú */
            body.menu-open::before {
                content: '';
                position: fixed;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: rgba(0, 0, 0, 0.5);
                z-index: 1040;
                animation: fadeIn 0.3s ease;
            }

            @keyframes fadeIn {
                from { opacity: 0; }
                to { opacity: 1; }
            }

            /* Botón cerrar dentro del sidebar */
            .sidenav-close {
                position: absolute;
                top: 1rem;
                right: 1rem;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                background: #f8f9fa;
                border: none;
                border-radius: 0.5rem;
                cursor: pointer;
                z-index: 10;
                transition: all 0.15s ease;
            }

            .sidenav-close:hover {
                background: #e9ecef;
                transform: rotate(90deg);
            }

            .sidenav-close i {
                font-size: 1rem;
                color: #67748e;
            }
        }

        /* Livewire Loading Indicator */
        [x-cloak] { display: none !important; }

        .livewire-loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .livewire-loading-spinner {
            width: 50px;
            height: 50px;
            border: 4px solid #f3f4f6;
            border-top-color: #cb0c9f;
            border-radius: 50%;
            animation: spin 0.8s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Barra de progreso superior */
        .livewire-progress-bar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            height: 3px;
            background: linear-gradient(90deg, #cb0c9f, #5e72e4);
            z-index: 10000;
            transition: width 0.3s;
        }

        /* Unificación de diseño Dashboard */
        /* Stats Cards - altura fija */
        .stats-card {
            min-height: 90px;
            height: 100%;
        }

        .stats-card .card-body {
            display: flex;
            align-items: center;
            min-height: 90px;
            padding: 0.75rem !important;
        }

        /* Truncar texto largo con ellipsis */
        .text-truncate-custom {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            max-width: 100%;
            display: block;
        }

        /* Tooltip personalizado */
        [data-bs-toggle="tooltip"] {
            cursor: help;
        }

        /* Iconos de stats - tamaño uniforme */
        .icon-shape {
            width: 48px;
            height: 48px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .icon-shape i {
            font-size: 1.25rem !important;
            line-height: 1;
        }

        /* Asegurar visibilidad de iconos */
        .icon-shape .text-white {
            color: #fff !important;
        }

        /* Iconos en listas */
        .icon-sm {
            width: 32px;
            height: 32px;
        }

        .icon-sm i {
            font-size: 0.875rem !important;
        }

        /* Mejorar visibilidad de iconos - reducir opacidad excesiva */
        .icon-shape i.opacity-10,
        .icon-sm i.opacity-10 {
            opacity: 0.85 !important;
        }

        /* Números de stats - tamaño uniforme */
        .stats-card h5 {
            font-size: 1.5rem;
            line-height: 1.2;
        }

        .stats-card .text-sm {
            font-size: 0.75rem;
            line-height: 1.3;
        }

        /* Cards de contenido - altura ajustada para evitar scroll */
        .content-card {
            min-height: 280px;
            max-height: 320px;
            overflow-y: auto;
        }

        .content-card .card-body {
            padding: 1rem;
        }

        /* Ajustar tabla de transacciones */
        .content-card .table-responsive {
            max-height: 220px;
            overflow-y: auto;
        }

        /* Botones de acciones - altura uniforme */
        .action-btn {
            min-height: 44px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0.625rem 1.25rem;
        }

        /* List items - altura uniforme */
        .list-group-item {
            min-height: 50px;
            padding: 0.5rem 0.75rem;
        }

        /* Optimización para evitar scroll en desktop */
        @media (min-width: 1200px) {
            /* Altura controlada del contenedor principal */
            .main-content {
                max-height: 100vh;
                overflow-y: auto;
            }

            /* Navbar más compacto */
            .navbar-main {
                min-height: 60px;
                padding-top: 0.5rem !important;
                padding-bottom: 0.5rem !important;
            }

            /* Reducir espaciado general en dashboard */
            .main-content .container-fluid {
                padding-top: 0.75rem !important;
                padding-bottom: 0.5rem !important;
            }

            /* Reducir márgenes entre filas */
            .row.mt-4 {
                margin-top: 1rem !important;
            }

            .mb-4 {
                margin-bottom: 0.75rem !important;
            }

            /* Ajustar altura de las stats cards */
            .stats-card {
                min-height: 85px;
            }

            .stats-card .card-body {
                min-height: 85px;
                padding: 0.65rem !important;
            }

            .stats-card h5 {
                font-size: 1.35rem;
            }

            .stats-card .text-sm {
                font-size: 0.7rem;
            }

            /* Ajustar iconos */
            .stats-card .icon-shape {
                width: 42px;
                height: 42px;
            }

            /* Content cards más compactas */
            .content-card {
                min-height: 260px;
                max-height: 300px;
            }

            .content-card .card-header {
                padding: 0.75rem 1rem;
            }

            .content-card .card-body {
                padding: 0.75rem;
            }

            /* Listas más compactas */
            .list-group-item {
                min-height: 45px;
                padding: 0.4rem 0.5rem;
                font-size: 0.85rem;
            }

            /* Textos más pequeños en listas */
            .list-group-item h6 {
                font-size: 0.8rem;
                margin-bottom: 0.15rem;
            }

            .list-group-item .text-xs {
                font-size: 0.7rem;
            }

            /* Badges más pequeños */
            .badge-sm {
                font-size: 0.65rem;
                padding: 0.25rem 0.4rem;
            }

            /* Tabla más compacta */
            .table {
                font-size: 0.8rem;
            }

            .table th, .table td {
                padding: 0.4rem;
            }

            /* Botones de acción más pequeños */
            .action-btn {
                min-height: 38px;
                padding: 0.5rem 1rem;
                font-size: 0.85rem;
            }

            /* Reducir padding de las cards en general */
            .card {
                margin-bottom: 0.75rem;
            }

            .card-header {
                padding: 0.75rem 1rem;
            }

            .card-header h6 {
                margin-bottom: 0;
                font-size: 0.875rem;
            }

            /* Navbar breadcrumbs más compacto */
            .navbar .breadcrumb {
                margin-bottom: 0;
            }

            /* Ajustar altura del contenedor de las filas de acciones rápidas */
            .row:last-child {
                margin-bottom: 0 !important;
            }
        }

        /* Responsive - asegurar que no haya desbordamiento */
        @media (max-width: 767.98px) {
            .stats-card {
                margin-bottom: 1rem;
            }

            .content-card {
                min-height: 280px;
                max-height: none;
                overflow-y: visible;
            }

            .content-card .table-responsive {
                max-height: none;
            }

            .action-btn {
                margin-bottom: 0.5rem;
            }

            /* Reorganizar stats en móvil: texto izquierda, icono derecha */
            .stats-card .card-body {
                padding: 1rem !important;
            }

            .stats-card .card-body .row {
                display: flex !important;
                flex-direction: row !important;
                align-items: center !important;
                justify-content: space-between !important;
                margin: 0 !important;
            }

            .stats-card .col-8 {
                flex: 1 1 auto !important;
                max-width: calc(100% - 60px) !important;
                width: auto !important;
                padding-left: 0 !important;
                padding-right: 0.5rem !important;
            }

            .stats-card .col-4 {
                flex: 0 0 auto !important;
                max-width: 60px !important;
                width: 60px !important;
                display: flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 !important;
                text-align: center !important;
            }

            /* Eliminar text-end en móvil */
            .stats-card .col-4.text-end {
                text-align: center !important;
            }

            /* Ajustar números container */
            .stats-card .numbers {
                display: flex;
                flex-direction: column;
                align-items: flex-start;
                width: 100%;
            }

            /* Ajustar tamaño del número en móvil */
            .stats-card h5 {
                font-size: 1.5rem !important;
                margin-bottom: 0 !important;
            }

            .stats-card .stats-title {
                font-size: 0.65rem !important;
                line-height: 1.3 !important;
                margin-bottom: 0.25rem !important;
            }

            /* Icono en móvil */
            .stats-card .icon-shape {
                width: 48px !important;
                height: 48px !important;
                margin: 0 !important;
            }

            .stats-card .icon-shape i {
                font-size: 1.125rem !important;
            }
        }

        /* Título de stats - línea única con ellipsis */
        .stats-title {
            overflow: hidden;
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            line-height: 1.2;
            min-height: 2.4em;
            max-height: 2.4em;
        }
    </style>

    @livewireStyles
</head>

<body class="g-sidenav-show bg-gray-100">
    <!-- Livewire Loading Indicator -->
    <div wire:loading.delay class="livewire-loading-overlay">
        <div class="livewire-loading-spinner"></div>
    </div>

    {{ $slot }}

    <!-- Core JS Files -->
    <script src="/assets/js/core/popper.min.js"></script>
    <script src="/assets/js/core/bootstrap.min.js"></script>
    <script src="/assets/js/plugins/smooth-scrollbar.min.js"></script>
    <script>
        var win = navigator.platform.indexOf('Win') > -1;
        if (win && document.querySelector('#sidenav-scrollbar')) {
            var options = { damping: '0.5' }
            Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
        }
    </script>

    <!-- Control Center for Soft Dashboard -->
    <script src="/assets/js/soft-ui-dashboard.js"></script>

    <!-- Mobile Menu Toggle Script -->
    <script>
        // Función para inicializar el menú móvil
        function initMobileMenu() {
            const navbarToggler = document.querySelector('.navbar-toggler');
            const sidenav = document.querySelector('#sidenav-main');
            const sidenavCloseBtn = document.querySelector('#sidenav-close-btn');

            if (!navbarToggler || !sidenav) return;

            // Remover event listeners anteriores (si existen)
            const newNavbarToggler = navbarToggler.cloneNode(true);
            navbarToggler.parentNode.replaceChild(newNavbarToggler, navbarToggler);

            // Función para abrir menú
            function openMenu() {
                sidenav.classList.add('show');
                document.body.classList.add('menu-open');
                document.body.classList.add('g-sidenav-pinned');
                newNavbarToggler.classList.add('active');
            }

            // Función para cerrar menú
            function closeMenu() {
                sidenav.classList.remove('show');
                document.body.classList.remove('menu-open');
                document.body.classList.remove('g-sidenav-pinned');
                newNavbarToggler.classList.remove('active');
            }

            // Toggle menú con botón hamburguesa
            newNavbarToggler.addEventListener('click', function(e) {
                e.stopPropagation();
                if (sidenav.classList.contains('show')) {
                    closeMenu();
                } else {
                    openMenu();
                }
            });

            // Cerrar con botón X
            if (sidenavCloseBtn) {
                const newCloseBtn = sidenavCloseBtn.cloneNode(true);
                sidenavCloseBtn.parentNode.replaceChild(newCloseBtn, sidenavCloseBtn);

                newCloseBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    closeMenu();
                });
            }

            // Cerrar al hacer click en el overlay (fuera del sidebar)
            document.addEventListener('click', function(event) {
                if (window.innerWidth < 1200 && sidenav.classList.contains('show')) {
                    if (!sidenav.contains(event.target) && !newNavbarToggler.contains(event.target)) {
                        closeMenu();
                    }
                }
            });

            // Cerrar menú al hacer click en un enlace del menú
            const menuLinks = sidenav.querySelectorAll('a[wire\\:navigate]');
            menuLinks.forEach(link => {
                link.addEventListener('click', function() {
                    if (window.innerWidth < 1200) {
                        setTimeout(() => closeMenu(), 300);
                    }
                });
            });

            console.log('✓ Mobile menu initialized');
        }

        // Función para inicializar tooltips
        function initTooltips() {
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }

        // Inicializar al cargar la página
        document.addEventListener('DOMContentLoaded', function() {
            initMobileMenu();
            initTooltips();

            // Verificar que Font Awesome se haya cargado
            setTimeout(function() {
                var iconTest = document.querySelector('.fas, .far, .fab, .fa');
                if (iconTest) {
                    console.log('✓ Font Awesome icons loaded successfully');
                } else {
                    console.warn('⚠ Font Awesome might not be loaded correctly');
                }
            }, 1000);
        });

        // Re-inicializar después de navegación Livewire
        document.addEventListener('livewire:navigated', function() {
            initMobileMenu();
            initTooltips();
            console.log('✓ Mobile menu re-initialized after navigation');
        });
    </script>

    @livewireScripts

    <!-- DataTables CSS & JS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
    <script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>

    <!-- DataTables Soft UI Dashboard Styling -->
    <style>
        .dataTables_wrapper .dataTables_length select {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d2d6da;
        }
        .dataTables_wrapper .dataTables_filter input {
            padding: 0.5rem;
            border-radius: 0.5rem;
            border: 1px solid #d2d6da;
            margin-left: 0.5rem;
        }
        .dataTables_wrapper .dataTables_paginate .pagination {
            margin-top: 1rem;
        }
        table.dataTable thead th {
            border-bottom: 1px solid #e9ecef;
        }
        .dt-buttons {
            margin-bottom: 1rem;
        }
        .dt-button {
            background: linear-gradient(310deg, #17ad37 0%, #98ec2d 100%) !important;
            color: white !important;
            border: none !important;
            padding: 0.5rem 1rem !important;
            border-radius: 0.5rem !important;
            margin-right: 0.5rem !important;
            font-size: 0.875rem !important;
        }
        .dt-button:hover {
            opacity: 0.9 !important;
        }
    </style>

    <!-- Livewire Navigate Configuration -->
    <script>
        // Configuración de Livewire para navegación SPA
        document.addEventListener('livewire:navigating', () => {
            // Mostrar indicador de carga al navegar
            console.log('Navegando...');
        });

        document.addEventListener('livewire:navigated', () => {
            // Ocultar indicador después de navegar
            console.log('Navegación completada');

            // Re-inicializar plugins de Soft UI si es necesario
            if (typeof Scrollbar !== 'undefined') {
                var win = navigator.platform.indexOf('Win') > -1;
                if (win && document.querySelector('#sidenav-scrollbar')) {
                    var options = { damping: '0.5' }
                    Scrollbar.init(document.querySelector('#sidenav-scrollbar'), options);
                }
            }
        });

        // Barra de progreso para wire:navigate
        window.addEventListener('livewire:navigate', () => {
            let progressBar = document.createElement('div');
            progressBar.className = 'livewire-progress-bar';
            progressBar.style.width = '0%';
            document.body.appendChild(progressBar);

            let width = 0;
            let interval = setInterval(() => {
                width += 10;
                if (width <= 90) {
                    progressBar.style.width = width + '%';
                } else {
                    clearInterval(interval);
                }
            }, 100);

            window.addEventListener('livewire:navigated', () => {
                clearInterval(interval);
                progressBar.style.width = '100%';
                setTimeout(() => {
                    progressBar.remove();
                }, 300);
            }, { once: true });
        });
    </script>

    @stack('scripts')
</body>
</html>
