<?php

namespace App\Livewire;

use App\Mail\BookingConfirmation;
use App\Mail\BookingCreated;
use App\Models\Accommodation;
use App\Models\Booking;
use App\Models\Discount;
use App\Models\FerryRoute;
use App\Models\Tour;
use App\Models\TourDate;
use App\Models\Passenger;
use App\Models\PaymentSetting;
use App\Models\Schedule;
use App\Models\ScheduleAccommodation;
use App\Models\TransportClass;
use App\Models\VehicleBrand;
use App\Models\VehicleModel;
use App\Models\VehicleRate;
use App\Models\Transaction;
use App\Models\PromotionalTicket;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;
use Livewire\WithFileUploads;
use Carbon\Carbon;
use Spatie\LaravelPdf\Facades\Pdf;
use Illuminate\Support\Facades\Log;
use Throwable;
use Dompdf\Dompdf;
use Dompdf\Options;

class BookingForm extends Component
{
    use WithFileUploads;
    
    protected function failedValidation(\Illuminate\Contracts\Validation\Validator $validator)
    {
        $this->dispatch('validation-error');
        parent::failedValidation($validator);
    }
    
    public function confirmOperatorSelection(): void
    {
        $this->showOperatorConfirmation = false;
        $confirmKey = 'confirmed_operator_' . $this->mode . '_' . ($this->operator ?: 'all');
        session()->put($confirmKey, true);
    }
    
    public function changeSelection()
    {
        $confirmKey = 'confirmed_operator_' . $this->mode . '_' . ($this->operator ?: 'all');
        session()->forget($confirmKey);
        // Clear the pre-selected values so the user can choose again
        $this->adults = 1;
        $this->children = 0;
        $this->minors = 0;
        $this->infants = 0;
        $this->mode = '';
        $this->operator = null;
        $this->isModePreselected = false;
        $this->isOperatorPreselected = false;
        $this->showOperatorConfirmation = false;
        
        return redirect('/');
    }
    public int $step = 1;
    public string $trip_type = 'one_way';
    public string $mode = '';
    public string $origin = '';
    public string $destination = '';
    public ?string $departure_date = null;
    public ?string $return_date = null;
    public ?int $duration_days = null;
    public array $available_package_dates = [];
    public array $available_schedule_dates = [];
    public array $available_return_schedule_dates = [];
    public array $availableReturnSchedules = [];
    public int $adults = 1;
    public int $children = 0;
    public int $minors = 0;
    public int $infants = 0;
    public ?int $selected_schedule_id = null;
    public bool $showPassengerInfoModal = false;
    public bool $showMinorAgeWarning = false;
    public bool $hasSeenMinorAgeWarning = false;
    public bool $showModeDropdown = false;
    public bool $showOperatorDropdown = false;
    public bool $showOriginDropdown = false;
    public bool $showDestinationDropdown = false;
    public string $originSearch = '';
    public string $destinationSearch = '';
    public ?string $operator = null;
    public bool $showPresentIdWarning = false;
    public bool $hasSeenPresentIdWarning = false;
    public bool $showDataPrivacyWarning = true;
    public bool $showOperatorConfirmation = false;
    public bool $isOperatorPreselected = false;
    public bool $isModePreselected = false;

    // Each entry: ['type' => 'adult'|'child', 'name' => '', 'discount_id' => null]
    public array $passengers = [];
    public array $studentIdProofFronts = [];
    public array $studentIdProofBacks = [];

    protected $validationAttributes = [
        'passengers.*.first_name' => 'first name',
        'passengers.*.middle_name' => 'middle name',
        'passengers.*.last_name' => 'last name',
        'passengers.*.name' => 'full name',
        'passengers.*.birthdate' => 'date of birth',
        'passengers.*.student_number' => 'student number',
        'studentIdProofFronts.*' => 'school ID proof front',
        'studentIdProofBacks.*' => 'school ID proof back',
        'passengers.*.senior_osca_number' => 'OSCA number',
        'passengers.*.pwd_id_number' => 'PWD ID number',
        'passengers.*.passport_country' => 'passport issuing country',
        'passengers.*.passport_number' => 'passport number',
        'passengers.*.passport_issuance_date' => 'passport issuance date',
        'passengers.*.passport_expiry_date' => 'passport expiry date',
        'vehicle_type' => 'vehicle type',
        'vehicle_plate_number' => 'plate number',
        'vehicle_price' => 'vehicle price',
        'extra_baggage_type' => 'baggage item category',
        'extra_baggage_specify' => 'specified baggage details',
    ];

    // Selected schedule accommodation id
    public ?int $selected_schedule_accommodation_id = null;
    public ?int $selected_return_schedule_id = null;
    public ?int $selected_return_schedule_accommodation_id = null;
    public ?int $selected_transport_class_id = null;
    public ?int $selected_return_transport_class_id = null;

    public ?int $tour_id = null;
    public ?int $tour_date_id = null;
    public ?Tour $tour = null;
    public ?TourDate $selectedTourDate = null;

    // Prefilled package info (from CSV)
    public string $package_name = '';
    public string $package_price = '';
    public bool $prefilled_from_package = false;

    // Car booking fields
    public bool $has_vehicle = false;
    public string $vehicle_booking_method = 'category';
    public ?int $selected_vehicle_rate_id = null;
    public ?int $selected_brand_id = null;
    public ?int $selected_model_id = null;
    public string $vehicle_type = '';
    public string $vehicle_plate_number = '';
    public ?float $vehicle_price = null;
    public string $driver_first_name = '';
    public string $driver_middle_name = '';
    public string $driver_last_name = '';
    public string $driver_name = '';  // computed full name, kept for backward compat
    public ?string $driver_birthday = null;
    public bool $showBaggageRules = false;
    public bool $hasExtraBaggage = false;
    public string $baggage_trip_type = 'local'; // 'local' or 'international'
    public string $selected_baggage_airline = '';
    public string $extra_baggage_weight = '';
    public ?float $extra_baggage_price = null;
    public ?string $extra_baggage_type = '';
    public ?string $extra_baggage_specify = '';
    // NOTE: use_promo_ticket is kept for ferry bookings (backward compat).
    // For airline bookings the per-passenger $passengers[n]['use_promo'] flag is used instead.
    public bool $use_promo_ticket = false;

    public string $client_name = '';
    public string $client_email = '';
    public string $client_phone = '';
    public bool $showTermsModal = false;
    public bool $showPrivacyModal = false;
    public bool $hasAcceptedTerms = false;
    public bool $hasAcceptedPrivacy = false;
    public bool $showTermsAgreementWarning = false;
    public bool $showPrivacyAgreementWarning = false;
    public bool $isSubmittingBooking = false;
    public ?int $selected_hotel_id = null;
    public array $availableSchedules = [];

    public function mount(): void
    {
        // Check both session AND cookie so consent persists across browser sessions
        if (session()->get('has_accepted_data_privacy_warning', false) || Cookie::get('data_privacy_accepted') === '1') {
            $this->showDataPrivacyWarning = false;
        }

        $this->availableSchedules = [];

        // Check if we have tour/package query params first
        $allowed = [
            'trip_type','mode','operator','origin','destination','departure_date','return_date','duration_days','adults','children','minors','infants',
            'client_name','client_email','client_phone','hasAcceptedTerms','hasAcceptedPrivacy','selected_hotel','selected_hotel_id','hotel','package_name','price','tour_id','tour_date_id','has_vehicle',
            'vehicle_booking_method','selected_vehicle_rate_id','selected_brand_id','selected_model_id','vehicle_plate_number','driver_first_name','driver_middle_name','driver_last_name','driver_name','driver_birthday'
        ];
        $packageQueryKeys = ['tour_id','tour_date_id','package_name','price','available_dates'];
        $hasPackageQueryParams = ! empty(array_intersect(array_keys(request()->query()), $packageQueryKeys));
        
        // Check if mode and operator are pre-selected from card click
        $this->isModePreselected = ! empty(request()->query('mode'));
        $this->isOperatorPreselected = ! empty(request()->query('operator'));

        // If we have package/tour query params, ignore session draft entirely; otherwise load draft first
        $hasSessionDraft = session()->has('booking_draft');
        if (!$hasPackageQueryParams && $hasSessionDraft) {
            $draft = session('booking_draft', []);
            foreach ($draft as $key => $value) {
                if (property_exists($this, $key)) {
                    $this->{$key} = $value;
                }
            }
            // Manually map hasExtraBaggage since it's camelCase but stored as snake_case in draft
            if (isset($draft['has_extra_baggage'])) {
                $this->hasExtraBaggage = $draft['has_extra_baggage'];
            }
            if (isset($draft['extra_baggage_weight'])) {
                $this->extra_baggage_weight = $draft['extra_baggage_weight'];
            }
            if (isset($draft['extra_baggage_type'])) {
                $this->extra_baggage_type = $draft['extra_baggage_type'];
            }
            if (isset($draft['extra_baggage_specify'])) {
                $this->extra_baggage_specify = $draft['extra_baggage_specify'];
            }
        } else {
            // If we have package params, clear the draft to avoid conflicts
            session()->forget('booking_draft');
        }

        $isOperatorLinkWithoutPackage = request()->query('operator') && empty(array_intersect(array_keys(request()->query()), $packageQueryKeys));
        if ($isOperatorLinkWithoutPackage) {
            $this->tour_id = null;
            $this->tour = null;
            $this->selectedTourDate = null;
            $this->prefilled_from_package = false;
        }

        // vehicleModelCatalog is now computed dynamically.

        // Now apply tour/package query params
        // Pre-fill tour if present in query params
        $reqTour = request()->query('tour_id');
        $reqTourDate = request()->query('tour_date_id');
        if ($reqTour) {
            $this->tour_id = intval($reqTour);
            $this->tour = Tour::with('dates')->find($this->tour_id);

            if ($this->tour) {
                // Prefill route/mode from tour if provided
                if ($this->tour->mode) {
                    $this->mode = $this->tour->mode;
                }

                if ($this->tour->origin) {
                    $this->origin = $this->tour->origin;
                }

                if ($this->tour->destination) {
                    $this->destination = $this->tour->destination;
                }
                
                // Set duration days from the tour!
                $this->duration_days = $this->tour->duration_days;
                
                // If a tour date was passed, preselect it; otherwise allow the user to pick from tour dates
                if ($reqTourDate) {
                    $this->tour_date_id = intval($reqTourDate);
                    $this->selectedTourDate = $this->tour->dates->firstWhere('id', $this->tour_date_id) ?: TourDate::find($this->tour_date_id);

                    if ($this->selectedTourDate) {
                        $this->departure_date = Carbon::parse($this->selectedTourDate->date)->format('Y-m-d');
                        $this->return_date = Carbon::parse($this->selectedTourDate->date)->addDays($this->tour->duration_days - 1)->format('Y-m-d');
                    }
                }
            }
        }

        // Prefill other booking fields from query params
        $hasQueryParamsPrefill = false;
        foreach (request()->query() as $key => $value) {
            if (in_array($key, $allowed, true) && property_exists($this, $key)) {
                $hasQueryParamsPrefill = true;
                // cast ints where appropriate
                if (in_array($key, ['adults','children','minors','infants','duration_days','selected_vehicle_rate_id','selected_brand_id','selected_model_id'], true)) {
                    $this->{$key} = intval($value);
                } elseif ($key === 'has_vehicle') {
                    $this->{$key} = filter_var($value, FILTER_VALIDATE_BOOLEAN);
                } else {
                    $this->{$key} = $value;
                }
            }
        }
        if ($this->operator) {
            $this->operator = normalize_operator_name($this->operator);
        }
        if ($this->has_vehicle) {
            if ($this->vehicle_booking_method === 'category' && $this->selected_vehicle_rate_id) {
                $this->updatedSelectedVehicleRateId($this->selected_vehicle_rate_id);
            } elseif ($this->vehicle_booking_method === 'brand_model' && $this->selected_model_id) {
                $this->updatedSelectedModelId($this->selected_model_id);
            }
        }
        if ($hasQueryParamsPrefill) {
            $this->saveDraft();
        }

        // Advance directly to schedule step when the search is fully prefilled from home and step=2 is requested.
        $requestedStep = intval(request()->query('step', 1));
        if ($this->mode) {
            $this->mode = strtolower(trim($this->mode));
        }

        if ($requestedStep === 2 && ! $this->tour_id && $this->mode && $this->operator && $this->origin && $this->destination && $this->departure_date) {
            $this->step = 2;
            $this->availableSchedules = $this->getAvailableSchedules();
            $this->availableReturnSchedules = $this->getAvailableReturnSchedules();
        }

        // Mark that the form has been prefilled from a package only when actual package/tour params are present
        $packagePrefillKeys = array_intersect(array_keys(request()->query()), $packageQueryKeys);
        if (! empty($packagePrefillKeys)) {
            $this->prefilled_from_package = true;
            // also populate package_name and package_price if present
            $this->package_name = request()->query('package_name', $this->package_name);
            $this->package_price = request()->query('price', $this->package_price);
        } else {
            $this->prefilled_from_package = false;
        }

        // If API passed an available_dates list (comma-separated) or multiple params, parse them into array
        $rawAvailable = request()->query('available_dates');
        if ($rawAvailable) {
            if (is_array($rawAvailable)) {
                $candidates = $rawAvailable;
            } else {
                $candidates = preg_split('/[;,|]+/', $rawAvailable);
            }

            foreach ($candidates as $cand) {
                $cand = trim((string) $cand);
                if ($cand === '') continue;
                try {
                    $dt = Carbon::parse($cand);
                    $iso = $dt->format('Y-m-d');
                    if (! in_array($iso, $this->available_package_dates, true)) {
                        $this->available_package_dates[] = $iso;
                    }
                } catch (\Throwable $e) {
                    // ignore unparseable entries
                }
            }
        }

        // If a duration_days param was provided, store duration_days
        $durationDays = request()->query('duration_days');
        if ($durationDays !== null) {
            $this->duration_days = intval($durationDays);
        }
        
        // Show clarification modal if mode and operator are pre-selected from a card click (only if not already confirmed)
        $confirmKey = 'confirmed_operator_' . $this->mode . '_' . ($this->operator ?: 'all');
        if ($this->isModePreselected && $this->isOperatorPreselected && ! session()->has($confirmKey)) {
            $this->showOperatorConfirmation = true;
        }

        // For tour packages: force round trip and lock it
        if ($this->prefilled_from_package || $this->tour_id) {
            $this->trip_type = 'round_trip';
        }
        // If the package has duration days and no explicit trip type, assume round trip
        elseif ($this->duration_days > 1 && $this->trip_type === 'one_way') {
            $this->trip_type = 'round_trip';
        }

        // If return_date is missing, compute it from departure_date and duration_days.
        if (empty($this->return_date) && ! empty($this->departure_date) && ! empty($this->duration_days) && $this->duration_days > 1) {
            $this->trip_type = 'round_trip';
            $this->updateReturnDateFromDuration();
        }

        // If hotel name was provided (hotel) but selected_hotel_id not, try to resolve by name
        $hotelName = request()->query('hotel') ?? request()->query('selected_hotel');
        if (! empty($hotelName) && empty($this->selected_hotel_id)) {
            $hotel = Accommodation::query()->where('name', 'like', '%' . trim($hotelName) . '%')->first();
            if ($hotel) {
                $this->selected_hotel_id = $hotel->id;
            }
        }

        // Fetch available schedules if needed
        if (! blank($this->origin) && ! blank($this->destination)) {
            $this->updateAvailableScheduleDates();
            if (! blank($this->departure_date)) {
                $this->availableSchedules = $this->getAvailableSchedules();
            }
            // Also fetch return schedules for round trips
            if ($this->trip_type === 'round_trip' && ! blank($this->return_date)) {
                $this->availableReturnSchedules = $this->getAvailableReturnSchedules();
            }
        }

        $this->clampPassengersToMax();
        $this->syncPassengerEntries();
    }

