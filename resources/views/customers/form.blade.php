@extends('layouts.dashboard')

@section('title', $isEditing ? 'Edit Vehicle Record' : 'Add Vehicle Record')
@section('page-title', $isEditing ? 'Edit Vehicle Record' : 'Add Vehicle Record')
@section('page-subtitle', $isEditing ? 'Modify details of this customer vehicle record.' : 'Create a new customer vehicle record for PUC renewal reminders.')

@section('content')
<style>
*, *::before, *::after { box-sizing: border-box; }

:root {
    --blue-50:  #EFF6FF;
    --blue-100: #DBEAFE;
    --blue-200: #BFDBFE;
    --blue-400: #60A5FA;
    --blue-500: #3B82F6;
    --blue-600: #2563EB;
    --blue-700: #1D4ED8;
    --gray-50:  #F8FAFC;
    --gray-100: #F1F5F9;
    --gray-200: #E2E8F0;
    --gray-300: #CBD5E1;
    --gray-400: #94A3B8;
    --gray-500: #64748B;
    --gray-600: #475569;
    --gray-700: #334155;
    --gray-800: #1E293B;
    --gray-900: #0F172A;
    --green-50:  #F0FDF4;
    --green-100: #DCFCE7;
    --green-600: #16A34A;
    --green-700: #15803D;
    --green-800: #166534;
    --amber-50:  #FFFBEB;
    --amber-100: #FEF3C7;
    --amber-700: #B45309;
    --amber-800: #92400E;
    --red-50:  #FFF1F2;
    --red-100: #FFE4E6;
    --red-600: #DC2626;
    --red-800: #991B1B;
    --orange-50: #FFF7ED;
    --orange-100: #FFEDD5;
    --orange-800: #9A3412;
    --shadow-xs: 0 1px 2px rgba(15,23,42,.04);
    --shadow-sm: 0 1px 3px rgba(15,23,42,.07), 0 1px 2px rgba(15,23,42,.04);
    --shadow-md: 0 4px 6px rgba(15,23,42,.05), 0 2px 4px rgba(15,23,42,.04);
    --shadow-lg: 0 10px 24px rgba(15,23,42,.07), 0 4px 8px rgba(15,23,42,.04);
    --radius-sm: 8px;
    --radius-md: 12px;
    --radius-lg: 16px;
}

.cf-wrap { max-width: 860px; margin: 0 auto; padding: 0px 0px 48px; }

