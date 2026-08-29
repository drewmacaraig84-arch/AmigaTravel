@extends('layouts.app')

@section('content')
@php
    $showCancelSuggestion = request()->query('show_cancel_suggestion');
    $suggestTxn = request()->query('transaction_number');
@endphp
@if($showCancelSuggestion)
    <div x-data="{ open: true }" x-init="open = true">
        <div x-show="open" class="fixed inset-0 z-50 flex items-center justify-center">
            <div class="fixed inset-0 bg-black/40" @click="open = false"></div>
            <div class="relative max-w-lg w-full rounded-2xl bg-white p-6 z-10 shadow-lg relative ws-sbtn-container">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'cancel_modal' })" class="ws-sbtn absolute top-2 right-2"></button> @endif
                <h3 class="text-lg font-semibold text-slate-900">{{ data_get($pageContent, 'cancel_modal_title', 'Want to cancel your booking?') }}</h3>
                <p class="mt-3 text-sm text-slate-700">{{ data_get($pageContent, 'cancel_modal_desc', 'We received your proof of payment. If you change your mind, you can start a 5-minute cancellation window now to request a refund. After 5 minutes, cancellation will no longer be available.') }}</p>
                <div class="mt-4 flex gap-3 justify-end">
                    <a href="{{ url('/') }}" class="inline-flex items-center justify-center rounded-3xl border border-slate-200 px-5 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">{{ data_get($pageContent, 'cancel_modal_btn_cancel', 'Maybe later') }}</a>
                    <a href="{{ url('/book/status?transaction_number=' . urlencode($suggestTxn) . '&start_cancellation=1') }}" class="inline-flex items-center justify-center rounded-3xl bg-amber-600 px-5 py-2 text-sm font-semibold text-white hover:bg-amber-700">{{ data_get($pageContent, 'cancel_modal_btn_confirm', 'Start cancellation') }}</a>
                </div>
            </div>
        </div>
    </div>
    {{-- Modal for promo image preview --}}
    <div x-show="modalOpen" x-cloak x-transition class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 p-4" style="display:none;">
        <div class="relative max-w-4xl w-full">
            <button @click="modalOpen = false; modalImage = null" class="absolute right-2 top-2 z-20 inline-flex items-center justify-center w-10 h-10 rounded-full bg-white text-slate-700 shadow-md hover:bg-slate-100">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
            <img :src="modalImage" alt="Promotion" class="w-full max-h-[80vh] object-contain rounded-lg shadow-2xl bg-white">
        </div>
    </div>
@endif

{{-- NEW: Airpaz-Style Green Hero Banner --}}
<div class="relative w-full bg-gradient-to-b from-[#008000] to-green-400">
    @if(session()->has('booking_draft'))
        <div class="w-full bg-pink-50/95 border-b border-pink-200 px-4 sm:px-6 lg:px-8 py-3.5 text-slate-900 shadow-sm relative z-20">
            <div class="max-w-7xl mx-auto flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <p class="text-sm font-semibold text-pink-700">You have a pending booking in progress.</p>
                    <p class="mt-0.5 text-xs text-slate-600">Return to complete your booking or cancel the draft to start a new one.</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 shrink-0">
                    <a href="{{ url('/book/new') }}" class="inline-flex items-center justify-center rounded-full bg-pink-600 px-4 py-2 text-xs font-semibold text-white shadow-sm transition hover:bg-pink-700">Return to booking</a>
                    <form method="POST" action="{{ route('booking.draft.cancel') }}" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center rounded-full border border-pink-600 px-4 py-2 text-xs font-semibold text-pink-700 transition hover:bg-pink-100">Cancel draft</button>
                    </form>
                </div>
            </div>
        </div>
    @endif
    <div class="pt-0 pb-28 sm:pb-32 lg:pb-36 px-4 sm:px-6 lg:px-8 overflow-hidden">
        {{-- Header Title, Subtitle & Video --}}
        <div class="max-w-5xl mx-auto flex flex-col md:flex-row items-center justify-between gap-6 md:gap-8 relative z-10 ws-sbtn-container">
            <div class="text-left w-full md:flex-1 relative"
                 x-data="{ showTitle: false, showSubtitle: false }" 
                 x-init="setTimeout(() => showTitle = true, 150); setTimeout(() => showSubtitle = true, 450)">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'welcome_section' })" class="ws-sbtn absolute top-0 right-0 z-20"></button> @endif
                
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white tracking-tight inline-block transition-all duration-300 hover:scale-105 hover:drop-shadow-[0_0_15px_rgba(255,255,255,0.4)] cursor-default origin-left"
                    x-show="showTitle"
                    x-transition:enter="transition ease-out duration-[800ms] transform"
                    x-transition:enter-start="opacity-0 translate-y-12 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    style="display: none;">
                    {{ $pageContent['welcome_title'] ?? 'Welcome to Amiga Gracia' }}
                </h1>
                
                <p class="mt-4 text-base sm:text-lg text-white/90 max-w-4xl font-medium transition-all duration-300 hover:text-white hover:translate-x-2 cursor-default"
                   x-show="showSubtitle"
                   x-transition:enter="transition ease-out duration-[800ms] transform"
                   x-transition:enter-start="opacity-0 translate-y-8"
                   x-transition:enter-end="opacity-100 translate-y-0"
                   style="display: none;">
                    {{ $pageContent['welcome_subtitle'] ?? 'Your journey deserves more than a destination - it deserves an exceptional experience' }}
                </p>
            </div>
            
            <div class="w-40 h-40 sm:w-48 sm:h-48 md:w-56 md:h-56 lg:w-64 lg:h-64 flex-shrink-0 mx-auto md:mx-0 rounded-full overflow-hidden shadow-[0_0_30px_rgba(255,255,255,0.1)] border-4 border-white/20 relative">
                <video class="w-full h-full object-cover" autoplay loop muted playsinline>
                    <source src="{{ asset('video/animation1.mp4') }}" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</div>

{{-- White Search Box (overlapping Green Hero and White Section below like Airpaz) --}}
<script>
    window.AMIGA_ACTIVE_ROUTES = @json($activeRoutes ?? []);
    window.AMIGA_VEHICLE_RATES = @json($vehicleRates ?? []);
    window.AMIGA_VEHICLE_BRANDS = @json($vehicleBrands ?? []);

    function amigaDatePicker(type) {
        return {
            isOpen: false,
            viewYear: new Date().getFullYear(),
            viewMonth: new Date().getMonth() + 1,
            
            init() {
                let val = type === 'departure' ? this.departure_date : this.return_date;
                if (val) {
                    let parts = val.split('-');
                    if (parts.length === 3) {
                        this.viewYear = parseInt(parts[0], 10);
                        this.viewMonth = parseInt(parts[1], 10);
                    }
                }
                this.$watch(type === 'departure' ? 'departure_date' : 'return_date', (newVal) => {
                    if (newVal) {
                        let parts = newVal.split('-');
                        if (parts.length === 3) {
                            this.viewYear = parseInt(parts[0], 10);
                            this.viewMonth = parseInt(parts[1], 10);
                        }
                    }
                });
                this.$watch(type === 'departure' ? 'enabledDepartureDates' : 'enabledReturnDates', (dates) => {
                    let current = type === 'departure' ? this.departure_date : this.return_date;
                    if (current && dates.length > 0 && !dates.includes(current)) {
                        if (type === 'departure') {
                            this.departure_date = '';
                        } else {
                            this.return_date = '';
                        }
                    }
                });
            },
            
            get isDisabled() {
                if (!this.origin || !this.destination) return true;
                let enabled = type === 'departure' ? this.enabledDepartureDates : this.enabledReturnDates;
                return enabled.length === 0;
            },

            get placeholderText() {
                if (!this.origin || !this.destination) {
                    return 'Select origin & destination first';
                }
                let enabled = type === 'departure' ? this.enabledDepartureDates : this.enabledReturnDates;
                if (enabled.length === 0) {
                    return type === 'departure' ? 'No schedules available' : 'No return schedules available';
                }
                return 'Select date';
            },

            get formattedDate() {
                let val = type === 'departure' ? this.departure_date : this.return_date;
                if (!val) return '';
                let parts = val.split('-');
                if (parts.length !== 3) return val;
                let dateObj = new Date(parts[0], parts[1] - 1, parts[2]);
                return dateObj.toLocaleDateString('default', { month: 'short', day: '2-digit', year: 'numeric' });
            },

            get monthLabel() {
                const date = new Date(this.viewYear, this.viewMonth - 1, 1);
                return date.toLocaleString('default', { month: 'long' });
            },

            get minDateStr() {
                if (type === 'return' && this.departure_date) {
                    return this.departure_date;
                }
                let today = new Date();
                let y = today.getFullYear();
                let m = String(today.getMonth() + 1).padStart(2, '0');
                let d = String(today.getDate()).padStart(2, '0');
                return `${y}-${m}-${d}`;
            },

            get calendarDays() {
                const firstDay = new Date(this.viewYear, this.viewMonth - 1, 1);
                const startOffset = firstDay.getDay();
                const daysInMonth = new Date(this.viewYear, this.viewMonth, 0).getDate();
                
                let days = Array(startOffset).fill(null);
                let enabled = type === 'departure' ? this.enabledDepartureDates : this.enabledReturnDates;
                let minDate = this.minDateStr;
                
                for (let day = 1; day <= daysInMonth; day++) {
                    const m = String(this.viewMonth).padStart(2, '0');
                    const d = String(day).padStart(2, '0');
                    const dateStr = `${this.viewYear}-${m}-${d}`;
                    
                    let disabled = false;
                    if (dateStr < minDate) {
                        disabled = true;
                    }
                    if (enabled.length > 0 && !enabled.includes(dateStr)) {
                        disabled = true;
                    }
                    
                    days.push({ day, disabled, dateStr });
                }
                
                while (days.length % 7 !== 0) {
                    days.push(null);
                }
                
                return days;
            },

            prevMonth() {
                if (this.viewMonth === 1) {
                    this.viewMonth = 12;
                    this.viewYear--;
                } else {
                    this.viewMonth--;
                }
            },

            nextMonth() {
                if (this.viewMonth === 12) {
                    this.viewMonth = 1;
                    this.viewYear++;
                } else {
                    this.viewMonth++;
                }
            },

            selectDate(dayObj) {
                if (dayObj.disabled) return;
                if (type === 'departure') {
                    this.departure_date = dayObj.dateStr;
                    if (this.return_date && this.return_date < dayObj.dateStr) {
                        this.return_date = '';
                    }
                    this.errors.departure_date = '';
                } else {
                    this.return_date = dayObj.dateStr;
                    this.errors.return_date = '';
                }
                this.isOpen = false;
            }
        };
    }
