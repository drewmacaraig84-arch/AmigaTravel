# Graph Report - AmigaTravel  (2026-08-18)

## Corpus Check
- 666 files · ~2,384,682 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17393 nodes · 52705 edges · 537 communities (494 shown, 43 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 8137 edges (avg confidence: 0.56)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `633a6ed5`
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
- tT
- bn
- _each
- 🚀 Part 1: Backend Setup (Laravel)
- win32_window.cpp
- e_
- RunnerTests.swift
- require
- bZ
- d5
- nE
- hw
- flutter.js
- _notify
- AdminPanelProvider.php
- ho
- Widget
- composer.json
- bY
- gaR
- G
- Deployment setup
- rs
- scripts
- color-picker.js
- manifest.json
- GeneratedPluginRegistrant.swift
- wWinMain
- BookingsRelationManager.php
- app.js
- GraciaPointLedgersRelationManager.php
- AccommodationsRelationManager.php
- manifest.json
- PassengersRelationManager.php
- TransportClassesRelationManager.php
- aYd
- FerryRoutesRelationManager.php
- DatesRelationManager.php
- VehicleModelsRelationManager.php
- RedemptionsRelationManager.php
- ThrottleSensitiveActions.php
- manifest.json
- EnsureStaffPermission.php
- booking-reschedule.blade.php
- flutter_export_environment.sh
- Flutter & Android Studio Setup Guide
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- require-dev
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
- `ServiceCancellationTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/User.php
- `Na()` --indirect_call--> `H()`  [INFERRED]
  public/app/canvaskit/skwasm.js → public/app/main.dart.js
- `getType()` --indirect_call--> `Rt()`  [INFERRED]
  public/js/filament/forms/components/file-upload.js → public/app/main.dart.js

## Import Cycles
- None detected.

## Communities (537 total, 43 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, BelongsTo, TourDate, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (461): $3$crossAxisPosition$mainAxisPosition(), a03(), a0a(), a0b(), a0m(), a0O(), a10(), a12() (+453 more)

### Community 2 - ".mount"
Cohesion: 0.01
Nodes (538): d6(), $1(), $2(), $2$priority$scheduler(), $4(), a0(), a1R(), a1V() (+530 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.09
Nodes (38): aa(), Ac(), ba(), Bf(), bl(), Bn(), call(), cf() (+30 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.04
Nodes (23): CreateBookingAction, MyPage, BelongsTo, BelongsToMany, Builder, HasMany, Schedule, BelongsTo (+15 more)

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.15
Nodes (17): average(), getCenterPoint(), getProps(), hasValue(), inRange(), Is(), Kt(), Lt() (+9 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.02
Nodes (150): $5(), a05(), a07(), a0p(), a0x(), a23(), a3T(), a42() (+142 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.03
Nodes (42): Table, BookingController, Request, NotifyAffectedBookerJob, SendBookingConfirmationJob, PaymentProof, UserDashboard, BookingCancellation (+34 more)

### Community 10 - "download.blade.php"
Cohesion: 0.04
Nodes (15): Form, ViewBooking, Form, Table, ServiceCancellationResource, Form, Table, DatePicker (+7 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.02
Nodes (285): $2$priority$scheduler(), a04(), a1J(), a1p(), a1s(), a29(), a4B(), a4C() (+277 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (546): bool get, dart:async, dart:io, DateTime?, double?, double get, 30, _accommodations (+538 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (109): acquireContext(), addControllers(), addPlugins(), addScales(), afterDraw(), alpha(), beforeDatasetDraw(), beforeDatasetsDraw() (+101 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (65): AccommodationResource, Form, Table, AirlineBaggageRuleResource, Form, Table, ApkUserResource, Builder (+57 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (120): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), canAcceptDataTransfer(), canRedo(), canUndo() (+112 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (200): u(), _a(), Aa(), Ac(), Ae(), af(), ai(), al() (+192 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (104): aa(), an(), Ao(), applyStack(), aspectRatio(), beforeDatasetDraw(), beforeDatasetsDraw(), Bn() (+96 more)

### Community 20 - "Booking"
Cohesion: 0.25
Nodes (16): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), fn(), getAlignment(), getAlignmentAxis(), getAlignmentSides(), getAxisLength() (+8 more)

### Community 21 - "livewire.js"
Cohesion: 0.01
Nodes (183): second(), addAssetsToHeadTagOfPage(), addCall(), addCleanup(), addDebounceOrThrottle(), addResolver(), applyUpdates(), _arrayLikeToArray() (+175 more)

### Community 22 - "User.php"
Cohesion: 0.03
Nodes (31): Accommodation, BelongsToMany, BaseTestCase, CreatesApplication, dismissCancellationReminder, RefreshDatabase, requestCancellation, selectRebookingDepartureAccommodation( (+23 more)

### Community 23 - "draw"
Cohesion: 0.04
Nodes (101): ad(), adjustHitBoxes(), ae(), af(), calculateLabelRotation(), _computeAngle(), _computeGridLineItems(), _computeLabelArea() (+93 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (348): a06(), a0k(), a0O(), a0S(), a0t(), a0U(), a0W(), a0x() (+340 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.03
Nodes (70): ae(), applyUpdates(), au(), bc(), bo(), bt(), bu(), constructor() (+62 more)

### Community 26 - "k"
Cohesion: 0.10
Nodes (29): af(), ca(), Cc(), each(), get(), gp(), has(), hp() (+21 more)

### Community 27 - "select.js"
Cohesion: 0.07
Nodes (68): [g](), [x](), $c(), D(), E(), Ea(), g(), H() (+60 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.03
Nodes (120): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), breakFormattedBlock(), breaksOnReturn() (+112 more)

### Community 29 - "_update"
Cohesion: 0.04
Nodes (84): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+76 more)

### Community 30 - "fromObject"
Cohesion: 0.03
Nodes (127): _a(), abutsStart(), after(), afterAutoSkip(), Ag(), Ai(), Al(), before() (+119 more)

### Community 31 - "constructor"
Cohesion: 0.04
Nodes (66): Bl(), cf(), clone(), create(), Dl(), dtFormatter(), eg(), el() (+58 more)

### Community 32 - "d"
Cohesion: 0.03
Nodes (102): a0d(), a0n(), a0q(), a22(), a35(), a3B(), a4C(), a6H() (+94 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (306): $2$from$to(), $3(), a(), a09(), a1B(), a1E(), a1q(), a2q() (+298 more)

### Community 34 - "H"
Cohesion: 0.01
Nodes (182): $1$1(), $5(), A1(), a13(), a20(), A3(), a32(), a35() (+174 more)

### Community 35 - "TransportClass"
Cohesion: 0.07
Nodes (41): a1k(), a4H(), a64(), a6P(), aB8(), aBk(), adF(), ahL() (+33 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.08
Nodes (33): a19(), a9V(), aAs(), aG2(), anw(), aod(), at8(), aUd() (+25 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.02
Nodes (223): directive2(), add(), addAssetsToHeadTagOfPage(), addCall(), addCleanup(), addResolver(), applyUpdates(), [attribute]() (+215 more)

### Community 38 - "add"
Cohesion: 0.06
Nodes (74): target(), add(), bufferPoolingForFiveMs(), call(), checkIdentityKeys(), cleanupAttributes(), clear(), colocateCommitsByComponent() (+66 more)

### Community 39 - "User"
Cohesion: 0.06
Nodes (41): a(), a(), a(), a(), a(), alpha(), At(), be() (+33 more)

### Community 40 - "a3"
Cohesion: 0.03
Nodes (23): ManageWebsiteSettings, Form, CreateFerryRoute, EditFerryRoute, Operator, Builder, HasMany, Vehicle (+15 more)

### Community 41 - "x"
Cohesion: 0.09
Nodes (80): Sg(), ad(), at(), B(), br(), Bt(), ca(), cd() (+72 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (327): a00(), a0E(), a0f(), a0g(), a0h(), a0i(), a0k(), a11() (+319 more)

### Community 43 - "gv"
Cohesion: 0.03
Nodes (121): arr(), addInitSelector(), addRootSelector(), addScopeToNode(), allSelectors(), applyBindingsObject(), [attribute](), attributesOnly() (+113 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (11): Bi(), bn(), ji(), kd(), Ri(), te(), Vi(), Xc() (+3 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.07
Nodes (41): search(), url(), cancelUpload(), cleanupModal(), contentIsFromDump(), extractStreamObjects(), getCsrfToken(), getUpdateUri() (+33 more)

### Community 46 - "_update"
Cohesion: 0.08
Nodes (43): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+35 more)

### Community 47 - "ListRecords"
Cohesion: 0.04
Nodes (21): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+13 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.05
Nodes (68): $a(), ab(), Ac(), Ad(), b(), bb(), bc(), c() (+60 more)

### Community 49 - "getContext"
Cohesion: 0.05
Nodes (55): Ac(), an(), Au(), average(), ba(), beforeDraw(), bu(), dataset() (+47 more)

### Community 50 - "file-upload.js"
Cohesion: 0.05
Nodes (55): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+47 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.04
Nodes (112): attachFiles(), attachmentManagerDidRequestRemovalOfAttachment(), backspace(), Ca(), canDecreaseBlockAttributeLevel(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment() (+104 more)

### Community 52 - "AC"
Cohesion: 0.06
Nodes (51): Yn(), Ge(), _a(), active(), add(), _animateOptions(), ba(), _cachedScopes() (+43 more)

### Community 53 - "push"
Cohesion: 0.10
Nodes (37): adjustHitBoxes(), afterDraw(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight(), da(), draw() (+29 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.04
Nodes (72): $a(), ab(), Ac(), Ad(), b(), bb(), bc(), c() (+64 more)

### Community 55 - "Voucher"
Cohesion: 0.06
Nodes (42): disabled(), afterAutoSkip(), beforeDraw(), Bi(), buildLookupTable(), buildTicks(), determineDataLimits(), diff() (+34 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (29): A(), Ad(), b(), c(), d(), E(), eb(), f() (+21 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (313): $1(), $2$alignmentPolicy(), a0B(), a0D(), a11(), a1m(), a1x(), a27() (+305 more)

### Community 59 - "aQ"
Cohesion: 0.12
Nodes (6): BelongsTo, Builder, HasMany, ServiceCancellation, Collection, ServiceCancellationManager

### Community 60 - "buildTicks"
Cohesion: 0.07
Nodes (51): a00(), a07(), a1l(), a2f(), a2g(), a2h(), a2i(), a5e() (+43 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.04
Nodes (37): RetroactiveGraciaPoints, GraciaPointsController, Request, NotificationController, JsonResponse, Request, JsonResponse, Request (+29 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (165): ut(), Nt(), Qt(), _a(), aa(), Ae(), ai(), apply() (+157 more)

### Community 63 - "gO"
Cohesion: 0.06
Nodes (63): a_k(), ad(), agE(), ajk(), anS(), ao8(), aro(), b2B() (+55 more)

### Community 64 - "RelationManager"
Cohesion: 0.02
Nodes (223): a05(), a36(), a39(), a3a(), a3b(), a3I(), a3K(), a3y() (+215 more)

### Community 65 - "I"
Cohesion: 0.12
Nodes (24): afterDatasetsUpdate(), _d(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), _handleEvent() (+16 more)

### Community 66 - "i"
Cohesion: 0.02
Nodes (170): $3$crossAxisPosition$mainAxisPosition(), a0Z(), a15(), a1G(), a2_(), a24(), a2r(), a2s() (+162 more)

### Community 67 - "get"
Cohesion: 0.03
Nodes (84): a0r(), A1(), a16(), a1j(), a41(), a48(), a4d(), a6G() (+76 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (61): ForgotPasswordScreen, _ForgotPasswordScreenState, ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState (+53 more)

### Community 69 - "setAttribute"
Cohesion: 0.05
Nodes (71): It(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), attributeChangedCallback(), box(), canBeGrouped() (+63 more)

### Community 70 - "a"
Cohesion: 0.02
Nodes (144): a0A(), a0Q(), a1u(), a3P(), a48(), a4h(), a5N(), a69() (+136 more)

### Community 71 - "a5"
Cohesion: 0.07
Nodes (54): buildOrUpdateScales(), C(), cl(), Co(), _computeLabelSizes(), cr(), Ct(), D() (+46 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (25): observer(), actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo() (+17 more)

### Community 73 - "s"
Cohesion: 0.06
Nodes (48): ap(), bd(), Bi(), bp(), Br(), children(), cleanup(), fe() (+40 more)

### Community 74 - "EditRecord"
Cohesion: 0.05
Nodes (19): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditGraciaEarningRule, EditHotel, OperatorResource (+11 more)

### Community 75 - "Controller"
Cohesion: 0.08
Nodes (32): addControllers(), addElements(), addPlugins(), addScales(), beforeUpdate(), buildOrUpdateControllers(), buildOrUpdateElements(), _dataCheck() (+24 more)

### Community 76 - "updateElements"
Cohesion: 0.05
Nodes (56): afterDatasetsUpdate(), as(), bc(), _calculateBarIndexPixels(), calculateCircumference(), _circumference(), countVisibleElements(), _createItems() (+48 more)

### Community 77 - "sendRequest"
Cohesion: 0.02
Nodes (132): $1$1(), $2$alignmentPolicy(), a1m(), a1N(), a1o(), a3O(), a4Q(), a4R() (+124 more)

### Community 78 - "push"
Cohesion: 0.05
Nodes (22): CreateUser, EditUser, AuthController, Request, AdminNotificationStatus, BelongsTo, HasMany, HasOne (+14 more)

### Community 79 - "o8"
Cohesion: 0.12
Nodes (21): cancelUpload(), Di(), gt(), handleS3PreSignedUrl(), handleSignedUrl(), Hi(), ji(), makeRequest() (+13 more)

### Community 80 - "E"
Cohesion: 0.27
Nodes (13): canDecreaseNestingLevel(), canIncreaseNestingLevel(), decreaseNestingLevel(), formatIndent(), formatOutdent(), getLastNestableAttribute(), getListItemAttributes(), getNestableAttributes() (+5 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (14): x(), ma(), c(), Ka(), La(), ma(), Nc(), p() (+6 more)

### Community 82 - "skwasm.js"
Cohesion: 0.05
Nodes (65): e(), fe(), Ra(), a(), aa(), ab(), ac(), $b() (+57 more)

### Community 83 - "$1"
Cohesion: 0.04
Nodes (64): a2P(), a3W(), a5X(), a7J(), a8A(), a8B(), a9Y(), a_j() (+56 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.12
Nodes (48): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), evaluate2(), expandPaddingObject(), getBoundingClientRect(), getClientRectFromClippingAncestor() (+40 more)

### Community 86 - "ManageProofs"
Cohesion: 0.01
Nodes (326): a0Z(), a1U(), a21(), a2B(), a2E(), a2G(), a2H(), a2K() (+318 more)

### Community 87 - "Dt"
Cohesion: 0.07
Nodes (16): ListTours, Table, AccommodationController, BookingCalculateController, DiscountController, PromotionController, Request, ScheduleController (+8 more)

### Community 88 - "preload"
Cohesion: 0.06
Nodes (61): acquireContext(), ar(), calculateLabelRotation(), _calculatePadding(), _computeAngle(), _computeGridLineItems(), _computeLabelItems(), computeTickLimit() (+53 more)

### Community 89 - "HasFactory"
Cohesion: 0.09
Nodes (10): Request, VoucherController, Discount, HasMany, BelongsTo, HasMany, Voucher, BelongsTo (+2 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.05
Nodes (16): Ba(), d(), Ga(), Ja(), Ka(), La(), n(), Pc() (+8 more)

### Community 91 - "b5"
Cohesion: 0.29
Nodes (6): e(), i(), l(), Ni(), o(), t()

### Community 92 - "G"
Cohesion: 0.02
Nodes (200): a17(), a1A(), a1P(), a1s(), a1y(), a6(), a6I(), aC() (+192 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (363): $3$color$endFraction$startFraction(), a0S(), a1D(), a1f(), a2l(), a2Z(), a33(), a34() (+355 more)

### Community 94 - "draw"
Cohesion: 0.02
Nodes (165): a1c(), a1T(), a2M(), a2N(), a4Y(), a5W(), a6y(), a7V() (+157 more)

### Community 95 - "r"
Cohesion: 0.12
Nodes (13): BookingsSheet, Collection, Worksheet, OverallBreakdownSheet, Collection, Worksheet, FromArray, FromCollection (+5 more)

### Community 96 - ".$1"
Cohesion: 0.10
Nodes (24): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), ch(), _checkEventBindings(), cu(), Du() (+16 more)

### Community 97 - "$0"
Cohesion: 0.06
Nodes (51): aspectRatio(), C(), Ce(), co(), _computeLabelSizes(), De(), eh(), ei() (+43 more)

### Community 98 - "jU"
Cohesion: 0.03
Nodes (108): a0l(), a0u(), a0v(), a0w(), a0y(), a4O(), a5j(), a5M() (+100 more)

### Community 99 - "M"
Cohesion: 0.05
Nodes (35): A(), b(), be(), c(), e(), f(), fc(), g() (+27 more)

### Community 100 - "get"
Cohesion: 0.05
Nodes (31): ld(), A(), b(), be(), c(), e(), ee(), f() (+23 more)

### Community 101 - "createMorphContext"
Cohesion: 0.08
Nodes (38): appendChild(), cloneNode(), cloneScriptTag(), closestComponent(), closestDataStack(), componentIsMissingProperty(), createElement(), createMorphContext() (+30 more)

### Community 102 - "navigate_default"
Cohesion: 0.11
Nodes (23): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+15 more)

### Community 103 - "aG"
Cohesion: 0.02
Nodes (130): $0(), $1$allowPlatformDefault(), $2$isClosing(), $2$params(), a01(), a02(), a18(), a1H() (+122 more)

### Community 104 - "render"
Cohesion: 0.06
Nodes (42): cacheViewForObject(), canSyncDocumentView(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync(), createElement() (+34 more)

### Community 107 - "add"
Cohesion: 0.11
Nodes (22): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidChangeDocument() (+14 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.05
Nodes (50): Bd(), Cd(), Ed(), Fd(), Gd(), Hd(), Jd(), Kd() (+42 more)

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
Cohesion: 0.06
Nodes (74): A(), add(), addCall(), addCleanup(), addResolver(), al(), At(), Be() (+66 more)

### Community 114 - "fn"
Cohesion: 0.06
Nodes (9): d(), n(), Pc(), q(), r(), Ra, t(), u() (+1 more)

### Community 115 - "Ve"
Cohesion: 0.10
Nodes (42): ad(), as(), ["@blur"](), c(), Ce(), ["@change"](), cs(), Ct() (+34 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.05
Nodes (62): addElements(), as(), At(), Bi(), Bs(), buildOrUpdateControllers(), buildOrUpdateElements(), Ca() (+54 more)

### Community 117 - "Ra"
Cohesion: 0.12
Nodes (19): ai(), appendChild(), Dl(), effect(), Fi(), fl(), Hd(), insertBefore() (+11 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.11
Nodes (23): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), cs() (+15 more)

### Community 120 - "a1"
Cohesion: 0.02
Nodes (123): a08(), a0l(), a30(), a3L(), a3x(), a5W(), a62(), a6e() (+115 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.16
Nodes (5): BookingStatusChart, RecentActivityWidget, RevenueChartWidget, TopRoutesWidget, Widget

### Community 122 - "aW_"
Cohesion: 0.02
Nodes (186): a0f(), a2b(), a3O(), a4m(), a5A(), a5Y(), a60(), a64() (+178 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "dO"
Cohesion: 0.12
Nodes (16): a(), c(), f(), g(), h(), i(), J(), l() (+8 more)

### Community 126 - "gN"
Cohesion: 0.14
Nodes (19): ArrowLeft(), ArrowRight(), editAttachment(), expandSelectionInDirection(), findNodeAndOffsetFromLocation(), getAttachmentAtRange(), getExpandedRangeInDirection(), getSignificantNodesForIndex() (+11 more)

### Community 127 - "bJ"
Cohesion: 0.02
Nodes (131): $0(), $2$params(), a1Q(), a25(), a2E(), a2t(), a33(), a3D() (+123 more)

### Community 128 - "start"
Cohesion: 0.14
Nodes (17): attachmentForFile(), attributesForFile(), didChangeAttributes(), getContentType(), getHeight(), getHref(), getPreviewURL(), getURL() (+9 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.07
Nodes (11): CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, NotifyExpiringVouchers, PurgeExpiredProofs, PurgeExpiredSchedules, RetrofitReferrals, SendPaymentReminders (+3 more)

### Community 131 - "gt"
Cohesion: 0.20
Nodes (4): BelongsTo, Builder, HasMany, PromotionalTicket

### Community 132 - "railway-start.sh"
Cohesion: 0.07
Nodes (26): APP_DEBUG, APP_ENV, APP_NAME, APP_URL, CACHE_STORE, DB_CONNECTION, DB_DATABASE, DB_HOST (+18 more)

### Community 133 - "Vehicle"
Cohesion: 0.20
Nodes (24): add(), adjustScroll(), animate(), autoAnimate(), cleanUp(), deletePosition(), forEach(), getCoords() (+16 more)

### Community 134 - "St"
Cohesion: 0.17
Nodes (8): $, ack(), bdW(), bgH(), bi8(), bhE(), bkA(), bm2()

### Community 135 - "d4"
Cohesion: 0.24
Nodes (10): tl(), ac(), Ai(), ca(), Li(), oc(), ro(), sc() (+2 more)

### Community 136 - "call"
Cohesion: 0.38
Nodes (7): bs(), ds(), Fr(), ft(), Ii(), ni(), oi()

### Community 137 - "d4"
Cohesion: 0.02
Nodes (399): $2(), $2$from$to(), $3(), $4(), a0(), a0e(), a0i(), a0j() (+391 more)

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
Cohesion: 0.06
Nodes (42): add(), ar(), _cachedScopes(), chartOptionScopes(), constructor(), describe(), divideEqually(), Ec() (+34 more)

### Community 142 - ".$1"
Cohesion: 0.08
Nodes (26): canSetCurrentTextAttribute(), compositionControllerDidRequestDeselectingAttachment(), compositionDidStartEditingAttachment(), cut(), didClickAttachment(), dragstart(), findAttachmentForElement(), getAttachmentAndPositionById() (+18 more)

### Community 143 - "echo.js"
Cohesion: 0.06
Nodes (48): a(), ar(), at(), b(), Be(), Ce(), cr(), De() (+40 more)

### Community 144 - "m"
Cohesion: 0.09
Nodes (22): @pragma, _channelDescription, _channelId, _channelName, clearBadge, _firebaseMessagingBackgroundHandler, initialize, NotificationService (+14 more)

### Community 145 - "V"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 146 - "$0"
Cohesion: 0.60
Nodes (3): AdminMiddleware, Closure, Request

### Community 147 - "gO"
Cohesion: 0.60
Nodes (3): Closure, Request, UpdateUserActivity

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
Cohesion: 0.60
Nodes (3): Closure, Request, UseAdminGuard

### Community 154 - "dB"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (368): $2$isClosing(), a(), a01(), a02(), a03(), a0c(), a0g(), a0h() (+360 more)

### Community 156 - "dD"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 157 - "bw"
Cohesion: 0.15
Nodes (19): Cl(), En(), gf(), Gr(), io(), jo(), l(), ll() (+11 more)

### Community 158 - "b6"
Cohesion: 0.05
Nodes (52): a41(), a5o(), a_m(), aCd(), aCo(), aCp(), aCz(), adr() (+44 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 162 - "tT"
Cohesion: 0.19
Nodes (13): A(), form(), Ge(), ks(), labels(), name(), reportValidity(), required() (+5 more)

### Community 163 - "bn"
Cohesion: 0.39
Nodes (8): generateEvaluatorFromFunction(), generateEvaluatorFromString(), generateFunctionFromString(), handleError(), normalEvaluator(), params(), runIfTypeOfFunction(), tryCatch()

### Community 164 - "_each"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 165 - "🚀 Part 1: Backend Setup (Laravel)"
Cohesion: 0.09
Nodes (22): 1. Clone the repository, 1. Navigate to the Flutter folder, 2. Install Flutter Dependencies, 2. Install PHP Dependencies, 3. Install Node Dependencies, 3. Update the API Endpoint, 4. Environment Configuration, 4. Run the App (+14 more)

### Community 166 - "win32_window.cpp"
Cohesion: 0.18
Nodes (13): wchar_t, Scale(), Create, Destroy, Win32Window::Win32Window(), WindowClassRegistrar, class_registered_, GetWindowClass (+5 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.06
Nodes (50): aa(), Ah(), applyStack(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference(), _calculatePadding(), _circumference() (+42 more)

### Community 173 - "d5"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 174 - "nE"
Cohesion: 0.20
Nodes (8): Any, AppDelegate, Bool, AppDelegate, Bool, FlutterAppDelegate, NSApplication, UIApplication

### Community 179 - "flutter.js"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 180 - "_notify"
Cohesion: 0.20
Nodes (14): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), kh(), _notify() (+6 more)

### Community 181 - "AdminPanelProvider.php"
Cohesion: 0.02
Nodes (56): Action, AdminNotifications, ManagePaymentSettings, Form, ManageProofs, Collection, Form, ManageRebookings (+48 more)

### Community 182 - "ho"
Cohesion: 0.15
Nodes (8): BookingsExport, AdminNotificationController, JsonResponse, Request, BookingExportController, Response, Exportable, WithMultipleSheets

### Community 185 - "Widget"
Cohesion: 0.50
Nodes (3): confirmAdd, confirmReplace, deleteImage(

### Community 186 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 188 - "bY"
Cohesion: 0.02
Nodes (186): a1O(), a4e(), a8u(), a9N(), a_6(), a_7(), a_d(), a_n() (+178 more)

### Community 189 - "gaR"
Cohesion: 0.33
Nodes (5): BookingsRelationManager, Table, LoginHistoriesRelationManager, Table, RelationManager

### Community 190 - "G"
Cohesion: 0.67
Nodes (3): CustomPainter, _GiftBoxPainter, _ZigzagFillPainter

### Community 195 - "Deployment setup"
Cohesion: 0.12
Nodes (15): API routes and auth, Current deployment files, Deployment, Security, and API Route Notes, Deployment security notes, Deployment security summary, Deployment setup, Deployment TODOs, How to use this note (+7 more)

### Community 201 - "scripts"
Cohesion: 0.11
Nodes (19): scripts, dev, post-root-package-install, post-update-cmd, pre-package-uninstall, setup, test, Composer\\Config::disableProcessTimeout (+11 more)

### Community 214 - "manifest.json"
Cohesion: 0.13
Nodes (14): background_color, categories, description, display, icons, lang, name, orientation (+6 more)

### Community 222 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.14
Nodes (13): file_selector_macos, firebase_core, firebase_messaging, flutter_app_badger, RegisterGeneratedPlugins(), flutter_local_notifications, FlutterPluginRegistry, Foundation (+5 more)

### Community 223 - "wWinMain"
Cohesion: 0.24
Nodes (9): wWinMain(), string, wchar_t, CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16(), _In_, _In_opt_ (+1 more)

### Community 225 - "BookingsRelationManager.php"
Cohesion: 0.47
Nodes (3): BookingsRelationManager, Form, Table

### Community 231 - "app.js"
Cohesion: 0.21
Nodes (9): C(), D(), J(), O(), U(), v(), X(), d() (+1 more)

### Community 234 - "GraciaPointLedgersRelationManager.php"
Cohesion: 0.47
Nodes (3): GraciaPointLedgersRelationManager, Form, Table

### Community 235 - "AccommodationsRelationManager.php"
Cohesion: 0.47
Nodes (3): AccommodationsRelationManager, Form, Table

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 237 - "PassengersRelationManager.php"
Cohesion: 0.47
Nodes (3): PassengersRelationManager, Form, Table

### Community 238 - "TransportClassesRelationManager.php"
Cohesion: 0.47
Nodes (3): Form, Table, TransportClassesRelationManager

### Community 239 - "aYd"
Cohesion: 0.06
Nodes (41): beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), dragend(), elementDidMutate(), end() (+33 more)

### Community 240 - "FerryRoutesRelationManager.php"
Cohesion: 0.47
Nodes (3): FerryRoutesRelationManager, Form, Table

### Community 241 - "DatesRelationManager.php"
Cohesion: 0.47
Nodes (3): DatesRelationManager, Form, Table

### Community 242 - "VehicleModelsRelationManager.php"
Cohesion: 0.47
Nodes (3): Form, Table, VehicleModelsRelationManager

### Community 243 - "RedemptionsRelationManager.php"
Cohesion: 0.47
Nodes (3): Form, Table, RedemptionsRelationManager

### Community 244 - "ThrottleSensitiveActions.php"
Cohesion: 0.53
Nodes (4): Closure, Request, Response, ThrottleSensitiveActions

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 248 - "EnsureStaffPermission.php"
Cohesion: 0.60
Nodes (3): EnsureStaffPermission, Closure, Request

### Community 250 - "booking-reschedule.blade.php"
Cohesion: 0.20
Nodes (9): closeRefundForm, openRefundForm, selectDepartureAccommodation(, selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectReturnAccommodation(, selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === , setStep(, submitCancelAndRefund (+1 more)

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
- **943 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+938 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **43 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `St` to `start`, `.mount`, `d4`, `schedules.blade.php`, `rich-editor.js`, `draw`, `livewire.min.js`, `aM_`, `select.js`, `d`, `Schedule`, `x`, `te`, `getSelectedRange`, `dH`, `buildTicks`, `support.js`, `i`, `get`, `setAttribute`, `a`, `G`, `draw`, `render`, `dO`?**
  _High betweenness centrality (0.045) - this node is a cross-community bridge._
- **Why does `a2()` connect `.mount` to `.saveDraft`, `.getActivePromoTicket`, `d4`, `schedules.blade.php`, `b6`, `d`, `Schedule`, `TransportClass`, `dH`, `bY`, `buildTicks`, `RelationManager`, `i`, `ManageProofs`, `G`, `.$2`, `draw`, `jU`, `aG`, `a1`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `H()` connect `dH` to `RelationManager`, `i`, `.mount`, `H`, `deleteInDirection`, `a`, `get`, `d4`, `schedules.blade.php`, `skwasm.js`, `bY`, `b`, `aW_`, `aM_`, `a1`, `.$2`, `b6`, `bJ`?**
  _High betweenness centrality (0.030) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `a()` (e.g. with `loadEntrypoint()` and `_loadJSEntrypoint()`) actually correct?**
  _`a()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 235 inferred relationships involving `a()` (e.g. with `$0()` and `b()`) actually correct?**
  _`a()` has 235 INFERRED edges - model-reasoned connections that need verification._
- **Are the 498 inferred relationships involving `b()` (e.g. with `main.dart.js` and `$0()`) actually correct?**
  _`b()` has 498 INFERRED edges - model-reasoned connections that need verification._
- **Are the 496 inferred relationships involving `c()` (e.g. with `$0()` and `$1()`) actually correct?**
  _`c()` has 496 INFERRED edges - model-reasoned connections that need verification._