    #[Computed]
    public function origins(): array
    {
        if (blank($this->mode)) {
            return [];
        }

        return FerryRoute::scheduleOrigins($this->mode, $this->operator);
    }

    #[Computed]
    public function destinations(): array
    {
        if (blank($this->origin)) {
            return [];
        }

        $requireReturn = $this->trip_type === 'round_trip';

        return FerryRoute::scheduleDestinationsFor($this->origin, $this->mode, $this->operator, $requireReturn);
    }

    #[Computed]
    public function filteredOrigins(): array
    {
        if (blank($this->originSearch)) {
            return $this->origins;
        }

        return collect($this->origins)
            ->filter(fn ($item) => str_starts_with(strtolower($item), strtolower($this->originSearch)))
            ->values()
            ->all();
    }

    #[Computed]
    public function filteredDestinations(): array
    {
        if (blank($this->destinationSearch)) {
            return $this->destinations;
        }

        return collect($this->destinations)
            ->filter(fn ($item) => str_starts_with(strtolower($item), strtolower($this->destinationSearch)))
            ->values()
            ->all();
    }

    #[Computed]
    public function operators(): array
    {
        if (blank($this->mode)) {
            return [];
        }

        return FerryRoute::scheduleOperatorsFor($this->mode);
    }

    #[Computed]
    public function operatorLogos(): array
    {
        return \App\Models\Operator::where('is_active', true)
            ->whereNotNull('logo_path')
            ->get()
            ->mapWithKeys(function ($op) {
                return [$op->name => $op->logo_url];
            })
            ->toArray();
    }
    
    #[Computed]
    public function baggageRules(): ?array
    {
        if (blank($this->operator)) {
            return null;
        }
        
        $json = \Illuminate\Support\Facades\Cache::remember('baggage_rules_json_v1', now()->addHours(12), function () {
            $filePath = base_path('baggage-rules.json');
            if (!file_exists($filePath)) {
                return null;
            }
            return json_decode(file_get_contents($filePath), true);
        });
        if (!$json) {
            return null;
        }

        $carriers = $json['carriers'] ?? [];
        $meta = $json['meta'] ?? [];
        
        // Normalize operator name to match with possible keys
        $normalizedOperator = strtolower(trim($this->operator));
        
        foreach ($carriers as $carrier) {
            $carrierName = strtolower(trim($carrier['name'] ?? ''));
            $carrierId = strtolower(trim($carrier['id'] ?? ''));
            
            if (str_contains($carrierName, $normalizedOperator) || str_contains($normalizedOperator, $carrierName) || $carrierId === $normalizedOperator) {
                return array_merge($carrier, ['meta' => $meta]);
            }
        }
        
        return null;
    }

    public int $maxStep = 5;

    #[Computed]
public function selectedSchedule(): ?array
{
    if (! $this->selected_schedule_id) {
        return null;
    }

    return collect($this->availableSchedules)->firstWhere('id', $this->selected_schedule_id);
}

