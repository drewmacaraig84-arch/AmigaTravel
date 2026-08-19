# Graph Report - AmigaTravel  (2026-08-19)

## Corpus Check
- 665 files · ~2,386,233 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17279 nodes · 52725 edges · 541 communities (498 shown, 43 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 8143 edges (avg confidence: 0.56)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `1a5233c4`
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
- add
- target
- tT
- bn
- _each
- 🚀 Part 1: Backend Setup (Laravel)
- win32_window.cpp
- e_
- getDatasetMeta
- du
- RunnerTests.swift
- require
- bZ
- d5
- nE
- hw
- Vl
- .processRow
- makeRequest
- flutter.js
- jd
- AdminPanelProvider.php
- ho
- constructor
- Widget
- composer.json
- sc
- bY
- gkr
- G
- Deployment setup
- rs
- scripts
- color-picker.js
- manifest.json
- c
- .fromWireType
- GeneratedPluginRegistrant.swift
- wWinMain
- app.js
- manifest.json
- aYd
- manifest.json
- mergeNewHead
- booking-reschedule.blade.php
- dispatchEvent
- fb
- fb
- Rb
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

## Communities (541 total, 43 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (495): $3$crossAxisPosition$mainAxisPosition(), a03(), a09(), a0a(), a0b(), a0O(), a10(), a1i() (+487 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (557): $2$priority$scheduler(), $4(), a0(), a1V(), a2(), a2A(), a2D(), a2E() (+549 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.15
Nodes (49): getOptions(), Bn(), bs(), c(), d(), Di(), Dl(), Dr() (+41 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.04
Nodes (8): BookingLookup, Schedule, ServiceCancellationReplacementSchedule, ServiceCancellationManager, DateTimeInterface, HasOneThrough, Illuminate\Database\Eloquent\Relations\HasMany, ServiceCancellationTest

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.02
Nodes (160): $2$alignmentPolicy(), a0m(), a11(), a13(), a22(), a26(), a2P(), a35() (+152 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.04
Nodes (17): ManageWebsiteSettings, Operator, VehicleRate, WebsiteSetting, AirlineBaggageRuleSeeder, DatabaseSeeder, DiscountSeeder, FerryRouteOperatorFixSeeder (+9 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.04
Nodes (26): NotifyAffectedBookerJob, SendBookingConfirmationJob, BookingCancellation, self, BookingConfirmation, BookingCreated, PaymentProofReceived, RebookingRequested (+18 more)

### Community 10 - "download.blade.php"
Cohesion: 0.02
Nodes (114): a0i(), a0O(), a10(), a1G(), a2d(), a46(), a4t(), a7m() (+106 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.03
Nodes (117): $2$from$to(), $2$priority$scheduler(), $3$crossAxisPosition$mainAxisPosition(), a0Z(), a2t(), a3D(), a3E(), a3F() (+109 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (560): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+552 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (115): acquireContext(), afterDraw(), beforeDatasetDraw(), beforeDatasetsDraw(), beforeDraw(), bh(), ch(), clone() (+107 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (27): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+19 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.01
Nodes (132): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), box(), canRedo() (+124 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.04
Nodes (182): u(), _a(), Ac(), Ae(), af(), ai(), al(), An() (+174 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (114): aa(), active(), addControllers(), addPlugins(), addScales(), an(), _animateOptions(), aspectRatio() (+106 more)

### Community 20 - "Booking"
Cohesion: 0.04
Nodes (77): a2t(), a36(), a4a(), a4u(), a5t(), a6K(), a9B(), a9P() (+69 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (95): addAssetsToHeadTagOfPage(), _arrayLikeToArray(), _arrayWithoutHoles(), bindInputValue(), bindStyles(), checkedAttrLooseCompare(), checkedAttrLooseCompare2(), children() (+87 more)

### Community 22 - "User.php"
Cohesion: 0.04
Nodes (29): CreatesApplication, dismissCancellationReminder, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, requestCancellation, selectRebookingDepartureAccommodation(, selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectRebookingReturnAccommodation( (+21 more)

### Community 23 - "draw"
Cohesion: 0.05
Nodes (82): adjustHitBoxes(), ae(), af(), calculateLabelRotation(), _computeGridLineItems(), _computeLabelArea(), _computeTitleHeight(), cs() (+74 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (373): a0k(), a0S(), a0t(), a0U(), a1B(), a1C(), a1E(), a1k() (+365 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.02
Nodes (88): Ac(), addCleanup(), ae(), applyUpdates(), au(), bl(), ["@blur"](), bo() (+80 more)

### Community 26 - "k"
Cohesion: 0.05
Nodes (54): Yn(), addElements(), al(), beforeUpdate(), buildOrUpdateControllers(), buildOrUpdateElements(), _cachedScopes(), chartOptionScopes() (+46 more)

### Community 27 - "select.js"
Cohesion: 0.07
Nodes (69): [g](), [x](), $c(), D(), E(), Ea(), g(), H() (+61 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.03
Nodes (117): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), canBeConsolidatedWith(), canBeGroupedWith() (+109 more)

### Community 29 - "_update"
Cohesion: 0.05
Nodes (75): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+67 more)

### Community 30 - "fromObject"
Cohesion: 0.05
Nodes (57): a07(), a0x(), a54(), a6W(), a7H(), a82(), a8A(), a8B() (+49 more)

### Community 31 - "constructor"
Cohesion: 0.03
Nodes (81): Ac(), an(), ar(), Bl(), cf(), constructor(), create(), Dc() (+73 more)

### Community 32 - "d"
Cohesion: 0.03
Nodes (96): $2$from$to(), a0S(), a2l(), a2q(), a38(), a39(), a3U(), a4X() (+88 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (380): $1$allowPlatformDefault(), a(), a0p(), a18(), a1B(), a1c(), a1D(), a1E() (+372 more)

### Community 34 - "H"
Cohesion: 0.02
Nodes (130): a14(), a41(), a48(), a4z(), a5g(), a5h(), a5U(), a6P() (+122 more)

### Community 35 - "TransportClass"
Cohesion: 0.03
Nodes (80): a08(), a0A(), a15(), a2q(), A3(), a32(), a7G(), a_2() (+72 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.05
Nodes (45): a0d(), a0f(), a12(), a15(), a6R(), a7F(), aB8(), aD4() (+37 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (48): addAssetsToHeadTagOfPage(), [attribute](), callAndClearComponentDebounces(), children(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), closestComponent(), componentIsMissingProperty() (+40 more)

### Community 38 - "add"
Cohesion: 0.08
Nodes (49): call(), checkIdentityKeys(), clear(), createForEach(), _createForOfIteratorHelper(), createGetter(), createInstrumentations(), createIterableMethod() (+41 more)

### Community 39 - "User"
Cohesion: 0.07
Nodes (37): alpha(), be(), beforeDraw(), dataset(), ea(), en(), fe(), ge() (+29 more)

### Community 40 - "a3"
Cohesion: 0.04
Nodes (60): a00(), a07(), a1l(), a1Q(), a2_(), a2Z(), a61(), a6f() (+52 more)

### Community 41 - "x"
Cohesion: 0.08
Nodes (84): Sg(), ad(), at(), B(), br(), Bt(), ca(), cd() (+76 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (337): $0(), $2(), $2$params(), $3(), $5(), a00(), a0E(), a0g() (+329 more)

### Community 43 - "gv"
Cohesion: 0.03
Nodes (97): arr(), addInitSelector(), addRootSelector(), addScopeToNode(), allSelectors(), applyBindingsObject(), [attribute](), attributesOnly() (+89 more)

### Community 44 - "te"
Cohesion: 0.03
Nodes (22): Aa(), Bi(), bn(), Jc(), ji(), Ln(), ma(), pi() (+14 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.07
Nodes (39): addCleanup(), cleanupModal(), constructor(), containsTargets(), contentIsFromDump(), deepClone(), diff(), extractData() (+31 more)

### Community 46 - "_update"
Cohesion: 0.07
Nodes (45): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+37 more)

### Community 47 - "ListRecords"
Cohesion: 0.04
Nodes (20): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+12 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.05
Nodes (56): $a(), ab(), Ac(), Ad(), b(), bb(), bc(), c() (+48 more)

### Community 49 - "getContext"
Cohesion: 0.06
Nodes (39): Au(), ba(), bu(), cu(), dataset(), dn(), _drawDatasets(), en() (+31 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (55): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+47 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (62): attachmentManagerDidRequestRemovalOfAttachment(), breakFormattedBlock(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), copyWithoutText() (+54 more)

### Community 52 - "AC"
Cohesion: 0.14
Nodes (21): buildOrUpdateScales(), cl(), D(), data(), E(), ensureScalesHaveIDs(), Eo(), fl() (+13 more)

### Community 53 - "push"
Cohesion: 0.07
Nodes (52): acquireContext(), adjustHitBoxes(), afterDraw(), bc(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight() (+44 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.08
Nodes (14): Ad(), bc(), fe(), Kc(), mc(), oc(), P(), Qc() (+6 more)

### Community 55 - "Voucher"
Cohesion: 0.07
Nodes (37): average(), Ca(), cd(), clear(), cn(), Da(), Fc(), fh() (+29 more)

### Community 56 - "qt"
Cohesion: 0.08
Nodes (9): ScheduleAccommodation, TransportClass, Carbon, ScheduleCsvImportService, Carbon\Carbon, TransportClassSeeder, Illuminate\Database\Eloquent\Relations\BelongsToMany, AccommodationRateCodeTest (+1 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (32): A(), Ad(), b(), Ba(), c(), d(), dd(), E() (+24 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (396): $1(), a0B(), a0D(), a0l(), a11(), a1m(), a1x(), a24() (+388 more)

### Community 59 - "aQ"
Cohesion: 0.03
Nodes (52): AdminNotifications, ManagePaymentSettings, ManageRebookings, ManageTransportAccommodation, MyPage, Collection, StaffPerformance, CreateAccommodation (+44 more)

### Community 60 - "buildTicks"
Cohesion: 0.03
Nodes (94): a2f(), a2g(), a2h(), a2i(), a5e(), a5S(), a66(), a7k() (+86 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.02
Nodes (39): CreateBookingAction, CreateServiceCancellation, GraciaPointsController, NotificationController, ReferralController, Accommodation, AdminNotificationStatus, AirlineBaggageRule (+31 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (164): ut(), Nt(), _a(), aa(), Ae(), ai(), apply(), ar() (+156 more)

### Community 63 - "gO"
Cohesion: 0.09
Nodes (32): add(), addCall(), addResolver(), bufferPoolingForFiveMs(), cloneIfObject(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool() (+24 more)

### Community 64 - "RelationManager"
Cohesion: 0.02
Nodes (177): a05(), a0g(), a0x(), a27(), a36(), a39(), a3a(), a3b() (+169 more)

### Community 65 - "I"
Cohesion: 0.08
Nodes (32): afterDatasetsUpdate(), _d(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), _handleEvent() (+24 more)

### Community 66 - "i"
Cohesion: 0.05
Nodes (57): _a(), afterAutoSkip(), Ai(), buildLookupTable(), daysInMonth(), daysInYear(), Di(), Fl() (+49 more)

### Community 67 - "get"
Cohesion: 0.12
Nodes (24): addEventListener(), as(), At(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), Bs(), cc() (+16 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (61): ForgotPasswordScreen, _ForgotPasswordScreenState, ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState (+53 more)

### Community 69 - "setAttribute"
Cohesion: 0.23
Nodes (14): A(), connectedCallback(), constructor(), formDisabledCallback(), Ge(), has(), io(), ke() (+6 more)

### Community 70 - "a"
Cohesion: 0.10
Nodes (28): cancelUpload(), componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal() (+20 more)

### Community 71 - "a5"
Cohesion: 0.08
Nodes (33): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), cs(), Ct() (+25 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (23): actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo(), duration() (+15 more)

### Community 73 - "s"
Cohesion: 0.06
Nodes (48): putPersistantElementsBack(), ap(), bd(), Bi(), bp(), Br(), bu(), children() (+40 more)

### Community 74 - "EditRecord"
Cohesion: 0.05
Nodes (17): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditFerryRoute, EditGraciaEarningRule, EditHotel (+9 more)

### Community 75 - "Controller"
Cohesion: 0.06
Nodes (56): disabled(), _a(), add(), ba(), C(), Co(), cr(), diff() (+48 more)

### Community 76 - "updateElements"
Cohesion: 0.06
Nodes (55): afterAutoSkip(), Ao(), applyStack(), ar(), as(), Bi(), buildLookupTable(), _calculateBarIndexPixels() (+47 more)

### Community 77 - "sendRequest"
Cohesion: 0.01
Nodes (168): $1$1(), a1m(), a1N(), a2K(), a3O(), a4d(), a4R(), a57() (+160 more)

### Community 78 - "push"
Cohesion: 0.03
Nodes (33): CreateUser, EditUser, AdminNotificationController, JsonResponse, AccommodationController, BookingCalculateController, BookingController, DiscountController (+25 more)

### Community 79 - "o8"
Cohesion: 0.11
Nodes (20): bc(), dc(), eo(), fe(), gc(), ha(), hc(), Ko() (+12 more)

### Community 80 - "E"
Cohesion: 0.05
Nodes (56): ArrowLeft(), ArrowRight(), canAcceptDataTransfer(), compositionControllerDidFocus(), compositionDidRequestChangingSelectionToLocationRange(), createDOMRangeFromLocationRange(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange() (+48 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (15): Ga(), td(), c(), Ha(), Ka(), La(), ma(), Nc() (+7 more)

### Community 82 - "skwasm.js"
Cohesion: 0.05
Nodes (63): e(), k(), a(), aa(), ab(), ac(), $b(), bb() (+55 more)

### Community 83 - "$1"
Cohesion: 0.03
Nodes (114): $3$color$endFraction$startFraction(), a05(), a23(), a2G(), a2o(), a2Z(), a3k(), a3l() (+106 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.11
Nodes (53): target(), autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), call(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), "node_modules/@alpinejs/collapse/dist/module.cjs.js"(), "node_modules/@alpinejs/focus/dist/module.cjs.js"() (+45 more)

### Community 86 - "ManageProofs"
Cohesion: 0.05
Nodes (65): a1U(), a2s(), a3J(), a5W(), a6E(), aBt(), aCK(), adN() (+57 more)

### Community 87 - "Dt"
Cohesion: 0.21
Nodes (18): appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes(), findBlockElementAncestors() (+10 more)

### Community 88 - "preload"
Cohesion: 0.07
Nodes (46): buildTicks(), calculateLabelRotation(), _calculatePadding(), _computeAngle(), _computeGridLineItems(), _computeLabelItems(), _computeLabelSizes(), computeTickLimit() (+38 more)

### Community 89 - "HasFactory"
Cohesion: 0.21
Nodes (12): average(), getCenterPoint(), getProps(), hasValue(), hs(), inXRange(), inYRange(), nearest() (+4 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (12): d(), Ja(), Ka(), La(), n(), Pc(), q(), r() (+4 more)

### Community 91 - "b5"
Cohesion: 0.18
Nodes (13): a5i(), aCd(), aFn(), agc(), agx(), aio(), bi2(), ga4f() (+5 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (203): a17(), a1A(), a1P(), a1s(), a1y(), a6(), a6I(), aC() (+195 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (380): $1(), a0n(), a0r(), A1(), a1T(), a2B(), a2R(), a33() (+372 more)

### Community 94 - "draw"
Cohesion: 0.02
Nodes (133): a0q(), a14(), a16(), a1H(), a1j(), a1q(), a2M(), a2N() (+125 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.04
Nodes (84): abutsStart(), after(), Ag(), Al(), before(), count(), determineDataLimits(), Dh() (+76 more)

### Community 97 - "$0"
Cohesion: 0.17
Nodes (8): $, ack(), bdW(), bgH(), bi8(), bhE(), bkA(), bm2()

### Community 98 - "jU"
Cohesion: 0.03
Nodes (114): a0l(), a0u(), a0v(), a0w(), a0y(), a0Z(), a5j(), a5M() (+106 more)

### Community 99 - "M"
Cohesion: 0.06
Nodes (31): A(), b(), be(), c(), e(), f(), fc(), g() (+23 more)

### Community 100 - "get"
Cohesion: 0.05
Nodes (31): ld(), A(), b(), be(), c(), e(), ee(), f() (+23 more)

### Community 101 - "createMorphContext"
Cohesion: 0.08
Nodes (37): appendChild(), cloneNode(), cloneScriptTag(), closestComponent(), componentIsMissingProperty(), createElement(), createMorphContext(), extractUriAndQueryString() (+29 more)

### Community 102 - "navigate_default"
Cohesion: 0.08
Nodes (36): autofocusElementsWithTheAutofocusAttribute(), bindClasses(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject() (+28 more)

### Community 103 - "aG"
Cohesion: 0.03
Nodes (114): $2$isClosing(), a01(), a02(), a1k(), a2j(), a64(), a6P(), a73() (+106 more)

### Community 104 - "render"
Cohesion: 0.06
Nodes (37): xt(), cacheViewForObject(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync() (+29 more)

### Community 107 - "add"
Cohesion: 0.06
Nodes (43): actionIsExternal(), attachmentForFile(), attributesForFile(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidRender(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment() (+35 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.06
Nodes (47): Bd(), Cd(), Fd(), Gd(), Hd(), Id(), Kd(), Ld() (+39 more)

### Community 109 - "add"
Cohesion: 0.07
Nodes (49): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap() (+41 more)

### Community 110 - "notification_service.dart"
Cohesion: 0.29
Nodes (7): build, _fetchBookingAndNavigate, _goNext, _goToSchedule, handleNotificationTap, _showPackageDetailsModal, MaterialPageRoute

### Community 111 - "gaf"
Cohesion: 0.25
Nodes (15): dd(), fn(), id(), Is(), Lr(), od(), Or(), Pr() (+7 more)

### Community 112 - "le"
Cohesion: 0.05
Nodes (14): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+6 more)

### Community 113 - "bi"
Cohesion: 0.07
Nodes (51): A(), aa(), add(), addCall(), addResolver(), At(), Be(), Bf() (+43 more)

### Community 114 - "fn"
Cohesion: 0.05
Nodes (14): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+6 more)

### Community 115 - "Ve"
Cohesion: 0.12
Nodes (33): ad(), as(), Ce(), cs(), Ct(), ed(), ei(), Es() (+25 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.07
Nodes (38): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateControllers(), buildOrUpdateElements(), _dataCheck(), _destroy() (+30 more)

### Community 117 - "Ra"
Cohesion: 0.06
Nodes (42): af(), ai(), al(), ba(), cancelUpload(), ci(), co(), cr() (+34 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (14): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+6 more)

### Community 119 - "b"
Cohesion: 0.33
Nodes (12): g(), l(), m(), p(), t(), u(), w(), x() (+4 more)

### Community 120 - "a1"
Cohesion: 0.06
Nodes (35): a30(), a3L(), a70(), a71(), a7j(), a7u(), a_1(), aie() (+27 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.24
Nodes (10): a(), a(), a(), a(), a(), At(), Fa(), getMaximumSize() (+2 more)

### Community 122 - "aW_"
Cohesion: 0.01
Nodes (305): $0(), $2$params(), a0Q(), a19(), a1I(), a2a(), a33(), a3O() (+297 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "dO"
Cohesion: 0.12
Nodes (16): a(), c(), f(), g(), h(), i(), J(), l() (+8 more)

### Community 126 - "gN"
Cohesion: 0.13
Nodes (20): input(), attributeShouldntBePreservedIfFalsy(), bind(), bindAttribute(), bindAttributeAndProperty(), camelCase(), formatInput(), generateEvaluatorFromFunction() (+12 more)

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (328): $1$1(), $2$alignmentPolicy(), $3(), $5(), a04(), a06(), a0f(), a13() (+320 more)

### Community 128 - "start"
Cohesion: 0.22
Nodes (18): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), evaluate2(), fn(), getAlignment(), getAlignmentAxis(), getAlignmentSides() (+10 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.03
Nodes (34): CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, NotifyExpiringVouchers, PurgeExpiredProofs, PurgeExpiredSchedules, RetroactiveGraciaPoints, RetrofitReferrals (+26 more)

### Community 131 - "gt"
Cohesion: 0.15
Nodes (17): ca(), Cc(), ec(), has(), hn(), ia(), jc(), Kt() (+9 more)

### Community 132 - "railway-start.sh"
Cohesion: 0.07
Nodes (26): APP_DEBUG, APP_ENV, APP_NAME, APP_URL, CACHE_STORE, DB_CONNECTION, DB_DATABASE, DB_HOST (+18 more)

### Community 133 - "Vehicle"
Cohesion: 0.20
Nodes (24): add(), adjustScroll(), animate(), autoAnimate(), cleanUp(), deletePosition(), forEach(), getCoords() (+16 more)

### Community 134 - "St"
Cohesion: 0.09
Nodes (32): second(), base64toBlob(), cleanupModal(), contentIsFromDump(), extractStreamObjects(), fromQueryString(), getEncodedSnapshotWithLatestChildrenMergedIn(), getPooledCommits() (+24 more)

### Community 135 - "d4"
Cohesion: 0.28
Nodes (9): ac(), Ai(), ca(), Li(), oc(), ro(), sc(), Us() (+1 more)

### Community 136 - "call"
Cohesion: 0.10
Nodes (29): cleanup(), cloneIfObject(), containsTargets(), customDirectiveHasBeenRegistered(), destroyComponent(), directive(), dirtyTargets(), generateEntangleFunction() (+21 more)

### Community 137 - "d4"
Cohesion: 0.02
Nodes (404): $2(), $4(), a0(), a0e(), a0j(), a1A(), a1H(), a1J() (+396 more)

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
Nodes (47): active(), add(), _animateOptions(), Bi(), _cachedScopes(), cancel(), chartOptionScopes(), _createAnimations() (+39 more)

### Community 142 - ".$1"
Cohesion: 0.15
Nodes (5): ListTours, TourController, Tour, Attribute, Illuminate\Database\Eloquent\Casts\Attribute

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
Cohesion: 0.29
Nodes (6): e(), i(), l(), Ni(), o(), t()

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

### Community 154 - "dB"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (328): $2$isClosing(), a(), a01(), a02(), a03(), a0c(), a0h(), a0N() (+320 more)

### Community 156 - "dD"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 157 - "bw"
Cohesion: 0.10
Nodes (28): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+20 more)

### Community 158 - "b6"
Cohesion: 0.03
Nodes (93): a0W(), a1s(), a1z(), a29(), a2E(), a3w(), a4R(), a8Y() (+85 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 161 - "target"
Cohesion: 0.15
Nodes (19): search(), url(), cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl() (+11 more)

### Community 162 - "tT"
Cohesion: 0.11
Nodes (20): canSetCurrentTextAttribute(), didClickAttachment(), dragstart(), findAttachmentForElement(), getAttachmentAndPositionById(), getAttachmentById(), getAttachmentPieces(), getAttachments() (+12 more)

### Community 163 - "bn"
Cohesion: 0.15
Nodes (13): applyUpdates(), cleanup(), dataSet(), deferHandlingDirectives(), destroyComponent(), holdNextTicks(), nextTick(), once() (+5 more)

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
Cohesion: 0.09
Nodes (27): addDebounceOrThrottle(), camelCase2(), debounce(), dotSyntax(), extractDurationFrom(), extractScriptTagContent(), getIterationScopeVariables(), history2() (+19 more)

### Community 168 - "getDatasetMeta"
Cohesion: 0.10
Nodes (28): afterDatasetsUpdate(), datasetAnimationScopeKeys(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), _getSortedDatasetMetas(), getStyle() (+20 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.04
Nodes (98): aa(), ad(), Ah(), applyStack(), aspectRatio(), bf(), buildTicks(), C() (+90 more)

### Community 173 - "d5"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 174 - "nE"
Cohesion: 0.20
Nodes (8): Any, AppDelegate, Bool, AppDelegate, Bool, FlutterAppDelegate, NSApplication, UIApplication

### Community 175 - "hw"
Cohesion: 0.15
Nodes (19): add(), addCall(), addResolver(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool(), delete() (+11 more)

### Community 176 - "Vl"
Cohesion: 0.31
Nodes (13): canDecreaseNestingLevel(), canIncreaseNestingLevel(), decreaseNestingLevel(), formatIndent(), formatOutdent(), getBlock(), getLastNestableAttribute(), getListItemAttributes() (+5 more)

### Community 177 - ".processRow"
Cohesion: 0.50
Nodes (4): R(), td(), ud(), vd()

### Community 178 - "makeRequest"
Cohesion: 0.18
Nodes (13): handleS3PreSignedUrl(), handleSignedUrl(), ji(), makeRequest(), markUploadErrored(), markUploadFinished(), prepare(), qt() (+5 more)

### Community 179 - "flutter.js"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 180 - "jd"
Cohesion: 0.20
Nodes (11): appendChild(), cp(), fp(), Hd(), insertBefore(), jd(), lp(), Qs() (+3 more)

### Community 181 - "AdminPanelProvider.php"
Cohesion: 0.03
Nodes (15): Action, ManageProofs, ViewBooking, ServiceCancellationResource, DatePicker, PaymentSetting, self, Transaction (+7 more)

### Community 182 - "ho"
Cohesion: 0.22
Nodes (5): BookingsExport, BookingExportController, Illuminate\Http\Response, Maatwebsite\Excel\Concerns\Exportable, Maatwebsite\Excel\Concerns\WithMultipleSheets

### Community 183 - "constructor"
Cohesion: 0.11
Nodes (24): addCleanup(), applyUpdates(), constructor(), dataSet(), deepClone(), diff(), ensureLivewireScriptIsntMisplaced(), extractData() (+16 more)

### Community 185 - "Widget"
Cohesion: 0.50
Nodes (3): confirmAdd, confirmReplace, deleteImage(

### Community 186 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 187 - "sc"
Cohesion: 0.26
Nodes (17): D(), f(), g(), k(), l(), m(), mb(), q() (+9 more)

### Community 188 - "bY"
Cohesion: 0.02
Nodes (203): a1O(), a4e(), a52(), a62(), a8u(), a9N(), a_6(), a_7() (+195 more)

### Community 189 - "gkr"
Cohesion: 0.33
Nodes (9): a78(), a7A(), aim(), aWu(), aWV(), gHK(), gkr(), grp() (+1 more)

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

### Community 219 - "c"
Cohesion: 0.23
Nodes (15): $a(), ab(), b(), bb(), c(), cb(), da(), e() (+7 more)

### Community 221 - ".fromWireType"
Cohesion: 0.16
Nodes (10): Ac(), cc(), dc(), nb(), Ob(), Pb(), Qb(), Rd() (+2 more)

### Community 222 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.14
Nodes (13): file_selector_macos, firebase_core, firebase_messaging, flutter_app_badger, RegisterGeneratedPlugins(), flutter_local_notifications, FlutterPluginRegistry, Foundation (+5 more)

### Community 223 - "wWinMain"
Cohesion: 0.24
Nodes (9): wWinMain(), string, wchar_t, CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16(), _In_, _In_opt_ (+1 more)

### Community 231 - "app.js"
Cohesion: 0.21
Nodes (9): C(), D(), J(), O(), U(), v(), X(), d() (+1 more)

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.04
Nodes (75): attachFiles(), backspace(), beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), createLinkHTML() (+67 more)

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 247 - "mergeNewHead"
Cohesion: 0.16
Nodes (17): cloneScriptTag(), extractScriptTagContent(), extractUriAndQueryString(), ifTheQueryStringChangedSinceLastRequest(), ignoreAttributes(), injectScriptTagAndWaitForItToFullyLoad(), isAsset(), isScript() (+9 more)

### Community 250 - "booking-reschedule.blade.php"
Cohesion: 0.20
Nodes (9): closeRefundForm, openRefundForm, selectDepartureAccommodation(, selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectReturnAccommodation(, selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === , setStep(, submitCancelAndRefund (+1 more)

### Community 251 - "dispatchEvent"
Cohesion: 0.22
Nodes (11): componentsByName(), dispatch(), dispatch2(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf(), dispatchTo() (+3 more)

### Community 254 - "fb"
Cohesion: 0.31
Nodes (5): fb(), get(), ve(), wc(), we()

### Community 255 - "fb"
Cohesion: 0.27
Nodes (6): fa(), fb(), get(), pe(), qe(), wc()

### Community 256 - "Rb"
Cohesion: 0.29
Nodes (3): Lb(), Rb(), zb()

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
- **954 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+949 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **43 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `$0` to `.mount`, `.updateBaggagePriceFromRates`, `d4`, `draw`, `aM_`, `select.js`, `b6`, `Schedule`, `H`, `a3`, `x`, `te`, `getSelectedRange`, `dH`, `support.js`, `o8`, `E`, `G`, `.$2`, `render`, `add`, `add`, `dO`, `bJ`?**
  _High betweenness centrality (0.040) - this node is a cross-community bridge._
- **Why does `ut()` connect `support.js` to `b`, `select.js`, `aM_`, `constructor`?**
  _High betweenness centrality (0.033) - this node is a cross-community bridge._
- **Why does `a3()` connect `.mount` to `.saveDraft`, `.updateBaggagePriceFromRates`, `d4`, `schedules.blade.php`, `Booking`, `fromObject`, `d`, `Schedule`, `TransportClass`, `deleteInDirection`, `a3`, `j_`, `dH`, `buildTicks`, `sendRequest`, `G`, `.$2`, `draw`, `jU`, `a1`, `bJ`?**
  _High betweenness centrality (0.029) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `a()` (e.g. with `loadEntrypoint()` and `_loadJSEntrypoint()`) actually correct?**
  _`a()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 235 inferred relationships involving `a()` (e.g. with `$0()` and `b()`) actually correct?**
  _`a()` has 235 INFERRED edges - model-reasoned connections that need verification._
- **Are the 498 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 498 INFERRED edges - model-reasoned connections that need verification._
- **Are the 496 inferred relationships involving `c()` (e.g. with `$0()` and `$1()`) actually correct?**
  _`c()` has 496 INFERRED edges - model-reasoned connections that need verification._