@extends('layout.midwife')
@section('title', 'Vaccine Management')
@section('page-title', 'Vaccine Management')
@section('page-subtitle', 'Manage vaccine information & TDaP lot inventory')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/midwife/midwife.css') }}">
<link rel="stylesheet" href="{{ asset('css/midwife/vaccines-index.css') }}">
<style>
    .tab-active { border-bottom: 3px solid #0d9488; color: #0d9488; font-weight: 600; }
    .tab-inactive { border-bottom: 3px solid transparent; color: #6b7280; }
    .lot-status-in  { background: #d1fae5; color: #065f46; }
    .lot-status-low { background: #fef3c7; color: #92400e; }
    .lot-status-out { background: #fee2e2; color: #991b1b; }
    .lot-status-exp { background: #f3f4f6; color: #6b7280; text-decoration: line-through; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
    .animate-fade-in { animation: fadeIn 0.25s ease; }
</style>
@endpush

@section('content')
<div class="space-y-6">

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ TAB NAV â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="flex border-b border-gray-200 px-4">
            <button id="tab-vaccines" onclick="switchTab('vaccines')"
                    class="tab-active px-5 py-4 text-sm transition-all duration-200">
                <i class="fas fa-flask mr-2"></i> Vaccines
            </button>
            <button id="tab-lots" onclick="switchTab('lots')"
                    class="tab-inactive px-5 py-4 text-sm transition-all duration-200">
                <i class="fas fa-boxes mr-2"></i> TDaP Lot Inventory
                <span id="lotAlertBadge" class="ml-2 hidden items-center px-2 py-0.5 rounded-full text-xs bg-red-100 text-red-700">!</span>
            </button>
        </div>
    </div>

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ VACCINES TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div id="section-vaccines" class="animate-fade-in">
        <div class="flex justify-between items-center">
            <div></div>
            <div class="flex space-x-3">
                <button onclick="openVaccineModal()" class="bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 flex items-center btn-primary">
                    <i class="fas fa-plus w-4 h-4 mr-2"></i>
                    Add Vaccine
                </button>
            </div>
        </div>

        <div class="bg-white p-4 rounded-lg shadow-sm border mt-4">
            <form method="GET" action="{{ route('midwife.vaccines.index') }}">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <div class="relative">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or category..." class="w-full pl-10 pr-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                            <i class="fas fa-search w-5 h-5 text-gray-400 absolute left-3 top-2.5"></i>
                        </div>
                    </div>
                    <div>
                        <select name="category" class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary form-input">
                            <option value="">All Categories</option>
                            @foreach($categories as $category)
                                <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>
                                    {{ $category }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="flex space-x-2">
                        <button type="submit" class="flex-1 bg-secondary text-white px-4 py-2 rounded-lg hover:bg-hover-color transition-all duration-200 btn-primary">
                            Search
                        </button>
                        <a href="{{ route('midwife.vaccines.index') }}" class="flex-1 bg-gray-100 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-200 transition-colors text-center">
                            Clear
                        </a>
                    </div>
                </div>
            </form>
        </div>

        <div class="bg-white rounded-lg shadow-sm border mt-4">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vaccine</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Category</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dosage (ml)</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Doses</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Stock</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Expiry Date</th>
                            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($vaccines as $vaccine)
                        <tr class="hover:bg-gray-50 transition-colors duration-150">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-gray-900">{{ $vaccine->name }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vaccine->category_color }}">
                                    {{ $vaccine->category }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $vaccine->dosage }}{{ !str_contains($vaccine->dosage, 'ml') ? ' ml' : '' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-indigo-100 text-indigo-800">
                                    {{ $vaccine->dose_count }} {{ $vaccine->dose_count == 1 ? 'Dose' : 'Doses' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="flex items-center space-x-2">
                                    <span class="font-medium {{ $vaccine->stock_status_color }}">
                                        {{ $vaccine->current_stock }}
                                    </span>
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $vaccine->stock_status_badge_color }}">
                                        {{ $vaccine->stock_status }}
                                    </span>
                                </div>
                                @if($vaccine->is_low_stock)
                                    <div class="text-xs text-amber-600 mt-1">Min: {{ $vaccine->min_stock }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm">
                                <div class="{{ $vaccine->is_expiring_soon ? 'text-red-600 font-medium' : 'text-gray-900' }}">
                                    {{ $vaccine->expiry_date->format('M d, Y') }}
                                    @if($vaccine->is_expiring_soon)
                                        <div class="text-xs text-red-600">Expiring Soon</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex space-x-2">
                                    <button data-vaccine='@json($vaccine)' onclick='openViewVaccineModal(JSON.parse(this.dataset.vaccine))' class="btn-action btn-view inline-flex items-center justify-center">
                                        <i class="fas fa-eye mr-1"></i>
                                    </button>
                                    <button data-vaccine='@json($vaccine)' onclick='openEditVaccineModal(JSON.parse(this.dataset.vaccine))' class="btn-action btn-edit inline-flex items-center justify-center">
                                        <i class="fas fa-edit mr-1"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                <div class="flex flex-col items-center">
                                    <i class="fas fa-flask w-12 h-12 text-gray-400 mb-4"></i>
                                    <p class="text-lg font-medium text-gray-900 mb-2">No vaccines found</p>
                                    <p class="text-gray-600 mb-4">Get started by adding your first vaccine</p>
                                    <button onclick="openVaccineModal()" class="bg-primary text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors btn-primary">
                                        Add First Vaccine
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            @if($vaccines->hasPages())
                <div class="px-6 py-3 border-t border-gray-200">
                    {{ $vaccines->links() }}
                </div>
            @endif
        </div>
    </div>

    {{-- â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ LOT INVENTORY TAB â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€ --}}
    <div id="section-lots" class="hidden animate-fade-in space-y-4">

        {{-- Stats row --}}
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4" id="lotStatsRow">
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <div class="text-2xl font-bold text-teal-600" id="statOnHand">0</div>
                <div class="text-xs text-gray-500 mt-1">Total On Hand</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <div class="text-2xl font-bold text-indigo-600" id="statUsed">0</div>
                <div class="text-xs text-gray-500 mt-1">Total Doses Used</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <div class="text-2xl font-bold text-yellow-600" id="statLow">0</div>
                <div class="text-xs text-gray-500 mt-1">Low-Stock Lots</div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border p-4 text-center">
                <div class="text-2xl font-bold text-red-600" id="statExpiring">0</div>
                <div class="text-xs text-gray-500 mt-1">Expiring Soon</div>
            </div>
        </div>

        <div class="flex justify-between items-center">
            <h3 class="text-base font-semibold text-gray-700 flex items-center">
                <i class="fas fa-boxes text-teal-500 mr-2"></i> TDaP Lot Inventory
            </h3>
            <button onclick="openAddLotModal()"
                    class="inline-flex items-center px-4 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition-colors shadow-sm">
                <i class="fas fa-plus mr-2"></i> Add New Lot
            </button>
        </div>

        <div class="bg-white rounded-xl shadow-sm border overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-gray-50 text-xs font-medium text-gray-500 uppercase tracking-wider">
                        <tr>
                            <th class="px-5 py-3 text-left">Lot Number</th>
                            <th class="px-5 py-3 text-left">Expiry Date</th>
                            <th class="px-5 py-3 text-left">On Hand</th>
                            <th class="px-5 py-3 text-left">Used</th>
                            <th class="px-5 py-3 text-left">Threshold</th>
                            <th class="px-5 py-3 text-left">Status</th>
                            <th class="px-5 py-3 text-left">Supplier</th>
                        </tr>
                    </thead>
                    <tbody id="lotsTableBody" class="divide-y divide-gray-100">
                        <tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">
                            <i class="fas fa-spinner fa-spin text-xl"></i> Loading lots...
                        </td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

{{-- Vaccine modals --}}
@include('partials.midwife.vaccine.vaccine_add')
@include('partials.midwife.vaccine.vaccine_view')
@include('partials.midwife.vaccine.vaccine_edit')

{{-- Lot Add modal --}}
@include('partials.midwife.vaccine.vaccine_lot_add')

@endsection

@push('scripts')
<script src="{{ asset('js/midwife/midwife.js') }}"></script>
<script src="{{ asset('js/midwife/vaccines-index.js') }}"></script>
<script>
/* â”€â”€ Tab switching â”€â”€ */
function switchTab(tab) {
    ['vaccines', 'lots'].forEach(t => {
        document.getElementById('section-' + t).classList.toggle('hidden', t !== tab);
        document.getElementById('tab-' + t).className = t === tab ? 'tab-active px-5 py-4 text-sm transition-all duration-200' : 'tab-inactive px-5 py-4 text-sm transition-all duration-200';
    });
    if (tab === 'lots') loadLots();
}

/* â”€â”€ Lot Add Modal â”€â”€ */
function openAddLotModal()  { document.getElementById('addLotModal').classList.remove('hidden'); }
function closeAddLotModal() { document.getElementById('addLotModal').classList.add('hidden'); }

/* â”€â”€ Load lots via AJAX â”€â”€ */
function loadLots() {
    fetch('{{ route("midwife.vaccine-lots.index") }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(({ lots, stats }) => {
            renderStats(stats);
            renderLots(lots);
        })
        .catch(() => {
            document.getElementById('lotsTableBody').innerHTML =
                '<tr><td colspan="7" class="px-5 py-6 text-center text-red-500">Failed to load lots.</td></tr>';
        });
}

function renderStats(s) {
    document.getElementById('statOnHand').textContent = s.total_on_hand ?? 0;
    document.getElementById('statUsed').textContent   = s.total_used ?? 0;
    document.getElementById('statLow').textContent    = s.low_stock_count ?? 0;
    document.getElementById('statExpiring').textContent = s.expiring_count ?? 0;

    // Show badge on tab if alerts exist
    const badge = document.getElementById('lotAlertBadge');
    if ((s.low_stock_count + s.expiring_count) > 0) {
        badge.classList.remove('hidden');
        badge.classList.add('inline-flex');
    }
}

function renderLots(lots) {
    const tbody = document.getElementById('lotsTableBody');
    if (!lots.length) {
        tbody.innerHTML = '<tr><td colspan="7" class="px-5 py-8 text-center text-gray-400">No lots recorded yet. Add your first TDaP lot.</td></tr>';
        return;
    }
    tbody.innerHTML = lots.map(lot => {
        const expDate = new Date(lot.expiry_date);
        const now = new Date();
        const daysLeft = Math.ceil((expDate - now) / 86400000);
        const isExpired = daysLeft < 0;
        const isExpiringSoon = daysLeft >= 0 && daysLeft <= 30;
        const isLow = lot.quantity_on_hand > 0 && lot.quantity_on_hand <= lot.low_stock_threshold;
        const isOut = lot.quantity_on_hand <= 0;

        let statusHtml;
        if (isExpired) {
            statusHtml = '<span class="px-2 py-0.5 rounded-full text-xs lot-status-exp">Expired</span>';
        } else if (isOut) {
            statusHtml = '<span class="px-2 py-0.5 rounded-full text-xs lot-status-out">Out of Stock</span>';
        } else if (isLow) {
            statusHtml = '<span class="px-2 py-0.5 rounded-full text-xs lot-status-low">Low Stock</span>';
        } else {
            statusHtml = '<span class="px-2 py-0.5 rounded-full text-xs lot-status-in">In Stock</span>';
        }

        const expiryClass = isExpired ? 'text-gray-400 line-through' : isExpiringSoon ? 'text-red-600 font-semibold' : 'text-gray-800';
        const expiryLabel = isExpiringSoon && !isExpired ? ` <span class="text-xs text-red-500">(${daysLeft}d)</span>` : '';

        return `
        <tr class="hover:bg-gray-50 transition-colors">
            <td class="px-5 py-3 font-mono font-semibold text-gray-800">${lot.lot_number}</td>
            <td class="px-5 py-3 ${expiryClass}">${expDate.toLocaleDateString('en-PH', {month: 'short', day: 'numeric', year: 'numeric'})}${expiryLabel}</td>
            <td class="px-5 py-3 font-semibold ${isOut ? 'text-red-600' : isLow ? 'text-yellow-600' : 'text-teal-700'}">${lot.quantity_on_hand}</td>
            <td class="px-5 py-3 text-gray-600">${lot.quantity_used}</td>
            <td class="px-5 py-3 text-gray-500">${lot.low_stock_threshold}</td>
            <td class="px-5 py-3">${statusHtml}</td>
            <td class="px-5 py-3 text-gray-500">${lot.supplier ?? 'â€”'}</td>
        </tr>`;
    }).join('');
}

/* â”€â”€ Submit Add Lot â”€â”€ */
function submitAddLot(e) {
    e.preventDefault();
    const btn = document.getElementById('addLotSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Saving...';
    const form = e.target;

    fetch('{{ route("midwife.vaccine-lots.store") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: new FormData(form),
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            closeAddLotModal();
            form.reset();
            loadLots();
            showLotToast(res.message, 'success');
        } else {
            showLotToast(res.message ?? 'Error saving lot.', 'error');
        }
    })
    .catch(() => showLotToast('Unexpected error. Please try again.', 'error'))
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-1"></i> Save Lot';
    });
}

function showLotToast(msg, type) {
    const color = type === 'success' ? 'bg-teal-600' : 'bg-red-600';
    const t = document.createElement('div');
    t.className = `fixed bottom-4 right-4 z-[9999] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-lg ${color}`;
    t.textContent = msg;
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 3500);
}
</script>
@endpush
