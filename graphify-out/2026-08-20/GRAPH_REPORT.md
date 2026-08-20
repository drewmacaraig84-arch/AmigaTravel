# Graph Report - AmigaTravel  (2026-08-20)

## Corpus Check
- 670 files · ~2,395,422 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17518 nodes · 54290 edges · 538 communities (499 shown, 39 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 8595 edges (avg confidence: 0.54)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `40cdb15c`
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
- OverallReports
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
- R
- Q
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
- Be
- aM_
- dD
- bw
- b6
- StatelessWidget
- add
- target
- post-autoload-dump
- _each
- 🚀 Part 1: Backend Setup (Laravel)
- win32_window.cpp
- getDatasetMeta
- du
- RunnerTests.swift
- require
- bZ
- d5
- nE
- hw
- R
- makeRequest
- flutter.js
- add
- ho
- constructor
- Widget
- composer.json
- sc
- bY
- G
- Deployment setup
- rs
- scripts
- color-picker.js
- manifest.json
- DatePicker
- GeneratedPluginRegistrant.swift
- wWinMain
- app.js
- manifest.json
- aYd
- appendBlockForElement
- manifest.json
- mergeNewHead
- booking-reschedule.blade.php
- dispatchEvent
- Flutter & Android Studio Setup Guide
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- graphify reference: extra exports and benchmark
- dispatchEvent
- pe
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
- Program
- why-travel-section.blade.php
- HasMany
- HasOne

## God Nodes (most connected - your core abstractions)
1. `a()` - 664 edges
2. `a()` - 664 edges
3. `b()` - 599 edges
4. `b()` - 599 edges
5. `c()` - 474 edges
6. `h()` - 474 edges
7. `c()` - 474 edges
8. `h()` - 474 edges
9. `p()` - 436 edges
10. `p()` - 436 edges

## Surprising Connections (you probably didn't know these)
- `ServiceCancellationTest` --references--> `Booking`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/Booking.php
- `ServiceCancellationTest` --references--> `FerryRoute`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/FerryRoute.php
- `ServiceCancellationTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/User.php
- `Td()` --indirect_call--> `Da()`  [INFERRED]
  public/app/canvaskit/experimental_webparagraph/canvaskit.js → public/js/filament/widgets/components/chart.js
- `ma()` --indirect_call--> `x()`  [INFERRED]
  public/app/canvaskit/skwasm.js → public/app/main.dart.js

## Import Cycles
- None detected.

## Communities (538 total, 39 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (512): $2$isClosing(), a03(), a0q(), a0V(), a1B(), a1x(), a2a(), a2D() (+504 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (398): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+390 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.08
Nodes (45): getOptions(), ad(), bl(), ["@blur"](), c(), ["@change"](), Dl(), Dr() (+37 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.04
Nodes (8): BookingLookup, Schedule, ServiceCancellationReplacementSchedule, ServiceCancellationManager, DateTimeInterface, HasOneThrough, Illuminate\Database\Eloquent\Relations\HasMany, ServiceCancellationTest

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.04
Nodes (92): a31(), a4v(), a5s(), a7O(), a9H(), a9t(), a9x(), aA8() (+84 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.04
Nodes (16): ManageWebsiteSettings, Operator, VehicleRate, WebsiteSetting, AirlineBaggageRuleSeeder, DatabaseSeeder, FerryRouteOperatorFixSeeder, GraciaEarningRuleSeeder (+8 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.04
Nodes (28): NotifyAffectedBookerJob, SendBookingConfirmationJob, BookingCancellation, self, BookingConfirmation, BookingCreated, PaymentProofReceived, RebookingRequested (+20 more)

### Community 10 - "download.blade.php"
Cohesion: 0.03
Nodes (97): $0(), $2$params(), a1C(), a1N(), a1s(), a25(), a27(), a2n() (+89 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.02
Nodes (175): a09(), a0l(), a17(), a1e(), a1M(), a1o(), a2K(), a2X() (+167 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (558): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+550 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (113): acquireContext(), Ag(), beforeDatasetDraw(), beforeDatasetsDraw(), bh(), bn(), Br(), color() (+105 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (25): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+17 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (128): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), canRedo(), canSyncDocumentView() (+120 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (196): t(), u(), _a(), Aa(), Ac(), Ae(), af(), ai() (+188 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (123): Yn(), aa(), ac(), Ai(), alpha(), an(), Ao(), applyStack() (+115 more)

### Community 20 - "Booking"
Cohesion: 0.02
Nodes (137): a1Z(), a26(), a2K(), a3c(), a4U(), a7F(), a7Z(), a94() (+129 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (100): addAssetsToHeadTagOfPage(), addDebounceOrThrottle(), _arrayLikeToArray(), _arrayWithoutHoles(), [attribute](), bindInputValue(), bindStyles(), callAndClearComponentDebounces() (+92 more)

### Community 22 - "User.php"
Cohesion: 0.04
Nodes (28): CreatesApplication, dismissCancellationReminder, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, requestCancellation, selectRebookingDepartureAccommodation(, selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectRebookingReturnAccommodation( (+20 more)

### Community 23 - "draw"
Cohesion: 0.05
Nodes (90): adjustHitBoxes(), ae(), af(), afterDraw(), calculateLabelRotation(), _computeGridLineItems(), _computeLabelArea(), _computeTitleHeight() (+82 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (512): $2$isClosing(), a03(), a0q(), a0V(), a1B(), a2a(), a2D(), a2e() (+504 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.03
Nodes (59): appendChild(), au(), bo(), bt(), cf(), corraleCommitsIntoPools(), cp(), createAndSendNewPool() (+51 more)

### Community 26 - "k"
Cohesion: 0.07
Nodes (43): _a(), active(), add(), al(), _animateOptions(), ba(), _cachedScopes(), configure() (+35 more)

### Community 27 - "select.js"
Cohesion: 0.07
Nodes (69): [g](), [x](), l(), $c(), D(), E(), Ea(), g() (+61 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.03
Nodes (123): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), canBeGroupedWith(), canDecreaseBlockAttributeLevel() (+115 more)

### Community 29 - "_update"
Cohesion: 0.04
Nodes (87): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+79 more)

### Community 30 - "fromObject"
Cohesion: 0.04
Nodes (77): $2$alignmentPolicy(), a2o(), a4p(), a56(), a5T(), a84(), a_1(), aAN() (+69 more)

### Community 31 - "constructor"
Cohesion: 0.04
Nodes (82): ar(), Bl(), cf(), chartOptionScopes(), clone(), constructor(), create(), describe() (+74 more)

### Community 32 - "d"
Cohesion: 0.03
Nodes (121): $3$color$endFraction$startFraction(), a05(), a08(), a0E(), a1u(), a3S(), a3y(), a4r() (+113 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (340): a0h(), a0I(), a1Q(), a20(), a21(), a2C(), a2I(), a2M() (+332 more)

### Community 34 - "H"
Cohesion: 0.03
Nodes (119): $3$color$endFraction$startFraction(), a05(), a08(), a1u(), a3S(), a3y(), a40(), a4r() (+111 more)

### Community 35 - "TransportClass"
Cohesion: 0.01
Nodes (210): $1$1(), a0c(), a0f(), a13(), a1f(), a1h(), a1J(), a2F() (+202 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.02
Nodes (114): a1Z(), a26(), a3c(), a3P(), a4U(), a94(), a9f(), a9p() (+106 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (48): addAssetsToHeadTagOfPage(), applyUpdates(), [attribute](), callAndClearComponentDebounces(), children(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), closestComponent() (+40 more)

### Community 38 - "add"
Cohesion: 0.07
Nodes (54): input(), call(), checkIdentityKeys(), cleanupAttributes(), clear(), createArrayInstrumentations(), createForEach(), createGetter() (+46 more)

### Community 39 - "User"
Cohesion: 0.09
Nodes (34): a28(), a3P(), a58(), a5a(), a5K(), a5p(), a_n(), aF9() (+26 more)

### Community 40 - "a3"
Cohesion: 0.04
Nodes (108): $1$allowPlatformDefault(), a0k(), a0S(), a0T(), a0y(), a13(), a4l(), a5D() (+100 more)

### Community 41 - "x"
Cohesion: 0.08
Nodes (87): $, blp(), blp(), Sg(), ad(), at(), B(), br() (+79 more)

### Community 42 - "j_"
Cohesion: 0.02
Nodes (182): $3(), $5(), a00(), a0A(), a0B(), a0W(), a0Z(), a16() (+174 more)

### Community 43 - "gv"
Cohesion: 0.05
Nodes (17): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditFerryRoute, EditGraciaEarningRule, EditHotel (+9 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (10): Bi(), bn(), ji(), kd(), Ri(), te(), Vi(), de (+2 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.07
Nodes (31): addCleanup(), applyUpdates(), cleanup(), constructor(), dataSet(), deepClone(), deferHandlingDirectives(), destroyComponent() (+23 more)

### Community 46 - "_update"
Cohesion: 0.07
Nodes (51): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterDraw(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+43 more)

### Community 47 - "ListRecords"
Cohesion: 0.03
Nodes (25): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+17 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.05
Nodes (32): A(), b(), c(), e(), f(), fc(), g(), gc() (+24 more)

### Community 49 - "getContext"
Cohesion: 0.07
Nodes (39): addEventListener(), average(), bindResponsiveEvents(), ch(), cu(), dataset(), dn(), Du() (+31 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (52): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+44 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (57): attachmentManagerDidRequestRemovalOfAttachment(), breakFormattedBlock(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), compositionDidRequestChangingSelectionToLocationRange() (+49 more)

### Community 52 - "AC"
Cohesion: 0.09
Nodes (33): add(), addCall(), addResolver(), bindClasses(), bufferPoolingForFiveMs(), cloneIfObject(), colocateCommitsByComponent(), corraleCommitsIntoPools() (+25 more)

### Community 53 - "push"
Cohesion: 0.07
Nodes (49): acquireContext(), adjustHitBoxes(), bc(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight(), _createItems() (+41 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.04
Nodes (74): a01(), a02(), a32(), a7F(), a_j(), aA6(), afX(), ag3() (+66 more)

### Community 55 - "Voucher"
Cohesion: 0.04
Nodes (94): $2$alignmentPolicy(), a0A(), a0B(), a0Z(), a2o(), a4p(), a56(), a82() (+86 more)

### Community 56 - "qt"
Cohesion: 0.03
Nodes (25): CreateBookingAction, MyPage, DiscountController, Accommodation, Discount, ScheduleAccommodation, ScheduleTransportClass, TransportClass (+17 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (32): A(), b(), Ba(), Bd(), c(), d(), E(), eb() (+24 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (298): $1(), a18(), a1a(), a1w(), a2y(), a3h(), a3J(), a4i() (+290 more)

### Community 59 - "aQ"
Cohesion: 0.03
Nodes (47): AdminNotifications, ManagePaymentSettings, ManageRebookings, ManageTransportAccommodation, Collection, StaffPerformance, CreateAccommodation, CreateAirlineBaggageRule (+39 more)

### Community 60 - "buildTicks"
Cohesion: 0.09
Nodes (6): Action, ManageProofs, Transaction, BinaryFileResponse, Filament\Actions\Concerns\InteractsWithActions, Filament\Actions\Contracts\HasActions

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.03
Nodes (29): CreateServiceCancellation, GraciaPointsController, NotificationController, ReferralController, AirlineBaggageRule, AppNotification, DeletedVirtualNotification, GraciaEarningRule (+21 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (163): Qt(), _a(), aa(), Ae(), ai(), apply(), ar(), at() (+155 more)

### Community 63 - "gO"
Cohesion: 0.02
Nodes (164): $2$from$to(), a0d(), a0x(), a1P(), a2g(), a2H(), a2l(), a33() (+156 more)

### Community 64 - "RelationManager"
Cohesion: 0.11
Nodes (26): afterDatasetsUpdate(), generateLabels(), getDatasetMeta(), getDataVisibility(), _getLegendItemAt(), getMaxBorderWidth(), _getSortedDatasetMetas(), getStyle() (+18 more)

### Community 65 - "I"
Cohesion: 0.11
Nodes (26): afterDatasetsUpdate(), _d(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), _handleEvent() (+18 more)

### Community 66 - "i"
Cohesion: 0.04
Nodes (102): ad(), Ah(), applyStack(), aspectRatio(), bf(), buildTicks(), C(), Ca() (+94 more)

### Community 67 - "get"
Cohesion: 0.03
Nodes (116): $2$from$to(), a0d(), a0x(), a1P(), a2g(), a2H(), a2l(), a33() (+108 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (59): ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState, ContactScreen, _ContactScreenState (+51 more)

### Community 69 - "setAttribute"
Cohesion: 0.05
Nodes (56): attachFiles(), beforeinput(), box(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), constructor() (+48 more)

### Community 70 - "a"
Cohesion: 0.05
Nodes (54): addInitSelector(), base64toBlob(), cleanupModal(), containsTargets(), contentIsFromDump(), createReactiveEffect(), effect2(), enableTracking() (+46 more)

### Community 71 - "a5"
Cohesion: 0.08
Nodes (34): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateControllers(), buildOrUpdateElements(), cancel(), _createDescriptors() (+26 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (23): actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo(), duration() (+15 more)

### Community 73 - "s"
Cohesion: 0.06
Nodes (57): disabled(), ar(), buildTicks(), calculateLabelRotation(), _calculatePadding(), _computeAngle(), _computeGridLineItems(), _computeLabelItems() (+49 more)

### Community 74 - "EditRecord"
Cohesion: 0.16
Nodes (15): average(), getBasePosition(), getBaseValue(), getCenterPoint(), getProps(), hasValue(), hs(), inXRange() (+7 more)

### Community 75 - "Controller"
Cohesion: 0.06
Nodes (56): buildOrUpdateScales(), C(), cl(), Co(), _computeLabelSizes(), cr(), Ct(), D() (+48 more)

### Community 76 - "updateElements"
Cohesion: 0.08
Nodes (38): as(), _calculateBarIndexPixels(), calculateCircumference(), _circumference(), countVisibleElements(), datasetAnimationScopeKeys(), dr(), format() (+30 more)

### Community 77 - "sendRequest"
Cohesion: 0.01
Nodes (272): $3$crossAxisPosition$mainAxisPosition(), a(), a0G(), a0J(), a0o(), a12(), a14(), a15() (+264 more)

### Community 78 - "push"
Cohesion: 0.03
Nodes (34): CreateUser, EditUser, AdminNotificationController, JsonResponse, AccommodationController, BookingCalculateController, BookingController, PromotionController (+26 more)

### Community 79 - "o8"
Cohesion: 0.10
Nodes (28): allSelectors(), applyBindingsObject(), attributesOnly(), bind2(), byPriority(), clone(), cloneTree(), closestIdRoot() (+20 more)

### Community 80 - "E"
Cohesion: 0.08
Nodes (36): canAcceptDataTransfer(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange(), decreaseNestingLevel(), didMouseDown() (+28 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 82 - "skwasm.js"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ka(), La(), ma(), n(), Pc(), q() (+5 more)

### Community 83 - "$1"
Cohesion: 0.01
Nodes (256): a09(), a0l(), a17(), a1e(), a1M(), a1N(), a1o(), a1s() (+248 more)

### Community 84 - "push"
Cohesion: 0.06
Nodes (41): ArrowLeft(), ArrowRight(), attachmentForFile(), attributesForFile(), canSetCurrentTextAttribute(), compositionShouldAcceptFile(), didClickAttachment(), editAttachment() (+33 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (44): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), getBoundingClientRect(), getClientRectFromClippingAncestor(), getClientRects(), getClippingElementAncestors() (+36 more)

### Community 86 - "ManageProofs"
Cohesion: 0.09
Nodes (27): actionIsExternal(), canBeConsolidatedWith(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidRender(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL() (+19 more)

### Community 87 - "Dt"
Cohesion: 0.32
Nodes (4): e(), i(), Ni(), o()

### Community 88 - "preload"
Cohesion: 0.38
Nodes (7): bs(), ds(), Fr(), ft(), Ii(), ni(), oi()

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (12): d(), Ga(), Ja(), Ka(), La(), ma(), n(), q() (+4 more)

### Community 91 - "b5"
Cohesion: 0.09
Nodes (30): afterAutoSkip(), Bi(), buildLookupTable(), determineDataLimits(), endOf(), Fi(), getAllParsedValues(), getDataTimestamps() (+22 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (200): a11(), a1v(), a5(), a6G(), a_2(), ac3(), ag1(), aK() (+192 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (306): $1(), a18(), a1a(), a1w(), a2y(), a3h(), a3J(), a4i() (+298 more)

### Community 94 - "draw"
Cohesion: 0.03
Nodes (114): $0(), $2$params(), a1C(), a25(), a27(), a2n(), a39(), a3R() (+106 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.03
Nodes (111): _a(), abutsStart(), after(), afterAutoSkip(), Ai(), Al(), before(), buildLookupTable() (+103 more)

### Community 97 - "$0"
Cohesion: 0.09
Nodes (31): Bn(), En(), eo(), Gl(), gn(), ha(), Ie(), je() (+23 more)

### Community 98 - "jU"
Cohesion: 0.05
Nodes (97): $1$allowPlatformDefault(), a0k(), a0S(), a0T(), a0y(), a4l(), a5D(), a5i() (+89 more)

### Community 99 - "M"
Cohesion: 0.06
Nodes (31): A(), b(), c(), e(), f(), fc(), g(), gc() (+23 more)

### Community 100 - "get"
Cohesion: 0.06
Nodes (27): A(), b(), c(), e(), ee(), f(), fc(), g() (+19 more)

### Community 101 - "createMorphContext"
Cohesion: 0.08
Nodes (37): appendChild(), cloneNode(), cloneScriptTag(), closestComponent(), componentIsMissingProperty(), createElement(), createMorphContext(), extractUriAndQueryString() (+29 more)

### Community 102 - "navigate_default"
Cohesion: 0.07
Nodes (39): addScopeToNode(), autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject() (+31 more)

### Community 103 - "aG"
Cohesion: 0.04
Nodes (77): a01(), a02(), a32(), a4C(), a62(), a6I(), a6N(), a_j() (+69 more)

### Community 104 - "render"
Cohesion: 0.06
Nodes (42): xt(), cacheViewForObject(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync() (+34 more)

### Community 106 - "Vn"
Cohesion: 0.06
Nodes (6): BookingReschedule, PaymentProof, PromoImageManager, UserDashboard, Livewire\Component, Livewire\WithFileUploads

### Community 107 - "add"
Cohesion: 0.08
Nodes (30): ap(), bp(), bu(), children(), er(), gf(), Ja(), method() (+22 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.05
Nodes (52): Ad(), Cd(), dd(), Ed(), Fd(), Gd(), Hd(), Jd() (+44 more)

### Community 109 - "add"
Cohesion: 0.07
Nodes (51): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap() (+43 more)

### Community 110 - "notification_service.dart"
Cohesion: 0.29
Nodes (7): build, _fetchBookingAndNavigate, _goNext, _goToSchedule, handleNotificationTap, _showPackageDetailsModal, MaterialPageRoute

### Community 111 - "gaf"
Cohesion: 0.19
Nodes (19): Br(), dd(), fn(), id(), Is(), jc(), Lr(), Ls() (+11 more)

### Community 112 - "le"
Cohesion: 0.06
Nodes (14): d(), Ga(), Ja(), Ka(), La(), ma(), n(), Pc() (+6 more)

### Community 113 - "bi"
Cohesion: 0.12
Nodes (29): destroyTree(), putPersistantElementsBack(), At(), bd(), Be(), Bi(), cu(), De() (+21 more)

### Community 114 - "fn"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ka(), La(), ma(), n(), Pc(), q() (+5 more)

### Community 115 - "Ve"
Cohesion: 0.13
Nodes (35): as(), Ce(), cs(), Ct(), d(), ed(), ei(), Es() (+27 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.09
Nodes (27): addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateControllers(), buildOrUpdateElements(), _dataCheck(), _destroy() (+19 more)

### Community 117 - "Ra"
Cohesion: 0.07
Nodes (36): ai(), al(), ba(), bc(), ci(), co(), cr(), dc() (+28 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.16
Nodes (6): saDP(), saDP(), a(), M, w(), x()

### Community 120 - "a1"
Cohesion: 0.04
Nodes (82): $3(), a31(), a44(), a4v(), a5c(), a5s(), a9b(), a9t() (+74 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.11
Nodes (21): a(), a(), a(), a(), a(), a(), At(), beforeDraw() (+13 more)

### Community 122 - "aW_"
Cohesion: 0.02
Nodes (196): $5(), a00(), a0E(), a0G(), a0W(), a16(), a1d(), a1g() (+188 more)

### Community 123 - "navigate_default"
Cohesion: 0.12
Nodes (11): c(), f(), g(), i(), J(), l(), o(), p() (+3 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "R"
Cohesion: 0.11
Nodes (19): beforeDraw(), eh(), getSortedVisibleDatasetMetas(), getVisibleDatasetCount(), Gi(), ih(), Me(), mo() (+11 more)

### Community 126 - "Q"
Cohesion: 0.22
Nodes (5): fromString(), h(), N(), Q, r()

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (381): a0c(), a0f(), a0h(), a0I(), a1Q(), a20(), a21(), a2C() (+373 more)

### Community 128 - "start"
Cohesion: 0.22
Nodes (18): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), evaluate2(), fn(), getAlignment(), getAlignmentAxis(), getAlignmentSides() (+10 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.03
Nodes (32): CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, IssueSlaVouchers, NotifyExpiringVouchers, PurgeExpiredProofs, PurgeExpiredSchedules, RetroactiveGraciaPoints (+24 more)

### Community 131 - "gt"
Cohesion: 0.04
Nodes (71): Ac(), alpha(), an(), as(), At(), Au(), ba(), Bs() (+63 more)

### Community 132 - "railway-start.sh"
Cohesion: 0.07
Nodes (26): APP_DEBUG, APP_ENV, APP_NAME, APP_URL, CACHE_STORE, DB_CONNECTION, DB_DATABASE, DB_HOST (+18 more)

### Community 133 - "Vehicle"
Cohesion: 0.20
Nodes (24): add(), adjustScroll(), animate(), autoAnimate(), cleanUp(), deletePosition(), forEach(), getCoords() (+16 more)

### Community 134 - "St"
Cohesion: 0.08
Nodes (38): second(), base64toBlob(), cleanupModal(), contentIsFromDump(), extractScriptTagContent(), extractStreamObjects(), fromQueryString(), getEncodedSnapshotWithLatestChildrenMergedIn() (+30 more)

### Community 135 - "d4"
Cohesion: 0.60
Nodes (6): A(), connectedCallback(), Ge(), required(), setCustomValidity(), setFormValue()

### Community 136 - "call"
Cohesion: 0.10
Nodes (28): directive2(), cleanup(), cloneIfObject(), customDirectiveHasBeenRegistered(), destroyComponent(), directive(), dirtyTargets(), ensureLivewireScriptIsntMisplaced() (+20 more)

### Community 137 - "d4"
Cohesion: 0.02
Nodes (405): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+397 more)

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
Cohesion: 0.09
Nodes (33): active(), add(), _animateOptions(), Bi(), _cachedScopes(), cancel(), _createAnimations(), _createDescriptors() (+25 more)

### Community 142 - ".$1"
Cohesion: 0.05
Nodes (65): addRootSelector(), attributeShouldntBePreservedIfFalsy(), bind(), bindAttribute(), bindAttributeAndProperty(), camelCase(), cloneIfObject2(), closestDataStack() (+57 more)

### Community 143 - "echo.js"
Cohesion: 0.07
Nodes (42): ar(), b(), Be(), Ce(), cr(), De(), di(), Dt() (+34 more)

### Community 144 - "m"
Cohesion: 0.09
Nodes (22): @pragma, _channelDescription, _channelId, _channelName, clearBadge, _firebaseMessagingBackgroundHandler, initialize, NotificationService (+14 more)

### Community 146 - "$0"
Cohesion: 0.50
Nodes (4): be(), be(), Ia(), S3()

### Community 147 - "gO"
Cohesion: 0.50
Nodes (4): be(), be(), Ia(), S3()

### Community 149 - "package.json"
Cohesion: 0.09
Nodes (22): alpinejs, apexcharts, concurrently, laravel-vite-plugin, dependencies, alpinejs, apexcharts, devDependencies (+14 more)

### Community 150 - "ViewRecord"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 151 - "replacement_booking_screen.dart"
Cohesion: 0.04
Nodes (48): dart:convert, build, _confirmPassController, createState, _emailController, ForgotPasswordScreen, _ForgotPasswordScreenState, _isLoading (+40 more)

### Community 152 - "kr"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (341): $3$crossAxisPosition$mainAxisPosition(), a(), a0J(), a0o(), a12(), a14(), a15(), a19() (+333 more)

### Community 156 - "dD"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 157 - "bw"
Cohesion: 0.10
Nodes (28): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+20 more)

### Community 158 - "b6"
Cohesion: 0.02
Nodes (165): $1$1(), a1f(), a1h(), a1J(), a2F(), a2j(), a48(), a49() (+157 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "add"
Cohesion: 0.08
Nodes (30): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), cs() (+22 more)

### Community 161 - "target"
Cohesion: 0.15
Nodes (19): search(), url(), cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl() (+11 more)

### Community 163 - "post-autoload-dump"
Cohesion: 0.50
Nodes (4): post-autoload-dump, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump, @php artisan filament:upgrade, @php artisan package:discover --ansi

### Community 164 - "_each"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 165 - "🚀 Part 1: Backend Setup (Laravel)"
Cohesion: 0.09
Nodes (22): 1. Clone the repository, 1. Navigate to the Flutter folder, 2. Install Flutter Dependencies, 2. Install PHP Dependencies, 3. Install Node Dependencies, 3. Update the API Endpoint, 4. Environment Configuration, 4. Run the App (+14 more)

### Community 166 - "win32_window.cpp"
Cohesion: 0.18
Nodes (13): wchar_t, Scale(), Create, Destroy, Win32Window::Win32Window(), WindowClassRegistrar, class_registered_, GetWindowClass (+5 more)

### Community 168 - "getDatasetMeta"
Cohesion: 0.50
Nodes (4): post-create-project-cmd, @php artisan key:generate --ansi, @php artisan migrate --graceful --ansi, @php -r \"file_exists('database/database.sqlite') || touch('database/database.sqlite');\

### Community 169 - "du"
Cohesion: 0.07
Nodes (55): A(), aa(), Ac(), add(), addCall(), addResolver(), ae(), af() (+47 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.25
Nodes (11): aa(), determineDataLimits(), Dh(), _getLabelBounds(), getMinMax(), _getOtherScale(), getUserBounds(), handleTickRangeOptions() (+3 more)

### Community 173 - "d5"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 174 - "nE"
Cohesion: 0.20
Nodes (8): Any, AppDelegate, Bool, AppDelegate, Bool, FlutterAppDelegate, NSApplication, UIApplication

### Community 175 - "hw"
Cohesion: 0.15
Nodes (19): add(), addCall(), addResolver(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools(), createAndSendNewPool(), delete() (+11 more)

### Community 176 - "R"
Cohesion: 0.67
Nodes (3): ld(), ld(), R()

### Community 178 - "makeRequest"
Cohesion: 0.12
Nodes (21): cancelUpload(), Di(), gt(), handleS3PreSignedUrl(), handleSignedUrl(), Hi(), ji(), makeRequest() (+13 more)

### Community 179 - "flutter.js"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 180 - "add"
Cohesion: 0.12
Nodes (20): addCleanup(), applyUpdates(), Cl(), cleanup(), constructor(), Ee(), extractTypeModifiersAndValue(), ip() (+12 more)

### Community 182 - "ho"
Cohesion: 0.22
Nodes (5): BookingsExport, BookingExportController, Illuminate\Http\Response, Maatwebsite\Excel\Concerns\Exportable, Maatwebsite\Excel\Concerns\WithMultipleSheets

### Community 183 - "constructor"
Cohesion: 0.16
Nodes (16): addCleanup(), constructor(), deepClone(), diff(), extractData(), generateWireObject(), initComponent(), isArray() (+8 more)

### Community 185 - "Widget"
Cohesion: 0.50
Nodes (3): confirmAdd, confirmReplace, deleteImage(

### Community 186 - "composer.json"
Cohesion: 0.12
Nodes (16): autoload-dev, psr-4, description, extra, laravel, keywords, dont-discover, license (+8 more)

### Community 187 - "sc"
Cohesion: 0.06
Nodes (30): ld(), A(), b(), c(), e(), ee(), f(), fc() (+22 more)

### Community 188 - "bY"
Cohesion: 0.02
Nodes (216): a11(), a1v(), a5(), a6G(), a_2(), ac3(), ag1(), agX() (+208 more)

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

### Community 216 - "DatePicker"
Cohesion: 0.06
Nodes (8): ViewBooking, ServiceCancellationResource, TransportClassResource, DatePicker, normalize_operator_name(), operator_is_ferry(), storage_asset_path(), Illuminate\Support\HtmlString

### Community 222 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.14
Nodes (13): file_selector_macos, firebase_core, firebase_messaging, flutter_app_badger, RegisterGeneratedPlugins(), flutter_local_notifications, FlutterPluginRegistry, Foundation (+5 more)

### Community 223 - "wWinMain"
Cohesion: 0.24
Nodes (9): wWinMain(), string, wchar_t, CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16(), _In_, _In_opt_ (+1 more)

### Community 231 - "app.js"
Cohesion: 0.13
Nodes (15): C(), D(), J(), O(), U(), v(), X(), a() (+7 more)

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.06
Nodes (49): backspace(), createLinkHTML(), cut(), d(), delete(), deleteByComposition(), deleteByCut(), deleteByDrag() (+41 more)

### Community 244 - "appendBlockForElement"
Cohesion: 0.19
Nodes (19): It(), appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes() (+11 more)

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 247 - "mergeNewHead"
Cohesion: 0.22
Nodes (13): cloneScriptTag(), extractUriAndQueryString(), ifTheQueryStringChangedSinceLastRequest(), ignoreAttributes(), injectScriptTagAndWaitForItToFullyLoad(), isAsset(), isScript(), isTracked() (+5 more)

### Community 250 - "booking-reschedule.blade.php"
Cohesion: 0.20
Nodes (9): closeRefundForm, openRefundForm, selectDepartureAccommodation(, selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectReturnAccommodation(, selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === , setStep(, submitCancelAndRefund (+1 more)

### Community 251 - "dispatchEvent"
Cohesion: 0.11
Nodes (28): target(), call(), componentsByName(), containsTargets(), dispatch(), dispatch2(), dispatchEvent(), dispatchEvents() (+20 more)

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

### Community 263 - "dispatchEvent"
Cohesion: 0.10
Nodes (29): cancelUpload(), componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal() (+21 more)

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
- **950 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+945 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **39 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `x` to `.mount`, `d4`, `Booking`, `draw`, `aM_`, `select.js`, `Schedule`, `deleteInDirection`, `j_`, `te`, `getSelectedRange`, `dH`, `bY`, `support.js`, `setAttribute`, `sendRequest`, `E`, `G`, `.$2`, `$0`, `render`, `add`, `aW_`, `navigate_default`, `bJ`?**
  _High betweenness centrality (0.305) - this node is a cross-community bridge._
- **Why does `Q` connect `Q` to `d`, `Schedule`, `H`, `get`, `j_`, `navigate_default`, `locationFromPosition`, `echo.js`, `aYd`, `getSelectedRange`, `push`, `aW_`, `select.js`, `bJ`, `gO`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Why does `xc()` connect `$0` to `livewire.min.js`, `du`, `Ra`, `x`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Are the 224 inferred relationships involving `a()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 224 inferred relationships involving `a()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._