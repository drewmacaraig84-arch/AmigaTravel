# Graph Report - AmigaTravel  (2026-08-20)

## Corpus Check
- 670 files · ~2,395,666 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17518 nodes · 54290 edges · 538 communities (499 shown, 39 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 8595 edges (avg confidence: 0.54)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `ad8257dc`
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
- a
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
- `ServiceCancellationTest` --references--> `Schedule`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/Schedule.php
- `ServiceCancellationTest` --references--> `User`  [EXTRACTED]
  tests/Feature/ServiceCancellationTest.php → app/Models/User.php
- `Td()` --indirect_call--> `Da()`  [INFERRED]
  public/app/canvaskit/experimental_webparagraph/canvaskit.js → public/js/filament/widgets/components/chart.js

## Import Cycles
- None detected.

## Communities (538 total, 39 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (522): a03(), a0q(), a0V(), a1B(), a2a(), a2B(), a2D(), a2e() (+514 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (384): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+376 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.07
Nodes (56): getOptions(), ad(), bl(), ["@blur"](), Bn(), c(), ["@change"](), Dl() (+48 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.05
Nodes (5): BookingLookup, Schedule, DateTimeInterface, HasOneThrough, Illuminate\Database\Eloquent\Relations\HasMany

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.02
Nodes (146): a09(), a0l(), a2X(), a3X(), a4b(), a7O(), a90(), a9C() (+138 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.04
Nodes (17): ManageWebsiteSettings, Operator, VehicleRate, WebsiteSetting, AirlineBaggageRuleSeeder, DatabaseSeeder, DiscountSeeder, FerryRouteOperatorFixSeeder (+9 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.03
Nodes (29): NotifyAffectedBookerJob, SendBookingConfirmationJob, BookingCancellation, self, BookingConfirmation, BookingCreated, PaymentProofReceived, RebookingRequested (+21 more)

### Community 10 - "download.blade.php"
Cohesion: 0.02
Nodes (204): $0(), $2$params(), a0c(), a17(), a1C(), a1e(), a1l(), a1N() (+196 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.03
Nodes (109): a09(), a0l(), a2X(), a3X(), a4b(), a90(), a9e(), a9I() (+101 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (558): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+550 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (131): acquireContext(), active(), addControllers(), addPlugins(), addScales(), Ag(), _animateOptions(), beforeDatasetDraw() (+123 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (26): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+18 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (135): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), canRedo(), canSyncDocumentView() (+127 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (196): t(), u(), _a(), Aa(), Ac(), Ae(), af(), ai() (+188 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (127): Yn(), aa(), alpha(), an(), aspectRatio(), be(), beforeDatasetDraw(), beforeDatasetsDraw() (+119 more)

### Community 20 - "Booking"
Cohesion: 0.03
Nodes (107): a0x(), a2C(), a2H(), a3I(), a3R(), a5O(), a5W(), a5X() (+99 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (100): addAssetsToHeadTagOfPage(), addDebounceOrThrottle(), _arrayLikeToArray(), _arrayWithoutHoles(), [attribute](), bindInputValue(), bindStyles(), callAndClearComponentDebounces() (+92 more)

### Community 22 - "User.php"
Cohesion: 0.04
Nodes (29): CreatesApplication, dismissCancellationReminder, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, requestCancellation, selectRebookingDepartureAccommodation(, selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectRebookingReturnAccommodation( (+21 more)

### Community 23 - "draw"
Cohesion: 0.05
Nodes (83): adjustHitBoxes(), ae(), af(), afterDraw(), calculateLabelRotation(), _computeGridLineItems(), _computeLabelArea(), _computeTitleHeight() (+75 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (520): $2$isClosing(), a03(), a0q(), a0V(), a18(), a1a(), a1B(), a2a() (+512 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.03
Nodes (70): addResolver(), ap(), au(), bc(), bo(), bt(), cf(), cleanup() (+62 more)

### Community 26 - "k"
Cohesion: 0.07
Nodes (43): _a(), active(), add(), al(), _animateOptions(), ba(), _cachedScopes(), configure() (+35 more)

### Community 27 - "select.js"
Cohesion: 0.07
Nodes (69): [g](), [x](), l(), $c(), D(), E(), Ea(), g() (+61 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.04
Nodes (113): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), canBeGroupedWith(), canDecreaseBlockAttributeLevel() (+105 more)

### Community 29 - "_update"
Cohesion: 0.03
Nodes (130): addBox(), addElements(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion() (+122 more)

### Community 30 - "fromObject"
Cohesion: 0.13
Nodes (22): afQ(), agX(), aiq(), am2(), am5(), aqM(), avF(), biK() (+14 more)

### Community 31 - "constructor"
Cohesion: 0.05
Nodes (65): Bl(), cf(), clone(), create(), Dl(), dtFormatter(), eg(), el() (+57 more)

### Community 32 - "d"
Cohesion: 0.04
Nodes (79): $1$allowPlatformDefault(), a01(), a02(), a32(), a62(), a6I(), a6N(), a_j() (+71 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (338): a0I(), a1M(), a1Q(), a28(), a2I(), a2M(), a2r(), a2S() (+330 more)

### Community 34 - "H"
Cohesion: 0.02
Nodes (222): $2$from$to(), $3$color$endFraction$startFraction(), a05(), a08(), a0d(), a1u(), a2g(), a2l() (+214 more)

### Community 35 - "TransportClass"
Cohesion: 0.02
Nodes (140): $1$1(), $2$isClosing(), a13(), a1f(), a1h(), a1J(), a2j(), a47() (+132 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.03
Nodes (92): a2K(), a4U(), a97(), a98(), a9f(), a9p(), aaC(), aak() (+84 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (48): addAssetsToHeadTagOfPage(), applyUpdates(), [attribute](), callAndClearComponentDebounces(), children(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), closestComponent() (+40 more)

### Community 38 - "add"
Cohesion: 0.07
Nodes (54): input(), call(), checkIdentityKeys(), cleanupAttributes(), clear(), createArrayInstrumentations(), createForEach(), createGetter() (+46 more)

### Community 39 - "User"
Cohesion: 0.04
Nodes (76): a0x(), a1P(), a2F(), a2H(), a7F(), a7G(), a7Y(), a88() (+68 more)

### Community 40 - "a3"
Cohesion: 0.05
Nodes (94): $1$allowPlatformDefault(), a0k(), a0S(), a0T(), a0y(), a4l(), a5D(), a5i() (+86 more)

### Community 41 - "x"
Cohesion: 0.08
Nodes (87): $, blp(), blp(), Sg(), ad(), at(), B(), br() (+79 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (279): $0(), $2$params(), $3(), $5(), a00(), a0A(), a0B(), a0E() (+271 more)

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
Cohesion: 0.05
Nodes (50): $2$alignmentPolicy(), a20(), a21(), a2o(), a4p(), a7U(), a84(), a_1() (+42 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.05
Nodes (32): A(), b(), c(), e(), f(), fc(), g(), gc() (+24 more)

### Community 49 - "getContext"
Cohesion: 0.05
Nodes (55): Ac(), alpha(), an(), as(), At(), Au(), ba(), Bs() (+47 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (52): ba(), be(), bi(), c(), ca(), clickPercent(), constructor(), de() (+44 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (57): attachmentManagerDidRequestRemovalOfAttachment(), breakFormattedBlock(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), compositionDidRequestChangingSelectionToLocationRange() (+49 more)

### Community 52 - "AC"
Cohesion: 0.06
Nodes (35): a48(), a7B(), a7Z(), a9f(), aag(), aD3(), aDt(), amu() (+27 more)

### Community 53 - "push"
Cohesion: 0.06
Nodes (63): acquireContext(), adjustHitBoxes(), bc(), Bl(), calculateLabelRotation(), clear(), _computeLabelArea(), _computeTitleHeight() (+55 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.02
Nodes (140): a01(), a02(), a0f(), a4C(), a62(), a6I(), a6N(), a7P() (+132 more)

### Community 55 - "Voucher"
Cohesion: 0.04
Nodes (74): $2$alignmentPolicy(), a20(), a21(), a2o(), a4p(), a56(), a84(), a_1() (+66 more)

### Community 56 - "qt"
Cohesion: 0.04
Nodes (17): CreateBookingAction, Accommodation, Discount, ScheduleAccommodation, ScheduleTransportClass, TransportClass, Voucher, BookingObserver (+9 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (32): A(), b(), Ba(), Bd(), c(), d(), E(), eb() (+24 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (303): $1(), a1w(), a2y(), a3h(), a3J(), a4i(), a5e(), a60() (+295 more)

### Community 59 - "aQ"
Cohesion: 0.02
Nodes (54): CreateAccommodation, ListAccommodations, CreateAirlineBaggageRule, ListAirlineBaggageRules, ListApkUsers, BookingsRelationManager, GraciaPointLedgersRelationManager, CreateAppNotification (+46 more)

### Community 60 - "buildTicks"
Cohesion: 0.09
Nodes (29): ac(), Ai(), Ao(), applyStack(), Bi(), ca(), determineDataLimits(), endOf() (+21 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.03
Nodes (31): CreateServiceCancellation, GraciaPointsController, NotificationController, ReferralController, AirlineBaggageRule, AppNotification, DeletedVirtualNotification, GraciaEarningRule (+23 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (163): Qt(), _a(), aa(), Ae(), ai(), apply(), ar(), at() (+155 more)

### Community 63 - "gO"
Cohesion: 0.09
Nodes (33): a28(), a3P(), a4N(), a58(), a5a(), a5K(), a5p(), a6F() (+25 more)

### Community 64 - "RelationManager"
Cohesion: 0.11
Nodes (26): afterDatasetsUpdate(), generateLabels(), getDatasetMeta(), getDataVisibility(), _getLegendItemAt(), getMaxBorderWidth(), _getSortedDatasetMetas(), getStyle() (+18 more)

### Community 65 - "I"
Cohesion: 0.10
Nodes (31): afterDatasetsUpdate(), buildOrUpdateControllers(), _d(), _destroyDatasetMeta(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth() (+23 more)

### Community 66 - "i"
Cohesion: 0.08
Nodes (38): ad(), bf(), buildTicks(), calculateCircumference(), _calculatePadding(), _circumference(), _computeAngle(), _computeLabelItems() (+30 more)

### Community 67 - "get"
Cohesion: 0.02
Nodes (211): $2$from$to(), $3$color$endFraction$startFraction(), a05(), a08(), a0d(), a2F(), a2g(), a2l() (+203 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (59): ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState, ContactScreen, _ContactScreenState (+51 more)

### Community 69 - "setAttribute"
Cohesion: 0.04
Nodes (73): attachFiles(), beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), constructor(), createLinkHTML() (+65 more)

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
Cohesion: 0.05
Nodes (67): disabled(), afterAutoSkip(), ar(), buildLookupTable(), buildTicks(), _calculatePadding(), _computeAngle(), _computeGridLineItems() (+59 more)

### Community 74 - "EditRecord"
Cohesion: 0.10
Nodes (25): average(), beforeDraw(), dataset(), getCenterPoint(), getProps(), getSortedVisibleDatasetMetas(), getVisibleDatasetCount(), hasValue() (+17 more)

### Community 75 - "Controller"
Cohesion: 0.09
Nodes (39): as(), C(), Co(), _computeLabelSizes(), cr(), diff(), Et(), format() (+31 more)

### Community 76 - "updateElements"
Cohesion: 0.09
Nodes (33): add(), addCall(), addResolver(), bindClasses(), bufferPoolingForFiveMs(), cloneIfObject(), colocateCommitsByComponent(), corraleCommitsIntoPools() (+25 more)

### Community 77 - "sendRequest"
Cohesion: 0.01
Nodes (323): $3$crossAxisPosition$mainAxisPosition(), a(), a0c(), a0G(), a0J(), a0o(), a12(), a14() (+315 more)

### Community 78 - "push"
Cohesion: 0.04
Nodes (28): CreateUser, EditUser, AdminNotificationController, JsonResponse, BookingController, ScheduleController, VoucherController, AuthController (+20 more)

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
Nodes (11): d(), Ga(), Ka(), La(), ma(), n(), q(), r() (+3 more)

### Community 83 - "$1"
Cohesion: 0.04
Nodes (85): a31(), a4v(), a5s(), a8g(), a9H(), a9t(), a9x(), aA8() (+77 more)

### Community 84 - "push"
Cohesion: 0.06
Nodes (40): attachmentForFile(), attributesForFile(), canSetCurrentTextAttribute(), compositionShouldAcceptFile(), cut(), didChangeAttributes(), didClickAttachment(), dragstart() (+32 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (44): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), getBoundingClientRect(), getClientRectFromClippingAncestor(), getClientRects(), getClippingElementAncestors() (+36 more)

### Community 86 - "ManageProofs"
Cohesion: 0.13
Nodes (19): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidEditAttachment() (+11 more)

### Community 87 - "Dt"
Cohesion: 0.32
Nodes (4): e(), i(), Ni(), o()

### Community 88 - "preload"
Cohesion: 0.38
Nodes (7): bs(), ds(), Fr(), ft(), Ii(), ni(), oi()

### Community 89 - "OverallReports"
Cohesion: 0.04
Nodes (25): Action, AdminNotifications, ManagePaymentSettings, ManageProofs, ManageRebookings, ManageTransportAccommodation, MyPage, OverallReports (+17 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (14): d(), Ga(), Ja(), Ka(), La(), ma(), n(), Pc() (+6 more)

### Community 91 - "b5"
Cohesion: 0.10
Nodes (27): _calculateBarIndexPixels(), calculateCircumference(), _circumference(), countVisibleElements(), datasetAnimationScopeKeys(), dr(), _getCircumference(), getParsed() (+19 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (182): a11(), a1v(), a5(), a6G(), a_2(), ag1(), aK(), an() (+174 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (233): a18(), a1a(), a2y(), a3J(), a_d(), aA1(), aB2(), aB5() (+225 more)

### Community 94 - "draw"
Cohesion: 0.01
Nodes (223): $1(), a1C(), a1w(), a1Z(), a26(), a27(), a2K(), a2n() (+215 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.03
Nodes (112): _a(), abutsStart(), after(), afterAutoSkip(), Ai(), Al(), before(), Br() (+104 more)

### Community 97 - "$0"
Cohesion: 0.14
Nodes (15): box(), canBeConsolidatedWith(), compositionControllerDidRender(), fromUCS2String(), getTargetDOMRange(), hasSameAttributesAsPiece(), hasSameConstructorAs(), hasSameStringValueAsPiece() (+7 more)

### Community 98 - "jU"
Cohesion: 0.04
Nodes (128): a0k(), a0S(), a0T(), a0y(), a2z(), a5c(), a5D(), a5i() (+120 more)

### Community 99 - "M"
Cohesion: 0.05
Nodes (34): A(), b(), c(), e(), f(), fc(), g(), gc() (+26 more)

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
Nodes (54): a0f(), a6C(), a95(), a96(), a_q(), aDq(), aDV(), aNk() (+46 more)

### Community 104 - "render"
Cohesion: 0.07
Nodes (31): xt(), cacheViewForObject(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync() (+23 more)

### Community 106 - "Vn"
Cohesion: 0.06
Nodes (6): BookingReschedule, PaymentProof, PromoImageManager, UserDashboard, Livewire\Component, Livewire\WithFileUploads

### Community 107 - "add"
Cohesion: 0.25
Nodes (11): ac1(), ajD(), b_d(), bau(), gme(), gmO(), gnf(), gnS() (+3 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.05
Nodes (52): Ad(), Cd(), dd(), Ed(), Fd(), Gd(), Hd(), Jd() (+44 more)

### Community 109 - "add"
Cohesion: 0.06
Nodes (54): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap() (+46 more)

### Community 110 - "notification_service.dart"
Cohesion: 0.29
Nodes (7): build, _fetchBookingAndNavigate, _goNext, _goToSchedule, handleNotificationTap, _showPackageDetailsModal, MaterialPageRoute

### Community 111 - "gaf"
Cohesion: 0.08
Nodes (37): destroyTree(), putPersistantElementsBack(), appendChild(), bd(), Bi(), Br(), cp(), dd() (+29 more)

### Community 112 - "le"
Cohesion: 0.06
Nodes (14): d(), Ga(), Ja(), Ka(), La(), ma(), n(), Pc() (+6 more)

### Community 113 - "bi"
Cohesion: 0.07
Nodes (52): al(), At(), ba(), Be(), ci(), co(), cr(), cu() (+44 more)

### Community 114 - "fn"
Cohesion: 0.06
Nodes (10): d(), ma(), n(), Pc(), q(), r(), Ra, t() (+2 more)

### Community 115 - "Ve"
Cohesion: 0.13
Nodes (35): as(), Ce(), cs(), Ct(), d(), ed(), ei(), Es() (+27 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.08
Nodes (32): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), ch(), cu(), dataset(), dn() (+24 more)

### Community 117 - "Ra"
Cohesion: 0.07
Nodes (37): add(), ae(), af(), ai(), Bf(), corraleCommitsIntoPools(), createAndSendNewPool(), da() (+29 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.13
Nodes (11): saDP(), saDP(), a(), h(), i(), J(), M, N() (+3 more)

### Community 120 - "a1"
Cohesion: 0.02
Nodes (125): $3(), a1M(), a31(), a44(), a4v(), a5s(), a7O(), a9b() (+117 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.22
Nodes (11): a(), a(), a(), a(), a(), a(), At(), Fa() (+3 more)

### Community 122 - "aW_"
Cohesion: 0.01
Nodes (212): a00(), a0A(), a0B(), a0E(), a0W(), a0Z(), a16(), a1d() (+204 more)

### Community 123 - "navigate_default"
Cohesion: 0.12
Nodes (9): c(), f(), g(), l(), p(), s(), v(), w() (+1 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "R"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (343): $5(), a0h(), a0I(), a1Q(), a2C(), a2I(), a2M(), a2S() (+335 more)

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
Cohesion: 0.06
Nodes (45): average(), Ca(), cd(), _checkEventBindings(), clear(), cn(), Da(), _destroy() (+37 more)

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
Nodes (396): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+388 more)

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
Cohesion: 0.07
Nodes (38): add(), ar(), Bi(), _cachedScopes(), chartOptionScopes(), constructor(), describe(), Ec() (+30 more)

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
Nodes (240): $3$crossAxisPosition$mainAxisPosition(), a(), a0G(), a0J(), a0o(), a12(), a14(), a15() (+232 more)

### Community 157 - "bw"
Cohesion: 0.10
Nodes (28): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+20 more)

### Community 158 - "b6"
Cohesion: 0.02
Nodes (164): $1$1(), a13(), a1f(), a1h(), a1J(), a2j(), a47(), a49() (+156 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "add"
Cohesion: 0.09
Nodes (31): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), buildOrUpdateScales(), _checkEventBindings() (+23 more)

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
Cohesion: 0.08
Nodes (51): A(), addCall(), bp(), bu(), ca(), Cc(), children(), Di() (+43 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.06
Nodes (51): aa(), Ah(), applyStack(), aspectRatio(), _calculateBarIndexPixels(), _calculateBarValuePixels(), countVisibleElements(), determineDataLimits() (+43 more)

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
Cohesion: 0.10
Nodes (25): aa(), Ac(), call(), cancelUpload(), ec(), Gi(), handleS3PreSignedUrl(), handleSignedUrl() (+17 more)

### Community 179 - "flutter.js"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 180 - "add"
Cohesion: 0.14
Nodes (17): addCleanup(), applyUpdates(), Cl(), constructor(), Ee(), extractTypeModifiersAndValue(), ip(), jt() (+9 more)

### Community 181 - "a"
Cohesion: 0.25
Nodes (8): a(), at(), d(), f(), H(), ji(), L(), pt()

### Community 182 - "ho"
Cohesion: 0.05
Nodes (17): BookingsExport, ListTours, AccommodationController, BookingCalculateController, DiscountController, PromotionController, TourController, BookingExportController (+9 more)

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
Nodes (183): a11(), a1v(), a5(), a6G(), a_2(), ag1(), aK(), an() (+175 more)

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
Cohesion: 0.07
Nodes (7): ViewBooking, ServiceCancellationResource, DatePicker, normalize_operator_name(), operator_is_ferry(), storage_asset_path(), Illuminate\Support\HtmlString

### Community 222 - "GeneratedPluginRegistrant.swift"
Cohesion: 0.14
Nodes (13): file_selector_macos, firebase_core, firebase_messaging, flutter_app_badger, RegisterGeneratedPlugins(), flutter_local_notifications, FlutterPluginRegistry, Foundation (+5 more)

### Community 223 - "wWinMain"
Cohesion: 0.24
Nodes (9): wWinMain(), string, wchar_t, CreateAndAttachConsole(), GetCommandLineArguments(), Utf8FromUtf16(), _In_, _In_opt_ (+1 more)

### Community 231 - "app.js"
Cohesion: 0.26
Nodes (7): C(), D(), J(), O(), U(), v(), X()

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.07
Nodes (37): ArrowLeft(), ArrowRight(), backspace(), d(), delete(), deleteByComposition(), deleteByCut(), deleteCompositionText() (+29 more)

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

- **Why does `$` connect `x` to `.mount`, `d4`, `rich-editor.js`, `draw`, `livewire.min.js`, `aM_`, `select.js`, `Schedule`, `deleteInDirection`, `j_`, `te`, `getSelectedRange`, `dH`, `bY`, `support.js`, `sendRequest`, `E`, `push`, `G`, `draw`, `render`, `add`, `aW_`, `navigate_default`, `bJ`?**
  _High betweenness centrality (0.305) - this node is a cross-community bridge._
- **Why does `Q` connect `Q` to `H`, `get`, `setAttribute`, `User`, `j_`, `navigate_default`, `echo.js`, `getSelectedRange`, `Booking`, `push`, `b`, `aW_`, `select.js`, `locationFromPosition`, `draw`, `bJ`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Why does `xc()` connect `livewire.min.js` to `x`, `du`, `bi`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Are the 224 inferred relationships involving `a()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 224 inferred relationships involving `a()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._