    public function updatedTripType(string $value): void
    {
        if ($this->prefilled_from_package || $this->tour_id) {
            $this->trip_type = 'round_trip';
        } else {
            $this->trip_type = $value;
        }

        if ($this->trip_type === 'round_trip') {
            $this->clampPassengersToMax();
        }

        if ($this->trip_type === 'round_trip' && !empty($this->destination)) {
            $hasReturn = FerryRoute::hasBidirectionalSchedules(
                $this->origin,
                $this->destination,
                $this->mode,
                $this->operator
            );
            if (! $hasReturn) {
                $this->destination = '';
                $this->return_date = null;
                $this->selected_return_schedule_id = null;
                $this->selected_return_schedule_accommodation_id = null;
                $this->availableReturnSchedules = [];
            }
        }
        
        if ($this->trip_type === 'one_way') {
            $this->return_date = null;
            $this->selected_return_schedule_id = null;
            $this->selected_return_schedule_accommodation_id = null;
            $this->availableReturnSchedules = [];
            $this->available_return_schedule_dates = [];
            $this->syncPassengerEntries();
            $this->saveDraft();
            return;
        }
        
        if (!$this->updateReturnDateFromDuration() && !empty($this->departure_date) && empty($this->return_date)) {
            try {
                $dt = Carbon::parse($this->departure_date);
                $this->return_date = $dt->addDay()->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }
        $this->updateAvailableScheduleDates();
        $this->syncPassengerEntries();
        $this->saveDraft();
    }

    public function setTripType(string $type): void
    {
        if (! in_array($type, ['one_way', 'round_trip'], true)) {
            return;
        }

        if ($this->prefilled_from_package || $this->tour_id) {
            $this->trip_type = 'round_trip';
        } else {
            $this->trip_type = $type;
        }

        if ($this->trip_type === 'round_trip') {
            $this->clampPassengersToMax();
        }

        if ($this->trip_type === 'round_trip' && !empty($this->destination)) {
            $hasReturn = FerryRoute::hasBidirectionalSchedules(
                $this->origin,
                $this->destination,
                $this->mode,
                $this->operator
            );
            if (! $hasReturn) {
                $this->destination = '';
                $this->return_date = null;
                $this->selected_return_schedule_id = null;
                $this->selected_return_schedule_accommodation_id = null;
                $this->availableReturnSchedules = [];
            }
        }
        
        if ($this->trip_type === 'one_way') {
            $this->return_date = null;
            $this->selected_return_schedule_id = null;
            $this->selected_return_schedule_accommodation_id = null;
            $this->availableReturnSchedules = [];
            $this->available_return_schedule_dates = [];
            $this->syncPassengerEntries();
            $this->saveDraft();
            return;
        }
        
        if (!$this->updateReturnDateFromDuration() && !empty($this->departure_date) && empty($this->return_date)) {
            try {
                $dt = Carbon::parse($this->departure_date);
                $this->return_date = $dt->addDay()->format('Y-m-d');
            } catch (\Throwable $e) {
            }
        }
        $this->updateAvailableScheduleDates();
        $this->syncPassengerEntries();
        $this->saveDraft();
    }

    public function updatedDepartureDate(?string $value): void
    {
        $this->departure_date = $value;
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        
        // If it's a tour package with duration, recalculate return date
        if (($this->prefilled_from_package || $this->tour_id) && !empty($this->duration_days) && $this->duration_days > 1) {
            $this->updateReturnDateFromDuration();
        }
        
        $this->saveDraft();
    }

    public function updatedReturnDate(?string $value): void
    {
        $this->return_date = $value;
        $this->selected_return_schedule_id = null;
        $this->availableReturnSchedules = [];
        $this->saveDraft();
    }

    #[On('datePickerUpdated')]
    public function onDatePickerUpdated($field = null, $value = null): void
    {
        if (is_array($field)) {
            $value = $field['value'] ?? null;
            $field = $field['field'] ?? null;
        }

        if ($field === 'departure_date') {
            if ($this->departure_date !== $value) {
                $this->updatedDepartureDate($value);
            }
        } elseif ($field === 'return_date') {
            if ($this->return_date !== $value) {
                $this->updatedReturnDate($value);
            }
        }
    }

    public function updatedDurationDays(): void
    {
        $this->updateReturnDateFromDuration();
    }

    protected function updateReturnDateFromDuration(): bool
    {
        if (empty($this->departure_date) || empty($this->duration_days) || $this->duration_days < 2) {
            return false;
        }

        try {
            $dt = Carbon::parse($this->departure_date);
            $this->return_date = $dt->addDays($this->duration_days - 1)->format('Y-m-d');
            if ($this->trip_type !== 'round_trip') {
                $this->trip_type = 'round_trip';
            }
            return true;
        } catch (\Throwable $e) {
            // ignore parse errors
            return false;
        }
    }

    protected $listeners = [
        'datePickerUpdated',
        'dropdownOpened' => 'onDropdownOpened',
    ];

    public function updatedMode(string $value): void
    {
        $this->mode = $value;
        $this->origin = '';
        $this->destination = '';
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->resetVehicleData();
    }

    public function getModeOptions(): array
    {
        return [
            'ferry' => 'Ferry',
            'airline' => 'Airline',
        ];
    }

    public function toggleModeDropdown(): void
    {
        $this->showModeDropdown = ! $this->showModeDropdown;
        if ($this->showModeDropdown) {
            $this->showOperatorDropdown = false;
            $this->showOriginDropdown = false;
            $this->showDestinationDropdown = false;
            $this->dispatch('dropdownOpened', name: 'mode');
        }
    }

    public function toggleOperatorDropdown(): void
    {
        $this->showOperatorDropdown = ! $this->showOperatorDropdown;
        if ($this->showOperatorDropdown) {
            $this->showModeDropdown = false;
            $this->showOriginDropdown = false;
            $this->showDestinationDropdown = false;
            $this->dispatch('dropdownOpened', name: 'operator');
        }
    }

    public function onDropdownOpened($name = null): void
    {
        if (is_array($name) && isset($name['name'])) {
            $name = $name['name'];
        }

        // If another dropdown opened and it's not one of BookingForm's, close ours.
        if ($name === null) {
            $this->showModeDropdown = false;
            $this->showOperatorDropdown = false;
            $this->showOriginDropdown = false;
            $this->showDestinationDropdown = false;
            return;
        }

        if ($name !== 'mode') {
            $this->showModeDropdown = false;
        }
        if ($name !== 'operator') {
            $this->showOperatorDropdown = false;
        }
        if ($name !== 'origin') {
            $this->showOriginDropdown = false;
        }
        if ($name !== 'destination') {
            $this->showDestinationDropdown = false;
        }
    }

    public function selectMode(string $mode): void
    {
        if (! array_key_exists($mode, $this->getModeOptions())) {
            return;
        }

        $this->mode = $mode;
        $this->operator = null;
        $this->origin = '';
        $this->destination = '';
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->resetVehicleData();
        $this->showModeDropdown = false;
        $this->saveDraft();
    }

    public function selectOperator(?string $operator): void
    {
        $this->operator = $operator;
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->showOperatorDropdown = false;
        $this->resetVehicleData();
        $this->updateAvailableScheduleDates();
        $this->saveDraft();
    }

    protected function resetVehicleData(): void
    {
        if (strtolower($this->mode) === 'airline' || ($this->mode === 'ferry' && stripos($this->operator ?? '', 'Starlite') === false)) {
            $this->has_vehicle = false;
            $this->selected_vehicle_rate_id = null;
            $this->vehicle_type = '';
            $this->vehicle_plate_number = '';
            $this->vehicle_price = null;
        }
    }

    public function acceptDataPrivacyWarning(): void
    {
        $this->showDataPrivacyWarning = false;
        session()->put('has_accepted_data_privacy_warning', true);
        // Set a cookie that lasts 365 days so consent is remembered across sessions
        Cookie::queue('data_privacy_accepted', '1', 60 * 24 * 365);
    }

    public function declineDataPrivacyWarning()
    {
        session()->forget('has_accepted_data_privacy_warning');
        Cookie::queue(Cookie::forget('data_privacy_accepted'));
        return redirect()->to('/');
    }

    public function getAirlineExtraBaggageRates(): array
    {
        $scope = $this->autoDetectBaggageScope();
        $this->baggage_trip_type = $scope;
        return \App\Models\AirlineBaggageRule::getRatesForBooking($scope);
    }

    public function autoDetectBaggageScope(): string
    {
        if ($this->isInternational) {
            return 'international';
        }
        if ($this->selected_schedule_id) {
            $sched = Schedule::with('ferryRoute')->find($this->selected_schedule_id);
            if ($sched?->ferryRoute?->trip_type) {
                return $sched->ferryRoute->trip_type;
            }
        }
        if ($this->origin && $this->destination) {
            $routeScope = \App\Models\FerryRoute::query()
                ->where('origin', $this->origin)
                ->where('destination', $this->destination)
                ->whereNotNull('trip_type')
                ->value('trip_type');
            if ($routeScope) {
                return $routeScope;
            }
        }

        return 'local';
    }

    public function updateBaggagePriceFromRates(): void
    {
        $rates = $this->getAirlineExtraBaggageRates();
        $key = $this->selected_baggage_airline ?: $this->autoDetectBaggageAirline();
        $this->selected_baggage_airline = $key;

        $airlineOptions = $rates[$key]['options'] ?? [];
        $optionsByWeight = [];
        foreach ($airlineOptions as $opt) {
            $optionsByWeight[$opt['weight']] = floatval($opt['price']);
        }

        // Re-sync each passenger's price to match the selected operator's rates
        foreach ($this->passengers as $idx => $pax) {
            if (! empty($pax['extra_baggage_weight'])) {
                $w = $pax['extra_baggage_weight'];
                if (isset($optionsByWeight[$w])) {
                    $this->passengers[$idx]['extra_baggage_price'] = $optionsByWeight[$w];
                } else {
                    $this->passengers[$idx]['extra_baggage_weight'] = '';
                    $this->passengers[$idx]['extra_baggage_price'] = 0.0;
                }
            }
        }
        $this->hasExtraBaggage = $this->getExtraBaggageTotalPrice() > 0;
    }

    public function updatedBaggageTripType($value): void
    {
        if (! $this->selected_baggage_airline) {
            $this->selected_baggage_airline = $this->autoDetectBaggageAirline();
        }
        $this->updateBaggagePriceFromRates();
        $this->saveDraft();
    }

    public function autoDetectBaggageAirline(): string
    {
        $op = strtolower($this->operator ?: '');
        if (! $op && $this->selected_schedule_id) {
            $sched = Schedule::with(['ferryRoute.operatorRecord'])->find($this->selected_schedule_id);
            $op = strtolower($sched?->ferryRoute?->operatorRecord?->name ?: ($sched?->ferryRoute?->operator ?: ($sched?->service_name ?: '')));
        }

        if (stripos($op, 'pal') !== false || stripos($op, 'philippine') !== false) {
            return 'pal';
        }
        if (stripos($op, 'airasia') !== false || stripos($op, 'air asia') !== false) {
            return 'airasia';
        }
        if (stripos($op, 'cebu') !== false || stripos($op, 'ceb') !== false || stripos($op, 'pacific') !== false) {
            return 'ceb_pac';
        }

        return 'ceb_pac';
    }

    public function selectBaggageOption(string $weight, float $price): void
    {
        $this->applyBaggageToAllPassengers($weight, $price);
    }

    public function setPassengerBaggage(int $index, ?string $weight = '', $price = 0): void
    {
        if (isset($this->passengers[$index])) {
            $this->passengers[$index]['extra_baggage_weight'] = $weight ?: '';
            $this->passengers[$index]['extra_baggage_price'] = $price ? floatval($price) : 0.0;
            $this->hasExtraBaggage = $this->getExtraBaggageTotalPrice() > 0;
            $this->saveDraft();
        }
    }

    public function applyBaggageToAllPassengers(string $weight, $price): void
    {
        foreach ($this->passengers as $idx => $pax) {
            $this->passengers[$idx]['extra_baggage_weight'] = $weight;
            $this->passengers[$idx]['extra_baggage_price'] = floatval($price);
        }
        $this->hasExtraBaggage = true;
        $this->extra_baggage_weight = $weight;
        $this->extra_baggage_price = floatval($price);
        $this->saveDraft();
    }

    public function clearAllBaggage(): void
    {
        foreach ($this->passengers as $idx => $pax) {
            $this->passengers[$idx]['extra_baggage_weight'] = '';
            $this->passengers[$idx]['extra_baggage_price'] = 0.0;
        }
        $this->hasExtraBaggage = false;
        $this->extra_baggage_weight = '';
        $this->extra_baggage_price = null;
        $this->extra_baggage_type = '';
        $this->extra_baggage_specify = '';
        $this->saveDraft();
    }

    public function updatedHasExtraBaggage($value): void
    {
        if ($value) {
            $this->baggage_trip_type = $this->autoDetectBaggageScope();
            if (! $this->selected_baggage_airline) {
                $this->selected_baggage_airline = $this->autoDetectBaggageAirline();
            }
            $rates = $this->getAirlineExtraBaggageRates();
            $key = $this->selected_baggage_airline;
            if (! empty($rates[$key]['options'])) {
                $first = $rates[$key]['options'][0];
                $this->applyBaggageToAllPassengers($first['weight'], floatval($first['price']));
            }
        } else {
            $this->clearAllBaggage();
        }
        $this->saveDraft();
    }

    public function updatedSelectedBaggageAirline($value): void
    {
        $this->updateBaggagePriceFromRates();
        $this->saveDraft();
    }

    public function getExtraBaggageTotalPrice(): float
    {
        $total = 0.0;
        foreach ($this->passengers as $pax) {
            if (! empty($pax['extra_baggage_price'])) {
                $total += floatval($pax['extra_baggage_price']);
            }
        }
        return $total;
    }

    public function getPassengersWithBaggageCount(): int
    {
        return collect($this->passengers)->filter(function ($pax) {
            return ! empty($pax['extra_baggage_weight']) && (float) ($pax['extra_baggage_price'] ?? 0) > 0;
        })->count();
    }

    public function getTotalBaggageWeightSummary(): string
    {
        $parts = [];
        foreach ($this->passengers as $idx => $pax) {
            if (! empty($pax['extra_baggage_weight']) && (float) ($pax['extra_baggage_price'] ?? 0) > 0) {
                $paxName = ! empty($pax['name']) ? $pax['name'] : 'Traveler #' . ($idx + 1);
                $parts[] = "{$paxName} ({$pax['extra_baggage_weight']})";
            }
        }
        return implode(', ', $parts);
    }

    public function toggleOriginDropdown(): void
    {
        $this->showOriginDropdown = ! $this->showOriginDropdown;

        if ($this->showOriginDropdown) {
            $this->showModeDropdown = false;
            $this->showOperatorDropdown = false;
            $this->showDestinationDropdown = false;
            $this->dispatch('dropdownOpened', name: 'origin');
        }

        if (! $this->showOriginDropdown) {
            $this->originSearch = '';
        }
    }

    public function toggleDestinationDropdown(): void
    {
        $this->showDestinationDropdown = ! $this->showDestinationDropdown;

        if ($this->showDestinationDropdown) {
            $this->showModeDropdown = false;
            $this->showOperatorDropdown = false;
            $this->showOriginDropdown = false;
            $this->dispatch('dropdownOpened', name: 'destination');
        }

        if (! $this->showDestinationDropdown) {
            $this->destinationSearch = '';
        }
    }

    public function selectOrigin(string $origin): void
    {
        $this->origin = $origin;
        $this->destination = '';
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->showOriginDropdown = false;
        $this->originSearch = '';
        $this->updateAvailableScheduleDates();
        $this->saveDraft();
    }

    public function selectDestination(string $destination): void
    {
        $this->destination = $destination;
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->showDestinationDropdown = false;
        $this->destinationSearch = '';
        $this->baggage_trip_type = $this->autoDetectBaggageScope();

        $this->updateAvailableScheduleDates();
        $this->saveDraft();
    }

    protected function updateAvailableScheduleDates(): void
    {
        if ($this->prefilled_from_package || $this->tour_id) {
            $this->available_schedule_dates = [];
            $this->available_return_schedule_dates = [];
            return;
        }

        $this->available_package_dates = [];

        if (empty($this->mode) || empty($this->origin) || empty($this->destination)) {
            $this->available_schedule_dates = [];
            $this->available_return_schedule_dates = [];
            return;
        }

        $departureDates = Schedule::active()
            ->where('departure_time', '>=', now())
            ->whereHas('ferryRoute', function ($query) {
                $query->where('origin', $this->origin)
                      ->where('destination', $this->destination)
                      ->where('mode', $this->mode)
                      ->where('is_active', true);

                if (! empty($this->operator)) {
                    $query->where(function ($q) {
                        $q->where('operator', $this->operator)
                          ->orWhere('operator', 'like', '%' . $this->operator . '%');
                    });
                }
            })
            ->selectRaw('DATE(departure_time) as date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->filter()
            ->values()
            ->all();

        $this->available_schedule_dates = $departureDates;

        // Only clear departure_date if it's not coming from a fresh URL pre-fill
        $urlDate = request()->query('departure_date');
        if ($this->departure_date && $this->departure_date !== $urlDate && ! in_array($this->departure_date, $this->available_schedule_dates, true)) {
            $this->departure_date = null;
        }

        // If departure_date came from the URL and is not yet in the available dates list
        // (could happen if operator filter is too strict), verify it exists for this route
        // without the operator filter and add it so the date picker can pre-select it.
        if ($urlDate && ! in_array($urlDate, $this->available_schedule_dates, true)
            && ! empty($this->origin) && ! empty($this->destination)) {
            $exists = Schedule::active()
                ->whereDate('departure_time', $urlDate)
                ->whereHas('ferryRoute', function ($q) {
                    $q->where('origin', $this->origin)
                      ->where('destination', $this->destination)
                      ->where('mode', $this->mode)
                      ->where('is_active', true);
                })
                ->exists();
            if ($exists) {
                $this->available_schedule_dates = array_unique(array_merge($this->available_schedule_dates, [$urlDate]));
                sort($this->available_schedule_dates);
            }
        }

        $returnDates = Schedule::active()
            ->where('departure_time', '>=', now())
            ->whereHas('ferryRoute', function ($query) {
                $query->where('origin', $this->destination)
                      ->where('destination', $this->origin)
                      ->where('mode', $this->mode)
                      ->where('is_active', true);

                if (! empty($this->operator)) {
                    $query->where(function ($q) {
                        $q->where('operator', $this->operator)
                          ->orWhere('operator', 'like', '%' . $this->operator . '%');
                    });
                }
            })
            ->selectRaw('DATE(departure_time) as date')
            ->distinct()
            ->orderBy('date')
            ->pluck('date')
            ->filter()
            ->values()
            ->all();

        $this->available_return_schedule_dates = $returnDates;

        if ($this->return_date && ! in_array($this->return_date, $this->available_return_schedule_dates, true)) {
            $this->return_date = null;
        }
    }

    public function updatedOriginSearch(): void
    {
        $this->showOriginDropdown = true;
        $this->showModeDropdown = false;
        $this->showDestinationDropdown = false;
        $this->dispatch('dropdownOpened', 'origin');
    }

    public function updatedDestinationSearch(): void
    {
        $this->showDestinationDropdown = true;
        $this->showModeDropdown = false;
        $this->showOriginDropdown = false;
        $this->dispatch('dropdownOpened', 'destination');
    }
    
    public function updatedOperator(): void
    {
        $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
        $this->availableSchedules = [];
        $this->updateAvailableScheduleDates();
        $this->saveDraft();
    }

    #[\Livewire\Attributes\On('datePickerUpdated')]
    public function datePickerUpdated($field = null, $value = null): void
    {
        if (is_array($field)) {
            $value = $field['value'] ?? ($field[1] ?? null);
            $field = $field['field'] ?? ($field[0] ?? null);
        }

        if (! in_array($field, ['departure_date', 'return_date'], true)) {
            return;
        }

        $this->$field = $value;

        if ($field === 'departure_date') {
            $this->selected_schedule_id = null;
            $this->selected_return_schedule_id = null;
            $this->availableSchedules = [];
            $this->updateReturnDateFromDuration();
            $this->updateAvailableScheduleDates();
        }

        try {
            $this->validateOnly($field, $this->allRules());
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Log/ignore partial step validation errors during date picking
        }

        $this->saveDraft();
    }

    public function hydrate(): void
    {
        $this->updateReturnDateFromDuration();
    }

    public function updated($propertyName): void
    {
        if ($propertyName === 'trip_type') {
            $this->saveDraft();
            return;
        }
        if ($propertyName === 'tour_date_id') {
            $this->selectedTourDate = $this->tour?->dates->firstWhere('id', $this->tour_date_id) ?: TourDate::find($this->tour_date_id);
            if ($this->selectedTourDate && $this->tour) {
                $this->departure_date = Carbon::parse($this->selectedTourDate->date)->format('Y-m-d');
                $this->return_date = Carbon::parse($this->selectedTourDate->date)->addDays($this->tour->duration_days)->format('Y-m-d');
            }
            $this->saveDraft();
            return;
        }
        if (str_starts_with($propertyName, 'selected_accommodation_ids')) {
            $this->saveDraft();

            return;
        }

        if (in_array($propertyName, ['adults', 'children', 'minors', 'infants', 'has_vehicle', 'selected_vehicle_rate_id', 'vehicle_type', 'vehicle_plate_number', 'vehicle_price', 'driver_first_name', 'driver_middle_name', 'driver_last_name', 'driver_birthday'], true)) {
            $this->syncPassengerEntries();
            $this->saveDraft();

            return;
        }

        if (in_array($propertyName, ['origin', 'destination', 'departure_date'], true)) {
            $this->selected_schedule_id = null;
        $this->selected_return_schedule_id = null;
            $this->availableSchedules = [];
        }

        if ($propertyName === 'origin') {
            $this->destination = '';
        }

        if ($propertyName === 'departure_date') {
            $this->updateReturnDateFromDuration();
        }

        if (str_starts_with($propertyName, 'passengers.')) {
            if (preg_match('/^passengers\.(\d+)\.(first_name|middle_name|last_name)$/', $propertyName, $matches)) {
                $this->syncFullPassengerNames();
            }

            if (preg_match('/^passengers\.(\d+)\.(passport_issuance_day|passport_issuance_month|passport_issuance_year)$/', $propertyName, $matches)) {
                $idx = (int) $matches[1];
                $d = $this->passengers[$idx]['passport_issuance_day'] ?? '';
                $m = $this->passengers[$idx]['passport_issuance_month'] ?? '';
                $y = $this->passengers[$idx]['passport_issuance_year'] ?? '';
                if ($d && $m && $y) {
                    $this->passengers[$idx]['passport_issuance_date'] = sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
                }
            }

            if (preg_match('/^passengers\.(\d+)\.(passport_expiry_day|passport_expiry_month|passport_expiry_year)$/', $propertyName, $matches)) {
                $idx = (int) $matches[1];
                $d = $this->passengers[$idx]['passport_expiry_day'] ?? '';
                $m = $this->passengers[$idx]['passport_expiry_month'] ?? '';
                $y = $this->passengers[$idx]['passport_expiry_year'] ?? '';
                if ($d && $m && $y) {
                    $this->passengers[$idx]['passport_expiry_date'] = sprintf('%04d-%02d-%02d', (int) $y, (int) $m, (int) $d);
                }
            }

            if (preg_match('/^passengers\.(\d+)\.discount_id$/', $propertyName, $matches)) {
                $discountId = $this->passengers[$matches[1]]['discount_id'] ?? null;
                $discount = $this->discounts->firstWhere('id', $discountId);

                if (! $this->hasSeenPresentIdWarning && $discount && preg_match('/student|senior|pwd/i', $discount->name)) {
                    $this->showPresentIdWarning = true;
                }
            }

            $this->saveDraft();

            return;
        }

        $this->saveDraft();
        try {
            $this->validateOnly($propertyName, $this->allRules());
        } catch (\Throwable $e) {
            // Ignore validation exception during typing
        }
    }

    public function closePresentIdWarning(): void
    {
        $this->showPresentIdWarning = false;
        $this->hasSeenPresentIdWarning = true;
    }

    public function nextStep(): void
    {
        $this->syncFullPassengerNames();
        $rules = $this->stepRules();

        if (! empty($rules)) {
            try {
                $this->validate($rules);
            } catch (\Illuminate\Validation\ValidationException $e) {
                $this->dispatch('validation-error');
                throw $e;
            }
        }

        if ($this->step === 1) {
            if (! $this->tour_id) {
                $this->availableSchedules = $this->getAvailableSchedules();
                $this->availableReturnSchedules = $this->getAvailableReturnSchedules();

                if (empty($this->availableSchedules)) {
                    $this->dispatch('validation-error');
                    throw \Illuminate\Validation\ValidationException::withMessages([
                        'departure_date' => 'No ferry schedules are available for this route on the selected date. (DEBUG: origin=' . $this->origin . ', dest=' . $this->destination . ', date=' . $this->departure_date . ', mode=' . $this->mode . ', operator=' . $this->operator . ')',
                    ]);
                }
                
                if ($this->trip_type === 'round_trip' && empty($this->availableReturnSchedules)) {
                    throw ValidationException::withMessages([
                        'return_date' => 'No return schedules are available for this route on the selected date. Try another date.',
                    ]);
                }
            }
            $this->syncPassengerEntries();
        }

        if ($this->step === 2 && ! $this->tour_id) {
            $this->assertSelectedScheduleIsValid();
        }

        if ($this->step === 3) {
            $this->validatePassengerExtras();
        }

        if ($this->step < 5) {
            $this->step++;
            if (($this->tour_id || $this->prefilled_from_package) && $this->step === 2) {
                $this->step = 3;
            }
        }

        $this->saveDraft();
    }

    public function previousStep(): void
    {
        if ($this->step > 1) {
            $this->step--;
            if (($this->tour_id || $this->prefilled_from_package) && $this->step === 2) {
                $this->step = 1;
            }
            $this->saveDraft();
        }
    }

    public function selectSchedule(int $scheduleId): void
    {
        $this->selected_schedule_id = $scheduleId;
        $this->selected_transport_class_id = null;
        $this->baggage_trip_type = $this->autoDetectBaggageScope();
        $this->selected_baggage_airline = $this->autoDetectBaggageAirline();
        $this->updateBaggagePriceFromRates();
        $this->saveDraft();
    }
    
    public function selectReturnSchedule(int $scheduleId): void
    {
        $this->selected_return_schedule_id = $scheduleId;
        $this->saveDraft();
    }

    protected function getAvailableSchedules(): array
    {
        return Schedule::query()
            ->with(['ferryRoute', 'transportClasses', 'scheduleAccommodations'])
            ->forRouteAndDate($this->origin, $this->destination, $this->departure_date, $this->mode, $this->operator)
            ->get()
            ->map(fn (Schedule $schedule) => $schedule->toBookingArray($this->departure_date, []))
            ->values()
            ->all();
    }

    protected function getAvailableReturnSchedules(): array
    {
        if ($this->trip_type !== 'round_trip' || !$this->return_date) {
            return [];
        }

        return Schedule::query()
            ->with(['ferryRoute', 'transportClasses', 'scheduleAccommodations'])
            // Reverse origin and destination for return trip
            ->forRouteAndDate($this->destination, $this->origin, $this->return_date, $this->mode, $this->operator)
            ->get()
            ->map(fn (Schedule $schedule) => $schedule->toBookingArray($this->return_date, []))
            ->values()
            ->all();
    }

    /**
     * Build (or resize) the per-passenger entries based on the adult/child
     * counts entered in step 3, preserving names/discounts already typed in for
     * passengers that still exist after a count change.
     */
    protected function syncPassengerEntries(): void
    {
        $existingByType = collect($this->passengers)->groupBy('type');

        $driverCount = ($this->operator === 'Starlite' && $this->has_vehicle) ? 1 : 0;
        $adultCount = max(0, $this->adults - $driverCount);

        $rebuilt = [];

        $types = [];
        if ($driverCount > 0) {
            $types['driver'] = $driverCount;
        }
        if ($adultCount > 0) {
            $types['adult'] = $adultCount;
        }
        if (strtolower($this->mode) === 'airline') {
            if ($this->minors > 0) {
                $types['minor'] = $this->minors;
            }
            if ($this->children > 0) {
                $types['child'] = $this->children;
            }
            if ($this->infants > 0) {
                $types['infant'] = $this->infants;
            }
        } else {
            // For Ferry, combine minors/children into 'child' (or whatever they were before)
            if ($this->children > 0) {
                $types['child'] = $this->children;
            }
            if ($this->infants > 0) {
                $types['infant'] = $this->infants;
            }
        }

        // Helper to grab existing from either driver or adult if they toggle has_vehicle
        $getExisting = function($t, $index) use ($existingByType) {
            $pool = $existingByType->get($t, collect())->values();
            if ($pool->has($index)) return $pool->get($index);
            // Fallback for switching between adult and driver
            if ($t === 'driver') return $existingByType->get('adult', collect())->values()->get(0);
            if ($t === 'adult' && $index === 0) return $existingByType->get('driver', collect())->values()->get(0);
            return null;
        };

        foreach ($types as $type => $count) {
            for ($i = 0; $i < $count; $i++) {
                $existing = $getExisting($type, $i) ?? [];
                
                $passenger = array_merge([
                    'type' => $type,
                    'name' => '',
                    'first_name' => '',
                    'middle_name' => '',
                    'last_name' => '',
                    'discount_id' => null,
                    'use_promo' => false,
                    'promo_cleared_discount' => false,
                ], $existing);

                if ($type === 'driver') {
                    $passenger['first_name'] = $this->driver_first_name ?? '';
                    $passenger['middle_name'] = $this->driver_middle_name ?? '';
                    $passenger['last_name'] = $this->driver_last_name ?? '';
                    $passenger['birthdate'] = $this->driver_birthday ?? '';
                }

                $issDate = $passenger['passport_issuance_date'] ?? '';
                $expDate = $passenger['passport_expiry_date'] ?? '';
                $issParts = $issDate ? explode('-', $issDate) : [];
                $expParts = $expDate ? explode('-', $expDate) : [];

                $nameParts = $this->passengerNameParts($passenger);

                $rebuilt[] = array_merge([
                    'type' => $type,
                    'name' => $passenger['name'] ?? '',
                    'first_name' => $nameParts['first_name'],
                    'middle_name' => $nameParts['middle_name'],
                    'last_name' => $nameParts['last_name'],
                    'discount_id' => $passenger['discount_id'] ?? null,
                    'use_promo' => $passenger['use_promo'] ?? false,
                    'promo_cleared_discount' => $passenger['promo_cleared_discount'] ?? false,
                    'passport_country' => $passenger['passport_country'] ?? '',
                    'passport_number' => $passenger['passport_number'] ?? '',
                    'passport_issuance_date' => $issDate,
                    'passport_expiry_date' => $expDate,
                    'passport_issuance_day' => $passenger['passport_issuance_day'] ?? ($issParts[2] ?? ''),
                    'passport_issuance_month' => $passenger['passport_issuance_month'] ?? ($issParts[1] ?? ''),
                    'passport_issuance_year' => $passenger['passport_issuance_year'] ?? ($issParts[0] ?? ''),
                    'passport_expiry_day' => $passenger['passport_expiry_day'] ?? ($expParts[2] ?? ''),
                    'passport_expiry_month' => $passenger['passport_expiry_month'] ?? ($expParts[1] ?? ''),
                    'passport_expiry_year' => $passenger['passport_expiry_year'] ?? ($expParts[0] ?? ''),
                    'extra_baggage_weight' => $passenger['extra_baggage_weight'] ?? '',
                    'extra_baggage_price' => isset($passenger['extra_baggage_price']) ? floatval($passenger['extra_baggage_price']) : 0.0,
                ], $passenger);
            }
        }

        $this->passengers = $rebuilt;
        $this->syncFullPassengerNames();
    }

    protected function passengerNameParts(array $passenger): array
    {
        $first = trim($passenger['first_name'] ?? '');
        $middle = trim($passenger['middle_name'] ?? '');
        $last = trim($passenger['last_name'] ?? '');

        if ($first === '' && $middle === '' && $last === '' && ! empty($passenger['name'])) {
            $words = preg_split('/\s+/', trim($passenger['name']));
            $first = $words[0] ?? '';

            if (count($words) === 1) {
                $last = '';
            } elseif (count($words) === 2) {
                $last = $words[1];
            } else {
                $last = array_pop($words);
                array_shift($words);
                $middle = trim(implode(' ', $words));
            }
        }

        return [
            'first_name' => $first,
            'middle_name' => $middle,
            'last_name' => $last,
        ];
    }

    protected function syncFullPassengerNames(): void
    {
        foreach ($this->passengers as $index => $passenger) {
            $first = trim($passenger['first_name'] ?? '');
            $middle = trim($passenger['middle_name'] ?? '');
            $last = trim($passenger['last_name'] ?? '');

            $this->passengers[$index]['name'] = trim(implode(' ', array_filter([$first, $middle, $last], fn ($value) => $value !== '')));
        }
    }

    public function updatedHasVehicle(bool $value): void
    {
        if (! $value) {
            $this->selected_vehicle_rate_id = null;
            $this->selected_brand_id = null;
            $this->selected_model_id = null;
            $this->vehicle_type = '';
            $this->vehicle_plate_number = '';
            $this->vehicle_price = null;
        }

        $this->saveDraft();
    }

    public function updatedUsePromoTicket(bool $value): void
    {
        // Ferry-mode booking-level toggle — unchanged behavior
        $this->saveDraft();
    }

    // ─── Per-passenger promo helpers (airline mode only) ─────────────────────

    public function togglePassengerPromo(int $index): void
    {
        if ($this->mode !== 'airline') {
            return;
        }

        $passenger = $this->passengers[$index] ?? null;
        if ($passenger === null) {
            return;
        }

        $currentlyOn = (bool) ($passenger['use_promo'] ?? false);

        if ($currentlyOn) {
            // Disable promo for this passenger
            $this->passengers[$index]['use_promo'] = false;
            $this->passengers[$index]['promo_cleared_discount'] = false;
        } else {
            // Enable promo — first check there are slots remaining
            if ($this->getAvailablePromoSlotsRemaining() <= 0) {
                return; // No slots left — silently refuse
            }

            $this->passengers[$index]['use_promo'] = true;

            // Auto-clear any discount that was selected
            if (! empty($passenger['discount_id'])) {
                $this->passengers[$index]['discount_id'] = null;
                $this->passengers[$index]['promo_cleared_discount'] = true;
            } else {
                $this->passengers[$index]['promo_cleared_discount'] = false;
            }
        }

        $this->saveDraft();
    }

    public function getSelectedPromoPassengerCount(): int
    {
        return collect($this->passengers)->filter(fn ($p) => ! empty($p['use_promo']))->count();
    }

    public function getAvailablePromoSlotsRemaining(): int
    {
        $promoTicket = $this->getActivePromoTicket();
        if (! $promoTicket) {
            return 0;
        }

        return max(0, $promoTicket->remaining_quantity - $this->getSelectedPromoPassengerCount());
    }

    public function updatedVehicleBookingMethod(string $value): void
    {
        if ($value === 'category') {
            $this->selected_brand_id = null;
            $this->selected_model_id = null;
            if ($this->selected_vehicle_rate_id) {
                $this->updatedSelectedVehicleRateId($this->selected_vehicle_rate_id);
            } else {
                $this->vehicle_type = '';
                $this->vehicle_price = null;
            }
        }

        if ($value === 'brand_model') {
            $this->selected_vehicle_rate_id = null;
            $this->vehicle_type = '';
            $this->vehicle_price = null;
        }

        $this->saveDraft();
    }

    public function updatedSelectedBrandId($value): void
    {
        if (blank($value)) {
            $this->selected_model_id = null;
            $this->vehicle_type = '';
            $this->vehicle_price = null;
            $this->saveDraft();
            return;
        }

        $this->selected_model_id = null;
        $this->vehicle_type = '';
        $this->vehicle_price = null;
        $this->saveDraft();
    }

    public function updatedSelectedModelId($value): void
    {
        if (blank($value)) {
            $this->vehicle_type = '';
            $this->vehicle_price = null;
            $this->saveDraft();
            return;
        }

        $model = $this->vehicleModelCatalog->firstWhere('id', (int) $value);
        $brandName = $this->vehicleBrandCatalog->firstWhere('id', (int) $this->selected_brand_id)?->name;

        if ($model) {
            $this->vehicle_type = trim(($brandName ? $brandName . ' ' : '') . $model->name);
            $this->vehicle_price = floatval($model->price);
        }

        $this->saveDraft();
    }

    public function updatedSelectedVehicleRateId($value): void
    {
        if (blank($value)) {
            $this->vehicle_type = '';
            $this->vehicle_price = null;

            return;
        }

        $rate = $this->vehicleRateCatalog->firstWhere('id', (int) $value);

        if ($rate) {
            $this->vehicle_type = $rate->name;
            $this->vehicle_price = floatval($rate->price);
        }

        $this->saveDraft();
    }

    public function selectScheduleAccommodation(int $accommodationId): void
    {
        if ($this->selected_schedule_accommodation_id === $accommodationId) {
            $this->selected_schedule_accommodation_id = null;
        } else {
            $this->selected_schedule_accommodation_id = $accommodationId;
        }
        $this->saveDraft();
    }

    public function selectReturnScheduleAccommodation(int $accommodationId): void
    {
        if ($this->selected_return_schedule_accommodation_id === $accommodationId) {
            $this->selected_return_schedule_accommodation_id = null;
        } else {
            $this->selected_return_schedule_accommodation_id = $accommodationId;
        }
        $this->saveDraft();
    }

    public function selectTransportClass(?int $classId): void
    {
        $this->selected_transport_class_id = $this->selected_transport_class_id === $classId ? null : $classId;
        $this->saveDraft();
    }

    public function selectReturnTransportClass(?int $classId): void
    {
        $this->selected_return_transport_class_id = $this->selected_return_transport_class_id === $classId ? null : $classId;
        $this->saveDraft();
    }


    public function submit()
    {
        try {
            $this->syncFullPassengerNames();

            $this->validate([
                'client_name' => 'required|string|max:255',
                'client_email' => 'required|email',
                'client_phone' => 'required|string|max:25',
            ]);

            $this->validatePassengerExtras();
            $this->assertSelectedScheduleIsValid();

            $this->showTermsAgreementWarning = false;
            $this->showPrivacyAgreementWarning = false;

            if (! $this->hasAcceptedTerms) {
                $this->showTermsModal = true;
                $this->showPrivacyModal = false;
                $this->showTermsAgreementWarning = true;
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'You need to read and agree to continue.',
                ]);
                return;
            }

            if (! $this->hasAcceptedPrivacy) {
                $this->showTermsModal = false;
                $this->showPrivacyModal = true;
                $this->showPrivacyAgreementWarning = true;
                $this->dispatch('notify', [
                    'type' => 'warning',
                    'message' => 'You need to read and agree to continue.',
                ]);
                return;
            }
        } catch (ValidationException $e) {
            $this->dispatch('validation-error');
            $this->dispatch('notify', [
                'type' => 'error',
                'message' => 'Please review the highlighted fields and try again.',
            ]);
            throw $e;
        } catch (\Throwable $e) {
            Log::error('submit() pre-flight error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Could not validate booking: ' . $e->getMessage(),
            ]);
        }

