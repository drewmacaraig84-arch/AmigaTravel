# Graph Report - AmigaTravel  (2026-08-20)

## Corpus Check
- 670 files · ~2,394,283 words
- Verdict: corpus is large enough that graph structure adds value.

## Summary
- 17518 nodes · 54291 edges · 543 communities (505 shown, 38 thin omitted)
- Extraction: 84% EXTRACTED · 16% INFERRED · 0% AMBIGUOUS · INFERRED: 8595 edges (avg confidence: 0.54)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `dbae0ef3`
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
- dB
- aM_
- dD
- bw
- b6
- StatelessWidget
- add
- target
- bn
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
- Vl
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
- xc
- dispatchEvent
- setup
- ut
- Be
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

## Communities (543 total, 38 thin omitted)

### Community 0 - "BookingForm"
Cohesion: 0.04
Nodes (4): BookingForm, TourDate, Illuminate\Support\Facades\Validator, Validator

### Community 1 - ".saveDraft"
Cohesion: 0.00
Nodes (505): a03(), a0q(), a0V(), a1B(), a1x(), a2a(), a2D(), a2e() (+497 more)

### Community 2 - ".mount"
Cohesion: 0.02
Nodes (412): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+404 more)

### Community 3 - ".processBookingInternal"
Cohesion: 0.08
Nodes (45): getOptions(), ad(), bl(), ["@blur"](), c(), ["@change"](), Dl(), Dr() (+37 more)

### Community 4 - "manage-website-settings.blade.php"
Cohesion: 0.14
Nodes (13): addFaq, addQuickFact, addSocialLink, closePanel, removeFaq({{ $fi }}), removeHeroImage({{ (int)$idx }}), removeQuickFact({{ $fi }}), removeSocialLink({{ $li }}) (+5 more)

### Community 5 - ".updateAvailableScheduleDates"
Cohesion: 0.05
Nodes (5): BookingLookup, Schedule, DateTimeInterface, HasOneThrough, Illuminate\Database\Eloquent\Relations\HasMany

### Community 6 - ".updateBaggagePriceFromRates"
Cohesion: 0.03
Nodes (114): $3(), a1M(), a31(), a4v(), a5s(), a7O(), a9b(), a9C() (+106 more)

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
Cohesion: 0.03
Nodes (106): a09(), a0l(), a2X(), a3X(), a4b(), a90(), a9e(), a9I() (+98 more)

### Community 12 - "schedules.blade.php"
Cohesion: 0.02
Nodes (127): a1Z(), a26(), a2K(), a3c(), a3P(), a4U(), a94(), a9f() (+119 more)

### Community 14 - "main.dart"
Cohesion: 0.00
Nodes (558): bool get, Color, dart:async, dart:io, DateTime?, double?, double get, 30 (+550 more)

### Community 15 - "chart.js"
Cohesion: 0.01
Nodes (113): abutsStart(), acquireContext(), addControllers(), addPlugins(), addScales(), Au(), ba(), beforeDatasetDraw() (+105 more)

### Community 16 - "static"
Cohesion: 0.01
Nodes (26): AccommodationResource, AirlineBaggageRuleResource, ApkUserResource, AppNotificationResource, BookingResource, DiscountResource, FerryRouteResource, GraciaEarningRuleResource (+18 more)

### Community 17 - "rich-editor.js"
Cohesion: 0.02
Nodes (131): activateAttributeIfSupported(), appendStringToTextAtIndex(), applyBlockAttribute(), attachmentDidChangeUploadProgress(), attachmentIsManaged(), attributeChangedCallback(), canRedo(), canSyncDocumentView() (+123 more)

### Community 18 - "markdown-editor.js"
Cohesion: 0.03
Nodes (185): be(), _a(), Aa(), Ac(), ad(), Ae(), af(), ai() (+177 more)

### Community 19 - "chart.js"
Cohesion: 0.02
Nodes (110): Yn(), aa(), alpha(), an(), aspectRatio(), be(), Bn(), br() (+102 more)

### Community 20 - "Booking"
Cohesion: 0.04
Nodes (70): a0x(), a2H(), a3I(), a5W(), a5X(), a7F(), a7G(), a7K() (+62 more)

