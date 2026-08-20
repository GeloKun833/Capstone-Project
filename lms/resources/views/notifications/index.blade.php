@extends('layouts.master')
@section('content')
    {{-- message --}}
    {!! Toastr::message() !!}
    <div class="page-wrapper">
        <div class="content container-fluid">
            <div class="page-header">
                <div class="row align-items-center">
                    <div class="col">
                        <h3 class="page-title">Notifications</h3>
                        <ul class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}">Dashboard</a></li>
                            <li class="breadcrumb-item active">Notifications</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-12">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">All Notifications</h4>
                        </div>
                        <div class="card-body">
                            @if(isset($notifications) && $notifications->count() > 0)
                                <div class="notification-list">
                                    @foreach($notifications as $notification)
                                        <div class="notification-item {{ $notification->read_at ? '' : 'unread' }}">
                                            <div class="notification-content">
                                                <div class="notification-icon">
                                                    <i class="fas fa-info-circle text-primary"></i>
                                                </div>
                                                <div class="notification-details">
                                                    <h6>{{ $notification->data['title'] ?? 'Notification' }}</h6>
                                                    <p>{{ $notification->data['message'] ?? 'You have a new notification' }}</p>
                                                    <small class="text-muted">{{ $notification->created_at->diffForHumans() }}</small>
                                                </div>
                                                @if(!$notification->read_at)
                                                    <div class="notification-actions">
                                                        <button class="btn btn-sm btn-primary" onclick="markAsRead('{{ $notification->id }}')">
                                                            Mark as Read
                                                        </button>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="d-flex justify-content-center mt-4">
                                    {{ $notifications->links() }}
                                </div>
                            @else
                                <div class="text-center py-4">
                                    <i class="fas fa-bell fa-3x text-muted mb-3"></i>
                                    <h5>No notifications</h5>
                                    <p class="text-muted">You don't have any notifications yet.</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
    .notification-item {
        border: 1px solid #e9ecef;
        border-radius: 8px;
        margin-bottom: 15px;
        padding: 15px;
        transition: all 0.3s ease;
    }

    .notification-item:hover {
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }

    .notification-item.unread {
        background-color: #f8f9fa;
        border-left: 4px solid #007bff;
    }

    .notification-content {
        display: flex;
        align-items: center;
        gap: 15px;
    }

    .notification-icon {
        font-size: 1.5rem;
        width: 40px;
        text-align: center;
    }

    .notification-details {
        flex: 1;
    }

    .notification-details h6 {
        margin-bottom: 5px;
        color: #333;
    }

    .notification-details p {
        margin-bottom: 5px;
        color: #666;
    }

    .notification-actions {
        margin-left: auto;
    }
    </style>
@endsection 