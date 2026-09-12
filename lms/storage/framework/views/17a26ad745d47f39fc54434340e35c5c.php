
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e(config('app.name', 'Laravel')); ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="<?php echo e(URL::to('assets/plugins/bootstrap/css/bootstrap.min.css')); ?>">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="<?php echo e(URL::to('assets/plugins/fontawesome/css/all.min.css')); ?>">
    <!-- Toastr CSS (local assets — avoids CDN/mixed-content failures in production) -->
    <link rel="stylesheet" href="<?php echo e(URL::to('assets/css/toastr.min.css')); ?>">
    
    <style>
        :root {
            --auth-navy: #0f2744;
            --auth-navy-light: #1a3a5c;
            --auth-blue: #2563eb;
            --auth-blue-hover: #1d4ed8;
            --auth-danger: #dc2626;
            --auth-text: #0f172a;
            --auth-text-muted: #64748b;
            --auth-border: #e2e8f0;
            --auth-surface: #ffffff;
            --auth-radius: 16px;
            --auth-shadow: 0 25px 50px -12px rgba(15, 39, 68, 0.35);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            background: url('<?php echo e(URL::to("assets/img/background.png")); ?>') no-repeat center center fixed;
            background-size: cover;
            min-height: 100vh;
            position: relative;
            -webkit-font-smoothing: antialiased;
        }
        
        body::before {
            content: '';
            position: fixed;
            inset: 0;
            background: linear-gradient(135deg, rgba(15, 39, 68, 0.82) 0%, rgba(15, 39, 68, 0.55) 50%, rgba(0, 0, 0, 0.45) 100%);
            z-index: 1;
        }
        
        .main-content {
            position: relative;
            z-index: 2;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }

        /* ── Split login layout ── */
        .auth-shell {
            display: grid;
            grid-template-columns: minmax(0, 440px) minmax(0, 1fr);
            width: 100%;
            max-width: 1080px;
            min-height: min(640px, calc(100vh - 48px));
            border-radius: 28px;
            overflow: hidden;
            box-shadow: var(--auth-shadow);
            animation: authFadeIn 0.55s ease-out;
        }

        @keyframes authFadeIn {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .auth-form-panel {
            display: flex;
            align-items: center;
            justify-content: center;
            background: var(--auth-surface);
            padding: 48px 40px;
        }

        .login-card {
            width: 100%;
            max-width: 360px;
        }

        .login-card__header {
            text-align: center;
            margin-bottom: 32px;
        }

        .login-card__logo {
            width: 72px;
            height: 72px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid var(--auth-border);
            box-shadow: 0 4px 14px rgba(15, 39, 68, 0.12);
            margin-bottom: 20px;
        }

        .login-card__title {
            font-size: 1.625rem;
            font-weight: 700;
            color: var(--auth-text);
            letter-spacing: -0.02em;
            margin-bottom: 6px;
        }

        .login-card__subtitle {
            font-size: 0.9375rem;
            color: var(--auth-text-muted);
            line-height: 1.5;
        }

        .login-card__footer {
            text-align: center;
            font-size: 0.8125rem;
            color: var(--auth-text-muted);
            margin-top: 4px;
        }

        .login-form .form-label {
            display: block;
            font-size: 0.875rem;
            font-weight: 600;
            color: var(--auth-text);
            margin-bottom: 8px;
        }

        .form-label-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 8px;
        }

        .form-label-row .form-label {
            margin-bottom: 0;
        }

        .form-link {
            font-size: 0.8125rem;
            font-weight: 500;
            color: var(--auth-blue);
            text-decoration: none;
            transition: color 0.2s ease;
        }

        .form-link:hover {
            color: var(--auth-blue-hover);
            text-decoration: underline;
        }

        .form-link--inline {
            flex-shrink: 0;
        }

        .input-field {
            position: relative;
        }

        .input-field__icon {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 0.9375rem;
            pointer-events: none;
            z-index: 2;
        }

        .input-field__toggle {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #94a3b8;
            cursor: pointer;
            padding: 6px;
            border-radius: 8px;
            line-height: 1;
            transition: color 0.2s ease, background 0.2s ease;
            z-index: 2;
        }

        .input-field__toggle:hover {
            color: var(--auth-blue);
            background: rgba(37, 99, 235, 0.08);
        }

        .input-field__toggle:focus-visible {
            outline: 2px solid var(--auth-blue);
            outline-offset: 2px;
        }

        .login-form .form-control {
            width: 100%;
            padding: 13px 44px 13px 44px;
            font-size: 0.9375rem;
            color: var(--auth-text);
            background: #f8fafc;
            border: 1.5px solid var(--auth-border);
            border-radius: var(--auth-radius);
            transition: border-color 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
        }

        .login-form .form-control::placeholder {
            color: #94a3b8;
        }

        .login-form .form-control:hover {
            border-color: #cbd5e1;
        }

        .login-form .form-control:focus {
            outline: none;
            background: var(--auth-surface);
            border-color: var(--auth-blue);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .login-form .form-control.is-invalid {
            border-color: var(--auth-danger);
            box-shadow: 0 0 0 4px rgba(220, 38, 38, 0.1);
        }

        .login-form .invalid-feedback {
            display: block;
            color: var(--auth-danger);
            font-size: 0.8125rem;
            margin-top: 6px;
        }

        .login-form .form-group {
            margin-bottom: 20px;
        }

        .login-form .form-group--compact {
            margin-bottom: 24px;
        }

        .login-form .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            user-select: none;
        }

        .login-form .form-check-input {
            width: 18px;
            height: 18px;
            margin: 0;
            border: 1.5px solid #cbd5e1;
            border-radius: 5px;
            cursor: pointer;
            accent-color: var(--auth-blue);
        }

        .login-form .form-check-input:focus {
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.12);
        }

        .login-form .form-check-label {
            font-size: 0.875rem;
            color: var(--auth-text-muted);
        }

        .btn-sign-in {
            position: relative;
            width: 100%;
            padding: 14px 24px;
            font-size: 1rem;
            font-weight: 600;
            color: #fff;
            background: linear-gradient(135deg, #3b82f6 0%, var(--auth-blue) 50%, var(--auth-blue-hover) 100%);
            border: none;
            border-radius: var(--auth-radius);
            cursor: pointer;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            box-shadow: 0 4px 14px rgba(37, 99, 235, 0.35);
        }

        .btn-sign-in:hover:not(:disabled) {
            transform: translateY(-1px);
            box-shadow: 0 8px 20px rgba(37, 99, 235, 0.4);
        }

        .btn-sign-in:active:not(:disabled) {
            transform: translateY(0);
        }

        .btn-sign-in:focus-visible {
            outline: 2px solid var(--auth-blue);
            outline-offset: 3px;
        }

        .btn-sign-in:disabled {
            opacity: 0.85;
            cursor: not-allowed;
        }

        .btn-sign-in__text i {
            margin-right: 8px;
        }

        .btn-sign-in__loading {
            display: none;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-sign-in.is-loading .btn-sign-in__text {
            display: none;
        }

        .btn-sign-in.is-loading .btn-sign-in__loading {
            display: inline-flex;
        }

        /* Brand panel */
        .auth-brand-panel {
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 48px 40px;
            background: linear-gradient(160deg, var(--auth-navy) 0%, var(--auth-navy-light) 55%, #0c1f35 100%);
            overflow: hidden;
        }

        .auth-brand-panel::before {
            content: '';
            position: absolute;
            width: 420px;
            height: 420px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.18) 0%, transparent 70%);
            top: -80px;
            right: -80px;
            pointer-events: none;
        }

        .auth-brand-panel::after {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 255, 255, 0.04) 0%, transparent 70%);
            bottom: -60px;
            left: -60px;
            pointer-events: none;
        }

        .auth-brand-panel__content {
            position: relative;
            z-index: 1;
            max-width: 420px;
            color: #fff;
        }

        .auth-brand-panel__eyebrow {
            font-size: 0.75rem;
            font-weight: 600;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.55);
            margin-bottom: 20px;
        }

        .auth-brand-panel__name {
            font-size: clamp(1.75rem, 3vw, 2.25rem);
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -0.02em;
            margin-bottom: 20px;
        }

        .auth-brand-panel__address {
            font-size: 0.9375rem;
            line-height: 1.7;
            color: rgba(255, 255, 255, 0.75);
            margin-bottom: 32px;
        }

        .auth-brand-panel__divider {
            width: 48px;
            height: 3px;
            background: linear-gradient(90deg, #3b82f6, rgba(59, 130, 246, 0.2));
            border-radius: 2px;
            margin-bottom: 28px;
        }

        .auth-brand-panel__tagline {
            margin-bottom: 12px;
        }

        .auth-brand-panel__tagline-prefix {
            display: block;
            font-size: 0.8125rem;
            font-weight: 500;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.6);
            margin-bottom: 4px;
        }

        .auth-brand-panel__tagline-accent {
            display: block;
            font-family: 'Playfair Display', Georgia, serif;
            font-size: clamp(1.75rem, 3vw, 2.125rem);
            font-style: italic;
            font-weight: 600;
            line-height: 1.2;
        }

        .auth-brand-panel__motto {
            font-size: 0.9375rem;
            font-style: italic;
            color: rgba(255, 255, 255, 0.65);
        }

        @media (max-width: 900px) {
            .auth-shell {
                grid-template-columns: 1fr;
                max-width: 440px;
                min-height: auto;
            }

            .auth-brand-panel {
                display: none;
            }

            .auth-form-panel {
                padding: 40px 32px;
            }
        }

        @media (max-width: 480px) {
            .main-content {
                padding: 16px;
            }

            .auth-shell {
                border-radius: 20px;
            }

            .auth-form-panel {
                padding: 32px 24px;
            }

            .login-card__title {
                font-size: 1.5rem;
            }
        }

        /* ── Legacy glass form (other auth pages) ── */
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
            transition: all 0.3s ease !important;
            animation: authFadeIn 0.6s ease-out;
        }

        .glass-form:hover {
            box-shadow:
                0 12px 40px rgba(31, 38, 135, 0.45),
                0 6px 20px rgba(0, 0, 0, 0.15),
                inset 0 1px 0 rgba(255, 255, 255, 0.5) !important;
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
        
        .login-danger {
            color: var(--auth-danger);
            font-weight: 600;
        }

        /* Form styling (legacy glass pages) */
        .glass-form .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        
        .glass-form .form-group label {
            color: white;
            font-weight: 600;
            margin-bottom: 8px;
            display: block;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        .glass-form .form-control {
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
        
        .glass-form .form-control::placeholder {
            color: rgba(255, 255, 255, 0.7);
            font-weight: 300;
        }
        
        .glass-form .form-control:focus {
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
        
        .glass-form .form-control.is-invalid {
            border-color: #ff6b6b;
            box-shadow: 0 0 15px rgba(255, 107, 107, 0.3);
        }
        
        /* Input with icon wrapper */
        .glass-form .input-with-icon {
            position: relative;
        }
        
        /* Left icon (envelope, lock, etc) - More visible */
        .glass-form .input-icon-left {
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
        .glass-form .input-icon-right {
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
        
        .glass-form .input-icon-right:hover {
            color: #60a5fa;
            transform: translateY(-50%) scale(1.15);
            opacity: 1;
        }
        
        .glass-form .input-icon-right:active {
            transform: translateY(-50%) scale(0.95);
        }
        
        /* Select styling - Fixed */
        .glass-form select.form-control {
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
        .glass-form .btn-primary {
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
        
        .glass-form .btn-primary::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s ease;
        }
        
        .glass-form .btn-primary:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 
                0 10px 30px rgba(37, 99, 235, 0.6),
                0 4px 12px rgba(0, 0, 0, 0.2),
                inset 0 1px 0 rgba(255, 255, 255, 0.3);
            background-position: 100% 0%;
        }
        
        .glass-form .btn-primary:hover::before {
            left: 100%;
        }
        
        .glass-form .btn-primary:active {
            transform: translateY(-1px) scale(0.98);
            box-shadow: 
                0 4px 15px rgba(37, 99, 235, 0.5),
                0 2px 6px rgba(0, 0, 0, 0.15);
        }
        
        /* Links - Enhanced */
        .glass-form a {
            color: rgba(255, 255, 255, 0.92);
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            position: relative;
        }
        
        .glass-form a:hover {
            color: white;
            text-shadow: 0 0 12px rgba(255, 255, 255, 0.6);
        }
        
        /* Forgot Password link */
        .glass-form .d-flex a {
            font-size: 0.92rem;
            font-weight: 500;
        }
        
        .glass-form .d-flex a::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 0;
            height: 2px;
            background: rgba(255, 255, 255, 0.8);
            transition: width 0.3s ease;
        }
        
        .glass-form .d-flex a:hover::after {
            width: 100%;
        }
        
        /* Bottom text */
        .glass-form .text-center p {
            font-size: 0.9rem;
            font-weight: 400;
            letter-spacing: 0.2px;
        }
        
        /* Error messages */
        .glass-form .invalid-feedback {
            color: #ff6b6b;
            font-size: 0.85rem;
            margin-top: 5px;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.5);
        }
        
        /* Checkbox styling - Enhanced */
        .glass-form .form-check-input {
            background-color: rgba(255, 255, 255, 0.15);
            border: 2px solid rgba(255, 255, 255, 0.3);
            border-radius: 6px;
            width: 20px;
            height: 20px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
        }
        
        .glass-form .form-check-input:hover {
            background-color: rgba(255, 255, 255, 0.22);
            border-color: rgba(255, 255, 255, 0.4);
            transform: scale(1.05);
        }
        
        .glass-form .form-check-input:checked {
            background-color: #3b82f6;
            border-color: #3b82f6;
            box-shadow: 
                0 0 0 3px rgba(59, 130, 246, 0.2),
                0 2px 8px rgba(37, 99, 235, 0.3);
        }
        
        .glass-form .form-check-input:focus {
            box-shadow: 
                0 0 0 4px rgba(59, 130, 246, 0.25),
                0 2px 8px rgba(0, 0, 0, 0.15);
            outline: none;
        }
        
        .glass-form .form-check-label {
            color: rgba(255, 255, 255, 0.95) !important;
            font-size: 0.92rem;
            font-weight: 400;
            cursor: pointer;
            user-select: none;
            transition: color 0.3s ease;
        }
        
        .glass-form .form-check-label:hover {
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
            
            .glass-form .form-control {
                padding: 14px 18px 14px 48px;
                font-size: 0.95rem;
            }
            
            .glass-form .input-icon-left {
                left: 16px;
                font-size: 1.05rem;
            }
            
            .glass-form .input-icon-right {
                right: 16px;
                font-size: 1.05rem;
            }
            
            .glass-form .btn-primary {
                padding: 14px 28px;
                font-size: 1rem;
            }
            
            .glass-form .form-check-label,
            .glass-form .d-flex a {
                font-size: 0.88rem;
            }
            
            .glass-form .text-center p {
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
            
            .glass-form .form-control {
                padding: 13px 16px 13px 45px;
            }
        }
        
        /* Loading state */
        .glass-form .btn-primary:disabled {
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
        <?php echo $__env->yieldContent('content'); ?>
    </div>
    
    <!-- jQuery -->
    <script src="<?php echo e(URL::to('assets/js/jquery-3.6.0.min.js')); ?>"></script>
    <!-- Bootstrap JS -->
    <script src="<?php echo e(URL::to('assets/plugins/bootstrap/js/bootstrap.bundle.min.js')); ?>"></script>
    <!-- Toastr JS -->
    <script src="<?php echo e(URL::to('assets/js/toastr.min.js')); ?>"></script>
    <?php echo $__env->make('partials.toastr-scripts', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?>
    
    <script>
        $(document).ready(function() {
            $('.toggle-password, .reg-toggle-password').on('click', function(e) {
                e.preventDefault();

                const $toggle = $(this);
                const $input = $toggle.siblings('input').first();

                if (!$input.length) {
                    return;
                }

                const showPassword = $input.attr('type') === 'password';
                $input.attr('type', showPassword ? 'text' : 'password');

                const $icon = $toggle.find('i').length ? $toggle.find('i') : $toggle;
                $icon.toggleClass('fa-eye', !showPassword);
                $icon.toggleClass('fa-eye-slash', showPassword);

                if ($toggle.is('button')) {
                    $toggle.attr('aria-pressed', showPassword ? 'true' : 'false');
                    $toggle.attr('aria-label', showPassword ? 'Hide password' : 'Show password');
                }
            });

            $('.form-control').on('input', function() {
                if ($(this).hasClass('is-invalid')) {
                    $(this).removeClass('is-invalid');
                }
            });
        });
    </script>
</body>
</html>
<?php /**PATH C:\Laravel\Capstone-Project\lms\resources\views/layouts/app.blade.php ENDPATH**/ ?>