### Community 21 - "livewire.js"
Cohesion: 0.02
Nodes (95): input(), addAssetsToHeadTagOfPage(), _arrayLikeToArray(), _arrayWithoutHoles(), [attribute](), attributeShouldntBePreservedIfFalsy(), bind(), bindAttribute() (+87 more)

### Community 22 - "User.php"
Cohesion: 0.04
Nodes (29): CreatesApplication, dismissCancellationReminder, Illuminate\Foundation\Testing\RefreshDatabase, Illuminate\Foundation\Testing\TestCase, requestCancellation, selectRebookingDepartureAccommodation(, selectRebookingDepartureSchedule({{ $sch->id }}, {{ $booking->getMode() === , selectRebookingReturnAccommodation( (+21 more)

### Community 23 - "draw"
Cohesion: 0.04
Nodes (109): ad(), adjustHitBoxes(), ae(), af(), afterDraw(), calculateLabelRotation(), _calculatePadding(), _computeAngle() (+101 more)

### Community 24 - "b"
Cohesion: 0.00
Nodes (524): $3$crossAxisPosition$mainAxisPosition(), a03(), a0q(), a0V(), a18(), a1a(), a1B(), a2a() (+516 more)

### Community 25 - "livewire.min.js"
Cohesion: 0.03
Nodes (61): appendChild(), au(), bo(), bt(), cf(), corraleCommitsIntoPools(), cp(), createAndSendNewPool() (+53 more)

### Community 26 - "k"
Cohesion: 0.12
Nodes (23): _a(), al(), ba(), _cachedScopes(), configure(), createResolver(), datasetElementScopeKeys(), datasetScopeKeys() (+15 more)

### Community 27 - "select.js"
Cohesion: 0.07
Nodes (71): [g](), [x](), $c(), ca(), D(), E(), Ea(), g() (+63 more)

### Community 28 - "locationFromPosition"
Cohesion: 0.04
Nodes (115): addAttribute(), addAttributeAtRange(), addAttributesAtRange(), addHTMLAttribute(), appendText(), applyBlockAttributeAtRange(), canBeGroupedWith(), canDecreaseBlockAttributeLevel() (+107 more)

### Community 29 - "_update"
Cohesion: 0.04
Nodes (81): addBox(), afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate() (+73 more)

### Community 30 - "fromObject"
Cohesion: 0.11
Nodes (26): afQ(), agX(), aiq(), am2(), am5(), aqM(), avF(), biK() (+18 more)

### Community 31 - "constructor"
Cohesion: 0.04
Nodes (77): Bl(), cf(), chartOptionScopes(), clone(), constructor(), create(), describe(), dtFormatter() (+69 more)

### Community 32 - "d"
Cohesion: 0.04
Nodes (109): $3$color$endFraction$startFraction(), a05(), a08(), a1u(), a3S(), a4r(), a6l(), a72() (+101 more)

### Community 33 - "Schedule"
Cohesion: 0.01
Nodes (372): $2$alignmentPolicy(), $5(), a0h(), a0I(), a1Q(), a28(), a2C(), a2I() (+364 more)

### Community 34 - "H"
Cohesion: 0.03
Nodes (121): $3$color$endFraction$startFraction(), a05(), a08(), a1u(), a2U(), a3S(), a3y(), a4r() (+113 more)

### Community 35 - "TransportClass"
Cohesion: 0.01
Nodes (203): $1$1(), $2$isClosing(), a1f(), a1h(), a1J(), a2F(), a3O(), a3y() (+195 more)

### Community 36 - "deleteInDirection"
Cohesion: 0.02
Nodes (99): a0c(), a0f(), a17(), a1e(), a1l(), a1N(), a1o(), a1s() (+91 more)

### Community 37 - "livewire.esm.js"
Cohesion: 0.03
Nodes (48): addAssetsToHeadTagOfPage(), applyUpdates(), [attribute](), callAndClearComponentDebounces(), children(), cleanupAlpineElementsOnThePageThatArentInsideAPersistedElement(), cloneScriptTag2(), closestComponent() (+40 more)

### Community 38 - "add"
Cohesion: 0.07
Nodes (54): call(), checkIdentityKeys(), clear(), containsTargets(), createArrayInstrumentations(), createForEach(), createGetter(), createInstrumentations() (+46 more)

### Community 39 - "User"
Cohesion: 0.04
Nodes (58): a0c(), a0f(), a95(), a96(), a_q(), aAz(), aDV(), akB() (+50 more)

### Community 40 - "a3"
Cohesion: 0.05
Nodes (100): $1$allowPlatformDefault(), a0k(), a0S(), a0T(), a0y(), a13(), a4l(), a5D() (+92 more)

### Community 41 - "x"
Cohesion: 0.10
Nodes (67): Sg(), at(), B(), bf(), br(), Bt(), Cr(), Ct() (+59 more)

### Community 42 - "j_"
Cohesion: 0.02
Nodes (186): a00(), a09(), a0E(), a0W(), a16(), a1d(), a1g(), a1I() (+178 more)

### Community 43 - "gv"
Cohesion: 0.11
Nodes (19): clone(), cloneIfObject(), cloneIfObject2(), commitTransaction(), dontRegisterReactiveSideEffects(), effect(), entangle(), flushJobs() (+11 more)

### Community 44 - "te"
Cohesion: 0.04
Nodes (11): Bi(), bn(), Id(), ji(), qi(), Ri(), te(), Vi() (+3 more)

### Community 45 - ""node_modules/alpinejs/dist/module.cjs.js""
Cohesion: 0.06
Nodes (44): addCleanup(), applyUpdates(), cleanupModal(), constructor(), contentIsFromDump(), dataSet(), deepClone(), deferHandlingDirectives() (+36 more)

### Community 46 - "_update"
Cohesion: 0.09
Nodes (36): afterBuildTicks(), afterCalculateLabelRotation(), afterDataLimits(), afterFit(), afterSetDimensions(), afterTickToLabelConversion(), afterUpdate(), beforeBuildTicks() (+28 more)

### Community 47 - "ListRecords"
Cohesion: 0.04
Nodes (20): ListAccommodations, ListAirlineBaggageRules, ListApkUsers, ListAppNotifications, ListBookings, ListDiscounts, ListFerryRoutes, ListGraciaEarningRules (+12 more)

### Community 48 - "canvaskit.js"
Cohesion: 0.06
Nodes (31): A(), b(), c(), e(), f(), fc(), g(), gc() (+23 more)

### Community 49 - "getContext"
Cohesion: 0.12
Nodes (22): active(), _animateOptions(), average(), _createAnimations(), dataset(), getCenterPoint(), getProps(), hasValue() (+14 more)

### Community 50 - "file-upload.js"
Cohesion: 0.04
Nodes (80): e(), i(), l(), Ni(), o(), t(), u(), ba() (+72 more)

### Community 51 - "getSelectedRange"
Cohesion: 0.06
Nodes (56): attachmentManagerDidRequestRemovalOfAttachment(), breakFormattedBlock(), breaksOnReturn(), Ca(), canSetCurrentAttribute(), canSetCurrentBlockAttribute(), compositionControllerDidRequestRemovalOfAttachment(), compositionDidRequestChangingSelectionToLocationRange() (+48 more)

### Community 52 - "AC"
Cohesion: 0.07
Nodes (44): add(), addCall(), addResolver(), addScopeToNode(), bindClasses(), bufferPoolingForFiveMs(), colocateCommitsByComponent(), corraleCommitsIntoPools() (+36 more)

### Community 53 - "push"
Cohesion: 0.06
Nodes (56): acquireContext(), adjustHitBoxes(), Bl(), calculateLabelRotation(), clear(), _computeLabelArea(), _computeTitleHeight(), da() (+48 more)

### Community 54 - "canvaskit.js"
Cohesion: 0.06
Nodes (42): a2o(), a4p(), a56(), a7U(), a_1(), aAN(), aEU(), aF3() (+34 more)

### Community 55 - "Voucher"
Cohesion: 0.06
Nodes (48): alpha(), as(), At(), Bi(), Bs(), cc(), cd(), clear() (+40 more)

### Community 56 - "qt"
Cohesion: 0.04
Nodes (17): CreateBookingAction, Accommodation, Discount, ScheduleAccommodation, ScheduleTransportClass, TransportClass, Voucher, BookingObserver (+9 more)

### Community 57 - "canvaskit.js"
Cohesion: 0.05
Nodes (51): A(), Ad(), b(), Ba(), Bd(), c(), Cd(), d() (+43 more)

### Community 58 - "dH"
Cohesion: 0.01
Nodes (298): $1(), a1w(), a2y(), a3h(), a3J(), a4i(), a5e(), a60() (+290 more)

### Community 59 - "aQ"
Cohesion: 0.03
Nodes (51): CreateAccommodation, EditAccommodation, CreateAirlineBaggageRule, EditAirlineBaggageRule, BookingsRelationManager, GraciaPointLedgersRelationManager, CreateAppNotification, EditAppNotification (+43 more)

### Community 60 - "buildTicks"
Cohesion: 0.03
Nodes (99): a01(), a02(), a32(), a62(), a6I(), a6N(), a7F(), a7Z() (+91 more)

### Community 61 - "ManageWebsiteSettings"
Cohesion: 0.03
Nodes (31): CreateServiceCancellation, GraciaPointsController, NotificationController, ReferralController, AirlineBaggageRule, AppNotification, DeletedVirtualNotification, GraciaEarningRule (+23 more)

### Community 62 - "support.js"
Cohesion: 0.04
Nodes (164): Qt(), _a(), aa(), Ae(), ai(), apply(), ar(), at() (+156 more)

### Community 63 - "gO"
Cohesion: 0.06
Nodes (56): a0d(), a2g(), ag(), agn(), agT(), alJ(), am4(), ao8() (+48 more)

### Community 64 - "RelationManager"
Cohesion: 0.04
Nodes (93): $0(), $2$params(), a1C(), a25(), a27(), a2n(), a39(), a3R() (+85 more)

### Community 65 - "I"
Cohesion: 0.09
Nodes (33): afterDatasetsUpdate(), buildOrUpdateControllers(), _d(), _destroyDatasetMeta(), generateLabels(), getDatasetMeta(), getDataVisibility(), getMaxBorderWidth() (+25 more)

### Community 66 - "i"
Cohesion: 0.06
Nodes (54): addElements(), aspectRatio(), buildOrUpdateElements(), C(), Ca(), Ce(), co(), _dataCheck() (+46 more)

### Community 67 - "get"
Cohesion: 0.04
Nodes (73): a0d(), a2g(), a83(), a9a(), aeL(), ag(), agn(), aGR() (+65 more)

### Community 68 - "State"
Cohesion: 0.05
Nodes (59): ActivityScreen, _ActivityScreenState, BookingDetailsScreen, _BookingDetailsScreenState, BookingSubmitScreen, _BookingSubmitScreenState, ContactScreen, _ContactScreenState (+51 more)

### Community 69 - "setAttribute"
Cohesion: 0.06
Nodes (43): beforeinput(), canApplyToDocument(), compositionend(), compositionstart(), compositionupdate(), constructor(), dragend(), end() (+35 more)

### Community 70 - "a"
Cohesion: 0.18
Nodes (17): cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl(), makeRequest(), markUploadErrored() (+9 more)

### Community 71 - "a5"
Cohesion: 0.08
Nodes (30): active(), addControllers(), addPlugins(), addScales(), _animateOptions(), cancel(), _createAnimations(), _createDescriptors() (+22 more)

### Community 72 - "notifications.js"
Cohesion: 0.06
Nodes (23): actions(), button(), constructor(), danger(), dispatch(), dispatchSelf(), dispatchTo(), duration() (+15 more)

### Community 73 - "s"
Cohesion: 0.08
Nodes (30): ap(), bp(), bu(), children(), er(), gf(), Ja(), method() (+22 more)

### Community 74 - "EditRecord"
Cohesion: 0.08
Nodes (28): addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), dn(), Du(), Ef() (+20 more)

### Community 75 - "Controller"
Cohesion: 0.09
Nodes (38): buildOrUpdateScales(), cl(), _computeLabelSizes(), cr(), D(), E(), ensureScalesHaveIDs(), Eo() (+30 more)

### Community 76 - "updateElements"
Cohesion: 0.07
Nodes (44): as(), bc(), _calculateBarIndexPixels(), calculateCircumference(), _circumference(), countVisibleElements(), _createItems(), datasetAnimationScopeKeys() (+36 more)

### Community 77 - "sendRequest"
Cohesion: 0.01
Nodes (325): $2$from$to(), $3$crossAxisPosition$mainAxisPosition(), a(), a0G(), a0J(), a0o(), a12(), a14() (+317 more)

### Community 78 - "push"
Cohesion: 0.04
Nodes (28): CreateUser, EditUser, AdminNotificationController, JsonResponse, BookingController, ScheduleController, VoucherController, AuthController (+20 more)

### Community 79 - "o8"
Cohesion: 0.10
Nodes (26): addDebounceOrThrottle(), applyBindingsObject(), attributesOnly(), bind2(), byPriority(), camelCase2(), debounce(), directives() (+18 more)

### Community 80 - "E"
Cohesion: 0.08
Nodes (36): canAcceptDataTransfer(), canDecreaseNestingLevel(), canIncreaseNestingLevel(), compositionControllerDidFocus(), createDOMRangeFromPoint(), createLocationRangeFromDOMRange(), decreaseNestingLevel(), didMouseDown() (+28 more)

### Community 81 - "wimp.js"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 82 - "skwasm.js"
Cohesion: 0.06
Nodes (14): d(), Ga(), Ja(), Ka(), La(), ma(), n(), Pc() (+6 more)

### Community 83 - "$1"
Cohesion: 0.02
Nodes (151): a0l(), a17(), a1e(), a1o(), a2K(), a2X(), a3X(), a43() (+143 more)

### Community 84 - "push"
Cohesion: 0.14
Nodes (15): box(), canBeConsolidatedWith(), compositionControllerDidRender(), fromUCS2String(), getTargetDOMRange(), hasSameAttributesAsPiece(), hasSameConstructorAs(), hasSameStringValueAsPiece() (+7 more)

### Community 85 - "getBoundingClientRect"
Cohesion: 0.13
Nodes (44): autoUpdate(), convertOffsetParentRelativeRectToViewportRelativeRect(), detectOverflow(), "node_modules/@alpinejs/anchor/dist/module.cjs.js"(), getBoundingClientRect(), getClientRectFromClippingAncestor(), getClientRects(), getClippingElementAncestors() (+36 more)

### Community 86 - "ManageProofs"
Cohesion: 0.12
Nodes (20): actionIsExternal(), canInvokeAction(), compositionControllerDidBlur(), compositionControllerDidSyncDocumentView(), compositionDidAddAttachment(), compositionDidChangeAttachmentPreviewURL(), compositionDidChangeCurrentAttributes(), compositionDidEditAttachment() (+12 more)

### Community 87 - "Dt"
Cohesion: 0.17
Nodes (16): ArrowLeft(), ArrowRight(), editAttachment(), expandSelectionInDirection(), getAttachmentAtRange(), getExpandedRangeInDirection(), left(), moveCursorInDirection() (+8 more)

### Community 88 - "preload"
Cohesion: 0.07
Nodes (50): disabled(), add(), ar(), buildTicks(), C(), _calculatePadding(), Co(), _computeAngle() (+42 more)

### Community 89 - "HasFactory"
Cohesion: 0.09
Nodes (30): average(), fn(), getBasePosition(), getBaseValue(), getCenterPoint(), getProps(), hasValue(), hn() (+22 more)

### Community 90 - "skwasm_heavy.js"
Cohesion: 0.06
Nodes (13): d(), Ga(), Ka(), La(), ma(), n(), Pc(), q() (+5 more)

### Community 91 - "b5"
Cohesion: 0.07
Nodes (36): afterAutoSkip(), Ao(), applyStack(), Bi(), buildLookupTable(), determineDataLimits(), endOf(), Fi() (+28 more)

### Community 92 - "G"
Cohesion: 0.02
Nodes (184): a11(), a1v(), a5(), a6G(), a_2(), ag1(), aK(), an() (+176 more)

### Community 93 - ".$2"
Cohesion: 0.01
Nodes (306): $1(), a18(), a1a(), a1w(), a2y(), a3h(), a3J(), a4i() (+298 more)

### Community 94 - "draw"
Cohesion: 0.01
Nodes (245): $0(), $1$allowPlatformDefault(), $2$params(), a0A(), a0B(), a0Z(), a1C(), a1N() (+237 more)

### Community 95 - "r"
Cohesion: 0.14
Nodes (10): BookingsSheet, OverallBreakdownSheet, Maatwebsite\Excel\Concerns\FromArray, Maatwebsite\Excel\Concerns\FromCollection, Maatwebsite\Excel\Concerns\WithColumnWidths, Maatwebsite\Excel\Concerns\WithHeadings, Maatwebsite\Excel\Concerns\WithMapping, Maatwebsite\Excel\Concerns\WithStyles (+2 more)

### Community 96 - ".$1"
Cohesion: 0.03
Nodes (108): _a(), after(), afterAutoSkip(), Ag(), Ai(), before(), buildLookupTable(), daysInMonth() (+100 more)

### Community 97 - "$0"
Cohesion: 0.15
Nodes (14): Ce(), De(), di(), e(), Ht(), Ie(), Me(), Re() (+6 more)

### Community 98 - "jU"
Cohesion: 0.04
Nodes (104): a0k(), a0S(), a0T(), a0y(), a13(), a5D(), a5i(), a5m() (+96 more)

### Community 99 - "M"
Cohesion: 0.06
Nodes (31): A(), b(), c(), e(), f(), fc(), g(), gc() (+23 more)

### Community 100 - "get"
Cohesion: 0.06
Nodes (30): ld(), A(), b(), c(), e(), ee(), f(), fc() (+22 more)

### Community 101 - "createMorphContext"
Cohesion: 0.08
Nodes (37): appendChild(), cloneNode(), cloneScriptTag(), closestComponent(), componentIsMissingProperty(), createElement(), createMorphContext(), extractUriAndQueryString() (+29 more)

### Community 102 - "navigate_default"
Cohesion: 0.11
Nodes (23): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+15 more)

### Community 103 - "aG"
Cohesion: 0.06
Nodes (56): a01(), a02(), a32(), a7Z(), a_j(), aA6(), afX(), ag3() (+48 more)

### Community 104 - "render"
Cohesion: 0.07
Nodes (33): xt(), cacheViewForObject(), compositionDidChangeDocument(), compositionDidLoadSnapshot(), createAttachmentNodes(), createChildView(), createContainerElement(), createDocumentFragmentForSync() (+25 more)

### Community 106 - "Vn"
Cohesion: 0.06
Nodes (6): BookingReschedule, PaymentProof, PromoImageManager, UserDashboard, Livewire\Component, Livewire\WithFileUploads

### Community 107 - "add"
Cohesion: 0.06
Nodes (36): attachmentForFile(), attributesForFile(), canSetCurrentTextAttribute(), compositionShouldAcceptFile(), didChangeAttributes(), didClickAttachment(), findAttachmentForElement(), getAttachmentAndPositionById() (+28 more)

### Community 108 - "UseAdminGuard.php"
Cohesion: 0.08
Nodes (33): A(), b(), Ba(), c(), Cd(), d(), E(), eb() (+25 more)

### Community 109 - "add"
Cohesion: 0.07
Nodes (49): observer(), add(), applyKeyboardCommand(), attachmentDidChangeAttributes(), attachmentEditorDidRequestRemovalOfAttachment(), canBeGrouped(), checkValidity(), copyUsingObjectMap() (+41 more)

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
Nodes (12): d(), Ga(), Ja(), Ka(), La(), ma(), n(), q() (+4 more)

### Community 115 - "Ve"
Cohesion: 0.13
Nodes (35): as(), Ce(), cs(), Ct(), d(), ed(), ei(), Es() (+27 more)

### Community 116 - "BookingReschedule"
Cohesion: 0.18
Nodes (12): Be(), ei(), ii(), le(), ni(), oi(), r(), ri() (+4 more)

### Community 117 - "Ra"
Cohesion: 0.07
Nodes (36): ai(), al(), ba(), bc(), ci(), co(), cr(), dc() (+28 more)

### Community 118 - "OJ"
Cohesion: 0.06
Nodes (13): c(), Ha(), Ka(), La(), ma(), Nc(), p(), q() (+5 more)

### Community 119 - "b"
Cohesion: 0.20
Nodes (11): b(), Dt(), Fe(), g(), He(), i(), ir(), Mt() (+3 more)

### Community 120 - "a1"
Cohesion: 0.03
Nodes (109): a1M(), a31(), a4v(), a5s(), a7O(), a9C(), a9t(), a9x() (+101 more)

### Community 121 - "getDatasetMeta"
Cohesion: 0.10
Nodes (24): a(), a(), a(), a(), a(), a(), At(), beforeDatasetDraw() (+16 more)

### Community 122 - "aW_"
Cohesion: 0.01
Nodes (234): $3(), a00(), a0A(), a0B(), a0E(), a0W(), a0Z(), a16() (+226 more)

### Community 123 - "navigate_default"
Cohesion: 0.07
Nodes (25): $, blp(), saDP(), blp(), saDP(), fromString(), a(), c() (+17 more)

### Community 124 - "Win32Window"
Cohesion: 0.12
Nodes (14): DartProject, HWND, LPARAM, LRESULT, UINT, WPARAM, FlutterWindow, flutter_controller_ (+6 more)

### Community 125 - "dO"
Cohesion: 0.28
Nodes (9): ac(), Ai(), ca(), Li(), oc(), ro(), sc(), Us() (+1 more)

### Community 126 - "gN"
Cohesion: 0.13
Nodes (19): extractScriptTagContent(), generateEvaluatorFromFunction(), generateEvaluatorFromString(), generateFunctionFromString(), getIterationScopeVariables(), getLengthValue(), getRootMargin(), getThreshold() (+11 more)

### Community 127 - "bJ"
Cohesion: 0.01
Nodes (353): $2$alignmentPolicy(), $5(), a0h(), a0I(), a1Q(), a20(), a21(), a2C() (+345 more)

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
Cohesion: 0.09
Nodes (31): Ac(), an(), color(), darken(), Dc(), desaturate(), eo(), fo() (+23 more)

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
Nodes (416): $2(), $2$priority$scheduler(), $4(), a0(), a0m(), a0p(), a0U(), a1() (+408 more)

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
Nodes (56): aa(), add(), Al(), ar(), bf(), buildTicks(), _cachedScopes(), count() (+48 more)

### Community 142 - ".$1"
Cohesion: 0.04
Nodes (80): addInitSelector(), addRootSelector(), allSelectors(), base64toBlob(), children(), cleanup(), cleanupAttributes(), cloneTree() (+72 more)

### Community 143 - "echo.js"
Cohesion: 0.10
Nodes (13): a(), ar(), at(), cr(), d(), f(), H(), ji() (+5 more)

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

### Community 154 - "dB"
Cohesion: 0.04
Nodes (82): a0x(), a1P(), a2H(), a2j(), a58(), a5a(), a5K(), a5p() (+74 more)

### Community 155 - "aM_"
Cohesion: 0.01
Nodes (369): $2$from$to(), a(), a0G(), a0J(), a0o(), a12(), a14(), a15() (+361 more)

### Community 156 - "dD"
Cohesion: 0.38
Nodes (10): HWND, LPARAM, LRESULT, UINT, WPARAM, EnableFullDpiSupportIfAvailable(), GetThisFromHandle, MessageHandler (+2 more)

### Community 157 - "bw"
Cohesion: 0.10
Nodes (28): autofocusElementsWithTheAutofocusAttribute(), createUrlObjectFromString(), extractDestinationFromLink(), fetchHtml(), fetchHtmlOrUsePrefetchedHtml(), getPretchedHtmlOr(), getUriStringFromUrlObject(), isPopoverSupported() (+20 more)

### Community 158 - "b6"
Cohesion: 0.02
Nodes (143): $1$1(), $2$isClosing(), a1f(), a1h(), a1J(), a2F(), a3K(), a47() (+135 more)

### Community 159 - "StatelessWidget"
Cohesion: 0.08
Nodes (25): _AboutFact, AboutScreen, AppDrawer, BookingSuccessScreen, _ContactInfoCard, _CounterButton, _DiscountCouponCard, _Field (+17 more)

### Community 160 - "add"
Cohesion: 0.08
Nodes (30): Bt(), xo(), addEventListener(), bindEvents(), bindResponsiveEvents(), bindUserEvents(), _checkEventBindings(), cs() (+22 more)

### Community 161 - "target"
Cohesion: 0.15
Nodes (19): search(), url(), cancelUpload(), getCsrfToken(), getUploadManager(), handleFileUpload(), handleS3PreSignedUrl(), handleSignedUrl() (+11 more)

### Community 163 - "bn"
Cohesion: 0.67
Nodes (3): ld(), ld(), R()

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
Cohesion: 0.05
Nodes (57): addElements(), afterDatasetsUpdate(), afterDraw(), buildOrUpdateControllers(), buildOrUpdateElements(), _dataCheck(), _destroy(), _destroyDatasetMeta() (+49 more)

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
Cohesion: 0.06
Nodes (51): Ah(), applyStack(), _calculateBarIndexPixels(), _calculateBarValuePixels(), calculateCircumference(), _circumference(), countVisibleElements(), fa() (+43 more)

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
Cohesion: 0.38
Nodes (7): bs(), ds(), Fr(), ft(), Ii(), ni(), oi()

### Community 178 - "makeRequest"
Cohesion: 0.14
Nodes (19): cancelUpload(), Di(), gt(), handleS3PreSignedUrl(), handleSignedUrl(), Hi(), ji(), makeRequest() (+11 more)

### Community 179 - "flutter.js"
Cohesion: 0.24
Nodes (14): c(), _createScriptTag(), E(), F(), _getNewServiceWorker(), I(), load(), loadEntrypoint() (+6 more)

### Community 180 - "add"
Cohesion: 0.12
Nodes (20): addCleanup(), applyUpdates(), Cl(), cleanup(), constructor(), Ee(), extractTypeModifiersAndValue(), ip() (+12 more)

### Community 181 - "AdminPanelProvider.php"
Cohesion: 0.09
Nodes (6): ViewBooking, Transaction, normalize_operator_name(), operator_is_ferry(), storage_asset_path(), Illuminate\Support\HtmlString

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
Nodes (27): A(), b(), c(), e(), ee(), f(), fc(), g() (+19 more)

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
Nodes (19): scripts, dev, post-autoload-dump, post-create-project-cmd, post-update-cmd, pre-package-uninstall, test, Composer\\Config::disableProcessTimeout (+11 more)

### Community 214 - "manifest.json"
Cohesion: 0.13
Nodes (14): background_color, categories, description, display, icons, lang, name, orientation (+6 more)

### Community 216 - "DatePicker"
Cohesion: 0.04
Nodes (26): Action, AdminNotifications, ManagePaymentSettings, ManageProofs, ManageRebookings, ManageTransportAccommodation, MyPage, OverallReports (+18 more)

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
Cohesion: 0.05
Nodes (60): attachFiles(), backspace(), createLinkHTML(), cut(), d(), delete(), deleteByComposition(), deleteByCut() (+52 more)

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

### Community 262 - "xc"
Cohesion: 0.09
Nodes (31): Bn(), En(), eo(), Gl(), gn(), ha(), Ie(), je() (+23 more)

### Community 263 - "dispatchEvent"
Cohesion: 0.20
Nodes (12): componentsByName(), dispatch(), dispatch2(), dispatch3(), dispatchEvent(), dispatchEvents(), dispatchGlobal(), dispatchSelf() (+4 more)

### Community 264 - "setup"
Cohesion: 0.25
Nodes (8): post-root-package-install, setup, composer install, npm install --ignore-scripts, npm run build, @php artisan key:generate, @php artisan migrate --force, @php -r \"file_exists('.env') || copy('.env.example', '.env');\

### Community 265 - "ut"
Cohesion: 0.09
Nodes (29): a86(), agX(), ah1(), aiq(), am2(), am5(), aqM(), avF() (+21 more)

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
- **38 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `$` connect `navigate_default` to `.mount`, `xc`, `d4`, `schedules.blade.php`, `draw`, `aM_`, `select.js`, `Schedule`, `TransportClass`, `x`, `j_`, `te`, `getSelectedRange`, `dH`, `bY`, `support.js`, `setAttribute`, `sendRequest`, `E`, `G`, `.$2`, `render`, `add`, `add`, `aW_`, `bJ`?**
  _High betweenness centrality (0.305) - this node is a cross-community bridge._
- **Why does `Q` connect `navigate_default` to `d`, `Schedule`, `H`, `$0`, `aW_`, `j_`, `add`, `aYd`, `getSelectedRange`, `Booking`, `b`, `dB`, `select.js`, `locationFromPosition`, `bJ`?**
  _High betweenness centrality (0.078) - this node is a cross-community bridge._
- **Why does `xc()` connect `xc` to `du`, `livewire.min.js`, `navigate_default`, `Ra`?**
  _High betweenness centrality (0.061) - this node is a cross-community bridge._
- **Are the 224 inferred relationships involving `a()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 224 inferred relationships involving `a()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`a()` has 224 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `app/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._
- **Are the 484 inferred relationships involving `b()` (e.g. with `web/main.dart.js` and `$0()`) actually correct?**
  _`b()` has 484 INFERRED edges - model-reasoned connections that need verification._