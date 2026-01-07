<!-- View Immunization Modal -->
<div id="viewImmunizationModal" 
     class="hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-center justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeViewModal(event)">
    
    <div id="viewImmunizationModalContent" 
         class="relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 transform -translate-y-10 opacity-0 transition-all duration-300"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-primary">
            <h2 class="text-2xl font-bold text-gray-900 flex items-center">
                <i class="fas fa-syringe text-primary mr-3"></i>
                Immunization Details
            </h2>
            <button onclick="closeViewModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-2 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        
        <!-- Patient Information Card -->
        <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-lg p-6 mb-6 border-l-4 border-primary">
            <div class="flex items-start space-x-4">
                <div class="flex-shrink-0">
                    <div class="w-16 h-16 bg-primary rounded-full flex items-center justify-center">
                        <i class="fas fa-baby text-white text-2xl"></i>
                    </div>
                </div>
                <div class="flex-1">
                    <h3 class="text-xl font-bold text-gray-900 mb-2" id="modalChildName">-</h3>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 text-sm">
                        <div class="flex items-center">
                            <i class="fas fa-birthday-cake text-primary mr-2"></i>
                            <span class="text-gray-600 font-medium">Age:</span>
                            <span class="ml-2 text-gray-900 font-semibold" id="modalChildAge">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-venus-mars text-primary mr-2"></i>
                            <span class="text-gray-600 font-medium">Gender:</span>
                            <span class="ml-2 text-gray-900 font-semibold" id="modalChildGender">-</span>
                        </div>
                        <div class="flex items-center">
                            <i class="fas fa-female text-primary mr-2"></i>
                            <span class="text-gray-600 font-medium">Mother:</span>
                            <span class="ml-2 text-gray-900 font-semibold" id="modalMotherName">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Body -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Vaccine Information -->
            <div class="space-y-4">
                <div class="bg-green-50 rounded-lg p-4 border-l-4 border-green-500">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center mb-4">
                        <i class="fas fa-vial text-green-600 mr-2"></i>
                        Vaccine Details
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Vaccine:</span>
                            <span id="modalVaccineName" class="text-sm text-gray-900 font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Dose:</span>
                            <span id="modalDose" class="text-sm text-gray-900 font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Status:</span>
                            <span id="modalStatus" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold">
                                <i id="modalStatusIcon" class="mr-1"></i>
                                <span id="modalStatusText">-</span>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Schedule Information -->
            <div class="space-y-4">
                <div class="bg-blue-50 rounded-lg p-4 border-l-4 border-blue-500">
                    <h3 class="text-lg font-bold text-gray-900 flex items-center mb-4">
                        <i class="fas fa-calendar-alt text-blue-600 mr-2"></i>
                        Schedule Information
                    </h3>
                    
                    <div class="space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Date:</span>
                            <span id="modalScheduleDate" class="text-sm text-gray-900 font-semibold">-</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm font-medium text-gray-600">Time:</span>
                            <span id="modalScheduleTime" class="text-sm text-gray-900 font-semibold">-</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Additional Information -->
        <div class="mt-6">
            <div class="bg-amber-50 rounded-lg p-4 border-l-4 border-amber-500">
                <h3 class="text-lg font-bold text-gray-900 flex items-center mb-3">
                    <i class="fas fa-sticky-note text-amber-600 mr-2"></i>
                    Additional Information
                </h3>
                
                <div>
                    <span class="text-sm font-medium text-gray-600 block mb-2">Notes:</span>
                    <div id="modalNotes" class="text-sm text-gray-900 bg-white p-3 rounded-lg min-h-[60px] border border-amber-200">-</div>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-6 border-t mt-6">
            <button onclick="closeViewModal()" class="px-6 py-2.5 text-gray-700 bg-gray-100 border border-gray-300 rounded-lg hover:bg-gray-200 transition-colors font-medium">
                <i class="fas fa-times mr-2"></i>Close
            </button>
        </div>
    </div>
</div>
