<div id="userModal" 
     class="modal-overlay hidden fixed inset-0 bg-gray-900 bg-opacity-50 overflow-y-auto h-full w-full z-50 flex items-start justify-center p-4"
     role="dialog"
     aria-modal="true"
     onclick="closeModal(event)">
    
    <div class="modal-content relative w-full max-w-4xl bg-white rounded-xl shadow-2xl p-6 my-8"
         onclick="event.stopPropagation()">
        
        <!-- Modal Header -->
        <div class="flex justify-between items-center mb-6">
            <h2 id="modalTitle" class="text-xl font-semibold text-gray-900 flex items-center">
                <i class="fas fa-plus text-[#68727A] mr-2"></i>
                Add User
            </h2>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors p-1 rounded-full hover:bg-gray-100">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <!-- Modal Body Form -->
        <!-- ADD & EDIT FORM-->

<form id="userForm" action="{{ route((Auth::user()->role === 'admin' ? 'admin' : 'midwife') . '.user.store') }}" method="POST" class="space-y-5" novalidate>
            @csrf
            <input type="hidden" id="userId" name="id">
            
            <!-- Show server-side validation errors -->
            @if ($errors->any())
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                    <div class="font-medium">Please correct the following errors:</div>
                    <ul class="list-disc list-inside mt-2">
                        @foreach ($errors->all() as $error)
                            <li class="text-sm">{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            
            <div class="modal-form-grid grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Personal Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Personal Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Full Name *</label>
                            <input type="text" id="name" name="name" required 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('name') error-border @enderror"
                                   placeholder="Enter full name"
                                   value="{{ old('name') }}">
                            @error('name')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gender *</label>
                            <div class="flex space-x-6">
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Male" required class="text-[#68727A] focus:ring-[#68727A]" {{ old('gender') == 'Male' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">Male</span>
                                </label>
                                <label class="flex items-center">
                                    <input type="radio" name="gender" value="Female" required class="text-[#68727A] focus:ring-[#68727A]" {{ old('gender') == 'Female' ? 'checked' : '' }}>
                                    <span class="ml-2 text-gray-700">Female</span>
                                </label>
                            </div>
                            @error('gender')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Age *</label>
                            <input type="number" id="age" name="age" required min="18" max="100"
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('age') error-border @enderror"
                                   placeholder="Enter age"
                                   value="{{ old('age') }}">
                            @error('age')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Account Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Account Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Username *</label>
                            <input type="text" id="username" name="username" required
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('username') error-border @enderror"
                                   placeholder="Enter username"
                                   value="{{ old('username') }}">
                            @error('username')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Role *</label>
                            <select id="role" name="role" required class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('role') error-border @enderror">
                                <option value="">Select Role</option>
                                <option value="midwife" {{ old('role') == 'midwife' ? 'selected' : '' }}>Midwife</option>
                                <option value="bhw" {{ old('role') == 'bhw' ? 'selected' : '' }}>Barangay Health Worker (BHW)</option>
                                @if(Auth::user()->role === 'admin')
                                <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Admin</option>
                                @endif
                            </select>
                            @error('role')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div id="passwordSection">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Password *</label>
                            <input type="password" id="password" name="password" required
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg @error('password') error-border @enderror"
                                   placeholder="Enter password"
                                   minlength="8">
                            <div class="text-xs text-gray-500 mt-1">Minimum 8 characters</div>
                            @error('password')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Contact Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">Contact Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Contact Number *</label>
                            <div class="relative">
                                <span class="phone-prefix">+63</span>
                                <input type="tel" id="contact_number" name="contact_number" required
                                       class="form-input input-clean phone-input w-full px-4 py-2.5 rounded-lg @error('contact_number') error-border @enderror"
                                       placeholder="9123456789"
                                       pattern="[9]\d{9}"
                                       maxlength="10"
                                       value="{{ old('contact_number') }}">
                            </div>
                            <div class="text-xs text-gray-500 mt-1">Format: 9123456789 (Philippine mobile number)</div>
                            @error('contact_number')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Address</label>
                            <textarea id="address" name="address" rows="4"
                                      class="form-input input-clean w-full px-4 py-2.5 rounded-lg resize-none @error('address') error-border @enderror"
                                      placeholder="Enter complete address">{{ old('address') }}</textarea>
                            @error('address')
                                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- System Information -->
                <div>
                    <div class="section-header border-b pb-2 mb-4">
                        <h3 class="text-lg font-semibold text-gray-900">System Information</h3>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Date Created</label>
                            <input type="text" 
                                   class="form-input input-clean w-full px-4 py-2.5 rounded-lg bg-gray-50"
                                   value="{{ now()->format('F j, Y') }}"
                                   readonly>
                            <div class="text-xs text-gray-500 mt-1">Automatically set to current date</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="flex flex-col sm:flex-row justify-end space-y-3 sm:space-y-0 sm:space-x-3 pt-4 border-t">
                <button type="button" onclick="closeModal()" class="btn-minimal px-6 py-2.5 text-gray-600 border border-gray-300 rounded-lg">
                    Cancel
                </button>
                <button type="submit" id="submit-btn" class="px-6 py-2.5 bg-primary text-white rounded-lg hover:bg-primary-dark transition-all duration-200 font-medium">
                    <i class="fas fa-save mr-2"></i>Save User
                </button>
            </div>
        </form>
    </div>
</div>

