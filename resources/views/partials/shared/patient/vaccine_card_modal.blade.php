{{--
    Maternal TDaP Vaccine Card Modal
    Variables available: $patient (with maternalImmunizations loaded)
--}}
@php
    $doses = $patient->maternalImmunizations ?? collect();
    $dose1 = $doses->firstWhere('dose_number', 1);
    $dose2 = $doses->firstWhere('dose_number', 2);

    // Compute current gestational week from the active prenatal record's LMP
    $currentGestationalWeek = null;
    $activePrenatal = $patient->activePrenatalRecord ?? null;
    if ($activePrenatal && $activePrenatal->last_menstrual_period) {
        $totalDays = \Carbon\Carbon::parse($activePrenatal->last_menstrual_period)->diffInDays(now());
        $currentGestationalWeek = intval($totalDays / 7); // whole weeks only
        if ($currentGestationalWeek < 1 || $currentGestationalWeek > 45) {
            $currentGestationalWeek = null; // out of plausible range, don't auto-fill
        }
    }

    // Compute status
    if (!$dose1) {
        $cardStatus  = 'no_record';
        $badgeText   = 'No Record Yet';
        $badgeClass  = 'bg-gray-100 text-gray-600';
        $badgeIcon   = 'fa-clock';
        $btnClass    = 'bg-gray-100 text-gray-700 hover:bg-gray-200';
        $btnBadge    = '';
    } elseif (!$dose2) {
        $due = $dose1->next_dose_due_date;
        $daysLeft = now()->diffInDays($due, false); // negative = past

        if ($daysLeft < 0) {
            $cardStatus  = 'overdue';
            $badgeText   = 'Dose 2 Overdue';
            $badgeClass  = 'bg-red-100 text-red-700';
            $badgeIcon   = 'fa-exclamation-circle';
            $btnClass    = 'bg-red-50 text-red-700 border border-red-300 hover:bg-red-100';
            $btnBadge    = '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-red-600 text-white">Dose 2 Overdue</span>';
        } elseif ($daysLeft <= 14) {
            $cardStatus  = 'due_soon';
            $badgeText   = 'Dose 2 Due Soon';
            $badgeClass  = 'bg-yellow-100 text-yellow-700';
            $badgeIcon   = 'fa-bell';
            $btnClass    = 'bg-yellow-50 text-yellow-700 border border-yellow-300 hover:bg-yellow-100';
            $btnBadge    = '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-500 text-white">Due ' . ($due ? $due->format('M j') : '') . '</span>';
        } else {
            $cardStatus  = 'upcoming';
            $badgeText   = 'Dose 2 Due ' . ($due ? $due->format('M j, Y') : '');
            $badgeClass  = 'bg-blue-100 text-blue-700';
            $badgeIcon   = 'fa-calendar-check';
            $btnClass    = 'bg-blue-50 text-blue-700 border border-blue-300 hover:bg-blue-100';
            $btnBadge    = '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-blue-500 text-white">Dose 2 Due ' . ($due ? $due->format('M j') : '') . '</span>';
        }
    } else {
        $cardStatus  = 'complete';
        $badgeText   = 'Series Complete';
        $badgeClass  = 'bg-green-100 text-green-700';
        $badgeIcon   = 'fa-check-circle';
        $btnClass    = 'bg-green-50 text-green-700 border border-green-300 hover:bg-green-100';
        $btnBadge    = '<span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-bold bg-green-600 text-white">Complete</span>';
    }
@endphp

{{-- ─── Trigger Button ─── --}}
<button id="openVaccineCardBtn"
        onclick="document.getElementById('vaccineCardModal').classList.remove('hidden')"
        class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 shadow-sm {{ $btnClass }}">
    <i class="fas fa-syringe mr-2"></i>
    View Vaccine Card
    {!! $btnBadge !!}
</button>

