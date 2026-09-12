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
        'Admin' => 'chat-role-admin',
        'Teacher' => 'chat-role-teacher',
        'Registrar' => 'chat-role-registrar',
        'Student' => 'chat-role-student',
        'Parent' => 'chat-role-parent',
    ];
@endphp

<div class="page-wrapper">
    <div class="content container-fluid chat-page">
        <div class="chat-shell">
            {{-- Contacts --}}
            <aside class="chat-sidebar">
                <div class="chat-sidebar-head">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h4 class="chat-title mb-0">
                            <i class="far fa-comments me-2"></i>Chat
                        </h4>
                        <span class="chat-unread-pill d-none" id="total-unread">0</span>
                    </div>
                    @if(Auth::user()->role_name === 'Admin')
                        <p class="chat-hint">You can message Teachers and Registrars only.</p>
                    @elseif(Auth::user()->role_name === 'Registrar')
                        <p class="chat-hint">Chat with Admin and Teachers.</p>
                    @endif
                    <div class="chat-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Search contacts..." id="search-contacts">
                    </div>
                </div>

                <div class="contacts-list" id="contacts-list">
                    @forelse($grouped as $role => $items)
                        <div class="contact-group" data-role-group="{{ $role }}">
                            <div class="contact-group-title">
                                {{ $roleLabels[$role] ?? $role }}
                                <span>{{ $items->count() }}</span>
                            </div>
                            @foreach($items as $conversation)
                                @php $u = $conversation['user']; @endphp
                                <div class="contact-item"
                                     data-user-id="{{ $u->id }}"
                                     data-user-name="{{ $u->name }}"
                                     data-user-avatar="{{ $u->avatar ?? 'default-avatar.png' }}"
                                     data-user-role="{{ $u->role_name }}"
                                     onclick="loadConversation({{ $u->id }}, '{{ addslashes($u->name) }}', '{{ $u->avatar ?? 'default-avatar.png' }}', '{{ $u->role_name }}')">
                                    <div class="contact-row">
                                        <div class="contact-avatar-wrap">
                                            <img src="{{ asset('assets/img/profiles/' . ($u->avatar ?? 'default-avatar.png')) }}"
                                                 alt="{{ $u->name }}"
                                                 class="contact-avatar">
                                        </div>
                                        <div class="contact-meta">
                                            <div class="contact-top">
                                                <h6>{{ $u->name }}</h6>
                                                @if($conversation['last_message'])
                                                    <small class="last-time">{{ $conversation['last_message']->created_at->diffForHumans(null, true, true) }}</small>
                                                @endif
                                            </div>
                                            <div class="contact-bottom">
                                                <p class="last-preview">
                                                    {{ $conversation['last_message']->content ?? 'No messages yet' }}
                                                </p>
                                                @if($conversation['unread_count'] > 0)
                                                    <span class="unread-badge">{{ $conversation['unread_count'] }}</span>
                                                @endif
                                            </div>
                                            <span class="chat-role-badge {{ $roleBadge[$u->role_name] ?? 'chat-role-default' }}">{{ $u->role_name }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @empty
                        <div class="chat-empty-contacts">
                            <i class="fas fa-user-friends"></i>
                            <p>No contacts available</p>
                            @if(Auth::user()->role_name === 'Admin')
                                <small>Create Teacher or Registrar accounts under User Management to start chatting.</small>
                            @else
                                <small>There is no one available to chat with yet.</small>
                            @endif
                        </div>
                    @endforelse
                </div>
            </aside>

            {{-- Conversation pane --}}
            <section class="chat-main">
                <div id="empty-state" class="chat-empty-state">
                    <div class="chat-empty-card">
                        <div class="chat-empty-icon"><i class="far fa-comments"></i></div>
                        <h4>Select a conversation</h4>
                        <p>Choose a contact on the left to start messaging.</p>
                    </div>
                </div>

                <div id="chat-container" class="d-none chat-thread">
                    <div class="chat-thread-head" id="chat-header">
                        <div class="d-flex align-items-center">
                            <img src="" alt="Contact" id="contact-avatar" class="contact-avatar-sm">
                            <div class="flex-grow-1 ms-3">
                                <h5 class="mb-0" id="contact-name">Contact Name</h5>
                                <small id="contact-role-label"></small>
                            </div>
                            <button type="button" class="chat-icon-btn" onclick="refreshChat()" title="Refresh">
                                <i class="fas fa-sync-alt"></i>
                            </button>
                        </div>
                    </div>

                    <div id="messages-area"></div>

                    <div class="chat-composer">
                        <form id="message-form" onsubmit="sendMessage(event)">
                            <div class="chat-composer-inner">
                                <textarea id="message-input"
                                          placeholder="Type a message..."
                                          rows="1"
                                          onkeydown="handleKeyPress(event)"></textarea>
                                <button class="chat-send-btn" type="submit" title="Send">
                                    <i class="fas fa-paper-plane"></i>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </section>
        </div>
    </div>
</div>

<style>
:root {
    --chat-orange: #e67e22;
    --chat-orange-dark: #d35400;
    --chat-orange-soft: #fff4eb;
    --chat-bg: #f5f6f8;
    --chat-card: #ffffff;
    --chat-text: #1f2937;
    --chat-muted: #6b7280;
    --chat-border: #e8eaed;
    --chat-radius: 12px;
}
.page-wrapper .content.chat-page {
    background: var(--chat-bg) !important;
    max-width: none !important;
    width: 100% !important;
    padding: 1rem 1.25rem 1rem 1.75rem !important;
}
.chat-shell {
    display: grid;
    grid-template-columns: minmax(300px, 380px) minmax(0, 1fr);
    gap: 1rem;
    height: calc(100vh - 100px);
    min-height: 560px;
    width: 100%;
}
.chat-sidebar,
.chat-main {
    background: var(--chat-card);
    border: 1px solid var(--chat-border);
    border-radius: var(--chat-radius);
    box-shadow: 0 1px 3px rgba(16,24,40,.05);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    min-width: 0;
}
.chat-sidebar-head {
    padding: 1rem 1.1rem;
    border-bottom: 1px solid var(--chat-border);
    background: #fff;
}
.chat-title {
    font-size: 1.15rem;
    font-weight: 700;
    color: var(--chat-text);
}
.chat-title i { color: var(--chat-orange); }
.chat-unread-pill {
    background: var(--chat-orange);
    color: #fff;
    font-size: .75rem;
    font-weight: 700;
    min-width: 22px;
    height: 22px;
    border-radius: 999px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .45rem;
}
.chat-hint {
    font-size: .78rem;
    color: var(--chat-muted);
    margin: 0 0 .75rem;
}
.chat-search {
    position: relative;
}
.chat-search i {
    position: absolute;
    left: 12px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--chat-muted);
    font-size: .85rem;
}
.chat-search input {
    width: 100%;
    border: 1px solid var(--chat-border);
    border-radius: 10px;
    padding: .65rem .85rem .65rem 2.2rem;
    background: #f8f9fb;
    font-size: .9rem;
    outline: none;
}
.chat-search input:focus {
    border-color: #f0c49a;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(230,126,34,.12);
}
.contacts-list {
    overflow-y: auto;
    flex: 1;
}
.contact-group-title {
    padding: .55rem 1.1rem;
    font-size: .7rem;
    font-weight: 700;
    letter-spacing: .05em;
    text-transform: uppercase;
    color: var(--chat-muted);
    background: #fafbfc;
    border-bottom: 1px solid var(--chat-border);
    display: flex;
    align-items: center;
    gap: .4rem;
}
.contact-group-title span {
    background: var(--chat-orange-soft);
    color: var(--chat-orange-dark);
    border-radius: 999px;
    padding: .1rem .45rem;
    font-size: .68rem;
}
.contact-item {
    padding: .85rem 1.1rem;
    cursor: pointer;
    border-bottom: 1px solid var(--chat-border);
    transition: background .15s;
    background: #fff;
}
.contact-item:hover { background: #fafbfc; }
.contact-item.active {
    background: var(--chat-orange-soft) !important;
    border-left: 3px solid var(--chat-orange);
}
.contact-row { display: flex; gap: .75rem; align-items: flex-start; }
.contact-avatar,
.contact-avatar-sm {
    width: 44px;
    height: 44px;
    border-radius: 50%;
    object-fit: cover;
    background: #f0f1f3;
    border: 2px solid #fff;
    box-shadow: 0 0 0 1px var(--chat-border);
}
.contact-avatar-sm { width: 40px; height: 40px; }
.contact-meta { flex: 1; min-width: 0; }
.contact-top,
.contact-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: .5rem;
}
.contact-top h6 {
    margin: 0;
    font-size: .9rem;
    font-weight: 650;
    color: var(--chat-text);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.last-time { color: var(--chat-muted); font-size: .72rem; flex-shrink: 0; }
.last-preview {
    margin: .2rem 0 0;
    font-size: .8rem;
    color: var(--chat-muted);
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
.unread-badge {
    background: var(--chat-orange);
    color: #fff;
    border-radius: 999px;
    font-size: .68rem;
    font-weight: 700;
    min-width: 18px;
    height: 18px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0 .35rem;
    flex-shrink: 0;
}
.chat-role-badge {
    display: inline-block;
    margin-top: .35rem;
    font-size: .65rem;
    font-weight: 700;
    padding: .15rem .5rem;
    border-radius: 999px;
}
.chat-role-admin { background: #fee2e2; color: #b91c1c; }
.chat-role-teacher { background: var(--chat-orange-soft); color: var(--chat-orange-dark); }
.chat-role-registrar { background: #fef3c7; color: #b45309; }
.chat-role-student { background: #ecfdf5; color: #047857; }
.chat-role-parent { background: #f3f4f6; color: #4b5563; }
.chat-role-default { background: #f3f4f6; color: #6b7280; }

.chat-empty-contacts,
.chat-empty-state {
    display: flex;
    align-items: center;
    justify-content: center;
    flex: 1;
    padding: 2rem;
    text-align: center;
    color: var(--chat-muted);
}
.chat-empty-contacts i { font-size: 2.5rem; margin-bottom: .75rem; color: #d1d5db; }
.chat-empty-card {
    max-width: 360px;
}
.chat-empty-icon {
    width: 72px;
    height: 72px;
    margin: 0 auto 1rem;
    border-radius: 18px;
    background: var(--chat-orange-soft);
    color: var(--chat-orange);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.75rem;
}
.chat-empty-state h4 {
    color: var(--chat-text);
    font-weight: 700;
    margin-bottom: .35rem;
}
.chat-empty-state p { margin: 0; font-size: .9rem; }

.chat-thread { flex: 1; display: flex; flex-direction: column; min-height: 0; }
.chat-thread-head {
    padding: .9rem 1.15rem;
    border-bottom: 1px solid var(--chat-border);
    background: #fff;
}
.chat-thread-head h5 { font-size: 1rem; font-weight: 700; }
.chat-thread-head small { color: var(--chat-muted); }
.chat-icon-btn {
    width: 36px;
    height: 36px;
    border-radius: 10px;
    border: 1px solid var(--chat-border);
    background: #fafbfc;
    color: var(--chat-muted);
}
.chat-icon-btn:hover {
    color: var(--chat-orange);
    border-color: #f0c49a;
    background: var(--chat-orange-soft);
}
#messages-area {
    flex: 1;
    overflow-y: auto;
    padding: 1.15rem 1.25rem;
    background: #fafbfc;
}
#messages-area::-webkit-scrollbar { width: 6px; }
#messages-area::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 3px; }
.message-bubble {
    max-width: min(65%, 520px);
    word-wrap: break-word;
    margin-bottom: 10px;
    padding: 10px 14px;
    border-radius: 16px;
    display: inline-block;
    text-align: left;
}
.message-bubble.sent {
    background: var(--chat-orange);
    color: #fff;
    margin-left: auto;
    border-bottom-right-radius: 4px;
}
.message-bubble.received {
    background: #fff;
    color: var(--chat-text);
    border: 1px solid var(--chat-border);
    margin-right: auto;
    border-bottom-left-radius: 4px;
}
.message-time { font-size: 11px; margin-top: 4px; opacity: .8; }
.chat-composer {
    padding: .85rem 1.1rem;
    border-top: 1px solid var(--chat-border);
    background: #fff;
}
.chat-composer-inner {
    display: flex;
    gap: .6rem;
    align-items: flex-end;
}
#message-input {
    flex: 1;
    resize: none;
    max-height: 100px;
    border: 1px solid var(--chat-border);
    border-radius: 12px;
    padding: .7rem .9rem;
    background: #f8f9fb;
    font-size: .92rem;
    outline: none;
}
#message-input:focus {
    border-color: #f0c49a;
    background: #fff;
    box-shadow: 0 0 0 3px rgba(230,126,34,.12);
}
.chat-send-btn {
    width: 44px;
    height: 44px;
    border: none;
    border-radius: 12px;
    background: var(--chat-orange);
    color: #fff;
    flex-shrink: 0;
}
.chat-send-btn:hover { background: var(--chat-orange-dark); }
.chat-send-btn:disabled { opacity: .65; }

@media (max-width: 992px) {
    .chat-shell {
        grid-template-columns: 1fr;
        height: auto;
        min-height: 0;
    }
    .chat-sidebar { max-height: 42vh; }
    .chat-main { min-height: 55vh; }
    .page-wrapper .content.chat-page {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}
</style>

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
            $('#messages-area').html('<div class="text-center py-3 text-muted"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>');
        }

        $.ajax({
            url: '/chat/conversation/' + userId,
            type: 'GET',
            success: function (response) {
                const messagesArea = $('#messages-area');
                const el = messagesArea[0];
                const isScrolledToBottom = el.scrollHeight - el.scrollTop - messagesArea.height() < 100;

                if (!response.messages || response.messages.length === 0) {
                    messagesArea.html('<div class="text-center py-5"><div class="chat-empty-icon mx-auto mb-3"><i class="far fa-comments"></i></div><p class="text-muted mb-0">No messages yet. Start the conversation!</p></div>');
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
                    if ($('#messages-area .fa-comments').length || $('#messages-area .chat-empty-icon').length) {
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