@php
    $operatorsList = \App\Models\Operator::where('is_active', true)
        ->orderByRaw("CASE 
            WHEN LOWER(name) LIKE '%starlite%' THEN 1 
            WHEN LOWER(name) LIKE '%2go%' THEN 2 
            WHEN LOWER(name) LIKE '%cebu%' THEN 3 
            WHEN LOWER(name) LIKE '%philippine%' OR LOWER(name) LIKE '%pal%' THEN 4 
            WHEN LOWER(name) LIKE '%airasia%' THEN 5 
            ELSE 6 END")
        ->orderBy('name')
        ->get()
        ->map(function($op) {
            return [
                'name' => $op->name,
                'value' => normalize_operator_name($op->name) ?: $op->name,
                'logo' => $op->logo_path ? \Illuminate\Support\Facades\Storage::url($op->logo_path) : null,
                'mode' => $op->mode
            ];
        })->toArray();
@endphp
    window.AMIGA_OPERATORS_LIST = @json($operatorsList);
</script>
<div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-30 -mt-16 sm:-mt-20 lg:-mt-20"
     x-data="{
             trip_type: 'one_way',
             mode: 'ferry',
             operator: '',
             origin: '',
             destination: '',
             departure_date: '{{ \Carbon\Carbon::tomorrow()->format('Y-m-d') }}',
             return_date: '{{ \Carbon\Carbon::tomorrow()->addDay()->format('Y-m-d') }}',
             adults: 1,
             children: 0,
             minors: 0,
             infants: 0,
             has_vehicle: false,
             vehicleRatesList: window.AMIGA_VEHICLE_RATES || [],
             vehicleBrandsList: window.AMIGA_VEHICLE_BRANDS || [],
             vehicle_booking_method: 'category',
             selected_vehicle_rate_id: '',
             selected_brand_id: '',
             selected_model_id: '',
             vehicle_plate_number: '',
             driver_first_name: '',
             driver_middle_name: '',
             driver_last_name: '',
             driver_birthday: '',
             get selectedCargoRate() {
                 if (this.vehicle_booking_method === 'category' && this.selected_vehicle_rate_id) {
                     let r = this.vehicleRatesList.find(x => x.id == this.selected_vehicle_rate_id);
                     return r ? parseFloat(r.price || 0) : 0;
                 }
                 if (this.vehicle_booking_method === 'brand_model' && this.selected_model_id) {
                     let b = this.vehicleBrandsList.find(x => x.id == this.selected_brand_id);
                     if (b && b.models) {
                         let m = b.models.find(x => x.id == this.selected_model_id);
                         return m ? parseFloat(m.price || 0) : 0;
                     }
                 }
                 return 0;
             },
             get availableVehicleModels() {
                 if (!this.selected_brand_id) return [];
                 let b = this.vehicleBrandsList.find(x => x.id == this.selected_brand_id);
                 return b && b.models ? b.models : [];
             },
             showTripTypeDropdown: false,
             showModeDropdown: false,
             showOperatorDropdown: false,
             showPassengerDropdown: false,
             showOriginSuggestions: false,
             showDestinationSuggestions: false,
             showMinorAgeWarning: false,
             hasSeenMinorAgeWarning: false,
             showDataPrivacyModal: false,
             pendingSearchUrl: '',
             errors: {
                 operator: '',
                 origin: '',
                 destination: '',
                 departure_date: '',
                 return_date: '',
                 vehicle_category: '',
                 vehicle_brand: '',
                 vehicle_model: '',
                 vehicle_plate: '',
                 driver_first_name: '',
                 driver_last_name: '',
                 driver_birthday: ''
             },
             init() {
                  this.clampPassengersToMax();
                  this.$watch('trip_type', (val) => {
                      this.clampPassengersToMax();
                      if (val === 'round_trip' && this.origin && this.destination) {
                          if (!this.hasReturnRoute(this.origin, this.destination)) {
                              this.destination = '';
                              this.return_date = '';
                          }
                      }
                  });
                  this.$watch('adults', () => this.clampPassengersToMax());
                  this.$watch('children', () => this.clampPassengersToMax());
                  this.$watch('minors', () => this.clampPassengersToMax());
                  this.$watch('infants', () => this.clampPassengersToMax());
                  this.$watch('mode', () => this.clampPassengersToMax());
             },
             hasReturnRoute(origin, destination) {
                 return this.activeRoutes.some(r => 
                     (!this.mode || r.mode === this.mode) && 
                     (!this.operator || r.operator === this.operator) && 
                     r.origin === destination && 
                     r.destination === origin &&
                     (!r.dates || r.dates.length > 0)
                 );
             },
             activeRoutes: window.AMIGA_ACTIVE_ROUTES || [],
             popularPorts: ['Batangas', 'Calapan', 'Caticlan', 'Odiongan', 'Manila', 'Cebu', 'Puerto Princesa', 'Roxas'],
             operatorsList: window.AMIGA_OPERATORS_LIST || [],
             get filteredOperatorsList() {
                 if (!this.mode) return this.operatorsList;
                 return this.operatorsList.filter(o => o.mode === 'all' || o.mode === this.mode);
             },
             get operatorLabel() {
                 let op = this.operatorsList.find(o => o.value === this.operator);
                 return op ? op.name : 'Select Operator';
             },
             get modeLabel() {
                 if (this.mode === 'airline') return 'Airline';
                 return 'Ferry';
             },
             get availableOrigins() {
                 if (!this.operator) return [];
                 let origins = [];
                 this.activeRoutes.forEach(r => {
                     if ((!this.mode || r.mode === this.mode) && r.operator === this.operator) {
                         if (this.trip_type === 'round_trip' && !this.hasReturnRoute(r.origin, r.destination)) {
                             return;
                         }
                         if (!origins.includes(r.origin)) origins.push(r.origin);
                     }
                 });
                 return origins.sort();
             },
             get availableDestinations() {
                 if (!this.operator || !this.origin) return [];
                 let destinations = [];
                 this.activeRoutes.forEach(r => {
                     if ((!this.mode || r.mode === this.mode) && r.operator === this.operator && r.origin === this.origin) {
                         if (this.trip_type === 'round_trip' && !this.hasReturnRoute(this.origin, r.destination)) {
                             return;
                         }
                         if (!destinations.includes(r.destination)) destinations.push(r.destination);
                     }
                 });
                 return destinations.sort();
             },
              get enabledDepartureDates() {
                  if (!this.origin || !this.destination) return [];
                  let dates = [];
                  this.activeRoutes.forEach(r => {
                      if ((!this.mode || r.mode === this.mode) && 
                          (!this.operator || r.operator === this.operator) && 
                          r.origin === this.origin && 
                          r.destination === this.destination) {
                          if (r.dates && Array.isArray(r.dates)) {
                              r.dates.forEach(d => {
                                  if (!dates.includes(d)) dates.push(d);
                              });
                          }
                      }
                  });
                  return dates.sort();
              },
              get enabledReturnDates() {
                  if (!this.origin || !this.destination) return [];
                  let dates = [];
                  this.activeRoutes.forEach(r => {
                      if ((!this.mode || r.mode === this.mode) && 
                          (!this.operator || r.operator === this.operator) && 
                          r.origin === this.destination && 
                          r.destination === this.origin) {
                          if (r.dates && Array.isArray(r.dates)) {
                              r.dates.forEach(d => {
                                  if (!dates.includes(d)) dates.push(d);
                              });
                          }
                      }
                  });
                  return dates.sort();
              },
              get maxPassengers() {
                  return this.trip_type === 'round_trip' ? 4 : 8;
              },
              setTripType(val) {
                  this.trip_type = val;
                  this.clampPassengersToMax();
                  this.showTripTypeDropdown = false;
                  if (val === 'round_trip' && this.origin && this.destination) {
                      if (!this.hasReturnRoute(this.origin, this.destination)) {
                          this.destination = '';
                          this.return_date = '';
                      }
                  }
              },
              get totalPassengers() {
                  return parseInt(this.adults) + parseInt(this.children) + (this.mode === 'airline' ? parseInt(this.minors) + parseInt(this.infants) : 0);
              },
              clampPassengersToMax() {
                  const limit = this.trip_type === 'round_trip' ? 4 : 8;
                  while (this.totalPassengers > limit) {
                      if (this.infants > 0) {
                          this.infants--;
                      } else if (this.minors > 0) {
                          this.minors--;
                      } else if (this.children > 0) {
                          this.children--;
                      } else if (this.adults > 1) {
                          this.adults--;
                      } else {
                          break;
                      }
                  }
                  if (this.infants > this.adults) {
                      this.infants = this.adults;
                  }
              },
              swapPorts() {
                  let tmp = this.origin;
                  this.origin = this.destination;
                  this.destination = tmp;
              },
             search() {
                 this.clampPassengersToMax();
                 this.errors.operator = '';
                 this.errors.origin = '';
                 this.errors.destination = '';
                 let hasError = false;
                 if (!this.operator || !this.operator.trim()) {
                     this.errors.operator = 'Please select an operator';
                     hasError = true;
                 }
                 if (!this.origin || !this.origin.trim()) {
                     this.errors.origin = 'Departure city is required';
                     hasError = true;
                 }
                  if (!this.destination || !this.destination.trim()) {
                      this.errors.destination = 'Arrival City is required';
                      hasError = true;
                  }
                  if (!this.departure_date) {
                      this.errors.departure_date = 'Please select a departure date';
                      hasError = true;
                  }
                  if (this.trip_type === 'round_trip' && !this.return_date) {
                      this.errors.return_date = 'Please select a return date';
                      hasError = true;
                  }
                  if (this.mode === 'ferry' && this.operator && this.operator.toLowerCase().includes('starlite') && this.has_vehicle) {
                      if (this.vehicle_booking_method === 'category' && !this.selected_vehicle_rate_id) {
                          this.errors.vehicle_category = 'Please select a category';
                          hasError = true;
                      }
                      if (this.vehicle_booking_method === 'brand_model') {
                          if (!this.selected_brand_id) {
                              this.errors.vehicle_brand = 'Please select a brand';
                              hasError = true;
                          }
                          if (!this.selected_model_id && this.availableVehicleModels.length > 0) {
                              this.errors.vehicle_model = 'Please select a model';
                              hasError = true;
                          }
                      }
                      if (!this.vehicle_plate_number || !this.vehicle_plate_number.trim()) {
                          this.errors.vehicle_plate = 'Plate number is required';
                          hasError = true;
                      }
                      if (!this.driver_first_name || !this.driver_first_name.trim()) {
                          this.errors.driver_first_name = 'First name is required';
                          hasError = true;
                      }
                      if (!this.driver_last_name || !this.driver_last_name.trim()) {
                          this.errors.driver_last_name = 'Last name is required';
                          hasError = true;
                      }
                      if (!this.driver_birthday) {
                          this.errors.driver_birthday = 'Birthday is required';
                          hasError = true;
                      }
                  }
                  if (hasError) {
                      setTimeout(() => {
                          this.errors.operator = '';
                          this.errors.origin = '';
                          this.errors.destination = '';
                          this.errors.departure_date = '';
                          this.errors.return_date = '';
                          this.errors.vehicle_category = '';
                          this.errors.vehicle_brand = '';
                          this.errors.vehicle_model = '';
                          this.errors.vehicle_plate = '';
                          this.errors.driver_first_name = '';
                          this.errors.driver_last_name = '';
                          this.errors.driver_birthday = '';
                      }, 4000);
                      return;
                  }

                 let params = new URLSearchParams();
                 params.append('trip_type', this.trip_type);
                 if (this.mode) params.append('mode', this.mode);
                 if (this.operator) params.append('operator', this.operator);
                 if (this.origin) params.append('origin', this.origin);
                 if (this.destination) params.append('destination', this.destination);
                 if (this.departure_date) params.append('departure_date', this.departure_date);
                 if (this.trip_type === 'round_trip' && this.return_date) {
                     params.append('return_date', this.return_date);
                 }
                 params.append('adults', this.adults);
                 params.append('children', this.children);
                 if (this.mode === 'airline') {
                     params.append('minors', this.minors);
                     params.append('infants', this.infants);
                 } else {
                     params.append('infants', 0);
                     params.append('minors', 0);
                 }
                 params.append('step', 2);
                 if (this.mode === 'ferry' && this.operator && this.operator.toLowerCase().includes('starlite') && this.has_vehicle) {
                     params.append('has_vehicle', '1');
                     if (this.vehicle_booking_method) params.append('vehicle_booking_method', this.vehicle_booking_method);
                     if (this.vehicle_booking_method === 'category' && this.selected_vehicle_rate_id) {
                         params.append('selected_vehicle_rate_id', this.selected_vehicle_rate_id);
                     }
                     if (this.vehicle_booking_method === 'brand_model') {
                         if (this.selected_brand_id) params.append('selected_brand_id', this.selected_brand_id);
                         if (this.selected_model_id) params.append('selected_model_id', this.selected_model_id);
                     }
                     if (this.vehicle_plate_number) params.append('vehicle_plate_number', this.vehicle_plate_number);
                     if (this.driver_first_name) params.append('driver_first_name', this.driver_first_name);
                     if (this.driver_middle_name) params.append('driver_middle_name', this.driver_middle_name);
                     if (this.driver_last_name) params.append('driver_last_name', this.driver_last_name);
                     const driverFullName = [this.driver_first_name, this.driver_middle_name, this.driver_last_name].filter(Boolean).join(' ');
                     if (driverFullName) params.append('driver_name', driverFullName);
                     if (this.driver_birthday) params.append('driver_birthday', this.driver_birthday);
                 }
                 
                 this.pendingSearchUrl = '{{ url('/book/new') }}?' + params.toString();
                 this.showDataPrivacyModal = true;
             }
         }"
         @click.away="
             showTripTypeDropdown = false;
             showModeDropdown = false;
             showOperatorDropdown = false;
             showPassengerDropdown = false;
             showOriginSuggestions = false;
             showDestinationSuggestions = false;
         ">
         
        <div class="bg-white rounded-xl shadow-lg border border-gray-100 py-4 px-4 sm:px-6 relative z-10">
            {{-- Top Row: Plain Inline Toolbar Labels (No Boxed Pills, No Divider Line) --}}
            <div class="flex flex-wrap items-center justify-start gap-5 sm:gap-6 mb-3 relative">
                
                <!-- 1. TRIP TYPE Selector -->
                <div class="relative">
                    <button type="button" 
                            @click="showTripTypeDropdown = !showTripTypeDropdown; showModeDropdown = false; showOperatorDropdown = false; showPassengerDropdown = false;"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-[#216417] transition-colors py-1">
                        <svg class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                        <span x-text="trip_type === 'round_trip' ? 'Round Trip' : 'One Way'">One Way</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showTripTypeDropdown" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         class="absolute left-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50"
                         style="display: none;">
                        <button type="button" @click="setTripType('one_way')"
                                class="w-full text-left px-4 py-2.5 text-sm font-semibold flex items-center justify-between hover:bg-slate-50 transition"
                                :class="trip_type === 'one_way' ? 'text-[#216417] bg-emerald-50/50' : 'text-slate-700'">
                            <span>One Way</span>
                            <svg x-show="trip_type === 'one_way'" class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" @click="setTripType('round_trip')"
                                class="w-full text-left px-4 py-2.5 text-sm font-semibold flex items-center justify-between hover:bg-slate-50 transition"
                                :class="trip_type === 'round_trip' ? 'text-[#216417] bg-emerald-50/50' : 'text-slate-700'">
                            <span>Round Trip</span>
                            <svg x-show="trip_type === 'round_trip'" class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- 2. MODE Selector -->
                <div class="relative">
                    <button type="button" 
                            @click="showModeDropdown = !showModeDropdown; showTripTypeDropdown = false; showOperatorDropdown = false; showPassengerDropdown = false;"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-[#216417] transition-colors py-1">
                        <svg class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064" />
                        </svg>
                        <span x-text="modeLabel">Ferry</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showModeDropdown" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         class="absolute left-0 top-full mt-2 w-48 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50"
                         style="display: none;">
                        <button type="button" @click="mode = 'ferry'; if(operator && !filteredOperatorsList.some(o => o.value === operator)) operator = ''; showModeDropdown = false;"
                                class="w-full text-left px-4 py-2.5 text-sm font-semibold flex items-center justify-between hover:bg-slate-50 transition"
                                :class="mode === 'ferry' ? 'text-[#216417] bg-emerald-50/50' : 'text-slate-700'">
                            <span>Ferry</span>
                            <svg x-show="mode === 'ferry'" class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                        <button type="button" @click="mode = 'airline'; if(operator && !filteredOperatorsList.some(o => o.value === operator)) operator = ''; showModeDropdown = false;"
                                class="w-full text-left px-4 py-2.5 text-sm font-semibold flex items-center justify-between hover:bg-slate-50 transition"
                                :class="mode === 'airline' ? 'text-[#216417] bg-emerald-50/50' : 'text-slate-700'">
                            <span>Airline</span>
                            <svg x-show="mode === 'airline'" class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                        </button>
                    </div>
                </div>

                <!-- 3. OPERATOR Selector -->
                <div class="relative">
                    {{-- Validation Tooltip --}}
                    <div x-show="errors.operator" 
                         x-transition
                         x-cloak
                         class="absolute bottom-full left-0 mb-2 bg-[#3f3f46] text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-xl z-50 whitespace-nowrap">
                        <span x-text="errors.operator"></span>
                        <div class="absolute top-full left-6 -mt-1 border-4 border-transparent border-t-[#3f3f46]"></div>
                    </div>

                    <button type="button" 
                            @click="showOperatorDropdown = !showOperatorDropdown; showTripTypeDropdown = false; showModeDropdown = false; showPassengerDropdown = false; errors.operator = '';"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-[#216417] transition-colors py-1">
                        <svg class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                        <span x-text="operatorLabel">Select Operator</span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    <div x-show="showOperatorDropdown" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         class="absolute left-0 top-full mt-2 w-64 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50 max-h-72 overflow-y-auto"
                         style="display: none;">
                        <template x-for="op in filteredOperatorsList" :key="op.value">
                            <button type="button" @click="operator = op.value; showOperatorDropdown = false; errors.operator = ''; if (origin && !availableOrigins.includes(origin)) { origin = ''; destination = ''; } if (destination && !availableDestinations.includes(destination)) { destination = ''; }"
                                    class="w-full text-left px-4 py-2.5 text-sm font-semibold flex items-center justify-between hover:bg-slate-50 transition"
                                    :class="operator === op.value ? 'text-[#216417] bg-emerald-50/50' : 'text-slate-700'">
                                <div class="flex items-center gap-3">
                                    <template x-if="op.logo">
                                        <img :src="op.logo" :alt="op.name" class="w-6 h-6 object-contain">
                                    </template>
                                    <template x-if="!op.logo">
                                        <div class="w-6 h-6 rounded-full bg-slate-100 flex items-center justify-center text-xs font-bold text-slate-500">All</div>
                                    </template>
                                    <span x-text="op.name"></span>
                                </div>
                                <svg x-show="operator === op.value" class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- 4. PASSENGER Selector -->
                <div class="relative">
                    <button type="button" 
                            @click="showPassengerDropdown = !showPassengerDropdown; showTripTypeDropdown = false; showModeDropdown = false; showOperatorDropdown = false;"
                            class="inline-flex items-center gap-1.5 text-sm font-semibold text-gray-700 hover:text-[#216417] transition-colors py-1">
                        <svg class="w-4 h-4 text-[#216417]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        <span><span x-text="totalPassengers"></span> <span x-text="totalPassengers === 1 ? 'Passenger' : 'Passengers'"></span></span>
                        <svg class="w-3.5 h-3.5 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                    </button>
                    
                    {{-- Passenger Counter Modal/Dropdown --}}
                    <div x-show="showPassengerDropdown" 
                         x-transition:enter="transition ease-out duration-100"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-75"
                         class="absolute left-0 top-full mt-2 w-72 bg-white rounded-2xl shadow-xl border border-slate-100 p-5 z-50"
                         style="display: none;">
                        
                        {{-- Airline Layout --}}
                        <template x-if="mode === 'airline'">
                            <div>
                                {{-- Adult Row --}}
                                <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Adults</p>
                                        <p class="text-xs text-slate-500">Age 12 and above</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(adults > 1) adults--" 
                                                :disabled="adults <= 1"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="adults"></span>
                                        <button type="button" 
                                                @click="if(totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) adults++" 
                                                :disabled="totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Minor Row --}}
                                <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Minor</p>
                                        <p class="text-xs text-slate-500">Age 7 to 11</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(minors > 0) minors--" 
                                                :disabled="minors <= 0"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="minors"></span>
                                        <button type="button" 
                                                @click="if(totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) minors++" 
                                                :disabled="totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Child Row --}}
                                <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Child</p>
                                        <p class="text-xs text-slate-500">Age 2 to 6</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(children > 0) children--" 
                                                :disabled="children <= 0"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="children"></span>
                                        <button type="button" 
                                                @click="if(totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) children++" 
                                                :disabled="totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Infant Row --}}
                                <div class="flex items-center justify-between py-2.5">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Infants</p>
                                        <p class="text-xs text-slate-500">0 to 23 months</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(infants > 0) infants--" 
                                                :disabled="infants <= 0"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="infants"></span>
                                        <button type="button" 
                                                @click="if(infants < adults && totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) infants++" 
                                                :disabled="infants >= adults || totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Ferry Layout --}}
                        <template x-if="mode !== 'airline'">
                            <div>
                                {{-- Adult Row --}}
                                <div class="flex items-center justify-between py-2.5 border-b border-slate-100">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Adults</p>
                                        <p class="text-xs text-slate-500">Age 12 and above</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(adults > 1) adults--" 
                                                :disabled="adults <= 1"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="adults"></span>
                                        <button type="button" 
                                                @click="if(totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) adults++" 
                                                :disabled="totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>

                                {{-- Child Row --}}
                                <div class="flex items-center justify-between py-2.5">
                                    <div>
                                        <p class="text-sm font-bold text-slate-900">Child</p>
                                        <p class="text-xs text-slate-500">Age 2 to 11</p>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button type="button" 
                                                @click="if(children > 0) children--" 
                                                :disabled="children <= 0"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            -
                                        </button>
                                        <span class="w-5 text-center font-bold text-slate-900" x-text="children"></span>
                                        <button type="button" 
                                                @click="if(totalPassengers < (trip_type === 'round_trip' ? 4 : 8)) { children++; if(!hasSeenMinorAgeWarning) { showMinorAgeWarning = true; hasSeenMinorAgeWarning = true; } }" 
                                                :disabled="totalPassengers >= (trip_type === 'round_trip' ? 4 : 8)"
                                                class="w-8 h-8 rounded-full border border-slate-200 flex items-center justify-center text-slate-700 hover:border-[#216417] hover:text-[#216417] disabled:opacity-40 disabled:cursor-not-allowed transition">
                                            +
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </template>

                        {{-- Footer with Done button --}}
                        <div class="mt-4 pt-3 border-t border-slate-100 flex items-center justify-between">
                            <span class="text-xs text-slate-400 font-medium" x-text="'Limit ' + (trip_type === 'round_trip' ? 4 : 8) + ' passengers'">Limit 8 passengers</span>
                            <button type="button" 
                                    @click="showPassengerDropdown = false"
                                    class="bg-[#008000] hover:bg-[#006600] text-white font-bold px-6 py-2 rounded-xl text-sm shadow transition">
                                Done
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- Bottom Row: Bordered Box Inputs in a Single Compact Horizontal Bar --}}
            <div class="flex flex-col md:flex-row gap-2.5 md:items-stretch w-full min-w-0">
                
                <!-- From (Origin) -->
                <div @click.outside="showOriginSuggestions = false" class="w-full md:flex-1 min-w-0 relative border border-gray-200 rounded-xl px-4 py-2.5 bg-white hover:border-[#216417] focus-within:border-[#216417] focus-within:ring-1 focus-within:ring-[#216417] transition flex flex-col justify-center">
                    {{-- Validation Tooltip --}}
                    <div x-show="errors.origin" 
                         x-transition
                         x-cloak
                         class="absolute bottom-full left-4 mb-2 bg-[#3f3f46] text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-xl z-50 whitespace-nowrap">
                        <span x-text="errors.origin"></span>
                        <div class="absolute top-full left-6 -mt-1 border-4 border-transparent border-t-[#3f3f46]"></div>
                    </div>

                    <label class="text-xs text-gray-400 font-medium block mb-0.5 truncate">From</label>
                    <div class="flex items-center gap-2 w-full min-w-0">
                        <input type="text" 
                               x-model="origin" 
                               @input="errors.origin = ''"
                               @focus="if (!operator) { errors.operator = 'Please select an operator first'; showOperatorDropdown = true; return; } showOriginSuggestions = true; errors.origin = ''" 
                               @click="if (!operator) { errors.operator = 'Please select an operator first'; showOperatorDropdown = true; return; } showOriginSuggestions = true; errors.origin = ''" 
                               placeholder="Origin" 
                               class="w-full min-w-0 bg-transparent text-sm md:text-base font-semibold text-gray-800 placeholder:text-gray-400 focus:outline-none border-0 p-0 truncate">
                    </div>
                    {{-- Origin Suggestions --}}
                    <div x-show="showOriginSuggestions" 
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 max-h-56 overflow-y-auto"
                         style="display: none;">
                        <div class="px-4 py-1.5 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Available Origins</div>
                        <template x-if="availableOrigins.length === 0">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500">No schedules found for this operator</div>
                        </template>
                        <template x-for="port in availableOrigins" :key="port">
                            <button type="button" @click="origin = port; showOriginSuggestions = false; errors.origin = '';"
                                    class="w-full text-left px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50 hover:text-[#216417] transition flex items-center justify-between">
                                <span x-text="port"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Swap Button Circle between Origin & Destination -->
                <div class="flex items-center justify-center shrink-0 -my-2 md:my-auto md:-mx-4 z-20">
                    <button type="button" 
                            @click="swapPorts()" 
                            title="Swap Origin & Destination"
                            class="w-8 h-8 rounded-full bg-[#008000] hover:bg-[#006600] text-white flex items-center justify-center transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4" />
                        </svg>
                    </button>
                </div>

                <!-- To (Destination) -->
                <div @click.outside="showDestinationSuggestions = false" class="w-full md:flex-1 min-w-0 relative border border-gray-200 rounded-xl px-4 py-2.5 bg-white hover:border-[#216417] focus-within:border-[#216417] focus-within:ring-1 focus-within:ring-[#216417] transition flex flex-col justify-center">
                    {{-- Validation Tooltip --}}
                    <div x-show="errors.destination" 
                         x-transition
                         x-cloak
                         class="absolute bottom-full left-4 mb-2 bg-[#3f3f46] text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-xl z-50 whitespace-nowrap">
                        <span x-text="errors.destination"></span>
                        <div class="absolute top-full left-6 -mt-1 border-4 border-transparent border-t-[#3f3f46]"></div>
                    </div>

                    <label class="text-xs text-gray-400 font-medium block mb-0.5 truncate">To</label>
                    <div class="flex items-center gap-2 w-full min-w-0">
                        <input type="text" 
                               x-model="destination" 
                               @input="errors.destination = ''"
                               @focus="if (!operator) { errors.operator = 'Please select an operator first'; showOperatorDropdown = true; return; } showDestinationSuggestions = true; errors.destination = ''" 
                               @click="if (!operator) { errors.operator = 'Please select an operator first'; showOperatorDropdown = true; return; } showDestinationSuggestions = true; errors.destination = ''" 
                               placeholder="Destination" 
                               class="w-full min-w-0 bg-transparent text-sm md:text-base font-semibold text-gray-800 placeholder:text-gray-400 focus:outline-none border-0 p-0 truncate">
                    </div>
                    {{-- Destination Suggestions --}}
                    <div x-show="showDestinationSuggestions" 
                         class="absolute left-0 right-0 top-full mt-2 bg-white rounded-xl shadow-xl border border-gray-100 py-2 z-50 max-h-56 overflow-y-auto"
                         style="display: none;">
                        <div class="px-4 py-1.5 text-[11px] font-extrabold text-gray-400 uppercase tracking-wider">Available Destinations</div>
                        <template x-if="!origin">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500">Please select From (Origin) first</div>
                        </template>
                        <template x-if="origin && availableDestinations.length === 0">
                            <div class="px-4 py-2 text-xs font-semibold text-gray-500">No destinations available from <span x-text="origin"></span></div>
                        </template>
                        <template x-for="port in availableDestinations" :key="port">
                            <button type="button" @click="destination = port; showDestinationSuggestions = false; errors.destination = '';"
                                    class="w-full text-left px-4 py-2 text-sm font-semibold text-gray-700 hover:bg-emerald-50 hover:text-[#216417] transition flex items-center justify-between">
                                <span x-text="port"></span>
                            </button>
                        </template>
                    </div>
                </div>

                <!-- Departure Date -->
                <div x-data="amigaDatePicker('departure')" 
                     @click.outside="isOpen = false"
                     class="w-full md:flex-1 min-w-0 relative border border-gray-200 rounded-xl px-4 py-2.5 bg-white hover:border-[#216417] transition flex flex-col justify-center"
                     :class="{ 'opacity-60 cursor-not-allowed bg-slate-50': isDisabled, 'cursor-pointer': !isDisabled }">
                    {{-- Validation Tooltip --}}
                    <div x-show="errors.departure_date" 
                         x-transition
                         x-cloak
                         class="absolute bottom-full left-4 mb-2 bg-[#3f3f46] text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-xl z-50 whitespace-nowrap">
                        <span x-text="errors.departure_date"></span>
                        <div class="absolute top-full left-6 -mt-1 border-4 border-transparent border-t-[#3f3f46]"></div>
                    </div>

                    <label class="text-xs text-gray-400 font-medium block mb-0.5 truncate pointer-events-none">Departure</label>
                    
                    <div @click="if (!isDisabled) isOpen = !isOpen" class="flex items-center justify-between w-full min-w-0 select-none">
                        <span class="truncate text-sm md:text-base font-semibold" 
                              :class="{ 'text-gray-400 font-normal': !departure_date || isDisabled, 'text-gray-800': departure_date && !isDisabled }"
                              x-text="departure_date && !isDisabled ? formattedDate : placeholderText"></span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    {{-- Custom Calendar Popup --}}
                    <div x-show="isOpen" 
                         x-cloak 
                         x-transition
                         class="absolute left-0 top-full mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-xl z-50 min-w-[280px]"
                         style="display: none;">
                        <div class="flex items-center justify-between text-slate-900 font-bold mb-3">
                            <button type="button" @click.prevent="prevMonth" class="rounded-full p-2 hover:bg-slate-100 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></button>
                            <div x-text="monthLabel + ' ' + viewYear"></div>
                            <button type="button" @click.prevent="nextMonth" class="rounded-full p-2 hover:bg-slate-100 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg></button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500 mb-2">
                            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-sm">
                            <template x-for="(day, index) in calendarDays" :key="index">
                                <div>
                                    <template x-if="day === null">
                                        <div class="h-10 rounded-lg"></div>
                                    </template>
                                    <template x-if="day !== null">
                                        <button
                                            type="button"
                                            @click.prevent="selectDate(day)"
                                            :disabled="day.disabled"
                                            :class="{
                                                'h-10 rounded-lg transition-colors font-medium flex items-center justify-center w-full': true,
                                                'bg-[#216417] text-white shadow-md': day.dateStr === departure_date,
                                                'bg-slate-50 text-slate-300 cursor-not-allowed line-through': day.disabled && day.dateStr !== departure_date,
                                                'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900': !day.disabled && day.dateStr !== departure_date
                                            }"
                                            x-text="day.day"
                                        ></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Return Date (Only for Round Trip) -->
                <div x-show="trip_type === 'round_trip'" 
                     x-cloak
                     x-data="amigaDatePicker('return')" 
                     @click.outside="isOpen = false"
                     class="w-full md:flex-1 min-w-0 relative border border-gray-200 rounded-xl px-4 py-2.5 bg-white hover:border-[#216417] transition flex flex-col justify-center"
                     :class="{ 'opacity-60 cursor-not-allowed bg-slate-50': isDisabled, 'cursor-pointer': !isDisabled }">
                    {{-- Validation Tooltip --}}
                    <div x-show="errors.return_date" 
                         x-transition
                         x-cloak
                         class="absolute bottom-full left-4 mb-2 bg-[#3f3f46] text-white text-xs font-semibold px-3.5 py-2 rounded-lg shadow-xl z-50 whitespace-nowrap">
                        <span x-text="errors.return_date"></span>
                        <div class="absolute top-full left-6 -mt-1 border-4 border-transparent border-t-[#3f3f46]"></div>
                    </div>

                    <label class="text-xs text-gray-400 font-medium block mb-0.5 truncate pointer-events-none">Return</label>
                    
                    <div @click="if (!isDisabled) isOpen = !isOpen" class="flex items-center justify-between w-full min-w-0 select-none">
                        <span class="truncate text-sm md:text-base font-semibold" 
                              :class="{ 'text-gray-400 font-normal': !return_date || isDisabled, 'text-gray-800': return_date && !isDisabled }"
                              x-text="return_date && !isDisabled ? formattedDate : placeholderText"></span>
                        <svg class="w-4 h-4 text-gray-400 shrink-0 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>

                    {{-- Custom Calendar Popup --}}
                    <div x-show="isOpen" 
                         x-cloak 
                         x-transition
                         class="absolute right-0 md:left-0 top-full mt-2 rounded-xl border border-slate-200 bg-white p-4 shadow-xl z-50 min-w-[280px]"
                         style="display: none;">
                        <div class="flex items-center justify-between text-slate-900 font-bold mb-3">
                            <button type="button" @click.prevent="prevMonth" class="rounded-full p-2 hover:bg-slate-100 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg></button>
                            <div x-text="monthLabel + ' ' + viewYear"></div>
                            <button type="button" @click.prevent="nextMonth" class="rounded-full p-2 hover:bg-slate-100 transition"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg></button>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-center text-xs font-semibold text-slate-500 mb-2">
                            <div>Su</div><div>Mo</div><div>Tu</div><div>We</div><div>Th</div><div>Fr</div><div>Sa</div>
                        </div>
                        <div class="grid grid-cols-7 gap-1 text-sm">
                            <template x-for="(day, index) in calendarDays" :key="index">
                                <div>
                                    <template x-if="day === null">
                                        <div class="h-10 rounded-lg"></div>
                                    </template>
                                    <template x-if="day !== null">
                                        <button
                                            type="button"
                                            @click.prevent="selectDate(day)"
                                            :disabled="day.disabled"
                                            :class="{
                                                'h-10 rounded-lg transition-colors font-medium flex items-center justify-center w-full': true,
                                                'bg-[#216417] text-white shadow-md': day.dateStr === return_date,
                                                'bg-slate-50 text-slate-300 cursor-not-allowed line-through': day.disabled && day.dateStr !== return_date,
                                                'bg-white text-slate-700 hover:bg-slate-100 hover:text-slate-900': !day.disabled && day.dateStr !== return_date
                                            }"
                                            x-text="day.day"
                                        ></button>
                                    </template>
                                </div>
                            </template>
                        </div>
                    </div>
                </div>

                <!-- Green Search Button -->
                <div class="w-full md:w-auto shrink-0 flex items-stretch">
                    <button type="button" 
                            @click="search()"
                            class="w-full md:w-auto px-7 py-3 md:py-0 bg-[#008000] hover:bg-[#006600] text-white font-bold rounded-xl shadow-md hover:shadow-lg transition-all duration-200 flex items-center justify-center gap-2 text-base">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                        <span>Search</span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Starlite Vehicle Booking Extension Underneath (White Card & Slim Form) -->
        <div x-show="mode === 'ferry' && operator && operator.toLowerCase().includes('starlite')"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
             x-transition:enter-end="opacity-100 translate-y-0 max-h-[700px]"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 max-h-[700px]"
             x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
             x-cloak
             class="mt-3 overflow-hidden"
             style="display: none;">
            <div class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 shadow-lg text-slate-900">
                <!-- Header / Toggle Row (Screenshot 3 Style) -->
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <p class="text-slate-900 font-semibold text-base">Vehicle booking</p>
                        <p class="mt-0.5 text-sm text-slate-600">Add a vehicle to your ferry trip (optional).</p>
                    </div>
                    <label class="relative inline-flex cursor-pointer items-center gap-3">
                        <input type="checkbox" x-model="has_vehicle" class="peer sr-only" />
                        <span class="relative h-7 w-12 shrink-0 rounded-full bg-slate-200 transition peer-checked:bg-[#db2777] peer-focus:outline-none after:absolute after:left-0.5 after:top-0.5 after:h-6 after:w-6 after:rounded-full after:bg-white after:shadow after:transition-transform peer-checked:after:translate-x-5"></span>
                        <span class="text-sm font-semibold text-slate-700" x-text="has_vehicle ? 'Yes' : 'No'">No</span>
                    </label>
                </div>

                <!-- Slim Vehicle Booking Form (Screenshot 2 Style made Slim) -->
                <div x-show="has_vehicle"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 -translate-y-2 max-h-0"
                     x-transition:enter-end="opacity-100 translate-y-0 max-h-[500px]"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 max-h-[500px]"
                     x-transition:leave-end="opacity-0 -translate-y-2 max-h-0"
                     class="mt-4 pt-4 border-t border-slate-200">
                    <!-- Row 1: 4 slim columns -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
                        <!-- Col 1: Classify Cargo by -->
                        <div>
                            <span class="text-xs font-semibold text-slate-700 block mb-1.5">Classify Cargo by:</span>
                            <div class="flex items-center gap-1.5">
                                <button type="button" 
                                        @click="vehicle_booking_method = 'category'"
                                        :class="vehicle_booking_method === 'category' ? 'border-[#db2777] bg-[#db2777]/5 text-[#db2777] font-bold' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                        class="flex-1 h-9 px-3 rounded-lg border text-xs sm:text-sm transition flex items-center justify-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full border flex items-center justify-center" :class="vehicle_booking_method === 'category' ? 'border-[#db2777]' : 'border-slate-300'">
                                        <span x-show="vehicle_booking_method === 'category'" class="w-1.5 h-1.5 rounded-full bg-[#db2777]"></span>
                                    </span>
                                    <span>Category</span>
                                </button>
                                <button type="button" 
                                        @click="vehicle_booking_method = 'brand_model'"
                                        :class="vehicle_booking_method === 'brand_model' ? 'border-[#db2777] bg-[#db2777]/5 text-[#db2777] font-bold' : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300'"
                                        class="flex-1 h-9 px-3 rounded-lg border text-xs sm:text-sm transition flex items-center justify-center gap-1.5">
                                    <span class="w-3 h-3 rounded-full border flex items-center justify-center" :class="vehicle_booking_method === 'brand_model' ? 'border-[#db2777]' : 'border-slate-300'">
                                        <span x-show="vehicle_booking_method === 'brand_model'" class="w-1.5 h-1.5 rounded-full bg-[#db2777]"></span>
                                    </span>
                                    <span>Brand</span>
                                </button>
                            </div>
                        </div>

                        <!-- Col 2: Category or Brand/Model Dropdown -->
                        <div>
                            <template x-if="vehicle_booking_method === 'category'">
                                <div>
                                    <span class="text-xs font-semibold text-slate-700 block mb-1.5">Category *</span>
                                    <select x-model="selected_vehicle_rate_id" 
                                            @change="errors.vehicle_category = ''"
                                            :class="errors.vehicle_category ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                            class="w-full h-9 px-3 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1">
                                        <option value="">Select category</option>
                                        <template x-for="rate in vehicleRatesList" :key="rate.id">
                                            <option :value="rate.id" x-text="rate.name"></option>
                                        </template>
                                    </select>
                                    <div x-show="errors.vehicle_category" x-transition class="mt-1">
                                        <span class="text-[10px] text-rose-500" x-text="errors.vehicle_category"></span>
                                    </div>
                                </div>
                            </template>
                            <template x-if="vehicle_booking_method === 'brand_model'">
                                <div class="grid grid-cols-2 gap-1.5">
                                    <div>
                                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">Brand *</span>
                                        <select x-model="selected_brand_id" 
                                                @change="selected_model_id = ''; errors.vehicle_brand = '';"
                                                :class="errors.vehicle_brand ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                                class="w-full h-9 px-2 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1">
                                            <option value="">Brand</option>
                                            <template x-for="brand in vehicleBrandsList" :key="brand.id">
                                                <option :value="brand.id" x-text="brand.name"></option>
                                            </template>
                                        </select>
                                        <div x-show="errors.vehicle_brand" x-transition class="mt-1">
                                            <span class="text-[10px] text-rose-500" x-text="errors.vehicle_brand"></span>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">Model *</span>
                                        <select x-model="selected_model_id" 
                                                @change="errors.vehicle_model = ''"
                                                :disabled="!selected_brand_id"
                                                :class="errors.vehicle_model ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                                class="w-full h-9 px-2 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1 disabled:opacity-50">
                                            <option value="">Model</option>
                                            <template x-for="model in availableVehicleModels" :key="model.id">
                                                <option :value="model.id" x-text="model.name"></option>
                                            </template>
                                        </select>
                                        <div x-show="errors.vehicle_model" x-transition class="mt-1">
                                            <span class="text-[10px] text-rose-500" x-text="errors.vehicle_model"></span>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <!-- Col 3: Plate Number -->
                        <div>
                            <span class="text-xs font-semibold text-slate-700 block mb-1.5">Plate Number *</span>
                            <input type="text" 
                                   x-model="vehicle_plate_number" 
                                   @input="errors.vehicle_plate = ''"
                                   placeholder="e.g., ABC 1234" 
                                   :class="errors.vehicle_plate ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                   class="w-full h-9 px-3 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1" />
                            <div x-show="errors.vehicle_plate" x-transition class="mt-1">
                                <span class="text-[10px] text-rose-500" x-text="errors.vehicle_plate"></span>
                            </div>
                        </div>

                        <!-- Col 4: Cargo Rate -->
                        <div>
                            <span class="text-xs font-semibold text-slate-700 block mb-1.5">Cargo Rate</span>
                            <div class="h-9 rounded-lg border border-slate-200 bg-slate-100/80 px-3 flex items-center justify-center text-sm font-extrabold text-slate-900">
                                <span x-text="'₱' + selectedCargoRate.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2})">₱0.00</span>
                            </div>
                        </div>
                    </div>

                    <!-- Row 2: Driver Details -->
                    <div class="mt-3">
                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">Driver name</span>
                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-2">
                            <div>
                                <span class="text-[10px] font-medium text-slate-500 block mb-1">First Name <span class="text-rose-500">*</span></span>
                                <input type="text"
                                       x-model="driver_first_name"
                                       @input="errors.driver_first_name = ''"
                                       placeholder="e.g., Juan"
                                       :class="errors.driver_first_name ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                       class="w-full h-9 px-3 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1" />
                                <div x-show="errors.driver_first_name" x-transition class="mt-1">
                                    <span class="text-[10px] text-rose-500" x-text="errors.driver_first_name"></span>
                                </div>
                            </div>
                            <div>
                                <span class="text-[10px] font-medium text-slate-500 block mb-1">Middle Name <span class="text-slate-400">(optional)</span></span>
                                <input type="text"
                                       x-model="driver_middle_name"
                                       placeholder="e.g., Dela"
                                       class="w-full h-9 px-3 rounded-lg border border-slate-300 bg-slate-50 text-xs sm:text-sm text-slate-900 focus:border-[#db2777] focus:outline-none focus:ring-1 focus:ring-[#db2777]/20" />
                            </div>
                            <div>
                                <span class="text-[10px] font-medium text-slate-500 block mb-1">Last Name <span class="text-rose-500">*</span></span>
                                <input type="text"
                                       x-model="driver_last_name"
                                       @input="errors.driver_last_name = ''"
                                       placeholder="e.g., Cruz"
                                       :class="errors.driver_last_name ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                                       class="w-full h-9 px-3 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1" />
                                <div x-show="errors.driver_last_name" x-transition class="mt-1">
                                    <span class="text-[10px] text-rose-500" x-text="errors.driver_last_name"></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="mt-3">
                        <span class="text-xs font-semibold text-slate-700 block mb-1.5">Driver birthday</span>
                        <input type="date"
                               x-model="driver_birthday"
                               @change="errors.driver_birthday = ''"
                               :class="errors.driver_birthday ? 'border-rose-500 ring-1 ring-rose-500' : 'border-slate-300 focus:border-[#db2777] focus:ring-[#db2777]/20'"
                               class="w-full h-9 px-3 rounded-lg border bg-slate-50 text-xs sm:text-sm text-slate-900 focus:outline-none focus:ring-1" />
                        <div x-show="errors.driver_birthday" x-transition class="mt-1">
                            <span class="text-[10px] text-rose-500" x-text="errors.driver_birthday"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Minor Age Reminder Modal -->
        <div x-show="showMinorAgeWarning && mode !== 'airline'"
             x-cloak
             x-transition
             @click.self="showMinorAgeWarning = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
             style="display: none;">
            <div class="relative w-full max-w-md overflow-hidden rounded-2xl bg-white p-6 shadow-2xl text-left">
                <button type="button" @click="showMinorAgeWarning = false" class="absolute right-4 top-4 inline-flex h-10 w-10 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-600 transition hover:bg-slate-100">
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="sr-only">Close</span>
                </button>

                <h2 class="text-xl font-bold text-slate-900">Minor age reminder</h2>
                <p class="mt-3 text-slate-600">23 months and under will be issued upon arrival at the port/airport.</p>
                <div class="mt-6 flex justify-end">
                    <button type="button" @click="showMinorAgeWarning = false" class="inline-flex rounded-full bg-[#db2777] px-4 py-2 text-sm font-semibold text-white transition hover:bg-[#be185d]">Close</button>
                </div>
            </div>
        </div>

        <!-- Data Privacy Consent Modal (Republic Act No. 10173 - Data Privacy Act of 2012) -->
        <div x-show="showDataPrivacyModal"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 scale-100"
             x-transition:leave-end="opacity-0 scale-95"
             @keydown.escape.window="showDataPrivacyModal = false"
             class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 p-4 backdrop-blur-sm"
             style="display: none;">
            <div @click.away="showDataPrivacyModal = false" class="relative w-full max-w-lg overflow-hidden rounded-2xl bg-white p-6 sm:p-8 shadow-2xl text-left border border-slate-100">
                <!-- Close X Button -->
                <button type="button" @click="showDataPrivacyModal = false" class="absolute right-4 top-4 inline-flex h-9 w-9 items-center justify-center rounded-full border border-slate-200 bg-slate-50 text-slate-500 transition hover:bg-slate-100 hover:text-slate-700">
                    <svg aria-hidden="true" class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                    <span class="sr-only">Close</span>
                </button>

                <!-- Header Badge & Title -->
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 border border-emerald-200/80 flex items-center justify-center text-[#216417] shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <div>
                        <span class="text-[11px] font-bold uppercase tracking-wider text-[#216417]">Republic Act No. 10173</span>
                        <h2 class="text-xl sm:text-2xl font-black text-slate-900 tracking-tight">Data Privacy Consent</h2>
                    </div>
                </div>

                <!-- Modal Content Box -->
                <div class="bg-slate-50 border border-slate-200/80 rounded-xl p-4 sm:p-5 text-xs sm:text-sm text-slate-600 leading-relaxed space-y-3 max-h-[50vh] overflow-y-auto">
                    <p class="font-semibold text-slate-800">
                        In compliance with the Data Privacy Act of 2012 (R.A. 10173), we value your privacy and are committed to safeguarding your personal information.
                    </p>
                    <p>
                        By proceeding with your travel search and reservation, you acknowledge and consent that <strong>Amiga Gracia Travel Services</strong> will collect, process, and securely share your travel booking details (such as passenger names, contact numbers, and itinerary preferences) with our accredited sea transit and airline operators (including <strong>2GO Travel, Starlite Ferries, Cebu Pacific, and PAL</strong>).
                    </p>
                    <p>
                        Your personal data is used strictly for ticketing, manifest issuance, and statutory compliance with maritime and aviation authorities.
                    </p>
                </div>

                <!-- Footer / Action Buttons -->
                <div class="mt-6 flex flex-col-reverse sm:flex-row items-center justify-end gap-3">
                    <button type="button"
                            @click="showDataPrivacyModal = false"
                            class="w-full sm:w-auto px-5 py-2.5 rounded-xl border border-slate-300 bg-white text-slate-700 font-semibold text-sm hover:bg-slate-50 transition">
                        Cancel
                    </button>
                    <button type="button"
                            @click="if (pendingSearchUrl) { window.location.href = pendingSearchUrl; }"
                            class="w-full sm:w-auto px-6 py-2.5 rounded-xl bg-[#008000] hover:bg-[#006600] text-white font-bold text-sm shadow-md hover:shadow-lg transition flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>I Agree & Proceed</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- Spacer and Scroll Indicator to push promotions below the fold --}}
    <div class="w-full flex flex-col items-center justify-start pt-8 pb-12 sm:pt-10 sm:pb-24"
         x-data="{ scrolled: false }"
         @scroll.window="scrolled = (window.pageYOffset > 20)">
        <div class="hidden sm:flex flex-col items-center justify-center cursor-default animate-pulse drop-shadow-[0_0_12px_rgba(255,255,255,1)]"
             :class="scrolled ? 'opacity-0 scale-95 translate-y-4 pointer-events-none' : 'opacity-100 scale-100 translate-y-0'"
             style="transition: all 0.5s ease-out;">
            <span class="text-sm font-black text-black mb-3 uppercase tracking-[0.2em]">Scroll to Explore</span>
            <div class="w-7 h-12 border-2 border-black rounded-full flex justify-center p-1.5 shadow-sm bg-white/30 backdrop-blur-md">
                <div class="w-1.5 h-3 bg-black rounded-full animate-[bounce_1.5s_infinite]"></div>
            </div>
        </div>
    </div>

    @php
        // Load promo slides: primary source = storage/app/public/prmotion_images (Railway persistent volume)
        // Fallback = public/images/prmotion_images (legacy local folder)
        if (!isset($__promo_slides)) {
            $__promo_files = [];
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'jfif'];

            // Primary: Storage disk (persistent on Railway)
            if (\Illuminate\Support\Facades\Storage::disk('public')->exists('prmotion_images')) {
                $storageFiles = \Illuminate\Support\Facades\Storage::disk('public')->files('prmotion_images');
                foreach ($storageFiles as $__f) {
                    $__basename = basename($__f);
                    if (in_array(strtolower(pathinfo($__basename, PATHINFO_EXTENSION)), $extensions, true)) {
                        $__promo_files[] = [
                            'file'     => $__basename,
                            'title'    => ucwords(str_replace(['-', '_'], ' ', pathinfo($__basename, PATHINFO_FILENAME))),
                            'subtitle' => '',
                            'image'    => storage_asset_path('prmotion_images/' . $__basename),
                        ];
                    }
                }
            }

            // Fallback: legacy public/images/prmotion_images folder
            if (empty($__promo_files)) {
                $__promo_dir = public_path('images/prmotion_images');
                if (!is_dir($__promo_dir)) {
                    $__promo_dir = public_path('images/promotion_images');
                }
                if (is_dir($__promo_dir)) {
                    $__dir_name = basename($__promo_dir);
                    foreach (scandir($__promo_dir) as $__f) {
                        if (in_array(strtolower(pathinfo($__f, PATHINFO_EXTENSION)), $extensions, true)) {
                            $__promo_files[] = [
                                'file'     => $__f,
                                'title'    => ucwords(str_replace(['-', '_'], ' ', pathinfo($__f, PATHINFO_FILENAME))),
                                'subtitle' => '',
                                'image'    => asset('images/' . $__dir_name . '/' . $__f),
                            ];
                        }
                    }
                }
            }

            // Sort alphabetically by filename
            usort($__promo_files, fn($a, $b) => strcmp($a['file'], $b['file']));
            $__promo_slides = $__promo_files;
        }
    @endphp
    <div class="max-w-7xl mx-auto px-4 mt-10 amiga-animate-on-scroll amiga-transition" x-data='{ 
        currentSlide: 0, slides: @json($__promo_slides), currentVideo: 0, 
        videos: ["{{ asset('video/Concept_A_smooth_motion_graph.mp4') }}", "https://interactive-examples.mdn.mozilla.net/media/cc0-videos/flower.mp4"], 
        modalOpen: false, modalImage: null, zoomLevel: 1,
        showControls: true, controlTimeout: null,
        changeVideo(index, container) {
            container.querySelectorAll("video").forEach(v => v.pause());
            this.currentVideo = index;
            setTimeout(() => {
                let activeVid = container.querySelectorAll("video")[index];
                if (activeVid) { activeVid.currentTime = 0; activeVid.play(); }
            }, 50);
        },
        resetControls() {
            this.showControls = true;
            clearTimeout(this.controlTimeout);
            this.controlTimeout = setTimeout(() => { this.showControls = false; }, 3000);
        }
    }' x-init="console.log('promotions slides', slides); resetControls(); if (slides && slides.length) { setInterval(() => { if (!modalOpen) { currentSlide = (currentSlide + 1) % slides.length } }, 5000); }">
        <div class="mb-6 text-center relative ws-sbtn-container">
            @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'promo_gallery' })" class="ws-sbtn absolute top-0 right-2 z-20"></button> @endif
            <h2 class="text-3xl sm:text-4xl font-black text-[#216417] tracking-tight">{{ data_get($pageContent, 'promo_gallery_title', 'Featured Promotions') }}</h2>
            <p class="text-base sm:text-lg text-black font-semibold mt-2">{{ data_get($pageContent, 'promo_gallery_subtitle', 'Browse three highlighted offers from our latest deals.') }}</p>
        </div>
        <div class="grid gap-6 lg:grid-cols-[2fr_1fr] items-stretch">
            <div class="rounded-[1.5rem] border border-slate-200 bg-white/95 shadow-lg overflow-hidden p-3 sm:p-6 h-full flex flex-col relative ws-sbtn-container">
                @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'promo_video' })" class="ws-sbtn absolute top-2 right-2 z-20"></button> @endif
                <div class="rounded-[1rem] sm:rounded-[1.5rem] overflow-hidden bg-black relative flex-1 min-h-0 w-full aspect-video group"
                     @mouseenter="resetControls()" 
                     @mousemove="resetControls()"
                     @mouseleave="clearTimeout(controlTimeout); controlTimeout = setTimeout(() => { showControls = false; }, 3000)">
                    <template x-for="(video, index) in videos" :key="index">
                        <video x-show="currentVideo === index"
                               x-transition:enter="transition ease-out duration-500"
                               x-transition:enter-start="opacity-0"
                               x-transition:enter-end="opacity-100"
                               class="absolute inset-0 w-full h-full object-cover"
                               autoplay muted loop playsinline controls
                               :src="video">
                        </video>
                    </template>
                    
                    <!-- Previous/Next Arrows -->
                    <div class="absolute inset-y-0 left-0 flex items-center px-2 sm:px-4 z-20" x-show="videos.length > 1 && showControls" x-transition.opacity>
                        <button @click="changeVideo(currentVideo === 0 ? videos.length - 1 : currentVideo - 1, $el.closest('.group'))" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-sm border border-white/20">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                        </button>
                    </div>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 sm:px-4 z-20" x-show="videos.length > 1 && showControls" x-transition.opacity>
                        <button @click="changeVideo(currentVideo === videos.length - 1 ? 0 : currentVideo + 1, $el.closest('.group'))" class="w-8 h-8 sm:w-10 sm:h-10 rounded-full bg-black/40 hover:bg-black/60 text-white flex items-center justify-center transition backdrop-blur-sm border border-white/20">
                            <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                        </button>
                    </div>

                    <!-- Navigation Dots -->
                    <div class="absolute top-4 left-0 right-0 flex justify-center gap-2 z-20" x-show="videos.length > 1 && showControls" x-transition.opacity>
                        <template x-for="(video, index) in videos" :key="index">
                            <button @click="changeVideo(index, $el.closest('.group'))"
                                    class="w-2.5 h-2.5 rounded-full transition-all duration-300 shadow-sm"
                                    :class="currentVideo === index ? 'bg-[#216417] scale-125 border border-white/50' : 'bg-white/50 hover:bg-white/80 border border-white/30'"></button>
                        </template>
                    </div>
                </div>
            </div>

            <div>
                <div class="rounded-[1.5rem] border border-slate-200 bg-white/95 shadow-lg overflow-hidden flex flex-col h-full group relative ws-sbtn-container">
                    @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'promotion_images' })" class="ws-sbtn absolute top-2 right-2 z-20"></button> @endif
                    {{-- Image Area (Clean, no hover overlays) --}}
                    <div class="relative aspect-[3/4] bg-slate-100 overflow-hidden">
                        <template x-for="(slide, index) in slides" :key="index">
                            <div x-show="currentSlide === index"
                                 x-transition:enter="transition ease-out duration-500"
                                 x-transition:enter-start="opacity-0 scale-98"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 flex items-center justify-center bg-slate-100">
                                <img :src="slide.image || slide" alt="Promotional Image"
                                     class="max-h-full max-w-full object-contain object-center transition-transform duration-500 group-hover:scale-[1.03]"
                                     onerror="console.error('promo image failed to load', this.src); this.onerror=null; this.src='https://via.placeholder.com/400x600?text=Promo';">
                            </div>
                        </template>
                    </div>

                    {{-- Card Footer Navigation & Control Bar (Outside the Image) --}}
                    <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="font-bold text-sm text-[#216417] truncate">Promotion Offer</div>
                            <div class="text-xs text-slate-500 truncate" x-text="(currentSlide + 1) + ' of ' + slides.length"></div>
                        </div>

                        {{-- Prev / View (Eye) / Next Arrow Buttons Outside the Image --}}
                        <div class="flex items-center gap-2 shrink-0">
                            <button type="button"
                                    x-show="slides.length > 1"
                                    @click.stop="currentSlide = (currentSlide === 0 ? slides.length - 1 : currentSlide - 1)"
                                    class="w-10 h-10 rounded-full bg-slate-100 hover:bg-[#216417] hover:text-white text-slate-700 flex items-center justify-center shadow-sm border border-slate-200 transition-all cursor-pointer focus:outline-none"
                                    :disabled="slides.length <= 1"
                                    title="Previous Slide">
                                <svg class="w-5 h-5 pr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            {{-- Eye Icon View Button in between < and > --}}
                            <button type="button"
                                    @click.stop="modalImage = (slides[currentSlide] ? (slides[currentSlide].image || slides[currentSlide]) : ''); modalOpen = true; zoomLevel = 1;"
                                    class="w-10 h-10 rounded-full bg-[#216417] text-white hover:bg-[#1a5012] flex items-center justify-center shadow-md border border-slate-200 transition-all cursor-pointer focus:outline-none"
                                    title="View & Zoom Image">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                            </button>

                            <button type="button"
                                    x-show="slides.length > 1"
                                    @click.stop="currentSlide = (currentSlide === slides.length - 1 ? 0 : currentSlide + 1)"
                                    class="w-10 h-10 rounded-full bg-slate-100 hover:bg-[#216417] hover:text-white text-slate-700 flex items-center justify-center shadow-sm border border-slate-200 transition-all cursor-pointer focus:outline-none"
                                    :disabled="slides.length <= 1"
                                    title="Next Slide">
                                <svg class="w-5 h-5 pl-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Fullscreen Promotional Image Modal (Direct DOM element, guaranteed to show) --}}
        <div x-show="modalOpen"
             style="display: none;"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="modalOpen = false; zoomLevel = 1;"
             class="fixed inset-0 z-[999999] flex items-center justify-center bg-black/20 backdrop-blur-2xl p-4 sm:p-8"
             @click.self="modalOpen = false; zoomLevel = 1;">

            {{-- Top Right Controls (Zoom Out, Reset, Zoom In, Close) --}}
            <div class="absolute top-4 right-4 sm:top-6 sm:right-6 z-50 flex items-center gap-2 bg-black/50 backdrop-blur-md p-1.5 rounded-full border border-white/15 shadow-xl">
                <button type="button"
                        @click.stop="zoomLevel = Math.max(zoomLevel - 0.35, 0.5)"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer"
                        title="Zoom Out">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                    </svg>
                </button>
                <button type="button"
                        @click.stop="zoomLevel = 1"
                        class="px-2.5 h-9 rounded-full bg-white/10 hover:bg-white/25 text-white text-xs font-bold flex items-center justify-center transition-all cursor-pointer"
                        title="Reset Zoom">
                    100%
                </button>
                <button type="button"
                        @click.stop="zoomLevel = Math.min(zoomLevel + 0.35, 3)"
                        class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 text-white flex items-center justify-center transition-all cursor-pointer"
                        title="Zoom In">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                </button>
                <div class="w-px h-6 bg-white/20 mx-0.5"></div>
                <button type="button"
                        @click="modalOpen = false; zoomLevel = 1;"
                        class="w-9 h-9 rounded-full bg-red-600/80 hover:bg-red-600 text-white flex items-center justify-center transition-all cursor-pointer"
                        title="Close Modal">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            {{-- Previous Slide Button (in modal) --}}
            <button type="button"
                    x-show="slides.length > 1"
                    @click.stop="currentSlide = (currentSlide === 0 ? slides.length - 1 : currentSlide - 1); modalImage = (slides[currentSlide] ? (slides[currentSlide].image || slides[currentSlide]) : ''); zoomLevel = 1;"
                    class="absolute left-4 sm:left-6 top-1/2 -translate-y-1/2 z-50 w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all shadow-lg backdrop-blur-sm cursor-pointer"
                    title="Previous Slide">
                <svg class="w-6 h-6 pr-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>

            {{-- Next Slide Button (in modal) --}}
            <button type="button"
                    x-show="slides.length > 1"
                    @click.stop="currentSlide = (currentSlide === slides.length - 1 ? 0 : currentSlide + 1); modalImage = (slides[currentSlide] ? (slides[currentSlide].image || slides[currentSlide]) : ''); zoomLevel = 1;"
                    class="absolute right-4 sm:right-6 top-1/2 -translate-y-1/2 z-50 w-12 h-12 rounded-full bg-white/20 hover:bg-white/30 text-white flex items-center justify-center transition-all shadow-lg backdrop-blur-sm cursor-pointer"
                    title="Next Slide">
                <svg class="w-6 h-6 pl-0.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </button>

            {{-- Modal Content & Zoomable Image --}}
            <div class="relative max-w-6xl max-h-[85vh] w-full h-full flex flex-col items-center justify-center overflow-auto p-4">
                <img :src="modalImage || (slides[currentSlide] ? (slides[currentSlide].image || slides[currentSlide]) : '')"
                     alt="Promotional Full Image"
                     :style="'transform: scale(' + zoomLevel + '); transition: transform 0.2s ease; transform-origin: center center;'"
                     :class="zoomLevel === 1 ? 'cursor-zoom-in' : 'cursor-zoom-out'"
                     class="max-h-[78vh] max-w-[85vw] object-contain rounded-xl shadow-2xl bg-transparent select-none"
                     @click.stop="zoomLevel = (zoomLevel === 1 ? 1.75 : 1)">

                {{-- Slide Title & Indicator in Modal --}}
                <div class="mt-4 text-center text-white z-40 bg-black/40 px-4 py-1.5 rounded-full backdrop-blur-sm">
                    <div class="font-bold text-sm sm:text-base drop-shadow" x-text="slides[currentSlide] ? (slides[currentSlide].title || 'Promotion') : 'Promotion'"></div>
                    <div class="text-xs text-white/80 mt-0.5" x-text="((currentSlide + 1) + ' of ' + slides.length)"></div>
                </div>
            </div>
        </div>
    </div>

