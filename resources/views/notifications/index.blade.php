@extends('layout.midwife')
@section('title', 'Notifications')
@section('page-title', 'Notifications')
@section('page-subtitle', 'Manage your healthcare system notifications')

@section('content')
@include('components.flowbite-alert')

<style>
    /* @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap'); */
    
    .notification-item {
        transition: all 0.2s ease;
    }
    
    .notification-item:hover {
        background-color: #f8fafc;
        transform: translateY(-1px);
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .notification-unread {
        background-color: #eff6ff;
        border-left: 4px solid #3b82f6;
    }
    
    .notification-type-info { border-left-color: #3b82f6; }
    .notification-type-success { border-left-color: #10b981; }
    .notification-type-warning { border-left-color: #f59e0b; }
    .notification-type-error { border-left-color: #ef4444; }
    
    .notification-badge {
        font-size: 0.75rem;
        padding: 0.25rem 0.5rem;
        border-radius: 9999px;
        font-weight: 500;
    }
    
    .badge-info { background-color: #dbeafe; color: #1e40af; }
    .badge-success { background-color: #d1fae5; color: #065f46; }
    .badge-warning { background-color: #fef3c7; color: #92400e; }
    .badge-error { background-color: #fee2e2; color: #991b1b; }
</style>

<div class="space-y-6">
    <!-- Header with Actions -->
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Notifications</h1>
            <p class="text-gray-600 mt-1">{{ $unreadCount }} unread notifications</p>
        </div>
        <div class="flex space-x-3">
            <button onclick="sendTestNotification()" 
                class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                <i class="fas fa-bell mr-2"></i>Send Test
            </button>
            <button onclick="markAllAsRead()" 
                class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-colors">
                <i class="fas fa-check-double mr-2"></i>Mark All Read
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        @if($notifications->count() > 0)
            <div class="divide-y divide-gray-200">
                @foreach($notifications as $notification)
                    <div class="notification-item p-4 {{ $notification->read_at ? '' : 'notification-unread notification-type-' . ($notification->data['type'] ?? 'info') }}" 
                         data-notification-id="{{ $notification->id }}">
                        <div class="flex items-start justify-between">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center space-x-3">
                                    <div class="flex-shrink-0">
                                        @php
                                            $type = $notification->data['type'] ?? 'info';
                                            $icons = [
                                                'info' => 'fa-info-circle text-blue-500',
                                                'success' => 'fa-check-circle text-green-500', 
                                                'warning' => 'fa-exclamation-triangle text-yellow-500',
                                                'error' => 'fa-times-circle text-red-500'
                                            ];
                                        @endphp
                                        <i class="fas {{ $icons[$type] ?? $icons['info'] }} text-xl"></i>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center space-x-2 mb-1">
                                            <h3 class="text-sm font-semibold text-gray-900">
                                                {{ $notification->data['title'] ?? 'Notification' }}
                                            </h3>
                                            <span class="notification-badge badge-{{ $type }}">
                                                {{ ucfirst($type) }}
                                            </span>
                                            @if(!$notification->read_at)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    New
                                                </span>
                                            @endif
                                        </div>
                                        <p class="text-sm text-gray-600 mb-2">
                                            {{ $notification->data['message'] ?? 'No message' }}
                                        </p>
                                        <div class="flex items-center text-xs text-gray-500">
                                            <i class="fas fa-clock mr-1"></i>
                                            {{ $notification->created_at->diffForHumans() }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-2 ml-4">
                                @if($notification->data['action_url'] ?? false)
                                    <a href="{{ $notification->data['action_url'] }}" 
                                       onclick="markAsRead('{{ $notification->id }}')"
                                       class="text-blue-600 hover:text-blue-800 text-sm font-medium">
                                        <i class="fas fa-external-link-alt mr-1"></i>View
                                    </a>
                                @endif
                                @if(!$notification->read_at)
                                    <button onclick="markAsRead('{{ $notification->id }}')" 
                                            class="text-gray-600 hover:text-gray-800 text-sm">
                                        <i class="fas fa-check mr-1"></i>Mark Read
                                    </button>
                                @endif
                                <button onclick="confirmDelete('this notification', function() { deleteNotification('{{ $notification->id }}') })" 
                                        class="text-red-600 hover:text-red-800 text-sm">
                                    <i class="fas fa-trash mr-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Pagination -->
            <div class="px-4 py-3 bg-gray-50 border-t border-gray-200">
                {{ $notifications->links() }}
            </div>
        @else
            <!-- Empty State -->
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bell-slash text-gray-400 text-xl"></i>
                </div>
                <h3 class="text-lg font-medium text-gray-900 mb-2">No notifications</h3>
                <p class="text-gray-500 mb-6">You're all caught up! No new notifications.</p>
                <button onclick="sendTestNotification()" 
                    class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-bell mr-2"></i>Send Test Notification
                </button>
            </div>
        @endif
    </div>
</div>

<script>
function markAsRead(notificationId) {
    fetch(`/notifications/mark-as-read/${notificationId}`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.classList.remove('notification-unread', 'notification-type-info', 'notification-type-success', 'notification-type-warning', 'notification-type-error');
                const newBadge = notificationElement.querySelector('.bg-blue-100');
                if (newBadge) newBadge.remove();
                const markReadBtn = notificationElement.querySelector('[onclick*="markAsRead"]');
                if (markReadBtn) markReadBtn.remove();
            }
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function markAllAsRead() {
    fetch('/notifications/mark-all-as-read', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    })
    .catch(error => console.error('Error:', error));
}

function deleteNotification(notificationId) {
    fetch(`/notifications/${notificationId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const notificationElement = document.querySelector(`[data-notification-id="${notificationId}"]`);
            if (notificationElement) {
                notificationElement.remove();
            }
            updateNotificationCount();
        }
    })
    .catch(error => console.error('Error:', error));
}

function sendTestNotification() {
    fetch('/notifications/send-test', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Test notification sent! Check your notifications.');
            setTimeout(() => location.reload(), 1000);
        }
    })
    .catch(error => console.error('Error:', error));
}

function updateNotificationCount() {
    fetch('/notifications/unread-count')
        .then(response => response.json())
        .then(data => {
            // Update notification badge in header if exists
            const badge = document.querySelector('.notification-badge-count');
            if (badge) {
                badge.textContent = data.count;
                if (data.count === 0) {
                    badge.style.display = 'none';
                }
            }
        });
}
</script>
@endsection