
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ config('app.name', 'Laravel') }}</title>
    
    <!-- Google Fonts - Poppins -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Toastr CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: url('{{ URL::to("assets/img/background.png") }}') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            position: relative;
        }
        
        /* Full background overlay */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.35);
            z-index: 1;
        }
        
        /* Main content container */
        .main-content {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: flex-start;
            padding: 20px;
        }
        
        /* Glassmorphic form container - Enhanced */
        .glass-form {
            background: rgba(255, 255, 255, 0.15) !important;
            backdrop-filter: blur(30px) saturate(180%) !important;
            -webkit-backdrop-filter: blur(30px) saturate(180%) !important;
            border: 1.5px solid rgba(255, 255, 255, 0.25) !important;
            border-radius: 24px !important;
            padding: 45px 40px !important;
            box-shadow: 
                0 8px 32px rgba(31, 38, 135, 0.37),
                0 4px 16px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.4) !important;
            width: 100% !important;
            max-width: 460px !important;
            position: relative !important;
            margin-left: 50px !important;
            transition: all 0.3s ease !important;
        }
        
        .glass-form:hover {
            box-shadow: 
                0 12px 40px rgba(31, 38, 135, 0.45),
                0 6px 20px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.5) !important;
        }
        
        /* Override any existing white backgrounds */
        .glass-form * {
            background: transparent !important;
        }
        
        .glass-form input,
        .glass-form select,
        .glass-form textarea {
            background: rgba(255, 255, 255, 0.15) !important;
        }
        
        /* School logo at top */
        .school-logo-top {
            text-align: center;
            margin-bottom: 35px;
            animation: fadeInDown 0.8s ease-out;
        }
        
        @keyframes fadeInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .school-logo-top img {
            width: 90px;
            height: 90px;
            border-radius: 50%;
            border: 4px solid rgba(255, 255, 255, 0.4);
            box-shadow: 
                0 8px 25px rgba(0, 0, 0, 0.3),
                0 0 0 8px rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            object-fit: cover;
        }
        
        .school-logo-top img:hover {
            transform: scale(1.05);
            box-shadow: 
                0 12px 35px rgba(0, 0, 0, 0.4),
                0 0 0 12px rgba(255, 255, 255, 0.15);
        }
        
        .school-name {
            color: white;
            font-size: 1.85rem;
            font-weight: 700;
            margin: 18px 0 8px 0;
            text-shadow: 
                2px 2px 8px rgba(0, 0, 0, 0.6),
                0 0 20px rgba(255, 255, 255, 0.2);
            letter-spacing: -0.5px;
        }
        
        .school-motto {
            color: rgba(255, 255, 255, 0.95);
            font-size: 0.95rem;
            font-weight: 400;
            font-style: italic;
            text-shadow: 1px 1px 4px rgba(0, 0, 0, 0.5);
            letter-spacing: 0.3px;
        }
        
        /* Form styling */
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .form-group label {
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .login-danger {
            color: #ff6b6b;
            font-weight: 700;
        }
        
        .form-control {
            background: rgba(255, 255, 255, 0.15);
            border: 1.5px solid rgba(255, 255, 255, 0.3);
            border-radius: 14px;
            padding: 16px 22px 16px 52px;
            color: white;
            font-size: 1rem;
            font-weight: 400;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
            width: 100%;
            box-shadow: 
                0 2px 8px rgba(0, 0, 0, 0.1),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
        }
        
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
        }
        
        .form-control:focus {
            background: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.5);
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.15),
                0 4px 16px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            outline: none;
            color: white;
            transform: translateY(-1px);
        }
        
        .form-control.is-invalid {
            border-color: #ff6b6b;
            box-shadow: 0 0 15px rgba(255, 107, 107, 0.3);
        }
        
        /* Input with icon wrapper */
        .input-with-icon {
            position: relative;
        }
        
        /* Left icon (envelope, lock, etc) - More visible */
        .input-icon-left {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 1.2rem;
            z-index: 10;
            pointer-events: none;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            opacity: 0.95;
        }
        
        /* Right icon (eye toggle) - More visible and clickable */
        .input-icon-right {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: white;
            font-size: 1.2rem;
            z-index: 10;
            cursor: pointer;
            transition: all 0.3s ease;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.5);
            opacity: 0.95;
            padding: 5px;
        }
        
        .input-icon-right:hover {
            color: #60a5fa;
            transform: translateY(-50%) scale(1.15);
            opacity: 1;
        }
        
        .input-icon-right:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        /* Select styling - Fixed */
        select.form-control {
            padding-left: 50px;
            padding-right: 40px;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%23ffffff' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='m6 8 4 4 4-4'/%3e%3c/svg%3e");
            background-position: right 15px center;
            background-repeat: no-repeat;
            background-size: 1.2em 1.2em;
            appearance: none;
            -webkit-appearance: none;
            -moz-appearance: none;
        }
        
        /* Button styling - Enhanced gradient */
        .btn-primary {
            background: linear-gradient(135deg, #3b82f6 0%, #2563eb 50%, #1d4ed8 100%);
            background-size: 200% 100%;
            background-position: 0% 0%;
            border: none;
            border-radius: 14px;
            padding: 16px 32px;
            font-weight: 600;
            font-size: 1.05rem;
            color: white;
            width: 100%;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 
                0 6px 20px rgba(37, 99, 235, 0.45),
                0 2px 8px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.2);
            letter-spacing: 0.5px;
            position: relative;
            overflow: hidden;
        }
        
        .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 10px 30px rgba(37, 99, 235, 0.6),
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            background-position: 100% 0%;
        }
        
        .btn-primary:hover::before {
            left: 100%;
        }
        
        .btn-primary:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: 
                0 4px 15px rgba(37, 99, 235, 0.5),
                0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        /* Links - Enhanced */
        a {
            color: rgba(255, 255, 255, 0.92);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        a:hover {
            color: white;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.6);
        }
        
        /* Forgot Password link */
        .d-flex a {
            font-size: 0.92rem;
            font-weight: 500;
        }
        
        .d-flex a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.8);
            transition: width 0.3s ease;
        }
        
        .d-flex a:hover::after {
            width: 100%;
        }
        
        /* Bottom text */
        .text-center p {
            font-size: 0.9rem;
            font-weight: 400;
            letter-spacing: 0.2px;
        }
        
        /* Error messages */
        .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        /* Checkbox styling - Enhanced */
        .form-check-input {
            background-color: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .form-check-input:hover {
            background-color: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }
        
        .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.2),
                0 2px 8px rgba(37, 99, 235, 0.3);
        }
        
        .form-check-input:focus {
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.25),
                0 2px 8px rgba(0, 0, 0, 0.15);
            outline: none;
        }
        
        .form-check-label {
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: 0.92rem;
            font-weight: 400;
            cursor: pointer;
            user-select: none;
            transition: color 0.3s ease;
        }
        
        .form-check-label:hover {
            color: white !important;
        }
        
        /* Responsive design - Mobile optimization */
        @media (max-width: 768px) {
            .glass-form {
                margin: 20px;
                padding: 35px 28px;
                margin-left: 20px;
                max-width: 100%;
                border-radius: 20px !important;
            }
            
            .school-logo-top {
                margin-bottom: 28px;
            }
            
            .school-logo-top img {
                width: 75px;
                height: 75px;
            }
            
            .school-name {
                font-size: 1.55rem;
            }
            
            .school-motto {
                font-size: 0.88rem;
            }
            
            .form-control {
                padding: 14px 18px 14px 48px;
                font-size: 0.95rem;
            }
            
            .input-icon-left {
                left: 16px;
                font-size: 1.05rem;
            }
            
            .input-icon-right {
                right: 16px;
                font-size: 1.05rem;
            }
            
            .btn-primary {
                padding: 14px 28px;
                font-size: 1rem;
            }
            
            .form-check-label,
            .d-flex a {
                font-size: 0.88rem;
            }
            
            .text-center p {
                font-size: 0.85rem;
            }
        }
        
        @media (max-width: 480px) {
            .glass-form {
                padding: 30px 22px;
                margin: 15px;
                margin-left: 15px;
            }
            
            .school-name {
                font-size: 1.4rem;
            }
            
            .form-control {
                padding: 13px 16px 13px 45px;
            }
        }
        
        /* Animation for form appearance */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .glass-form {
            animation: fadeInUp 0.6s ease-out;
        }
        
        /* Loading state */
        .btn-primary:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }
        
        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
        }
        
        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 4px;
        }
        
        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>
</head>
<body>
    <div class="main-content">
        @yield('content')
    </div>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Toastr JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    
    <script>
        // Password toggle functionality
        $(document).ready(function() {
            $('.toggle-password, .reg-toggle-password').click(function() {
                const input = $(this).siblings('input');
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).toggleClass('fa-eye fa-eye-slash');
            });
            
            // Form validation enhancement
            $('.form-control').on('input', function() {
                if ($(this).hasClass('is-invalid')) {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
