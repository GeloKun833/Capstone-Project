<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>PMS Portal</title>
    <link rel="shortcut icon" href="{{ URL::to('assets/img/favicon.png') }}">
    <link rel="preload" href="{{ URL::to('assets/css/style.css') }}" as="style">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/bootstrap/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/feather/feather.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/icons/flags/flags.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/bootstrap-datetimepicker.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/fontawesome/css/all.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/simple-calendar/simple-calendar.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/datatables/datatables.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/plugins/select2/css/select2.min.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/style.css') }}">
    <link rel="stylesheet" href="{{ URL::to('assets/css/toastr.min.css') }}">
    @stack('styles')
</head>
<body>
    <div class="main-wrapper">
        <div class="header">
            <div class="header-left">
                <a href="{{ route('home') }}" class="logo">
                    <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="Logo" width="40" height="40" loading="eager">
                </a>
                <a href="{{ route('home') }}" class="logo logo-small">
                    <img src="{{ URL::to('assets/img/Logo.jpg') }}" alt="Logo" width="30" height="30" loading="eager">
                </a>
            </div>
            <div class="menu-toggle">
                <a href="javascript:void(0);" id="toggle_btn">
                    <i class="fas fa-bars"></i>
                </a>
            </div>

            <div class="top-nav-search">
                <form>
                    <input type="text" class="form-control" placeholder="Search here">
                    <button class="btn" type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
            <a class="mobile_btn" id="mobile_btn">
                <i class="fas fa-bars"></i>
            </a>
            <ul class="nav user-menu">


                <li class="nav-item dropdown noti-dropdown me-2">
                    <a href="#" class="dropdown-toggle nav-link header-nav-list" data-bs-toggle="dropdown">
                        <i class="fas fa-bell"></i>
                        @if(($headerUnreadCount ?? 0) > 0)
                            <span class="badge badge-danger">{{ $headerUnreadCount }}</span>
                        @endif
                    </a>
                    <div class="dropdown-menu notifications">
                        <div class="topnav-dropdown-header">
                            <span class="notification-title">Notifications</span>
                            @if(($headerUnreadCount ?? 0) > 0)
                                <a href="javascript:void(0)" class="clear-noti" onclick="markAllAsRead()"> Clear All </a>
                            @endif
                        </div>
                        <div class="noti-content">
                            <ul class="notification-list">
                                @forelse(($headerNotifications ?? collect()) as $notification)
                                    <li class="notification-message {{ $notification->read_at ? '' : 'unread' }}">
                                        @php
                                            $data = is_array($notification->data) ? $notification->data : json_decode($notification->data, true);
                                            $url = $data['url'] ?? 'javascript:void(0)';
                                            $icon = $data['icon'] ?? 'fas fa-bell';
                                            $title = $data['title'] ?? 'Notification';
                                            $message = $data['message'] ?? 'You have a new notification';
                                            $teacher = $data['teacher'] ?? null;
                                        @endphp
                                        <a href="{{ $url }}" onclick="markAsRead('{{ $notification->id }}')">
                                            <div class="media d-flex">
                                                <span class="avatar avatar-sm flex-shrink-0">
                                                    <i class="{{ $icon }} text-primary"></i>
                                                </span>
                                                <div class="media-body flex-grow-1">
                                                    <p class="noti-details">
                                                        <span class="noti-title">{{ $title }}</span>
                                                        <br>
                                                        <small>{{ $message }}</small>
                                                        @if($teacher)
                                                            <br>
                                                            <small class="text-muted"><i class="fas fa-user"></i> By {{ $teacher }}</small>
                                                        @endif
                                                    </p>
                                                    <p class="noti-time">
                                                        <span class="notification-time">{{ $notification->created_at->diffForHumans() }}</span>
                                                    </p>
                                                </div>
                                            </div>
                                        </a>
                                    </li>
                                @empty
                                    <li class="notification-message">
                                        <div class="media d-flex">
                                            <div class="media-body flex-grow-1 text-center">
                                                <p class="noti-details text-muted">No notifications</p>
                                            </div>
                                        </div>
                                    </li>
                                @endforelse
                            </ul>
                        </div>
                        <div class="topnav-dropdown-footer">
                            <a href="{{ route('notifications.index') }}">View all Notifications</a>
                        </div>
                    </div>
                </li>

                <li class="nav-item zoom-screen me-2">
                    <a href="#" class="nav-link header-nav-list win-maximize">
                        <i class="fas fa-expand-arrows-alt"></i>
                    </a>
                </li>

                <li class="nav-item dropdown has-arrow new-user-menus">
                    <a href="#" class="dropdown-toggle nav-link" data-bs-toggle="dropdown">
                        <span class="user-img">
                            <div class="user-avatar-placeholder">
                                <i class="fas fa-user"></i>
                            </div>
                            <div class="user-text">
                                <h6>{{ auth()->user()->name }}</h6>
                                <p class="text-muted mb-0">{{ auth()->user()->role_name }}</p>
                            </div>
                        </span>
                    </a>
                    <div class="dropdown-menu">
                        <div class="user-header">
                            <div class="avatar avatar-sm">
                                <div class="user-avatar-placeholder">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="user-text">
                                <h6>{{ auth()->user()->name }}</h6>
                                <p class="text-muted mb-0">{{ auth()->user()->role_name }}</p>
                            </div>
                        </div>
                        <a class="dropdown-item" href="{{ route('user/profile/page') }}">My Profile</a>
                        <a class="dropdown-item" href="{{ route('notifications.index') }}">Notifications</a>
                        <a class="dropdown-item" href="{{ route('logout') }}">Logout</a>
                    </div>
                </li>
            </ul>
        </div>
		{{-- side bar --}}
		@include('sidebar.sidebar')
        <div class="sidebar-overlay"></div>
		{{-- content page --}}
        @yield('content')
        <footer>
            <div class="footer-content">
                <div class="footer-left">
                    <div class="footer-logo">
                        <img src="{{ asset('assets/img/Logo.jpg') }}" alt="Panorama Montessori School Logo" class="school-logo" width="40" height="40" loading="lazy">
                    </div>
                    <div class="footer-divider"></div>
                    <span class="system-name">Panorama Montessori School Portal</span>
                </div>
                <div class="footer-right">
                    <span class="motto">FOSTERING A PASSION</span>
                    <span class="motto-subtitle">for EXCELLENCE</span>
                </div>
            </div>
        </footer>
    
    </div>

    <script src="{{ URL::to('assets/js/jquery-3.6.0.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/toastr.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/feather.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/slimscroll/jquery.slimscroll.min.js') }}"></script>
    @if(request()->routeIs('home', 'dashboard'))
        <script src="{{ URL::to('assets/plugins/apexchart/apexcharts.min.js') }}"></script>
        <script src="{{ URL::to('assets/plugins/apexchart/chart-data.js') }}"></script>
    @endif
    <script src="{{ URL::to('assets/plugins/simple-calendar/jquery.simple-calendar.js') }}"></script>
    <script src="{{ URL::to('assets/js/calander.js') }}"></script>
    <script src="{{ URL::to('assets/js/circle-progress.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/moment/moment.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/bootstrap-datetimepicker.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/datatables/datatables.min.js') }}"></script>
    <script src="{{ URL::to('assets/plugins/select2/js/select2.min.js') }}"></script>
    <script src="{{ URL::to('assets/js/script.js') }}"></script>
    @yield('script')
    @stack('scripts')
    
    <style>
    /* Footer styling */
    footer {
        background: white;
        padding: 15px 20px;
        border-top: 1px solid #e9ecef;
        margin-top: auto;
        transition: all 0.3s ease;
    }
    
    .footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        max-width: 1200px;
        margin: 0 auto;
        flex-wrap: wrap;
        gap: 15px;
        transition: all 0.3s ease;
    }
    
    .footer-left {
        display: flex;
        align-items: center;
        gap: 15px;
        flex-wrap: wrap;
        transition: all 0.3s ease;
    }
    
    .footer-logo .school-logo {
        height: 40px;
        width: auto;
        max-width: 120px;
        object-fit: contain;
        transition: all 0.3s ease;
    }
    
    .footer-divider {
        width: 1px;
        height: 30px;
        background-color: #6c757d;
        margin: 0 10px;
        transition: all 0.3s ease;
    }
    
    .system-name {
        color: #495057;
        font-size: 16px;
        font-weight: 500;
        transition: all 0.3s ease;
    }
    
    .footer-right {
        text-align: right;
        flex-shrink: 0;
        transition: all 0.3s ease;
    }
    
    .motto {
        display: block;
        color: #495057;
        font-weight: bold;
        font-style: italic;
        font-size: 16px;
        margin-bottom: 2px;
        transition: all 0.3s ease;
    }
    
    .motto-subtitle {
        display: block;
        color: #495057;
        font-style: italic;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    /* Sidebar Open State - Footer adjusts to sidebar */
    body.sidebar-open footer {
        margin-left: 259px; /* Full sidebar width */
        width: calc(100% - 259px); /* Constrain width when sidebar is open */
        transition: all 0.3s ease;
    }
    
    /* Sidebar Closed State - Footer adjusts to mini sidebar */
    body.sidebar-closed footer {
        margin-left: 78px; /* Mini sidebar width */
        width: calc(100% - 78px); /* Adjust width for mini sidebar */
        transition: all 0.3s ease;
    }
    
    /* Ensure footer content expands properly */
    body.sidebar-closed .footer-content {
        max-width: calc(100% - 40px);
        padding: 0 20px;
    }
    
    body.sidebar-open .footer-content {
        max-width: calc(100% - 40px);
        padding: 0 20px;
    }
    
    /* Mobile responsive - footer takes full width */
    @media (max-width: 991px) {
        body.sidebar-open footer,
        body.sidebar-closed footer {
            margin-left: 0;
            width: 100%;
        }
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        /* Remove sidebar margin on mobile */
        body.sidebar-open footer,
        body.sidebar-closed footer {
            margin-left: 0;
        }
        
        .footer-content {
            flex-direction: column;
            text-align: center;
            gap: 20px;
        }
        
        .footer-left {
            flex-direction: column;
            gap: 10px;
        }
        
        .footer-divider {
            display: none;
        }
        
        .footer-right {
            text-align: center;
        }
        
        .system-name {
            font-size: 14px;
        }
        
        .motto {
            font-size: 14px;
        }
        
        .motto-subtitle {
            font-size: 12px;
        }
    }
    
    @media (max-width: 480px) {
        footer {
            padding: 15px 10px;
        }
        
        .footer-logo .school-logo {
            height: 35px;
            max-width: 100px;
        }
        
        .system-name {
            font-size: 13px;
        }
        
        .motto {
            font-size: 13px;
        }
        
        .motto-subtitle {
            font-size: 11px;
        }
    }
    
    /* Notification badge styling */
    .noti-dropdown .badge {
        position: absolute;
        top: -5px;
        right: -5px;
        font-size: 0.7rem;
        padding: 0.25rem 0.5rem;
        border-radius: 10px;
        background-color: #dc3545;
        color: white;
        border: 2px solid white;
    }
    
    .noti-dropdown {
        position: relative;
    }
    
    .notification-message.unread {
        background-color: #f8f9fa;
        border-left: 3px solid #007bff;
    }
    
    .notification-message.unread .noti-title {
        font-weight: 600;
    }
    
    /* User avatar placeholder styling */
    .user-avatar-placeholder {
        width: 31px;
        height: 31px;
        background-color: #f8f9fa;
        border: 2px solid #e9ecef;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #6c757d;
        font-size: 14px;
        transition: all 0.3s ease;
    }
    
    .user-avatar-placeholder:hover {
        background-color: #e9ecef;
        color: #495057;
        border-color: #dee2e6;
    }
    
    .avatar.avatar-sm .user-avatar-placeholder {
        width: 40px;
        height: 40px;
        font-size: 18px;
    }
    
    /* Remove old avatar styling */
    .user-img img {
        display: none;
    }
    
    .avatar-img {
        display: none;
    }
    </style>
    <script>
        $(document).ready(function() {
            $('.select2s-hidden-accessible').select2({
                closeOnSelect: false
            });
            
            // Footer responsive behavior based on sidebar state
            function updateFooterState() {
                const sidebar = $('#sidebar');
                const body = $('body');
                const footer = $('footer');
                
                // Check if sidebar is in mini-sidebar mode (collapsed)
                if (body.hasClass('mini-sidebar')) {
                    body.removeClass('sidebar-open').addClass('sidebar-closed');
                    footer.css({
                        'margin-left': '78px', // Mini sidebar width
                        'width': 'calc(100% - 78px)'
                    });
                } else {
                    body.removeClass('sidebar-closed').addClass('sidebar-open');
                    footer.css({
                        'margin-left': '259px', // Full sidebar width
                        'width': 'calc(100% - 259px)'
                    });
                }
            }
            
            // Initial state check
            updateFooterState();
            
            // Listen for sidebar toggle button clicks
            $(document).on('click', '#toggle_btn', function() {
                setTimeout(updateFooterState, 300); // Wait for animation to complete
            });
            
            // Listen for mobile sidebar toggle
            $(document).on('click', '#mobile_btn, .sidebar-overlay', function() {
                setTimeout(updateFooterState, 300); // Wait for animation to complete
            });
            
            // Listen for window resize
            $(window).on('resize', function() {
                updateFooterState();
            });
            
            // Listen for body class changes (for mini-sidebar)
            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    if (mutation.type === 'attributes' && mutation.attributeName === 'class') {
                        setTimeout(updateFooterState, 100);
                    }
                });
            });
            
            observer.observe(document.body, {
                attributes: true,
                attributeFilter: ['class']
            });
        });

        // Notification functions
        function markAsRead(notificationId) {
            $.ajax({
                url: '/notifications/' + notificationId + '/mark-as-read',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Update the notification count
                    updateNotificationCount();
                    // Reload the page to refresh notifications
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error marking notification as read:', error);
                }
            });
        }

        function markAllAsRead() {
            $.ajax({
                url: '/notifications/mark-all-as-read',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                success: function(response) {
                    // Update the notification count
                    updateNotificationCount();
                    // Reload the page to refresh notifications
                    location.reload();
                },
                error: function(xhr, status, error) {
                    console.error('Error marking all notifications as read:', error);
                }
            });
        }

        function updateNotificationCount() {
            $.ajax({
                url: '/notifications/unread-count',
                type: 'GET',
                success: function(response) {
                    const badge = $('.noti-dropdown .badge');
                    if (response.count > 0) {
                        if (badge.length) {
                            badge.text(response.count);
                        } else {
                            $('.noti-dropdown a').append('<span class="badge badge-danger">' + response.count + '</span>');
                        }
                    } else {
                        badge.remove();
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error updating notification count:', error);
                }
            });
        }

        // Auto-refresh notifications every 30 seconds
        setInterval(function() {
            updateNotificationCount();
        }, 30000);
    </script>
</body>
</html>