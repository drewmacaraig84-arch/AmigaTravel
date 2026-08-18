# Graph Report - AmigaTravel  (2026-08-18)

## Corpus Check
- 663 files · ~2,381,824 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17256 nodes · 52690 edges · 538 communities (498 shown, 40 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 8139 edges (avg confidence: 0.56)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `86327ecb`
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
- Flutter & Android Studio Setup Guide
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- require-dev
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
- `ServiceCancellationTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/User.php
- `Na()` --indirect_call--> `H()`  [INFERRED]
  public/app/canvaskit/skwasm.js → public/app/main.dart.js
- `getType()` --indirect_call--> `Rt()`  [INFERRED]
  public/js/filament/forms/components/file-upload.js → public/app/main.dart.js

## Import Cycles
- None detected.

## Communities (538 total, 40 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (511): $3$crossAxisPosition$mainAxisPosition(), a0a(), a0b(), a0d(), a0l(), a0O(), a10(), a12() (+503 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (551): $4(), a0(), a1V(), a2(), a2A(), a2D(), a2E(), a3() (+543 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.02
Nodes (152): a02(), a0A(), a0Q(), a1I(), a1u(), a2d(), a48(), a4h() (+144 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.02
Nodes (20): CreateBookingAction, Accommodation, Passenger, HasMany, PromotionalTicket, Schedule, ScheduleTransportClass, ServiceCancellation (+12 more)

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.08
Nodes (31): a(), a(), a(), a(), a(), At(), beforeDraw(), dataset() (+23 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.05
Nodes (59): a07(), a0x(), a54(), a6W(), a7H(), a82(), aaF(), aAv() (+51 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.03
Nodes (31): NotifyAffectedBookerJob, SendBookingConfirmationJob, PaymentProof, UserDashboard, BookingCancellation, self, BookingConfirmation, BookingCreated (+23 more)

### Community 10 - "download.blade.php"
Cohesion: 0.06
Nodes (14): ViewBooking, AccommodationController, PromoImageManager, storage_asset_path(), dismissCancellationReminder, Illuminate\Support\HtmlString, requestCancellation, selectRebookingDepartureAccommodation( (+6 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.20
Nodes (5): Carbon, ScheduleCsvImportService, normalize_operator_name(), operator_is_ferry(), Carbon\Carbon

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (548): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+540 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (119): acquireContext(), addControllers(), addPlugins(), addScales(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), bh() (+111 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (28): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+20 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (119): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), canRedo(), canUndo() (+111 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (198): u(), _a(), Aa(), Ac(), Ae(), af(), ai(), al() (+190 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (99): aa(), active(), an(), _animateOptions(), Ao(), applyStack(), aspectRatio(), beforeDatasetDraw() (+91 more)

### Community 20 - "Booking"
Cohesion: 0.22
Nodes (18): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), evaluate2(), fn(), getAlignment(), getAlignmentAxis(), getAlignmentSides() (+10 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (108): input(), addAssetsToHeadTagOfPage(), addCall(), addResolver(), _arrayLikeToArray(), _arrayWithoutHoles(), [attribute](), bindInputValue() (+100 more)

### Community 22 - "User.php"
Cohesion: 0.02
Nodes (54): ManageRebookings, ViewApkUser, BookingsRelationManager, GraciaPointLedgersRelationManager, AccommodationsRelationManager, PassengersRelationManager, TransportClassesRelationManager, ViewInquiry (+46 more)

### Community 23 - "draw"
Cohesion: 0.05
Nodes (90): adjustHitBoxes(), ae(), af(), afterDraw(), calculateLabelRotation(), _computeGridLineItems(), _computeLabelArea(), _computeTitleHeight() (+82 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (334): a0k(), a0O(), a0t(), a0x(), a14(), a1B(), a1C(), a1E() (+326 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.02
Nodes (86): ae(), appendChild(), au(), bc(), bl(), ["@blur"](), bo(), bt() (+78 more)

### Community 27 - "select.js"
Cohesion: 0.05
Nodes (57): [x](), $c(), ca(), E(), Ea(), g(), Id(), J() (+49 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.04
Nodes (110): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), breakFormattedBlock(), canBeGroupedWith() (+102 more)

### Community 29 - "_update"
Cohesion: 0.03
Nodes (112): addBox(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+104 more)

### Community 30 - "fromObject"
Cohesion: 0.03
Nodes (126): _a(), abutsStart(), after(), afterAutoSkip(), Ag(), Ai(), Al(), before() (+118 more)

### Community 31 - "constructor"
Cohesion: 0.05
Nodes (63): Bl(), cf(), clone(), create(), dtFormatter(), eg(), el(), eras() (+55 more)

### Community 32 - "d"
Cohesion: 0.02
Nodes (199): a14(), a1c(), a1D(), a1f(), a1T(), a1x(), a2M(), a2N() (+191 more)

### Community 33 - "Schedule"
Cohesion: 0.04
Nodes (83): $2$from$to(), a0S(), a2l(), a2q(), a38(), a39(), a4X(), a5S() (+75 more)

### Community 34 - "H"
Cohesion: 0.01
Nodes (186): $1$1(), $2$priority$scheduler(), $3(), $5(), A1(), a13(), A3(), a32() (+178 more)

### Community 35 - "TransportClass"
Cohesion: 0.08
Nodes (33): a10(), a46(), a4t(), a9W(), anw(), aod(), at8(), aUd() (+25 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.11
Nodes (28): as(), cr(), format(), getLabelAndValue(), getLabelForValue(), getLabels(), Gi(), gt() (+20 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (38): addAssetsToHeadTagOfPage(), applyUpdates(), [attribute](), callAndClearComponentDebounces(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), dataSet(), disableForm() (+30 more)

### Community 38 - "add"
Cohesion: 0.06
Nodes (68): add(), bufferPoolingForFiveMs(), call(), checkIdentityKeys(), cleanupAttributes(), clear(), colocateCommitsByComponent(), containsTargets() (+60 more)

### Community 39 - "User"
Cohesion: 0.07
Nodes (35): Yn(), alpha(), be(), chartOptionScopes(), constructor(), describe(), en(), fe() (+27 more)

### Community 40 - "a3"
Cohesion: 0.02
Nodes (28): Action, AdminNotifications, ManagePaymentSettings, ManageProofs, ManageTransportAccommodation, ManageWebsiteSettings, MyPage, OverallReports (+20 more)

### Community 41 - "x"
Cohesion: 0.09
Nodes (88): Sg(), ad(), at(), B(), br(), Bt(), cd(), Cr() (+80 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (290): $2(), $3(), $5(), a00(), a0E(), a0f(), a0k(), a15() (+282 more)

### Community 43 - "gv"
Cohesion: 0.05
Nodes (67): addRootSelector(), addScopeToNode(), attributeShouldntBePreservedIfFalsy(), bind(), bindAttribute(), bindAttributeAndProperty(), camelCase(), clone() (+59 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (9): Pr(), Bi(), bn(), ji(), Ri(), te(), Vi(), Xc() (+1 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.09
Nodes (27): componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf() (+19 more)

### Community 46 - "_update"
Cohesion: 0.09
Nodes (35): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+27 more)

### Community 47 - "ListRecords"
Cohesion: 0.05
Nodes (20): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+12 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.05
Nodes (56): $a(), ab(), Ac(), Ad(), b(), bb(), bc(), c() (+48 more)

### Community 49 - "getContext"
Cohesion: 0.08
Nodes (36): ArrowLeft(), ArrowRight(), backspace(), d(), delete(), deleteByComposition(), deleteByCut(), deleteCompositionText() (+28 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (54): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+46 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.07
Nodes (50): attachmentManagerDidRequestRemovalOfAttachment(), breaksOnReturn(), Ca(), compositionControllerDidRequestRemovalOfAttachment(), decreaseBlockAttributeLevel(), decreaseListLevel(), drop(), findRangesOfBlocks() (+42 more)

### Community 52 - "AC"
Cohesion: 0.08
Nodes (39): _a(), addElements(), ba(), beforeUpdate(), buildOrUpdateControllers(), buildOrUpdateElements(), _cachedScopes(), configure() (+31 more)

### Community 53 - "push"
Cohesion: 0.06
Nodes (58): acquireContext(), adjustHitBoxes(), afterDraw(), bc(), Bl(), clear(), _computeGridLineItems(), _computeLabelArea() (+50 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.08
Nodes (16): Ad(), bc(), fe(), get(), Kc(), mc(), oc(), P() (+8 more)

### Community 55 - "Voucher"
Cohesion: 0.09
Nodes (35): ar(), _calculateBarIndexPixels(), _computeAngle(), countVisibleElements(), datasetAnimationScopeKeys(), getBasePixel(), getBasePosition(), getBaseValue() (+27 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (30): A(), b(), Ba(), c(), d(), E(), eb(), Ed() (+22 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (523): $1(), a0B(), a0D(), a0f(), a11(), a1J(), a1m(), a1p() (+515 more)

### Community 59 - "aQ"
Cohesion: 0.02
Nodes (158): $1$1(), $2$priority$scheduler(), a1m(), a1N(), a2K(), a3O(), a4R(), a6Z() (+150 more)

### Community 60 - "buildTicks"
Cohesion: 0.07
Nodes (52): a00(), a07(), a1l(), a2f(), a2g(), a2h(), a2i(), a5e() (+44 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.04
Nodes (21): DiscountController, AirlineBaggageRule, Discount, Operator, VehicleBrand, VehicleModel, VehicleRate, AirlineBaggageRuleSeeder (+13 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (167): ut(), Nt(), Qt(), _a(), aa(), Ae(), ai(), apply() (+159 more)

### Community 63 - "gO"
Cohesion: 0.11
Nodes (36): ah2(), aro(), b2B(), fD(), gAW(), gnl(), gPc(), ir() (+28 more)

### Community 64 - "RelationManager"
Cohesion: 0.01
Nodes (213): a05(), a0g(), a27(), a36(), a39(), a3a(), a3b(), a3I() (+205 more)

### Community 65 - "I"
Cohesion: 0.06
Nodes (54): aa(), Ah(), applyStack(), _calculateBarIndexPixels(), _calculateBarValuePixels(), countVisibleElements(), determineDataLimits(), Dh() (+46 more)

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
Nodes (52): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap() (+44 more)

### Community 70 - "a"
Cohesion: 0.10
Nodes (31): afterDatasetsUpdate(), buildOrUpdateControllers(), _d(), _destroyDatasetMeta(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth() (+23 more)

### Community 71 - "a5"
Cohesion: 0.05
Nodes (53): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), buildOrUpdateScales(), _checkEventBindings() (+45 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (23): actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo(), duration() (+15 more)

### Community 73 - "s"
Cohesion: 0.14
Nodes (49): getOptions(), Bn(), bs(), c(), d(), Di(), Dl(), Dr() (+41 more)

### Community 74 - "EditRecord"
Cohesion: 0.05
Nodes (17): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditFerryRoute, EditGraciaEarningRule, EditHotel (+9 more)

### Community 75 - "Controller"
Cohesion: 0.01
Nodes (241): a(), a09(), a0p(), a0u(), a18(), a1B(), a1E(), a2f() (+233 more)

### Community 76 - "updateElements"
Cohesion: 0.15
Nodes (20): afterDatasetsUpdate(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), hide(), isDatasetVisible() (+12 more)

### Community 77 - "sendRequest"
Cohesion: 0.09
Nodes (36): ad(), bf(), buildTicks(), calculateCircumference(), _calculatePadding(), _circumference(), _computeAngle(), _computeLabelItems() (+28 more)

### Community 78 - "push"
Cohesion: 0.05
Nodes (20): CreateUser, EditUser, AdminNotificationStatus, GraciaEarningRule, GraciaPointLedger, GraciaUserBalance, HasOne, User (+12 more)

### Community 79 - "o8"
Cohesion: 0.05
Nodes (72): A(), aa(), Ac(), add(), addCall(), addResolver(), Bf(), bp() (+64 more)

### Community 80 - "E"
Cohesion: 0.07
Nodes (32): allSelectors(), applyUpdates(), cleanup(), cloneTree(), closestIdRoot(), closestRoot(), dataSet(), deferHandlingDirectives() (+24 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (15): Ga(), td(), c(), Ha(), Ka(), La(), ma(), Nc() (+7 more)

### Community 82 - "skwasm.js"
Cohesion: 0.05
Nodes (63): e(), k(), a(), aa(), ab(), ac(), $b(), bb() (+55 more)

### Community 83 - "$1"
Cohesion: 0.02
Nodes (184): $1$allowPlatformDefault(), $2$alignmentPolicy(), a0m(), a0q(), a22(), a2P(), a35(), a3G() (+176 more)

### Community 84 - "push"
Cohesion: 0.04
Nodes (81): a21(), a2t(), a36(), a4a(), a4u(), a5t(), a6K(), a9B() (+73 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.11
Nodes (53): target(), autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), call(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), "node_modules/@alpinejs/collapse/dist/module.cjs.js"(), "node_modules/@alpinejs/focus/dist/module.cjs.js"() (+45 more)

### Community 86 - "ManageProofs"
Cohesion: 0.02
Nodes (158): $0(), $2$params(), a0g(), a0h(), a0i(), a16(), a1H(), a1j() (+150 more)

### Community 87 - "Dt"
Cohesion: 0.04
Nodes (31): AdminNotificationController, JsonResponse, BookingCalculateController, BookingController, GraciaPointsController, NotificationController, PromotionController, ReferralController (+23 more)

### Community 88 - "preload"
Cohesion: 0.10
Nodes (31): calculateCircumference(), calculateLabelRotation(), _calculatePadding(), _circumference(), _computeLabelItems(), _computeLabelSizes(), computeTickLimit(), _drawArgs() (+23 more)

### Community 89 - "HasFactory"
Cohesion: 0.05
Nodes (51): add(), ar(), Bi(), _cachedScopes(), chartOptionScopes(), constructor(), createResolver(), datasetAnimationScopeKeys() (+43 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (12): d(), Ja(), Ka(), La(), n(), Pc(), q(), r() (+4 more)

### Community 91 - "b5"
Cohesion: 0.18
Nodes (17): cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl(), makeRequest(), markUploadErrored() (+9 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (203): a17(), a1A(), a1P(), a1s(), a1y(), a6(), a6I(), aC() (+195 more)

### Community 93 - ".$2"
Cohesion: 0.03
Nodes (115): $3$color$endFraction$startFraction(), a05(), a2G(), a2o(), a2s(), a2Z(), a3k(), a3l() (+107 more)

### Community 94 - "draw"
Cohesion: 0.01
Nodes (370): $1(), a0n(), a0r(), A1(), a2B(), a2R(), a33(), a34() (+362 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.09
Nodes (30): second(), cleanupModal(), contentIsFromDump(), extractDurationFrom(), extractStreamObjects(), find(), fromQueryString(), getEncodedSnapshotWithLatestChildrenMergedIn() (+22 more)

### Community 97 - "$0"
Cohesion: 0.22
Nodes (5): BookingsExport, BookingExportController, Illuminate\Http\Response, Maatwebsite\Excel\Concerns\Exportable, Maatwebsite\Excel\Concerns\WithMultipleSheets

### Community 98 - "jU"
Cohesion: 0.03
Nodes (149): a0v(), a0w(), a0y(), a0Z(), a11(), a13(), a26(), a3U() (+141 more)

### Community 99 - "M"
Cohesion: 0.05
Nodes (35): A(), b(), be(), c(), e(), f(), fc(), g() (+27 more)

### Community 100 - "get"
Cohesion: 0.05
Nodes (31): ld(), A(), b(), be(), c(), e(), ee(), f() (+23 more)

### Community 101 - "createMorphContext"
Cohesion: 0.08
Nodes (37): appendChild(), cloneNode(), cloneScriptTag(), closestComponent(), componentIsMissingProperty(), createElement(), createMorphContext(), extractUriAndQueryString() (+29 more)

### Community 102 - "navigate_default"
Cohesion: 0.07
Nodes (40): autofocusElementsWithTheAutofocusAttribute(), bindClasses(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject() (+32 more)

### Community 103 - "aG"
Cohesion: 0.04
Nodes (81): $2$isClosing(), a01(), a02(), a03(), a2j(), a73(), a7l(), a7W() (+73 more)

### Community 104 - "render"
Cohesion: 0.05
Nodes (45): xt(), beforeinput(), cacheViewForObject(), canSyncDocumentView(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), compositionend(), createAttachmentNodes() (+37 more)

### Community 106 - "Vn"
Cohesion: 0.11
Nodes (25): average(), fn(), getCenterPoint(), getProps(), hasValue(), hs(), inRange(), inXRange() (+17 more)

### Community 107 - "add"
Cohesion: 0.14
Nodes (18): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidEditAttachment() (+10 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.05
Nodes (50): Ad(), Bd(), Cd(), Fd(), Gd(), Id(), Jd(), Kd() (+42 more)

### Community 109 - "add"
Cohesion: 0.19
Nodes (19): It(), appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes() (+11 more)

### Community 110 - "notification_service.dart"
Cohesion: 0.29
Nodes (7): build, _fetchBookingAndNavigate, _goNext, _goToSchedule, handleNotificationTap, _showPackageDetailsModal, MaterialPageRoute

### Community 111 - "gaf"
Cohesion: 0.25
Nodes (15): dd(), fn(), id(), Is(), Lr(), od(), Or(), Pr() (+7 more)

### Community 112 - "le"
Cohesion: 0.06
Nodes (9): d(), n(), Pc(), q(), r(), Ra, t(), u() (+1 more)

### Community 113 - "bi"
Cohesion: 0.13
Nodes (20): addControllers(), addPlugins(), addScales(), al(), cancel(), _createDescriptors(), _descriptors(), _each() (+12 more)

### Community 114 - "fn"
Cohesion: 0.05
Nodes (14): Ba(), d(), Ga(), Ja(), Ka(), La(), n(), Pc() (+6 more)

### Community 115 - "Ve"
Cohesion: 0.13
Nodes (31): ad(), as(), Ce(), cs(), ed(), ei(), Es(), gd() (+23 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.04
Nodes (71): Ac(), alpha(), an(), as(), At(), Au(), ba(), Bs() (+63 more)

### Community 117 - "Ra"
Cohesion: 0.04
Nodes (62): a1k(), a1o(), a4g(), a4H(), a64(), a6P(), a_1(), adF() (+54 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.07
Nodes (18): CreateAccommodation, CreateAirlineBaggageRule, CreateAppNotification, CreateBooking, CreateDiscount, CreateFerryRoute, CreateGraciaEarningRule, CreateHotel (+10 more)

### Community 120 - "a1"
Cohesion: 0.02
Nodes (106): a06(), a0l(), a0S(), a0U(), a1Q(), a30(), a3L(), a62() (+98 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.08
Nodes (35): arr(), addInitSelector(), base64toBlob(), cleanupModal(), contentIsFromDump(), createArrayInstrumentations(), createReactiveEffect(), effect2() (+27 more)

### Community 122 - "aW_"
Cohesion: 0.02
Nodes (138): $2$isClosing(), a2b(), a3O(), a4m(), a5R(), a7n(), a80(), a82() (+130 more)

### Community 123 - "navigate_default"
Cohesion: 0.09
Nodes (29): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+21 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (15): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+7 more)

### Community 125 - "dO"
Cohesion: 0.06
Nodes (27): $, ack(), bdW(), bgH(), bi8(), bhE(), bkA(), bm2() (+19 more)

### Community 126 - "gN"
Cohesion: 0.06
Nodes (45): canAcceptDataTransfer(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), compositionDidRequestChangingSelectionToLocationRange(), createDOMRangeFromLocationRange(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange() (+37 more)

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (325): $2$alignmentPolicy(), a04(), a08(), a15(), a20(), a25(), a2q(), a2r() (+317 more)

### Community 128 - "start"
Cohesion: 0.08
Nodes (28): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), dn(), Du(), Ef() (+20 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.06
Nodes (13): CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, NotifyExpiringVouchers, PurgeExpiredProofs, PurgeExpiredSchedules, RetroactiveGraciaPoints, RetrofitReferrals (+5 more)

### Community 131 - "gt"
Cohesion: 0.03
Nodes (77): a0W(), a1s(), a1t(), a1z(), a29(), a2E(), a3w(), a4R() (+69 more)

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
Cohesion: 0.15
Nodes (19): search(), url(), cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl() (+11 more)

### Community 137 - "d4"
Cohesion: 0.02
Nodes (414): $2(), $2$from$to(), $4(), a0(), a0e(), a0i(), a0j(), a1A() (+406 more)

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
Cohesion: 0.12
Nodes (19): canSetCurrentAttribute(), canSetCurrentBlockAttribute(), canSetCurrentTextAttribute(), cut(), didClickAttachment(), dragstart(), findAttachmentForElement(), getAttachmentAndPositionById() (+11 more)

### Community 142 - ".$1"
Cohesion: 0.10
Nodes (24): attachmentForFile(), attributesForFile(), compositionShouldAcceptFile(), didChangeAttributes(), getContentType(), getCurrentTextAttributes(), getHeight(), getHref() (+16 more)

### Community 143 - "echo.js"
Cohesion: 0.06
Nodes (49): a(), ar(), at(), b(), Be(), Ce(), cr(), d() (+41 more)

### Community 144 - "m"
Cohesion: 0.09
Nodes (22): @pragma, _channelDescription, _channelId, _channelName, clearBadge, _firebaseMessagingBackgroundHandler, initialize, NotificationService (+14 more)

### Community 145 - "V"
Cohesion: 0.03
Nodes (84): $0(), $2$params(), a19(), a2_(), a2Z(), a33(), a3D(), a3E() (+76 more)

### Community 146 - "$0"
Cohesion: 0.09
Nodes (30): active(), _animateOptions(), average(), cancel(), _createAnimations(), _createDescriptors(), dataset(), _descriptors() (+22 more)

### Community 147 - "gO"
Cohesion: 0.16
Nodes (18): ac(), Ai(), ca(), ec(), Fc(), G(), getIndexAngle(), getPointPosition() (+10 more)

### Community 148 - "has"
Cohesion: 0.10
Nodes (33): directive2(), base64toBlob(), cleanup(), cloneIfObject(), containsTargets(), customDirectiveHasBeenRegistered(), directive(), dirtyTargets() (+25 more)

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
Cohesion: 0.14
Nodes (5): ListTours, TourController, Tour, Attribute, Illuminate\Database\Eloquent\Casts\Attribute

### Community 154 - "dB"
Cohesion: 0.16
Nodes (16): addCleanup(), constructor(), deepClone(), diff(), extractData(), generateWireObject(), initComponent(), isArray() (+8 more)

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (287): a(), a01(), a03(), a0c(), a0h(), a0N(), a0V(), a0y() (+279 more)

### Community 156 - "dD"
Cohesion: 0.12
Nodes (18): box(), canBeConsolidatedWith(), canDecreaseBlockAttributeLevel(), compositionControllerDidRender(), fromUCS2String(), getAttributeLevel(), getTargetDOMRange(), hasAttributes() (+10 more)

### Community 157 - "bw"
Cohesion: 0.06
Nodes (46): af(), ai(), al(), ba(), cancelUpload(), ci(), co(), cr() (+38 more)

### Community 158 - "b6"
Cohesion: 0.02
Nodes (114): $3$crossAxisPosition$mainAxisPosition(), a0Z(), a1G(), a2t(), a5d(), a6O(), a7m(), a7s() (+106 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "aJ"
Cohesion: 0.27
Nodes (10): $a(), ab(), bb(), cb(), e(), Ea(), La(), uc() (+2 more)

### Community 161 - "gbq"
Cohesion: 0.26
Nodes (17): D(), f(), g(), k(), l(), m(), mb(), q() (+9 more)

### Community 162 - "tT"
Cohesion: 0.31
Nodes (5): fb(), get(), ve(), wc(), we()

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
Cohesion: 0.18
Nodes (9): Ac(), cc(), dc(), nb(), Ob(), Pb(), Rd(), sb() (+1 more)

### Community 168 - "ah"
Cohesion: 0.25
Nodes (8): da(), fa(), jd(), kd(), R(), td(), ud(), vd()

### Community 169 - "add"
Cohesion: 0.14
Nodes (20): add(), addCall(), addResolver(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool(), delete() (+12 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.13
Nodes (11): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, FlutterViewController, NSWindow (+3 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.12
Nodes (18): children(), closestComponent(), componentIsMissingProperty(), destroyComponent(), ensureLivewireScriptIsntMisplaced(), extractDirective(), extractTypeModifiersAndValue(), findComponent() (+10 more)

### Community 173 - "d5"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 174 - "nE"
Cohesion: 0.20
Nodes (8): Any, AppDelegate, Bool, AppDelegate, Bool, FlutterAppDelegate, NSApplication, UIApplication

### Community 176 - "jB"
Cohesion: 0.29
Nodes (6): e(), i(), l(), Ni(), o(), t()

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
Cohesion: 0.23
Nodes (8): b(), c(), fb(), pe(), Qb(), qe(), tc(), ce

### Community 182 - "ho"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 183 - "aNl"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 185 - "Widget"
Cohesion: 0.50
Nodes (3): confirmAdd, confirmReplace, deleteImage(

### Community 186 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 187 - "d7"
Cohesion: 0.07
Nodes (44): disabled(), add(), afterAutoSkip(), Bi(), buildLookupTable(), buildTicks(), C(), Co() (+36 more)

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
Nodes (19): scripts, dev, post-root-package-install, post-update-cmd, pre-package-uninstall, setup, test, Composer\\Config::disableProcessTimeout (+11 more)

### Community 213 - "color-picker.js"
Cohesion: 0.11
Nodes (12): [g](), style(), update(), A(), connectedCallback(), Ge(), required(), setCustomValidity() (+4 more)

### Community 214 - "manifest.json"
Cohesion: 0.13
Nodes (14): background_color, categories, description, display, icons, lang, name, orientation (+6 more)

### Community 219 - "gh3"
Cohesion: 0.29
Nodes (7): formDisabledCallback(), get(), has(), io(), ke(), no(), refreshAttachments()

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
Cohesion: 0.26
Nodes (7): C(), D(), J(), O(), U(), v(), X()

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.05
Nodes (68): attachFiles(), canApplyToDocument(), compositionstart(), compositionupdate(), constructor(), createLinkHTML(), deleteByDrag(), dragend() (+60 more)

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 247 - "dispatchEvent"
Cohesion: 0.20
Nodes (12): componentsByName(), dispatch(), dispatch2(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf(), dispatchTo() (+4 more)

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
Cohesion: 0.16
Nodes (23): OnCreate, HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), HWND (+15 more)

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
- **942 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+937 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **40 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `dO` to `.mount`, `.processBookingInternal`, `gt`, `d4`, `.$1`, `draw`, `livewire.min.js`, `aM_`, `select.js`, `x`, `te`, `getSelectedRange`, `dH`, `buildTicks`, `support.js`, `setAttribute`, `Controller`, `$1`, `gh3`, `G`, `draw`, `render`, `gN`, `bJ`?**
  _High betweenness centrality (0.036) - this node is a cross-community bridge._
- **Why does `ut()` connect `support.js` to `b`, `select.js`, `aM_`, `HasFactory`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `a2()` connect `.mount` to `.saveDraft`, `d4`, `d`, `Schedule`, `j_`, `dH`, `bY`, `buildTicks`, `RelationManager`, `Controller`, `$1`, `push`, `ManageProofs`, `G`, `.$2`, `draw`, `jU`, `aG`, `Ra`, `a1`, `bJ`?**
  _High betweenness centrality (0.027) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `a()` (e.g. with `loadEntrypoint()` and `_loadJSEntrypoint()`) actually correct?**
  _`a()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 235 inferred relationships involving `a()` (e.g. with `$0()` and `b()`) actually correct?**
  _`a()` has 235 INFERRED edges - model-reasoned connections that need verification._
- **Are the 498 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 498 INFERRED edges - model-reasoned connections that need verification._
- **Are the 496 inferred relationships involving `c()` (e.g. with `$0()` and `$1()`) actually correct?**
  _`c()` has 496 INFERRED edges - model-reasoned connections that need verification._