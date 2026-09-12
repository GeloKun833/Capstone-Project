@extends('layouts.master')
@section('content')

@php
    $roleLabels = [
        'Admin' => 'Admins',
        'Teacher' => 'Teachers',
        'Registrar' => 'Registrars',
        'Student' => 'Students',
        'Parent' => 'Parents',
    ];
    $roleBadge = [
        'Admin' => 'bg-danger',
        'Teacher' => 'bg-primary',
        'Registrar' => 'bg-warning text-dark',
        'Student' => 'bg-success',
        'Parent' => 'bg-info text-dark',
    ];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid p-0">
        <div class="row g-0 chat-shell">
            {{-- Contacts --}}
            <div class="col-md-4 col-lg-3 border-end chat-sidebar">
                <div class="p-3 border-bottom bg-white sticky-top">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="mb-0">
                            <i class="fas fa-comments text-primary me-2"></i>Chat
                        </h4>
                        <span class="badge bg-primary" id="total-unread">0</span>
                    </div>
                    @if(Auth::user()->role_name === 'Admin')
                        <p class="text-muted small mb-2 mb-md-3">You can message Teachers and Registrars only.</p>
                    @elseif(Auth::user()->role_name === 'Registrar')
                        <p class="text-muted small mb-2 mb-md-3">Chat with Admin and Teachers.</p>
                    @endif
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" class="form-control border-start-0 ps-0"
                               placeholder="Search contacts..." id="search-contacts">
                    </div>
                </div>

                <div class="contacts-list" id="contacts-list">
                    @forelse($grouped as $role => $items)
                        <div class="contact-group" data-role-group="{{ $role }}">
                            <div class="contact-group-title px-3 py-2 text-uppercase small fw-semibold text-muted">
                                {{ $roleLabels[$role] ?? $role }}
                                <span class="badge bg-light text-dark ms-1">{{ $items->count() }}</span>
                            </div>
                            @foreach($items as $conversation)
                                @php $u = $conversation['user']; @endphp
                                <div class="contact-item p-3 border-bottom"
                                     data-user-id="{{ $u->id }}"
                                     data-user-name="{{ $u->name }}"
                                     data-user-avatar="{{ $u->avatar ?? 'default-avatar.png' }}"
                                     data-user-role="{{ $u->role_name }}"
                                     onclick="loadConversation({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->avatar ?? 'default-avatar.png' }}', '{{ $u->role_name }}')">
                                    <div class="d-flex align-items-start">
                                        <div class="position-relative me-3">
                                            <img src="{{ asset('assets/img/profiles/' . ($u->avatar ?? 'default-avatar.png')) }}"
                                                 alt="{{ $u->name }}"
                                                 class="rounded-circle contact-avatar">
                                        </div>
                                        <div class="flex-grow-1 overflow-hidden">
                                            <div class="d-flex justify-content-between align-items-start mb-1 gap-2">
                                                <h6 class="mb-0 text-truncate">{{ $u->name }}</h6>
                                                @if($conversation['last_message'])
                                                    <small class="text-muted flex-shrink-0 last-time">{{ $conversation['last_message']->created_at->diffForHumans(null, true, true) }}</small>
                                                @endif
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center gap-2">
                                                <p class="text-muted mb-0 small text-truncate last-preview">
                                                    {{ $conversation['last_message']->content ?? 'No messages yet' }}
                                                </p>
                                                @if($conversation['unread_count'] > 0)
                                                    <span class="badge bg-primary rounded-pill unread-badge">{{ $conversation['unread_count'] }}</span>
                                                @endif
                                            </div>
                                            <span class="badge {{ $roleBadge[$u->role_name] ?? 'bg-secondary' }} mt-1">{{ $u->role_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="text-center py-5 px-3">
                            <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-1">No contacts available</p>
                            @if(Auth::user()->role_name === 'Admin')
                                <small class="text-muted">Create Teacher or Registrar accounts under User Management to start chatting.</small>
                            @else
                                <small class="text-muted">There is no one available to chat with yet.</small>
                            @endif
                        </div>
                    @endforelse
                </div>
            </div>

            {{-- Conversation pane --}}
            <div class="col-md-8 col-lg-9 d-flex flex-column chat-main">
                <div id="empty-state" class="flex-grow-1 d-flex align-items-center justify-content-center">
                    <div class="text-center px-4">
                        <i class="fas fa-comments fa-5x text-muted mb-4"></i>
                        <h4 class="text-muted">Select a conversation</h4>
                        <p class="text-muted mb-0">Choose a contact on the left to start messaging</p>
                    </div>
                </div>

                <div id="chat-container" class="d-none flex-grow-1 d-flex flex-column">
                    <div class="p-3 border-bottom bg-white" id="chat-header">
                        <div class="d-flex align-items-center">
                            <img src="" alt="Contact" id="contact-avatar" class="rounded-circle me-3 contact-avatar-sm">
                            <div class="flex-grow-1">
                                <h5 class="mb-0" id="contact-name">Contact Name</h5>
                                <small class="text-muted" id="contact-role-label"></small>
                            </div>
                            <button type="button" class="btn btn-sm btn-light rounded-circle" onclick="refreshChat()" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div class="flex-grow-1 p-3" id="messages-area"></div>

                    <div class="p-3 border-top bg-white">
                        <form id="message-form" onsubmit="sendMessage(event)">
                            <div class="input-group">
                                <textarea class="form-control"
                                          id="message-input"
                                          placeholder="Type a message..."
                                          rows="1"
                                          onkeydown="handleKeyPress(event)"></textarea>
                                <button class="btn btn-primary px-4" type="submit">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .chat-shell { height: calc(100vh - 60px); }
    .chat-sidebar { height: 100%; overflow-y: auto; background: #f8f9fa; }
    .chat-main { height: 100%; background: #fff; }
    .contact-group-title { background: #eef1f4; letter-spacing: .04em; }
    .contact-item { cursor: pointer; transition: background-color .15s; background: #fff; }
    .contact-item:hover { background-color: #f0f2f5 !important; }
    .contact-item.active { background-color: #e7f3ff !important; border-left: 3px solid #0d6efd; }
    .contact-avatar { width: 48px; height: 48px; object-fit: cover; }
    .contact-avatar-sm { width: 40px; height: 40px; object-fit: cover; }
    .message-bubble {
        max-width: 65%;
        word-wrap: break-word;
        margin-bottom: 10px;
        padding: 10px 15px;
        border-radius: 18px;
    }
    .message-bubble.sent {
        background: #0d6efd;
        color: #fff;
        margin-left: auto;
        border-bottom-right-radius: 4px;
    }
    .message-bubble.received {
        background: #e9ecef;
        color: #333;
        margin-right: auto;
        border-bottom-left-radius: 4px;
    }
    .message-time { font-size: 11px; margin-top: 4px; opacity: .75; }
    #messages-area {
        overflow-y: auto;
        max-height: calc(100vh - 240px);
        background: #f8f9fa;
    }
    #message-input { resize: none; max-height: 100px; }
    #messages-area::-webkit-scrollbar { width: 6px; }
    #messages-area::-webkit-scrollbar-thumb { background: #888; border-radius: 3px; }
</style>
@endpush

@push('scripts')
<script>
    let currentContactId = null;
    let messageRefreshInterval = null;
    const initialReceiverId = {{ (int) ($receiverId ?? 0) }};

    function loadConversation(userId, userName, userAvatar, userRole) {
        currentContactId = userId;

        $('#empty-state').addClass('d-none');
        $('#chat-container').removeClass('d-none').addClass('d-flex');
        $('#contact-name').text(userName);
        $('#contact-role-label').text(userRole || '');
        $('#contact-avatar').attr('src', '{{ asset("assets/img/profiles/") }}/' + (userAvatar || 'default-avatar.png'));

        $('.contact-item').removeClass('active');
        $('.contact-item[data-user-id="' + userId + '"]').addClass('active');

        loadMessages(userId);

        if (messageRefreshInterval) clearInterval(messageRefreshInterval);
        messageRefreshInterval = setInterval(function () { loadMessages(userId, true); }, 3000);
    }

    function loadMessages(userId, silent) {
        silent = !!silent;
        if (!silent) {
            $('#messages-area').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>');
        }

        $.ajax({
            url: '/chat/conversation/' + userId,
            type: 'GET',
            success: function (response) {
                const messagesArea = $('#messages-area');
                const el = messagesArea[0];
                const isScrolledToBottom = el.scrollHeight - el.scrollTop - messagesArea.height() < 100;

                if (!response.messages || response.messages.length === 0) {
                    messagesArea.html('<div class="text-center py-5"><i class="fas fa-comments fa-3x text-muted mb-3"></i><p class="text-muted">No messages yet. Start the conversation!</p></div>');
                } else {
                    messagesArea.empty();
                    response.messages.forEach(function (message) {
                        appendMessage(message);
                    });
                    if (isScrolledToBottom || !silent) scrollToBottom();
                }

                const item = $('.contact-item[data-user-id="' + userId + '"]');
                item.find('.unread-badge').remove();
                updateUnreadCount();
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to load messages';
                if (typeof toastr !== 'undefined') toastr.error(msg);
            }
        });
    }

    function appendMessage(message) {
        const messageClass = message.is_own ? 'sent' : 'received';
        const alignClass = message.is_own ? 'text-end' : 'text-start';
        const html = '<div class="' + alignClass + ' mb-2">' +
            '<div class="message-bubble ' + messageClass + '">' +
            '<div>' + escapeHtml(message.content) + '</div>' +
            '<div class="message-time">' + escapeHtml(message.created_at_human) + '</div>' +
            '</div></div>';
        $('#messages-area').append(html);
    }

    function sendMessage(event) {
        event.preventDefault();
        const content = $('#message-input').val().trim();
        if (!content || !currentContactId) return;

        const $btn = $('#message-form button[type="submit"]').prop('disabled', true);

        $.ajax({
            url: '/chat/send',
            type: 'POST',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: { recipient_id: currentContactId, content: content },
            success: function (response) {
                if (response.success) {
                    $('#message-input').val('').css('height', 'auto');
                    // Clear empty-state placeholder if present
                    if ($('#messages-area .fa-comments').length) {
                        $('#messages-area').empty();
                    }
                    appendMessage(response.message);
                    scrollToBottom();
                    updateContactLastMessage(currentContactId, content);
                } else if (typeof toastr !== 'undefined') {
                    toastr.error(response.message || 'Failed to send message');
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : 'Failed to send message';
                if (typeof toastr !== 'undefined') toastr.error(msg);
            },
            complete: function () {
                $btn.prop('disabled', false);
            }
        });
    }

    function handleKeyPress(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage(event);
        }
    }

    function scrollToBottom() {
        const messagesArea = $('#messages-area');
        messagesArea.animate({ scrollTop: messagesArea[0].scrollHeight }, 250);
    }

    function refreshChat() {
        if (currentContactId) {
            loadMessages(currentContactId);
            if (typeof toastr !== 'undefined') toastr.success('Chat refreshed');
        }
    }

    function updateUnreadCount() {
        $.ajax({
            url: '/chat/unread-count',
            type: 'GET',
            success: function (response) {
                $('#total-unread').text(response.count || 0);
                if (response.count > 0) {
                    $('#total-unread').removeClass('d-none');
                } else {
                    $('#total-unread').addClass('d-none');
                }
            }
        });
    }

    function updateContactLastMessage(contactId, message) {
        const contactItem = $('.contact-item[data-user-id="' + contactId + '"]');
        contactItem.find('.last-preview').text(message);
        contactItem.find('.last-time').text('Just now');
        const group = contactItem.closest('.contact-group');
        contactItem.prependTo(group);
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text == null ? '' : String(text);
        return div.innerHTML;
    }

    $('#search-contacts').on('input', function () {
        const search = $(this).val().toLowerCase().trim();
        $('.contact-item').each(function () {
            const name = ($(this).data('user-name') || $(this).find('h6').text()).toString().toLowerCase();
            const role = ($(this).data('user-role') || '').toString().toLowerCase();
            $(this).toggle(!search || name.includes(search) || role.includes(search));
        });
        $('.contact-group').each(function () {
            const visible = $(this).find('.contact-item:visible').length;
            $(this).toggle(visible > 0 || !search);
        });
    });

    $('#message-input').on('input', function () {
        this.style.height = 'auto';
        this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    $(document).ready(function () {
        updateUnreadCount();
        setInterval(updateUnreadCount, 30000);

        if (initialReceiverId) {
            const item = $('.contact-item[data-user-id="' + initialReceiverId + '"]');
            if (item.length) {
                loadConversation(
                    initialReceiverId,
                    item.data('user-name'),
                    item.data('user-avatar'),
                    item.data('user-role')
                );
            }
        }
    });

    $(window).on('beforeunload', function () {
        if (messageRefreshInterval) clearInterval(messageRefreshInterval);
    });
</script>
@endpush
@endsection
