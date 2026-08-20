@extends('layouts.master')
@section('content')

    
    <div class="page-wrapper">
        <div class="content container-fluid p-0">
            <!-- Messenger-style Chat Interface -->
            <div class="row g-0" style="height: calc(100vh - 60px);">
                <!-- Contacts Sidebar -->
                <div class="col-md-4 col-lg-3 border-end" style="height: 100%; overflow-y: auto; background: #f8f9fa;">
                    <!-- Chat Header -->
                    <div class="p-3 border-bottom bg-white sticky-top">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h4 class="mb-0">
                                <i class="fas fa-comments text-primary me-2"></i>Messages
                            </h4>
                            <span class="badge bg-primary" id="total-unread">0</span>
                        </div>
                        
                        <!-- Search Box -->
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0">
                                <i class="fas fa-search text-muted"></i>
                            </span>
                            <input type="text" class="form-control border-start-0 ps-0" 
                                   placeholder="Search contacts..." id="search-contacts">
                        </div>
                    </div>

                    <!-- Contacts List -->
                    <div class="contacts-list" id="contacts-list">
                        @forelse($conversations as $conversation)
                            <div class="contact-item p-3 border-bottom" 
                                 data-user-id="{{ $conversation['user']->id }}"
                                 onclick="loadConversation({{ $conversation['user']->id }}, '{{ $conversation['user']->name }}', '{{ $conversation['user']->avatar ?? 'default-avatar.png' }}')">
                                <div class="d-flex align-items-start position-relative">
                                    <!-- Avatar -->
                                    <div class="position-relative me-3">
                                        <img src="{{ asset('assets/img/profiles/' . ($conversation['user']->avatar ?? 'default-avatar.png')) }}" 
                                             alt="{{ $conversation['user']->name }}" 
                                             class="rounded-circle" 
                                             style="width: 50px; height: 50px; object-fit: cover;">
                                        <span class="position-absolute bottom-0 end-0 bg-success border border-white rounded-circle" 
                                              style="width: 12px; height: 12px;"></span>
                                    </div>
                                    
                                    <!-- Contact Info -->
                                    <div class="flex-grow-1 overflow-hidden">
                                        <div class="d-flex justify-content-between align-items-start mb-1">
                                            <h6 class="mb-0 text-truncate">{{ $conversation['user']->name }}</h6>
                                            @if($conversation['last_message'])
                                                <small class="text-muted">{{ $conversation['last_message']->created_at->diffForHumans(null, true, true) }}</small>
                                            @endif
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center">
                                            <p class="text-muted mb-0 small text-truncate" style="max-width: 180px;">
                                                {{ $conversation['last_message']->content ?? 'No messages yet' }}
                                            </p>
                                            @if($conversation['unread_count'] > 0)
                                                <span class="badge bg-primary rounded-pill">{{ $conversation['unread_count'] }}</span>
                                            @endif
                                        </div>
                                        <small class="text-muted">{{ $conversation['user']->role_name }}</small>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-user-friends fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No contacts available</p>
                                <small class="text-muted">Start a conversation with teachers</small>
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Chat Area -->
                <div class="col-md-8 col-lg-9 d-flex flex-column" style="height: 100%; background: #fff;">
                    <!-- Empty State (shown when no chat is selected) -->
                    <div id="empty-state" class="flex-grow-1 d-flex align-items-center justify-content-center">
                        <div class="text-center">
                            <i class="fas fa-comments fa-5x text-muted mb-4"></i>
                            <h4 class="text-muted">Select a conversation</h4>
                            <p class="text-muted">Choose a contact from the sidebar to start messaging</p>
                        </div>
                    </div>

                    <!-- Chat Container (hidden by default) -->
                    <div id="chat-container" class="d-none flex-grow-1 d-flex flex-column" style="height: 100%;">
                        <!-- Chat Header -->
                        <div class="p-3 border-bottom bg-white" id="chat-header">
                            <div class="d-flex align-items-center">
                                <img src="" alt="Contact" id="contact-avatar" class="rounded-circle me-3" 
                                     style="width: 40px; height: 40px; object-fit: cover;">
                                <div class="flex-grow-1">
                                    <h5 class="mb-0" id="contact-name">Contact Name</h5>
                                    <small class="text-success">
                                        <i class="fas fa-circle" style="font-size: 8px;"></i> Active now
                                    </small>
                                </div>
                                <div class="d-flex gap-2">
                                    <button class="btn btn-sm btn-light rounded-circle" onclick="refreshChat()">
                                        <i class="fas fa-sync-alt"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Messages Area -->
                        <div class="flex-grow-1 p-3" id="messages-area" style="overflow-y: auto; max-height: calc(100vh - 240px); background: #f8f9fa;">
                            <!-- Messages will be loaded here dynamically -->
                        </div>

                        <!-- Message Input -->
                        <div class="p-3 border-top bg-white">
                            <form id="message-form" onsubmit="sendMessage(event)">
                                <div class="input-group">
                                    <textarea class="form-control" 
                                              id="message-input" 
                                              placeholder="Type a message..."
                                              rows="1"
                                              style="resize: none; max-height: 100px;"
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
        .contact-item {
            cursor: pointer;
            transition: background-color 0.2s;
            background: white;
        }
        
        .contact-item:hover {
            background-color: #f0f2f5 !important;
        }
        
        .contact-item.active {
            background-color: #e7f3ff !important;
            border-left: 3px solid #0d6efd;
        }

        .message-bubble {
            max-width: 60%;
            word-wrap: break-word;
            margin-bottom: 10px;
            padding: 10px 15px;
            border-radius: 18px;
            position: relative;
        }

        .message-bubble.sent {
            background: #0d6efd;
            color: white;
            margin-left: auto;
            border-bottom-right-radius: 4px;
        }

        .message-bubble.received {
            background: #e9ecef;
            color: #333;
            margin-right: auto;
            border-bottom-left-radius: 4px;
        }

        .message-time {
            font-size: 11px;
            margin-top: 4px;
            opacity: 0.7;
        }

        #messages-area::-webkit-scrollbar {
            width: 6px;
        }

        #messages-area::-webkit-scrollbar-track {
            background: #f1f1f1;
        }

        #messages-area::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 3px;
        }

        #messages-area::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        .typing-indicator {
            display: flex;
            align-items: center;
            padding: 10px;
        }

        .typing-indicator span {
            height: 8px;
            width: 8px;
            background-color: #90949c;
            border-radius: 50%;
            display: inline-block;
            margin-right: 5px;
            animation: typing 1.4s infinite;
        }

        .typing-indicator span:nth-child(2) {
            animation-delay: 0.2s;
        }

        .typing-indicator span:nth-child(3) {
            animation-delay: 0.4s;
        }

        @keyframes typing {
            0%, 60%, 100% {
                transform: translateY(0);
            }
            30% {
                transform: translateY(-10px);
            }
        }
    </style>
    @endpush

    @push('scripts')
    <script>
        let currentContactId = null;
        let messageRefreshInterval = null;

        // Load conversation
        function loadConversation(userId, userName, userAvatar) {
            currentContactId = userId;
            
            // Update UI
            $('#empty-state').addClass('d-none');
            $('#chat-container').removeClass('d-none');
            $('#contact-name').text(userName);
            $('#contact-avatar').attr('src', '{{ asset("assets/img/profiles/") }}/' + userAvatar);
            
            // Highlight active contact
            $('.contact-item').removeClass('active');
            $(`.contact-item[data-user-id="${userId}"]`).addClass('active');
            
            // Load messages
            loadMessages(userId);
            
            // Start auto-refresh
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
            messageRefreshInterval = setInterval(() => loadMessages(userId, true), 3000);
        }

        // Load messages
        function loadMessages(userId, silent = false) {
            if (!silent) {
                $('#messages-area').html('<div class="text-center py-3"><i class="fas fa-spinner fa-spin"></i> Loading messages...</div>');
            }

            $.ajax({
                url: `/chat/conversation/${userId}`,
                type: 'GET',
                success: function(response) {
                    if (!silent) {
                        $('#messages-area').empty();
                    }

                    const messagesArea = $('#messages-area');
                    const scrollHeight = messagesArea[0].scrollHeight;
                    const scrollTop = messagesArea.scrollTop();
                    const isScrolledToBottom = scrollHeight - scrollTop - messagesArea.height() < 100;

                    if (response.messages.length === 0) {
                        messagesArea.html('<div class="text-center py-5"><i class="fas fa-comments fa-3x text-muted mb-3"></i><p class="text-muted">No messages yet. Start the conversation!</p></div>');
                    } else {
                        messagesArea.empty();
                        response.messages.forEach(message => {
                            appendMessage(message, silent);
                        });
                        
                        // Auto-scroll to bottom if user was at bottom or new message
                        if (isScrolledToBottom || !silent) {
                            scrollToBottom();
                        }
                    }

                    updateUnreadCount();
                },
                error: function(xhr) {
                    toastr.error('Failed to load messages');
                }
            });
        }

        // Append message to chat
        function appendMessage(message, silent = false) {
            const messageClass = message.is_own ? 'sent' : 'received';
            const alignClass = message.is_own ? 'text-end' : 'text-start';
            
            const messageHtml = `
                <div class="${alignClass} mb-2">
                    <div class="message-bubble ${messageClass}">
                        <div>${escapeHtml(message.content)}</div>
                        <div class="message-time">${message.created_at_human}</div>
                    </div>
                </div>
            `;
            
            $('#messages-area').append(messageHtml);
        }

        // Send message
        function sendMessage(event) {
            event.preventDefault();
            
            const content = $('#message-input').val().trim();
            if (!content || !currentContactId) return;

            $.ajax({
                url: '/chat/send',
                type: 'POST',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                },
                data: {
                    recipient_id: currentContactId,
                    content: content
                },
                success: function(response) {
                    if (response.success) {
                        $('#message-input').val('').css('height', 'auto');
                        appendMessage(response.message);
                        scrollToBottom();
                        updateContactLastMessage(currentContactId, content);
                    }
                },
                error: function(xhr) {
                    toastr.error('Failed to send message');
                }
            });
        }

        // Handle Enter key
        function handleKeyPress(event) {
            if (event.key === 'Enter' && !event.shiftKey) {
                event.preventDefault();
                sendMessage(event);
            }
        }

        // Scroll to bottom
        function scrollToBottom() {
            const messagesArea = $('#messages-area');
            messagesArea.animate({ scrollTop: messagesArea[0].scrollHeight }, 300);
        }

        // Refresh chat
        function refreshChat() {
            if (currentContactId) {
                loadMessages(currentContactId);
                toastr.success('Chat refreshed');
            }
        }

        // Update unread count
        function updateUnreadCount() {
            $.ajax({
                url: '/chat/unread-count',
                type: 'GET',
                success: function(response) {
                    $('#total-unread').text(response.count);
                    if (response.count > 0) {
                        $('#total-unread').removeClass('d-none');
                    } else {
                        $('#total-unread').addClass('d-none');
                    }
                }
            });
        }

        // Update contact last message in sidebar
        function updateContactLastMessage(contactId, message) {
            const contactItem = $(`.contact-item[data-user-id="${contactId}"]`);
            contactItem.find('.text-truncate').text(message);
            contactItem.find('small.text-muted').first().text('Just now');
            
            // Move to top
            contactItem.prependTo('#contacts-list');
        }

        // Escape HTML
        function escapeHtml(text) {
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }

        // Search contacts
        $('#search-contacts').on('input', function() {
            const search = $(this).val().toLowerCase();
            $('.contact-item').each(function() {
                const name = $(this).find('h6').text().toLowerCase();
                $(this).toggle(name.includes(search));
            });
        });

        // Auto-resize textarea
        $('#message-input').on('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        // Initial load
        $(document).ready(function() {
            updateUnreadCount();
            
            // Update unread count every 30 seconds
            setInterval(updateUnreadCount, 30000);
        });

        // Cleanup on page unload
        $(window).on('beforeunload', function() {
            if (messageRefreshInterval) {
                clearInterval(messageRefreshInterval);
            }
        });
    </script>
    @endpush
@endsection