/* Flash alerts */
.cf-alert {
    display: flex; align-items: flex-start; gap: 12px;
    padding: 14px 18px; border-radius: var(--radius-md);
    border: 1px solid; margin-bottom: 16px;
    font-size: 13.5px; font-weight: 600; line-height: 1.5;
    animation: cfFade .2s ease;
}
.cf-alert.success { background: var(--green-50); border-color: #ABEFC6; color: var(--green-800); }
.cf-alert.danger  { background: var(--red-50);   border-color: #FECACA; color: var(--red-800); }
.cf-alert.warning { background: var(--amber-50); border-color: #FDE68A; color: var(--amber-800); }
.cf-alert svg { width: 16px; height: 16px; flex: 0 0 auto; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; margin-top: 1px; }
@keyframes cfFade { from { opacity: 0; transform: translateY(-4px); } to { opacity: 1; transform: none; } }

/* Mode badge */
.cf-mode {
    display: inline-flex; align-items: center; gap: 7px;
    height: 30px; padding: 0 12px; border-radius: 999px;
    font-size: 11.5px; font-weight: 700;
    background: var(--blue-50); border: 1px solid var(--blue-200); color: var(--blue-700);
}
.cf-mode::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; }

/* Buttons */
.cf-btn {
    height: 40px; border-radius: var(--radius-sm); display: inline-flex;
    align-items: center; justify-content: center; gap: 7px; padding: 0 16px;
    font-family: inherit; font-size: 13px; font-weight: 700; cursor: pointer;
    transition: all .14s; white-space: nowrap; border: 1px solid transparent;
}
.cf-btn-primary { background: var(--blue-600); color: #fff !important; border-color: var(--blue-600); box-shadow: 0 2px 8px rgba(37,99,235,.22); }
.cf-btn-primary:hover { background: var(--blue-700); box-shadow: 0 4px 14px rgba(37,99,235,.3); transform: translateY(-1px); }
.cf-btn-primary:disabled { opacity: .5; cursor: not-allowed; transform: none; box-shadow: none; }
.cf-btn-ghost { background: #fff; color: var(--gray-700) !important; border-color: var(--gray-200); box-shadow: var(--shadow-xs); }
.cf-btn-ghost:hover { background: var(--gray-50); border-color: var(--gray-300); transform: translateY(-1px); }
.cf-btn svg { width: 15px; height: 15px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex: 0 0 auto; }

/* Card */
.cf-card {
    background: #fff; border: 1px solid var(--gray-200);
    border-radius: var(--radius-lg); box-shadow: var(--shadow-sm);
    overflow: hidden; position: relative;
    transition: box-shadow .15s;
}
.cf-card::before {
    content: ''; position: absolute; inset: 0 0 auto 0;
    height: 3px; background: linear-gradient(90deg, var(--blue-600), var(--blue-400));
}

/* Section */
.cf-section { padding: 22px 24px; border-bottom: 1px solid var(--gray-100); }
.cf-section:last-child { border-bottom: 0; }
.cf-section-head {
    display: flex; align-items: center; gap: 10px; margin-bottom: 18px;
}
.cf-section-icon {
    width: 34px; height: 34px; border-radius: var(--radius-sm); flex: 0 0 auto;
    background: var(--blue-50); border: 1px solid var(--blue-100);
    color: var(--blue-600); display: flex; align-items: center; justify-content: center;
}
.cf-section-icon svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; }
.cf-section-title { font-size: 13px; font-weight: 700; color: var(--gray-900); letter-spacing: -.01em; }
.cf-section-sub { font-size: 12px; color: var(--gray-400); font-weight: 500; margin-top: 1px; }

/* Grid */
.cf-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.cf-full { grid-column: 1 / -1; }

/* Form fields */
.cf-group { min-width: 0; }
.cf-group label { display: block; margin-bottom: 7px; font-size: 12.5px; font-weight: 700; color: var(--gray-700); }
.cf-req { color: var(--red-600); }
.cf-field { position: relative; }
.cf-field-icon { position: absolute; left: 12px; top: 50%; transform: translateY(-50%); width: 16px; height: 16px; color: var(--gray-400); pointer-events: none; transition: color .14s; }
.cf-field-icon svg { width: 16px; height: 16px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
.cf-field:focus-within .cf-field-icon { color: var(--blue-500); }

.cf-input, .cf-select {
    width: 100%; height: 44px;
    border: 1px solid var(--gray-200); border-radius: var(--radius-sm);
    background: #fff; color: var(--gray-800); padding: 0 12px 0 38px !important;
    outline: none; font-family: inherit; font-size: 13.5px; font-weight: 600;
    transition: border-color .14s, box-shadow .14s; box-shadow: var(--shadow-xs);
}
.cf-input::placeholder { color: var(--gray-400); font-weight: 500; }
.cf-input:focus, .cf-select:focus {
    border-color: var(--blue-500); box-shadow: 0 0 0 3px rgba(59,130,246,.12);
}
.cf-input.cf-input-error, .cf-select.cf-input-error {
    border-color: var(--red-600) !important;
    box-shadow: 0 0 0 3px rgba(220,38,38,.12) !important;
}
.cf-error {
    color: var(--red-600);
    font-size: 11.5px;
    font-weight: 700;
    margin-top: 5px;
    display: block;
}
.cf-input:disabled { background: var(--gray-50); color: var(--gray-400); cursor: not-allowed; }
.cf-input.cf-no-icon { padding-left: 14px !important; }
.cf-select { appearance: none; -webkit-appearance: none; padding-right: 32px; }
.cf-select-arrow { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); pointer-events: none; color: var(--gray-400); }
.cf-select-arrow svg { width: 14px; height: 14px; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; display: block; }
.cf-vehicle { text-transform: uppercase; letter-spacing: .06em; font-weight: 700 !important; }
.cf-hint {
    margin-top: 6px; font-size: 12px; color: var(--gray-400); font-weight: 500; line-height: 1.4;
    /* Prevents a single orphan word from wrapping onto its own line */
    text-wrap: pretty;
    max-width: 46ch;
}

/* EV Alert */
.cf-ev-alert {
    display: none; align-items: flex-start; gap: 10px; margin-top: 10px;
    padding: 12px 14px; border-radius: var(--radius-sm);
    border: 1px solid #FDBA74; background: var(--orange-50);
    color: var(--orange-800); font-size: 13px; font-weight: 600; line-height: 1.5;
}
.cf-ev-alert svg { width: 16px; height: 16px; flex: 0 0 auto; stroke: currentColor; fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; margin-top: 1px; }
.cf-ev-active .cf-ev-alert { display: flex; }
.cf-ev-active [data-puc-dates] { opacity: .45; pointer-events: none; }

/* Quick expiry chips */
.cf-chips { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; padding: 12px 14px; background: var(--gray-50); border: 1px solid var(--gray-100); border-radius: var(--radius-sm); }
.cf-chips-label { font-size: 12px; font-weight: 700; color: var(--gray-500); white-space: nowrap; margin-right: 4px; }
.cf-chip {
    height: 32px; padding: 0 14px; border-radius: 999px;
    border: 1px solid var(--gray-200); background: #fff;
    font-family: inherit; font-size: 12.5px; font-weight: 700;
    color: var(--gray-600); cursor: pointer; transition: all .14s;
    box-shadow: var(--shadow-xs);
}
.cf-chip:hover { border-color: var(--blue-400); color: var(--blue-600); background: var(--blue-50); }
.cf-chip.active { background: var(--blue-600); border-color: var(--blue-600); color: #fff; box-shadow: 0 2px 8px rgba(37,99,235,.22); }

/* Form footer */
.cf-footer {
    display: flex; align-items: center; justify-content: space-between; gap: 14px;
    padding: 18px 24px; background: var(--gray-50);
    border-top: 1px solid var(--gray-100); flex-wrap: wrap;
}
.cf-footer-hint { display: flex; align-items: flex-start; gap: 8px; color: var(--gray-500); font-size: 12.5px; font-weight: 500; line-height: 1.5; max-width: 420px; text-wrap: pretty; }
.cf-footer-hint svg { width: 15px; height: 15px; stroke: var(--blue-500); fill: none; stroke-width: 2; stroke-linecap: round; stroke-linejoin: round; flex: 0 0 auto; margin-top: 1px; }
.cf-footer-btns { display: flex; gap: 10px; }

/* Responsive */
@media (max-width: 700px) {
    .cf-wrap { padding: 16px 14px 40px; }
    .cf-grid { grid-template-columns: 1fr; gap: 14px; }
    .cf-full { grid-column: 1; }
    .cf-footer { flex-direction: column; align-items: stretch; }
    .cf-footer-btns { flex-direction: column; }
    .cf-btn { width: 100%; }
    .cf-chips { flex-wrap: wrap; }
}
</style>

<div class="cf-wrap">
    @if(session('success'))
        <div class="cf-alert success">
            <svg viewBox="0 0 24 24"><path d="M20 6 9 17l-5-5"/></svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('danger'))
        <div class="cf-alert danger">
            <svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            {{ session('danger') }}
        </div>
    @endif

    @if(session('warning'))
        <div class="cf-alert warning">
            <svg viewBox="0 0 24 24"><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
            {{ session('warning') }}
        </div>
    @endif

    <div class="cf-card">
        <form id="cfForm" method="POST" action="{{ $isEditing ? route('customers.update', $record->id) : route('customers.store') }}" novalidate>
            @csrf
            @if($isEditing)
                @method('PUT')
            @endif

            <!-- ── Customer & Contact ── -->
            <div class="cf-section">
                <div class="cf-section-head">
                    <div class="cf-section-icon">
                        <svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                    </div>
                    <div>
                        <div class="cf-section-title">Customer & Contact</div>
                        <div class="cf-section-sub">Who owns this vehicle?</div>
                    </div>
                </div>
                <div class="cf-grid">
                    <div class="cf-group">
                        <label for="customer_name">Customer Name <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
                            <input class="cf-input @error('customer_name') cf-input-error @enderror" id="customer_name" name="customer_name" required
                                   minlength="2" maxlength="100"
                                   placeholder="e.g. Rajesh Kumar" value="{{ old('customer_name', $record->customer_name) }}">
                        </div>
                        <span class="cf-error" id="customer_name-client-error" style="display:none;"></span>
                        @error('customer_name') <span class="cf-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="cf-group">
                        <label for="customer_mobile">Mobile Number <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z"/></svg></span>
                            <input class="cf-input @error('customer_mobile') cf-input-error @enderror" id="customer_mobile" name="customer_mobile" required type="tel"
                                   inputmode="numeric" pattern="[6-9][0-9]{9}" minlength="10" maxlength="10"
                                   placeholder="10 digit mobile" value="{{ old('customer_mobile', $record->customer_mobile) }}">
                        </div>
                        <span class="cf-error" id="customer_mobile-client-error" style="display:none;">Please enter a valid 10-digit mobile number.</span>
                        @error('customer_mobile') <span class="cf-error">{{ $message }}</span> @enderror
                        <p class="cf-hint">Outbound SMS and WhatsApp reminders will be dispatched to this mobile.</p>
                    </div>
                </div>
            </div>

            <!-- ── Vehicle Parameters ── -->
            <div class="cf-section">
                <div class="cf-section-head">
                    <div class="cf-section-icon">
                        <svg viewBox="0 0 24 24"><rect x="1" y="3" width="22" height="13" rx="2" ry="2"/><path d="M12 18v4M9 22h6M4 8h16"/></svg>
                    </div>
                    <div>
                        <div class="cf-section-title">Vehicle Specifications</div>
                        <div class="cf-section-sub">Registration, Class, Fuel and Pricing</div>
                    </div>
                </div>
                <div class="cf-grid">
                    <div class="cf-group">
                        <label for="vehicle_number">Vehicle Number <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg></span>
                            <input class="cf-input cf-vehicle @error('vehicle_number') cf-input-error @enderror" id="vehicle_number" name="vehicle_number" required
                                   pattern="[A-Z0-9]{6,12}" minlength="6" maxlength="12"
                                   placeholder="e.g. MH12AB1234" value="{{ old('vehicle_number', $record->vehicle_number) }}">
                        </div>
                        <span class="cf-error" id="vehicle_number-client-error" style="display:none;">Please enter a valid vehicle number.</span>
                        @error('vehicle_number') <span class="cf-error">{{ $message }}</span> @enderror
                        <p class="cf-hint">Spaces and dashes will be automatically cleaned.</p>
                    </div>
                    <div class="cf-group">
                        <label for="vehicle_type">Vehicle Type <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><circle cx="7" cy="17" r="2"/><circle cx="17" cy="17" r="2"/><path d="M5 17h12v-6H5M9 11V6h6v5"/></svg></span>
                            <select class="cf-select @error('vehicle_type') cf-input-error @enderror" id="vehicle_type" name="vehicle_type" required>
                                @foreach(['Bike', 'Car', 'Auto', 'Truck', 'Bus', 'Other'] as $type)
                                    <option value="{{ $type }}" {{ old('vehicle_type', $record->vehicle_type) === $type ? 'selected' : '' }}>{{ $type }}</option>
                                @endforeach
                            </select>
                            <span class="cf-select-arrow"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>
                        </div>
                        @error('vehicle_type') <span class="cf-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="cf-group">
                        <label for="fuel_type">Fuel Type <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><path d="M3 22h12M7 22V5a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v17M15 8h2a2 2 0 0 1 2 2v6a2 2 0 0 0 2 2"/></svg></span>
                            <select class="cf-select @error('fuel_type') cf-input-error @enderror" id="fuel_type" name="fuel_type" required>
                                <option value="">Select fuel type</option>
                                @foreach(['Petrol', 'Diesel', 'CNG', 'LPG', 'Hybrid', 'Electric'] as $fuel)
                                    <option value="{{ $fuel }}" {{ old('fuel_type', $record->fuel_type) === $fuel ? 'selected' : '' }}>{{ $fuel }}</option>
                                @endforeach
                            </select>
                            <span class="cf-select-arrow"><svg viewBox="0 0 24 24"><path d="m6 9 6 6 6-6"/></svg></span>
                        </div>
                        @error('fuel_type') <span class="cf-error">{{ $message }}</span> @enderror
                        <div class="cf-ev-alert" id="ev-alert-container">
                            <svg viewBox="0 0 24 24"><path d="M12 9v4M12 17h.01"/><path d="M10.29 3.86 1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/></svg>
                            Electric vehicles do not need PUC expiry reminders. Change to Petrol, Diesel, CNG, LPG or Hybrid to save.
                        </div>
                    </div>
                    <div class="cf-group">
                        <label for="puc_certificate_number">PUC Certificate No.</label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8Z"/><path d="M14 2v6h6M8 13h8M8 17h5"/></svg></span>
                            <input class="cf-input @error('puc_certificate_number') cf-input-error @enderror" id="puc_certificate_number" name="puc_certificate_number"
                                   maxlength="50"
                                   placeholder="Optional" value="{{ old('puc_certificate_number', $record->puc_certificate_number) }}">
                        </div>
                        @error('puc_certificate_number') <span class="cf-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="cf-group">
                        <label for="puc_price">PUC Charge / Price (₹) <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <span class="cf-field-icon">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M6 3h12M6 8h12M6 13h8.5a4.5 4.5 0 0 0 0-9H6M6 13l9 9"></path>
                                </svg>
                            </span>
                            <input class="cf-input @error('puc_price') cf-input-error @enderror" id="puc_price" type="number" name="puc_price" step="0.01" min="0" max="999999" required
                                   placeholder="0.00" value="{{ old('puc_price', $record->puc_price) }}" style="padding-left: 38px !important;">
                        </div>
                        <span class="cf-error" id="puc_price-client-error" style="display:none;">Please enter a valid price.</span>
                        @error('puc_price') <span class="cf-error">{{ $message }}</span> @enderror
                    </div>
                    <div class="cf-group">
                        <label for="notes">Notes</label>
                        <div class="cf-field">
                            <span class="cf-field-icon"><svg viewBox="0 0 24 24"><path d="M12 20h9"/><path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/></svg></span>
                            <input class="cf-input @error('notes') cf-input-error @enderror" id="notes" name="notes" maxlength="255" placeholder="Optional notes" value="{{ old('notes', $record->notes) }}">
                        </div>
                        @error('notes') <span class="cf-error">{{ $message }}</span> @enderror
                    </div>
                </div>
            </div>

            <!-- ── Validity Dates ── -->
            <div class="cf-section" data-puc-dates id="puc-dates-section">
                <div class="cf-section-head">
                    <div class="cf-section-icon">
                        <svg viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                    </div>
                    <div>
                        <div class="cf-section-title">Validity Dates</div>
                        <div class="cf-section-sub">PUC issue and expiry</div>
                    </div>
                </div>
                <div class="cf-grid">
                    <div class="cf-group">
                        <label for="issue_date">Issue Date <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <input class="cf-input cf-no-icon @error('issue_date') cf-input-error @enderror" id="issue_date" type="date" name="issue_date"
                                   required value="{{ old('issue_date', $record->issue_date ? $record->issue_date->format('Y-m-d') : '') }}">
                        </div>
                        <span class="cf-error" id="issue_date-client-error" style="display:none;"></span>
                        @error('issue_date') <span class="cf-error">{{ $message }}</span> @enderror
                        <p class="cf-hint">Date printed on the PUC certificate.</p>
                    </div>
                    <div class="cf-group">
                        <label for="expiry_date">Expiry Date <span class="cf-req">*</span></label>
                        <div class="cf-field">
                            <input class="cf-input cf-no-icon @error('expiry_date') cf-input-error @enderror" id="expiry_date" type="date" name="expiry_date"
                                   required value="{{ old('expiry_date', $record->expiry_date ? $record->expiry_date->format('Y-m-d') : '') }}">
                        </div>
                        <span class="cf-error" id="expiry_date-client-error" style="display:none;">Expiry date must be after the issue date.</span>
                        @error('expiry_date') <span class="cf-error">{{ $message }}</span> @enderror
                        <p class="cf-hint">Use quick buttons below or enter a custom date.</p>
                    </div>
                    <div class="cf-group cf-full">
                        <label>Quick Expiry</label>
                        <div class="cf-chips">
                            <span class="cf-chips-label">From issue date →</span>
                            <button class="cf-chip" type="button" data-months="6">6 Months</button>
                            <button class="cf-chip" type="button" data-months="12">1 Year</button>
                            <button class="cf-chip active" type="button" data-custom="1">Custom date</button>
                        </div>
                        <p class="cf-hint">Enter the issue date, then tap 6 Months or 1 Year to auto-fill expiry.</p>
                    </div>
                </div>
            </div>

            <!-- ── Footer ── -->
            <div class="cf-footer">
                <div class="cf-footer-hint">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width:16px;height:16px;"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="M9 12l2 2 4-4"/></svg>
                    After saving, this record will appear in customer records, expiry reports and reminder workflow.
                </div>
                <div class="cf-footer-btns">
                    <a href="{{ route('customers.index') }}" class="cf-btn cf-btn-ghost">Cancel</a>
                    <button class="cf-btn cf-btn-primary" type="submit" id="cfSubmitBtn">
                        <svg viewBox="0 0 24 24"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"/><polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/></svg>
                        {{ $isEditing ? 'Update Record' : 'Save Record' }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var form         = document.getElementById('cfForm');
    var fuelSelect   = document.getElementById('fuel_type');
    var vehicleInput = document.getElementById('vehicle_number');
    var mobileInput  = document.getElementById('customer_mobile');
    var nameInput    = document.getElementById('customer_name');
    var issueInput   = document.getElementById('issue_date');
    var expiryInput  = document.getElementById('expiry_date');
    var priceInput   = document.getElementById('puc_price');
    var submitBtn    = document.getElementById('cfSubmitBtn');
    var monthBtns    = document.querySelectorAll('[data-months]');
    var customBtn    = document.querySelector('[data-custom]');
    var evAlert      = document.getElementById('ev-alert-container');
    var datesSection = document.getElementById('puc-dates-section');

    /* ---------- validation rules ---------- */
    var MOBILE_REGEX  = /^[6-9][0-9]{9}$/;      // 10 digits, starts 6-9
    var VEHICLE_REGEX = /^[A-Z0-9]{6,12}$/;      // cleaned uppercase reg. no.

    function showFieldError(input, message) {
        input.classList.add('cf-input-error');
        var err = document.getElementById(input.id + '-client-error');
        if (err) {
            if (message) err.textContent = message;
            err.style.display = 'block';
        }
    }

    function clearFieldError(input) {
        input.classList.remove('cf-input-error');
        var err = document.getElementById(input.id + '-client-error');
        if (err) err.style.display = 'none';
    }

    function validateName() {
        if (!nameInput) return true;
        var val = nameInput.value.trim();
        if (val.length < 2) {
            showFieldError(nameInput, 'Customer name must be at least 2 characters.');
            return false;
        }
        clearFieldError(nameInput);
        return true;
    }

    function validateMobile() {
        if (!mobileInput) return true;
        if (!MOBILE_REGEX.test(mobileInput.value.trim())) {
            showFieldError(mobileInput);
            return false;
        }
        clearFieldError(mobileInput);
        return true;
    }

    function validateVehicle() {
        if (!vehicleInput) return true;
        if (!VEHICLE_REGEX.test(vehicleInput.value.trim())) {
            showFieldError(vehicleInput);
            return false;
        }
        clearFieldError(vehicleInput);
        return true;
    }

    function validatePrice() {
        if (!priceInput || priceInput.disabled) return true;
        var val = parseFloat(priceInput.value);
        if (isNaN(val) || val < 0) {
            showFieldError(priceInput);
            return false;
        }
        clearFieldError(priceInput);
        return true;
    }

    function validateDates() {
        if (!issueInput || !expiryInput || issueInput.disabled) return true;
        var valid = true;
        if (!issueInput.value) {
            showFieldError(issueInput, 'Issue date is required.');
            valid = false;
        } else {
            clearFieldError(issueInput);
        }
        if (expiryInput.value && issueInput.value) {
            var issue  = parseLocalDate(issueInput.value);
            var expiry = parseLocalDate(expiryInput.value);
            if (expiry <= issue) {
                showFieldError(expiryInput, 'Expiry date must be after the issue date.');
                valid = false;
            } else {
                clearFieldError(expiryInput);
            }
        }
        return valid;
    }

    function pad(n) { return String(n).padStart(2, '0'); }

    function parseLocalDate(val) {
        if (!val) return null;
        var p = val.split('-');
        if (p.length !== 3) return null;
        return new Date(+p[0], +p[1] - 1, +p[2]);
    }

    function toInputDate(d) {
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate());
    }

    function addMonths(dateStr, months) {
        var d = parseLocalDate(dateStr);
        if (!d || isNaN(d.getTime())) return '';
        var day = d.getDate();
        d.setMonth(d.getMonth() + +months);
        if (d.getDate() < day) d.setDate(0);
        return toInputDate(d);
    }

    function setActiveChip(active) {
        monthBtns.forEach(function (b) { b.classList.toggle('active', b.getAttribute('data-months') === String(active)); });
        if (customBtn) customBtn.classList.toggle('active', active === 'custom');
    }

    function updateEVState() {
        var isEV = fuelSelect && fuelSelect.value === 'Electric';
        if (isEV) {
            evAlert.style.display = 'flex';
            datesSection.style.opacity = '0.45';
            datesSection.style.pointerEvents = 'none';
        } else {
            evAlert.style.display = 'none';
            datesSection.style.opacity = '1';
            datesSection.style.pointerEvents = 'auto';
        }
        if (issueInput)  issueInput.disabled  = isEV;
        if (expiryInput) expiryInput.disabled = isEV;
        if (priceInput)  priceInput.disabled  = isEV;
        if (isEV) {
            clearFieldError(issueInput);
            clearFieldError(expiryInput);
            clearFieldError(priceInput);
        }
        if (submitBtn) {
            submitBtn.disabled = isEV;
            submitBtn.title    = isEV ? 'Electric vehicles do not need PUC reminders.' : '';
        }
    }

    /* Vehicle number formatting + live validation */
    if (vehicleInput) {
        vehicleInput.addEventListener('input', function () {
            var pos = vehicleInput.selectionStart;
            vehicleInput.value = vehicleInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
            vehicleInput.setSelectionRange(pos, pos);
        });
        vehicleInput.addEventListener('blur', validateVehicle);
    }

    /* Mobile digits only + live validation */
    if (mobileInput) {
        mobileInput.addEventListener('input', function () {
            mobileInput.value = mobileInput.value.replace(/\D/g, '').slice(0, 10);
            if (mobileInput.value.length === 10) validateMobile();
            else clearFieldError(mobileInput);
        });
        mobileInput.addEventListener('blur', validateMobile);
    }

    if (nameInput) nameInput.addEventListener('blur', validateName);
    if (priceInput) priceInput.addEventListener('blur', validatePrice);
    if (issueInput) issueInput.addEventListener('blur', validateDates);
    if (expiryInput) expiryInput.addEventListener('blur', validateDates);

    /* Fuel change */
    if (fuelSelect) fuelSelect.addEventListener('change', updateEVState);

    /* Quick expiry chips */
    monthBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            if (!issueInput || !expiryInput) return;
            if (!issueInput.value) issueInput.value = toInputDate(new Date());
            var months = btn.getAttribute('data-months');
            expiryInput.value = addMonths(issueInput.value, months);
            setActiveChip(months);
            validateDates();
        });
    });

    if (customBtn) {
        customBtn.addEventListener('click', function () {
            if (expiryInput) expiryInput.focus();
            setActiveChip('custom');
        });
    }

    /* Re-calc expiry when issue date changes */
    if (issueInput) {
        issueInput.addEventListener('change', function () {
            var active = document.querySelector('[data-months].active');
            if (active && expiryInput) {
                expiryInput.value = addMonths(issueInput.value, active.getAttribute('data-months'));
            }
            validateDates();
        });
    }

    /* Mark custom when expiry manually changed */
    if (expiryInput) {
        expiryInput.addEventListener('change', function () { setActiveChip('custom'); validateDates(); });
    }

    /* Form submit guard: EV block + full client-side validation */
    if (form) {
        form.addEventListener('submit', function (e) {
            if (fuelSelect && fuelSelect.value === 'Electric') {
                e.preventDefault();
                updateEVState();
                fuelSelect.focus();
                alert('Electric vehicles do not require PUC. Please change the fuel type to save this record.');
                return;
            }

            var isValid = true;
            if (!validateName())    isValid = false;
            if (!validateMobile())  isValid = false;
            if (!validateVehicle()) isValid = false;
            if (!validatePrice())   isValid = false;
            if (!validateDates())   isValid = false;

            if (!isValid) {
                e.preventDefault();
                var firstError = form.querySelector('.cf-input-error');
                if (firstError) firstError.focus();
            }
        });
    }

    updateEVState();
});
</script>
@endsection