{{-- ─── Modal ─── --}}
<div id="vaccineCardModal"
     class="hidden fixed inset-0 z-50 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm"
     onclick="if(event.target===this) this.classList.add('hidden')">

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl overflow-hidden animate-slide-up">

        {{-- Modal Header --}}
        <div class="px-6 py-5 border-b border-gray-100 flex items-center justify-between bg-gradient-to-r from-teal-600 to-teal-500">
            <div class="flex items-center space-x-3">
                <div class="w-10 h-10 bg-white bg-opacity-20 rounded-full flex items-center justify-center">
                    <i class="fas fa-syringe text-white text-lg"></i>
                </div>
                <div>
                    <h3 class="text-lg font-bold text-white">TDaP Vaccine Card</h3>
                    <p class="text-teal-100 text-sm">Tetanus-Diphtheria-acellular Pertussis · 2-Dose Series</p>
                </div>
            </div>
            <button onclick="document.getElementById('vaccineCardModal').classList.add('hidden')"
                    class="text-white hover:text-teal-100 transition-colors">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="p-6 space-y-5 max-h-[75vh] overflow-y-auto">

            {{-- Status Banner --}}
            <div class="flex items-center space-x-3 px-4 py-3 rounded-xl {{ $badgeClass }} border {{ str_contains($badgeClass, 'red') ? 'border-red-200' : (str_contains($badgeClass, 'yellow') ? 'border-yellow-200' : (str_contains($badgeClass, 'green') ? 'border-green-200' : (str_contains($badgeClass, 'blue') ? 'border-blue-200' : 'border-gray-200'))) }}">
                <i class="fas {{ $badgeIcon }} text-xl"></i>
                <div>
                    <p class="font-semibold text-sm">{{ $badgeText }}</p>
                    @if($dose1 && !$dose2 && isset($due))
                        <p class="text-xs opacity-80">Next dose due: {{ $due ? $due->format('F j, Y') : '—' }}</p>
                    @endif
                </div>
            </div>

            {{-- Progress Bar --}}
            <div>
                <div class="flex justify-between text-xs font-medium text-gray-500 mb-2">
                    <span>Dose Progress</span>
                    <span>{{ $doses->count() }} / 2 doses</span>
                </div>
                <div class="relative h-3 bg-gray-100 rounded-full overflow-hidden">
                    <div class="h-full rounded-full bg-teal-500 transition-all duration-500"
                         style="width: {{ $doses->count() >= 2 ? 100 : ($doses->count() == 1 ? 50 : 0) }}%"></div>
                </div>
                <div class="flex justify-between mt-2">
                    @foreach([1, 2] as $doseNum)
                        @php $d = $doses->firstWhere('dose_number', $doseNum); @endphp
                        <div class="flex items-center space-x-1.5 text-xs {{ $d ? 'text-teal-700 font-semibold' : 'text-gray-400' }}">
                            <div class="w-5 h-5 rounded-full flex items-center justify-center
                                {{ $d ? 'bg-teal-500 text-white' : 'bg-gray-200 text-gray-400' }}">
                                {{ $doseNum }}
                            </div>
                            <span>{{ $doseNum == 1 ? '1st Dose' : '2nd Dose' }}</span>
                            @if($d) <i class="fas fa-check text-teal-600"></i> @endif
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- Dose History --}}
            <div>
                <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                    <i class="fas fa-list-ul text-teal-500 mr-2"></i>
                    Dose History
                </h4>

                @forelse($doses as $dose)
                    <div class="mb-3 rounded-xl border border-gray-100 bg-gray-50 p-4">
                        <div class="flex items-start justify-between mb-2">
                            <div class="flex items-center space-x-2">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-teal-100 text-teal-700 text-xs font-bold">
                                    {{ $dose->dose_number }}
                                </span>
                                <span class="font-semibold text-gray-800 text-sm">{{ $dose->dose_label }}</span>
                                @if($dose->is_external)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs bg-purple-100 text-purple-700">
                                        <i class="fas fa-hospital-alt mr-1"></i> External
                                    </span>
                                @endif
                            </div>
                            @can('midwife')
                            <button onclick="deleteDose({{ $patient->id }}, {{ $dose->id }}, {{ $dose->dose_number }})"
                                    class="text-red-400 hover:text-red-600 transition-colors text-xs">
                                <i class="fas fa-trash-alt"></i>
                            </button>
                            @endcan
                        </div>
                        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-gray-600">
                            <div><span class="text-gray-400">Date:</span> <span class="font-medium">{{ $dose->date_administered->format('M j, Y') }}</span></div>
                            <div><span class="text-gray-400">GA at Dose:</span> <span class="font-medium">{{ $dose->gestational_week_at_dose ? $dose->gestational_week_at_dose . ' weeks' : '—' }}</span></div>
                            <div><span class="text-gray-400">Given By:</span> <span class="font-medium">{{ $dose->administered_by ?: '—' }}</span></div>
                            <div><span class="text-gray-400">Lot #:</span> <span class="font-medium">{{ $dose->vaccineLot?->lot_number ?: ($dose->is_external ? 'N/A (External)' : '—') }}</span></div>
                            @if($dose->dose_number == 1 && $dose->next_dose_due_date)
                                <div class="col-span-2">
                                    <span class="text-gray-400">Next Dose Due:</span>
                                    <span class="font-semibold {{ $dose->next_dose_due_date->isPast() && !$dose2 ? 'text-red-600' : 'text-teal-700' }}">
                                        {{ $dose->next_dose_due_date->format('M j, Y') }}
                                    </span>
                                </div>
                            @endif
                            @if($dose->notes)
                                <div class="col-span-2"><span class="text-gray-400">Notes:</span> {{ $dose->notes }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="text-center py-6 text-gray-400">
                        <i class="fas fa-syringe text-3xl mb-2 text-gray-300"></i>
                        <p class="text-sm">No vaccine doses recorded yet.</p>
                    </div>
                @endforelse
            </div>

            {{-- Add Dose Form (only show if doses < 2 and role is midwife) --}}
            @if($doses->count() < 2 && auth()->user()->role === 'midwife')
                <div class="border-t border-gray-100 pt-5">
                    <h4 class="text-sm font-semibold text-gray-700 mb-3 flex items-center">
                        <i class="fas fa-plus-circle text-teal-500 mr-2"></i>
                        Record
                        @if(!$dose1) 1st @else 2nd @endif
                        Dose
                    </h4>

                    <form id="addDoseForm" onsubmit="submitDose(event, {{ $patient->id }})">
                        @csrf
                        <input type="hidden" name="dose_number" value="{{ !$dose1 ? 1 : 2 }}">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Date Administered <span class="text-red-500">*</span></label>
                                <input type="date" name="date_administered" max="{{ date('Y-m-d') }}" required
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">
                                    Gestational Week at Dose
                                    @if($currentGestationalWeek)
                                        <span class="ml-1 text-teal-600 font-normal">(auto-filled from prenatal record)</span>
                                    @endif
                                </label>
                                <input type="number" name="gestational_week_at_dose" min="1" max="45"
                                       value="{{ $currentGestationalWeek ?? '' }}"
                                       placeholder="e.g. 28"
                                       class="w-full px-3 py-2 border rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400
                                              {{ $currentGestationalWeek ? 'border-teal-300 bg-teal-50' : 'border-gray-200' }}">
                                @if($currentGestationalWeek)
                                    <p class="text-xs text-teal-600 mt-1">
                                        <i class="fas fa-info-circle mr-1"></i>
                                        Currently {{ $currentGestationalWeek }} weeks pregnant. You can edit if needed.
                                    </p>
                                @endif
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Vaccine Lot</label>
                                <select name="vaccine_lot_id" id="vaccineLotSelect"
                                        class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                                    <option value="">— Select lot —</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-600 mb-1">Administered By</label>
                                <input type="text" name="administered_by" placeholder="Staff name" value="{{ auth()->user()->name }}"
                                       class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400">
                            </div>
                        </div>

                        <div class="mt-3">
                            <label class="flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" name="is_external" value="1" id="isExternalCheck"
                                       onchange="document.getElementById('vaccineLotSelect').disabled = this.checked"
                                       class="rounded text-teal-500">
                                <span class="text-xs text-gray-600">Dose administered externally (private clinic) — no inventory deduction</span>
                            </label>
                        </div>

                        <div class="mt-3">
                            <label class="block text-xs font-medium text-gray-600 mb-1">Notes</label>
                            <textarea name="notes" rows="2" placeholder="Optional remarks..."
                                      class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-teal-400 resize-none"></textarea>
                        </div>

                        <div class="mt-4 flex justify-end">
                            <button type="submit" id="submitDoseBtn"
                                    class="inline-flex items-center px-5 py-2 bg-teal-600 text-white rounded-lg text-sm font-semibold hover:bg-teal-700 transition-colors shadow-sm">
                                <i class="fas fa-save mr-2"></i>
                                Save Dose
                            </button>
                        </div>
                    </form>
                </div>
            @endif

        </div>{{-- /p-6 --}}
    </div>{{-- /modal body --}}
</div>{{-- /modal backdrop --}}

<script>
// Load available lots on modal open
document.getElementById('openVaccineCardBtn')?.addEventListener('click', function () {
    fetch('{{ route("midwife.vaccine-lots.available") }}')
        .then(r => r.json())
        .then(lots => {
            const sel = document.getElementById('vaccineLotSelect');
            if (!sel) return;
            sel.innerHTML = '<option value="">— Select lot —</option>';
            lots.forEach(lot => {
                const expiry = new Date(lot.expiry_date).toLocaleDateString('en-PH', {month: 'short', day: 'numeric', year: 'numeric'});
                sel.innerHTML += `<option value="${lot.id}">Lot ${lot.lot_number} (exp. ${expiry}, ${lot.quantity_on_hand} left)</option>`;
            });
        })
        .catch(() => {});
});

function submitDose(e, patientId) {
    e.preventDefault();
    const form = e.target;
    const btn = document.getElementById('submitDoseBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i> Saving...';

    const data = new FormData(form);

    fetch(`/midwife/patients/${patientId}/maternal-immunizations`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
        body: data,
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(res.message, 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Dose';
        }
    })
    .catch(() => {
        showToast('An error occurred. Please try again.', 'error');
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save mr-2"></i> Save Dose';
    });
}

function deleteDose(patientId, doseId, doseNum) {
    if (!confirm(`Delete Dose ${doseNum} record? This will restore inventory if applicable.`)) return;

    fetch(`/midwife/patients/${patientId}/maternal-immunizations/${doseId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'Accept': 'application/json',
        },
    })
    .then(r => r.json())
    .then(res => {
        if (res.success) {
            showToast(res.message, 'success');
            setTimeout(() => location.reload(), 1000);
        } else {
            showToast(res.message || 'Error deleting dose.', 'error');
        }
    })
    .catch(() => showToast('An error occurred.', 'error'));
}

function showToast(message, type = 'success') {
    const color = type === 'success' ? 'bg-green-600' : 'bg-red-600';
    const toast = document.createElement('div');
    toast.className = `fixed bottom-4 right-4 z-[9999] px-5 py-3 rounded-xl text-white text-sm font-medium shadow-lg ${color} transition-all`;
    toast.textContent = message;
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3500);
}
</script>