{{-- Booking Request Cards --}}
<div class="max-w-7xl mx-auto px-4 pb-12 mt-10 amiga-animate-on-scroll amiga-transition">
    <div class="text-center mb-10 relative ws-sbtn-container">
        @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'booking_section' })" class="ws-sbtn absolute top-0 right-2 z-20"></button> @endif
        <h2 class="text-3xl sm:text-4xl font-black text-[#216417] tracking-tight">
            {{ data_get($pageContent, 'booking_section_title', 'Request Travel Bookings') }}
        </h2>

        <p class="text-base sm:text-lg text-black font-semibold mt-2">
            {{ data_get($pageContent, 'booking_section_description', 'Kay Amiga, Hassle Free Ka! Select a booking category to start your transaction request.') }}
        </p>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 sm:gap-6 lg:gap-8 relative ws-sbtn-container">
        @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'booking_cards' })" class="ws-sbtn absolute -top-4 right-2 z-20"></button> @endif
        @php
            $bookingCards = data_get($pageContent, 'content.booking_cards', data_get($pageContent, 'booking_cards', []));
            $defaultBookingCards = [
                [
                        'title' => '2GO',
                        'description' => 'Book premier overnight ship accommodation and fast cargo transits with 2GO.',
                        'image' => 'images/2GO-Logo.png',
                        'booking_button_text' => 'Book Now',
                        'link' => '/book/new?operator=' . urlencode('2GO') . '&trip_type=one_way&mode=ferry',
                    ],
                    [
                        'title' => 'Starlite',
                        'description' => 'Affordable regional ferry departures between Batangas, Calapan, and Roxas.',
                        'image' => 'images/Starlite_Logo.png',
                        'booking_button_text' => 'Book Now',
                        'link' => '/book/new?operator=' . urlencode('Starlite') . '&trip_type=one_way&mode=ferry',
                    ],
                    [
                        'title' => 'Cebu Pacific',
                        'description' => 'Search daily flights and budget fares across the Philippines and Asia.',
                        'image' => 'images/CebuPecific-Logo.png',
                        'booking_button_text' => 'Book Now',
                        'link' => '/book/new?operator=' . urlencode('Cebu Pacific') . '&trip_type=one_way&mode=airline',
                    ],
                    [
                        'title' => 'Philippine Airlines',
                        'description' => 'Book Philippine Airlines flights with premium support and flexible fare options.',
                        'image' => 'images/Pal-Logo.jfif',
                        'booking_button_text' => 'Book Now',
                        'link' => '/book/new?operator=' . urlencode('Philippine Airlines') . '&trip_type=one_way&mode=airline',
                    ],
                    [
                        'title' => 'AirAsia',
                        'description' => 'Find low-cost airline tickets and convenient domestic connections.',
                        'image' => 'images/AirAsia-Logo.png',
                        'booking_button_text' => 'Book Now',
                        'link' => '/book/new?operator=' . urlencode('AirAsia') . '&trip_type=one_way&mode=airline',
                    ],
            ];

            $totalCardsNeeded = 5;
            $cards = ! empty($bookingCards) ? array_values($bookingCards) : $defaultBookingCards;
            $cards = array_slice($cards, 0, $totalCardsNeeded);

            while (count($cards) < $totalCardsNeeded) {
                $cards[] = [
                    'title' => 'Travel Booking',
                    'description' => 'Kasiyahan po namin ang paglingkuran kayo.',
                    'image' => null,
                    'booking_button_text' => 'Book Now',
                    'link' => '/book/new',
                ];
            }
        @endphp

        @foreach($cards as $card)
            @php
                $rawCardImage = data_get($card, 'image');
                
                if (is_array($rawCardImage)) {
                    $rawCardImage = array_values(array_filter($rawCardImage))[0] ?? null;
                }

                $cardImage = $rawCardImage
                    ? (str_starts_with($rawCardImage, 'http://') || str_starts_with($rawCardImage, 'https://')
                        ? $rawCardImage
                        : (str_starts_with($rawCardImage, 'images/')
                            ? asset($rawCardImage)
                            : (storage_asset_path($rawCardImage) ?: 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80')))
                    : 'https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';

                $cardTitle = data_get($card, 'title', 'Travel Booking');
                $cardDescription = data_get($card, 'description', 'Kasiyahan po namin ang paglingkuran kayo.');
                $cardLink = data_get($card, 'link', '/book/new');
                $bookingText = data_get($card, 'booking_button_text', 'Book Now');
            @endphp
            <a href="{{ url($cardLink) }}" class="group rounded-xl sm:rounded-[2rem] bg-white border border-slate-200 shadow-sm hover:shadow-xl transition-all duration-200 flex flex-col overflow-hidden">
                @php
                    $isSmallLogo = in_array($cardTitle, ['Cebu Pacific', 'Philippine Airlines', 'Philippine Airline', 'AirAsia']);
                    $paddingClass = $isSmallLogo ? 'p-2 sm:p-4' : 'p-2 sm:p-8';
                @endphp
                <div class="h-20 sm:h-36 w-full bg-white flex items-center justify-center {{ $paddingClass }} border-b border-slate-100">
                    <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="max-h-full max-w-full object-contain transition-transform duration-300 scale-100 group-hover:scale-105" onerror="this.onerror=null;this.src='https://images.unsplash.com/photo-1507525428034-b723cf961d3e?auto=format&fit=crop&w=600&q=80';">
                </div>
                <div class="p-2.5 sm:p-6 flex flex-col flex-grow">
                    <span class="inline-flex items-center gap-1 text-[8px] sm:text-[10px] font-semibold text-[#ee018d] uppercase tracking-wider mb-1 sm:mb-3 leading-tight truncate">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 shrink-0" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2C6.477 2 2 6.477 2 12s4.477 10 10 10 10-4.477 10-10S17.523 2 12 2zm-1 15v-4H7l5-7v4h4l-5 7z"/>
                        </svg>
                        <span class="truncate">Amiga - Best Travel Buddy</span>
                    </span>
                    <h3 class="text-xs sm:text-lg font-bold text-slate-900 mb-1 sm:mb-2 leading-tight truncate">{{ $cardTitle }}</h3>
                    <p class="text-[9px] sm:text-sm text-slate-600 mb-2 sm:mb-4 flex-grow line-clamp-2 sm:line-clamp-none leading-tight">{{ $cardDescription }}</p>
                    <button class="w-full bg-[#ee018d] text-white text-[10px] sm:text-sm font-bold py-1.5 px-2 sm:py-3 sm:px-6 rounded-full hover:bg-pink-700 transition-colors leading-tight">
                        {{ $bookingText }}
                    </button>
                </div>
            </a>
        @endforeach
    </div>
</div>

<div class="max-w-7xl mx-auto px-4 pb-12 amiga-animate-on-scroll amiga-transition relative ws-sbtn-container">
    @if(auth('admin')->check()) <button type="button" @click.prevent="$dispatch('open-editor', { section: 'suggested_trips' })" class="ws-sbtn absolute top-0 right-2 z-20"></button> @endif
    @php
        $suggestedTrips = data_get($pageContent, 'suggested_trips', []);
    @endphp

    @if(!empty($suggestedTrips))
        <div class="bg-white/85 backdrop-blur-md rounded-[2rem] p-8 shadow-xl mb-16">
            <div class="max-w-3xl mx-auto text-center mb-8">
                <p class="text-xs font-semibold uppercase tracking-wider text-emerald-700">{{ data_get($pageContent, 'suggested_trips_title') ? ucfirst(data_get($pageContent, 'suggested_trips_title')) : 'Suggested Trips' }}</p>
                <h2 class="mt-4 text-3xl sm:text-4xl font-bold text-slate-900">{{ data_get($pageContent, 'suggested_trips_title') ?? 'Suggested Trips' }}</h2>
                <p class="mt-4 text-base sm:text-lg text-slate-600 max-w-2xl mx-auto">
                    {{ data_get($pageContent, 'suggested_trips_description') ?? 'Explore these suggested trips.' }}
                </p>
            </div>
            <div x-data="{
                    selectedCard: null,
                    openModal(card) { this.selectedCard = card; },
                    closeModal() { this.selectedCard = null; }
                }"
                class="w-full"
            >
                @if(count($suggestedTrips) > 3)
                    <div class="pause-on-hover flex overflow-hidden gap-6 w-full py-4 -my-4 px-4 -mx-4">
                        <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max">
                            @foreach($suggestedTrips as $card)
                                @php
                                    $rawCardImage = data_get($card, 'image');
                                    $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                    $cardTitle = data_get($card, 'title', 'Trip');
                                    $cardDescription = data_get($card, 'description', 'Discover a wonderful trip.');
                                    $cardDetail = data_get($card, 'detail', '');
                                    $cardPrice = data_get($card, 'price', '');
                                    $cardButtonText = data_get($card, 'button_text', 'View Trip');
                                    $cardButtonLink = data_get($card, 'button_link', '');
                                @endphp
                                <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), price: @json($cardPrice), image: @json($cardImage), link: @json($cardButtonLink), buttonText: @json($cardButtonText ?: "View Trip"), note: "Suggested Trip" })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                    <div class="aspect-[4/3] overflow-hidden">
                                        <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                    </div>
                                    <div class="p-6 flex flex-col gap-4 flex-grow">
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            @if($cardPrice)<p class="text-sm font-semibold text-emerald-700">{{ $cardPrice }}</p>@endif
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                        <div class="mt-auto pt-4">
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800 min-h-[36px] min-w-[100px]">
                                                {{ $cardButtonText ?: 'View Trip' }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                        <div class="flex flex-nowrap gap-6 animate-infinite-scroll min-w-max" aria-hidden="true">
                            @foreach($suggestedTrips as $card)
                                @php
                                    $rawCardImage = data_get($card, 'image');
                                    $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                    $cardTitle = data_get($card, 'title', 'Trip');
                                    $cardDescription = data_get($card, 'description', 'Discover a wonderful trip.');
                                    $cardDetail = data_get($card, 'detail', '');
                                    $cardPrice = data_get($card, 'price', '');
                                    $cardButtonText = data_get($card, 'button_text', 'View Trip');
                                    $cardButtonLink = data_get($card, 'button_link', '');
                                @endphp
                                <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), price: @json($cardPrice), image: @json($cardImage), link: @json($cardButtonLink), buttonText: @json($cardButtonText ?: "View Trip"), note: "Suggested Trip" })' class="w-[320px] shrink-0 group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                    <div class="aspect-[4/3] overflow-hidden">
                                        <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                    </div>
                                    <div class="p-6 flex flex-col gap-4 flex-grow">
                                        <div>
                                            <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                            @if($cardPrice)<p class="text-sm font-semibold text-emerald-700">{{ $cardPrice }}</p>@endif
                                        </div>
                                        <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                        <div class="mt-auto pt-4">
                                            <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800 min-h-[36px] min-w-[100px]">
                                                {{ $cardButtonText ?: 'View Trip' }}
                                            </span>
                                        </div>
                                    </div>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                        @foreach($suggestedTrips as $card)
                            @php
                                $rawCardImage = data_get($card, 'image');
                                $cardImage = $rawCardImage ? storage_asset_path($rawCardImage) : 'https://images.unsplash.com/photo-1506929562872-bb421503ef21?auto=format&fit=crop&w=600&q=80';
                                $cardTitle = data_get($card, 'title', 'Trip');
                                $cardDescription = data_get($card, 'description', 'Discover a wonderful trip.');
                                $cardDetail = data_get($card, 'detail', '');
                                $cardPrice = data_get($card, 'price', '');
                                $cardButtonText = data_get($card, 'button_text', 'View Trip');
                                $cardButtonLink = data_get($card, 'button_link', '');
                            @endphp
                            <button type="button" @click='openModal({ title: @json($cardTitle), description: @json($cardDescription), detail: @json($cardDetail), price: @json($cardPrice), image: @json($cardImage), link: @json($cardButtonLink), buttonText: @json($cardButtonText ?: "View Trip"), note: "Suggested Trip" })' class="group flex flex-col overflow-hidden rounded-[2rem] border border-slate-200 bg-white shadow-sm transition duration-200 hover:shadow-lg text-left">
                                <div class="aspect-[4/3] overflow-hidden">
                                    <img src="{{ $cardImage }}" alt="{{ $cardTitle }}" class="h-full w-full object-cover transition duration-200 group-hover:scale-105" />
                                </div>
                                <div class="p-6 flex flex-col gap-4 flex-grow">
                                    <div>
                                        <h3 class="text-xl font-bold text-slate-900">{{ $cardTitle }}</h3>
                                        @if($cardPrice)<p class="text-sm font-semibold text-emerald-700">{{ $cardPrice }}</p>@endif
                                    </div>
                                    <p class="text-sm text-slate-600 leading-relaxed">{{ $cardDescription }}</p>
                                    <div class="mt-auto pt-4">
                                        <span class="inline-flex items-center justify-center rounded-full bg-[#216417] px-4 py-2 text-sm font-semibold text-white shadow-sm transition duration-200 group-hover:bg-green-800 min-h-[36px] min-w-[100px]">
                                            {{ $cardButtonText ?: 'View Trip' }}
                                        </span>
                                    </div>
                                </div>
                            </button>
                        @endforeach
                    </div>
                @endif

                <div x-show="selectedCard" x-cloak x-transition.opacity class="fixed inset-0 z-50 bg-slate-900/70 flex items-center justify-center p-4" @click.self="closeModal()">
                    <div class="relative w-full max-w-3xl overflow-hidden rounded-[2rem] bg-white shadow-2xl">
                        <button type="button" @click="closeModal()" class="absolute right-4 top-4 rounded-full bg-white/90 p-2 text-slate-700 hover:bg-white z-10">
                            <span class="sr-only">Close</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M10 8.586l4.95-4.95a1 1 0 111.414 1.414L11.414 10l4.95 4.95a1 1 0 01-1.414 1.414L10 11.414l-4.95 4.95a1 1 0 01-1.414-1.414L8.586 10 3.636 5.05a1 1 0 011.414-1.414L10 8.586z" clip-rule="evenodd"/></svg>
                        </button>
                        <img x-bind:src="selectedCard?.image" x-bind:alt="selectedCard?.title" class="w-full max-h-80 object-cover" />
                        <div class="p-8">
                            <span class="inline-flex items-center gap-2 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#ee018d] mb-4" x-text="selectedCard?.note"></span>
                            <h2 class="text-2xl font-bold text-slate-900 mb-1" x-text="selectedCard?.title"></h2>
                            <p class="text-base font-semibold text-emerald-700 mb-3" x-show="selectedCard?.price" x-text="selectedCard?.price"></p>
                            <p class="text-sm text-slate-500 mb-4" x-text="selectedCard?.description"></p>
                            <p class="text-sm text-slate-600 leading-relaxed" x-show="selectedCard?.detail" x-text="selectedCard?.detail"></p>
                            <div class="mt-6" x-show="selectedCard?.link">
                                <a :href="selectedCard?.link" class="inline-flex items-center justify-center rounded-full bg-[#216417] px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-green-800 transition" x-text="selectedCard?.buttonText"></a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

