# Graph Report - AmigaTravel  (2026-08-19)

## Corpus Check
- 666 files · ~2,386,234 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17406 nodes · 52721 edges · 566 communities (524 shown, 42 thin omitted)
- Extraction: 85% EXTRACTED · 15% INFERRED · 0% AMBIGUOUS · INFERRED: 8140 edges (avg confidence: 0.56)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `71eaaa68`
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
- add
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
- DatePicker
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
- xc
- dispatchEvent
- setup
- ut
- Be
- oe
- pe
- le
- td
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

## Communities (566 total, 42 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (485): $3$crossAxisPosition$mainAxisPosition(), a09(), a0a(), a0b(), a0O(), a10(), a12(), a1i() (+477 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (543): $2$priority$scheduler(), $4(), a0(), a0Z(), a1V(), a2(), a2A(), a2D() (+535 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.07
Nodes (53): af(), bl(), ["@blur"](), Bn(), c(), ["@change"](), co(), Dl() (+45 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.03
Nodes (22): BookingLookup, BelongsTo, Builder, HasMany, PromotionalTicket, BelongsTo, BelongsToMany, Builder (+14 more)

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.06
Nodes (40): a0d(), a0q(), a16(), a1j(), a48(), a6G(), a7F(), a_S() (+32 more)

### Community 7 - ".getActivePromoTicket"
Cohesion: 0.02
Nodes (131): a0i(), a1R(), a2R(), a2s(), a44(), a59(), a5b(), a5l() (+123 more)

### Community 8 - "booking-form.blade.php"
Cohesion: 0.40
Nodes (4): changeSelection, confirmOperatorSelection, date-picker, setTripType(

### Community 9 - "HomePageTest"
Cohesion: 0.03
Nodes (32): BookingController, Request, NotifyAffectedBookerJob, SendBookingConfirmationJob, BookingCancellation, self, BookingConfirmation, BookingCreated (+24 more)

### Community 10 - "download.blade.php"
Cohesion: 0.02
Nodes (114): a0i(), a0O(), a10(), a1G(), a2d(), a46(), a4t(), a7m() (+106 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.05
Nodes (55): a14(), a1t(), a1z(), a4R(), a8Y(), a_f(), a_G(), aCs() (+47 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (558): bool get, dart:async, dart:io, DateTime?, double?, double get, 30, _accommodations (+550 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (112): acquireContext(), addControllers(), addPlugins(), addScales(), afterDraw(), beforeDatasetDraw(), beforeDatasetsDraw(), bh() (+104 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (67): AccommodationResource, Form, Table, AirlineBaggageRuleResource, Form, Table, ApkUserResource, Builder (+59 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (124): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), box(), canRedo() (+116 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.04
Nodes (135): _a(), Aa(), Ac(), af(), ai(), An(), ao(), ar() (+127 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (99): aa(), active(), an(), _animateOptions(), Ao(), applyStack(), aspectRatio(), beforeDatasetDraw() (+91 more)

### Community 20 - "Booking"
Cohesion: 0.18
Nodes (21): clamp(), computeCoordsFromPlacement(), convertValueToCoords(), detectOverflow(), evaluate2(), expandPaddingObject(), fn(), getAlignment() (+13 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (102): addAssetsToHeadTagOfPage(), addCall(), addDebounceOrThrottle(), addResolver(), _arrayLikeToArray(), _arrayWithoutHoles(), attributeShouldntBePreservedIfFalsy(), bind() (+94 more)

### Community 22 - "User.php"
Cohesion: 0.03
Nodes (34): BookingStatusChart, DashboardStatsOverview, RecentActivityWidget, RevenueChartWidget, TopRoutesWidget, Closure, Request, Response (+26 more)

### Community 23 - "draw"
Cohesion: 0.04
Nodes (104): ad(), adjustHitBoxes(), ae(), af(), calculateLabelRotation(), _calculatePadding(), _computeGridLineItems(), _computeLabelArea() (+96 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (386): a06(), a0k(), a0S(), a0t(), a0U(), a0W(), a1B(), a1C() (+378 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.03
Nodes (71): addResolver(), ae(), ai(), appendChild(), au(), bo(), bt(), cf() (+63 more)

### Community 26 - "k"
Cohesion: 0.04
Nodes (70): a0r(), A1(), a3a(), a7V(), a9i(), acA(), aD(), aDu() (+62 more)

### Community 27 - "select.js"
Cohesion: 0.08
Nodes (62): [g](), [x](), $c(), D(), E(), g(), H(), _i() (+54 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.04
Nodes (90): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), appendText(), applyBlockAttributeAtRange(), breakFormattedBlock(), charAt(), compositionControllerDidRequestDeselectingAttachment() (+82 more)

### Community 29 - "_update"
Cohesion: 0.05
Nodes (69): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+61 more)

### Community 30 - "fromObject"
Cohesion: 0.07
Nodes (43): a0x(), a3T(), a4k(), a54(), a7H(), aaF(), aAv(), aBA() (+35 more)

### Community 31 - "constructor"
Cohesion: 0.04
Nodes (71): Ac(), Bl(), Ce(), cf(), clone(), create(), Dl(), dtFormatter() (+63 more)

### Community 32 - "d"
Cohesion: 0.03
Nodes (76): a0n(), a22(), a2P(), a3G(), a3h(), a3W(), a5X(), aak() (+68 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (303): $0(), $1$allowPlatformDefault(), $2$alignmentPolicy(), $2$params(), a(), a07(), a0p(), a18() (+295 more)

### Community 34 - "H"
Cohesion: 0.01
Nodes (211): $1$1(), $3(), $5(), a13(), a2d(), A3(), a32(), a35() (+203 more)

### Community 35 - "TransportClass"
Cohesion: 0.02
Nodes (164): a0m(), a21(), a2f(), a2t(), a36(), a4a(), a4T(), a4u() (+156 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.06
Nodes (41): a0f(), a5i(), a9V(), aAs(), aCd(), aFn(), agc(), agx() (+33 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (38): addAssetsToHeadTagOfPage(), [attribute](), callAndClearComponentDebounces(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), closestComponent(), componentIsMissingProperty(), disableForm() (+30 more)

### Community 38 - "add"
Cohesion: 0.05
Nodes (77): target(), add(), addScopeToNode(), bufferPoolingForFiveMs(), call(), checkIdentityKeys(), clear(), closestComponent() (+69 more)

### Community 39 - "User"
Cohesion: 0.14
Nodes (18): a(), a(), a(), a(), a(), At(), dataset(), Fa() (+10 more)

### Community 40 - "a3"
Cohesion: 0.02
Nodes (110): a0i(), a0O(), a10(), a1G(), a46(), a4t(), a7m(), a7s() (+102 more)

### Community 41 - "x"
Cohesion: 0.10
Nodes (79): Sg(), al(), at(), B(), Be(), br(), Bt(), ca() (+71 more)

### Community 42 - "j_"
Cohesion: 0.01
Nodes (289): $2(), $3(), $5(), a00(), a0E(), a0f(), a0g(), a0h() (+281 more)

### Community 43 - "gv"
Cohesion: 0.09
Nodes (26): [attribute](), callAndClearComponentDebounces(), cloneIfObject(), cloneIfObject2(), commitTransaction(), each(), effect(), elementBoundEffect() (+18 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (15): Bi(), bn(), Id(), ji(), kd(), on(), qi(), Ri() (+7 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.06
Nodes (50): arr(), addInitSelector(), applyBindingsObject(), attributesOnly(), base64toBlob(), bind2(), byPriority(), cleanupModal() (+42 more)

### Community 46 - "_update"
Cohesion: 0.08
Nodes (44): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+36 more)

### Community 47 - "ListRecords"
Cohesion: 0.04
Nodes (20): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+12 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.06
Nodes (57): $a(), ab(), Ad(), b(), bb(), bc(), c(), cb() (+49 more)

### Community 49 - "getContext"
Cohesion: 0.04
Nodes (64): addEventListener(), Ah(), Au(), average(), ba(), beforeDraw(), bindResponsiveEvents(), bu() (+56 more)

### Community 50 - "file-upload.js"
Cohesion: 0.06
Nodes (53): ba(), bi(), c(), ca(), clickPercent(), constructor(), de(), define() (+45 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (59): attachmentManagerDidRequestRemovalOfAttachment(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), decreaseBlockAttributeLevel(), decreaseListLevel() (+51 more)

### Community 52 - "AC"
Cohesion: 0.06
Nodes (47): _a(), add(), al(), average(), ba(), _cachedScopes(), createResolver(), datasetElementScopeKeys() (+39 more)

### Community 53 - "push"
Cohesion: 0.10
Nodes (38): adjustHitBoxes(), afterDraw(), bc(), Bl(), clear(), _computeLabelArea(), _computeTitleHeight(), _createItems() (+30 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.08
Nodes (14): Ad(), bc(), fe(), Kc(), mc(), oc(), P(), Qc() (+6 more)

### Community 55 - "Voucher"
Cohesion: 0.05
Nodes (54): addElements(), as(), At(), Bs(), buildOrUpdateControllers(), buildOrUpdateElements(), cc(), cd() (+46 more)

### Community 56 - "qt"
Cohesion: 0.08
Nodes (15): AccommodationController, BookingCalculateController, DiscountController, PromotionController, JsonResponse, Request, ReferralController, Request (+7 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.08
Nodes (32): A(), Ad(), b(), Ba(), c(), d(), dd(), E() (+24 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (405): $1(), a0B(), a0D(), a0l(), a11(), a1m(), a1x(), a24() (+397 more)

### Community 59 - "aQ"
Cohesion: 0.03
Nodes (35): CreateAccommodation, CreateAirlineBaggageRule, ViewApkUser, CreateAppNotification, CreateBooking, CreateDiscount, CreateGraciaEarningRule, CreateHotel (+27 more)

### Community 60 - "buildTicks"
Cohesion: 0.04
Nodes (76): a00(), a07(), a1l(), a2f(), a2g(), a2h(), a2i(), a5e() (+68 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.03
Nodes (45): GraciaPointsController, Request, NotificationController, JsonResponse, Request, AirlineBaggageRule, AppNotification, DeletedVirtualNotification (+37 more)

### Community 62 - "support.js"
Cohesion: 0.05
Nodes (48): ai(), apply(), ar(), B(), co(), Cr(), es(), Et() (+40 more)

### Community 63 - "gO"
Cohesion: 0.04
Nodes (99): $2$from$to(), $3$crossAxisPosition$mainAxisPosition(), a3F(), a3n(), a5d(), a75(), a7i(), a81() (+91 more)

### Community 64 - "RelationManager"
Cohesion: 0.03
Nodes (101): a05(), a0x(), a39(), a3a(), a3b(), a5B(), a5Y(), a7y() (+93 more)

### Community 65 - "I"
Cohesion: 0.08
Nodes (32): afterDatasetsUpdate(), _d(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth(), getStyle(), _handleEvent() (+24 more)

### Community 66 - "i"
Cohesion: 0.06
Nodes (52): aa(), Al(), ar(), bf(), buildTicks(), Ca(), count(), determineDataLimits() (+44 more)

### Community 67 - "get"
Cohesion: 0.04
Nodes (75): $2$from$to(), a0S(), a2l(), a2q(), a38(), a39(), a4X(), a5S() (+67 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (59): ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState, ContactScreen, _ContactScreenState (+51 more)

### Community 69 - "setAttribute"
Cohesion: 0.09
Nodes (54): Ae(), as(), fd(), fi(), ga(), go(), lf(), ls() (+46 more)

### Community 70 - "a"
Cohesion: 0.10
Nodes (28): cancelUpload(), componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal() (+20 more)

### Community 71 - "a5"
Cohesion: 0.08
Nodes (40): buildOrUpdateScales(), cl(), _computeLabelSizes(), Ct(), D(), E(), ensureScalesHaveIDs(), Eo() (+32 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (23): actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo(), duration() (+15 more)

### Community 73 - "s"
Cohesion: 0.14
Nodes (18): ap(), bd(), Bi(), Br(), gf(), Ls(), nf(), of() (+10 more)

### Community 74 - "EditRecord"
Cohesion: 0.05
Nodes (19): EditAccommodation, EditAirlineBaggageRule, EditAppNotification, EditBooking, EditDiscount, EditGraciaEarningRule, EditHotel, OperatorResource (+11 more)

### Community 75 - "Controller"
Cohesion: 0.13
Nodes (27): as(), C(), Co(), cr(), endOf(), Et(), format(), formats() (+19 more)

### Community 76 - "updateElements"
Cohesion: 0.06
Nodes (45): ac(), Ai(), ca(), calculateCircumference(), _circumference(), datasetAnimationScopeKeys(), dr(), ec() (+37 more)

### Community 77 - "sendRequest"
Cohesion: 0.01
Nodes (204): $1$1(), a0k(), a19(), a1G(), a1m(), a1N(), a1w(), a1x() (+196 more)

### Community 78 - "push"
Cohesion: 0.03
Nodes (33): CreateUser, EditUser, AdminNotificationController, JsonResponse, AccommodationController, BookingCalculateController, BookingController, DiscountController (+25 more)

### Community 79 - "o8"
Cohesion: 0.12
Nodes (21): cancelUpload(), Di(), gt(), handleS3PreSignedUrl(), handleSignedUrl(), Hi(), ji(), makeRequest() (+13 more)

### Community 80 - "E"
Cohesion: 0.08
Nodes (36): canAcceptDataTransfer(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), compositionDidRequestChangingSelectionToLocationRange(), createDOMRangeFromLocationRange(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange() (+28 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (15): x(), ma(), c(), Ha(), Ka(), La(), ma(), Nc() (+7 more)

### Community 82 - "skwasm.js"
Cohesion: 0.05
Nodes (64): e(), a(), aa(), ab(), ac(), $b(), bb(), bc() (+56 more)

### Community 83 - "$1"
Cohesion: 0.04
Nodes (101): $3$color$endFraction$startFraction(), a05(), a23(), a2G(), a2o(), a2Z(), a3k(), a3l() (+93 more)

### Community 84 - "push"
Cohesion: 0.07
Nodes (41): addHTMLAttribute(), canBeConsolidatedWith(), canBeGroupedWith(), canDecreaseBlockAttributeLevel(), compositionControllerDidRender(), copyUsingObjectMap(), copyUsingObjectsFromDocument(), copyWithBaseBlockAttributes() (+33 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (43): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), getBoundingClientRect(), getClientRectFromClippingAncestor(), getClientRects(), getClippingElementAncestors(), getClippingRect() (+35 more)

### Community 86 - "ManageProofs"
Cohesion: 0.04
Nodes (54): a98(), a_o(), abZ(), aCu(), aDm(), aDX(), aFI(), aGq() (+46 more)

### Community 87 - "Dt"
Cohesion: 0.08
Nodes (11): ListTours, Builder, Form, Table, TourResource, Request, TourController, TourController (+3 more)

### Community 88 - "preload"
Cohesion: 0.07
Nodes (51): disabled(), acquireContext(), buildTicks(), calculateLabelRotation(), _computeAngle(), _computeGridLineItems(), _computeLabelItems(), computeTickLimit() (+43 more)

### Community 89 - "HasFactory"
Cohesion: 0.21
Nodes (12): average(), getCenterPoint(), getProps(), hasValue(), hs(), inXRange(), inYRange(), nearest() (+4 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+5 more)

### Community 91 - "b5"
Cohesion: 0.08
Nodes (39): afterAutoSkip(), ar(), Bi(), buildLookupTable(), _calculateBarIndexPixels(), _calculatePadding(), countVisibleElements(), determineDataLimits() (+31 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (185): a03(), a17(), a1A(), a1s(), a6(), a6I(), aC(), acN() (+177 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (415): $1(), a1k(), a2B(), a33(), a34(), a3m(), a3r(), a3Y() (+407 more)

### Community 94 - "draw"
Cohesion: 0.02
Nodes (144): a14(), a1c(), a1D(), a1f(), a1T(), a2M(), a2N(), a42() (+136 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.03
Nodes (96): _a(), abutsStart(), after(), afterAutoSkip(), Ag(), Ai(), before(), buildLookupTable() (+88 more)

### Community 97 - "$0"
Cohesion: 0.09
Nodes (31): e(), i(), l(), Ni(), o(), t(), u(), be() (+23 more)

### Community 98 - "jU"
Cohesion: 0.04
Nodes (102): a0l(), a0u(), a0v(), a0w(), a0y(), a3s(), a4O(), a5j() (+94 more)

### Community 99 - "M"
Cohesion: 0.06
Nodes (31): A(), b(), be(), c(), e(), f(), fc(), g() (+23 more)

### Community 100 - "get"
Cohesion: 0.05
Nodes (31): ld(), A(), b(), be(), c(), e(), ee(), f() (+23 more)

### Community 101 - "createMorphContext"
Cohesion: 0.09
Nodes (35): appendChild(), cloneNode(), cloneScriptTag(), createElement(), createMorphContext(), extractUriAndQueryString(), getFirstNode(), getNextSibling() (+27 more)

### Community 102 - "navigate_default"
Cohesion: 0.07
Nodes (42): autofocusElementsWithTheAutofocusAttribute(), bindClasses(), cleanupAttributes(), createUrlObjectFromString(), destroyTree(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml() (+34 more)

### Community 103 - "aG"
Cohesion: 0.05
Nodes (58): $2$isClosing(), a01(), a02(), a2j(), ag1(), ag8(), aHC(), ahe() (+50 more)

### Community 104 - "render"
Cohesion: 0.05
Nodes (48): xt(), cacheViewForObject(), canSyncDocumentView(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement() (+40 more)

### Community 107 - "add"
Cohesion: 0.12
Nodes (20): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidEditAttachment() (+12 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.06
Nodes (47): Bd(), Cd(), Fd(), Gd(), Hd(), Id(), Kd(), Ld() (+39 more)

### Community 109 - "add"
Cohesion: 0.07
Nodes (46): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), createCaptionElement() (+38 more)

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
Cohesion: 0.13
Nodes (22): ca(), Cc(), each(), get(), gp(), has(), hn(), hp() (+14 more)

### Community 114 - "fn"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ja(), Ka(), La(), n(), Pc(), q() (+5 more)

### Community 115 - "Ve"
Cohesion: 0.11
Nodes (38): ad(), as(), Ce(), cs(), Ct(), d(), ed(), ei() (+30 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.06
Nodes (34): a30(), a3L(), a70(), a71(), a7j(), a7u(), a_1(), aie() (+26 more)

### Community 117 - "Ra"
Cohesion: 0.08
Nodes (44): A(), addCall(), al(), ba(), bp(), bu(), children(), ci() (+36 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (14): Ba(), c(), Ha(), Ka(), La(), ma(), Nc(), p() (+6 more)

### Community 119 - "b"
Cohesion: 0.15
Nodes (17): addCleanup(), applyUpdates(), constructor(), deepClone(), diff(), extractData(), generateWireObject(), initComponent() (+9 more)

### Community 120 - "a1"
Cohesion: 0.03
Nodes (92): $0(), $2$params(), a19(), a1Q(), a2_(), a2t(), a2Z(), a33() (+84 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.17
Nodes (32): _a(), aa(), ba(), Be(), br(), Ca(), ce(), Dn() (+24 more)

### Community 122 - "aW_"
Cohesion: 0.02
Nodes (230): a0Q(), a0Z(), a1I(), a3O(), a4C(), a4h(), a4m(), a4Q() (+222 more)

### Community 123 - "navigate_default"
Cohesion: 0.06
Nodes (28): $, ack(), bdW(), bgH(), bi8(), bhE(), bkA(), bm2() (+20 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "dO"
Cohesion: 0.18
Nodes (29): Bi(), d(), Di(), f(), Ge(), h(), I(), ir() (+21 more)

### Community 126 - "gN"
Cohesion: 0.07
Nodes (33): ArrowLeft(), ArrowRight(), didClickAttachment(), dragstart(), editAttachment(), expandSelectionInDirection(), findAttachmentForElement(), findNodeAndOffsetFromLocation() (+25 more)

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (269): $2$alignmentPolicy(), a04(), a08(), a15(), a1f(), a20(), a25(), a2r() (+261 more)

### Community 128 - "start"
Cohesion: 0.08
Nodes (28): attachmentForFile(), attributesForFile(), canSetCurrentTextAttribute(), compositionShouldAcceptFile(), didChangeAttributes(), getContentType(), getCurrentTextAttributes(), getHeight() (+20 more)

### Community 129 - "What You Must Do When Invoked"
Cohesion: 0.07
Nodes (26): For /graphify add and --watch, For /graphify query, For the commit hook and native CLAUDE.md integration, For --update and --cluster-only, /graphify, Honesty Rules, Interpreter guard for subcommands, Part A - Structural extraction for code files (+18 more)

### Community 130 - "C"
Cohesion: 0.07
Nodes (11): CancelExpiredPayments, CleanupOldSchedules, DeleteAllUsers, NotifyExpiringVouchers, PurgeExpiredSchedules, RetroactiveGraciaPoints, RetrofitReferrals, SendPaymentReminders (+3 more)

### Community 131 - "gt"
Cohesion: 0.08
Nodes (32): alpha(), an(), color(), darken(), Dc(), desaturate(), eo(), fo() (+24 more)

### Community 132 - "railway-start.sh"
Cohesion: 0.07
Nodes (26): APP_DEBUG, APP_ENV, APP_NAME, APP_URL, CACHE_STORE, DB_CONNECTION, DB_DATABASE, DB_HOST (+18 more)

### Community 133 - "Vehicle"
Cohesion: 0.20
Nodes (24): add(), adjustScroll(), animate(), autoAnimate(), cleanUp(), deletePosition(), forEach(), getCoords() (+16 more)

### Community 134 - "St"
Cohesion: 0.07
Nodes (43): second(), base64toBlob(), cleanupModal(), contentIsFromDump(), extractDurationFrom(), extractStreamObjects(), find(), fromQueryString() (+35 more)

### Community 135 - "d4"
Cohesion: 0.09
Nodes (29): So(), alpha(), be(), en(), fe(), Fs(), greyscale(), Hi() (+21 more)

### Community 136 - "call"
Cohesion: 0.09
Nodes (31): cleanup(), cloneIfObject(), containsTargets(), customDirectiveHasBeenRegistered(), destroyComponent(), directive(), dirtyTargets(), extractScriptTagContent() (+23 more)

### Community 137 - "d4"
Cohesion: 0.02
Nodes (411): $2(), $4(), a0(), a0e(), a0j(), a1A(), a1H(), a1J() (+403 more)

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
Nodes (46): add(), Bi(), _cachedScopes(), chartOptionScopes(), constructor(), createResolver(), datasetAnimationScopeKeys(), datasetElementScopeKeys() (+38 more)

### Community 142 - ".$1"
Cohesion: 0.04
Nodes (70): addRootSelector(), allSelectors(), cleanup(), clone(), cloneTree(), closestIdRoot(), closestRoot(), data() (+62 more)

### Community 143 - "echo.js"
Cohesion: 0.06
Nodes (50): a(), ar(), at(), b(), Be(), Ce(), cr(), d() (+42 more)

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
Nodes (48): dart:convert, build, _confirmPassController, createState, _emailController, ForgotPasswordScreen, _ForgotPasswordScreenState, _isLoading (+40 more)

### Community 152 - "kr"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 154 - "dB"
Cohesion: 0.08
Nodes (28): a02(), a0A(), a2q(), a7G(), a9A(), aCe(), af0(), aj2() (+20 more)

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (375): $2$isClosing(), a(), a01(), a03(), a0c(), a0h(), a0N(), a0V() (+367 more)

### Community 156 - "dD"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 157 - "bw"
Cohesion: 0.08
Nodes (32): search(), url(), autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr() (+24 more)

### Community 158 - "b6"
Cohesion: 0.02
Nodes (133): $2$priority$scheduler(), a0g(), a1s(), a27(), a29(), a36(), a3I(), a3K() (+125 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "add"
Cohesion: 0.09
Nodes (28): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), cs() (+20 more)

### Community 161 - "target"
Cohesion: 0.17
Nodes (21): call(), cancelUpload(), getUploadManager(), handleFileUpload(), markUploadErrored(), markUploadFinished(), "node_modules/@alpinejs/collapse/dist/module.cjs.js"(), "node_modules/@alpinejs/focus/dist/module.cjs.js"() (+13 more)

### Community 162 - "tT"
Cohesion: 0.16
Nodes (25): Qt(), da(), En(), fa(), Fi(), fn(), Ii(), je() (+17 more)

### Community 163 - "bn"
Cohesion: 0.11
Nodes (27): closestDataStack(), extractScriptTagContent(), generateEvaluatorFromFunction(), generateEvaluatorFromString(), generateFunctionFromString(), getIterationScopeVariables(), getLengthValue(), getRootMargin() (+19 more)

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
Cohesion: 0.19
Nodes (22): Ae(), at(), Cn(), de(), dt(), fr(), Gt(), ht() (+14 more)

### Community 168 - "getDatasetMeta"
Cohesion: 0.09
Nodes (33): afterDatasetsUpdate(), buildOrUpdateControllers(), _destroyDatasetMeta(), generateLabels(), getController(), getDatasetMeta(), getDataVisibility(), _getLegendItemAt() (+25 more)

### Community 169 - "du"
Cohesion: 0.09
Nodes (47): $e(), getModifierTail(), aa(), Ac(), At(), Be(), call(), cu() (+39 more)

### Community 170 - "RunnerTests.swift"
Cohesion: 0.15
Nodes (10): Cocoa, Flutter, RunnerTests, MainFlutterWindow, RunnerTests, FlutterMacOS, NSWindow, UIKit (+2 more)

### Community 171 - "require"
Cohesion: 0.09
Nodes (23): require, anhskohbo/no-captcha, barryvdh/laravel-dompdf, dompdf/dompdf, filament/filament, filament/support, intervention/image, kreait/laravel-firebase (+15 more)

### Community 172 - "bZ"
Cohesion: 0.05
Nodes (71): applyStack(), aspectRatio(), C(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference(), _circumference(), co() (+63 more)

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
Cohesion: 0.12
Nodes (20): addCleanup(), applyUpdates(), bs(), constructor(), dp(), ds(), Fr(), ft() (+12 more)

### Community 177 - ".processRow"
Cohesion: 0.50
Nodes (4): R(), td(), ud(), vd()

### Community 178 - "makeRequest"
Cohesion: 0.18
Nodes (13): handleS3PreSignedUrl(), handleSignedUrl(), ji(), makeRequest(), markUploadErrored(), markUploadFinished(), prepare(), qt() (+5 more)

### Community 179 - "flutter.js"
Cohesion: 0.23
Nodes (9): b(), _createScriptTag(), _getNewServiceWorker(), load(), loadEntrypoint(), _loadJSEntrypoint(), loadServiceWorker(), _loadWasmEntrypoint() (+1 more)

### Community 180 - "add"
Cohesion: 0.13
Nodes (21): add(), Bf(), Cl(), corraleCommitsIntoPools(), createAndSendNewPool(), delete(), df(), Ee() (+13 more)

### Community 181 - "AdminPanelProvider.php"
Cohesion: 0.02
Nodes (39): Action, PurgeExpiredProofs, AdminNotifications, ManagePaymentSettings, Form, ManageProofs, Collection, Form (+31 more)

### Community 182 - "ho"
Cohesion: 0.22
Nodes (5): BookingsExport, BookingExportController, Illuminate\Http\Response, Maatwebsite\Excel\Concerns\Exportable, Maatwebsite\Excel\Concerns\WithMultipleSheets

### Community 183 - "constructor"
Cohesion: 0.09
Nodes (27): addCleanup(), applyUpdates(), children(), constructor(), dataSet(), deepClone(), diff(), ensureLivewireScriptIsntMisplaced() (+19 more)

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
Cohesion: 0.13
Nodes (15): scripts, dev, post-autoload-dump, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout, Illuminate\\Foundation\\ComposerScripts::postAutoloadDump (+7 more)

### Community 214 - "manifest.json"
Cohesion: 0.13
Nodes (14): background_color, categories, description, display, icons, lang, name, orientation (+6 more)

### Community 216 - "DatePicker"
Cohesion: 0.12
Nodes (4): Form, Table, ServiceCancellationResource, DatePicker

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
Cohesion: 0.26
Nodes (7): C(), D(), J(), O(), U(), v(), X()

### Community 234 - "GraciaPointLedgersRelationManager.php"
Cohesion: 0.47
Nodes (3): GraciaPointLedgersRelationManager, Form, Table

### Community 235 - "AccommodationsRelationManager.php"
Cohesion: 0.47
Nodes (3): AccommodationsRelationManager, Form, Table

### Community 236 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 239 - "aYd"
Cohesion: 0.04
Nodes (76): attachFiles(), backspace(), beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), copy() (+68 more)

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
Cohesion: 0.18
Nodes (20): appendAttachmentWithAttributes(), appendBlockForAttributesWithElement(), appendBlockForElement(), appendBlockForTextNode(), appendEmptyBlock(), appendPiece(), appendStringWithAttributes(), find() (+12 more)

### Community 245 - "mutationIsSignificant"
Cohesion: 0.18
Nodes (14): a5V(), aar(), aFO(), b3t(), baK(), bs0(), bs1(), C3() (+6 more)

### Community 246 - "manifest.json"
Cohesion: 0.18
Nodes (10): background_color, description, display, icons, name, orientation, prefer_related_applications, short_name (+2 more)

### Community 247 - "mergeNewHead"
Cohesion: 0.22
Nodes (13): cloneScriptTag(), extractUriAndQueryString(), ifTheQueryStringChangedSinceLastRequest(), ignoreAttributes(), injectScriptTagAndWaitForItToFullyLoad(), isAsset(), isScript(), isTracked() (+5 more)

### Community 248 - "EnsureStaffPermission.php"
Cohesion: 0.60
Nodes (3): EnsureStaffPermission, Closure, Request

### Community 249 - "_each"
Cohesion: 0.06
Nodes (37): Yn(), Rs(), U(), addControllers(), addElements(), addPlugins(), addScales(), buildOrUpdateElements() (+29 more)

### Community 250 - "booking-reschedule.blade.php"
Cohesion: 0.20
Nodes (9): closeRefundForm, openRefundForm, selectDepartureAccommodation(, selectDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectReturnAccommodation(, selectReturnSchedule({{ $sch->id }}, {{ $booking->getMode() === , setStep(, submitCancelAndRefund (+1 more)

### Community 251 - "dispatchEvent"
Cohesion: 0.22
Nodes (11): componentsByName(), dispatch(), dispatch2(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf(), dispatchTo() (+3 more)

### Community 254 - "fb"
Cohesion: 0.27
Nodes (6): fa(), fb(), get(), ve(), wc(), we()

### Community 255 - "fb"
Cohesion: 0.27
Nodes (6): fa(), fb(), get(), pe(), qe(), wc()

### Community 256 - "Rb"
Cohesion: 0.29
Nodes (3): Lb(), Rb(), zb()

### Community 257 - "oc"
Cohesion: 0.20
Nodes (14): active(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors(), _descriptors(), kh(), _notify() (+6 more)

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

### Community 262 - "xc"
Cohesion: 0.14
Nodes (14): bc(), dc(), eo(), fe(), gc(), hc(), ma(), pc() (+6 more)

### Community 263 - "dispatchEvent"
Cohesion: 0.20
Nodes (12): componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf() (+4 more)

### Community 264 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install --ignore-scripts, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 265 - "ut"
Cohesion: 0.47
Nodes (6): ut(), hs(), Nn(), ps(), Ro(), Se()

### Community 270 - "td"
Cohesion: 0.50
Nodes (4): R(), td(), ud(), vd()

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
- **955 isolated node(s):** `$schema`, `name`, `type`, `description`, `laravel` (+950 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **42 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `navigate_default` to `start`, `.mount`, `xc`, `d4`, `draw`, `b`, `k`, `aM_`, `select.js`, `_update`, `d`, `Schedule`, `tT`, `x`, `te`, `getSelectedRange`, `dH`, `buildTicks`, `E`, `G`, `.$2`, `render`, `add`, `dO`, `bJ`?**
  _High betweenness centrality (0.041) - this node is a cross-community bridge._
- **Why does `a2()` connect `.mount` to `.saveDraft`, `.getActivePromoTicket`, `d4`, `b`, `b6`, `d`, `Schedule`, `TransportClass`, `j_`, `dH`, `bY`, `buildTicks`, `gO`, `get`, `$1`, `.$2`, `jU`, `aG`, `a1`, `bJ`?**
  _High betweenness centrality (0.032) - this node is a cross-community bridge._
- **Why does `a3()` connect `.mount` to `.saveDraft`, `.getActivePromoTicket`, `d4`, `k`, `d`, `Schedule`, `TransportClass`, `j_`, `dH`, `buildTicks`, `gO`, `get`, `sendRequest`, `ManageProofs`, `G`, `.$2`, `draw`, `jU`, `BookingReschedule`, `a1`, `bJ`?**
  _High betweenness centrality (0.025) - this node is a cross-community bridge._
- **Are the 246 inferred relationships involving `a()` (e.g. with `loadEntrypoint()` and `_loadJSEntrypoint()`) actually correct?**
  _`a()` has 246 INFERRED edges - model-reasoned connections that need verification._
- **Are the 235 inferred relationships involving `a()` (e.g. with `$0()` and `b()`) actually correct?**
  _`a()` has 235 INFERRED edges - model-reasoned connections that need verification._
- **Are the 498 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 498 INFERRED edges - model-reasoned connections that need verification._
- **Are the 496 inferred relationships involving `c()` (e.g. with `$0()` and `$1()`) actually correct?**
  _`c()` has 496 INFERRED edges - model-reasoned connections that need verification._