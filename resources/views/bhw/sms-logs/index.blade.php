@extends('layout.bhw')

@section('title', 'SMS Logs')

@section('content')
<div class="container mx-auto px-4 py-6">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-gray-900">SMS Logs</h1>
        <div class="flex justify-between items-center mt-1">
            <p class="text-gray-600">Monitor all SMS messages sent from the system</p>
            <button id="btnTriggerReminders" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 shadow-sm transition-all flex items-center">
                <i class="fas fa-paper-plane mr-2"></i> Send Day-Before Reminders Now
            </button>
        </div>
    </div>

    <script>
        document.getElementById('btnTriggerReminders').addEventListener('click', function() {
            showConfirmation(
                'Send Day-Before Reminders',
                'This will scan for all checkups and vaccinations scheduled for tomorrow and send SMS reminders. Continue?',
                function() {
                    // On Confirm
                    showLoading('Sending reminders...');
                    
                    fetch("{{ url('notifications/trigger-checks') }}", {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            showSuccess(data.message, function() {
                                location.reload();
                            });
                        } else {
                            showError('Error: ' + data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        showError('An error occurred while sending reminders.');
                    });
                }
            );
        });
    </script>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-envelope text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Total SMS</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Sent</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['sent'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-red-100 text-red-600">
                    <i class="fas fa-times-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['failed'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-calendar-day text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm text-gray-600">Today</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['today'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <form method="GET" action="{{ route('midwife.sms-logs.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Number, name..." class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Status</label>
                <select name="status" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">All Status</option>
                    <option value="sent" {{ request('status') == 'sent' ? 'selected' : '' }}>Sent</option>
                    <option value="failed" {{ request('status') == 'failed' ? 'selected' : '' }}>Failed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Type</label>
                <select name="type" class="w-full px-3 py-2 border rounded-lg">
                    <option value="">All Types</option>
                    <option value="appointment_reminder" {{ request('type') == 'appointment_reminder' ? 'selected' : '' }}>Appointment</option>
                    <option value="vaccination_reminder" {{ request('type') == 'vaccination_reminder' ? 'selected' : '' }}>Vaccination</option>
                    <option value="missed_appointment" {{ request('type') == 'missed_appointment' ? 'selected' : '' }}>Missed</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="w-full px-3 py-2 border rounded-lg">
            </div>
            <div class="md:col-span-5 flex gap-2">
                <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                    <i class="fas fa-filter mr-1"></i> Filter
                </button>
                <a href="{{ route('midwife.sms-logs.index') }}" class="px-4 py-2 bg-gray-200 rounded-lg">Clear</a>
            </div>
        </form>
    </div>

    <!-- Table -->
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Date</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Recipient</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y">
                @forelse($smsLogs as $log)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 text-sm">{{ $log->created_at->format('M d, h:i A') }}</td>
                        <td class="px-4 py-3 text-sm">
                            <div>{{ $log->recipient_name ?? 'N/A' }}</div>
                            <div class="text-xs text-gray-500">{{ $log->recipient_number }}</div>
                        </td>
                        <td class="px-4 py-3 text-sm"><div class="max-w-xs truncate">{{ $log->message }}</div></td>
                        <td class="px-4 py-3 text-sm"><span class="px-2 py-1 text-xs bg-blue-100 rounded">{{ ucwords(str_replace('_', ' ', $log->type)) }}</span></td>
                        <td class="px-4 py-3 text-sm">
                            @if($log->status === 'sent')
                                <span class="px-2 py-1 text-xs bg-green-100 text-green-800 rounded"><i class="fas fa-check"></i> Sent</span>
                            @else
                                <span class="px-2 py-1 text-xs bg-red-100 text-red-800 rounded"><i class="fas fa-times"></i> Failed</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-8 text-center text-gray-500">No SMS logs found</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($smsLogs->hasPages())
            <div class="px-4 py-3 border-t">{{ $smsLogs->links() }}</div>
        @endif
    </div>
</div>
@endsection