{{-- SEO Subpage Sitelinks --}}
<div class="w-full bg-white py-12 border-t border-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <h2 class="sr-only">Explore Amiga Gracia Services</h2>
        <div class="flex flex-wrap items-center justify-center gap-4 md:gap-8 text-center">
            <a href="{{ url('/about') }}" class="text-sm font-semibold text-slate-600 hover:text-[#008000] hover:underline transition-colors">Learn About Us</a>
            <a href="{{ url('/schedules') }}" class="text-sm font-semibold text-slate-600 hover:text-[#008000] hover:underline transition-colors">Check Ferry Schedules</a>
            <a href="{{ url('/services') }}" class="text-sm font-semibold text-slate-600 hover:text-[#008000] hover:underline transition-colors">View Our Services</a>
            <a href="{{ url('/tour-package') }}" class="text-sm font-semibold text-slate-600 hover:text-[#008000] hover:underline transition-colors">Explore Tour Packages</a>
            <a href="{{ url('/download') }}" class="text-sm font-semibold text-slate-600 hover:text-[#008000] hover:underline transition-colors">Download App</a>
        </div>
    </div>
</div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var animatedSections = document.querySelectorAll('.amiga-animate-on-scroll');
        if (!('IntersectionObserver' in window) || animatedSections.length === 0) {
            animatedSections.forEach(function (el) {
                el.classList.add('amiga-visible');
            });
            return;
        }

        var observer = new IntersectionObserver(function (entries, observer) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('amiga-visible');
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.15,
        });

        animatedSections.forEach(function (el) {
            observer.observe(el);
        });
    });
</script>

<style>
    .amiga-transition {
        opacity: 0;
        transform: translateY(20px);
        transition: opacity 0.65s ease, transform 0.65s ease;
        will-change: opacity, transform;
    }

    .amiga-visible {
        opacity: 1 !important;
        transform: none !important;
    }

    /* ── Suggested Trips Infinite Auto-Scroll Carousel ── */
    @keyframes infinite-scroll {
        from { transform: translateX(0); }
        to   { transform: translateX(-50%); }
    }

    .animate-infinite-scroll {
        animation: infinite-scroll 30s linear infinite;
        will-change: transform;
    }

    .pause-on-hover:hover .animate-infinite-scroll {
        animation-play-state: paused;
    }
</style>
@endsection
