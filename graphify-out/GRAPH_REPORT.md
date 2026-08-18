# Graph Report - AmigaTravel  (2026-08-18)

## Corpus Check
- 662 files · ~2,381,057 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17243 nodes · 52672 edges · 542 communities (500 shown, 42 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 8139 edges (avg confidence: 0.56)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `cb71b75a`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- BookingForm
- .saveDraft
- .mount
- .processBookingInternal
- manage-website-settings.blade.php
- .updateAvailableScheduleDates
- .updateBaggagePriceFromRates
- .getActivePromoTicket
- booking-form.blade.php
- HomePageTest
- download.blade.php
- app.blade.php
- schedules.blade.php
- main.dart
- chart.js
- static
- rich-editor.js
- markdown-editor.js
- chart.js
- Booking
- livewire.js
- User.php
- draw
- b
- livewire.min.js
- k
- select.js
- locationFromPosition
- _update
- fromObject
- constructor
- d
- Schedule
- H
- TransportClass
- deleteInDirection
- livewire.esm.js
- add
- User
- a3
- x
- j_
- gv
- te
- "node_modules/alpinejs/dist/module.cjs.js"
- _update
- ListRecords
- canvaskit.js
- getContext
- file-upload.js
- getSelectedRange
- AC
- push
- canvaskit.js
- Voucher
- qt
- canvaskit.js
- dH
- aQ
- buildTicks
- ManageWebsiteSettings
- support.js
- gO
- RelationManager
- I
- i
- get
- State
- setAttribute
- a
- a5
- notifications.js
- s
- EditRecord
- Controller
- updateElements
- sendRequest
- push
- o8
- E
- wimp.js
- skwasm.js
- $1
- push
- getBoundingClientRect
- ManageProofs
- Dt
- preload
- HasFactory
- skwasm_heavy.js
- b5
- G
- .$2
- draw
- r
- .$1
- $0
- jU
- M
- get
- createMorphContext
- navigate_default
- aG
- render
- Vn
- add
- UseAdminGuard.php
- add
- notification_service.dart
- gaf
- le
- bi
- fn
- Ve
- BookingReschedule
- Ra
- OJ
- b
- a1
- getDatasetMeta
- aW_
- navigate_default
- Win32Window
- dO
- gN
- bJ
- start
- What You Must Do When Invoked
- C
- gt
- railway-start.sh
- Vehicle
- St
- d4
- call
- d4
- What You Must Do When Invoked
- my_application.cc
- What You Must Do When Invoked
- gP
- .$1
- echo.js
- m
- V
- $0
- gO
- has
- package.json
- ViewRecord
- replacement_booking_screen.dart
- kr
- $2
- dB
- aM_
- dD
- bw
- b6
- StatelessWidget
- aJ
- gbq
- tT
- bn
- _each
- 🚀 Part 1: Backend Setup (Laravel)
- win32_window.cpp
- e_
- ah
- add
- RunnerTests.swift
- require
- bZ
- d5
- nE
- hw
- jB
- aP
- AdminNotificationController
- flutter.js
- flutter_bootstrap.js
- gb5
- ho
- aNl
- qe
- Widget
- composer.json
- d7
- bY
- gaR
- G
- Deployment setup
- rs
- scripts
- color-picker.js
- manifest.json
- lY
- gh3
- mergeNewHead
- GeneratedPluginRegistrant.swift
- wWinMain
- gc1
- app.js
- manifest.json
- aYd
- manifest.json
- dispatchEvent
- booking-reschedule.blade.php
- MessageHandler
- Flutter & Android Studio Setup Guide
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- lL
- require-dev
- setup
- gvm
- config
- psr-4
- a_K
- How to Update the Android App (APK)
- graphify reference: query, path, explain
- graphify reference: query, path, explain
- graphify reference: query, path, explain
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- _CouponCardClipper
- Amiga Gracia Flutter App
- RegisterPlugins
- graphify reference: add a URL and watch a folder
- graphify reference: commit hook and native CLAUDE.md integration
- graphify reference: incremental update and cluster-only
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- MainActivity
- graphify reference: GitHub clone and cross-repo merge
- graphify reference: transcribe video and audio
- a9o
- manage-transport-accommodation.blade.php
- graphify.md
- graphify.md
- CLAUDE.md
- CLAUDE.md
- extraction-spec.md
- _AppVersion
- components._schedule-card
- extraction-spec.md
- downloadReport(
- filament.partials.scroll-validation
- README.md
- copilot-instructions.md
- extraction-spec.md
- manifest.json
- notification-bell.blade.php
- vehicle-type-toggle.blade.php
- admin-notifications.blade.php
- @gmail
- String?
- Widget
- flutter_service_worker.js
- Program
- why-travel-section.blade.php
- HasMany
- HasOne

## God Nodes (most connected - your core abstractions)
1. `a()` - 754 edges
2. `a()` - 659 edges
3. `b()` - 606 edges
4. `c()` - 501 edges
5. `k()` - 486 edges
6. `h()` - 473 edges
7. `j()` - 432 edges
8. `q()` - 391 edges
9. `n()` - 375 edges
10. `n()` - 375 edges

## Surprising Connections (you probably didn't know these)
- `ServiceCancellationTest` --references--> `Booking`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/Booking.php
- `ServiceCancellationTest` --references--> `FerryRoute`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/FerryRoute.php
- `ServiceCancellationTest` --references--> `Schedule`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/Schedule.php
- `ServiceCancellationTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/User.php
- `Na()` --indirect_call--> `H()`  [INFERRED]
  public/app/canvaskit/skwasm.js → public/app/main.dart.js

## Import Cycles
- None detected.

## Communities (542 total, 42 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (5): BookingForm, Accommodation, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (447): $3$crossAxisPosition$mainAxisPosition(), a03(), a0a(), a0b(), a0m(), a0O(), a0S(), a0Z() (+439 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (486): $2$priority$scheduler(), $4(), a0(), a13(), a1V(), a2(), a2A(), a2D() (+478 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.03
Nodes (104): a0Q(), a0Z(), a10(), a1I(), a1z(), a2a(), a3P(), a46() (+96 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.02
Nodes (44): CreateBookingAction, RetroactiveGraciaPoints, AccommodationController, BookingCalculateController, DiscountController, GraciaPointsController, NotificationController, PromotionController (+36 more)

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.10
Nodes (27): a(), a(), a(), a(), a(), At(), average(), dataset() (+19 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.01
Nodes (354): a0u(), a12(), a1P(), a1U(), a1y(), a21(), a2B(), a2E() (+346 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.04
Nodes (26): NotifyAffectedBookerJob, SendBookingConfirmationJob, BookingCancellation, self, BookingConfirmation, BookingCreated, PaymentProofReceived, RebookingRequested (+18 more)

### Community 10 - "download.blade.php"
Cohesion: 0.04
Nodes (31): CreatesApplication, dismissCancellationReminder, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, requestCancellation, selectRebookingDepartureAccommodation(, selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectRebookingReturnAccommodation( (+23 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.10
Nodes (8): ViewBooking, Carbon, ScheduleCsvImportService, normalize_operator_name(), operator_is_ferry(), storage_asset_path(), Carbon\Carbon, Illuminate\Support\HtmlString

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (545): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+537 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (110): acquireContext(), afterDraw(), alpha(), beforeDatasetDraw(), beforeDatasetsDraw(), bh(), color(), contains() (+102 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (27): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+19 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (128): activateAttributeIfSupported(), addHTMLAttribute(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeAttributes(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback() (+120 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (197): u(), _a(), Aa(), Ac(), Ae(), af(), ai(), al() (+189 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (120): aa(), addControllers(), addPlugins(), addScales(), an(), aspectRatio(), beforeDatasetDraw(), beforeDatasetsDraw() (+112 more)

### Community 20 - "Booking"
Cohesion: 0.22
Nodes (18): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), evaluate2(), fn(), getAlignment(), getAlignmentAxis(), getAlignmentSides() (+10 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (92): addAssetsToHeadTagOfPage(), _arrayLikeToArray(), _arrayWithoutHoles(), attributeShouldntBePreservedIfFalsy(), bind(), bindAttribute(), bindAttributeAndProperty(), bindInputValue() (+84 more)

### Community 22 - "User.php"
Cohesion: 0.03
Nodes (40): AdminNotifications, ManagePaymentSettings, ManageRebookings, ManageTransportAccommodation, Collection, StaffPerformance, ViewApkUser, BookingsRelationManager (+32 more)

### Community 23 - "draw"
Cohesion: 0.04
Nodes (117): ad(), adjustHitBoxes(), ae(), af(), _calculateBarValuePixels(), calculateLabelRotation(), _calculatePadding(), cd() (+109 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (368): a02(), a0k(), a0S(), a0t(), a0U(), a0x(), a14(), a1B() (+360 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.02
Nodes (86): ae(), appendChild(), au(), bc(), bl(), ["@blur"](), bo(), bt() (+78 more)

### Community 26 - "k"
Cohesion: 0.06
Nodes (6): BookingReschedule, PaymentProof, PromoImageManager, UserDashboard, Livewire\Component, Livewire\WithFileUploads

### Community 27 - "select.js"
Cohesion: 0.06
Nodes (76): [g](), [x](), Sg(), $c(), ca(), D(), E(), Ea() (+68 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.04
Nodes (102): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), appendText(), applyBlockAttributeAtRange(), breakFormattedBlock(), compositionControllerDidRequestDeselectingAttachment(), compositionDidStartEditingAttachment() (+94 more)

### Community 29 - "_update"
Cohesion: 0.04
Nodes (89): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+81 more)

### Community 30 - "fromObject"
Cohesion: 0.03
Nodes (92): _a(), abutsStart(), after(), afterAutoSkip(), Ag(), Ai(), before(), buildLookupTable() (+84 more)

### Community 31 - "constructor"
Cohesion: 0.03
Nodes (81): Bl(), Ce(), cf(), clone(), constructor(), create(), Dl(), dtFormatter() (+73 more)

### Community 32 - "d"
Cohesion: 0.02
Nodes (230): a0j(), a1J(), a1p(), a1w(), a2r(), a2s(), a4B(), a4C() (+222 more)

### Community 33 - "Schedule"
Cohesion: 0.09
Nodes (23): applyStack(), beforeDraw(), eh(), fa(), _getSortedDatasetMetas(), getSortedVisibleDatasetMetas(), getVisibleDatasetCount(), Gi() (+15 more)

### Community 34 - "H"
Cohesion: 0.03
Nodes (96): $1$1(), A1(), a4u(), a5h(), a5U(), a73(), a8p(), a8X() (+88 more)

### Community 35 - "TransportClass"
Cohesion: 0.07
Nodes (38): Ah(), as(), At(), Bi(), Bs(), cc(), De(), describe() (+30 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.03
Nodes (100): $3$crossAxisPosition$mainAxisPosition(), a04(), a08(), a3L(), a4J(), a4K(), a4o(), a70() (+92 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (54): addAssetsToHeadTagOfPage(), addCleanup(), applyUpdates(), [attribute](), callAndClearComponentDebounces(), children(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2() (+46 more)

### Community 38 - "add"
Cohesion: 0.09
Nodes (45): call(), checkIdentityKeys(), clear(), closestComponent(), componentIsMissingProperty(), createForEach(), createGetter(), createInstrumentations() (+37 more)

### Community 39 - "User"
Cohesion: 0.09
Nodes (29): ac(), Ai(), alpha(), be(), ca(), en(), fe(), greyscale() (+21 more)

### Community 40 - "a3"
Cohesion: 0.04
Nodes (9): ServiceCancellationResource, DatePicker, Schedule, ServiceCancellationReplacementSchedule, ServiceCancellationManager, DateTimeInterface, HasOneThrough, Illuminate\Database\Eloquent\Relations\HasMany (+1 more)

### Community 41 - "x"
Cohesion: 0.10
Nodes (73): ad(), at(), B(), br(), Bt(), cd(), Cr(), Ct() (+65 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (281): $3(), a00(), a07(), a0E(), a0f(), a0g(), a0h(), a0i() (+273 more)

### Community 43 - "gv"
Cohesion: 0.04
Nodes (69): addRootSelector(), addScopeToNode(), allSelectors(), cleanup(), cleanupAttributes(), clone(), cloneTree(), closestIdRoot() (+61 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (11): Pr(), Bi(), bn(), ji(), Ri(), te(), Vi(), Xc() (+3 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.15
Nodes (17): extractScriptTagContent(), generateEvaluatorFromFunction(), generateEvaluatorFromString(), generateFunctionFromString(), getIterationScopeVariables(), getLengthValue(), getRootMargin(), getThreshold() (+9 more)

### Community 46 - "_update"
Cohesion: 0.07
Nodes (44): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+36 more)

### Community 47 - "ListRecords"
Cohesion: 0.03
Nodes (25): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+17 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.06
Nodes (48): $a(), ab(), Ad(), b(), bb(), bc(), c(), cb() (+40 more)

### Community 49 - "getContext"
Cohesion: 0.07
Nodes (37): ArrowLeft(), ArrowRight(), backspace(), canDecreaseBlockAttributeLevel(), d(), delete(), deleteByComposition(), deleteByCut() (+29 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (54): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+46 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (63): attachmentManagerDidRequestRemovalOfAttachment(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), canSetCurrentTextAttribute(), compositionControllerDidRequestRemovalOfAttachment(), decreaseBlockAttributeLevel() (+55 more)

### Community 52 - "AC"
Cohesion: 0.06
Nodes (48): _a(), active(), add(), al(), _animateOptions(), ba(), _cachedScopes(), cancel() (+40 more)

### Community 53 - "push"
Cohesion: 0.08
Nodes (46): $h(), adjustHitBoxes(), afterDraw(), bc(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight() (+38 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.08
Nodes (14): Ad(), bc(), fe(), get(), R(), Ra(), td(), ub() (+6 more)

### Community 55 - "Voucher"
Cohesion: 0.06
Nodes (60): afterAutoSkip(), Ao(), applyStack(), ar(), as(), Bi(), buildLookupTable(), _calculateBarIndexPixels() (+52 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.05
Nodes (50): A(), Ad(), b(), Bd(), c(), Cd(), d(), dd() (+42 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (338): $1(), $2$alignmentPolicy(), a0B(), a0D(), a0W(), a11(), a1m(), a1x() (+330 more)

### Community 59 - "aQ"
Cohesion: 0.01
Nodes (258): $1$1(), a14(), a16(), a1D(), a1f(), a1j(), a1k(), a1m() (+250 more)

### Community 60 - "buildTicks"
Cohesion: 0.07
Nodes (52): a00(), a07(), a1l(), a2f(), a2g(), a2h(), a2i(), a5e() (+44 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.04
Nodes (19): ManageWebsiteSettings, VehicleRate, WebsiteSetting, AppServiceProvider, AirlineBaggageRuleSeeder, DatabaseSeeder, DiscountSeeder, GraciaEarningRuleSeeder (+11 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (165): ut(), Nt(), Qt(), _a(), aa(), Ae(), ai(), apply() (+157 more)

### Community 63 - "gO"
Cohesion: 0.11
Nodes (36): aev(), alr(), b2B(), fD(), gAW(), gPc(), gzK(), ir() (+28 more)

### Community 64 - "RelationManager"
Cohesion: 0.03
Nodes (92): a05(), a3b(), a5B(), a78(), a7A(), a8l(), aA6(), aC2() (+84 more)

### Community 65 - "I"
Cohesion: 0.07
Nodes (48): aspectRatio(), C(), Ca(), _calculateBarIndexPixels(), calculateCircumference(), _circumference(), co(), _computeAngle() (+40 more)

### Community 66 - "i"
Cohesion: 0.11
Nodes (23): putPersistantElementsBack(), ap(), bd(), Bi(), Br(), el(), getEncodedSnapshotWithLatestChildrenMergedIn(), jc() (+15 more)

### Community 67 - "get"
Cohesion: 0.15
Nodes (15): handleS3PreSignedUrl(), handleSignedUrl(), ji(), makeRequest(), markUploadErrored(), markUploadFinished(), prepare(), qt() (+7 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (61): ForgotPasswordScreen, _ForgotPasswordScreenState, ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState (+53 more)

### Community 69 - "setAttribute"
Cohesion: 0.07
Nodes (49): add(), applyKeyboardCommand(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap(), copyUsingObjectsFromDocument(), createCaptionElement() (+41 more)

### Community 70 - "a"
Cohesion: 0.11
Nodes (26): afterDatasetsUpdate(), _d(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), _handleEvent() (+18 more)

### Community 71 - "a5"
Cohesion: 0.05
Nodes (62): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), buildOrUpdateScales(), _checkEventBindings() (+54 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (25): observer(), actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo() (+17 more)

### Community 73 - "s"
Cohesion: 0.14
Nodes (49): getOptions(), Bn(), bs(), c(), d(), Di(), Dl(), Dr() (+41 more)

### Community 74 - "EditRecord"
Cohesion: 0.05
Nodes (17): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditFerryRoute, EditGraciaEarningRule, EditHotel (+9 more)

### Community 75 - "Controller"
Cohesion: 0.01
Nodes (232): $2$from$to(), a(), a09(), a18(), a19(), a1B(), a1E(), a2q() (+224 more)

### Community 76 - "updateElements"
Cohesion: 0.08
Nodes (38): addElements(), afterDatasetsUpdate(), buildOrUpdateControllers(), buildOrUpdateElements(), configure(), _dataCheck(), datasetScopeKeys(), _destroy() (+30 more)

### Community 77 - "sendRequest"
Cohesion: 0.06
Nodes (54): aa(), Al(), ar(), bf(), buildTicks(), count(), determineDataLimits(), Dh() (+46 more)

### Community 78 - "push"
Cohesion: 0.06
Nodes (22): BookingsSheet, OverallBreakdownSheet, CreateUser, EditUser, AdminNotificationStatus, HasOne, User, AdminNotificationFeed (+14 more)

### Community 79 - "o8"
Cohesion: 0.05
Nodes (72): A(), aa(), Ac(), add(), addCall(), addResolver(), Bf(), bp() (+64 more)

### Community 80 - "E"
Cohesion: 0.09
Nodes (31): add(), addCall(), addResolver(), applyUpdates(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool() (+23 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (15): Ga(), td(), c(), Ha(), Ka(), La(), ma(), Nc() (+7 more)

### Community 82 - "skwasm.js"
Cohesion: 0.06
Nodes (47): fe(), Ra(), a(), aa(), ab(), ac(), $b(), dc() (+39 more)

### Community 83 - "$1"
Cohesion: 0.01
Nodes (208): $2$alignmentPolicy(), a0n(), a0q(), a11(), a22(), a26(), a2P(), a35() (+200 more)

### Community 84 - "push"
Cohesion: 0.02
Nodes (198): a0d(), a2t(), a36(), a3T(), a4a(), a4T(), a4u(), a5t() (+190 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.12
Nodes (46): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), expandPaddingObject(), getBoundingClientRect(), getClientRectFromClippingAncestor(), getClientRects() (+38 more)

### Community 86 - "ManageProofs"
Cohesion: 0.02
Nodes (193): $0(), $1(), $1$allowPlatformDefault(), $2(), $2$params(), a1H(), a1R(), a1T() (+185 more)

### Community 87 - "Dt"
Cohesion: 0.06
Nodes (17): AdminNotificationController, JsonResponse, BookingController, ScheduleController, TourController, VoucherController, AuthController, AdminMiddleware (+9 more)

### Community 88 - "preload"
Cohesion: 0.07
Nodes (50): acquireContext(), calculateLabelRotation(), _calculatePadding(), _computeGridLineItems(), _computeLabelItems(), computeTickLimit(), _drawArgs(), drawBorder() (+42 more)

### Community 89 - "HasFactory"
Cohesion: 0.13
Nodes (20): add(), clear(), cn(), Da(), _getAnims(), _getLegendItemAt(), gn(), has() (+12 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (13): Ba(), d(), Ja(), Ka(), La(), n(), Pc(), q() (+5 more)

### Community 91 - "b5"
Cohesion: 0.10
Nodes (29): cancelUpload(), componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal() (+21 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (176): a17(), a1A(), a1s(), a6(), a6I(), aC(), aH(), aM() (+168 more)

### Community 93 - ".$2"
Cohesion: 0.02
Nodes (154): $3$color$endFraction$startFraction(), $5(), a05(), a2G(), a2o(), a2s(), a3k(), a3l() (+146 more)

### Community 94 - "draw"
Cohesion: 0.02
Nodes (149): a1c(), a2l(), a32(), a33(), a34(), a4Y(), a50(), a8A() (+141 more)

### Community 95 - "r"
Cohesion: 0.03
Nodes (105): a3F(), a9l(), a_j(), aAe(), acB(), aFJ(), ajb(), ak1() (+97 more)

### Community 96 - ".$1"
Cohesion: 0.08
Nodes (36): second(), base64toBlob(), cleanupModal(), contentIsFromDump(), extractDurationFrom(), extractStreamObjects(), find(), fromQueryString() (+28 more)

### Community 97 - "$0"
Cohesion: 0.22
Nodes (5): BookingsExport, BookingExportController, Illuminate\Http\Response, Maatwebsite\Excel\Concerns\Exportable, Maatwebsite\Excel\Concerns\WithMultipleSheets

### Community 98 - "jU"
Cohesion: 0.04
Nodes (103): a0l(), a0v(), a0w(), a0y(), a3s(), a4O(), a5j(), a5M() (+95 more)

### Community 99 - "M"
Cohesion: 0.06
Nodes (31): A(), b(), be(), c(), e(), f(), fc(), g() (+23 more)

### Community 100 - "get"
Cohesion: 0.05
Nodes (31): ld(), A(), b(), be(), c(), e(), ee(), f() (+23 more)

### Community 101 - "createMorphContext"
Cohesion: 0.09
Nodes (36): appendChild(), cloneNode(), cloneScriptTag(), closestDataStack(), createElement(), createMorphContext(), extractUriAndQueryString(), getFirstNode() (+28 more)

### Community 102 - "navigate_default"
Cohesion: 0.07
Nodes (40): autofocusElementsWithTheAutofocusAttribute(), bindClasses(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject() (+32 more)

### Community 103 - "aG"
Cohesion: 0.06
Nodes (57): $2$isClosing(), a01(), a02(), a73(), a8n(), aB1(), aFV(), ag1() (+49 more)

### Community 104 - "render"
Cohesion: 0.07
Nodes (33): cacheViewForObject(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync(), createElement(), createNodes() (+25 more)

### Community 106 - "Vn"
Cohesion: 0.06
Nodes (49): a0r(), A1(), a41(), a7V(), a9i(), acA(), aCp(), aD() (+41 more)

### Community 107 - "add"
Cohesion: 0.10
Nodes (23): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidRender(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes() (+15 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.08
Nodes (31): A(), b(), Ba(), Bd(), c(), d(), dd(), E() (+23 more)

### Community 109 - "add"
Cohesion: 0.23
Nodes (17): appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes(), findBlockElementAncestors() (+9 more)

### Community 110 - "notification_service.dart"
Cohesion: 0.29
Nodes (7): build, _fetchBookingAndNavigate, _goNext, _goToSchedule, handleNotificationTap, _showPackageDetailsModal, MaterialPageRoute

### Community 111 - "gaf"
Cohesion: 0.25
Nodes (15): dd(), fn(), id(), Is(), Lr(), od(), Or(), Pr() (+7 more)

### Community 112 - "le"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+5 more)

### Community 113 - "bi"
Cohesion: 0.07
Nodes (31): a4R(), a5i(), a8Y(), aCd(), aEP(), aFn(), agc(), agx() (+23 more)

### Community 114 - "fn"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+5 more)

### Community 115 - "Ve"
Cohesion: 0.13
Nodes (31): ad(), as(), Ce(), cs(), ed(), ei(), Es(), gd() (+23 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.09
Nodes (29): Ac(), an(), Au(), ba(), bu(), Dc(), eo(), fo() (+21 more)

### Community 117 - "Ra"
Cohesion: 0.07
Nodes (43): a0p(), a0x(), a7P(), acC(), adF(), ahL(), akP(), alv() (+35 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.07
Nodes (18): CreateAccommodation, CreateAirlineBaggageRule, CreateAppNotification, CreateBooking, CreateDiscount, CreateFerryRoute, CreateGraciaEarningRule, CreateHotel (+10 more)

### Community 120 - "a1"
Cohesion: 0.01
Nodes (193): $2$isClosing(), a06(), a0l(), a27(), a29(), a30(), a36(), a3a() (+185 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.07
Nodes (42): arr(), addInitSelector(), base64toBlob(), cleanupModal(), containsTargets(), contentIsFromDump(), createArrayInstrumentations(), createReactiveEffect() (+34 more)

### Community 122 - "aW_"
Cohesion: 0.02
Nodes (209): a0f(), a2b(), a3O(), a41(), a4m(), a5d(), a7n(), a80() (+201 more)

### Community 123 - "navigate_default"
Cohesion: 0.08
Nodes (32): search(), url(), autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr() (+24 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "dO"
Cohesion: 0.12
Nodes (16): a(), c(), f(), g(), h(), i(), J(), l() (+8 more)

### Community 126 - "gN"
Cohesion: 0.08
Nodes (37): canAcceptDataTransfer(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), compositionDidRequestChangingSelectionToLocationRange(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange(), decreaseNestingLevel() (+29 more)

### Community 127 - "bJ"
Cohesion: 0.02
Nodes (177): $0(), $2$params(), a24(), a2E(), a2t(), a33(), a3D(), a3E() (+169 more)

### Community 128 - "start"
Cohesion: 0.08
Nodes (30): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), ch(), _checkEventBindings(), cu(), dataset() (+22 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.04
Nodes (21): Action, CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, NotifyExpiringVouchers, PurgeExpiredProofs, PurgeExpiredSchedules, RetrofitReferrals (+13 more)

### Community 131 - "gt"
Cohesion: 0.03
Nodes (88): $5(), a13(), a1s(), a9C(), a_k(), aak(), aCF(), ae3() (+80 more)

### Community 132 - "railway-start.sh"
Cohesion: 0.07
Nodes (26): APP_DEBUG, APP_ENV, APP_NAME, APP_URL, CACHE_STORE, DB_CONNECTION, DB_DATABASE, DB_HOST (+18 more)

### Community 133 - "Vehicle"
Cohesion: 0.20
Nodes (24): add(), adjustScroll(), animate(), autoAnimate(), cleanUp(), deletePosition(), forEach(), getCoords() (+16 more)

### Community 134 - "St"
Cohesion: 0.11
Nodes (22): addCleanup(), applyUpdates(), cleanup(), constructor(), dp(), Ee(), extractTypeModifiersAndValue(), hr() (+14 more)

### Community 135 - "d4"
Cohesion: 0.10
Nodes (28): At(), Be(), Ct(), cu(), De(), Do(), du(), En() (+20 more)

### Community 136 - "call"
Cohesion: 0.14
Nodes (26): target(), call(), cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl() (+18 more)

### Community 137 - "d4"
Cohesion: 0.02
Nodes (413): $2(), $2$from$to(), $2$priority$scheduler(), $3(), $4(), a0(), a0A(), a0e() (+405 more)

### Community 138 - "What You Must Do When Invoked"
Cohesion: 0.08
Nodes (24): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+16 more)

### Community 139 - "my_application.cc"
Cohesion: 0.10
Nodes (20): FlPluginRegistry, fl_register_plugins(), main(), my_application_activate(), my_application_class_init(), my_application_dispose(), my_application_init(), my_application_local_command_line() (+12 more)

### Community 140 - "What You Must Do When Invoked"
Cohesion: 0.08
Nodes (24): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+16 more)

### Community 141 - "gP"
Cohesion: 0.10
Nodes (26): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateControllers(), buildOrUpdateElements(), _dataCheck(), _destroy() (+18 more)

### Community 142 - ".$1"
Cohesion: 0.09
Nodes (25): attachmentForFile(), attributesForFile(), didChangeAttributes(), getContentType(), getCurrentTextAttributes(), getHeight(), getHref(), getPreviewURL() (+17 more)

### Community 143 - "echo.js"
Cohesion: 0.06
Nodes (47): a(), ar(), at(), b(), Be(), Ce(), cr(), De() (+39 more)

### Community 144 - "m"
Cohesion: 0.09
Nodes (22): @pragma, _channelDescription, _channelId, _channelName, clearBadge, _firebaseMessagingBackgroundHandler, initialize, NotificationService (+14 more)

### Community 145 - "V"
Cohesion: 0.02
Nodes (94): a0O(), a1u(), a2d(), a48(), a5N(), a5R(), a6H(), a8A() (+86 more)

### Community 146 - "$0"
Cohesion: 0.12
Nodes (23): active(), _animateOptions(), average(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), getCenterPoint() (+15 more)

### Community 147 - "gO"
Cohesion: 0.18
Nodes (19): e(), k(), bb(), bc(), cc(), Eb(), f(), g() (+11 more)

### Community 148 - "has"
Cohesion: 0.08
Nodes (38): directive2(), cleanup(), cloneIfObject(), containsTargets(), customDirectiveHasBeenRegistered(), destroyComponent(), directive(), dirtyTargets() (+30 more)

### Community 149 - "package.json"
Cohesion: 0.09
Nodes (22): alpinejs, apexcharts, concurrently, laravel-vite-plugin, dependencies, alpinejs, apexcharts, devDependencies (+14 more)

### Community 150 - "ViewRecord"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 151 - "replacement_booking_screen.dart"
Cohesion: 0.04
Nodes (46): dart:convert, build, _confirmPassController, createState, _emailController, _isLoading, _isOtpSent, _obscureConfirm (+38 more)

### Community 152 - "kr"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 153 - "$2"
Cohesion: 0.12
Nodes (18): cloneIfObject(), cloneIfObject2(), commitTransaction(), dontRegisterReactiveSideEffects(), effect(), entangle(), flushJobs(), generateEntangleFunction() (+10 more)

### Community 154 - "dB"
Cohesion: 0.14
Nodes (9): Ac(), cc(), Lb(), nb(), Ob(), Rb(), Xd(), yc() (+1 more)

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (345): a(), a01(), a03(), a0c(), a0h(), a0N(), a0V(), a0y() (+337 more)

### Community 156 - "dD"
Cohesion: 0.06
Nodes (36): It(), box(), canBeConsolidatedWith(), canBeGroupedWith(), charAt(), constructor(), fromUCS2String(), getDirection() (+28 more)

### Community 157 - "bw"
Cohesion: 0.06
Nodes (46): af(), ai(), al(), ba(), cancelUpload(), ci(), co(), cr() (+38 more)

### Community 158 - "b6"
Cohesion: 0.03
Nodes (110): a19(), a1G(), a2_(), a2Z(), a7m(), a7s(), a9O(), aad() (+102 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "aJ"
Cohesion: 0.21
Nodes (16): $a(), ab(), b(), bb(), c(), cb(), da(), e() (+8 more)

### Community 161 - "gbq"
Cohesion: 0.28
Nodes (16): D(), f(), g(), k(), l(), m(), mb(), q() (+8 more)

### Community 162 - "tT"
Cohesion: 0.14
Nodes (16): Yn(), Ge(), chartOptionScopes(), constructor(), describe(), Fs(), getDevicePixelRatio(), getMeta() (+8 more)

### Community 163 - "bn"
Cohesion: 0.16
Nodes (16): addCleanup(), constructor(), deepClone(), diff(), extractData(), generateWireObject(), initComponent(), isArray() (+8 more)

### Community 164 - "_each"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 165 - "🚀 Part 1: Backend Setup (Laravel)"
Cohesion: 0.09
Nodes (22): 1. Clone the repository, 1. Navigate to the Flutter folder, 2. Install Flutter Dependencies, 2. Install PHP Dependencies, 3. Install Node Dependencies, 3. Update the API Endpoint, 4. Environment Configuration, 4. Run the App (+14 more)

### Community 166 - "win32_window.cpp"
Cohesion: 0.18
Nodes (13): wchar_t, Scale(), Create, Destroy, Win32Window::Win32Window(), WindowClassRegistrar, class_registered_, GetWindowClass (+5 more)

### Community 167 - "e_"
Cohesion: 0.16
Nodes (10): Ac(), cc(), dc(), nb(), Ob(), Pb(), Qb(), Rd() (+2 more)

### Community 169 - "add"
Cohesion: 0.15
Nodes (19): add(), addCall(), addResolver(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool(), delete() (+11 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.25
Nodes (8): closestComponent(), componentIsMissingProperty(), isComponentRootEl(), isntElement(), morph2(), "node_modules/nprogress/nprogress.js"(), parent(), set()

### Community 173 - "d5"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 174 - "nE"
Cohesion: 0.20
Nodes (8): Any, AppDelegate, Bool, AppDelegate, Bool, FlutterAppDelegate, NSApplication, UIApplication

### Community 175 - "hw"
Cohesion: 0.27
Nodes (5): MyPage, AdminPanelProvider, Filament\Panel, Filament\PanelProvider, Filament\Support\Colors\Color

### Community 176 - "jB"
Cohesion: 0.29
Nodes (6): e(), i(), l(), Ni(), o(), t()

### Community 177 - "aP"
Cohesion: 0.25
Nodes (9): [attribute](), callAndClearComponentDebounces(), each(), evaluate(), evaluateLater(), getElementBoundUtilities(), triggerComponentRequest(), ["x-show" + modifierString]() (+1 more)

### Community 178 - "AdminNotificationController"
Cohesion: 0.16
Nodes (5): BookingStatusChart, RecentActivityWidget, RevenueChartWidget, TopRoutesWidget, Filament\Widgets\Widget

### Community 179 - "flutter.js"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 180 - "flutter_bootstrap.js"
Cohesion: 0.29
Nodes (3): Lb(), Rb(), zb()

### Community 181 - "gb5"
Cohesion: 0.36
Nodes (4): fa(), fb(), pe(), qe()

### Community 183 - "aNl"
Cohesion: 0.29
Nodes (7): Kc(), mc(), oc(), P(), Qc(), rc(), zc()

### Community 185 - "Widget"
Cohesion: 0.50
Nodes (3): confirmAdd, confirmReplace, deleteImage(

### Community 186 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 187 - "d7"
Cohesion: 0.10
Nodes (36): disabled(), buildTicks(), C(), Co(), cr(), diff(), endOf(), Et() (+28 more)

### Community 188 - "bY"
Cohesion: 0.02
Nodes (185): a1O(), a4e(), a8u(), a9N(), a_6(), a_7(), a_d(), a_n() (+177 more)

### Community 190 - "G"
Cohesion: 0.67
Nodes (3): CustomPainter, _GiftBoxPainter, _ZigzagFillPainter

### Community 195 - "Deployment setup"
Cohesion: 0.12
Nodes (15): API routes and auth, Current deployment files, Deployment, Security, and API Route Notes, Deployment security notes, Deployment security summary, Deployment setup, Deployment TODOs, How to use this note (+7 more)

### Community 201 - "scripts"
Cohesion: 0.11
Nodes (19): scripts, dev, post-autoload-dump, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout (+11 more)

### Community 214 - "manifest.json"
Cohesion: 0.13
Nodes (14): background_color, categories, description, display, icons, lang, name, orientation (+6 more)

### Community 216 - "lY"
Cohesion: 0.15
Nodes (9): $, ack(), bdW(), bgH(), bi8(), bhE(), bkA(), bm2() (+1 more)

### Community 219 - "gh3"
Cohesion: 0.14
Nodes (20): oi(), A(), connectedCallback(), disconnectedCallback(), form(), formDisabledCallback(), Ge(), get() (+12 more)

### Community 221 - "mergeNewHead"
Cohesion: 0.22
Nodes (13): cloneScriptTag(), extractUriAndQueryString(), ifTheQueryStringChangedSinceLastRequest(), ignoreAttributes(), injectScriptTagAndWaitForItToFullyLoad(), isAsset(), isScript(), isTracked() (+5 more)

### Community 222 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.14
Nodes (13): file_selector_macos, firebase_core, firebase_messaging, flutter_app_badger, RegisterGeneratedPlugins(), flutter_local_notifications, FlutterPluginRegistry, Foundation (+5 more)

### Community 223 - "wWinMain"
Cohesion: 0.24
Nodes (9): wWinMain(), string, wchar_t, CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16(), _In_, _In_opt_ (+1 more)

### Community 225 - "gc1"
Cohesion: 0.11
Nodes (25): addDebounceOrThrottle(), applyBindingsObject(), attributesOnly(), bind2(), byPriority(), camelCase2(), debounce(), directives() (+17 more)

### Community 231 - "app.js"
Cohesion: 0.21
Nodes (9): C(), D(), J(), O(), U(), v(), X(), d() (+1 more)

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.05
Nodes (65): attachFiles(), beforeinput(), canApplyToDocument(), compositionend(), compositionShouldAcceptFile(), compositionstart(), compositionupdate(), createLinkHTML() (+57 more)

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 247 - "dispatchEvent"
Cohesion: 0.22
Nodes (11): componentsByName(), dispatch(), dispatch2(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf(), dispatchTo() (+3 more)

### Community 250 - "booking-reschedule.blade.php"
Cohesion: 0.20
Nodes (9): closeRefundForm, openRefundForm, selectDepartureAccommodation(, selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectReturnAccommodation(, selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === , setStep(, submitCancelAndRefund (+1 more)

### Community 251 - "MessageHandler"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 258 - "Flutter & Android Studio Setup Guide"
Cohesion: 0.20
Nodes (9): Flutter & Android Studio Setup Guide, Option A: VS Code (Recommended), Option B: Android Studio, 📋 Prerequisites, 🚀 Step 1: Install the Flutter SDK, 📱 Step 2: Install and Configure Android Studio, 🛠️ Step 3: Run Flutter Doctor, 💻 Step 4: Configure Your IDE (+1 more)

### Community 259 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 260 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 261 - "graphify reference: extra exports and benchmark"
Cohesion: 0.22
Nodes (8): graphify reference: extra exports and benchmark, Step 6b - Wiki (only if --wiki flag), Step 7 - Neo4j export (only if --neo4j or --neo4j-push flag), Step 7a - FalkorDB export (only if --falkordb or --falkordb-push flag), Step 7b - SVG export (only if --svg flag), Step 7c - GraphML export (only if --graphml flag), Step 7d - MCP server (only if --mcp flag), Step 8 - Token reduction benchmark (only if total_words > 5000)

### Community 272 - "require-dev"
Cohesion: 0.25
Nodes (8): require-dev, fakerphp/faker, laravel/pail, laravel/pao, laravel/pint, mockery/mockery, nunomaduro/collision, phpunit/phpunit

### Community 273 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install --ignore-scripts, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 278 - "gvm"
Cohesion: 0.33
Nodes (12): g(), l(), m(), p(), t(), u(), w(), x() (+4 more)

### Community 284 - "config"
Cohesion: 0.29
Nodes (7): pestphp/pest-plugin, php-http/discovery, config, allow-plugins, optimize-autoloader, preferred-install, sort-packages

### Community 285 - "psr-4"
Cohesion: 0.29
Nodes (7): autoload, files, psr-4, App\\, Database\\Factories\\, Database\\Seeders\\, app/Support/helpers.php

### Community 288 - "a_K"
Cohesion: 0.20
Nodes (9): 1. Referral Code Generation, 2. Applying a Referral Code (Registration Flow), 3. Reward Trigger — decision needed, 4. Reward Issued to the Referrer, 5. Anti-Abuse Safeguards, 6. Data to Track, 7. Notifications, 8. Open Decisions Before Building (+1 more)

### Community 290 - "How to Update the Android App (APK)"
Cohesion: 0.33
Nodes (5): How to Update the Android App (APK), Step 1: Bump the Version Number, Step 2: Build the New APK, Step 3: Copy the New APK to the Web Server, What happens automatically next?

### Community 292 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 293 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 294 - "graphify reference: query, path, explain"
Cohesion: 0.33
Nodes (5): For /graphify explain, For /graphify path, graphify reference: query, path, explain, Step 0 — Constrained query expansion (REQUIRED before traversal), Step 1 — Traversal

### Community 322 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 323 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 324 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 326 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 327 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 328 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 329 - "_CouponCardClipper"
Cohesion: 0.67
Nodes (3): CustomClipper, _CouponCardClipper, Path

### Community 330 - "Amiga Gracia Flutter App"
Cohesion: 0.50
Nodes (3): Amiga Gracia Flutter App, Getting Started, Railway build

### Community 331 - "RegisterPlugins"
Cohesion: 0.18
Nodes (15): OnCreate, OnDestroy, HWND, Win32Window, child_content_, GetClientArea, GetHandle, OnCreate (+7 more)

### Community 332 - "graphify reference: add a URL and watch a folder"
Cohesion: 0.50
Nodes (3): For /graphify add, For --watch, graphify reference: add a URL and watch a folder

### Community 333 - "graphify reference: commit hook and native CLAUDE.md integration"
Cohesion: 0.50
Nodes (3): For git commit hook, For native CLAUDE.md integration, graphify reference: commit hook and native CLAUDE.md integration

### Community 334 - "graphify reference: incremental update and cluster-only"
Cohesion: 0.50
Nodes (3): For --cluster-only, For --update (incremental re-extraction), graphify reference: incremental update and cluster-only

### Community 457 - "manage-transport-accommodation.blade.php"
Cohesion: 0.50
Nodes (3): switchMode(, updateOperator(, updateOperator(null)

## Knowledge Gaps
- **939 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+934 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `lY` to `.saveDraft`, `.mount`, `d4`, `.$1`, `V`, `draw`, `livewire.min.js`, `select.js`, `aM_`, `dD`, `d`, `deleteInDirection`, `x`, `te`, `getSelectedRange`, `push`, `dH`, `buildTicks`, `support.js`, `setAttribute`, `$1`, `ManageProofs`, `G`, `render`, `Vn`, `dO`, `gN`?**
  _High betweenness centrality (0.038) - this node is a cross-community bridge._
- **Why does `ut()` connect `support.js` to `b`, `select.js`, `aM_`, `constructor`?**
  _High betweenness centrality (0.034) - this node is a cross-community bridge._
- **Why does `a2()` connect `.mount` to `.saveDraft`, `.getActivePromoTicket`, `d4`, `d`, `deleteInDirection`, `dH`, `aQ`, `bY`, `buildTicks`, `RelationManager`, `Controller`, `$1`, `push`, `ManageProofs`, `.$2`, `draw`, `r`, `jU`, `aG`, `Ra`, `a1`, `aW_`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `a()` (e.g. with `loadEntrypoint()` and `_loadJSEntrypoint()`) actually correct?**
  _`a()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 235 inferred relationships involving `a()` (e.g. with `$0()` and `b()`) actually correct?**
  _`a()` has 235 INFERRED edges - model-reasoned connections that need verification._
- **Are the 498 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 498 INFERRED edges - model-reasoned connections that need verification._
- **Are the 496 inferred relationships involving `c()` (e.g. with `$0()` and `$1()`) actually correct?**
  _`c()` has 496 INFERRED edges - model-reasoned connections that need verification._