        $lockKey = 'booking_submit_lock_' . $this->getId();
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            return; // Silently ignore duplicate clicks while processing
        }

        $success = false;
        try {
            $this->isSubmittingBooking = true;
            $transaction = $this->processBookingInternal();
            if (! $transaction) {
                $this->isSubmittingBooking = false;
                throw ValidationException::withMessages([
                    'step' => 'Booking could not be processed at this time. Please try again.',
                ]);
            }
            $this->isSubmittingBooking = false;
            $success = true;
            $this->redirect(route('payment.show', $transaction), navigate: false);
            return null;
        } catch (ValidationException $e) {
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw $e;
        } catch (\Throwable $e) {
            Log::error('submit() processBooking fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Booking failed to save. Please review and try again. Error: ' . $e->getMessage(),
            ]);
        } finally {
            if (! $success) {
                $lock->release();
            }
        }
    }

    public function confirmTermsAndContinue()
    {
        $this->showTermsAgreementWarning = false;
        $this->showPrivacyAgreementWarning = false;

        if (! $this->hasAcceptedTerms) {
            $this->showTermsAgreementWarning = true;
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'You need to read and agree to continue.',
            ]);
            return;
        }

        $this->showTermsModal = false;

        if (! $this->hasAcceptedPrivacy) {
            $this->showPrivacyModal = true;
            return;
        }

        $lockKey = 'booking_submit_lock_' . $this->getId();
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            return; // Silently ignore duplicate clicks while processing
        }

        $success = false;
        try {
            $this->isSubmittingBooking = true;
            $transaction = $this->processBookingInternal();
            if (! $transaction) {
                $this->isSubmittingBooking = false;
                throw ValidationException::withMessages([
                    'step' => 'Booking could not be processed at this time. Please try again.',
                ]);
            }
            $this->isSubmittingBooking = false;
            $success = true;
            $this->redirect(route('payment.show', $transaction), navigate: false);
            return null;
        } catch (ValidationException $e) {
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw $e;
        } catch (\Throwable $e) {
            Log::error('confirmTermsAndContinue fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Booking failed to save: ' . $e->getMessage(),
            ]);
        } finally {
            if (! $success) {
                $lock->release();
            }
        }
    }

    protected function processBookingInternal(): ?Transaction
    {
        $this->syncFullPassengerNames();
        $this->showTermsModal = false;
        $this->showPrivacyModal = false;
        session()->forget('booking_draft');

        if ($this->tour_id && $this->tour) {
            $schedule = null;
            $scheduleAccommodation = null;
            $returnSchedule = null;
            $returnScheduleAccommodation = null;
        } else {
            try {
                $schedule = Schedule::query()
                    ->forRouteAndDate($this->origin, $this->destination, $this->departure_date, $this->mode)
                    ->findOrFail($this->selected_schedule_id);
            } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
                $this->dispatch('validation-error');
                throw ValidationException::withMessages([
                    'selected_schedule_id' => 'The selected schedule is no longer available. Please review and try again.',
                ]);
            }

            $scheduleAccommodation = $this->selected_schedule_accommodation_id
                ? ScheduleAccommodation::find($this->selected_schedule_accommodation_id)
                : null;

            $returnSchedule = $this->selected_return_schedule_id
                ? Schedule::find($this->selected_return_schedule_id)
                : null;

            $returnScheduleAccommodation = $this->selected_return_schedule_accommodation_id
                ? ScheduleAccommodation::find($this->selected_return_schedule_accommodation_id)
                : null;
        }

        $transaction = null;
        $booking = null;

        try {
            DB::transaction(function () use (&$transaction, &$booking, $schedule, $scheduleAccommodation, $returnSchedule, $returnScheduleAccommodation) {
                $usedPromoTicket = null;
                $promoTicketCount = 0;

                if (strtolower($this->mode) === 'airline' && $schedule) {
                    $promoPassengerIndices = collect($this->passengers)
                        ->keys()
                        ->filter(fn ($i) => ! empty($this->passengers[$i]['use_promo']))
                        ->values();

                    $promoTicketCount = $promoPassengerIndices->count();

                    if ($promoTicketCount > 0) {
                        $usedPromoTicket = $schedule->promotionalTickets()
                            ->activeAndAvailable()
                            ->lockForUpdate()
                            ->first();

                        if (! $usedPromoTicket || $usedPromoTicket->remaining_quantity < $promoTicketCount) {
                            throw new \RuntimeException(
                                'Not enough promotional ticket slots remaining. Please review your selection and try again.'
                            );
                        }

                        $usedPromoTicket->increment('quantity_sold', $promoTicketCount);
                    }
                } elseif ($this->use_promo_ticket && $schedule) {
                    $usedPromoTicket = $schedule->promotionalTickets()
                        ->activeAndAvailable()
                        ->lockForUpdate()
                        ->first();

                    if ($usedPromoTicket && $usedPromoTicket->quantity_sold < $usedPromoTicket->quantity_available) {
                        $usedPromoTicket->increment('quantity_sold');
                        $promoTicketCount = 1;
                    } else {
                        $this->use_promo_ticket = false;
                        $usedPromoTicket = null;
                    }
                }

                $usedSchedulePrice = $this->getSelectedSchedulePrice();

                // --- Decrement tickets_available (pessimistic lock to prevent overselling) ---
                if ($scheduleAccommodation) {
                    $lockedAccom = ScheduleAccommodation::where('id', $scheduleAccommodation->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedAccom || $lockedAccom->tickets_available <= 0) {
                        throw new \RuntimeException(
                            'Sorry, this accommodation is now fully booked. Please choose another option.'
                        );
                    }

                    $lockedAccom->decrement('tickets_available');
                }

                if ($returnScheduleAccommodation) {
                    $lockedReturnAccom = ScheduleAccommodation::where('id', $returnScheduleAccommodation->id)
                        ->lockForUpdate()
                        ->first();

                    if (! $lockedReturnAccom || $lockedReturnAccom->tickets_available <= 0) {
                        throw new \RuntimeException(
                            'Sorry, the return trip accommodation is now fully booked. Please choose another option.'
                        );
                    }

                    $lockedReturnAccom->decrement('tickets_available');
                }

                $termsVersion = 'amiga-terms-2026-07-24';
                $termsAcceptedAt = now();
                $termsAcceptedIp = request()->ip();
                $termsAcceptedUserAgent = request()->userAgent();

                $booking = Booking::create([
                    'user_id' => auth()->check() ? auth()->id() : null,
                    'transaction_number' => $this->generateTransactionNumber(),
                    'origin' => $this->origin,
                    'destination' => $this->destination,
                    'departure_date' => $this->departure_date,
                    'return_date' => $this->return_date,
                    'schedule_id' => $schedule?->id,
                    'schedule_service' => $schedule?->service_name,
                    'schedule_departure_time' => $schedule?->formatted_departure,
                    'schedule_arrival_time' => $schedule?->formatted_arrival,
                    'schedule_price' => $usedSchedulePrice,
                    'schedule_accommodation_id' => $scheduleAccommodation?->id,
                    'schedule_accommodation_name' => $scheduleAccommodation?->name,
                    'schedule_accommodation_price' => $scheduleAccommodation?->price,
                    'schedule_accommodation_rate_code' => $scheduleAccommodation?->rate_code,
                    'return_schedule_id' => $returnSchedule?->id,
                    'return_schedule_service' => $returnSchedule?->service_name,
                    'return_schedule_departure_time' => $returnSchedule?->formatted_departure,
                    'return_schedule_arrival_time' => $returnSchedule?->formatted_arrival,
                    'return_schedule_price' => $returnSchedule?->price,
                    'return_schedule_accommodation_id' => $returnScheduleAccommodation?->id,
                    'return_schedule_accommodation_name' => $returnScheduleAccommodation?->name,
                    'return_schedule_accommodation_price' => $returnScheduleAccommodation?->price,
                    'return_schedule_accommodation_rate_code' => $returnScheduleAccommodation?->rate_code,
                    'tour_id' => $this->tour_id,
                    'tour_date_id' => $this->tour_date_id,
                    'tour_inclusions' => $this->tour?->inclusions,
                    'client_name' => $this->client_name,
                    'client_email' => $this->client_email,
                    'client_phone' => $this->client_phone,
                    'total_price' => $this->calculateTotalPrice(),
                    'status' => 'pending',
                    'has_vehicle' => $this->has_vehicle,
                    'vehicle_type' => $this->vehicle_type,
                    'vehicle_plate_number' => $this->vehicle_plate_number,
                    'vehicle_price' => $this->vehicle_price,
                    'has_extra_baggage' => $this->getExtraBaggageTotalPrice() > 0,
                    'extra_baggage_weight' => $this->getTotalBaggageWeightSummary(),
                    'extra_baggage_price' => $this->getExtraBaggageTotalPrice(),
                    'driver_name' => trim(trim($this->driver_first_name) . ' ' . trim($this->driver_middle_name) . ' ' . trim($this->driver_last_name)),
                    'driver_birthday' => $this->driver_birthday,
                    'promotional_ticket_id' => $usedPromoTicket?->id,
                    'promo_ticket_count' => $promoTicketCount,
                    'terms_accepted_at' => $termsAcceptedAt,
                    'terms_version' => $termsVersion,
                    'terms_accepted_ip' => $termsAcceptedIp,
                    'terms_accepted_user_agent' => $termsAcceptedUserAgent,
                ]);

                $settings = PaymentSetting::current();
                $isShortHaul = $schedule ? (bool) ($schedule->is_short_haul ?? false) : false;
                $webAdminFeePerPax = (float) $settings->getWebAdminFee($isShortHaul);
                $txFeePerPax = (float) $settings->getTransactionFee($isShortHaul);

                $depTcPrice = 0.0;
                if ($this->selected_transport_class_id && $schedule) {
                    $stc = \App\Models\ScheduleTransportClass::where('schedule_id', $schedule->id)
                        ->where('transport_class_id', $this->selected_transport_class_id)
                        ->first();
                    $depTcPrice = (float) ($stc?->additional_price ?? $stc?->transportClass?->price ?? 0);
                }

                $retTcPrice = 0.0;
                if ($this->selected_return_transport_class_id && $returnSchedule) {
                    $rstc = \App\Models\ScheduleTransportClass::where('schedule_id', $returnSchedule->id)
                        ->where('transport_class_id', $this->selected_return_transport_class_id)
                        ->first();
                    $retTcPrice = (float) ($rstc?->additional_price ?? $rstc?->transportClass?->price ?? 0);
                }

                $schedBasePrice = (float) ($usedSchedulePrice ?? $schedule?->price ?? 0);
                $schedAccPrice = $scheduleAccommodation ? (float) $scheduleAccommodation->price : 0.0;
                $retBasePrice = $returnSchedule ? (float) ($returnSchedule->price ?? 0) : 0.0;
                $retAccPrice = $returnScheduleAccommodation ? (float) $returnScheduleAccommodation->price : 0.0;

                $discountsKeyed = \App\Models\Discount::all()->keyBy('id');

                foreach ($this->passengers as $idx => $passenger) {
                    $isPromo = strtolower($this->mode) === 'airline' && ! empty($passenger['use_promo']) && $usedPromoTicket;
                    $itemNum = $idx + 1;

                    $pType = strtolower($passenger['type'] ?? 'adult');
                    $isAirline = strtolower($this->mode) === 'airline';
                    $paxMultiplier = 1.0;
                    if ($isAirline) {
                        if (in_array($pType, ['minor', 'child', 'infant'], true)) {
                            $paxMultiplier = 0.5;
                        }
                    } else {
                        // Ferry
                        if (in_array($pType, ['child', 'minor'], true)) {
                            $paxMultiplier = 0.5;
                        }
                    }

                    if ($isPromo) {
                        $grossFare = floatval($usedPromoTicket->promo_price);
                        $grossAcc = 0.0;
                        $discAmount = 0.0;
                    } else {
                        $grossFare = ($schedBasePrice + $depTcPrice + $retBasePrice + $retTcPrice) * $paxMultiplier;
                        $grossAcc = $schedAccPrice + $retAccPrice;
                        $discAmount = 0.0;
                        $hasDiscount = !empty($passenger['discount_id']) && !($isAirline && $pType === 'infant');
                        if ($hasDiscount) {
                            $disc = $discountsKeyed->get($passenger['discount_id']);
                            if ($disc) {
                                $discAmount = ($grossFare + $grossAcc) * ((float) $disc->percentage / 100);
                            }
                        }
                    }
                    $extraBaggagePricePax = isset($passenger['extra_baggage_price']) ? floatval($passenger['extra_baggage_price']) : 0.0;
                    $itemTotal = max(0, ($grossFare + $grossAcc) - $discAmount + $webAdminFeePerPax + $txFeePerPax + $extraBaggagePricePax);

                    Passenger::create([
                        'booking_id'             => $booking->id,
                        'item_number'            => $itemNum,
                        'ticket_number'          => $booking->transaction_number . '-' . $itemNum,
                        'status'                 => 'pending',
                        'type'                   => $passenger['type'],
                        'name'                   => $passenger['name'] ?: null,
                        'birthdate'              => !empty($passenger['birthdate']) ? $passenger['birthdate'] : null,
                        'discount_id'            => ($isPromo || ($isAirline && $pType === 'infant')) ? null : ($passenger['discount_id'] ?: null),
                        'promotional_ticket_id'  => $isPromo ? $usedPromoTicket->id : null,
                        'is_promo'               => $isPromo,
                        'promo_price'            => $isPromo ? floatval($usedPromoTicket->promo_price) : null,
                        'passport_country'       => !empty($passenger['passport_country']) ? $passenger['passport_country'] : null,
                        'passport_number'        => !empty($passenger['passport_number']) ? $passenger['passport_number'] : null,
                        'passport_issuance_date' => !empty($passenger['passport_issuance_date']) ? $passenger['passport_issuance_date'] : null,
                        'passport_expiry_date'   => !empty($passenger['passport_expiry_date']) ? $passenger['passport_expiry_date'] : null,
                        'extra_baggage_weight'   => !empty($passenger['extra_baggage_weight']) ? $passenger['extra_baggage_weight'] : null,
                        'extra_baggage_price'    => $extraBaggagePricePax,
                        'fare_amount'            => $grossFare,
                        'accommodation_amount'   => $grossAcc,
                        'discount_amount'        => $discAmount,
                        'voucher_discount_share' => 0,
                        'points_discount_share'  => 0,
                        'web_admin_fee_share'    => $webAdminFeePerPax,
                        'transaction_fee_share'  => $txFeePerPax,
                        'item_total'             => $itemTotal,
                    ]);
                }

                if ($this->selected_transport_class_id) {
                    $scheduleTransportClass = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_transport_class_id);
                    if ($scheduleTransportClass && $scheduleTransportClass->transportClass) {
                        $price = $scheduleTransportClass->additional_price ?? $scheduleTransportClass->transportClass->price;
                        $booking->transportClasses()->attach($scheduleTransportClass->transport_class_id, [
                            'price' => $price,
                            'is_promo' => $scheduleTransportClass->is_promo,
                            'rate_code' => $scheduleTransportClass->rate_code,
                            'is_return' => false,
                        ]);
                    }
                }

                if ($this->selected_return_transport_class_id) {
                    $returnScheduleTransportClass = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_return_transport_class_id);
                    if ($returnScheduleTransportClass && $returnScheduleTransportClass->transportClass) {
                        $price = $returnScheduleTransportClass->additional_price ?? $returnScheduleTransportClass->transportClass->price;
                        $booking->transportClasses()->attach($returnScheduleTransportClass->transport_class_id, [
                            'price' => $price,
                            'is_promo' => $returnScheduleTransportClass->is_promo,
                            'rate_code' => $returnScheduleTransportClass->rate_code,
                            'is_return' => true,
                        ]);
                    }
                }

                if ($this->selected_hotel_id) {
                    $hotel = Accommodation::query()->find($this->selected_hotel_id);
                    if ($hotel) {
                        $booking->accommodations()->attach($hotel->id, [
                            'price' => $hotel->price,
                        ]);
                    }
                }

                $transaction = Transaction::create([
                    'booking_id' => $booking->id,
                    'payment_status' => 'unpaid',
                ]);

                $studentProofEntries = $this->collectStudentDiscountProofEntries();
                if (! empty($studentProofEntries)) {
                    $frontFiles = [];
                    $backFiles = [];
                    $passengerData = [];

                    foreach ($studentProofEntries as $index => $entry) {
                        $frontFiles[$index] = $entry['front'] ?? null;
                        $backFiles[$index] = $entry['back'] ?? null;
                        $passengerData[$index] = [
                            'name' => $entry['passenger_name'] ?? null,
                            'student_number' => $entry['student_number'] ?? null,
                            'discount_name' => $entry['discount_name'] ?? null,
                        ];
                    }

                    $transaction->storeStudentDiscountProofs($frontFiles, $backFiles, $passengerData);
                }
            });
        } catch (\RuntimeException $e) {
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'selected_schedule_id' => $e->getMessage(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Booking creation failed in DB transaction', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'client_email' => $this->client_email ?? null,
            ]);
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Booking could not be saved. Please review your details and try again. (' . $e->getMessage() . ')',
            ]);
        }

        if (! $booking || ! $transaction) {
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Booking could not be created. Please try again.',
            ]);
        }

        $booking->load('passengers.discount', 'scheduleAccommodation', 'transportClasses', 'transaction', 'schedule');

        // Bust the schedule search cache so the public Schedules page shows updated ticket counts immediately
        \App\Actions\Bookings\CreateBookingAction::bustScheduleCache(
            $booking->schedule,
            $booking->returnSchedule ?? null
        );

        \App\Jobs\SendBookingConfirmationJob::dispatch($booking);

        return $transaction;
    }

    public function confirmPrivacyAndContinue()
    {
        $this->showTermsAgreementWarning = false;
        $this->showPrivacyAgreementWarning = false;

        if (! $this->hasAcceptedPrivacy) {
            $this->showPrivacyAgreementWarning = true;
            $this->dispatch('notify', [
                'type' => 'warning',
                'message' => 'You need to read and agree to continue.',
            ]);
            return;
        }

        $this->showPrivacyModal = false;

        if (! $this->hasAcceptedTerms) {
            $this->showTermsModal = true;
            return;
        }

        $lockKey = 'booking_submit_lock_' . $this->getId();
        $lock = \Illuminate\Support\Facades\Cache::lock($lockKey, 60);

        if (! $lock->get()) {
            return; // Silently ignore duplicate clicks while processing
        }

        $success = false;
        try {
            $this->isSubmittingBooking = true;
            $transaction = $this->processBookingInternal();
            if (! $transaction) {
                $this->isSubmittingBooking = false;
                throw ValidationException::withMessages([
                    'step' => 'Booking could not be processed at this time. Please try again.',
                ]);
            }
            $this->isSubmittingBooking = false;
            $success = true;
            $this->redirect(route('payment.show', $transaction), navigate: false);
            return null;
        } catch (ValidationException $e) {
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw $e;
        } catch (\Throwable $e) {
            Log::error('confirmPrivacyAndContinue fatal', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            $this->isSubmittingBooking = false;
            $this->dispatch('validation-error');
            throw ValidationException::withMessages([
                'step' => 'Booking failed to save: ' . $e->getMessage(),
            ]);
        } finally {
            if (! $success) {
                $lock->release();
            }
        }
    }

    public function cancelTermsModal()
    {
        $this->showTermsModal = false;
        $this->hasAcceptedTerms = false;
        $this->showTermsAgreementWarning = false;
    }

    public function cancelPrivacyModal()
    {
        $this->showPrivacyModal = false;
        $this->hasAcceptedPrivacy = false;
        $this->showPrivacyAgreementWarning = false;
    }

    #[Computed]
    public function discounts()
    {
        $items = \Illuminate\Support\Facades\Cache::remember('catalog:discounts_v3', now()->addHours(6), function () {
            return Discount::all()->sortBy('name')->values()->toArray();
        });

        return Discount::hydrate($items);
    }

    #[Computed]
    public function transportClassCatalog()
    {
        $items = \Illuminate\Support\Facades\Cache::remember('catalog:transport_classes_v3', now()->addHours(6), function () {
            return TransportClass::query()->where('is_active', true)->orderBy('name')->get()->toArray();
        });

        return TransportClass::hydrate($items);
    }

    #[Computed]
    public function vehicleRateCatalog()
    {
        $items = \Illuminate\Support\Facades\Cache::remember('api:vehicle_rates_v3', now()->addHours(6), function () {
            return VehicleRate::query()->where('is_active', true)->orderBy('sort_order')->get()->toArray();
        });

        return VehicleRate::hydrate($items);
    }

    #[Computed]
    public function vehicleBrandCatalog()
    {
        $items = \Illuminate\Support\Facades\Cache::remember('catalog:vehicle_brands_v3', now()->addHours(6), function () {
            return VehicleBrand::query()->where('is_active', true)->orderBy('sort_order')->get()->toArray();
        });

        return VehicleBrand::hydrate($items);
    }

    #[Computed]
    public function vehicleModelCatalog()
    {
        if ($this->selected_brand_id) {
            $items = \Illuminate\Support\Facades\Cache::remember('catalog:vehicle_models_v3:' . (int) $this->selected_brand_id, now()->addHours(6), function () {
                return VehicleModel::query()
                    ->where('vehicle_brand_id', (int) $this->selected_brand_id)
                    ->where('is_active', true)
                    ->orderBy('sort_order')
                    ->get()
                    ->toArray();
            });

            return VehicleModel::hydrate($items);
        }

        return collect();
    }

    #[Computed]
    public function accommodationCatalog()
    {
        $items = \Illuminate\Support\Facades\Cache::remember('api:accommodations_v3', now()->addHours(6), function () {
            return Accommodation::query()->where('is_active', true)->orderBy('name')->get()->toArray();
        });

        return Accommodation::hydrate($items);
    }

    public function render()
    {
        return view('livewire.booking-form', [
            'discounts' => $this->discounts,
            'transportClassCatalog' => $this->transportClassCatalog,
            'vehicleRateCatalog' => $this->vehicleRateCatalog,
            'vehicleBrandCatalog' => $this->vehicleBrandCatalog,
            'vehicleModelCatalog' => $this->vehicleModelCatalog,
            'accommodationCatalog' => $this->accommodationCatalog,
        ]);
    }

    protected function saveDraft(): void
    {
        if ($this->step < 2) {
            session()->forget('booking_draft');
            return;
        }

        session(['booking_draft' => [
            'step' => $this->step,
            'trip_type' => $this->trip_type,
            'mode' => $this->mode,
            'operator' => $this->operator,
            'origin' => $this->origin,
            'destination' => $this->destination,
            'departure_date' => $this->departure_date,
            'return_date' => $this->return_date,
            'adults' => $this->adults,
            'children' => $this->children,
            'minors' => $this->minors,
            'infants' => $this->infants,
            'selected_schedule_id' => $this->selected_schedule_id,
            'selected_return_schedule_id' => $this->selected_return_schedule_id,
            'passengers' => $this->passengers,
            'selected_schedule_accommodation_id' => $this->selected_schedule_accommodation_id,
            'selected_return_schedule_accommodation_id' => $this->selected_return_schedule_accommodation_id,
            'selected_transport_class_id' => $this->selected_transport_class_id,
            'selected_return_transport_class_id' => $this->selected_return_transport_class_id,
            'has_vehicle' => $this->has_vehicle,
            'vehicle_booking_method' => $this->vehicle_booking_method,
            'selected_vehicle_rate_id' => $this->selected_vehicle_rate_id,
            'selected_brand_id' => $this->selected_brand_id,
            'selected_model_id' => $this->selected_model_id,
            'vehicle_type' => $this->vehicle_type,
            'vehicle_plate_number' => $this->vehicle_plate_number,
            'vehicle_price' => $this->vehicle_price,
            'has_extra_baggage' => $this->hasExtraBaggage,
            'extra_baggage_weight' => $this->extra_baggage_weight,
            'extra_baggage_type' => $this->extra_baggage_type,
            'extra_baggage_specify' => $this->extra_baggage_specify,
            'use_promo_ticket' => $this->use_promo_ticket, // retained for ferry mode
            'client_name' => $this->client_name,
            'client_email' => $this->client_email,
            'client_phone' => $this->client_phone,
            'hasAcceptedTerms' => $this->hasAcceptedTerms,
            'hasAcceptedPrivacy' => $this->hasAcceptedPrivacy,
            'selected_hotel_id' => $this->selected_hotel_id,
            'tour_id' => $this->tour_id,
            'tour_date_id' => $this->tour_date_id,
        ]]);
    }

    protected function stepRules(): array
    {
        return match ($this->step) {
            1 => [
                'trip_type' => 'required|string|in:one_way,round_trip',
                'mode' => $this->tour_id ? 'nullable' : 'required|string|in:ferry,airline',
                'origin' => $this->tour_id ? 'nullable' : 'required|string|max:255',
                'destination' => $this->tour_id ? 'nullable' : 'required|string|max:255',
                'departure_date' => 'required|date',
                'tour_date_id' => $this->tour_id ? 'required|integer|exists:tour_dates,id' : 'nullable',
                'return_date' => $this->trip_type === 'round_trip' ? 'required|date|after_or_equal:departure_date' : 'nullable|date|after_or_equal:departure_date',
                'adults' => [
                    'required',
                    'integer',
                    'min:1',
                    function ($attribute, $value, $fail) {
                        if ($value + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) > 8) {
                            $fail('Maximum of 8 passengers per booking.');
                        }
                    },
                ],
                'children' => [
                    'required',
                    'integer',
                    'min:0',
                    function ($attribute, $value, $fail) {
                        if ($value + $this->adults + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) > 8) {
                            $fail('Maximum of 8 passengers per booking.');
                        }
                    },
                ],
                'minors' => [
                    'required',
                    'integer',
                    'min:0',
                    function ($attribute, $value, $fail) {
                        if ($value + $this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->infants : 0) > 8) {
                            $fail('Maximum of 8 passengers per booking.');
                        }
                    },
                ],
                'infants' => [
                    'required',
                    'integer',
                    'min:0',
                    function ($attribute, $value, $fail) {
                        if ($value > $this->adults) {
                            $fail('Maximum of 1 infant per adult is allowed.');
                        }
                        if ($value + $this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors : 0) > 8) {
                            $fail('Maximum of 8 passengers per booking.');
                        }
                    },
                ],
                'has_vehicle' => 'boolean',
            'vehicle_booking_method' => $this->has_vehicle ? 'required|string|in:category,brand_model' : 'nullable|string|in:category,brand_model',
            'selected_vehicle_rate_id' => $this->has_vehicle && $this->vehicle_booking_method === 'category' && $this->vehicleRateCatalog->isNotEmpty() ? 'required|integer|exists:vehicle_rates,id' : 'nullable',
            'selected_brand_id' => $this->has_vehicle && $this->vehicle_booking_method === 'brand_model' ? 'required|integer|exists:vehicle_brands,id' : 'nullable',
            'selected_model_id' => $this->has_vehicle && $this->vehicle_booking_method === 'brand_model' ? 'required|integer|exists:vehicle_models,id' : 'nullable',
            'vehicle_type' => $this->vehicleRateCatalog->isNotEmpty() ? 'nullable|string|max:255' : 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_plate_number' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_price' => 'required_if:has_vehicle,true|nullable|numeric|min:0',
            'driver_first_name' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_middle_name' => 'nullable|string|max:255',
            'driver_last_name' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_birthday' => 'required_if:has_vehicle,true|nullable|date|before:today',
            'extra_baggage_weight' => 'nullable|numeric|min:0|max:100',
            ],
            2 => [
                'selected_schedule_id' => $this->tour_id ? 'nullable' : 'required|integer|exists:schedules,id',
            ],
            3 => array_merge([
                'passengers.*.first_name' => 'required|string|max:255',
                'passengers.*.middle_name' => 'nullable|string|max:255',
                'passengers.*.last_name' => 'required|string|max:255',
                'passengers.*.name' => 'nullable|string|max:255',
                'passengers.*.discount_id' => 'nullable|exists:discounts,id',
                'passengers.*.birthdate' => 'required|date|before:today',
                'passengers.*.pwd_id_number' => 'nullable|string|max:255',
                'studentIdProofFronts.*' => 'nullable|image|max:10240',
                'studentIdProofBacks.*' => 'nullable|image|max:10240',
            ], $this->isInternational ? [
                'passengers.*.passport_country' => 'required|string|max:100',
                'passengers.*.passport_number' => 'required|string|max:50',
                'passengers.*.passport_issuance_date' => 'required|date|before:today',
                'passengers.*.passport_expiry_date' => 'required|date|after:today',
            ] : []),
            4 => [],
            5 => [
                'client_name' => 'required|string|max:255',
                'client_email' => 'required|email',
                'client_phone' => 'required|string|max:25',
                'hasAcceptedTerms' => 'required|accepted',
                'hasAcceptedPrivacy' => 'required|accepted',
            ],
            default => [],
        };
    }

    public function getIsInternationalProperty(): bool
    {
        if (strtolower($this->mode ?? '') !== 'airline') {
            return false;
        }
        $domesticPorts = ['manila', 'batangas', 'calapan', 'caticlan', 'boracay', 'boracay (caticlan)', 'cebu', 'davao', 'roxas', 'puerto princesa', 'el nido', 'coron', 'bacolod', 'iloilo', 'tagbilaran', 'bohol', 'siargao', 'zamboanga', 'general santos', 'clark', 'laoag', 'legazpi', 'dumaguete', 'tacloban', 'cagayan de oro', 'butuan', 'ozamiz', 'dipolog', 'pagadian', 'surigao', 'tandag', 'camiguin', 'batanes', 'basco', 'busuanga', 'san jose'];
        return !in_array(strtolower(trim($this->origin ?? '')), $domesticPorts, true)
            || !in_array(strtolower(trim($this->destination ?? '')), $domesticPorts, true);
    }

    protected function allRules(): array
    {
        return [
            'trip_type' => 'required|string|in:one_way,round_trip',
            'mode' => $this->tour_id ? 'nullable' : 'required|string|in:ferry,airline',
            'origin' => $this->tour_id ? 'nullable' : 'required|string|max:255',
            'destination' => $this->tour_id ? 'nullable' : 'required|string|max:255',
            'departure_date' => 'required|date',
            'tour_date_id' => $this->tour_id ? 'required|integer|exists:tour_dates,id' : 'nullable',
            'return_date' => $this->trip_type === 'round_trip' ? 'required|date|after_or_equal:departure_date' : 'nullable|date|after_or_equal:departure_date',
            'selected_schedule_id' => $this->tour_id ? 'nullable' : 'required|integer|exists:schedules,id',
            'adults' => [
                'required',
                'integer',
                'min:1',
                function ($attribute, $value, $fail) {
                    if ($value + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) > 8) {
                        $fail('Maximum of 8 passengers per booking.');
                    }
                },
            ],
            'children' => [
                'required',
                'integer',
                'min:0',
                function ($attribute, $value, $fail) {
                    if ($value + $this->adults + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) > 8) {
                        $fail('Maximum of 8 passengers per booking.');
                    }
                },
            ],
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.middle_name' => 'nullable|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.name' => 'nullable|string|max:255',
            'passengers.*.discount_id' => 'nullable|exists:discounts,id',
            'passengers.*.birthdate' => 'required|date|before:today',
            'passengers.*.pwd_id_number' => 'nullable|string|max:255',
            'passengers.*.student_number' => 'nullable|string|max:255',
            'passengers.*.senior_osca_number' => 'nullable|string|max:255',
            'passengers.*.passport_country' => $this->isInternational ? 'required|string|max:100' : 'nullable|string|max:100',
            'passengers.*.passport_number' => $this->isInternational ? 'required|string|max:50' : 'nullable|string|max:50',
            'passengers.*.passport_issuance_date' => $this->isInternational ? 'required|date|before:today' : 'nullable|date',
            'passengers.*.passport_expiry_date' => $this->isInternational ? 'required|date|after:today' : 'nullable|date',
            'studentIdProofFronts.*' => 'nullable|image|max:10240',
            'studentIdProofBacks.*' => 'nullable|image|max:10240',
            'client_name' => 'required|string|max:255',
            'client_email' => 'required|email',
            'client_phone' => 'required|string|max:25',
            'has_vehicle' => 'boolean',
            'vehicle_booking_method' => 'required|string|in:category,brand_model',
            'driver_first_name' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_middle_name' => 'nullable|string|max:255',
            'driver_last_name' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'driver_birthday' => 'required_if:has_vehicle,true|nullable|date',
            'selected_vehicle_rate_id' => $this->vehicle_booking_method === 'category' && $this->vehicleRateCatalog->isNotEmpty() ? 'required_if:has_vehicle,true|nullable|integer|exists:vehicle_rates,id' : 'nullable',
            'selected_brand_id' => $this->vehicle_booking_method === 'brand_model' ? 'required_if:has_vehicle,true|nullable|integer|exists:vehicle_brands,id' : 'nullable',
            'selected_model_id' => $this->vehicle_booking_method === 'brand_model' ? 'required_if:has_vehicle,true|nullable|integer|exists:vehicle_models,id' : 'nullable',
            'vehicle_type' => $this->vehicleRateCatalog->isNotEmpty() ? 'nullable|string|max:255' : 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_plate_number' => 'required_if:has_vehicle,true|nullable|string|max:255',
            'vehicle_price' => 'required_if:has_vehicle,true|nullable|numeric|min:0',
            'extra_baggage_type' => $this->hasExtraBaggage ? 'required|string|max:255' : 'nullable|string|max:255',
            'extra_baggage_specify' => $this->hasExtraBaggage ? 'required|string|max:255' : 'nullable|string|max:255',
            'extra_baggage_weight' => 'nullable|numeric|min:0|max:100',
        ];
    }

    protected function assertSelectedScheduleIsValid(): void
    {
        if (! $this->selected_schedule_id) {
            throw ValidationException::withMessages([
                'selected_schedule_id' => 'Please select a schedule.',
            ]);
        }

        $isValid = Schedule::query()
            ->forRouteAndDate($this->origin, $this->destination, $this->departure_date, $this->mode)
            ->where('id', $this->selected_schedule_id)
            ->exists();

        if (! $isValid) {
            throw ValidationException::withMessages([
                'selected_schedule_id' => 'The selected schedule is no longer available for this route and date.',
            ]);
        }
        
        $sched = Schedule::query()->find($this->selected_schedule_id);
        if ($sched && $sched->transportClasses()->where('transport_classes.is_active', true)->exists() && ! $this->selected_transport_class_id) {
            throw ValidationException::withMessages([
                'selected_transport_class_id' => 'Please select a transport class for your schedule.',
            ]);
        }
        
        if ($this->trip_type === 'round_trip' && !$this->tour_id) {
            if (! $this->selected_return_schedule_id) {
                throw ValidationException::withMessages([
                    'selected_return_schedule_id' => 'Please select a return schedule.',
                ]);
            }
            
            $isReturnValid = Schedule::query()
                ->forRouteAndDate($this->destination, $this->origin, $this->return_date, $this->mode)
                ->where('id', $this->selected_return_schedule_id)
                ->exists();

            if (! $isReturnValid) {
                throw ValidationException::withMessages([
                    'selected_return_schedule_id' => 'The selected return schedule is no longer available for this route and date.',
                ]);
            }

            $returnSched = Schedule::query()->find($this->selected_return_schedule_id);
            if ($returnSched && $returnSched->transportClasses()->where('transport_classes.is_active', true)->exists() && ! $this->selected_return_transport_class_id) {
                throw ValidationException::withMessages([
                    'selected_return_transport_class_id' => 'Please select a transport class for your return schedule.',
                ]);
            }
        }
    }

    protected function generateTransactionNumber(): string
    {
        return 'AGT-' . now()->format('Ymd') . '-' . rand(1000, 9999);
    }

    public function isBookingShortHaul(): bool
    {
        if (strtolower($this->mode ?? '') === 'airline') {
            return false;
        }

        if ($this->prefilled_from_package || $this->tour_id || (!empty($this->duration_days) && $this->duration_days > 0)) {
            return false;
        }

        $depSchedule = collect($this->availableSchedules)->firstWhere('id', $this->selected_schedule_id);
        $retSchedule = $this->trip_type === 'round_trip'
            ? collect($this->availableReturnSchedules)->firstWhere('id', $this->selected_return_schedule_id)
            : null;

        if ($depSchedule) {
            $depMins = $this->parseScheduleDurationMinutes($depSchedule);
            if ($retSchedule) {
                $retMins = $this->parseScheduleDurationMinutes($retSchedule);
                return max($depMins, $retMins) < 300;
            }
            return $depMins < 300;
        }

        return false;
    }

    protected function parseScheduleDurationMinutes(?array $schedule): int
    {
        if (!$schedule) {
            return 0;
        }
        if (!empty($schedule['duration_minutes'])) {
            return (int) $schedule['duration_minutes'];
        }
        if (isset($schedule['is_short_haul'])) {
            return $schedule['is_short_haul'] ? 60 : 360;
        }
        if (!empty($schedule['duration'])) {
            preg_match('/(?:(\d+)\s*h)?\s*(?:(\d+)\s*m)?/i', $schedule['duration'], $m);
            $hours = !empty($m[1]) ? (int) $m[1] : 0;
            $mins = !empty($m[2]) ? (int) $m[2] : 0;
            if ($hours > 0 || $mins > 0) {
                return ($hours * 60) + $mins;
            }
        }
        return 0;
    }

    protected function getFeeMultiplier(): int
    {
        return max(1, count($this->passengers));
    }

    public function calculateTotalPrice(): float
    {
        // If booking a prefilled package from CSV API or a tour with package_price
        if (($this->prefilled_from_package || $this->tour_id) && !empty($this->package_price)) {
            // Parse package_price: remove currency symbols and commas
            $cleanPrice = preg_replace('/[^0-9.]/', '', $this->package_price);
            $base = floatval($cleanPrice);
            $transportTotal = $base * count($this->passengers);
            $vehicleTotal = $this->has_vehicle ? floatval($this->vehicle_price ?? 0) : 0;
            $hotelTotal = $this->selected_hotel_id
                ? floatval($this->accommodationCatalog->firstWhere('id', $this->selected_hotel_id)->price ?? 0)
                : 0;

            $settings = PaymentSetting::current();
            $multiplier = $this->getFeeMultiplier();
            $isShortHaul = $this->isBookingShortHaul();
            $serviceFee = ($multiplier * $settings->getWebAdminFee($isShortHaul));
            // Accommodation fee: only charged if accommodation is actually selected AND has a price
            $accommodationFee = $hotelTotal > 0 ? floatval($settings->fee_per_accommodation) : 0;
            $transactionFee = $settings->getTransactionFee($isShortHaul) * $multiplier;

            return $transportTotal + $vehicleTotal + $hotelTotal + $serviceFee + $accommodationFee + $transactionFee;
        }
        
        // If booking an Eloquent tour with tour pricing (future use when price_from is added)
        if ($this->tour_id && $this->tour) {
            $base = property_exists($this->tour, 'price_from') ? $this->tour->price_from : 0;

            if ($this->tour_date_id) {
                $date = $this->selectedTourDate ?? TourDate::find($this->tour_date_id);
                if ($date && property_exists($date, 'price') && $date->price) {
                    $base = $date->price;
                }
            }

            $transportTotal = floatval($base) * count($this->passengers);
            $vehicleTotal = $this->has_vehicle ? floatval($this->vehicle_price ?? 0) : 0;
            $hotelTotal = $this->selected_hotel_id
                ? floatval($this->accommodationCatalog->firstWhere('id', $this->selected_hotel_id)->price ?? 0)
                : 0;

            $settings = PaymentSetting::current();
            $multiplier = $this->getFeeMultiplier();
            $isShortHaul = $this->isBookingShortHaul();
            $serviceFee = ($multiplier * $settings->getWebAdminFee($isShortHaul));
            // Accommodation fee: only charged if accommodation is actually selected AND has a price
            $accommodationFee = $hotelTotal > 0 ? floatval($settings->fee_per_accommodation) : 0;
            $transactionFee = $settings->getTransactionFee($isShortHaul) * $multiplier;

            return $transportTotal + $vehicleTotal + $hotelTotal + $serviceFee + $accommodationFee + $transactionFee;
        }

        $baseSchedulePrice = $this->getSelectedSchedulePrice();
        $scheduleAccommodationPrice = $this->getSelectedScheduleAccommodationPrice();
        
        $returnSchedulePrice = $this->getSelectedReturnSchedulePrice();
        $returnScheduleAccommodationPrice = $this->getSelectedReturnScheduleAccommodationPrice();
        
        $discountsById = $this->discounts->keyBy('id');

        $departureTransportClassTotal = 0;
        $hasPromoClass = false;
        if ($this->selected_transport_class_id) {
            $stc = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_transport_class_id);
            $departureTransportClassTotal = floatval($stc->additional_price ?? $stc->transportClass->price ?? 0);
            if ($stc && $stc->is_promo) $hasPromoClass = true;
        }

        $returnTransportClassTotal = 0;
        if ($this->trip_type === 'round_trip' && $this->selected_return_transport_class_id) {
            $rstc = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_return_transport_class_id);
            $returnTransportClassTotal = floatval($rstc->additional_price ?? $rstc->transportClass->price ?? 0);
            if ($rstc && $rstc->is_promo) $hasPromoClass = true;
        }

        $isFerryPromo = ($this->mode !== 'airline' && $this->use_promo_ticket);
        $disableDiscounts = $isFerryPromo || $hasPromoClass;

        // For airline bookings, fetch the active promo ticket once (if any)
        $activePromoTicket = (strtolower($this->mode) === 'airline') ? $this->getActivePromoTicket() : null;

        $isFerry = (strtolower($this->mode ?? '') !== 'airline');

        $transportTotal = collect($this->passengers)->sum(function (array $passenger) use (
            $baseSchedulePrice,
            $scheduleAccommodationPrice,
            $returnSchedulePrice,
            $returnScheduleAccommodationPrice,
            $departureTransportClassTotal,
            $returnTransportClassTotal,
            $discountsById,
            $activePromoTicket,
            $disableDiscounts,
            $isFerry
        ) {
            $scheduleAccommodationPrice_ = $scheduleAccommodationPrice;

            // Airline per-passenger promo: departure leg uses promo_price, no discount applied
            if ($activePromoTicket && ! empty($passenger['use_promo'])) {
                $departureFare = floatval($activePromoTicket->promo_price) + $scheduleAccommodationPrice_ + $departureTransportClassTotal;
                $returnFare = $returnSchedulePrice + $returnScheduleAccommodationPrice + $returnTransportClassTotal;
                return $departureFare + $returnFare;
            }

            if ($passenger['type'] === 'driver') {
                return 0; // Driver ticket is free
            }

            $type = strtolower($passenger['type'] ?? 'adult');
            $paxMultiplier = 1.0;
            if ($isFerry) {
                if (in_array($type, ['child', 'minor'], true)) {
                    $paxMultiplier = 0.5;
                }
            } else {
                // Airline
                if (in_array($type, ['minor', 'child', 'infant'], true)) {
                    $paxMultiplier = 0.5;
                }
            }

            $depBaseAndClass = ($baseSchedulePrice + $departureTransportClassTotal) * $paxMultiplier;
            $retBaseAndClass = ($returnSchedulePrice + $returnTransportClassTotal) * $paxMultiplier;

            $depFare = $depBaseAndClass + $scheduleAccommodationPrice_;
            $retFare = $retBaseAndClass + $returnScheduleAccommodationPrice;

            $fare = $depFare + $retFare;

            $hasDiscount = ! empty($passenger['discount_id']) && !$disableDiscounts && !(!$isFerry && $type === 'infant');

            if ($hasDiscount) {
                $discount = $discountsById->get($passenger['discount_id']);

                if ($discount) {
                    $fare -= $fare * (floatval($discount->percentage) / 100);
                }
            }

            return $fare;
        });

        $transportClassTotal = 0; // Already added per passenger above

        $vehicleTotal = $this->has_vehicle ? floatval($this->vehicle_price ?? 0) : 0;

        $hotelTotal = $this->selected_hotel_id
            ? floatval($this->accommodationCatalog->firstWhere('id', $this->selected_hotel_id)->price ?? 0)
            : 0;

        $settings = PaymentSetting::current();
        $isShortHaul = $this->isBookingShortHaul();

        // Service fee: charged per ticket + transport class
        $multiplier = $this->getFeeMultiplier();
        $serviceFee = ($multiplier * $settings->getWebAdminFee($isShortHaul));
        
        // Accommodation fee: only charged if hotel is actually selected AND has a price
        $accommodationFee = $hotelTotal > 0 ? floatval($settings->fee_per_accommodation) : 0;
        
        $transactionFee = $settings->getTransactionFee($isShortHaul) * $multiplier;

        return $transportTotal + $transportClassTotal + $vehicleTotal + $hotelTotal + $serviceFee + $accommodationFee + $transactionFee + $this->getExtraBaggageTotalPrice();
    }

    /**
     * Get detailed price breakdown for display in the submit form
     */
    public function getPriceBreakdown(): array
    {
        $breakdown = [
            'departure_ticket' => 0,
            'return_ticket' => 0,
            'accommodation' => 0,
            'transport_class' => 0,
            'vehicle' => 0,
            'hotel' => 0,
            'extra_baggage' => 0,
            'fee_per_traveler' => 0,
            'fee_per_accommodation' => 0,
            'transaction_fee' => 0,
            'total' => 0,
        ];

        $settings = PaymentSetting::current();
        $passengerCount = count($this->passengers);

        // Get ticket prices per person
        $departureTicketPrice = $this->getSelectedSchedulePrice();
        $departureAccommodationPrice = $this->getSelectedScheduleAccommodationPrice();
        $returnTicketPrice = $this->getSelectedReturnSchedulePrice();
        $returnAccommodationPrice = $this->getSelectedReturnScheduleAccommodationPrice();

        $departureTransportClassTotal = 0;
        $hasPromoClass = false;
        if ($this->selected_transport_class_id) {
            $stc = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_transport_class_id);
            $departureTransportClassTotal = floatval($stc->additional_price ?? $stc->transportClass->price ?? 0);
            if ($stc && $stc->is_promo) $hasPromoClass = true;
        }

        $returnTransportClassTotal = 0;
        if ($this->trip_type === 'round_trip' && $this->selected_return_transport_class_id) {
            $rstc = \App\Models\ScheduleTransportClass::with('transportClass')->find($this->selected_return_transport_class_id);
            $returnTransportClassTotal = floatval($rstc->additional_price ?? $rstc->transportClass->price ?? 0);
            if ($rstc && $rstc->is_promo) $hasPromoClass = true;
        }

        $isFerryPromo = ($this->mode !== 'airline' && $this->use_promo_ticket);
        $disableDiscounts = $isFerryPromo || $hasPromoClass;

        // Calculate totals considering discounts
        $discountsById = $this->discounts->keyBy('id');
        
        $totalDepartureTicket = 0;
        $totalReturnTicket = 0;
        $totalDepartureAccommodation = 0;
        $totalReturnAccommodation = 0;
        $isFerry = (strtolower($this->mode ?? '') !== 'airline');

        foreach ($this->passengers as $passenger) {
            if ($passenger['type'] === 'driver') {
                continue; // Driver ticket and accommodation are free
            }

            $type = strtolower($passenger['type'] ?? 'adult');
            $paxMultiplier = 1.0;
            if ($isFerry) {
                if (in_array($type, ['child', 'minor'], true)) {
                    $paxMultiplier = 0.5;
                }
            } else {
                // Airline
                if (in_array($type, ['minor', 'child', 'infant'], true)) {
                    $paxMultiplier = 0.5;
                }
            }

            $depTicket = ($departureTicketPrice + $departureTransportClassTotal) * $paxMultiplier;
            $retTicket = ($returnTicketPrice + $returnTransportClassTotal) * $paxMultiplier;
            
            $isAirlinePromoPassenger = (strtolower($this->mode) === 'airline' && !empty($passenger['use_promo']));
            $hasDiscount = !empty($passenger['discount_id']) && !$disableDiscounts && !$isAirlinePromoPassenger && !(!$isFerry && $type === 'infant');

            if ($hasDiscount) {
                $discount = $discountsById->get($passenger['discount_id']);
                if ($discount) {
                    $percentage = floatval($discount->percentage) / 100;
                    $depTicket -= $depTicket * $percentage;
                    $retTicket -= $retTicket * $percentage;
                }
            }

            $totalDepartureTicket += $depTicket;
            $totalReturnTicket += $retTicket;
            $totalDepartureAccommodation += $departureAccommodationPrice;
            $totalReturnAccommodation += $returnAccommodationPrice;
        }

        $breakdown['departure_ticket'] = $totalDepartureTicket;
        $breakdown['return_ticket'] = $totalReturnTicket;
        $breakdown['accommodation'] = $totalDepartureAccommodation + $totalReturnAccommodation;
        $breakdown['transport_class'] = 0; // Combined into tickets above

        // Vehicle (per booking, not per person)
        $breakdown['vehicle'] = $this->has_vehicle ? floatval($this->vehicle_price ?? 0) : 0;

        // Hotel (per booking, not per person)
        $breakdown['hotel'] = $this->selected_hotel_id
            ? floatval($this->accommodationCatalog->firstWhere('id', $this->selected_hotel_id)->price ?? 0)
            : 0;

        // Extra Baggage (multiplied per passenger per explicit user selection)
        $breakdown['extra_baggage'] = $this->getExtraBaggageTotalPrice();

        // Fees
        $multiplier = $this->getFeeMultiplier();
        $isShortHaul = $this->isBookingShortHaul();
        $breakdown['fee_per_traveler'] = $multiplier * $settings->getWebAdminFee($isShortHaul);
        
        // Accommodation fee: only charged if hotel is actually selected AND has a price
        $breakdown['fee_per_accommodation'] = $breakdown['hotel'] > 0 ? floatval($settings->fee_per_accommodation) : 0;

        $breakdown['transaction_fee'] = $settings->getTransactionFee($isShortHaul) * $multiplier;

        // Calculate total (sum of all items)
        $breakdown['total'] = 
            $breakdown['departure_ticket'] +
            $breakdown['return_ticket'] +
            $breakdown['accommodation'] +
            $breakdown['transport_class'] +
            $breakdown['vehicle'] +
            $breakdown['hotel'] +
            $breakdown['extra_baggage'] +
            $breakdown['fee_per_traveler'] +
            $breakdown['fee_per_accommodation'] +
            $breakdown['transaction_fee'];

        return $breakdown;
    }

    protected function getSelectedReturnSchedulePrice(): float
    {
        if (! $this->selected_return_schedule_id) {
            return 0;
        }

        $schedule = collect($this->availableReturnSchedules)
            ->firstWhere('id', $this->selected_return_schedule_id);

        return $schedule ? floatval($schedule['price']) : 0;
    }

    protected function getSelectedScheduleAccommodationPrice(): float
    {
        if (! $this->selected_schedule_accommodation_id || ! $this->selected_schedule_id) {
            return 0;
        }

        $schedule = collect($this->availableSchedules)
            ->firstWhere('id', $this->selected_schedule_id);

        if ($schedule && isset($schedule['accommodations'])) {
            $accommodation = collect($schedule['accommodations'])
                ->firstWhere('id', $this->selected_schedule_accommodation_id);
            if ($accommodation) {
                return floatval($accommodation['price']);
            }
        }

        return 0;
    }

    public function getMaxPassengers(): int
    {
        return $this->trip_type === 'round_trip' ? 4 : 8;
    }

    public function clampPassengersToMax(): void
    {
        $max = $this->getMaxPassengers();
        $total = $this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0);
        if ($total > $max) {
            while ($this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) > $max) {
                if ($this->infants > 0) {
                    $this->infants--;
                } elseif ($this->minors > 0) {
                    $this->minors--;
                } elseif ($this->children > 0) {
                    $this->children--;
                } elseif ($this->adults > 1) {
                    $this->adults--;
                } else {
                    break;
                }
            }
            if ($this->infants > $this->adults) {
                $this->infants = $this->adults;
            }
            $this->syncPassengerEntries();
        }
    }

    public function incrementAdults(): void
    {
        if ($this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) >= $this->getMaxPassengers()) {
            return;
        }

        $this->adults++;
        $this->saveDraft();
    }

    public function decrementAdults(): void
    {
        if ($this->adults <= 1) {
            return;
        }

        $this->adults--;
        
        // Ensure infants don't exceed adults
        if ($this->infants > $this->adults) {
            $this->infants = $this->adults;
        }
        
        $this->saveDraft();
    }

    public function incrementChildren(): void
    {
        if ($this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) >= $this->getMaxPassengers()) {
            return;
        }

        $this->children++;
        $this->saveDraft();

        if (! $this->hasSeenMinorAgeWarning && $this->mode !== 'airline') {
            $this->showMinorAgeWarning = true;
            $this->hasSeenMinorAgeWarning = true;
        }
    }

    public function decrementChildren(): void
    {
        if ($this->children <= 0) {
            return;
        }

        $this->children--;
        $this->saveDraft();
    }
    
    public function incrementMinors(): void
    {
        if ($this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) >= $this->getMaxPassengers()) {
            return;
        }

        $this->minors++;
        $this->saveDraft();
    }

    public function decrementMinors(): void
    {
        if ($this->minors <= 0) {
            return;
        }

        $this->minors--;
        $this->saveDraft();
    }
    
    public function incrementInfants(): void
    {
        if ($this->infants >= $this->adults) {
            return; // 1 infant per adult limit
        }
        if ($this->adults + $this->children + (strtolower($this->mode) === 'airline' ? $this->minors + $this->infants : 0) >= $this->getMaxPassengers()) {
            return;
        }

        $this->infants++;
        $this->saveDraft();
    }

    public function decrementInfants(): void
    {
        if ($this->infants <= 0) {
            return;
        }

        $this->infants--;
        $this->saveDraft();
    }

    public function closeMinorAgeWarning(): void
    {
        $this->showMinorAgeWarning = false;
    }

    protected function validatePassengerExtras(): void
    {
        $this->syncFullPassengerNames();

        $validator = Validator::make([
            'passengers' => $this->passengers,
        ], [
            'passengers.*.first_name' => 'required|string|max:255',
            'passengers.*.middle_name' => 'nullable|string|max:255',
            'passengers.*.last_name' => 'required|string|max:255',
            'passengers.*.name' => 'nullable|string|max:255',
            'passengers.*.discount_id' => 'nullable|exists:discounts,id',
            'passengers.*.birthdate' => 'required|date|before:today',
            'passengers.*.pwd_id_number' => 'nullable|string|max:255',
        ]);

        $validator->after(function ($validator) {
            $isAirline = strtolower($this->mode ?? '') === 'airline';

            foreach ($this->passengers as $index => $passenger) {
                $type      = strtolower($passenger['type'] ?? 'adult');
                $birthdate = $passenger['birthdate'] ?? null;

                // --- Age-range check per passenger type ---
                if ($birthdate && strtotime($birthdate)) {
                    $dob     = \Carbon\Carbon::parse($birthdate);
                    $ageYrs  = $dob->diffInYears(now());
                    $ageMths = $dob->diffInMonths(now());

                    $ageError = match (true) {
                        $type === 'adult' && $ageYrs < 11
                            => 'Adult passenger must be 11 years old or above.',
                        $type === 'minor' && ($ageYrs < 7 || $ageYrs > 11)
                            => 'Minor passenger must be 7 to 11 years old.',
                        $type === 'child' && $isAirline && ($ageYrs < 2 || $ageYrs > 6)
                            => 'Child passenger (airline) must be 2 to 6 years old.',
                        $type === 'child' && !$isAirline && ($ageYrs < 2 || $ageYrs > 11)
                            => 'Child passenger (ferry) must be 2 to 11 years old.',
                        $type === 'infant' && $ageMths > 23
                            => 'Infant passenger must be 0 to 23 months old.',
                        default => null,
                    };

                    if ($ageError) {
                        $validator->errors()->add("passengers.{$index}.birthdate", $ageError);
                    }
                }

                // --- Discount eligibility ---
                $discount = $this->discounts->firstWhere('id', $passenger['discount_id']);

                if (! $discount) {
                    continue;
                }

                $discountKey = strtolower($discount->name);

                if (str_contains($discountKey, 'student')) {
                    if (blank($this->studentIdProofFronts[$index] ?? null)) {
                        $validator->errors()->add("studentIdProofFronts.{$index}", 'School ID proof (front) is required when Student discount is selected.');
                    }

                    if (blank($this->studentIdProofBacks[$index] ?? null)) {
                        $validator->errors()->add("studentIdProofBacks.{$index}", 'School ID proof (back) is required when Student discount is selected.');
                    }

                    if (blank($passenger['student_number'] ?? null)) {
                        $validator->errors()->add("passengers.{$index}.student_number", 'Student number is required when Student discount is selected.');
                    }
                }

                if (str_contains($discountKey, 'senior')) {
                    if (blank($passenger['senior_osca_number'] ?? null)) {
                        $validator->errors()->add("passengers.{$index}.senior_osca_number", 'OSCA number is required when Senior Citizen discount is selected.');
                    }
                    // Age check: Senior must be 60 years old or older
                    if ($birthdate && strtotime($birthdate)) {
                        $age = \Carbon\Carbon::parse($birthdate)->diffInYears(now());
                        if ($age < 60) {
                            $validator->errors()->add(
                                "passengers.{$index}.discount_id",
                                'Senior Citizen discount requires the passenger to be at least 60 years old.'
                            );
                        }
                    }
                }

                if (str_contains($discountKey, 'pwd')) {
                    if (blank($passenger['pwd_id_number'] ?? null)) {
                        $validator->errors()->add("passengers.{$index}.pwd_id_number", 'PWD ID number is required when PWD Card discount is selected.');
                    }
                }
            }
        });

        $validator->validate();
    }


    public function togglePassengerInfoModal(): void
    {
        $this->showPassengerInfoModal = ! $this->showPassengerInfoModal;
    }

    protected function collectStudentDiscountProofEntries(): array
    {
        $proofEntries = [];

        foreach ($this->passengers as $index => $passenger) {
            $discount = $this->discounts->firstWhere('id', $passenger['discount_id'] ?? null);

            if (! $discount || ! str_contains(strtolower($discount->name), 'student')) {
                continue;
            }

            $front = $this->studentIdProofFronts[$index] ?? null;
            $back = $this->studentIdProofBacks[$index] ?? null;

            if (blank($front) && blank($back)) {
                continue;
            }

            $proofEntries[$index] = [
                'front' => $front,
                'back' => $back,
                'passenger_name' => trim((string) ($passenger['name'] ?? '')) ?: trim((string) (($passenger['first_name'] ?? '') . ' ' . ($passenger['last_name'] ?? ''))),
                'student_number' => $passenger['student_number'] ?? null,
                'discount_name' => $discount->name,
            ];
        }

        return $proofEntries;
    }

    protected function getSelectedReturnScheduleAccommodationPrice(): float
    {
        if (! $this->selected_return_schedule_accommodation_id || ! $this->selected_return_schedule_id) {
            return 0;
        }

        $schedule = collect($this->availableReturnSchedules)
            ->firstWhere('id', $this->selected_return_schedule_id);

        if (! $schedule || empty($schedule['schedule_accommodations'])) {
            return 0;
        }

        $accommodation = collect($schedule['schedule_accommodations'])
            ->firstWhere('id', $this->selected_return_schedule_accommodation_id);

        return $accommodation ? floatval($accommodation['price']) : 0;
    }

    protected function getSelectedSchedulePrice(): float
    {
        if (! $this->selected_schedule_id) {
            return 0;
        }

        // For airline bookings, per-passenger promo pricing is applied in calculateTotalPrice().
        // For ferry (and other) modes, honour the booking-level use_promo_ticket toggle.
        if ($this->mode !== 'airline' && $this->use_promo_ticket) {
            $schedule = Schedule::query()->find($this->selected_schedule_id);
            if ($schedule) {
                $promoTicket = $schedule->activePromotionalTicket();
                if ($promoTicket) {
                    return floatval($promoTicket->promo_price);
                }
            }
        }

        $schedule = collect($this->availableSchedules)
            ->firstWhere('id', $this->selected_schedule_id);

        if ($schedule) {
            return floatval($schedule['price']);
        }

        return floatval(Schedule::query()->whereKey($this->selected_schedule_id)->value('price') ?? 0);
    }

    public function getActivePromoTicket(): ?PromotionalTicket
    {
        if (! $this->selected_schedule_id) {
            return null;
        }
        $schedule = Schedule::query()->find($this->selected_schedule_id);
        return $schedule ? $schedule->activePromotionalTicket() : null;
    }
}
