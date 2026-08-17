// ignore_for_file: use_build_context_synchronously, curly_braces_in_flow_control_structures, unused_local_variable, unnecessary_cast, unused_field, unused_element
import 'package:flutter/material.dart';
import 'package:flutter/services.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'dart:io';
import 'package:flutter/foundation.dart' show kIsWeb;
import 'package:url_launcher/url_launcher.dart';
import 'package:image_picker/image_picker.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'package:package_info_plus/package_info_plus.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import 'dart:async';
import 'package:intl/intl.dart';
import 'package:video_player/video_player.dart';
import 'package:flutter_svg/flutter_svg.dart';

import 'package:flutter_app_badger/flutter_app_badger.dart';
import 'notification_service.dart';
import 'forgot_password_screen.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();
  final prefs = await SharedPreferences.getInstance();
  final isFirstLaunch = prefs.getBool('first_launch') ?? true;
  await UserSession.init();
  try {
    await NotificationService.initialize(
        onNotificationTap: handleNotificationTap);
  } catch (e) {
    debugPrint('Failed to initialize notifications: $e');
  }
  runApp(MyApp(isFirstLaunch: isFirstLaunch));
}

// ==========================================
// BRAND COLORS
// ==========================================
const kGreen = Color(0xFF216417);
const kPink = Color(0xFFEE018D);

double _parseDouble(dynamic val) {
  if (val == null) return 0.0;
  if (val is num) return val.toDouble();
  return double.tryParse(val.toString()) ?? 0.0;
}

const kBgLight = Color(0xFFF8FAFC);
const kSlate800 = Color(0xFF1E293B);
const kSlate700 = Color(0xFF334155);
const kSlate600 = Color(0xFF475569);
const kSlate500 = Color(0xFF64748B);
const kSlate400 = Color(0xFF94A3B8);
const kSlate300 = Color(0xFFCBD5E1);
const kSlate200 = Color(0xFFE2E8F0);
const kSlate100 = Color(0xFFF1F5F9);
const kSlate50 = Color(0xFFF8FAFC);

// ==========================================
// GLOBAL EVENT BUS
// ==========================================
class AppEventBus {
  static final StreamController<String> _controller =
      StreamController<String>.broadcast();
  static Stream<String> get stream => _controller.stream;
  static void emit(String event) => _controller.add(event);
}

// ==========================================
// GLOBAL SESSION
// ==========================================
class UserSession {
  static bool isLoggedIn = false;
  static bool isEmailVerified = false;
  static String username = 'Traveler';
  static String email = 'user@amigagracia.com';
  static String phone = '';
  static String token = '';
  static String lookupToken = '';
  static String? referralCode;
  static int graciaPoints = 0;
  static final ValueNotifier<int> unreadNotificationsNotifier =
      ValueNotifier<int>(0);
  static int get unreadNotificationsCount => unreadNotificationsNotifier.value;
  static set unreadNotificationsCount(int count) {
    unreadNotificationsNotifier.value = count;
    if (count > 0) {
      FlutterAppBadger.updateBadgeCount(count);
    } else {
      FlutterAppBadger.removeBadge();
    }
  }

  static int pointsAwarded = 0;
  static int spendThreshold = 0;
  static String? autoApplyVoucherCode;

  // Match this with pubspec.yaml version
  static const String appVersion = '1.0.97+108';
  static String installedAppVersion = appVersion;

  static Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    isLoggedIn = prefs.getBool('isLoggedIn') ?? false;
    isEmailVerified = prefs.getBool('isEmailVerified') ?? false;
    username = prefs.getString('username') ?? 'Traveler';
    email = prefs.getString('email') ?? 'user@amigagracia.com';
    phone = prefs.getString('phone') ?? '';
    token = prefs.getString('token') ?? '';
    lookupToken = prefs.getString('lookupToken') ?? '';
    referralCode = prefs.getString('referralCode');
    graciaPoints = prefs.getInt('graciaPoints') ?? 0;
    pointsAwarded = prefs.getInt('pointsAwarded') ?? 0;
    spendThreshold = prefs.getInt('spendThreshold') ?? 0;

    try {
      final packageInfo = await PackageInfo.fromPlatform();
      final version = packageInfo.version.trim();
      final buildNumber = packageInfo.buildNumber.trim().isEmpty
          ? '0'
          : packageInfo.buildNumber.trim();
      installedAppVersion = '$version+$buildNumber';
    } catch (_) {
      installedAppVersion = appVersion;
    }
  }

  static bool isUpdateRequired(String latestVersion) {
    final normalizedLatest = latestVersion.trim();
    final normalizedInstalled = installedAppVersion.trim();
    if (normalizedLatest == normalizedInstalled) {
      return false;
    }

    final latest = _AppVersion.parse(normalizedLatest);
    final current = _AppVersion.parse(normalizedInstalled);
    if (latest == null || current == null) {
      return normalizedLatest != normalizedInstalled;
    }

    return latest.compareTo(current) > 0;
  }

  static Future<void> refreshInstalledAppVersion() async {
    try {
      final packageInfo = await PackageInfo.fromPlatform();
      final version = packageInfo.version.trim();
      final buildNumber = packageInfo.buildNumber.trim().isEmpty
          ? '0'
          : packageInfo.buildNumber.trim();
      installedAppVersion = '$version+$buildNumber';
    } catch (_) {
      installedAppVersion = appVersion;
    }
  }

  static Future<void> save() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('isLoggedIn', isLoggedIn);
    await prefs.setBool('isEmailVerified', isEmailVerified);
    await prefs.setString('username', username);
    await prefs.setString('email', email);
    await prefs.setString('phone', phone);
    if (referralCode != null) {
      await prefs.setString('referralCode', referralCode!);
    }
    await prefs.setString('token', token);
    await prefs.setString('lookupToken', lookupToken);
    await prefs.setInt('graciaPoints', graciaPoints);
    await prefs.setInt('pointsAwarded', pointsAwarded);
    await prefs.setInt('spendThreshold', spendThreshold);
  }

  static Future<void> clear() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('isLoggedIn');
    await prefs.remove('isEmailVerified');
    await prefs.remove('username');
    await prefs.remove('email');
    await prefs.remove('token');
    await prefs.remove('lookupToken');
    await prefs.remove('referralCode');
    await prefs.remove('graciaPoints');
    await prefs.remove('pointsAwarded');
    await prefs.remove('spendThreshold');
    isLoggedIn = false;
    isEmailVerified = false;
    username = 'Traveler';
    email = 'user@amigagracia.com';
    token = '';
    lookupToken = '';
    referralCode = null;
    graciaPoints = 0;
    pointsAwarded = 0;
    spendThreshold = 0;
  }

  static String getBaseUrl() {
    const configuredUrl = String.fromEnvironment(
      'API_BASE_URL',
      defaultValue: 'https://www.amigagracia.com',
    );

    if (kIsWeb && configuredUrl.isEmpty) return '';
    return configuredUrl.replaceFirst(RegExp(r'/$'), '');
  }

  static int calculateEarnedPoints(double price) {
    if (spendThreshold <= 0 || pointsAwarded <= 0) return 0;
    final centavos = (price * 100).toInt();
    return (centavos ~/ spendThreshold) * pointsAwarded;
  }
}

class _AppVersion implements Comparable<_AppVersion> {
  final List<int> versionParts;
  final int buildNumber;

  _AppVersion(this.versionParts, this.buildNumber);

  static _AppVersion? parse(String raw) {
    final parts = raw.split('+');
    if (parts.isEmpty) return null;

    final versionNumbers = parts[0].split('.');
    final parsedParts = <int>[];
    for (final number in versionNumbers) {
      final parsed = int.tryParse(number);
      if (parsed == null) return null;
      parsedParts.add(parsed);
    }

    final build = parts.length > 1 ? int.tryParse(parts[1]) ?? 0 : 0;
    return _AppVersion(parsedParts, build);
  }

  @override
  int compareTo(_AppVersion other) {
    for (var i = 0;
        i < versionParts.length || i < other.versionParts.length;
        i++) {
      final a = i < versionParts.length ? versionParts[i] : 0;
      final b = i < other.versionParts.length ? other.versionParts[i] : 0;
      if (a != b) return a.compareTo(b);
    }
    return buildNumber.compareTo(other.buildNumber);
  }
}

// ==========================================
// BOOKING STATE (passed through screens)
// ==========================================
class BookingData {
  static BookingData? activeSession;

  String mode = 'ferry'; // ferry | airline
  String tripType = 'one_way';
  String? operator;
  String origin = '';
  String destination = '';
  String departureDate = '';
  String? returnDate;
  int adults = 1;
  int children = 0;
  int minors = 0;
  int infants = 0;

  int get totalPassengers => adults + children + minors + infants;

  static String passengerTypeLabel(String type, int index) {
    switch (type.toLowerCase()) {
      case 'driver':
        return 'Driver';
      case 'adult':
        return 'Adult $index';
      case 'minor':
        return 'Minor $index';
      case 'child':
        return 'Child $index';
      case 'infant':
        return 'Infant $index';
      default:
        return type[0].toUpperCase() + type.substring(1);
    }
  }

  static List<Map<String, dynamic>> buildPassengerEntries({
    required int adults,
    required int children,
    required int minors,
    required int infants,
    required bool hasVehicle,
    required String vehicleDriverName,
    required String vehicleDriverBirthday,
    required bool isAirline,
  }) {
    final entries = <Map<String, dynamic>>[];
    final driverCount = hasVehicle ? 1 : 0;
    final adultCount = adults - driverCount;

    for (var i = 0; i < driverCount; i++) {
      entries.add({
        'type': 'driver',
        'name': vehicleDriverName,
        'birthdate': vehicleDriverBirthday,
        'discount_id': null,
      });
    }

    for (var i = 0; i < adultCount; i++) {
      entries.add({
        'type': 'adult',
        'name': '',
        'birthdate': '',
        'discount_id': null,
      });
    }

    if (isAirline) {
      for (var i = 0; i < minors; i++) {
        entries.add({
          'type': 'minor',
          'name': '',
          'birthdate': '',
          'discount_id': null,
        });
      }
      for (var i = 0; i < children; i++) {
        entries.add({
          'type': 'child',
          'name': '',
          'birthdate': '',
          'discount_id': null,
        });
      }
      for (var i = 0; i < infants; i++) {
        entries.add({
          'type': 'infant',
          'name': '',
          'birthdate': '',
          'discount_id': null,
        });
      }
    } else {
      for (var i = 0; i < children; i++) {
        entries.add({
          'type': 'child',
          'name': '',
          'birthdate': '',
          'discount_id': null,
        });
      }
    }

    return entries;
  }

  // Step 2 — Schedule
  bool hasExtraBaggage = false;
  int? extraBaggageKg; // selected kg (e.g. 10, 20, 25, 30)
  double extraBaggagePrice = 0.0; // price per pax for the selected kg
  String? extraBaggageType;
  String? extraBaggageSpecify;
  Map<String, dynamic>? selectedSchedule;
  int? selectedTransportClassId;
  Map<String, dynamic>? selectedTransportClass;
  int? selectedScheduleAccommodationId;
  Map<String, dynamic>? selectedScheduleAccommodation;

  Map<String, dynamic>? selectedReturnSchedule;
  int? selectedReturnScheduleAccommodationId;
  Map<String, dynamic>? selectedReturnScheduleAccommodation;

  int? selectedFerryAccommodationId;
  String? selectedFerryAccommodationName;
  double? selectedFerryAccommodationPrice;
  int? selectedReturnFerryAccommodationId;
  String? selectedReturnFerryAccommodationName;
  double? selectedReturnFerryAccommodationPrice;

  int? selectedAirlineClassId;
  String? selectedAirlineClassName;
  double? selectedAirlineClassPrice;
  int? selectedReturnAirlineClassId;
  String? selectedReturnAirlineClassName;
  double? selectedReturnAirlineClassPrice;

  // Vehicle (Ferry only)
  bool hasVehicle = false;
  int? selectedVehicleRateId;
  String vehicleType = '';
  String vehiclePlateNumber = '';
  double vehiclePrice = 0.0;
  String vehicleDriverFirstName = '';
  String vehicleDriverMiddleName = '';
  String vehicleDriverLastName = '';
  String vehicleDriverBirthday = '';

  // Step 3 — Passengers with discounts
  // Each passenger: {'type': 'adult'|'child', 'name': '', 'discount_id': int?}
  List<Map<String, dynamic>> passengers = [];

  // Step 4 — Stay (accommodations)
  List<int> selectedAccommodationIds = [];
  List<Map<String, dynamic>> availableAccommodations = [];

  // Step 5 — Contact
  String clientName = '';
  String clientEmail = '';
  String clientPhone = '';

  // Voucher
  String? voucherCode;
  Map<String, dynamic>?
      voucherData; // {name, discount_type, discount_value, eligible_scope, discount_amount, final_total}

  // Promotional ticket (promo price applied when schedule has an active promo)
  int? promotionalTicketId;
  bool usePromoTicket = true;

  // Pricing
  double totalPrice = 0;

  // State restoration
  int savedStep = 0;

  Map<String, dynamic> toJson() {
    return {
      'mode': mode,
      'tripType': tripType,
      'operator': operator,
      'origin': origin,
      'destination': destination,
      'departureDate': departureDate,
      'returnDate': returnDate,
      'adults': adults,
      'children': children,
      'minors': minors,
      'infants': infants,
      'hasExtraBaggage': hasExtraBaggage,
      'extraBaggageKg': extraBaggageKg,
      'extraBaggagePrice': extraBaggagePrice,
      'extraBaggageType': extraBaggageType,
      'extraBaggageSpecify': extraBaggageSpecify,
      'selectedSchedule': selectedSchedule,
      'selectedTransportClassId': selectedTransportClassId,
      'selectedTransportClass': selectedTransportClass,
      'selectedScheduleAccommodationId': selectedScheduleAccommodationId,
      'selectedScheduleAccommodation': selectedScheduleAccommodation,
      'selectedReturnSchedule': selectedReturnSchedule,
      'selectedReturnScheduleAccommodationId':
          selectedReturnScheduleAccommodationId,
      'selectedReturnScheduleAccommodation':
          selectedReturnScheduleAccommodation,
      'selectedFerryAccommodationId': selectedFerryAccommodationId,
      'selectedFerryAccommodationName': selectedFerryAccommodationName,
      'selectedFerryAccommodationPrice': selectedFerryAccommodationPrice,
      'selectedReturnFerryAccommodationId': selectedReturnFerryAccommodationId,
      'selectedReturnFerryAccommodationName':
          selectedReturnFerryAccommodationName,
      'selectedReturnFerryAccommodationPrice':
          selectedReturnFerryAccommodationPrice,
      'selectedAirlineClassId': selectedAirlineClassId,
      'selectedAirlineClassName': selectedAirlineClassName,
      'selectedAirlineClassPrice': selectedAirlineClassPrice,
      'hasVehicle': hasVehicle,
      'selectedVehicleRateId': selectedVehicleRateId,
      'vehicleType': vehicleType,
      'vehiclePlateNumber': vehiclePlateNumber,
      'vehiclePrice': vehiclePrice,
      'vehicleDriverFirstName': vehicleDriverFirstName,
      'vehicleDriverMiddleName': vehicleDriverMiddleName,
      'vehicleDriverLastName': vehicleDriverLastName,
      'vehicleDriverBirthday': vehicleDriverBirthday,
      'passengers': passengers,
      'selectedAccommodationIds': selectedAccommodationIds,
      'availableAccommodations': availableAccommodations,
      'clientName': clientName,
      'clientEmail': clientEmail,
      'clientPhone': clientPhone,
      'voucherCode': voucherCode,
      'voucherData': voucherData,
      'promotionalTicketId': promotionalTicketId,
      'usePromoTicket': usePromoTicket,
      'totalPrice': totalPrice,
      'savedStep': savedStep,
    };
  }

  static BookingData fromJson(Map<String, dynamic> json) {
    final b = BookingData();
    b.mode = json['mode'] ?? 'ferry';
    b.tripType = json['tripType'] ?? 'one_way';
    b.operator = json['operator'];
    b.origin = json['origin'] ?? '';
    b.destination = json['destination'] ?? '';
    b.departureDate = json['departureDate'] ?? '';
    b.returnDate = json['returnDate'];
    b.adults = json['adults'] ?? 1;
    b.children = json['children'] ?? 0;
    b.minors = json['minors'] ?? 0;
    b.infants = json['infants'] ?? 0;
    b.hasExtraBaggage = json['hasExtraBaggage'] ?? false;
    b.extraBaggageKg = json['extraBaggageKg'];
    b.extraBaggagePrice = (json['extraBaggagePrice'] ?? 0.0).toDouble();
    b.extraBaggageType = json['extraBaggageType'];
    b.extraBaggageSpecify = json['extraBaggageSpecify'];

    b.selectedSchedule = json['selectedSchedule'] != null
        ? Map<String, dynamic>.from(json['selectedSchedule'])
        : null;
    b.selectedTransportClassId = json['selectedTransportClassId'];
    b.selectedTransportClass = json['selectedTransportClass'] != null
        ? Map<String, dynamic>.from(json['selectedTransportClass'])
        : null;
    b.selectedScheduleAccommodationId = json['selectedScheduleAccommodationId'];
    b.selectedScheduleAccommodation =
        json['selectedScheduleAccommodation'] != null
            ? Map<String, dynamic>.from(json['selectedScheduleAccommodation'])
            : null;

    b.selectedReturnSchedule = json['selectedReturnSchedule'] != null
        ? Map<String, dynamic>.from(json['selectedReturnSchedule'])
        : null;
    b.selectedReturnScheduleAccommodationId =
        json['selectedReturnScheduleAccommodationId'];
    b.selectedReturnScheduleAccommodation =
        json['selectedReturnScheduleAccommodation'] != null
            ? Map<String, dynamic>.from(
                json['selectedReturnScheduleAccommodation'])
            : null;

    b.selectedFerryAccommodationId = json['selectedFerryAccommodationId'];
    b.selectedFerryAccommodationName = json['selectedFerryAccommodationName'];
    b.selectedFerryAccommodationPrice =
        json['selectedFerryAccommodationPrice'] != null
            ? (json['selectedFerryAccommodationPrice'] as num).toDouble()
            : null;

    b.selectedReturnFerryAccommodationId =
        json['selectedReturnFerryAccommodationId'];
    b.selectedReturnFerryAccommodationName =
        json['selectedReturnFerryAccommodationName'];
    b.selectedReturnFerryAccommodationPrice =
        json['selectedReturnFerryAccommodationPrice'] != null
            ? (json['selectedReturnFerryAccommodationPrice'] as num).toDouble()
            : null;

    b.selectedAirlineClassId = json['selectedAirlineClassId'];
    b.selectedAirlineClassName = json['selectedAirlineClassName'];
    b.selectedAirlineClassPrice = json['selectedAirlineClassPrice'] != null
        ? (json['selectedAirlineClassPrice'] as num).toDouble()
        : null;

    b.hasVehicle = json['hasVehicle'] ?? false;
    b.selectedVehicleRateId = json['selectedVehicleRateId'];
    b.vehicleType = json['vehicleType'] ?? '';
    b.vehiclePlateNumber = json['vehiclePlateNumber'] ?? '';
    b.vehiclePrice = (json['vehiclePrice'] ?? 0.0).toDouble();
    b.vehicleDriverFirstName = json['vehicleDriverFirstName'] ?? '';
    b.vehicleDriverMiddleName = json['vehicleDriverMiddleName'] ?? '';
    b.vehicleDriverLastName = json['vehicleDriverLastName'] ?? '';
    b.vehicleDriverBirthday = json['vehicleDriverBirthday'] ?? '';

    if (json['passengers'] != null) {
      b.passengers = List<Map<String, dynamic>>.from(
          json['passengers'].map((x) => Map<String, dynamic>.from(x)));
    }

    if (json['selectedAccommodationIds'] != null) {
      b.selectedAccommodationIds =
          List<int>.from(json['selectedAccommodationIds']);
    }
    if (json['availableAccommodations'] != null) {
      b.availableAccommodations = List<Map<String, dynamic>>.from(
          json['availableAccommodations']
              .map((x) => Map<String, dynamic>.from(x)));
    }

    b.clientName = json['clientName'] ?? '';
    b.clientEmail = json['clientEmail'] ?? '';
    b.clientPhone = json['clientPhone'] ?? '';
    b.voucherCode = json['voucherCode'];
    b.voucherData = json['voucherData'] != null
        ? Map<String, dynamic>.from(json['voucherData'])
        : null;
    b.promotionalTicketId = json['promotionalTicketId'];
    b.usePromoTicket = json['usePromoTicket'] ?? true;
    b.totalPrice = (json['totalPrice'] ?? 0.0).toDouble();
    b.savedStep = json['savedStep'] ?? 0;

    return b;
  }

  Future<void> saveToPrefs(int currentStep) async {
    savedStep = currentStep;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString('saved_booking_session', jsonEncode(toJson()));
  }

  static Future<BookingData?> loadFromPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    final str = prefs.getString('saved_booking_session');
    if (str != null && str.isNotEmpty) {
      try {
        return fromJson(jsonDecode(str));
      } catch (e) {
        debugPrint('Error decoding saved session: $e');
      }
    }
    return null;
  }

  static Future<void> clearPrefs() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove('saved_booking_session');
  }
} // end BookingData

List<dynamic> parseJsonList(dynamic raw) {
  if (raw is List) return raw;
  if (raw is Map) return raw.values.toList();
  return [];
}

List<dynamic> parseAndFilterSchedules(dynamic raw, [String? selectedDate]) {
  final list = parseJsonList(raw);
  final now = DateTime.now();
  return list.where((s) {
    final depTimeStr = s['departure_time_iso'] ?? s['departure_time'];
    if (depTimeStr == null) return true;
    try {
      String timeStr = depTimeStr.toString();
      DateTime dt;
      if (timeStr.length == 8 &&
          selectedDate != null &&
          selectedDate.isNotEmpty) {
        dt = DateTime.parse('$selectedDate $timeStr').toLocal();
      } else {
        dt = DateTime.parse(timeStr).toLocal();
      }
      return dt.isAfter(now);
    } catch (_) {
      return true;
    }
  }).toList();
}

Map<String, dynamic>? pendingNotificationData;

Future<void> handleNotificationTap(Map<String, dynamic> data) async {
  final String type = data['type'] ?? 'general';
  final String targetId = data['target_id']?.toString() ??
      data['transaction_number']?.toString() ??
      '';

  // Check if we have a context
  final context = navigatorKey.currentContext;
  if (context == null) {
    pendingNotificationData = data;
    return;
  }

  if (type == 'booking' || type == 'payment') {
    if (targetId.isNotEmpty) {
      showDialog(
        context: context,
        barrierDismissible: false,
        builder: (context) =>
            const Center(child: CircularProgressIndicator(color: kGreen)),
      );

      try {
        final response = await http.get(
          Uri.parse('${UserSession.getBaseUrl()}/api/bookings/$targetId'),
          headers: {
            'Authorization': 'Bearer ${UserSession.token}',
            'Accept': 'application/json',
          },
        );

        Navigator.pop(context); // hide loading

        if (response.statusCode == 200) {
          final resData = jsonDecode(response.body);
          if (resData['status'] == 'success' && resData['booking'] != null) {
            Navigator.push(
              context,
              MaterialPageRoute(
                builder: (_) =>
                    BookingDetailsScreen(booking: resData['booking']),
              ),
            );
          } else {
            showTopSnack(context,
                const SnackBar(content: Text('Booking details not found.')));
          }
        } else {
          showTopSnack(context,
              const SnackBar(content: Text('Failed to load booking details.')));
        }
      } catch (e) {
        Navigator.pop(context);
        showTopSnack(context, SnackBar(content: Text('Error: $e')));
      }
    }
  } else if (type == 'promo') {
    Navigator.popUntil(context, (route) => route.isFirst);
    final mainState = context.findAncestorStateOfType<_MainScreenState>();
    mainState?.switchTab(1);
  } else if (type == 'voucher') {
    Navigator.popUntil(context, (route) => route.isFirst);
    final mainState = context.findAncestorStateOfType<_MainScreenState>();
    mainState?.switchTab(3);
  }
}

final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

class GlobalUpdateWrapper extends StatefulWidget {
  final Widget child;
  const GlobalUpdateWrapper({super.key, required this.child});
  @override
  State<GlobalUpdateWrapper> createState() => _GlobalUpdateWrapperState();
}

class _GlobalUpdateWrapperState extends State<GlobalUpdateWrapper>
    with WidgetsBindingObserver {
  bool _isChecking = false;

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addObserver(this);

    WidgetsBinding.instance.addPostFrameCallback((_) {
      _checkVersionAndPrompt();
    });
  }

  @override
  void dispose() {
    WidgetsBinding.instance.removeObserver(this);
    super.dispose();
  }

  @override
  void didChangeAppLifecycleState(AppLifecycleState state) {
    if (state == AppLifecycleState.resumed) {
      _checkVersionAndPrompt();
    } else if (state == AppLifecycleState.paused ||
        state == AppLifecycleState.inactive) {
      if (BookingData.activeSession != null) {
        BookingData.activeSession!
            .saveToPrefs(BookingData.activeSession!.savedStep);
      }
    }
  }

  Future<void> _checkVersionAndPrompt() async {
    if (_isChecking) return;
    
    // Skip version check for 10 seconds after an update is triggered
    if (UpdateChecker._updateInstalledTime != null) {
      final elapsed = DateTime.now().difference(UpdateChecker._updateInstalledTime!);
      if (elapsed.inSeconds < 10) {
        return;
      }
      UpdateChecker._updateInstalledTime = null;
    }
    
    _isChecking = true;
    try {
      await UserSession.refreshInstalledAppVersion();
      final response = await http
          .get(Uri.parse('${UserSession.getBaseUrl()}/api/app-version'))
          .timeout(const Duration(seconds: 5));
      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final latestVersion = data['version'] as String;
        final forceUpdate = data['force_update'] as bool? ?? true;
        final updateRequired = UserSession.isUpdateRequired(latestVersion);
        if (!updateRequired) {
          UpdateChecker._apkInstallTriggered = false;
        }
        if (forceUpdate && updateRequired && mounted) {
          final context = navigatorKey.currentContext;
          if (context != null) {
            if (!mounted) return;
            await UpdateChecker.showUpdateDialog(context, latestVersion);
          }
        }
      }
    } catch (_) {
    } finally {
      _isChecking = false;
    }
  }

  @override
  Widget build(BuildContext context) => widget.child;
}

class UpdateChecker {
  static String? _lastPromptedVersion;
  static bool _dialogAlreadyVisible = false;
  static bool _apkInstallTriggered = false;
  static DateTime? _updateInstalledTime;

  static Future<void> showUpdateDialog(
      BuildContext context, String latestVersion) async {
    if (_dialogAlreadyVisible && _lastPromptedVersion == latestVersion) {
      return;
    }

    if (_apkInstallTriggered && _lastPromptedVersion == latestVersion) {
      return;
    }

    _dialogAlreadyVisible = true;
    _lastPromptedVersion = latestVersion;

    await showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) {
        bool isDownloading = false;
        double progress = 0.0;
        String dlError = '';
        return StatefulBuilder(
          builder: (context, setState) {
            return AlertDialog(
              title: const Text('Update Required'),
              content: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Text(
                      'A new version ($latestVersion) of Amiga Gracia is available. Please update to continue using the app.'),
                  if (isDownloading) ...[
                    const SizedBox(height: 20),
                    LinearProgressIndicator(value: progress, color: kGreen),
                    const SizedBox(height: 8),
                    Text('${(progress * 100).toStringAsFixed(0)}% downloaded'),
                  ],
                  if (dlError.isNotEmpty) ...[
                    const SizedBox(height: 12),
                    Text(dlError,
                        style:
                            const TextStyle(color: Colors.red, fontSize: 12)),
                  ]
                ],
              ),
              actions: [
                if (!isDownloading)
                  FilledButton(
                    onPressed: () async {
                      setState(() {
                        isDownloading = true;
                        dlError = '';
                      });
                      try {
                        final apkUrl =
                            '${UserSession.getBaseUrl()}/downloads/amiga-travel.apk?v=$latestVersion';
                        final request = http.Request('GET', Uri.parse(apkUrl));
                        final response = await http.Client().send(request);
                        if (response.statusCode != 200) {
                          throw Exception(
                              'Server returned ${response.statusCode}');
                        }

                        final contentLength = response.contentLength ?? 1;
                        final dir = await getExternalStorageDirectory();
                        final file =
                            File('${dir!.path}/update_$latestVersion.apk');
                        final sink = file.openWrite();

                        int bytes = 0;
                        double lastProgress = 0.0;
                        await response.stream.listen((List<int> chunk) {
                          bytes += chunk.length;
                          sink.add(chunk);
                          double currentProgress = bytes / contentLength;
                          if (currentProgress - lastProgress >= 0.01 ||
                              currentProgress >= 1.0) {
                            lastProgress = currentProgress;
                            setState(() => progress = currentProgress);
                          }
                        }).asFuture();
                        await sink.close();

                        await UserSession.save();
                        if (BookingData.activeSession != null) {
                          await BookingData.activeSession!.saveToPrefs(
                              BookingData.activeSession!.savedStep);
                        }

                        _apkInstallTriggered = true;
                        UpdateChecker._updateInstalledTime = DateTime.now();

                        final result = await OpenFilex.open(file.path);
                        if (result.type != ResultType.done) {
                          _apkInstallTriggered = false;
                          UpdateChecker._updateInstalledTime = null;
                          throw Exception(result.message);
                        }

                        if (context.mounted) {
                          Navigator.of(context, rootNavigator: true).pop();
                        }

                        return;
                      } catch (e) {
                        debugPrint('Download error: $e');
                        setState(() {
                          isDownloading = false;
                          dlError =
                              'Failed to download update. Please try again or download from website.';
                        });
                      }
                    },
                    child: const Text('Update Now'),
                  ),
              ],
            );
          },
        );
      },
    );

    _dialogAlreadyVisible = false;
  }
}

// ==========================================
// APP ENTRY
// ==========================================
class MyApp extends StatelessWidget {
  final bool isFirstLaunch;
  const MyApp({super.key, required this.isFirstLaunch});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      navigatorKey: navigatorKey,
      title: 'Amiga Gracia',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        useMaterial3: true,
        colorScheme: ColorScheme.fromSeed(
          seedColor: kGreen,
          primary: kGreen,
          secondary: kPink,
        ),
        scaffoldBackgroundColor: kBgLight,
        appBarTheme: const AppBarTheme(
          backgroundColor: kGreen,
          foregroundColor: Colors.white,
          elevation: 2,
        ),
      ),
      builder: (context, child) => GlobalUpdateWrapper(child: child!),
      home: SplashLoaderScreen(isFirstLaunch: isFirstLaunch),
    );
  }
}

// â”€â”€ Top Snackbar Helper â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
/// Shows a snackbar anchored to the TOP of the screen.
void showTopSnack(
  BuildContext context,
  SnackBar snackBar,
) {
  final messenger = ScaffoldMessenger.of(context);
  messenger.hideCurrentSnackBar();
  messenger.showSnackBar(
    SnackBar(
      content: snackBar.content,
      behavior: SnackBarBehavior.floating,
      margin: EdgeInsets.only(
        top: MediaQuery.of(context).padding.top + 12,
        left: 12,
        right: 12,
      ),
      duration: snackBar.duration,
      action: snackBar.action,
      backgroundColor: snackBar.backgroundColor,
      shape: snackBar.shape ??
          RoundedRectangleBorder(
            borderRadius: BorderRadius.circular(12),
          ),
      elevation: snackBar.elevation,
      padding: snackBar.padding,
      width: snackBar.width,
      onVisible: snackBar.onVisible,
      dismissDirection: snackBar.dismissDirection,
      showCloseIcon: snackBar.showCloseIcon,
      closeIconColor: snackBar.closeIconColor,
    ),
  );
}

// â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€â”€
// ==========================================
// SPLASH & ONBOARDING
// ==========================================
class SplashLoaderScreen extends StatefulWidget {
  final bool isFirstLaunch;
  const SplashLoaderScreen({super.key, required this.isFirstLaunch});

  @override
  State<SplashLoaderScreen> createState() => _SplashLoaderScreenState();
}

class _SplashLoaderScreenState extends State<SplashLoaderScreen> {
  Timer? _navigationTimer;

  @override
  void initState() {
    super.initState();
    _checkVersionAndProceed();
  }

  @override
  void dispose() {
    _navigationTimer?.cancel();
    super.dispose();
  }

  Future<void> _checkVersionAndProceed() async {
    // Skip version check for 10 seconds after an update is triggered
    if (UpdateChecker._updateInstalledTime != null) {
      final elapsed = DateTime.now().difference(UpdateChecker._updateInstalledTime!);
      if (elapsed.inSeconds < 10) {
        _navigateForward();
        return;
      }
      UpdateChecker._updateInstalledTime = null;
    }

    await UserSession.refreshInstalledAppVersion();

    try {
      final response = await http
          .get(Uri.parse('${UserSession.getBaseUrl()}/api/app-version'))
          .timeout(const Duration(seconds: 5));

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final latestVersion = data['version'] as String;
        final forceUpdate = data['force_update'] as bool? ?? true;

        if (forceUpdate && UserSession.isUpdateRequired(latestVersion)) {
          if (mounted) {
            await UpdateChecker.showUpdateDialog(context, latestVersion);
          }
          await UserSession.refreshInstalledAppVersion();
          if (!UserSession.isUpdateRequired(latestVersion)) {
            _navigateForward();
            return;
          }
          _navigationTimer = Timer(const Duration(seconds: 3), () {
            if (mounted) _checkVersionAndProceed();
          });
          return;
        }
      }
    } catch (e) {
      debugPrint('Version check failed: $e');
    }

    _navigateForward();
  }

  void _navigateForward() {
    _navigationTimer = Timer(const Duration(seconds: 2), () {
      if (mounted) {
        Navigator.pushReplacement(
          context,
          MaterialPageRoute(
            builder: (_) => widget.isFirstLaunch
                ? const OnboardingScreen()
                : const MainScreen(),
          ),
        );
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Image.asset('assets/icon/app_icon.png',
                width: 180, height: 180, fit: BoxFit.contain),
            const SizedBox(height: 24),
            const CircularProgressIndicator(color: kGreen),
            const SizedBox(height: 16),
            const Text('Connecting to Amiga Gracia...',
                style: TextStyle(
                    color: kGreen, fontSize: 16, fontWeight: FontWeight.bold)),
          ],
        ),
      ),
    );
  }
}

class OnboardingScreen extends StatefulWidget {
  const OnboardingScreen({super.key});

  @override
  State<OnboardingScreen> createState() => _OnboardingScreenState();
}

class _OnboardingScreenState extends State<OnboardingScreen> {
  final PageController _pageController = PageController();
  int _currentPage = 0;

  final List<Map<String, String>> _slides = [
    {
      'title': 'Welcome to Amiga Gracia',
      'desc':
          'The fastest way to book your ferry, flight, and tour packages online.',
      'icon': 'explore',
    },
    {
      'title': 'Hassle-Free Travel',
      'desc':
          'Skip the lines at the terminal. Pay securely via GCash or Bank Transfer directly in the app.',
      'icon': 'payments',
    },
    {
      'title': 'Exclusive Promos & Discounts',
      'desc':
          'Get access to special rates for students, seniors, PWDs, and early bookings.',
      'icon': 'local_offer',
    },
  ];

  void _finishOnboarding() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setBool('first_launch', false);
    if (mounted) {
      Navigator.pushReplacement(
          context, MaterialPageRoute(builder: (_) => const MainScreen()));
    }
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.white,
      body: SafeArea(
        child: Column(
          children: [
            Expanded(
              child: PageView.builder(
                controller: _pageController,
                onPageChanged: (i) => setState(() => _currentPage = i),
                itemCount: _slides.length,
                itemBuilder: (context, i) {
                  return Padding(
                    padding: const EdgeInsets.all(40),
                    child: Column(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Icon(
                          i == 0
                              ? Icons.explore
                              : i == 1
                                  ? Icons.payments
                                  : Icons.local_offer,
                          size: 100,
                          color: kGreen,
                        ),
                        const SizedBox(height: 40),
                        Text(
                          _slides[i]['title']!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              fontSize: 24,
                              fontWeight: FontWeight.w900,
                              color: kSlate800),
                        ),
                        const SizedBox(height: 16),
                        Text(
                          _slides[i]['desc']!,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              fontSize: 16, color: kSlate600, height: 1.5),
                        ),
                      ],
                    ),
                  );
                },
              ),
            ),
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(_slides.length, (i) {
                return AnimatedContainer(
                  duration: const Duration(milliseconds: 300),
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  height: 8,
                  width: _currentPage == i ? 24 : 8,
                  decoration: BoxDecoration(
                      color: _currentPage == i ? kGreen : kSlate200,
                      borderRadius: BorderRadius.circular(4)),
                );
              }),
            ),
            const SizedBox(height: 40),
            Padding(
              padding: const EdgeInsets.symmetric(horizontal: 24),
              child: SizedBox(
                width: double.infinity,
                height: 54,
                child: ElevatedButton(
                  onPressed: () {
                    if (_currentPage < _slides.length - 1) {
                      _pageController.nextPage(
                          duration: const Duration(milliseconds: 300),
                          curve: Curves.easeInOut);
                    } else {
                      _finishOnboarding();
                    }
                  },
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPink,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16)),
                  ),
                  child: Text(
                    _currentPage == _slides.length - 1 ? 'Get Started' : 'Next',
                    style: const TextStyle(
                        fontSize: 16, fontWeight: FontWeight.bold),
                  ),
                ),
              ),
            ),
            const SizedBox(height: 32),
          ],
        ),
      ),
    );
  }
}

// ==========================================
// MAIN SCREEN WITH BOTTOM NAV
// ==========================================
class MainScreen extends StatefulWidget {
  final int initialTab;
  const MainScreen({super.key, this.initialTab = 0});

  @override
  State<MainScreen> createState() => _MainScreenState();
}

class _MainScreenState extends State<MainScreen> {
  late int _selectedIndex;
  String? _travelMode;
  final GlobalKey<ScaffoldState> _scaffoldKey = GlobalKey<ScaffoldState>();
  Key _activityKey = UniqueKey();

  Timer? _notificationPollTimer;
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    _selectedIndex = widget.initialTab;
    _fetchGlobalData();
    NotificationService.requestPermission();
    NotificationService.initialize();

    _notificationPollTimer =
        Timer.periodic(const Duration(seconds: 5), (timer) {
      if (UserSession.isLoggedIn) {
        _fetchGlobalData(isBackground: true);
      }
    });

    WidgetsBinding.instance.addPostFrameCallback((_) {
      if (pendingNotificationData != null) {
        final data = pendingNotificationData!;
        pendingNotificationData = null;
        handleNotificationTap(data);
      }
    });

    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'fetch_global_data') {
        _fetchGlobalData(isBackground: true);
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    _notificationPollTimer?.cancel();
    super.dispose();
  }

  Future<void> _fetchGlobalData({bool isBackground = false}) async {
    if (UserSession.isLoggedIn && UserSession.token.isNotEmpty) {
      try {
        final res = await http.get(
          Uri.parse('${UserSession.getBaseUrl()}/api/gracia-points'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ${UserSession.token}',
          },
        );
        final data = jsonDecode(res.body);
        if (res.statusCode == 200 && data['status'] == 'success') {
          setState(() {
            UserSession.graciaPoints = data['current_points'] ?? 0;
            if (data['active_rule'] != null) {
              UserSession.pointsAwarded =
                  data['active_rule']['points_awarded'] ?? 0;
              UserSession.spendThreshold =
                  data['active_rule']['spend_threshold_centavos'] ?? 0;
            }
          });
          UserSession.save();
          AppEventBus.emit('points_updated');
        }

        final notifRes = await http.get(
          Uri.parse('${UserSession.getBaseUrl()}/api/notifications'),
          headers: {
            'Accept': 'application/json',
            'Authorization': 'Bearer ${UserSession.token}',
          },
        );
        final notifData = jsonDecode(notifRes.body);
        if (notifRes.statusCode == 200 && notifData['status'] == 'success') {
          int newUnread = notifData['unread_count'] ?? 0;

          if (newUnread > UserSession.unreadNotificationsCount) {
            final notifs = notifData['notifications'] as List?;
            if (notifs != null && notifs.isNotEmpty) {
              final latest = notifs.first;
              NotificationService.showNotification(
                id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
                title: latest['title'] ?? 'New Notification',
                body: latest['body'] ?? '',
              );
            }
          }

          if (UserSession.unreadNotificationsCount != newUnread) {
            if (isBackground) {
              UserSession.unreadNotificationsCount = newUnread;
            } else {
              setState(() {
                UserSession.unreadNotificationsCount = newUnread;
              });
            }
            if (newUnread > 0) {
              FlutterAppBadger.updateBadgeCount(newUnread);
            } else {
              FlutterAppBadger.removeBadge();
            }
          }
          AppEventBus.emit('notifications_updated');
        }
      } catch (e) {
        debugPrint('Failed to fetch global data: $e');
      }
    }
  }

  void _navigateToTravel(String mode) {
    setState(() {
      _travelMode = mode;
      _selectedIndex = 2;
    });
  }

  void switchTab(int index) {
    setState(() {
      _selectedIndex = index;
    });
  }

  void _handleLogout() async {
    // Unsubscribe from user-specific FCM topic before clearing session
    if (UserSession.isLoggedIn && UserSession.email.isNotEmpty) {
      await NotificationService.unsubscribeFromUserTopic(UserSession.email);
    }
    await UserSession.clear();
    setState(() {
      UserSession.isLoggedIn = false;
      UserSession.isEmailVerified = false;
      UserSession.username = 'Traveler';
      UserSession.email = 'user@amigagracia.com';
      UserSession.token = '';
      UserSession.lookupToken = '';
      _activityKey = UniqueKey();
      _selectedIndex = 0; // Immediately navigate away from Transaction tab
    });
  }

  @override
  Widget build(BuildContext context) {
    Widget buildNavItem(
        int index, IconData iconOutlined, IconData iconActive, String label) {
      final isSelected = _selectedIndex == index;
      return InkWell(
        onTap: () => setState(() => _selectedIndex = index),
        customBorder: const CircleBorder(),
        child: Center(
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 4.0, vertical: 6.0),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(isSelected ? iconActive : iconOutlined,
                    color: isSelected ? kPink : kSlate400, size: 26),
                const SizedBox(height: 2),
                Text(label,
                    style: TextStyle(
                        color: isSelected ? kPink : kSlate400,
                        fontSize: 11,
                        fontWeight:
                            isSelected ? FontWeight.w600 : FontWeight.normal)),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      key: _scaffoldKey,
      resizeToAvoidBottomInset: false,
      drawer: AppDrawer(
          onLogout: _handleLogout, onProfileUpdated: () => setState(() {})),
      appBar: AppBar(
        leading: Stack(
          clipBehavior: Clip.none,
          children: [
            IconButton(
              icon: const Icon(Icons.menu, color: Colors.white),
              onPressed: () => _scaffoldKey.currentState?.openDrawer(),
            ),
            if (UserSession.isLoggedIn && UserSession.phone.trim().isEmpty)
              Positioned(
                right: 10,
                top: 10,
                child: Container(
                  width: 10,
                  height: 10,
                  decoration: const BoxDecoration(
                      color: Colors.red, shape: BoxShape.circle),
                ),
              ),
          ],
        ),
        title: Row(
          children: [
            ClipRRect(
              borderRadius: BorderRadius.circular(6),
              child: Image.asset(
                'assets/icon/app_icon.png',
                height: 32,
                width: 32,
                fit: BoxFit.contain,
              ),
            ),
            const SizedBox(width: 10),
            const Text(
              'AMIGA GRACIA',
              style: TextStyle(
                  fontWeight: FontWeight.w900,
                  fontSize: 14,
                  letterSpacing: 1.2),
            ),
          ],
        ),
        actions: [
          if (UserSession.isLoggedIn) ...[
            Padding(
              padding: const EdgeInsets.only(right: 8.0),
              child: GestureDetector(
                onTap: () {
                  Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (_) => const NotificationsScreen()))
                      .then((_) => setState(() {}));
                },
                child: ValueListenableBuilder<int>(
                  valueListenable: UserSession.unreadNotificationsNotifier,
                  builder: (context, count, child) {
                    return Stack(
                      alignment: Alignment.center,
                      children: [
                        const Icon(Icons.notifications_outlined,
                            color: Colors.white, size: 24),
                        if (count > 0)
                          Positioned(
                            right: -2,
                            top: -2,
                            child: Container(
                              padding: const EdgeInsets.all(2),
                              decoration: const BoxDecoration(
                                  color: Colors.red, shape: BoxShape.circle),
                              constraints: const BoxConstraints(
                                  minWidth: 14, minHeight: 14),
                              child: Text(
                                '$count',
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold),
                                textAlign: TextAlign.center,
                              ),
                            ),
                          ),
                      ],
                    );
                  },
                ),
              ),
            ),
            Padding(
              padding: const EdgeInsets.only(right: 16.0),
              child: GestureDetector(
                onTap: () {
                  Navigator.push(
                      context,
                      MaterialPageRoute(
                          builder: (_) => const GraciaPointsScreen()));
                },
                child: Row(
                  children: [
                    const Icon(Icons.star_rounded, color: kPink, size: 20),
                    const SizedBox(width: 6),
                    Text('${UserSession.graciaPoints} pts',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            color: kPink)),
                  ],
                ),
              ),
            ),
          ]
        ],
      ),
      body: IndexedStack(
        index: _selectedIndex,
        children: [
          HomeScreen(
            onBookFerry: () => _navigateToTravel('ferry'),
            onBookAirline: () => _navigateToTravel('airline'),
            onTrackBooking: () => setState(() => _selectedIndex = 4),
          ),
          const SchedulesScreen(),
          TravelScreen(initialMode: _travelMode),
          VouchersScreen(
              onUseVoucher: () => setState(() => _selectedIndex = 2)),
          ActivityScreen(
              key: _activityKey, onLoginSuccess: () => setState(() {})),
        ],
      ),
      floatingActionButtonLocation:
          const _RaisedCenterDockedFabLocation(riseAboveNotch: -8),
      floatingActionButton: SizedBox(
        width: 60,
        height: 60,
        child: FloatingActionButton(
          onPressed: () => setState(() => _selectedIndex = 2),
          backgroundColor: kPink,
          elevation: 6,
          shape: const CircleBorder(),
          child: const Icon(Icons.explore, color: Colors.white, size: 28),
        ),
      ),
      bottomNavigationBar: BottomAppBar(
        shape: const CircularNotchedRectangle(),
        notchMargin: 8.0,
        color: Colors.white,
        elevation: 12,
        shadowColor: Colors.black26,
        child: SizedBox(
          height: 68,
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Expanded(
                  child:
                      buildNavItem(0, Icons.home_outlined, Icons.home, 'Home')),
              Expanded(
                  child: buildNavItem(1, Icons.calendar_month_outlined,
                      Icons.calendar_month, 'Schedules')),
              const SizedBox(width: 72), // Wider spacer to prevent FAB overlap
              Expanded(
                  child: buildNavItem(3, Icons.local_activity_outlined,
                      Icons.local_activity, 'Vouchers')),
              Expanded(
                  child: buildNavItem(4, Icons.receipt_long_outlined,
                      Icons.receipt_long, 'Booking')),
            ],
          ),
        ),
      ),
    );
  }
}

// ── Custom FAB Location: sits flush inside the bar notch ─────────────────────
/// Behaves like [FloatingActionButtonLocation.centerDocked] but lets you
/// nudge the button up/down by [riseAboveNotch] pixels.
/// riseAboveNotch: 6  → circle sits nearly flush with the bar surface.
class _RaisedCenterDockedFabLocation extends FloatingActionButtonLocation {
  const _RaisedCenterDockedFabLocation({this.riseAboveNotch = 6});
  final double riseAboveNotch;

  @override
  Offset getOffset(ScaffoldPrelayoutGeometry scaffoldGeometry) {
    final Offset base =
        FloatingActionButtonLocation.centerDocked.getOffset(scaffoldGeometry);
    return Offset(base.dx, base.dy - riseAboveNotch);
  }
}

// ==========================================
// 1. HOME SCREEN
// ==========================================
class HomeScreen extends StatefulWidget {
  final VoidCallback onBookFerry;
  final VoidCallback onBookAirline;
  final VoidCallback onTrackBooking;

  const HomeScreen({
    super.key,
    required this.onBookFerry,
    required this.onBookAirline,
    required this.onTrackBooking,
  });

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen>
    with SingleTickerProviderStateMixin {
  List<dynamic> _promotions = [];
  late TabController _tourTabController;
  final PageController _promoPageController = PageController();
  int _currentPromoPage = 0;
  bool _promoLoading = true;

  List<Map<String, dynamic>> _domesticPackages = [];
  List<Map<String, dynamic>> _internationalPackages = [];
  List<Map<String, dynamic>> _services = [];
  bool _toursLoading = true;
  bool _servicesLoading = true;
  BookingData? _savedSession;
  Timer? _carouselTimer;

  @override
  void initState() {
    super.initState();
    _tourTabController = TabController(length: 2, vsync: this);
    _checkSavedSession();
    _fetchPromotions();
    _fetchTours();
    _fetchServices();

    _carouselTimer = Timer.periodic(const Duration(seconds: 11), (timer) {
      if (_promoPageController.hasClients) {
        final nextPage = (_currentPromoPage + 1) % (2 + _promotions.length);
        _promoPageController.animateToPage(nextPage,
            duration: const Duration(milliseconds: 500),
            curve: Curves.easeInOut);
      }
    });
  }

  void _checkSavedSession() async {
    final session = await BookingData.loadFromPrefs();
    if (mounted) setState(() => _savedSession = session);
  }

  void _resumeBooking() {
    if (_savedSession == null) return;
    BookingData.activeSession = _savedSession;

    PageRouteBuilder buildRoute(Widget screen, bool animate) {
      return PageRouteBuilder(
        pageBuilder: (context, animation, secondaryAnimation) => screen,
        transitionDuration:
            animate ? const Duration(milliseconds: 300) : Duration.zero,
        reverseTransitionDuration:
            animate ? const Duration(milliseconds: 300) : Duration.zero,
        transitionsBuilder: animate
            ? (context, animation, secondaryAnimation, child) {
                const begin = Offset(1.0, 0.0);
                const end = Offset.zero;
                const curve = Curves.ease;
                var tween = Tween(begin: begin, end: end)
                    .chain(CurveTween(curve: curve));
                return SlideTransition(
                    position: animation.drive(tween), child: child);
              }
            : (context, animation, secondaryAnimation, child) => child,
      );
    }

    if (_savedSession!.savedStep >= 0) {
      Navigator.push(
          context,
          buildRoute(TravelScreen(initialMode: _savedSession!.mode),
              _savedSession!.savedStep == 0));
    }
    if (_savedSession!.savedStep >= 1) {
      Navigator.push(
          context,
          buildRoute(ScheduleSelectScreen(booking: _savedSession!),
              _savedSession!.savedStep == 1));
    }
    if (_savedSession!.savedStep >= 2) {
      Navigator.push(
          context,
          buildRoute(DiscountScreen(booking: _savedSession!),
              _savedSession!.savedStep == 2));
    }
    if (_savedSession!.savedStep >= 3) {
      Navigator.push(
          context,
          buildRoute(StayScreen(booking: _savedSession!),
              _savedSession!.savedStep == 3));
    }
    if (_savedSession!.savedStep >= 4) {
      Navigator.push(
          context,
          buildRoute(BookingSubmitScreen(booking: _savedSession!),
              _savedSession!.savedStep == 4));
    }
  }

  void _cancelDraft() async {
    await BookingData.clearPrefs();
    if (mounted) setState(() => _savedSession = null);
  }

  void _fetchTours() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/tours'));
      if (res.statusCode == 200) {
        final List<dynamic> data = jsonDecode(res.body);
        final domestic = <Map<String, dynamic>>[];
        final intl = <Map<String, dynamic>>[];

        for (var t in data) {
          final isDomestic = t['country'] != null &&
              t['country'].toString().toLowerCase().contains('philippines');
          final formatted = {
            'name': t['tour_name'] ?? t['package_name'] ?? 'Tour',
            'desc': t['destinations'] ?? '${t['duration'] ?? ''}',
            'price': t['price_per_pax'] != null
                ? '₱${t['price_per_pax']}'
                : 'Contact us',
            'tag': t['country'] ?? 'Tour',
            'tagColor': isDomestic ? kGreen : kPink,
            'gradient': isDomestic
                ? [kGreen, const Color(0xFF0e2709)]
                : [kPink, const Color(0xFF880E4F)],
            'image': t['image'],
          };
          if (isDomestic) {
            domestic.add(formatted);
          } else {
            intl.add(formatted);
          }
        }

        if (mounted) {
          setState(() {
            _domesticPackages = domestic;
            _internationalPackages = intl;
          });
        }
      }
    } catch (e) {
      debugPrint('Fetch tours error: $e');
    } finally {
      if (mounted) setState(() => _toursLoading = false);
    }
  }

  void _fetchServices() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/services'));
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success' && data['services'] != null) {
          final List<dynamic> srvs = data['services'];
          final parsed = srvs.map((s) {
            IconData icon = Icons.check_circle_outline;
            Color color = kGreen;
            final t = (s['title'] ?? '').toString().toLowerCase();
            if (t.contains('2go')) {
              icon = Icons.directions_boat;
              color = kPink;
            } else if (t.contains('starlite') || t.contains('supercat')) {
              icon = Icons.sailing;
              color = kGreen;
            } else if (t.contains('air')) {
              icon = Icons.flight;
              color = const Color(0xFF1565C0);
            } else if (t.contains('tour')) {
              icon = Icons.landscape;
              color = const Color(0xFF7B1FA2);
            } else if (t.contains('training') || t.contains('school')) {
              icon = Icons.school;
              color = const Color(0xFFF57C00);
            } else if (t.contains('group')) {
              icon = Icons.groups;
              color = const Color(0xFF00897B);
            }

            return {
              'title': s['title'] ?? 'Service',
              'desc': s['description'] ?? '',
              'icon': icon,
              'color': color,
            };
          }).toList();

          if (mounted) setState(() => _services = parsed);
        }
      }
    } catch (e) {
      debugPrint('Fetch services error: $e');
    } finally {
      if (mounted) setState(() => _servicesLoading = false);
    }
  }

  @override
  void dispose() {
    _carouselTimer?.cancel();
    _tourTabController.dispose();
    _promoPageController.dispose();
    super.dispose();
  }

  void _fetchPromotions() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/promotions'));
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success') {
          setState(() => _promotions = data['promotions']);
        }
      }
    } catch (_) {
    } finally {
      setState(() => _promoLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // ── 1. Immersive Carousel (edge-to-edge, taller) ──────────────────
          SizedBox(
            height: 220,
            child: PageView.builder(
              controller: _promoPageController,
              onPageChanged: (i) => setState(() => _currentPromoPage = i),
              itemCount: 2 + _promotions.length,
              itemBuilder: (context, i) {
                if (i == 0) {
                  return const _WelcomeBanner();
                } else if (i == 1) {
                  return const _HeroVideoBanner();
                } else {
                  final promo = _promotions[i - 2];
                  final imgUrl = promo['image_url'] as String?;
                  return Container(
                    margin: EdgeInsets.zero,
                    decoration: const BoxDecoration(
                      color: kSlate100,
                    ),
                    clipBehavior: Clip.antiAlias,
                    child: imgUrl != null
                        ? Image.network(imgUrl,
                            fit: BoxFit.cover,
                            errorBuilder: (_, __, ___) => const Center(
                                child: Icon(Icons.broken_image,
                                    color: kSlate400, size: 40)))
                        : const Center(
                            child:
                                Icon(Icons.image, color: kSlate400, size: 40)),
                  );
                }
              },
            ),
          ),
          const SizedBox(height: 12),
          // Dot indicators (green accent)
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(
              2 + _promotions.length,
              (index) => AnimatedContainer(
                duration: const Duration(milliseconds: 300),
                margin: const EdgeInsets.symmetric(horizontal: 3),
                height: 6,
                width: _currentPromoPage == index ? 22 : 6,
                decoration: BoxDecoration(
                  color: _currentPromoPage == index ? kGreen : kSlate300,
                  borderRadius: BorderRadius.circular(3),
                ),
              ),
            ),
          ),

          const SizedBox(height: 24),

          // ── 2. Track Booking ──────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Container(
              padding: const EdgeInsets.fromLTRB(16, 14, 8, 14),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(18),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.06),
                    blurRadius: 14,
                    offset: const Offset(0, 5),
                  )
                ],
              ),
              child: Row(
                children: [
                  Container(
                    padding: const EdgeInsets.all(10),
                    decoration: BoxDecoration(
                      color: kGreen.withOpacity(0.10),
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.my_location_rounded,
                        color: kGreen, size: 22),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: TextField(
                      decoration: const InputDecoration(
                        hintText: 'Enter booking / tracking number',
                        hintStyle: TextStyle(color: kSlate400, fontSize: 13),
                        border: InputBorder.none,
                        isDense: true,
                      ),
                      onSubmitted: (_) => widget.onTrackBooking(),
                    ),
                  ),
                  GestureDetector(
                    onTap: widget.onTrackBooking,
                    child: Container(
                      margin: const EdgeInsets.only(left: 8),
                      padding: const EdgeInsets.all(10),
                      decoration: BoxDecoration(
                        color: kGreen,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.arrow_forward_rounded,
                          color: Colors.white, size: 18),
                    ),
                  ),
                ],
              ),
            ),
          ),

          const SizedBox(height: 20),

          // ── 3. Quick-Book Grid ────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: _ModernBookCard(
                    label: 'Book Ferry',
                    subtitle: 'Starlite · 2GO · FastCat',
                    icon: Icons.directions_boat_rounded,
                    gradient: const LinearGradient(
                      colors: [kGreen, Color(0xFF1B5E20)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    onTap: widget.onBookFerry,
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: _ModernBookCard(
                    label: 'Book Airline',
                    subtitle: 'PAL · CebuPac · AirAsia',
                    icon: Icons.flight_takeoff_rounded,
                    gradient: const LinearGradient(
                      colors: [kPink, Color(0xFF880E4F)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    onTap: widget.onBookAirline,
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // ── 4. Points & Vouchers (gradient cards) ─────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Row(
              children: [
                Expanded(
                  child: GestureDetector(
                    onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => const GraciaPointsScreen())),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          vertical: 20, horizontal: 16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [kPink, Color(0xFFC2185B)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                              color: kPink.withOpacity(0.30),
                              blurRadius: 10,
                              offset: const Offset(0, 4))
                        ],
                      ),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.stars_rounded,
                              color: Colors.white, size: 30),
                          SizedBox(height: 10),
                          Text('My Points',
                              style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15)),
                          SizedBox(height: 2),
                          Text('View rewards',
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: GestureDetector(
                    onTap: () => Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => VouchersScreen(onUseVoucher: () {
                                  Navigator.pop(context);
                                  widget.onBookFerry();
                                }))),
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                          vertical: 20, horizontal: 16),
                      decoration: BoxDecoration(
                        gradient: const LinearGradient(
                          colors: [kGreen, Color(0xFF1B5E20)],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(20),
                        boxShadow: [
                          BoxShadow(
                              color: kGreen.withOpacity(0.30),
                              blurRadius: 10,
                              offset: const Offset(0, 4))
                        ],
                      ),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Icon(Icons.local_activity_rounded,
                              color: Colors.white, size: 30),
                          SizedBox(height: 10),
                          Text('Vouchers',
                              style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 15)),
                          SizedBox(height: 2),
                          Text('Claim promos',
                              style: TextStyle(
                                  color: Colors.white70, fontSize: 12)),
                        ],
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 20),

          // ── 5. Request Travel Booking (dark banner) ───────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: GestureDetector(
              onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                      builder: (_) => const RequestBookingScreen())),
              child: Container(
                width: double.infinity,
                padding: const EdgeInsets.all(18),
                decoration: BoxDecoration(
                  color: const Color(0xFF0D1B2A),
                  borderRadius: BorderRadius.circular(20),
                  boxShadow: [
                    BoxShadow(
                        color: Colors.black.withOpacity(0.25),
                        blurRadius: 14,
                        offset: const Offset(0, 6))
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.10),
                        borderRadius: BorderRadius.circular(14),
                      ),
                      child: const Icon(Icons.map_rounded,
                          color: Colors.white, size: 28),
                    ),
                    const SizedBox(width: 14),
                    const Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('Custom Travel Package',
                              style: TextStyle(
                                  color: Colors.white,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 15)),
                          SizedBox(height: 4),
                          Text('Request a tailor-made booking',
                              style: TextStyle(
                                  color: Colors.white60, fontSize: 12)),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_right_rounded,
                        color: Colors.white38),
                  ],
                ),
              ),
            ),
          ),

          const SizedBox(height: 24),

          // ── 6. Our Services ───────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Our Services',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: kSlate800)),
                    TextButton(
                      onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (_) => const ServicesScreen())),
                      child: const Text('See all →',
                          style: TextStyle(
                              color: kPink,
                              fontSize: 12,
                              fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                SizedBox(
                  height: 130,
                  child: _servicesLoading
                      ? const Center(
                          child: CircularProgressIndicator(color: kGreen))
                      : _services.isEmpty
                          ? const Center(
                              child: Text('No services configured yet.',
                                  style: TextStyle(
                                      color: kSlate400, fontSize: 13)),
                            )
                          : ListView.builder(
                              scrollDirection: Axis.horizontal,
                              itemCount: _services.length,
                              itemBuilder: (context, i) {
                                final s = _services[i];
                                return GestureDetector(
                                  onTap: () => Navigator.push(
                                      context,
                                      MaterialPageRoute(
                                          builder: (_) =>
                                              const ServicesScreen())),
                                  child: Container(
                                    width: 110,
                                    margin: const EdgeInsets.only(right: 12),
                                    padding: const EdgeInsets.all(12),
                                    decoration: BoxDecoration(
                                      color: Colors.white,
                                      borderRadius: BorderRadius.circular(16),
                                      border: Border.all(color: kSlate200),
                                      boxShadow: [
                                        BoxShadow(
                                            color: Colors.black
                                                .withOpacity(0.04),
                                            blurRadius: 6,
                                            offset: const Offset(0, 2))
                                      ],
                                    ),
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.center,
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Container(
                                          padding: const EdgeInsets.all(8),
                                          decoration: BoxDecoration(
                                            color: (s['color'] as Color)
                                                .withOpacity(0.1),
                                            borderRadius:
                                                BorderRadius.circular(10),
                                          ),
                                          child: Icon(s['icon'] as IconData,
                                              color: s['color'] as Color,
                                              size: 20),
                                        ),
                                        const SizedBox(height: 8),
                                        Text(s['title'] as String,
                                            style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 11,
                                                color: kSlate800),
                                            maxLines: 2,
                                            overflow: TextOverflow.ellipsis,
                                            textAlign: TextAlign.center),
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                ),
              ],
            ),
          ),

          const SizedBox(height: 24),

          // ── 7. Tour Packages ──────────────────────────────────────────────
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Tour Packages',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: kSlate800)),
                    TextButton(
                      onPressed: () => Navigator.push(
                          context,
                          MaterialPageRoute(
                              builder: (_) => const TourPackagesScreen())),
                      child: const Text('See all →',
                          style: TextStyle(
                              color: kPink,
                              fontSize: 12,
                              fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                if (_toursLoading)
                  const SizedBox(
                      height: 150,
                      child: Center(
                          child: CircularProgressIndicator(color: kGreen)))
                else if (_domesticPackages.isEmpty &&
                    _internationalPackages.isEmpty)
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.symmetric(vertical: 32),
                    decoration: BoxDecoration(
                        color: kSlate100,
                        borderRadius: BorderRadius.circular(12)),
                    child: const Center(
                      child: Text('Coming Soon',
                          style: TextStyle(
                              color: kSlate500,
                              fontWeight: FontWeight.bold,
                              fontSize: 16)),
                    ),
                  )
                else
                  Column(
                    children: [
                      Container(
                        decoration: BoxDecoration(
                          color: kSlate100,
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: TabBar(
                          controller: _tourTabController,
                          indicatorColor: kGreen,
                          labelColor: kGreen,
                          unselectedLabelColor: kSlate500,
                          indicatorSize: TabBarIndicatorSize.tab,
                          labelStyle: const TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 12),
                          tabs: const [
                            Tab(text: 'Domestic'),
                            Tab(text: 'International')
                          ],
                        ),
                      ),
                      const SizedBox(height: 12),
                      SizedBox(
                        height: 200,
                        child: TabBarView(
                          controller: _tourTabController,
                          children: [
                            _PackageHorizontalList(packages: _domesticPackages),
                            _PackageHorizontalList(
                                packages: _internationalPackages),
                          ],
                        ),
                      ),
                    ],
                  ),
              ],
            ),
          ),
          const SizedBox(height: 32),
          _buildWhyTravelSection(),
          const SizedBox(height: 32),
        ],
      ),
    );
  }

  Widget _buildWhyTravelSection() {
    final features = [
      {
        'title': 'Simplify Your Booking',
        'desc':
            'Feel the flexibility and simplicity with instant online e-ticketing.',
        'icon': Icons.touch_app,
        'color': Colors.red,
      },
      {
        'title': 'Wide Selection',
        'desc':
            'Enjoy memorable journeys with our ferry, airline, and tour partners.',
        'icon': Icons.explore,
        'color': Colors.pink,
      },
      {
        'title': 'Exclusive Deals',
        'desc':
            'Access daily promotions, special group packages, and competitive fares.',
        'icon': Icons.local_offer,
        'color': Colors.redAccent,
      },
      {
        'title': 'Trusted Expert',
        'desc':
            'Fulfilling countless travelers\' needs since 2017 with credible partners.',
        'icon': Icons.verified,
        'color': Colors.green,
      },
      {
        'title': 'Affectionate Support',
        'desc':
            'Our dedicated customer support is ready to help you with every journey.',
        'icon': Icons.support_agent,
        'color': Colors.blue,
      },
      {
        'title': 'Seamless Payment',
        'desc': 'Stress-free booking with convenient local payment options.',
        'icon': Icons.payment,
        'color': Colors.orange,
      },
    ];

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.center,
        children: [
          const Text('Why travel with Amiga Gracia?',
              style: TextStyle(
                  fontWeight: FontWeight.w900, fontSize: 18, color: kSlate800)),
          const SizedBox(height: 16),
          SizedBox(
            height: 180,
            child: ListView.builder(
              scrollDirection: Axis.horizontal,
              clipBehavior: Clip.none,
              itemCount: features.length,
              itemBuilder: (context, i) {
                final f = features[i];
                final color = f['color'] as Color;
                return Container(
                  width: 150,
                  margin: const EdgeInsets.only(right: 16),
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(20),
                    boxShadow: [
                      BoxShadow(
                        color: Colors.black.withOpacity(0.04),
                        blurRadius: 10,
                        offset: const Offset(0, 4),
                      )
                    ],
                    border: Border.all(color: color.withOpacity(0.1)),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                          color: color.withOpacity(0.1),
                          shape: BoxShape.circle,
                        ),
                        child:
                            Icon(f['icon'] as IconData, color: color, size: 28),
                      ),
                      const SizedBox(height: 12),
                      Text(f['title'] as String,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 12,
                              color: kSlate800),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis),
                      const SizedBox(height: 6),
                      Text(f['desc'] as String,
                          textAlign: TextAlign.center,
                          style: const TextStyle(
                              fontSize: 10, color: kSlate500, height: 1.2),
                          maxLines: 3,
                          overflow: TextOverflow.ellipsis),
                    ],
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _PackageHorizontalList extends StatelessWidget {
  final List<Map<String, dynamic>> packages;
  const _PackageHorizontalList({required this.packages});

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      scrollDirection: Axis.horizontal,
      itemCount: packages.length,
      itemBuilder: (context, i) {
        final p = packages[i];
        final gradient = p['gradient'] as List<Color>;
        return GestureDetector(
          onTap: () => Navigator.push(context,
              MaterialPageRoute(builder: (_) => const TourPackagesScreen())),
          child: Container(
            width: 170,
            margin: const EdgeInsets.only(right: 12),
            decoration: BoxDecoration(
              borderRadius: BorderRadius.circular(18),
              gradient: LinearGradient(
                  colors: gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight),
              boxShadow: [
                BoxShadow(
                    color: gradient.first.withOpacity(0.4),
                    blurRadius: 10,
                    offset: const Offset(0, 4))
              ],
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                    decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.2),
                        borderRadius: BorderRadius.circular(10)),
                    child: Text(p['tag'] as String,
                        style: const TextStyle(
                            color: Colors.white,
                            fontSize: 9,
                            fontWeight: FontWeight.bold)),
                  ),
                  const Spacer(),
                  Text(p['name'] as String,
                      style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 13),
                      maxLines: 2),
                  const SizedBox(height: 4),
                  Text(p['desc'] as String,
                      style:
                          const TextStyle(color: Colors.white70, fontSize: 10),
                      maxLines: 2),
                  const SizedBox(height: 8),
                  Text(p['price'] as String,
                      style: const TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.w900,
                          fontSize: 15)),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

// ── Modern gradient book card used in HomeScreen quick-book grid ──────────
class _ModernBookCard extends StatelessWidget {
  final String label;
  final String subtitle;
  final IconData icon;
  final LinearGradient gradient;
  final VoidCallback onTap;

  const _ModernBookCard({
    required this.label,
    required this.subtitle,
    required this.icon,
    required this.gradient,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(18),
        decoration: BoxDecoration(
          gradient: gradient,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
              color: gradient.colors.first.withOpacity(0.30),
              blurRadius: 12,
              offset: const Offset(0, 5),
            )
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.all(10),
              decoration: BoxDecoration(
                color: Colors.white.withOpacity(0.15),
                borderRadius: BorderRadius.circular(12),
              ),
              child: Icon(icon, color: Colors.white, size: 24),
            ),
            const SizedBox(height: 14),
            Text(label,
                style: const TextStyle(
                    color: Colors.white,
                    fontWeight: FontWeight.bold,
                    fontSize: 15)),
            const SizedBox(height: 4),
            Text(subtitle,
                style: const TextStyle(color: Colors.white70, fontSize: 11),
                maxLines: 1,
                overflow: TextOverflow.ellipsis),
          ],
        ),
      ),
    );
  }
}

class _ServiceCard extends StatelessWidget {
  final String label;
  final String subtitle;
  final IconData icon;
  final Color iconBg;
  final Color iconColor;
  final VoidCallback onTap;

  const _ServiceCard({
    required this.label,
    required this.subtitle,
    required this.icon,
    required this.iconBg,
    required this.iconColor,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 20, horizontal: 18),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          boxShadow: [
            BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 12,
                offset: const Offset(0, 4))
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                  color: iconBg, borderRadius: BorderRadius.circular(16)),
              child: Icon(icon, color: iconColor, size: 28),
            ),
            const SizedBox(width: 16),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                mainAxisAlignment: MainAxisAlignment.center,
                children: [
                  Text(label,
                      style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 16,
                          color: kSlate800)),
                  const SizedBox(height: 4),
                  Text(subtitle,
                      style: const TextStyle(
                          color: kSlate500, fontSize: 12, height: 1.2),
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis),
                ],
              ),
            ),
            const Icon(Icons.arrow_forward_ios, color: kSlate300, size: 16),
          ],
        ),
      ),
    );
  }
}

class _WelcomeBanner extends StatelessWidget {
  const _WelcomeBanner();

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.zero,
      decoration: const BoxDecoration(
        gradient: LinearGradient(
            colors: [kGreen, Color(0xFF0e2709)],
            begin: Alignment.topLeft,
            end: Alignment.bottomRight),
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        fit: StackFit.expand,
        children: [
          Positioned.fill(
            child: Opacity(
              opacity: 0.15,
              child: SvgPicture.network(
                '${UserSession.getBaseUrl()}/images/world-map.svg',
                fit: BoxFit.cover,
                colorFilter:
                    const ColorFilter.mode(Colors.white, BlendMode.srcIn),
              ),
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                const Text('Welcome to Amiga Gracia\nTravel Services',
                    style: TextStyle(
                        color: Colors.white,
                        fontSize: 22,
                        fontWeight: FontWeight.w900,
                        height: 1.2)),
                const SizedBox(height: 14),
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                      color: kPink, borderRadius: BorderRadius.circular(20)),
                  child: const Text(
                      'Your journey deserves more than a destination - it deserves an exceptional experience',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold)),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _HeroVideoBanner extends StatefulWidget {
  const _HeroVideoBanner();

  @override
  __HeroVideoBannerState createState() => __HeroVideoBannerState();
}

class __HeroVideoBannerState extends State<_HeroVideoBanner> {
  VideoPlayerController? _controller;
  bool _initialized = false;

  @override
  void initState() {
    super.initState();
    _controller = VideoPlayerController.networkUrl(Uri.parse(
        '${UserSession.getBaseUrl()}/video/Concept_A_smooth_motion_graph.mp4'))
      ..initialize().then((_) {
        if (mounted) {
          setState(() {
            _initialized = true;
          });
          _controller?.setLooping(true);
          _controller?.setVolume(0.0);
          _controller?.play();
        }
      }).catchError((_) {});
  }

  @override
  void dispose() {
    _controller?.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.zero,
      decoration: const BoxDecoration(
        color: kGreen,
      ),
      clipBehavior: Clip.antiAlias,
      child: Stack(
        fit: StackFit.expand,
        children: [
          if (_initialized && _controller != null)
            FittedBox(
              fit: BoxFit.cover,
              child: SizedBox(
                width: _controller!.value.size.width,
                height: _controller!.value.size.height,
                child: VideoPlayer(_controller!),
              ),
            )
          else
            Container(
              decoration: const BoxDecoration(
                color: kSlate100,
              ),
            ),
        ],
      ),
    );
  }
}

// ==========================================
// 2. TRAVEL SCREEN (Step 1: Route & Passengers)
// ==========================================
class TravelScreen extends StatefulWidget {
  final String? initialMode;
  final String? initialOperator;
  final String? initialOrigin;
  final String? initialDestination;
  final String? initialDate;
  const TravelScreen({
    super.key,
    this.initialMode,
    this.initialOperator,
    this.initialOrigin,
    this.initialDestination,
    this.initialDate,
  });

  @override
  State<TravelScreen> createState() => _TravelScreenState();
}

class _TravelScreenState extends State<TravelScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tripTabController;
  String _mode = 'ferry';
  String? _origin;
  String? _destination;
  DateTime? _departureDate;
  DateTime? _returnDate;
  int _adults = 1;
  int _children = 0;
  int _minors = 0;
  int _infants = 0;
  bool _showPassengerDropdown = false;

  String? _operator;
  List<String> _operators = [];
  bool _loadingOperators = false;

  List<String> _origins = [];
  List<String> _destinations = [];
  List<String> _availableDepartureDates = [];
  List<String> _availableReturnDates = [];
  bool _loadingOrigins = false;
  bool _loadingDestinations = false;

  List<Map<String, dynamic>> _vehicleRates = [];
  final _plateCtrl = TextEditingController();
  final _driverFirstNameCtrl = TextEditingController();
  final _driverMiddleNameCtrl = TextEditingController();
  final _driverLastNameCtrl = TextEditingController();
  final _driverBirthdayCtrl = TextEditingController();
  bool _isVehicleBookingEnabled = false;

  @override
  void initState() {
    super.initState();
    _tripTabController = TabController(length: 2, vsync: this);
    _tripTabController.addListener(() {
      if (_tripTabController.indexIsChanging) return;
      setState(() {});
      if (_origin != null) {
        if (_tripTabController.index == 1 && _destination != null) {
          _verifyBidirectionalAndClearIfNeeded();
        } else {
          _fetchDestinations(_origin!);
        }
      }
    });
    _mode = widget.initialMode ?? 'ferry';
    _operator = widget.initialOperator;
    _origin = widget.initialOrigin;
    _destination = widget.initialDestination;
    if (widget.initialDate != null) {
      _departureDate = DateTime.tryParse(widget.initialDate!);
    }
    _fetchOperators(preserveSelections: true);
    _fetchOrigins(preserveSelections: true);
    if (_origin != null) {
      _fetchDestinations(_origin!);
    }
    if (_origin != null && _destination != null) {
      _fetchAvailableDates();
    }
    _fetchVehicleRates();
  }

  @override
  void didUpdateWidget(TravelScreen oldWidget) {
    super.didUpdateWidget(oldWidget);
    if (widget.initialMode != null &&
        widget.initialMode != oldWidget.initialMode &&
        widget.initialMode != _mode) {
      setState(() {
        _mode = widget.initialMode!;
        _operator = null;
        _operators = [];
        _origin = null;
        _destination = null;
        _origins = [];
        _destinations = [];
        _availableDepartureDates = [];
        _availableReturnDates = [];
      });
      _fetchOperators();
    }
  }

  @override
  void dispose() {
    _tripTabController.dispose();
    _plateCtrl.dispose();
    _driverFirstNameCtrl.dispose();
    _driverMiddleNameCtrl.dispose();
    _driverLastNameCtrl.dispose();
    _driverBirthdayCtrl.dispose();
    super.dispose();
  }

  void _fetchVehicleRates() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/vehicle-rates'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        if (mounted) {
          setState(() => _vehicleRates =
              List<Map<String, dynamic>>.from(data['vehicle_rates']));
        }
      }
    } catch (_) {}
  }

  void _fetchOperators({bool preserveSelections = false}) async {
    setState(() => _loadingOperators = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res =
          await http.get(Uri.parse('$baseUrl/api/operators?mode=$_mode'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _operators = List<String>.from(data['operators']);
          if (!preserveSelections ||
              (_operator != null && !_operators.contains(_operator))) {
            _operator = null;
          }
        });
      }
    } catch (e) {
      debugPrint('Error fetching operators: $e');
    } finally {
      setState(() => _loadingOperators = false);
    }
  }

  void _fetchOrigins({bool preserveSelections = false}) async {
    setState(() => _loadingOrigins = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final operatorQuery = _operator != null
          ? '&operator=${Uri.encodeComponent(_operator!)}'
          : '';
      final res = await http
          .get(Uri.parse('$baseUrl/api/origins?mode=$_mode$operatorQuery'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _origins = List<String>.from(data['origins']);
          if (!preserveSelections ||
              (_origin != null && !_origins.contains(_origin))) {
            _origin = null;
            _destination = null;
            _destinations = [];
            _availableDepartureDates = [];
            _availableReturnDates = [];
          }
        });
      }
    } catch (e) {
      debugPrint('Error fetching origins: $e');
    } finally {
      setState(() => _loadingOrigins = false);
    }
  }

  void _fetchDestinations(String origin) async {
    setState(() => _loadingDestinations = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final operatorQuery = _operator != null
          ? '&operator=${Uri.encodeComponent(_operator!)}'
          : '';
      final tripType = _tripTabController.index == 1 ? 'round_trip' : 'one_way';
      final res = await http.get(Uri.parse(
          '$baseUrl/api/destinations?origin=${Uri.encodeComponent(origin)}&mode=$_mode&trip_type=$tripType$operatorQuery'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _destinations = List<String>.from(data['destinations']);
          if (_destination != null && !_destinations.contains(_destination)) {
            _destination = null;
            _departureDate = null;
            _returnDate = null;
            _availableDepartureDates = [];
            _availableReturnDates = [];
          }
        });
      }
    } catch (e) {
      debugPrint('Error fetching destinations: $e');
    } finally {
      setState(() => _loadingDestinations = false);
    }
  }

  void _fetchAvailableDates() async {
    if (_origin == null || _destination == null) return;
    try {
      final baseUrl = UserSession.getBaseUrl();
      final operatorQuery = _operator != null
          ? '&operator=${Uri.encodeComponent(_operator!)}'
          : '';

      final res = await http.get(Uri.parse(
          '$baseUrl/api/available-dates?origin=${Uri.encodeComponent(_origin!)}&destination=${Uri.encodeComponent(_destination!)}&mode=$_mode$operatorQuery'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _availableDepartureDates = List<String>.from(data['available_dates']);
        });
      }

      final resRet = await http.get(Uri.parse(
          '$baseUrl/api/available-dates?origin=${Uri.encodeComponent(_destination!)}&destination=${Uri.encodeComponent(_origin!)}&mode=$_mode$operatorQuery'));
      final dataRet = jsonDecode(resRet.body);
      if (resRet.statusCode == 200 && dataRet['status'] == 'success') {
        setState(() {
          _availableReturnDates = List<String>.from(dataRet['available_dates']);
        });
      }
    } catch (e) {
      debugPrint('Error fetching available dates: $e');
    }
  }

  Future<void> _verifyBidirectionalAndClearIfNeeded() async {
    if (_origin == null || _destination == null) return;
    try {
      final baseUrl = UserSession.getBaseUrl();
      final operatorQuery = _operator != null
          ? '&operator=${Uri.encodeComponent(_operator!)}'
          : '';
      const tripType = 'round_trip';
      final res = await http.get(Uri.parse(
          '$baseUrl/api/destinations?origin=${Uri.encodeComponent(_origin!)}&mode=$_mode&trip_type=$tripType$operatorQuery'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        final filtered = List<String>.from(data['destinations']);
        if (!filtered.contains(_destination)) {
          setState(() {
            _destination = null;
            _returnDate = null;
            _departureDate = null;
            _destinations = filtered;
            _availableDepartureDates = [];
            _availableReturnDates = [];
          });
          if (mounted) {
            showTopSnack(
              context,
              const SnackBar(
                content: Text(
                    'No return schedule exists for this route in Round Trip mode. Please select a different destination.'),
                backgroundColor: Colors.red,
                duration: Duration(seconds: 4),
              ),
            );
          }
        }
      }
    } catch (e) {
      debugPrint('Error verifying bidirectional schedules: $e');
    }
  }

  Future<void> _selectDate(BuildContext context, bool isDeparture) async {
    final picked = await showDatePicker(
      context: context,
      initialDate: isDeparture ? _departureDate : _returnDate,
      firstDate: DateTime.now(),
      lastDate: DateTime.now().add(const Duration(days: 365)),
      selectableDayPredicate: (DateTime day) {
        if (_origin == null || _destination == null) return false;
        if (isDeparture) {
          if (_availableDepartureDates.isEmpty) return false;
          return _availableDepartureDates.contains(_fmt(day));
        } else {
          if (_availableReturnDates.isEmpty) return false;
          return _availableReturnDates.contains(_fmt(day));
        }
      },
      builder: (context, child) => Theme(
        data: Theme.of(context).copyWith(
            colorScheme:
                const ColorScheme.light(primary: kGreen, secondary: kPink)),
        child: child!,
      ),
    );
    if (picked != null) {
      setState(() {
        if (isDeparture) {
          _departureDate = picked;
        } else {
          _returnDate = picked;
        }
      });
    }
  }

  String _fmt(DateTime d) =>
      '${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}';
  String _fmtDisplay(DateTime d) {
    const months = [
      'Jan',
      'Feb',
      'Mar',
      'Apr',
      'May',
      'Jun',
      'Jul',
      'Aug',
      'Sep',
      'Oct',
      'Nov',
      'Dec'
    ];
    return '${months[d.month - 1]} ${d.day}, ${d.year}';
  }

  int get _totalPassengers =>
      _adults + _children + (_mode == 'airline' ? _minors + _infants : 0);

  void _goToSchedule() {
    if (!UserSession.isLoggedIn) {
      showDialog(
          context: context,
          builder: (_) => AlertDialog(
                title: const Text('Login Required'),
                content: const Text(
                    'You must be logged in to proceed with the booking.'),
                actions: [
                  TextButton(
                      onPressed: () => Navigator.pop(context),
                      child: const Text('Cancel')),
                  TextButton(
                      onPressed: () {
                        Navigator.pop(context);
                        showModalBottomSheet(
                          context: context,
                          isScrollControlled: true,
                          backgroundColor: Colors.white,
                          shape: const RoundedRectangleBorder(
                              borderRadius: BorderRadius.vertical(
                                  top: Radius.circular(20))),
                          builder: (modalCtx) => Padding(
                            padding: EdgeInsets.only(
                                bottom:
                                    MediaQuery.of(modalCtx).viewInsets.bottom),
                            child: SizedBox(
                              height:
                                  MediaQuery.of(modalCtx).size.height * 0.85,
                              child: ActivityScreen(onLoginSuccess: () {
                                Navigator.pop(
                                    modalCtx); // Close the ActivityScreen modal on success
                                _goToSchedule(); // Resume the booking flow
                              }),
                            ),
                          ),
                        );
                      },
                      child: const Text('Login')),
                ],
              ));
      return;
    }
    if (_origin == null || _destination == null || _departureDate == null) {
      return;
    }
    if (_tripTabController.index == 1 && _returnDate == null) return;
    final booking = BookingData()
      ..mode = _mode
      ..operator = _operator
      ..tripType = _tripTabController.index == 0 ? 'one_way' : 'round_trip'
      ..origin = _origin!
      ..destination = _destination!
      ..departureDate = _fmt(_departureDate!)
      ..returnDate = _tripTabController.index == 1 ? _fmt(_returnDate!) : null
      ..adults = _adults
      ..children = _children
      ..minors = _mode == 'airline' ? _minors : 0
      ..infants = _mode == 'airline' ? _infants : 0;

    final bool isStarlite = _mode == 'ferry' &&
        (_operator?.toLowerCase().contains('starlite') ?? false);
    if (_mode == 'ferry' && _isVehicleBookingEnabled && isStarlite) {
      if (_plateCtrl.text.trim().isEmpty ||
          _driverFirstNameCtrl.text.trim().isEmpty ||
          _driverLastNameCtrl.text.trim().isEmpty ||
          _driverBirthdayCtrl.text.trim().isEmpty) {
        showTopSnack(
          context,
          const SnackBar(
            content:
                Text('Please fill out all required vehicle booking fields.'),
            backgroundColor: Colors.red,
          ),
        );
        return;
      }
      booking.hasVehicle = true;
      booking.vehiclePlateNumber = _plateCtrl.text;
      booking.vehicleDriverFirstName = _driverFirstNameCtrl.text;
      booking.vehicleDriverMiddleName = _driverMiddleNameCtrl.text;
      booking.vehicleDriverLastName = _driverLastNameCtrl.text;
      booking.vehicleDriverBirthday = _driverBirthdayCtrl.text;
      final selected =
          _vehicleRates.where((r) => r['selected'] == true).toList();
      if (selected.isNotEmpty) {
        booking.selectedVehicleRateId = selected.first['id'];
        booking.vehicleType = selected.first['name'];
        booking.vehiclePrice =
            double.tryParse(selected.first['price'].toString()) ?? 0;
      }
    }

    BookingData.activeSession = booking;
    booking.savedStep = 1;
    booking.saveToPrefs(1);

    Navigator.push(
        context,
        MaterialPageRoute(
            builder: (_) => ScheduleSelectScreen(booking: booking))).then((_) {
      if (mounted) {
        booking.savedStep = 0;
        booking.saveToPrefs(0);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        // Mode selector (Ferry / Airline)
        Container(
          color: Colors.white,
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
          child: Row(
            children: [
              _ModeTab(
                  label: 'Ferry',
                  icon: Icons.directions_boat,
                  selected: _mode == 'ferry',
                  onTap: () {
                    if (_mode != 'ferry') {
                      setState(() {
                        _mode = 'ferry';
                        _operator = null;
                        _operators = [];
                        _origin = null;
                        _destination = null;
                        _origins = [];
                        _destinations = [];
                      });
                      _fetchOperators();
                    }
                  }),
              const SizedBox(width: 10),
              _ModeTab(
                  label: 'Airline',
                  icon: Icons.flight,
                  selected: _mode == 'airline',
                  onTap: () {
                    if (_mode != 'airline') {
                      setState(() {
                        _mode = 'airline';
                        _operator = null;
                        _operators = [];
                        _origin = null;
                        _destination = null;
                        _origins = [];
                        _destinations = [];
                      });
                      _fetchOperators();
                    }
                  }),
            ],
          ),
        ),

        // One-Way / Round Trip tabs
        Container(
          color: Colors.white,
          child: TabBar(
            controller: _tripTabController,
            indicatorColor: kPink,
            labelColor: kPink,
            unselectedLabelColor: kSlate600,
            indicatorWeight: 3,
            tabs: const [Tab(text: 'One-Way'), Tab(text: 'Round Trip')],
          ),
        ),

        Expanded(
          child: _loadingOrigins || _loadingOperators
              ? const Center(child: CircularProgressIndicator(color: kGreen))
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Card(
                    color: Colors.white,
                    elevation: 2,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(16)),
                    child: Padding(
                      padding: const EdgeInsets.all(16.0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          // Operator
                          _label('Operator'),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<String>(
                            value: _operators.contains(_operator)
                                ? _operator
                                : null,
                            hint: const Text('Choose an operator'),
                            items: _operators
                                .toSet()
                                .map((c) =>
                                    DropdownMenuItem(value: c, child: Text(c)))
                                .toList(),
                            onChanged: (val) {
                              setState(() {
                                _operator = val;
                                _origin = null;
                                _destination = null;
                                _destinations = [];
                                _origins = [];
                                _departureDate = null;
                                _returnDate = null;
                                _availableDepartureDates = [];
                                _availableReturnDates = [];
                              });
                              if (val != null) _fetchOrigins();
                            },
                            decoration: _dropDecor(Icons.business),
                          ),
                          const SizedBox(height: 16),
                          // Origin
                          _label('Origin'),
                          const SizedBox(height: 6),
                          DropdownButtonFormField<String>(
                            value:
                                _origins.contains(_origin) ? _origin : null,
                            hint: const Text('Select Origin'),
                            items: _origins
                                .toSet()
                                .map((c) =>
                                    DropdownMenuItem(value: c, child: Text(c)))
                                .toList(),
                            onChanged: _operator == null
                                ? null
                                : (v) {
                                    if (v != null) {
                                      setState(() {
                                        _origin = v;
                                        _destination = null;
                                        _destinations = [];
                                        _departureDate = null;
                                        _returnDate = null;
                                        _availableDepartureDates = [];
                                        _availableReturnDates = [];
                                      });
                                      _fetchDestinations(v);
                                    }
                                  },
                            decoration: _dropDecor(Icons.location_on),
                          ),
                          const SizedBox(height: 16),

                          // Destination
                          _label('Destination'),
                          const SizedBox(height: 6),
                          _loadingDestinations
                              ? const SizedBox(
                                  height: 52,
                                  child: Center(
                                      child: CircularProgressIndicator(
                                          color: kGreen)))
                              : DropdownButtonFormField<String>(
                                  value:
                                      _destinations.contains(_destination)
                                          ? _destination
                                          : null,
                                  hint: const Text('Select Destination'),
                                  items: _destinations
                                      .toSet()
                                      .map((c) => DropdownMenuItem(
                                          value: c, child: Text(c)))
                                      .toList(),
                                  onChanged: _origin == null
                                      ? null
                                      : (v) {
                                          setState(() {
                                            _destination = v;
                                            _departureDate = null;
                                            _returnDate = null;
                                            _availableDepartureDates = [];
                                            _availableReturnDates = [];
                                          });
                                          if (v != null) _fetchAvailableDates();
                                        },
                                  decoration: _dropDecor(Icons.navigation),
                                ),
                          const SizedBox(height: 16),

                          // Travel Dates
                          _label('Travel Dates'),
                          const SizedBox(height: 6),
                          _datePicker(
                              _departureDate != null
                                  ? _fmtDisplay(_departureDate!)
                                  : 'Select Date',
                              _destination == null
                                  ? null
                                  : () => _selectDate(context, true)),
                          if (_tripTabController.index == 1) ...[
                            const SizedBox(height: 10),
                            _datePicker(
                                _returnDate != null
                                    ? _fmtDisplay(_returnDate!)
                                    : 'Select Date',
                                _destination == null
                                    ? null
                                    : () => _selectDate(context, false),
                                label: 'Return'),
                          ],
                          const SizedBox(height: 16),

                          // Passenger Selector
                          _label('Passenger'),
                          const SizedBox(height: 6),
                          GestureDetector(
                            onTap: () => setState(() => _showPassengerDropdown =
                                !_showPassengerDropdown),
                            child: Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 14),
                              decoration: BoxDecoration(
                                border: Border.all(color: kSlate200),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Row(
                                mainAxisAlignment:
                                    MainAxisAlignment.spaceBetween,
                                children: [
                                  Row(children: [
                                    const Icon(Icons.people,
                                        color: kGreen, size: 20),
                                    const SizedBox(width: 8),
                                    Text(
                                      _mode == 'airline'
                                          ? '$_adults Adult${_adults > 1 ? 's' : ''}'
                                              '${_minors > 0 ? '  $_minors Minor${_minors > 1 ? 's' : ''}' : ''}'
                                              '${_children > 0 ? '  $_children Child${_children > 1 ? 'ren' : ''}' : ''}'
                                              '${_infants > 0 ? '  $_infants Infant${_infants > 1 ? 's' : ''}' : ''}'
                                          : '$_adults Adult${_adults > 1 ? 's' : ''}${_children > 0 ? '  $_children Minor${_children > 1 ? 's' : ''}' : ''}',
                                      style: const TextStyle(
                                          fontSize: 14, color: kSlate800),
                                    ),
                                  ]),
                                  Icon(
                                      _showPassengerDropdown
                                          ? Icons.expand_less
                                          : Icons.expand_more,
                                      color: kSlate400),
                                ],
                              ),
                            ),
                          ),
                          if (_showPassengerDropdown) ...[
                            Container(
                              margin: const EdgeInsets.only(top: 4),
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: kSlate200),
                                boxShadow: [
                                  BoxShadow(
                                      color:
                                          Colors.black.withOpacity(0.06),
                                      blurRadius: 10,
                                      offset: const Offset(0, 4))
                                ],
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Maximum 8 passengers per booking',
                                      style: TextStyle(
                                          color: kSlate500, fontSize: 12)),
                                  const SizedBox(height: 12),
                                  _PassengerCounter(
                                    label: 'Adult',
                                    subtitle: _mode == 'airline' ? '12 years and above' : '11 years and above',
                                    count: _adults,
                                    onIncrement: _totalPassengers < 8
                                        ? () => setState(() => _adults++)
                                        : null,
                                    onDecrement: _adults > 1
                                        ? () => setState(() => _adults--)
                                        : null,
                                  ),
                                  if (_mode != 'airline') ...[
                                    const Divider(height: 20),
                                    _PassengerCounter(
                                      label: 'Minor',
                                      subtitle: '2 - 11 years',
                                      count: _children,
                                      onIncrement: _totalPassengers < 8
                                          ? () {
                                              showDialog(
                                                context: context,
                                                builder: (c) => AlertDialog(
                                                  title: const Text(
                                                      'Minor age reminder',
                                                      style: TextStyle(
                                                          fontWeight:
                                                              FontWeight.bold)),
                                                  content: const Text(
                                                      '23 months and under will be issued upon arrival at the port/airport.',
                                                      style: TextStyle(
                                                          color: kSlate700)),
                                                  actions: [
                                                    TextButton(
                                                      onPressed: () {
                                                        Navigator.pop(c);
                                                        setState(
                                                            () => _children++);
                                                      },
                                                      child: const Text('Close',
                                                          style: TextStyle(
                                                              color: kPink)),
                                                    ),
                                                  ],
                                                ),
                                              );
                                            }
                                          : null,
                                      onDecrement: _children > 0
                                          ? () => setState(() => _children--)
                                          : null,
                                    ),
                                  ] else ...[
                                    const Divider(height: 20),
                                    _PassengerCounter(
                                      label: 'Minor',
                                      subtitle: '7 - 11 years',
                                      count: _minors,
                                      onIncrement: _totalPassengers < 8
                                          ? () => setState(() => _minors++)
                                          : null,
                                      onDecrement: _minors > 0
                                          ? () => setState(() => _minors--)
                                          : null,
                                    ),
                                    const Divider(height: 20),
                                    _PassengerCounter(
                                      label: 'Child',
                                      subtitle: '2 - 6 years',
                                      count: _children,
                                      onIncrement: _totalPassengers < 8
                                          ? () => setState(() => _children++)
                                          : null,
                                      onDecrement: _children > 0
                                          ? () => setState(() => _children--)
                                          : null,
                                    ),
                                    const Divider(height: 20),
                                    _PassengerCounter(
                                      label: 'Infant',
                                      subtitle: 'Under 2 years',
                                      count: _infants,
                                      onIncrement: _totalPassengers < 8 &&
                                              _infants < _adults
                                          ? () => setState(() => _infants++)
                                          : null,
                                      onDecrement: _infants > 0
                                          ? () => setState(() => _infants--)
                                          : null,
                                    ),
                                  ],
                                  const Divider(height: 20),
                                  GestureDetector(
                                    onTap: () {
                                      showDialog(
                                        context: context,
                                        builder: (ctx) => AlertDialog(
                                          title: const Text(
                                              'Passenger limits and guidance',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 16)),
                                          content: Text(
                                            _mode == 'airline'
                                                ? 'You can book up to 8 travelers total (Adults, Children, Minors, Infants).\n\n'
                                                    '1. Adults and minors count towards the 8-person total.\n\n'
                                                    '2. Infants under 2 years must be accompanied by an adult (max 1 infant per adult).\n\n'
                                                    '3. Use the buttons to update counts. The form prevents totals above 8.'
                                                : 'You can book up to 8 travelers total. This includes both adults and minors combined. Any discounts are applied per traveler on the next step.\n\n'
                                                    '1. Adults are counted separately from minors, but both count toward the same 8-person total.\n\n'
                                                    '2. Minors aged 2 to 11 are still part of the booking capacity limit.\n\n'
                                                    '3. Use the buttons to update counts. The form prevents totals above 8.',
                                            style: const TextStyle(
                                                fontSize: 13,
                                                color: kSlate600,
                                                height: 1.5),
                                          ),
                                          shape: RoundedRectangleBorder(
                                              borderRadius:
                                                  BorderRadius.circular(16)),
                                          actions: [
                                            TextButton(
                                              onPressed: () =>
                                                  Navigator.pop(ctx),
                                              child: const Text('Close',
                                                  style: TextStyle(
                                                      color: kPink,
                                                      fontWeight:
                                                          FontWeight.bold)),
                                            ),
                                          ],
                                        ),
                                      );
                                    },
                                    child: const Row(
                                      children: [
                                        Text('Info',
                                            style: TextStyle(
                                                color: kPink,
                                                fontWeight: FontWeight.bold,
                                                fontSize: 13)),
                                        SizedBox(width: 4),
                                        Icon(Icons.info_outline,
                                            color: kPink, size: 14),
                                      ],
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ],
                          const SizedBox(height: 24),

                          // Vehicle / Car Booking (Ferry only, Starlite only)
                          if (_mode == 'ferry' &&
                              (_operator?.toLowerCase().contains('starlite') ??
                                  false)) ...[
                            Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: Colors.white,
                                border: Border.all(color: kSlate200),
                                borderRadius: BorderRadius.circular(16),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      const Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text('Vehicle / Car Booking',
                                              style: TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 15,
                                                  color: kSlate800)),
                                          Text(
                                              'Bring your vehicle on the ferry',
                                              style: TextStyle(
                                                  color: kSlate500,
                                                  fontSize: 12)),
                                        ],
                                      ),
                                      Switch(
                                        value: _isVehicleBookingEnabled,
                                        activeColor: kGreen,
                                        onChanged: (val) {
                                          setState(() {
                                            _isVehicleBookingEnabled = val;
                                            if (!val) {
                                              _plateCtrl.clear();
                                              _driverFirstNameCtrl.clear();
                                              _driverMiddleNameCtrl.clear();
                                              _driverLastNameCtrl.clear();
                                              _driverBirthdayCtrl.clear();
                                              for (var r in _vehicleRates) {
                                                r['selected'] = false;
                                              }
                                            } else {
                                              if (_vehicleRates.isNotEmpty) {
                                                _vehicleRates
                                                    .first['selected'] = true;
                                              }
                                            }
                                          });
                                        },
                                      ),
                                    ],
                                  ),
                                  if (_isVehicleBookingEnabled) ...[
                                    const SizedBox(height: 16),
                                    Container(
                                      padding: const EdgeInsets.all(12),
                                      decoration: BoxDecoration(
                                        color: Colors.amber.shade50,
                                        border: Border.all(
                                            color: Colors.amber.shade200),
                                        borderRadius: BorderRadius.circular(10),
                                      ),
                                      child: const Row(
                                        children: [
                                          Icon(Icons.info_outline,
                                              color: Colors.amber, size: 18),
                                          SizedBox(width: 8),
                                          Expanded(
                                              child: Text(
                                                  'Vehicle bookings are subject to availability.',
                                                  style: TextStyle(
                                                      color: Colors.amber,
                                                      fontSize: 12))),
                                        ],
                                      ),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text('Vehicle Type',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    if (_vehicleRates.isNotEmpty)
                                      Column(
                                        children: _vehicleRates.map((rate) {
                                          final selected =
                                              rate['selected'] == true;
                                          return GestureDetector(
                                            onTap: () {
                                              setState(() {
                                                for (var r in _vehicleRates) {
                                                  r['selected'] = false;
                                                }
                                                rate['selected'] = true;
                                              });
                                            },
                                            child: Container(
                                              margin: const EdgeInsets.only(
                                                  bottom: 8),
                                              padding:
                                                  const EdgeInsets.symmetric(
                                                      horizontal: 14,
                                                      vertical: 12),
                                              decoration: BoxDecoration(
                                                color: selected
                                                    ? kGreen.withOpacity(0.05)
                                                    : kSlate50,
                                                border: Border.all(
                                                    color: selected
                                                        ? kGreen
                                                        : kSlate200,
                                                    width: selected ? 2 : 1),
                                                borderRadius:
                                                    BorderRadius.circular(12),
                                              ),
                                              child: Row(
                                                children: [
                                                  Icon(Icons.directions_car,
                                                      color: selected
                                                          ? kGreen
                                                          : kSlate400,
                                                      size: 20),
                                                  const SizedBox(width: 10),
                                                  Expanded(
                                                      child: Text(rate['name'],
                                                          style: TextStyle(
                                                              fontWeight:
                                                                  FontWeight
                                                                      .w600,
                                                              color: selected
                                                                  ? kGreen
                                                                  : kSlate800))),
                                                  Text('₱${rate['price']}',
                                                      style: TextStyle(
                                                          color: selected
                                                              ? kGreen
                                                              : kPink,
                                                          fontWeight:
                                                              FontWeight.bold)),
                                                ],
                                              ),
                                            ),
                                          );
                                        }).toList(),
                                      ),
                                    const SizedBox(height: 14),
                                    const Text('Plate Number',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: _plateCtrl,
                                      onChanged: (v) => setState(() {}),
                                      decoration: InputDecoration(
                                          hintText: 'e.g., ABC 1234',
                                          border: OutlineInputBorder(
                                              borderRadius:
                                                  BorderRadius.circular(12))),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text('Driver First Name',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: _driverFirstNameCtrl,
                                      onChanged: (v) => setState(() {}),
                                      decoration: InputDecoration(
                                          hintText: 'e.g., John',
                                          border: OutlineInputBorder(
                                              borderRadius:
                                                  BorderRadius.circular(12))),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text('Driver Middle Name (Optional)',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: _driverMiddleNameCtrl,
                                      onChanged: (v) => setState(() {}),
                                      decoration: InputDecoration(
                                          hintText: 'e.g., A',
                                          border: OutlineInputBorder(
                                              borderRadius:
                                                  BorderRadius.circular(12))),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text('Driver Last Name',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: _driverLastNameCtrl,
                                      onChanged: (v) => setState(() {}),
                                      decoration: InputDecoration(
                                          hintText: 'e.g., Doe',
                                          border: OutlineInputBorder(
                                              borderRadius:
                                                  BorderRadius.circular(12))),
                                    ),
                                    const SizedBox(height: 14),
                                    const Text('Driver Birthdate',
                                        style: TextStyle(
                                            fontWeight: FontWeight.w600,
                                            color: kSlate700,
                                            fontSize: 13)),
                                    const SizedBox(height: 8),
                                    TextField(
                                      controller: _driverBirthdayCtrl,
                                      readOnly: true,
                                      onTap: () async {
                                        final date = await showDatePicker(
                                            context: context,
                                            initialDate: DateTime.now()
                                                .subtract(const Duration(
                                                    days: 365 * 18)),
                                            firstDate: DateTime(1900),
                                            lastDate: DateTime.now(),
                                            builder: (context, child) {
                                              return Theme(
                                                data:
                                                    Theme.of(context).copyWith(
                                                  colorScheme:
                                                      const ColorScheme.light(
                                                          primary: kPink,
                                                          onPrimary:
                                                              Colors.white,
                                                          surface: Colors.white,
                                                          onSurface:
                                                              Colors.black),
                                                ),
                                                child: child!,
                                              );
                                            });
                                        if (date != null) {
                                          setState(() {
                                            _driverBirthdayCtrl.text =
                                                "${date.year}-${date.month.toString().padLeft(2, '0')}-${date.day.toString().padLeft(2, '0')}";
                                          });
                                        }
                                      },
                                      decoration: InputDecoration(
                                          hintText: 'YYYY-MM-DD',
                                          suffixIcon: const Icon(
                                              Icons.calendar_today,
                                              color: kSlate400),
                                          border: OutlineInputBorder(
                                              borderRadius:
                                                  BorderRadius.circular(12))),
                                    ),
                                  ],
                                ],
                              ),
                            ),
                            const SizedBox(height: 24),
                          ],

                          // Next Button
                          SizedBox(
                            width: double.infinity,
                            height: 52,
                            child: ElevatedButton(
                              onPressed: () {
                                final bool isStarlite = _mode == 'ferry' &&
                                    (_operator
                                            ?.toLowerCase()
                                            .contains('starlite') ??
                                        false);
                                bool isVehicleValid = true;
                                if (_mode == 'ferry' &&
                                    _isVehicleBookingEnabled &&
                                    isStarlite) {
                                  if (_plateCtrl.text.trim().isEmpty ||
                                      _driverFirstNameCtrl.text
                                          .trim()
                                          .isEmpty ||
                                      _driverLastNameCtrl.text.trim().isEmpty ||
                                      _driverBirthdayCtrl.text.trim().isEmpty) {
                                    isVehicleValid = false;
                                  }
                                }
                                if (_origin == null ||
                                    _destination == null ||
                                    !isVehicleValid) {
                                  return null;
                                }
                                return _goToSchedule;
                              }(),
                              style: ElevatedButton.styleFrom(
                                backgroundColor: kPink,
                                foregroundColor: Colors.white,
                                disabledBackgroundColor: kSlate200,
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12)),
                                elevation: 4,
                              ),
                              child: const Text('Next',
                                  style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 16)),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ),
                ),
        ),
      ],
    );
  }

  Widget _label(String text) => Text(text,
      style: const TextStyle(
          fontSize: 12, fontWeight: FontWeight.bold, color: kSlate600));

  InputDecoration _dropDecor(IconData icon) => InputDecoration(
        prefixIcon: Icon(icon, color: kGreen),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
        enabledBorder: OutlineInputBorder(
            borderRadius: BorderRadius.circular(12),
            borderSide: const BorderSide(color: kSlate200)),
        contentPadding:
            const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
      );

  Widget _datePicker(String value, VoidCallback? onTap, {String? label}) =>
      InkWell(
        onTap: onTap,
        child: Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
          decoration: BoxDecoration(
              border: Border.all(color: kSlate400),
              borderRadius: BorderRadius.circular(12),
              color: onTap == null ? kSlate50 : Colors.white),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Row(children: [
                if (label != null) ...[
                  Text('$label: ',
                      style: const TextStyle(color: kSlate500, fontSize: 13)),
                ],
                Text(value,
                    style: const TextStyle(fontSize: 14, color: kSlate800)),
              ]),
              const Icon(Icons.calendar_today, size: 20, color: kPink),
            ],
          ),
        ),
      );
}

class _ModeTab extends StatelessWidget {
  final String label;
  final IconData icon;
  final bool selected;
  final VoidCallback onTap;

  const _ModeTab(
      {required this.label,
      required this.icon,
      required this.selected,
      required this.onTap});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 200),
        padding: const EdgeInsets.symmetric(horizontal: 18, vertical: 10),
        decoration: BoxDecoration(
          color: selected ? kGreen : kSlate100,
          borderRadius: BorderRadius.circular(30),
          boxShadow: selected
              ? [
                  BoxShadow(
                      color: kGreen.withOpacity(0.3),
                      blurRadius: 8,
                      offset: const Offset(0, 3))
                ]
              : [],
        ),
        child: Row(
          children: [
            Icon(icon, size: 16, color: selected ? Colors.white : kSlate600),
            const SizedBox(width: 6),
            Text(label,
                style: TextStyle(
                    color: selected ? Colors.white : kSlate600,
                    fontWeight: FontWeight.bold,
                    fontSize: 13)),
          ],
        ),
      ),
    );
  }
}

class _PassengerCounter extends StatelessWidget {
  final String label;
  final String subtitle;
  final int count;
  final VoidCallback? onIncrement;
  final VoidCallback? onDecrement;

  const _PassengerCounter({
    required this.label,
    required this.subtitle,
    required this.count,
    this.onIncrement,
    this.onDecrement,
  });

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(label,
                  style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 14,
                      color: kSlate800)),
              Text(subtitle,
                  style: const TextStyle(color: kSlate500, fontSize: 12)),
            ],
          ),
        ),
        Row(
          children: [
            _CounterButton(icon: Icons.remove, onPressed: onDecrement),
            SizedBox(
                width: 44,
                child: Text('$count',
                    textAlign: TextAlign.center,
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: kSlate800))),
            _CounterButton(icon: Icons.add, onPressed: onIncrement),
          ],
        ),
      ],
    );
  }
}

class _CounterButton extends StatelessWidget {
  final IconData icon;
  final VoidCallback? onPressed;
  const _CounterButton({required this.icon, this.onPressed});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onPressed,
      child: Container(
        width: 34,
        height: 34,
        decoration: BoxDecoration(
          shape: BoxShape.rectangle,
          borderRadius: BorderRadius.circular(8),
          border: Border.all(color: onPressed != null ? kSlate400 : kSlate200),
          color: onPressed != null ? Colors.white : kSlate50,
        ),
        child: Icon(icon,
            size: 16, color: onPressed != null ? kSlate800 : kSlate400),
      ),
    );
  }
}

void showTermsModal(BuildContext context) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (ctx) => Container(
      height: MediaQuery.of(ctx).size.height * 0.8,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Text(
                  'Amiga Gracia Travel Services Terms & Conditions',
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: kSlate800),
                ),
              ),
              IconButton(
                icon: const Icon(Icons.close),
                onPressed: () => Navigator.pop(ctx),
              ),
            ],
          ),
          const Divider(),
          const Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Boarding Requirements',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    '• One printed copy of the eTicket Itinerary Receipt.\n'
                    '• Presentation of each passenger\'s valid ID.\n'
                    '• Passengers must arrive at the terminal 3–4 hours before departure. Boarding gates close 1 hour before departure.\n'
                    '• The operating ferry carrier reserves the right to refuse boarding if a passenger cannot present the required documents upon request.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'eTicket Itinerary Receipt',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'The eTicket Itinerary Receipt is non-transferable. It is valid only until the date and time of departure printed on the ticket. Unused or expired eTickets are non-refundable and cannot be revalidated, subject to applicable return policies.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Government-Mandated Discounts',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    '• Senior Citizens: Applicable to passengers aged 60 or above with valid OSCA or government-issued ID. A 20% discount applies to the base rate.\n'
                    '• Infants: Infants below 2 years old and below 1 meter in height may be allowed to board. A fixed rate of ₱500.00 applies per infant regardless of destination or accommodation.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(
                backgroundColor: kPink,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Got It',
                  style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    ),
  );
}

void showPrivacyModal(BuildContext context) {
  showModalBottomSheet(
    context: context,
    isScrollControlled: true,
    shape: const RoundedRectangleBorder(
      borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
    ),
    builder: (ctx) => Container(
      height: MediaQuery.of(ctx).size.height * 0.75,
      padding: const EdgeInsets.all(20),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Text(
                  'Amiga Gracia Agency Data Privacy Policy',
                  style: TextStyle(
                      fontSize: 18,
                      fontWeight: FontWeight.bold,
                      color: kSlate800),
                ),
              ),
              IconButton(
                icon: const Icon(Icons.close),
                onPressed: () => Navigator.pop(ctx),
              ),
            ],
          ),
          const Divider(),
          const Expanded(
            child: SingleChildScrollView(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    'Personal Data Collection',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'We collect only the personal information necessary to process your booking, issue tickets, and contact you about your travel reservation.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Use of Personal Data',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'Your information is used to confirm your booking, communicate updates, send receipts, and comply with transportation partner requirements.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Data Security & Retention',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'We take technical and organizational measures to safeguard your personal data. We retain booking details only as long as necessary to fulfill our services and comply with legal obligations.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                  SizedBox(height: 16),
                  Text(
                    'Your Rights',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 15,
                        color: kSlate800),
                  ),
                  SizedBox(height: 4),
                  Text(
                    'You have the right to access, correct, or request deletion of your personal data in accordance with applicable privacy laws.',
                    style:
                        TextStyle(fontSize: 13, color: kSlate600, height: 1.4),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            height: 48,
            child: ElevatedButton(
              onPressed: () => Navigator.pop(ctx),
              style: ElevatedButton.styleFrom(
                backgroundColor: kPink,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(10)),
              ),
              child: const Text('Got It',
                  style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    ),
  );
}

// ==========================================
// 3. ACTIVITY SCREEN
// ==========================================
class ActivityScreen extends StatefulWidget {
  final VoidCallback onLoginSuccess;
  const ActivityScreen({super.key, required this.onLoginSuccess});

  @override
  State<ActivityScreen> createState() => _ActivityScreenState();
}

class _ActivityScreenState extends State<ActivityScreen> {
  // Login/Register form fields
  final _emailCtrl = TextEditingController();
  final _passCtrl = TextEditingController();
  final _nameCtrl = TextEditingController();
  final _referralCtrl = TextEditingController();
  bool _isLoading = false;
  bool _obscure = true;
  bool _isSignUp = false;
  bool _agreeTerms = false;
  bool _agreePrivacy = false;

  // OTP registration state
  String? _pendingRegisterEmail; // non-null when OTP step is active
  final _otpCtrl = TextEditingController();
  bool _otpLoading = false;
  Timer? _otpTimer;
  int _otpCountdown = 0;

  void _startOtpTimer() {
    _otpTimer?.cancel();
    setState(() => _otpCountdown = 120);
    _otpTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) {
        timer.cancel();
        return;
      }
      if (_otpCountdown > 0) {
        setState(() => _otpCountdown--);
      } else {
        timer.cancel();
      }
    });
  }

  // Guest booking lookup (separate from login/register fields)
  final _guestEmailCtrl = TextEditingController();
  final _verificationCodeCtrl = TextEditingController();

  List<dynamic> _bookings = [];
  bool _loadingBookings = false;
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    if (UserSession.isLoggedIn) {
      _fetchBookings();
    } else {
      _loadVerifiedEmail();
    }
    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'booking_created' || event == 'booking_cancelled') {
        if (UserSession.isLoggedIn || UserSession.isEmailVerified) {
          _fetchBookings();
        }
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _nameCtrl.dispose();
    _referralCtrl.dispose();
    _otpCtrl.dispose();
    _guestEmailCtrl.dispose();
    _verificationCodeCtrl.dispose();
    _otpTimer?.cancel();
    super.dispose();
  }

  Future<void> _loadVerifiedEmail() async {
    final prefs = await SharedPreferences.getInstance();
    final email = prefs.getString('verified_email');
    final lookupToken = prefs.getString('booking_lookup_token');
    if (!mounted || email == null || lookupToken == null) return;
    setState(() {
      UserSession.email = email;
      UserSession.lookupToken = lookupToken;
      UserSession.isEmailVerified = true;
    });
    await UserSession.save();
    _fetchBookings();
  }

  Future<void> _fetchBookings() async {
    setState(() => _loadingBookings = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final headers = <String, String>{'Accept': 'application/json'};
      if (UserSession.isLoggedIn && UserSession.token.isNotEmpty) {
        headers['Authorization'] = 'Bearer ${UserSession.token}';
      }
      final response = await http.get(
        Uri.parse(
            '$baseUrl/api/bookings?email=${Uri.encodeComponent(UserSession.email)}&lookup_token=${Uri.encodeComponent(UserSession.lookupToken)}'),
        headers: headers,
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _bookings = parseJsonList(data['bookings']);
        });
      } else {
        debugPrint('Bookings error: ${response.statusCode} ${response.body}');
      }
    } catch (e) {
      debugPrint('Error fetching bookings: $e');
    } finally {
      setState(() => _loadingBookings = false);
    }
  }

  // Step 1 of registration: request OTP
  Future<void> _requestRegisterOtp() async {
    final email = _emailCtrl.text.trim();
    final password = _passCtrl.text;
    final name = _nameCtrl.text.trim();

    if (name.isEmpty || email.isEmpty || password.isEmpty) {
      showTopSnack(
        context,
        const SnackBar(
            content: Text('Please fill in your username, email, and password.'),
            backgroundColor: Colors.red),
      );
      return;
    }
    if (password.length < 8) {
      showTopSnack(
        context,
        const SnackBar(
            content: Text('Password must be at least 8 characters.'),
            backgroundColor: Colors.red),
      );
      return;
    }
    if (!_agreeTerms || !_agreePrivacy) {
      showTopSnack(
        context,
        const SnackBar(
          content: Text(
              'You must agree to the Terms & Conditions and Data Privacy Policy to register.'),
          backgroundColor: Colors.red,
        ),
      );
      return;
    }

    setState(() => _isLoading = true);
    try {
      final response = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/register/request-otp'),
        headers: {'Accept': 'application/json'},
        body: {
          'name': name,
          'email': email,
          'password': password,
          'referral_code': _referralCtrl.text.trim(),
        },
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _pendingRegisterEmail = email;
          _otpCtrl.clear();
          _startOtpTimer();
        });
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text(data['message'] ?? 'OTP sent! Check your email.'),
              backgroundColor: kGreen),
        );
      } else {
        final msg = data['message'] ??
            data['errors']?.values?.first?.first ??
            'Could not send OTP.';
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(content: Text(msg), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(
            content: Text('Connection error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  // Step 2 of registration: verify OTP and complete account creation
  Future<void> _verifyRegisterOtp() async {
    final otp = _otpCtrl.text.trim();
    if (otp.length != 6) {
      showTopSnack(
        context,
        const SnackBar(
            content: Text('Enter the 6-digit code sent to your email.'),
            backgroundColor: Colors.red),
      );
      return;
    }
    setState(() => _otpLoading = true);
    try {
      final response = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/register/verify-otp'),
        headers: {'Accept': 'application/json'},
        body: {'email': _pendingRegisterEmail!, 'otp': otp},
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          UserSession.isLoggedIn = true;
          UserSession.username = data['user']['name'];
          UserSession.email = data['user']['email'];
          UserSession.phone = data['user']['phone'] ?? '';
          UserSession.referralCode = data['user']['referral_code'];
          UserSession.token = data['token'];
          UserSession.lookupToken = data['lookup_token'] ?? '';
          UserSession.isEmailVerified = UserSession.lookupToken.isNotEmpty;
          _pendingRegisterEmail = null;
        });
        await UserSession.save();

        // Attempt to apply referral code if provided
        final refCode = _referralCtrl.text.trim();
        if (refCode.isNotEmpty) {
          try {
            await http.post(
              Uri.parse('${UserSession.getBaseUrl()}/api/referral/apply'),
              headers: {
                'Accept': 'application/json',
                'Authorization': 'Bearer ${UserSession.token}',
              },
              body: {'code': refCode},
            );
          } catch (_) {}
        }

        // Subscribe to user-specific FCM topic for targeted notifications (e.g. booking cancellation)
        await NotificationService.subscribeToUserTopic(UserSession.email);
        widget.onLoginSuccess();
        _fetchBookings();
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content:
                  Text(data['message'] ?? 'Welcome, ${UserSession.username}!'),
              backgroundColor: kGreen),
        );
      } else {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text(data['message'] ?? 'Verification failed.'),
              backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(
            content: Text('Connection error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _otpLoading = false);
    }
  }

  Future<void> _resendRegisterOtp() async {
    if (_pendingRegisterEmail == null) return;
    setState(() => _otpLoading = true);
    try {
      final response = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/register/resend-otp'),
        headers: {'Accept': 'application/json'},
        body: {'email': _pendingRegisterEmail!},
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text(data['message'] ?? 'A new code has been sent.'),
              backgroundColor: kGreen),
        );
        _otpCtrl.clear();
        _startOtpTimer();
      } else {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text(data['message'] ?? 'Could not resend OTP.'),
              backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(
            content: Text('Connection error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _otpLoading = false);
    }
  }

  void _submitAuth() async {
    final email = _emailCtrl.text.trim();
    final password = _passCtrl.text;

    if (email.isEmpty || password.isEmpty) {
      showTopSnack(
        context,
        const SnackBar(
            content: Text('Please fill out all required fields.'),
            backgroundColor: Colors.red),
      );
      return;
    }

    setState(() => _isLoading = true);

    try {
      final response = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/login'),
        headers: {'Accept': 'application/json'},
        body: {'email': email, 'password': password},
      );

      final data = jsonDecode(response.body);

      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          UserSession.isLoggedIn = true;
          UserSession.username = data['user']['name'];
          UserSession.email = data['user']['email'];
          UserSession.phone = data['user']['phone'] ?? '';
          UserSession.referralCode = data['user']['referral_code'];
          UserSession.token = data['token'];
          UserSession.lookupToken = data['lookup_token'] ?? '';
          UserSession.isEmailVerified = UserSession.lookupToken.isNotEmpty;
        });
        await UserSession.save();
        // Subscribe to user-specific FCM topic for targeted notifications (e.g. booking cancellation)
        await NotificationService.subscribeToUserTopic(UserSession.email);
        widget.onLoginSuccess();
        _fetchBookings();
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text('Welcome back, ${data['user']['name']}!'),
              backgroundColor: kGreen),
        );
      } else {
        final errorMsg = data['message'] ??
            'Authentication failed. Please check your credentials.';
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(content: Text(errorMsg), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(
            content: Text('Error connecting to server: $e'),
            backgroundColor: Colors.red),
      );
    } finally {
      setState(() => _isLoading = false);
    }
  }

  void _showForgotPasswordSheet() {
    final emailController = TextEditingController(text: _emailCtrl.text.trim());
    final otpController = TextEditingController();
    final passController = TextEditingController();
    final confirmPassController = TextEditingController();
    bool isOtpSent = false;
    bool modalLoading = false;
    bool obscurePass = true;
    bool obscureConfirm = true;

    showModalBottomSheet(
      context: context,
      isScrollControlled: true,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(20)),
      ),
      builder: (ctx) => StatefulBuilder(
        builder: (context, setModalState) {
          return Padding(
            padding: EdgeInsets.only(
              bottom: MediaQuery.of(context).viewInsets.bottom,
              left: 20,
              right: 20,
              top: 20,
            ),
            child: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        isOtpSent ? 'Reset Your Password' : 'Forgot Password',
                        style: const TextStyle(
                            fontSize: 20,
                            fontWeight: FontWeight.bold,
                            color: kSlate800),
                      ),
                      IconButton(
                        icon: const Icon(Icons.close),
                        onPressed: () => Navigator.pop(ctx),
                      ),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(
                    isOtpSent
                        ? 'Enter the 6-digit code sent to ${emailController.text.trim()} and set your new password.'
                        : 'Enter your registered email address to receive a 6-digit password reset code.',
                    style: const TextStyle(color: kSlate600, fontSize: 13),
                  ),
                  const SizedBox(height: 16),
                  if (!isOtpSent) ...[
                    TextField(
                      controller: emailController,
                      keyboardType: TextInputType.emailAddress,
                      decoration: InputDecoration(
                        labelText: 'Registered Email Address',
                        prefixIcon:
                            const Icon(Icons.email_outlined, color: kGreen),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: modalLoading
                            ? null
                            : () async {
                                final email = emailController.text.trim();
                                if (email.isEmpty || !email.contains('@')) {
                                  showTopSnack(
                                    context,
                                    const SnackBar(
                                        content: Text(
                                            'Please enter a valid email address.'),
                                        backgroundColor: Colors.red),
                                  );
                                  return;
                                }
                                setModalState(() => modalLoading = true);
                                try {
                                  final res = await http.post(
                                    Uri.parse(
                                        '${UserSession.getBaseUrl()}/api/forgot-password/request-otp'),
                                    headers: {'Accept': 'application/json'},
                                    body: {'email': email},
                                  );
                                  final data = jsonDecode(res.body);
                                  if (res.statusCode == 200 &&
                                      data['status'] == 'success') {
                                    setModalState(() {
                                      isOtpSent = true;
                                      modalLoading = false;
                                    });
                                    if (!mounted) return;
                                    showTopSnack(
                                      context,
                                      SnackBar(
                                          content: Text(data['message'] ??
                                              'Reset code sent! Check your email.'),
                                          backgroundColor: kGreen),
                                    );
                                  } else {
                                    final msg = data['message'] ??
                                        'Failed to send verification code.';
                                    if (!mounted) return;
                                    showTopSnack(
                                      context,
                                      SnackBar(
                                          content: Text(msg),
                                          backgroundColor: Colors.red),
                                    );
                                  }
                                } catch (e) {
                                  if (!mounted) return;
                                  showTopSnack(
                                    context,
                                    SnackBar(
                                        content: Text('Connection error: $e'),
                                        backgroundColor: Colors.red),
                                  );
                                } finally {
                                  if (context.mounted) {
                                    setModalState(() => modalLoading = false);
                                  }
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: kPink,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        child: modalLoading
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2.5))
                            : const Text('Send Reset Code',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold, fontSize: 16)),
                      ),
                    ),
                  ] else ...[
                    TextField(
                      controller: otpController,
                      keyboardType: TextInputType.number,
                      maxLength: 6,
                      decoration: InputDecoration(
                        labelText: '6-Digit Verification Code',
                        prefixIcon:
                            const Icon(Icons.pin_outlined, color: kGreen),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: passController,
                      obscureText: obscurePass,
                      decoration: InputDecoration(
                        labelText: 'New Password',
                        prefixIcon:
                            const Icon(Icons.lock_outline, color: kGreen),
                        suffixIcon: IconButton(
                          icon: Icon(
                              obscurePass
                                  ? Icons.visibility_off
                                  : Icons.visibility,
                              color: kSlate400),
                          onPressed: () =>
                              setModalState(() => obscurePass = !obscurePass),
                        ),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    TextField(
                      controller: confirmPassController,
                      obscureText: obscureConfirm,
                      decoration: InputDecoration(
                        labelText: 'Confirm New Password',
                        prefixIcon:
                            const Icon(Icons.lock_outline, color: kGreen),
                        suffixIcon: IconButton(
                          icon: Icon(
                              obscureConfirm
                                  ? Icons.visibility_off
                                  : Icons.visibility,
                              color: kSlate400),
                          onPressed: () => setModalState(
                              () => obscureConfirm = !obscureConfirm),
                        ),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(12)),
                      ),
                    ),
                    const SizedBox(height: 20),
                    SizedBox(
                      width: double.infinity,
                      height: 50,
                      child: ElevatedButton(
                        onPressed: modalLoading
                            ? null
                            : () async {
                                final otp = otpController.text.trim();
                                final newPass = passController.text;
                                final confirmPass = confirmPassController.text;
                                if (otp.length != 6) {
                                  showTopSnack(
                                    context,
                                    const SnackBar(
                                        content: Text(
                                            'Please enter the 6-digit code.'),
                                        backgroundColor: Colors.red),
                                  );
                                  return;
                                }
                                if (newPass.length < 8) {
                                  showTopSnack(
                                    context,
                                    const SnackBar(
                                        content: Text(
                                            'Password must be at least 8 characters.'),
                                        backgroundColor: Colors.red),
                                  );
                                  return;
                                }
                                if (newPass != confirmPass) {
                                  showTopSnack(
                                    context,
                                    const SnackBar(
                                        content:
                                            Text('Passwords do not match.'),
                                        backgroundColor: Colors.red),
                                  );
                                  return;
                                }
                                setModalState(() => modalLoading = true);
                                try {
                                  final res = await http.post(
                                    Uri.parse(
                                        '${UserSession.getBaseUrl()}/api/forgot-password/reset'),
                                    headers: {'Accept': 'application/json'},
                                    body: {
                                      'email': emailController.text.trim(),
                                      'otp': otp,
                                      'password': newPass,
                                      'password_confirmation': confirmPass,
                                    },
                                  );
                                  final data = jsonDecode(res.body);
                                  if (res.statusCode == 200 &&
                                      data['status'] == 'success') {
                                    if (!mounted) return;
                                    Navigator.pop(ctx);
                                    setState(() {
                                      _emailCtrl.text =
                                          emailController.text.trim();
                                      _passCtrl.clear();
                                      _isSignUp = false;
                                    });
                                    if (!mounted) return;
                                    showTopSnack(
                                      context,
                                      SnackBar(
                                          content: Text(data['message'] ??
                                              'Password reset successfully! Please log in.'),
                                          backgroundColor: kGreen),
                                    );
                                  } else {
                                    final msg = data['message'] ??
                                        'Failed to reset password.';
                                    if (!mounted) return;
                                    showTopSnack(
                                      context,
                                      SnackBar(
                                          content: Text(msg),
                                          backgroundColor: Colors.red),
                                    );
                                  }
                                } catch (e) {
                                  if (!mounted) return;
                                  showTopSnack(
                                    context,
                                    SnackBar(
                                        content: Text('Connection error: $e'),
                                        backgroundColor: Colors.red),
                                  );
                                } finally {
                                  if (context.mounted) {
                                    setModalState(() => modalLoading = false);
                                  }
                                }
                              },
                        style: ElevatedButton.styleFrom(
                          backgroundColor: kPink,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12)),
                        ),
                        child: modalLoading
                            ? const SizedBox(
                                width: 22,
                                height: 22,
                                child: CircularProgressIndicator(
                                    color: Colors.white, strokeWidth: 2.5))
                            : const Text('Reset Password',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold, fontSize: 16)),
                      ),
                    ),
                  ],
                  const SizedBox(height: 24),
                ],
              ),
            ),
          );
        },
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    if (!UserSession.isLoggedIn && !UserSession.isEmailVerified) {
      // ── OTP verification screen (after sign-up form submitted) ──────────
      if (_pendingRegisterEmail != null) {
        return SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 40),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Image.asset('assets/icon/app_icon.png',
                  height: 90, width: 90, fit: BoxFit.contain),
              const SizedBox(height: 24),
              const Text(
                'Verify Your Email',
                style: TextStyle(
                    fontSize: 24, fontWeight: FontWeight.w900, color: kGreen),
              ),
              const SizedBox(height: 8),
              Text(
                'We sent a 6-digit code to\n$_pendingRegisterEmail',
                textAlign: TextAlign.center,
                style: const TextStyle(fontSize: 13, color: kSlate500),
              ),
              const SizedBox(height: 36),
              TextField(
                controller: _otpCtrl,
                keyboardType: TextInputType.number,
                maxLength: 6,
                textAlign: TextAlign.center,
                style: const TextStyle(
                    fontSize: 28,
                    fontWeight: FontWeight.bold,
                    letterSpacing: 12),
                decoration: InputDecoration(
                  hintText: '000000',
                  hintStyle: const TextStyle(
                      color: kSlate300, fontSize: 28, letterSpacing: 12),
                  prefixIcon:
                      const Icon(Icons.lock_clock_outlined, color: kGreen),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 8),
              Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  _otpCountdown > 0
                      ? Text(
                          'Code expires in ${_otpCountdown ~/ 60}:${(_otpCountdown % 60).toString().padLeft(2, '0')}',
                          style:
                              const TextStyle(fontSize: 12, color: kSlate400),
                        )
                      : const Text(
                          'Your code has expired.',
                          style: TextStyle(
                              fontSize: 12,
                              color: Colors.red,
                              fontWeight: FontWeight.bold),
                        ),
                  TextButton(
                    onPressed: (_otpLoading || _otpCountdown > 0)
                        ? null
                        : _resendRegisterOtp,
                    style: TextButton.styleFrom(
                      padding: EdgeInsets.zero,
                      minimumSize: const Size(50, 30),
                      tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                    ),
                    child: Text(
                      'Resend Code',
                      style: TextStyle(
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          color: (_otpLoading || _otpCountdown > 0)
                              ? Colors.grey
                              : kPink),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
              SizedBox(
                width: double.infinity,
                height: 52,
                child: ElevatedButton(
                  onPressed: _otpLoading ? null : _verifyRegisterOtp,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kPink,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                    elevation: 4,
                  ),
                  child: _otpLoading
                      ? const SizedBox(
                          width: 22,
                          height: 22,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2.5))
                      : const Text('Verify & Create Account',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 16)),
                ),
              ),
              const SizedBox(height: 12),
              TextButton(
                onPressed: () => setState(() => _pendingRegisterEmail = null),
                child: const Text('← Back to sign up',
                    style: TextStyle(color: kSlate500)),
              ),
            ],
          ),
        );
      }

      // ── Login / Register form ────────────────────────────────────────────
      return SingleChildScrollView(
        keyboardDismissBehavior: ScrollViewKeyboardDismissBehavior.onDrag,
        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 32),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.center,
          children: [
            const SizedBox(height: 16),
            // Amiga Gracia logo (transparent bg) instead of ship icon
            Container(
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                  color: kGreen, borderRadius: BorderRadius.circular(20)),
              child: Image.asset('assets/icon/amiga_logo_white_outline.png',
                  height: 64, width: 64, fit: BoxFit.contain),
            ),
            const SizedBox(height: 20),
            Text(
              _isSignUp ? 'Create Account' : 'Welcome Back!',
              style: const TextStyle(
                  fontSize: 24, fontWeight: FontWeight.w900, color: kGreen),
            ),
            const SizedBox(height: 6),
            Text(
              _isSignUp
                  ? 'Sign up to start booking ferry and flights'
                  : 'Sign in to view your bookings & transactions',
              textAlign: TextAlign.center,
              style: const TextStyle(fontSize: 13, color: kSlate500),
            ),
            const SizedBox(height: 36),

            // ── Sign-up extra field: Username ──────────────────────────────
            if (_isSignUp) ...[
              TextField(
                controller: _nameCtrl,
                keyboardType: TextInputType.name,
                decoration: InputDecoration(
                  labelText: 'Username',
                  prefixIcon: const Icon(Icons.person_outline, color: kGreen),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 16),
              TextField(
                controller: _referralCtrl,
                keyboardType: TextInputType.text,
                textCapitalization: TextCapitalization.characters,
                decoration: InputDecoration(
                  labelText: 'Referral Code (Optional)',
                  prefixIcon: const Icon(Icons.card_giftcard, color: kGreen),
                  border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12)),
                ),
              ),
              const SizedBox(height: 16),
            ],

            TextField(
              controller: _emailCtrl,
              keyboardType: TextInputType.emailAddress,
              decoration: InputDecoration(
                labelText: 'Email address',
                prefixIcon: const Icon(Icons.email_outlined, color: kGreen),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            const SizedBox(height: 16),

            TextField(
              controller: _passCtrl,
              obscureText: _obscure,
              decoration: InputDecoration(
                labelText: 'Password',
                prefixIcon: const Icon(Icons.lock_outline, color: kGreen),
                suffixIcon: IconButton(
                  icon: Icon(_obscure ? Icons.visibility_off : Icons.visibility,
                      color: kSlate400),
                  onPressed: () => setState(() => _obscure = !_obscure),
                ),
                border:
                    OutlineInputBorder(borderRadius: BorderRadius.circular(12)),
              ),
            ),
            if (!_isSignUp) ...[
              const SizedBox(height: 4),
              Align(
                alignment: Alignment.centerRight,
                child: TextButton(
                  onPressed: () {
                    Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => const ForgotPasswordScreen()));
                  },
                  style: TextButton.styleFrom(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                    minimumSize: Size.zero,
                    tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                  ),
                  child: const Text(
                    'Forgot Password?',
                    style: TextStyle(
                        color: kGreen,
                        fontWeight: FontWeight.w600,
                        fontSize: 13),
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ] else ...[
              const SizedBox(height: 12),
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Checkbox(
                    value: _agreeTerms,
                    onChanged: (val) =>
                        setState(() => _agreeTerms = val ?? false),
                    activeColor: kPink,
                  ),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => showTermsModal(context),
                      child: RichText(
                        text: const TextSpan(
                          style: TextStyle(fontSize: 12, color: kSlate700),
                          children: [
                            TextSpan(text: 'I agree to the '),
                            TextSpan(
                              text: 'Terms and Conditions / Agreement',
                              style: TextStyle(
                                  color: kPink,
                                  fontWeight: FontWeight.bold,
                                  decoration: TextDecoration.underline),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              Row(
                crossAxisAlignment: CrossAxisAlignment.center,
                children: [
                  Checkbox(
                    value: _agreePrivacy,
                    onChanged: (val) =>
                        setState(() => _agreePrivacy = val ?? false),
                    activeColor: kPink,
                  ),
                  Expanded(
                    child: GestureDetector(
                      onTap: () => showPrivacyModal(context),
                      child: RichText(
                        text: const TextSpan(
                          style: TextStyle(fontSize: 12, color: kSlate700),
                          children: [
                            TextSpan(text: 'I agree to the '),
                            TextSpan(
                              text: 'Data Privacy Policy',
                              style: TextStyle(
                                  color: kPink,
                                  fontWeight: FontWeight.bold,
                                  decoration: TextDecoration.underline),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 16),
            ],

            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: _isLoading
                    ? null
                    : (_isSignUp ? _requestRegisterOtp : _submitAuth),
                style: ElevatedButton.styleFrom(
                  backgroundColor: kPink,
                  foregroundColor: Colors.white,
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(12)),
                  elevation: 4,
                ),
                child: _isLoading
                    ? const SizedBox(
                        width: 22,
                        height: 22,
                        child: CircularProgressIndicator(
                            color: Colors.white, strokeWidth: 2.5))
                    : Text(_isSignUp ? 'Sign Up' : 'Login',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 16)),
              ),
            ),
            const SizedBox(height: 12),
            TextButton(
              onPressed: () => setState(() {
                _isSignUp = !_isSignUp;
                _emailCtrl.clear();
                _passCtrl.clear();
                _nameCtrl.clear();
              }),
              child: Text(
                _isSignUp
                    ? 'Already have an account? Login'
                    : "Don't have an account? Register",
                style:
                    const TextStyle(color: kPink, fontWeight: FontWeight.bold),
              ),
            ),
          ],
        ),
      );
    }

    if (_loadingBookings) {
      return const Center(child: CircularProgressIndicator(color: kGreen));
    }

    return RefreshIndicator(
      onRefresh: () async => _fetchBookings(),
      color: kGreen,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          const Text('My Bookings',
              style: TextStyle(
                  fontWeight: FontWeight.bold, fontSize: 18, color: kSlate800)),
          const SizedBox(height: 12),
          if (_bookings.isEmpty)
            const Center(
              child: Padding(
                padding: EdgeInsets.symmetric(vertical: 48),
                child: Column(
                  children: [
                    Icon(Icons.receipt_long, size: 64, color: kSlate200),
                    SizedBox(height: 16),
                    Text('No bookings yet',
                        style: TextStyle(color: kSlate400, fontSize: 16)),
                    Text('Your bookings will appear here after you book.',
                        style: TextStyle(color: kSlate400, fontSize: 12),
                        textAlign: TextAlign.center),
                  ],
                ),
              ),
            )
          else
            ..._bookings.whereType<Map>().map((b) {
              final status = b['status']?.toString() ?? 'pending';
              Color statusColor = Colors.orange;
              if (status == 'confirmed' || status == 'paid') {
                statusColor = kGreen;
              }
              if (status == 'cancelled' || status == 'operator_cancelled') {
                statusColor = Colors.red;
              }

              return Card(
                color: Colors.white,
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            b['transaction_number'] ?? '',
                            style: const TextStyle(
                                fontWeight: FontWeight.bold, color: kGreen),
                          ),
                          Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 8, vertical: 4),
                            decoration: BoxDecoration(
                              color: statusColor.withOpacity(0.1),
                              borderRadius: BorderRadius.circular(8),
                            ),
                            child: Text(
                              status == 'operator_cancelled'
                                  ? 'CANCELLED BY OPERATOR'
                                  : status.toUpperCase(),
                              style: TextStyle(
                                  color: statusColor,
                                  fontSize: 10,
                                  fontWeight: FontWeight.bold),
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      // Route
                      Row(
                        children: [
                          const Icon(Icons.location_pin, size: 14, color: kGreen),
                          const SizedBox(width: 6),
                          Text(
                            '${b['origin'] ?? ''} → ${b['destination'] ?? ''}',
                            style: const TextStyle(fontSize: 13, color: kSlate700, fontWeight: FontWeight.w600),
                          ),
                        ],
                      ),
                      const SizedBox(height: 4),
                      // Date
                      Row(
                        children: [
                          const Icon(Icons.calendar_today_outlined, size: 13, color: kSlate400),
                          const SizedBox(width: 6),
                          Text(
                            () {
                              final dtStr = (b['rebooking_departure_date'] ?? b['preferred_replacement_date'] ?? b['departure_date'])?.toString() ?? '';
                              if (dtStr.isEmpty) return '-';
                              final dt = DateTime.tryParse(dtStr)?.toLocal();
                              if (dt == null) return dtStr;
                              return '${dt.year}-${dt.month.toString().padLeft(2,'0')}-${dt.day.toString().padLeft(2,'0')}';
                            }(),
                            style: const TextStyle(fontSize: 12, color: kSlate500),
                          ),
                          if ((b['rebooking_return_date'] ?? b['return_date']) != null) ...[
                            const Text('  →  ', style: TextStyle(fontSize: 12, color: kSlate400)),
                            Text(
                              () {
                                final dtStr = (b['rebooking_return_date'] ?? b['return_date'])?.toString() ?? '';
                                if (dtStr.isEmpty) return '';
                                final dt = DateTime.tryParse(dtStr)?.toLocal();
                                if (dt == null) return dtStr;
                                return '${dt.year}-${dt.month.toString().padLeft(2,'0')}-${dt.day.toString().padLeft(2,'0')}';
                              }(),
                              style: const TextStyle(fontSize: 12, color: kSlate500),
                            ),
                          ],
                        ],
                      ),
                      const SizedBox(height: 4),
                      // Vessel / schedule name
                      Row(
                        children: [
                          Icon(
                            () {
                              final mode = (b['schedule']?['route']?['mode'] ?? b['mode'] ?? 'ferry').toString().toLowerCase();
                              return mode == 'airline' ? Icons.flight : Icons.directions_boat_outlined;
                            }(),
                            size: 13,
                            color: kSlate400,
                          ),
                          const SizedBox(width: 6),
                          Expanded(
                            child: Text(
                              (b['schedule_summary'] ?? b['schedule_service'] ?? '').toString(),
                              style: const TextStyle(fontSize: 12, color: kSlate500),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const Divider(height: 20, color: kSlate200),

                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text(
                            '₱${b['total_price']}',
                            style: const TextStyle(
                                fontWeight: FontWeight.w900,
                                fontSize: 15,
                                color: kPink),
                          ),
                          if (b['gracia_points'] != null &&
                              (int.tryParse(b['gracia_points'].toString()) ??
                                      0) >
                                  0)
                            Row(
                              children: [
                                const Icon(Icons.star_rounded,
                                    color: kPink, size: 14),
                                const SizedBox(width: 4),
                                Text('+${b['gracia_points']} pts',
                                    style: const TextStyle(
                                        color: kPink,
                                        fontWeight: FontWeight.bold,
                                        fontSize: 12)),
                              ],
                            ),
                          Text(
                            b['created_at'] != null
                                ? 'Booked: ${DateTime.tryParse(b['created_at'].toString())?.toLocal().toString().split(' ')[0] ?? ''}'
                                : '',
                            style:
                                const TextStyle(fontSize: 11, color: kSlate400),
                          ),
                        ],
                      ),
                      if (b['rebooking_status'] == 'pending') ...[
                        const SizedBox(height: 12),
                        const Text('Rebooking request pending verification',
                            style: TextStyle(
                                color: Colors.orange,
                                fontWeight: FontWeight.bold,
                                fontSize: 12)),
                      ],
                      const SizedBox(height: 12),
                      SizedBox(
                        width: double.infinity,
                        child: OutlinedButton.icon(
                          onPressed: () async {
                            final transaction = b['transaction'] is Map
                                ? b['transaction']
                                : null;
                            if (transaction != null &&
                                transaction['payment_status'] == 'unpaid' &&
                                transaction['proof_of_payment'] == null) {
                              await Navigator.push(
                                context,
                                MaterialPageRoute(
                                  builder: (_) => PaymentProofScreen(
                                    bookingId: b['id'],
                                    transactionNumber:
                                        b['transaction_number'] ?? 'N/A',
                                    totalPrice: b['total_price'] is num
                                        ? _parseDouble(b['total_price'])
                                        : double.tryParse(
                                                b['total_price'].toString()) ??
                                            0.0,
                                    paymentDeadlineAt: transaction[
                                                'payment_deadline_at'] !=
                                            null
                                        ? DateTime.tryParse(
                                            transaction['payment_deadline_at'])
                                        : null,
                                  ),
                                ),
                              );
                            } else {
                              await Navigator.push(
                                  context,
                                  MaterialPageRoute(
                                      builder: (_) => BookingDetailsScreen(
                                          booking:
                                              Map<String, dynamic>.from(b))));
                            }
                            _fetchBookings();
                          },
                          icon: const Icon(Icons.open_in_new, size: 18),
                          label: const Text('Open booking',
                              style: TextStyle(fontWeight: FontWeight.bold)),
                          style: OutlinedButton.styleFrom(
                            foregroundColor: kGreen,
                            side: const BorderSide(color: kGreen),
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(8)),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              );
            }),
        ],
      ),
    );
  }
}

class BookingDetailsScreen extends StatefulWidget {
  final Map<String, dynamic> booking;

  const BookingDetailsScreen({super.key, required this.booking});

  @override
  State<BookingDetailsScreen> createState() => _BookingDetailsScreenState();
}

class _BookingDetailsScreenState extends State<BookingDetailsScreen> {
  late Map<String, dynamic> _booking;
  final _refundInstitutionCtrl = TextEditingController();
  final _refundAccountCtrl = TextEditingController();
  final _refundNameCtrl = TextEditingController();
  Timer? _cancellationTimer;
  String _refundMethod = 'GCash';
  bool _cancellationStarted = false;
  bool _busy = false;
  String? _qrCodeUrl;
  double? _cancellationFee;
  double? _refundAmount;

  String get _baseUrl => UserSession.getBaseUrl();
  String get _paymentStatus => (_booking['transaction'] is Map
          ? (_booking['transaction']['payment_status'] ?? 'unpaid')
          : 'unpaid')
      .toString();
  bool get _canManage =>
      _booking['status'] != 'cancelled' &&
      _booking['status'] != 'operator_cancelled';
  bool get _isRoundTrip => _booking['return_date'] != null;

  DateTime? get _freeCancellationExpiresAt {
    if (_booking['created_at'] == null) return null;
    return DateTime.tryParse(_booking['created_at'].toString())
        ?.add(const Duration(minutes: 5));
  }

  @override
  void initState() {
    super.initState();
    _booking = Map<String, dynamic>.from(widget.booking);
    if (_booking['price_breakdown'] is String) {
      try {
        _booking['price_breakdown'] = jsonDecode(_booking['price_breakdown']);
      } catch (_) {
        _booking['price_breakdown'] = [];
      }
    }
    if (_booking['passengers'] is String) {
      try {
        _booking['passengers'] = jsonDecode(_booking['passengers']);
      } catch (_) {
        _booking['passengers'] = [];
      }
    }
    _fetchPaymentSettings();
    _cancellationTimer = Timer.periodic(const Duration(seconds: 1), (_) {
      if (mounted) setState(() {});
    });
  }

  @override
  void dispose() {
    _cancellationTimer?.cancel();
    _refundInstitutionCtrl.dispose();
    _refundAccountCtrl.dispose();
    _refundNameCtrl.dispose();
    super.dispose();
  }

  Future<void> _refreshBooking() async {
    try {
      final res = await http.get(
        Uri.parse('$_baseUrl/api/bookings/${_booking['transaction_number']}'),
        headers: {
          'Accept': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success' && data['booking'] != null) {
          if (mounted) {
            setState(() {
              _booking = data['booking'];
              if (_booking['price_breakdown'] is String) {
                try {
                  _booking['price_breakdown'] =
                      jsonDecode(_booking['price_breakdown']);
                } catch (_) {
                  _booking['price_breakdown'] = [];
                }
              }
              if (_booking['passengers'] is String) {
                try {
                  _booking['passengers'] = jsonDecode(_booking['passengers']);
                } catch (_) {
                  _booking['passengers'] = [];
                }
              }
            });
          }
        }
      }
    } catch (e) {
      // ignore
    }
  }

  Future<void> _fetchPaymentSettings() async {
    try {
      final response = await http.get(
          Uri.parse('$_baseUrl/api/payment-settings'),
          headers: {'Accept': 'application/json'});
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && mounted) {
        setState(() => _qrCodeUrl = data['qr_code_url']);
      }
    } catch (_) {}
  }

  void _showMessage(String message, {bool error = false}) {
    if (!mounted) return;
    showTopSnack(
        context,
        SnackBar(
            content: Text(message),
            backgroundColor: error ? Colors.red : kGreen));
  }

  String _refundDestination() {
    final parts = ['Method: $_refundMethod'];
    if (_refundMethod != 'GCash') {
      parts.add('Institution: ${_refundInstitutionCtrl.text.trim()}');
    }
    parts.add('Account No: ${_refundAccountCtrl.text.trim()}');
    parts.add('Name: ${_refundNameCtrl.text.trim()}');
    return parts.join(' | ');
  }

  Future<void> _confirmCancellation() async {
    if (_refundAccountCtrl.text.trim().isEmpty ||
        _refundNameCtrl.text.trim().isEmpty ||
        (_refundMethod != 'GCash' &&
            _refundInstitutionCtrl.text.trim().isEmpty)) {
      _showMessage('Complete the refund details first.', error: true);
      return;
    }
    final totalPrice = _parseDouble(_booking['total_price']);
    final displayFee = _cancellationFee ?? (totalPrice * 0.5);
    final displayRefund = _refundAmount ?? (totalPrice * 0.5);
    final confirm = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Confirm Cancellation'),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('Summary of your cancellation:',
                style: TextStyle(fontWeight: FontWeight.bold)),
            const SizedBox(height: 12),
            _feeRow('Original Total', totalPrice, color: Colors.black87),
            _feeRow('Cancellation Fee', -displayFee, color: Colors.red),
            const Divider(height: 20),
            _feeRow('Estimated Refund', displayRefund,
                color: Colors.green.shade700, bold: true),
            const SizedBox(height: 12),
            const Text('Refund will be processed in 3–5 business days.',
                style: TextStyle(fontSize: 12, color: Colors.grey)),
          ],
        ),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Back')),
          FilledButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: FilledButton.styleFrom(backgroundColor: Colors.red),
            child: const Text('Confirm Cancellation'),
          ),
        ],
      ),
    );
    if (confirm != true) return;
    setState(() => _busy = true);
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/api/bookings/${_booking['id']}/cancel'),
        headers: {'Accept': 'application/json'},
        body: {
          'email': UserSession.email,
          'action': 'confirm',
          'refund_destination': _refundDestination()
        },
      );
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        _booking['status'] = 'cancelled';
        _booking['cancellation_fee'] = data['cancellation_fee'];
        _booking['refund_amount'] = data['refund_amount'];
        setState(() => _cancellationStarted = false);
        _showMessage('Booking cancelled. Refund: ₱${data['refund_amount']}');
      } else {
        _showMessage(data['message'] ?? 'Cancellation failed.', error: true);
      }
    } catch (e) {
      _showMessage('Connection error: $e', error: true);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _uploadPaymentProof() async {
    final proof = await ImagePicker()
        .pickImage(source: ImageSource.gallery, imageQuality: 80);
    if (proof == null) return;
    setState(() => _busy = true);
    try {
      final request = http.MultipartRequest(
          'POST', Uri.parse('$_baseUrl/api/bookings/${_booking['id']}/proof'));
      request.headers['Accept'] = 'application/json';
      request.fields['email'] = UserSession.email;
      request.files.add(await http.MultipartFile.fromPath('proof', proof.path));
      final response = await http.Response.fromStream(await request.send());
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        final transaction =
            Map<String, dynamic>.from(_booking['transaction'] ?? {});
        transaction['payment_status'] = 'pending';
        _booking['transaction'] = transaction;
        setState(() {});
        _showMessage('Payment proof uploaded for verification.');
      } else {
        _showMessage(data['message'] ?? 'Upload failed.', error: true);
      }
    } catch (e) {
      _showMessage('Upload error: $e', error: true);
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Widget _feeRow(String label, double amount,
      {Color color = Colors.black87, bool bold = false}) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 3),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                  fontSize: 13,
                  color: color,
                  fontWeight: bold ? FontWeight.bold : FontWeight.normal)),
          Text(
            '${amount < 0 ? '-' : ''}₱${amount.abs().toStringAsFixed(2)}',
            style: TextStyle(
                fontSize: 13,
                color: color,
                fontWeight: bold ? FontWeight.bold : FontWeight.normal),
          ),
        ],
      ),
    );
  }

  Widget _priceBreakdownCard() {
    final breakdown = _booking['price_breakdown'];
    final total = _parseDouble(_booking['total_price']);

    if (breakdown == null || (breakdown as List).isEmpty) {
      return _SummarySection(
        title: 'Payment Summary',
        children: [
          _SummaryRow('Status', _paymentStatus.toUpperCase()),
          _SummaryRow('Total', '₱${total.toStringAsFixed(2)}', showDivider: false),
        ],
      );
    }

    final children = <Widget>[
      _SummaryRow('Status', _paymentStatus.toUpperCase()),
    ];

    for (final item in (breakdown as List)) {
      final amount = _parseDouble(item['amount']);
      final label = item['label']?.toString() ?? '';
      final isDiscount = amount < 0;
      final valStr = '${isDiscount ? '-' : ''}₱${amount.abs().toStringAsFixed(2)}';
      children.add(_SummaryRow(label, valStr));
    }

    children.add(_SummaryRow('Total Amount', '₱${total.toStringAsFixed(2)}', showDivider: false));

    return _SummarySection(
      title: 'Payment Summary',
      children: children,
    );
  }

  Future<void> _openSupport() async {
    final subjectCtrl = TextEditingController(
        text: 'Booking ${_booking['transaction_number']} support');
    final messageCtrl = TextEditingController();
    final submit = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        title: const Text('Contact support'),
        content: Column(mainAxisSize: MainAxisSize.min, children: [
          TextField(
              controller: subjectCtrl,
              decoration: const InputDecoration(labelText: 'Subject')),
          TextField(
              controller: messageCtrl,
              maxLines: 4,
              decoration: const InputDecoration(labelText: 'Message')),
        ]),
        actions: [
          TextButton(
              onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Back')),
          FilledButton(
              onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Send'))
        ],
      ),
    );
    if (submit != true || messageCtrl.text.trim().isEmpty) return;
    try {
      final response =
          await http.post(Uri.parse('$_baseUrl/api/support'), headers: {
        'Accept': 'application/json'
      }, body: {
        'name': UserSession.username,
        'email': UserSession.email,
        'subject': subjectCtrl.text.trim(),
        'message':
            'Booking ${_booking['transaction_number']}: ${messageCtrl.text.trim()}',
      });
      final data = jsonDecode(response.body);
      _showMessage(
          response.statusCode == 200
              ? 'Support request sent.'
              : (data['message'] ?? 'Unable to contact support.'),
          error: response.statusCode != 200);
    } catch (e) {
      _showMessage('Connection error: $e', error: true);
    } finally {
      subjectCtrl.dispose();
      messageCtrl.dispose();
    }
  }

  @override
  Widget build(BuildContext context) {
    final status = (_booking['status'] ?? 'pending').toString();
    final expiry = _freeCancellationExpiresAt;
    final isWithin5Mins = expiry != null && DateTime.now().isBefore(expiry);
    final secondsLeft = expiry == null
        ? 0
        : expiry.difference(DateTime.now()).inSeconds.clamp(0, 300);
    final tx = _booking['transaction'];
    final transaction = Map<String, dynamic>.from(tx is Map ? tx : {});

    DateTime? paymentDeadlineAt;
    if (transaction['payment_deadline_at'] != null) {
      paymentDeadlineAt =
          DateTime.tryParse(transaction['payment_deadline_at'].toString());
    }

    bool isExpired = false;
    String countdownText = '--:--:--';
    if (paymentDeadlineAt != null) {
      final now = DateTime.now();
      final diff = paymentDeadlineAt.difference(now);
      if (diff.isNegative) {
        isExpired = true;
        countdownText = '00:00:00';
      } else {
        final h = diff.inHours.toString().padLeft(2, '0');
        final m = (diff.inMinutes % 60).toString().padLeft(2, '0');
        final s = (diff.inSeconds % 60).toString().padLeft(2, '0');
        countdownText = '$h:$m:$s';
      }
    }

    return Scaffold(
      appBar: AppBar(title: const Text('Booking details')),
      body: ListView(padding: const EdgeInsets.all(16), children: [
        _detailHeader(status),
        if (status == 'operator_cancelled' ||
            _booking['service_cancellation_id'] != null)
          Card(
            color: Colors.red.shade50,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: BorderSide(color: Colors.red.shade300),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      const Icon(Icons.warning_amber_rounded,
                          color: Colors.red, size: 24),
                      const SizedBox(width: 10),
                      Expanded(
                        child: Text(
                          'Service Cancelled by Operator',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              color: Colors.red.shade900,
                              fontSize: 16),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 12),
                  SizedBox(
                    width: double.infinity,
                    child: FilledButton.icon(
                      onPressed: () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) =>
                                ServiceCancellationScreen(booking: _booking),
                          ),
                        ).then((res) {
                          if (res == true) _refreshBooking();
                        });
                      },
                      icon: const Icon(Icons.swap_horiz_rounded),
                      label: const Text('Reschedule / Refund Options'),
                      style: FilledButton.styleFrom(
                          backgroundColor: Colors.red.shade700,
                          foregroundColor: Colors.white),
                    ),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    'This trip has been cancelled by the operator/admin due to service disruption. Please contact support for rescheduling or assistance.',
                    style: TextStyle(color: Colors.red.shade800, fontSize: 13),
                  ),
                ],
              ),
            ),
          ),
        const SizedBox(height: 12),
        _SummarySection(
          title: 'Trip Details',
          children: [
            _SummaryRow('Route', '${_booking['origin'] ?? ''} → ${_booking['destination'] ?? ''}'),
            _SummaryRow('Mode', (_booking['mode'] ?? _booking['schedule']?['route']?['mode'] ?? 'ferry').toString().toUpperCase()),
            _SummaryRow(
              'Date',
              () {
                final rawDep = (_booking['rebooking_departure_date'] ?? _booking['preferred_replacement_date'] ?? _booking['departure_date'] ?? '').toString();
                if (rawDep.isEmpty) return '-';
                final dt = DateTime.tryParse(rawDep)?.toLocal();
                if (dt == null) return rawDep;
                return '${dt.year}-${dt.month.toString().padLeft(2,'0')}-${dt.day.toString().padLeft(2,'0')}';
              }()
            ),
            _SummaryRow(
              'Trip Type',
              (_booking['return_date'] != null || _booking['rebooking_return_date'] != null)
                  ? 'Round Trip'
                  : 'One Way',
            ),
            // Departure schedule row: "ServiceName  dep_time – arr_time"
            _SummaryRow(
              'Departure Schedule',
              () {
                final svc = (_booking['schedule_service'] ?? _booking['schedule_summary'] ?? '').toString();
                final depT = _booking['departure_time']?.toString() ?? _booking['schedule_departure_time']?.toString() ?? '';
                final arrT = _booking['schedule_arrival_formatted']?.toString() ?? _booking['schedule_arrival_time']?.toString() ?? '';
                if (depT.isEmpty) return svc.isEmpty ? 'Not recorded' : svc;
                final rawDep = (_booking['rebooking_departure_date'] ?? _booking['preferred_replacement_date'] ?? _booking['departure_date'] ?? '').toString();
                final dt = DateTime.tryParse(rawDep)?.toLocal();
                final monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                final dateLabel = dt != null ? '${monthNames[dt.month - 1]} ${dt.day}, ${dt.year}' : rawDep;
                final timeRange = arrT.isNotEmpty ? '$depT – $arrT' : depT;
                return svc.isNotEmpty ? '$svc  $dateLabel $timeRange' : '$dateLabel $timeRange';
              }()
            ),
            // Departure ticket + class price per pax
            _SummaryRow(
              'Departure Ticket & Class',
              () {
                final basePrice = _parseDouble(_booking['schedule_price']);
                final tcPrice   = _parseDouble(_booking['departure_tc_price_per_pax']);
                final accPrice  = _parseDouble(_booking['schedule_accommodation_price']);
                final total = basePrice + tcPrice + accPrice;
                return total > 0 ? '₱${total.toStringAsFixed(2)} / pax' : '-';
              }(),
              showDivider: (_booking['return_date'] == null && _booking['rebooking_return_date'] == null),
            ),
            // Return trip rows (only for round trips)
            if (_booking['return_date'] != null || _booking['rebooking_return_date'] != null) ...[
              _SummaryRow(
                'Return Schedule',
                () {
                  final svc = (_booking['return_schedule_service'] ?? '').toString();
                  final depT = _booking['return_time']?.toString() ?? _booking['return_schedule_departure_time']?.toString() ?? '';
                  final arrT = _booking['return_schedule_arrival_formatted']?.toString() ?? _booking['return_schedule_arrival_time']?.toString() ?? '';
                  if (depT.isEmpty) return svc.isEmpty ? 'Not recorded' : svc;
                  final rawRet = (_booking['rebooking_return_date'] ?? _booking['return_date'] ?? '').toString();
                  final dt = DateTime.tryParse(rawRet)?.toLocal();
                  final monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
                  final dateLabel = dt != null ? '${monthNames[dt.month - 1]} ${dt.day}, ${dt.year}' : rawRet;
                  final timeRange = arrT.isNotEmpty ? '$depT – $arrT' : depT;
                  return svc.isNotEmpty ? '$svc  $dateLabel $timeRange' : '$dateLabel $timeRange';
                }()
              ),
              _SummaryRow(
                'Return Ticket & Class',
                () {
                  final basePrice = _parseDouble(_booking['return_schedule_price']);
                  final tcPrice   = _parseDouble(_booking['return_tc_price_per_pax']);
                  final accPrice  = _parseDouble(_booking['return_schedule_accommodation_price']);
                  final total = basePrice + tcPrice + accPrice;
                  return total > 0 ? '₱${total.toStringAsFixed(2)} / pax' : '-';
                }(),
                showDivider: false,
              ),
            ],
          ],
        ),

        _priceBreakdownCard(),
        if (_booking['passengers'] != null &&
            _booking['passengers'] is List &&
            (_booking['passengers'] as List).isNotEmpty)
          _detailSection(
            'Passengers & Discount IDs',
            (_booking['passengers'] as List).map<String>((p) {
              if (p is! Map) return 'Invalid passenger entry';
              final name = p['name'] ?? 'Passenger';
              final type = (p['type'] ?? 'adult').toString().toUpperCase();
              final bday = p['birthdate'] ?? 'N/A';
              final idNum = p['id_number'];
              final front = p['id_image_front_url'] ?? p['id_image_front'];
              final back = p['id_image_back_url'] ?? p['id_image_back'];
              String str = '$name ($type) • Bday: $bday';
              if (idNum != null && idNum.toString().isNotEmpty) {
                str += ' • ID: $idNum';
              }
              if (front != null) str += ' • Front ID: Attached';
              if (back != null) str += ' • Back ID: Attached';
              return str;
            }).toList(),
          ),
        if (paymentDeadlineAt != null && _paymentStatus == 'unpaid') ...[
          Card(
            color: isExpired ? Colors.red.shade50 : Colors.orange.shade50,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                children: [
                  Text(
                    isExpired
                        ? 'Payment Window Expired'
                        : 'Time Remaining to Pay',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: isExpired
                            ? Colors.red.shade800
                            : Colors.orange.shade900),
                  ),
                  const SizedBox(height: 8),
                  Text(
                    countdownText,
                    style: TextStyle(
                        fontSize: 28,
                        fontWeight: FontWeight.w900,
                        color: isExpired ? Colors.red : Colors.orange.shade800,
                        letterSpacing: 2),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 12),
        ],
        if (_paymentStatus == 'unpaid' && !isExpired) ...[
          if (_qrCodeUrl != null) ...[
            Center(
              child: Image.network(
                _qrCodeUrl!,
                height: 250,
                fit: BoxFit.contain,
                errorBuilder: (c, o, s) => const SizedBox(
                    height: 250,
                    child: Center(
                        child: Text('Unable to load QR code',
                            style: TextStyle(color: Colors.red)))),
              ),
            ),
            const SizedBox(height: 12),
          ],
          OutlinedButton.icon(
              onPressed: _busy ? null : _uploadPaymentProof,
              icon: const Icon(Icons.upload_file),
              label: const Text('Upload payment proof')),
        ],
        if (_booking['rebooking_status'] == 'pending')
          _detailSection('Rebooking', <String>[
            'Request pending verification',
            'New dates will appear after approval.'
          ]),
        if (_booking['status'] != 'cancelled' &&
            _booking['status'] != 'operator_cancelled') ...[
          if (_booking['ticket_url'] != null && _paymentStatus == 'paid')
            OutlinedButton.icon(
              onPressed: () =>
                  launchUrl(Uri.parse(_booking['ticket_url'].toString())),
              icon: const Icon(Icons.receipt_long),
              label: const Text('Payment Acknowledgement'),
            ),
          if ((transaction['confirmation_url'] != null ||
                  _booking['confirmation_url'] != null ||
                  _booking['confirmation_pdf_url'] != null) &&
              _paymentStatus == 'paid')
            FilledButton.icon(
              onPressed: () {
                final url = transaction['confirmation_url'] ??
                    _booking['confirmation_url'] ??
                    _booking['confirmation_pdf_url'];
                launchUrl(Uri.parse(url.toString()));
              },
              icon: const Icon(Icons.download),
              label: const Text('Download Ticket'),
              style: FilledButton.styleFrom(
                  backgroundColor: kGreen, foregroundColor: Colors.white),
            ),
        ],
        const SizedBox(height: 12),
        if (_canManage && !_cancellationStarted) ...[
          // Show rebook/refund/cancel when payment is uploaded (pending) OR verified (paid).
          // Pre-84ab183 behavior: not restricted to admin-verified bookings only.
          if (_paymentStatus == 'paid' && _booking['can_rebook'] == true)
            OutlinedButton.icon(
                onPressed: _busy
                    ? null
                    : () {
                        Navigator.push(
                          context,
                          MaterialPageRoute(
                            builder: (_) => RebookScreen(booking: _booking),
                          ),
                        ).then((res) {
                          if (res == true) _refreshBooking();
                        });
                      },
                icon: const Icon(Icons.calendar_month),
                label: const Text('Request rebooking')),
          if (_booking['can_cancel'] == true ||
              ['unpaid', 'pending', 'paid'].contains(_paymentStatus))
            OutlinedButton.icon(
              onPressed: _busy
                  ? null
                  : () {
                      Navigator.push(
                        context,
                        MaterialPageRoute(
                          builder: (_) => RefundScreen(booking: _booking),
                        ),
                      ).then((res) {
                        if (res == true) _refreshBooking();
                      });
                    },
              icon: Icon(isWithin5Mins
                  ? Icons.cancel_outlined
                  : Icons.monetization_on_outlined),
              label: Text(isWithin5Mins
                  ? 'Cancel Booking (Free) - ${secondsLeft ~/ 60}:${(secondsLeft % 60).toString().padLeft(2, '0')}'
                  : 'Request Refund'),
              style: OutlinedButton.styleFrom(
                  foregroundColor: isWithin5Mins ? Colors.red : Colors.orange),
            ),
        ],
        if (_cancellationStarted) ...[
          Card(
            color: Colors.red.shade50,
            margin: const EdgeInsets.only(bottom: 12),
            shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(12),
                side: BorderSide(color: Colors.red.shade200)),
            child: Padding(
              padding: const EdgeInsets.all(14),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (isWithin5Mins) ...[
                    Row(
                      children: [
                        Icon(Icons.timer_outlined,
                            color: Colors.red.shade700, size: 18),
                        const SizedBox(width: 8),
                        Text(
                            'Free cancellation expires in ${secondsLeft ~/ 60}:${(secondsLeft % 60).toString().padLeft(2, '0')}',
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Colors.red.shade700)),
                      ],
                    ),
                    const SizedBox(height: 10),
                  ],
                  if (_cancellationFee != null)
                    _feeRow('Cancellation Fee', _cancellationFee!,
                        color: Colors.red.shade700),
                  if (_refundAmount != null)
                    _feeRow('Estimated Refund', _refundAmount!,
                        color: Colors.green.shade700, bold: true),
                ],
              ),
            ),
          ),
          DropdownButtonFormField<String>(
              value: _refundMethod,
              decoration: const InputDecoration(labelText: 'Refund method'),
              items: const [
                DropdownMenuItem(value: 'GCash', child: Text('GCash')),
                DropdownMenuItem(
                    value: 'Online Wallet', child: Text('Online Wallet')),
                DropdownMenuItem(
                    value: 'Bank Account', child: Text('Bank Account'))
              ],
              onChanged: (value) =>
                  setState(() => _refundMethod = value ?? 'GCash')),
          if (_refundMethod != 'GCash')
            TextField(
                controller: _refundInstitutionCtrl,
                decoration: const InputDecoration(
                    labelText: 'Bank or wallet provider')),
          TextField(
              controller: _refundAccountCtrl,
              decoration: InputDecoration(
                  labelText: _refundMethod == 'GCash'
                      ? 'GCash number'
                      : 'Account number')),
          TextField(
              controller: _refundNameCtrl,
              decoration: const InputDecoration(labelText: 'Account name')),
          const SizedBox(height: 12),
          FilledButton.icon(
              onPressed: _busy ? null : _confirmCancellation,
              icon: const Icon(Icons.check),
              label: Text(
                  isWithin5Mins ? 'Confirm cancellation' : 'Confirm refund')),
        ],
        const SizedBox(height: 12),
        OutlinedButton.icon(
            onPressed: _openSupport,
            icon: const Icon(Icons.support_agent),
            label: const Text('Contact support')),
      ]),
    );
  }

  Widget _detailHeader(String status) {
    final isOpCancelled = status == 'operator_cancelled' ||
        _booking['service_cancellation_id'] != null;
    final bg = isOpCancelled || status == 'cancelled' ? Colors.red : kGreen;
    return Card(
        color: bg,
        child: Padding(
            padding: const EdgeInsets.all(18),
            child:
                Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
              Text(_booking['transaction_number'] ?? '',
                  style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                      fontSize: 18)),
              const SizedBox(height: 8),
              Text(
                  isOpCancelled
                      ? 'CANCELLED BY OPERATOR'
                      : status.toUpperCase(),
                  style: const TextStyle(
                      color: Colors.white70, fontWeight: FontWeight.bold))
            ])));
  }

  Widget _detailSection(String title, List<String> values) => Card(
      margin: const EdgeInsets.only(bottom: 12),
      child: Padding(
          padding: const EdgeInsets.all(16),
          child:
              Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            Text(title,
                style: const TextStyle(
                    fontWeight: FontWeight.bold, color: kSlate800)),
            const SizedBox(height: 8),
            ...values.map((value) => Padding(
                padding: const EdgeInsets.only(bottom: 4),
                child: Text(value, style: const TextStyle(color: kSlate600))))
          ])));
}

class _NotificationDot extends StatelessWidget {
  final double size;
  const _NotificationDot({this.size = 10});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: size,
      height: size,
      decoration:
          const BoxDecoration(color: Colors.red, shape: BoxShape.circle),
    );
  }
}

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  late final TextEditingController _nameCtrl;
  late final TextEditingController _emailCtrl;
  late final TextEditingController _phoneCtrl;

  @override
  void initState() {
    super.initState();
    _nameCtrl = TextEditingController(text: UserSession.username);
    _emailCtrl = TextEditingController(text: UserSession.email);
    _phoneCtrl = TextEditingController(text: UserSession.phone);
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _phoneCtrl.dispose();
    super.dispose();
  }

  Future<void> _saveProfile() async {
    final name = _nameCtrl.text.trim();
    final email = _emailCtrl.text.trim();
    final phone = _phoneCtrl.text.trim();

    UserSession.username = name.isNotEmpty ? name : 'Traveler';
    UserSession.email = email.isNotEmpty ? email : 'user@amigagracia.com';
    UserSession.phone = phone;
    await UserSession.save();

    if (UserSession.token.isNotEmpty) {
      try {
        final res = await http.post(
          Uri.parse('${UserSession.getBaseUrl()}/api/profile/update'),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json',
            'Authorization': 'Bearer ${UserSession.token}',
          },
          body: jsonEncode({
            'name': UserSession.username,
            'phone': UserSession.phone,
          }),
        );
      } catch (_) {}
    }

    if (mounted) {
      showTopSnack(
        context,
        const SnackBar(
            content: Text('Profile updated'), backgroundColor: kGreen),
      );
      Navigator.pop(context);
    }
  }

  @override
  Widget build(BuildContext context) {
    final missingPhone = UserSession.phone.trim().isEmpty;

    return Scaffold(
      appBar: AppBar(title: const Text('My Profile')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Card(
            color: Colors.white,
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Row(
                    children: [
                      Text('Profile Details',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                              color: kSlate800)),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _nameCtrl,
                    decoration: InputDecoration(
                      labelText: 'Name',
                      prefixIcon:
                          const Icon(Icons.person_outline, color: kGreen),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _emailCtrl,
                    keyboardType: TextInputType.emailAddress,
                    decoration: InputDecoration(
                      labelText: 'Email',
                      prefixIcon:
                          const Icon(Icons.email_outlined, color: kGreen),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _phoneCtrl,
                    keyboardType: TextInputType.phone,
                    decoration: InputDecoration(
                      label: Row(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          const Text('Mobile Phone Number'),
                          if (missingPhone) ...[
                            const SizedBox(width: 6),
                            const _NotificationDot(size: 8),
                          ],
                        ],
                      ),
                      prefixIcon: const Icon(Icons.phone_android_outlined,
                          color: kGreen),
                      border: OutlineInputBorder(
                          borderRadius: BorderRadius.circular(10)),
                    ),
                  ),
                ],
              ),
            ),
          ),
          if (UserSession.referralCode != null &&
              UserSession.referralCode!.isNotEmpty) ...[
            const SizedBox(height: 16),
            Card(
              color: Colors.white,
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Row(
                      children: [
                        Icon(Icons.card_giftcard, color: kPink, size: 20),
                        SizedBox(width: 8),
                        Text('My Referral Code',
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 16,
                                color: kSlate800)),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        Expanded(
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 12),
                            decoration: BoxDecoration(
                              color: kSlate100,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: kSlate200),
                            ),
                            child: Text(
                              UserSession.referralCode!,
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 18,
                                  letterSpacing: 2.0),
                              textAlign: TextAlign.center,
                            ),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 8),
                    const Text(
                      'Share this code with friends! When they book their first trip, you will both receive a reward voucher.',
                      style: TextStyle(fontSize: 12, color: kSlate500),
                    ),
                  ],
                ),
              ),
            ),
          ],
          const SizedBox(height: 16),
          SizedBox(
            height: 48,
            child: ElevatedButton(
              onPressed: _saveProfile,
              style: ElevatedButton.styleFrom(
                backgroundColor: kPink,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Save Profile',
                  style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
          const SizedBox(height: 12),
          SizedBox(
            height: 48,
            child: OutlinedButton(
              onPressed: () {
                showDialog(
                  context: context,
                  builder: (ctx) {
                    bool modalLoading = false;
                    bool isOtpSent = false;
                    String otp = '';
                    String newPassword = '';
                    String confirmPassword = '';

                    return StatefulBuilder(builder: (ctx, setModalState) {
                      if (!isOtpSent) {
                        return AlertDialog(
                          title: const Text('Reset Password'),
                          content: const Text(
                              'Are you sure you want to reset your password? We will send an OTP to your email.'),
                          actions: [
                            TextButton(
                                onPressed: modalLoading
                                    ? null
                                    : () => Navigator.pop(ctx),
                                child: const Text('Cancel')),
                            ElevatedButton(
                              onPressed: modalLoading
                                  ? null
                                  : () async {
                                      setModalState(() => modalLoading = true);
                                      try {
                                        final res = await http.post(
                                          Uri.parse(
                                              '${UserSession.getBaseUrl()}/api/forgot-password/request-otp'),
                                          headers: {
                                            'Accept': 'application/json'
                                          },
                                          body: {'email': UserSession.email},
                                        );
                                        final data = jsonDecode(res.body);
                                        if (res.statusCode == 200 &&
                                            data['status'] == 'success') {
                                          setModalState(() {
                                            isOtpSent = true;
                                            modalLoading = false;
                                          });
                                          if (!mounted) return;
                                          showTopSnack(
                                            context,
                                            SnackBar(
                                                content: Text(data['message'] ??
                                                    'OTP sent! Check your email.'),
                                                backgroundColor: kGreen),
                                          );
                                        } else {
                                          final msg = data['message'] ??
                                              'Failed to send OTP.';
                                          if (!mounted) return;
                                          showTopSnack(
                                              context,
                                              SnackBar(
                                                  content: Text(msg),
                                                  backgroundColor: Colors.red));
                                          Navigator.pop(ctx);
                                        }
                                      } catch (e) {
                                        setModalState(
                                            () => modalLoading = false);
                                        if (!mounted) return;
                                        showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text('Network error'),
                                                backgroundColor: Colors.red));
                                      }
                                    },
                              child: modalLoading
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                          strokeWidth: 2))
                                  : const Text('Send OTP'),
                            ),
                          ],
                        );
                      } else {
                        return AlertDialog(
                          title: const Text('Create New Password'),
                          content: SingleChildScrollView(
                            child: Column(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                const Text(
                                    'Enter the verification code sent to your email and your new password.',
                                    style: TextStyle(fontSize: 14)),
                                const SizedBox(height: 16),
                                TextField(
                                  decoration: const InputDecoration(
                                      labelText: 'Verification Code (OTP)'),
                                  onChanged: (val) => otp = val,
                                ),
                                const SizedBox(height: 12),
                                TextField(
                                  obscureText: true,
                                  decoration: const InputDecoration(
                                      labelText: 'New Password'),
                                  onChanged: (val) => newPassword = val,
                                ),
                                const SizedBox(height: 12),
                                TextField(
                                  obscureText: true,
                                  decoration: const InputDecoration(
                                      labelText: 'Confirm Password'),
                                  onChanged: (val) => confirmPassword = val,
                                ),
                              ],
                            ),
                          ),
                          actions: [
                            TextButton(
                                onPressed: modalLoading
                                    ? null
                                    : () => Navigator.pop(ctx),
                                child: const Text('Cancel')),
                            ElevatedButton(
                              onPressed: modalLoading
                                  ? null
                                  : () async {
                                      if (otp.isEmpty ||
                                          newPassword.isEmpty ||
                                          confirmPassword.isEmpty) {
                                        showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text(
                                                    'All fields are required.'),
                                                backgroundColor: Colors.red));
                                        return;
                                      }
                                      if (newPassword != confirmPassword) {
                                        showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text(
                                                    'Passwords do not match.'),
                                                backgroundColor: Colors.red));
                                        return;
                                      }
                                      if (newPassword.length < 6) {
                                        showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text(
                                                    'Password must be at least 6 characters.'),
                                                backgroundColor: Colors.red));
                                        return;
                                      }

                                      setModalState(() => modalLoading = true);
                                      try {
                                        final res = await http.post(
                                          Uri.parse(
                                              '${UserSession.getBaseUrl()}/api/forgot-password/reset'),
                                          headers: {
                                            'Accept': 'application/json'
                                          },
                                          body: {
                                            'email': UserSession.email,
                                            'otp': otp.trim(),
                                            'password': newPassword,
                                            'password_confirmation':
                                                confirmPassword,
                                          },
                                        );
                                        final data = jsonDecode(res.body);
                                        if (res.statusCode == 200 &&
                                            data['status'] == 'success') {
                                          if (!mounted) return;
                                          showTopSnack(
                                            context,
                                            SnackBar(
                                                content: Text(data['message'] ??
                                                    'Password reset successfully.'),
                                                backgroundColor: kGreen),
                                          );
                                          Navigator.pop(ctx);
                                        } else {
                                          final msg = data['message'] ??
                                              'Failed to reset password.';
                                          if (!mounted) return;
                                          showTopSnack(
                                              context,
                                              SnackBar(
                                                  content: Text(msg),
                                                  backgroundColor: Colors.red));
                                          setModalState(
                                              () => modalLoading = false);
                                        }
                                      } catch (e) {
                                        setModalState(
                                            () => modalLoading = false);
                                        if (!mounted) return;
                                        showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text('Network error'),
                                                backgroundColor: Colors.red));
                                      }
                                    },
                              child: modalLoading
                                  ? const SizedBox(
                                      width: 20,
                                      height: 20,
                                      child: CircularProgressIndicator(
                                          strokeWidth: 2))
                                  : const Text('Reset Password'),
                            ),
                          ],
                        );
                      }
                    });
                  },
                );
              },
              style: OutlinedButton.styleFrom(
                foregroundColor: Colors.red,
                side: const BorderSide(color: Colors.red),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
              ),
              child: const Text('Reset Password',
                  style: TextStyle(fontWeight: FontWeight.bold)),
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// DRAWER
// ==========================================
class AppDrawer extends StatelessWidget {
  final VoidCallback onLogout;
  final VoidCallback onProfileUpdated;
  const AppDrawer(
      {super.key, required this.onLogout, required this.onProfileUpdated});

  @override
  Widget build(BuildContext context) {
    return Drawer(
      child: Column(
        children: [
          UserAccountsDrawerHeader(
            decoration: const BoxDecoration(color: kGreen),
            currentAccountPicture: CircleAvatar(
              backgroundColor: Colors.white,
              child: Text(
                UserSession.isLoggedIn ? UserSession.username[0] : '?',
                style: const TextStyle(
                    fontSize: 28, fontWeight: FontWeight.bold, color: kGreen),
              ),
            ),
            accountName: Text(
              UserSession.isLoggedIn
                  ? 'Hi, ${UserSession.username}!'
                  : 'Guest User',
              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
            ),
            accountEmail: Text(
              UserSession.isLoggedIn
                  ? UserSession.email
                  : 'Sign in to access your activities',
              style: const TextStyle(fontSize: 12, color: Colors.white70),
            ),
          ),
          if (UserSession.isLoggedIn)
            ListTile(
              leading: const Icon(Icons.person_outline, color: kGreen),
              title: const Text('My Profile'),
              trailing:
                  UserSession.isLoggedIn && UserSession.phone.trim().isEmpty
                      ? const _NotificationDot()
                      : null,
              onTap: () {
                Navigator.pop(context);
                Navigator.push(
                    context,
                    MaterialPageRoute(
                        builder: (_) => const ProfileScreen())).then((_) {
                  onProfileUpdated();
                });
              },
            ),
          ListTile(
            leading: const Icon(Icons.info_outline, color: kGreen),
            title: const Text('About'),
            onTap: () {
              Navigator.pop(context);
              Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const AboutScreen()));
            },
          ),
          ListTile(
            leading: const Icon(Icons.phone_outlined, color: kGreen),
            title: const Text('Contacts'),
            onTap: () {
              Navigator.pop(context);
              Navigator.push(context,
                  MaterialPageRoute(builder: (_) => const ContactScreen()));
            },
          ),
          ListTile(
            leading: const Icon(Icons.language, color: kGreen),
            title: const Text('Visit Website'),
            onTap: () async {
              Navigator.pop(context);
              final url = Uri.parse('https://www.amigagracia.com');
              if (await canLaunchUrl(url)) {
                await launchUrl(url, mode: LaunchMode.inAppWebView);
              }
            },
          ),
          const Spacer(),
          if (UserSession.isLoggedIn)
            ListTile(
              leading: const Icon(Icons.logout, color: Colors.redAccent),
              title: const Text('Log out',
                  style: TextStyle(color: Colors.redAccent)),
              onTap: () {
                Navigator.pop(context);
                onLogout(); // Clears full session & navigates to Home tab
              },
            ),
          const Padding(
            padding: EdgeInsets.all(16),
            child: Column(
              children: [
                Text(
                  'Version ${UserSession.appVersion}',
                  style: TextStyle(
                      color: kSlate400,
                      fontSize: 12,
                      fontWeight: FontWeight.bold),
                  textAlign: TextAlign.center,
                ),
                SizedBox(height: 4),
                Text(
                  '© 2025 Amiga Gracia Travel Services',
                  style: TextStyle(color: kSlate400, fontSize: 11),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// STEP PROGRESS INDICATOR
// ==========================================
class _StepProgress extends StatelessWidget {
  final int currentStep;
  final List<String> steps;
  final String mode;

  const _StepProgress(
      {required this.currentStep, required this.steps, this.mode = 'ferry'});

  IconData _getStepIcon(String stepName) {
    switch (stepName.toLowerCase()) {
      case 'route':
        return mode == 'airline' ? Icons.flight : Icons.directions_boat;
      case 'schedule':
        return Icons.calendar_month;
      case 'discount':
        return Icons.local_offer;
      case 'hotels':
        return Icons.hotel;
      case 'submit':
        return Icons.fact_check;
      default:
        return Icons.circle;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: List.generate(steps.length * 2 - 1, (i) {
          if (i.isOdd) {
            return Expanded(
              child: Container(
                height: 28,
                alignment: Alignment.center,
                child: Container(
                  height: 2,
                  color: i ~/ 2 < currentStep - 1 ? kGreen : kSlate200,
                ),
              ),
            );
          }
          final step = i ~/ 2 + 1;
          final active = step == currentStep;
          final done = step < currentStep;
          return Column(
            children: [
              Container(
                width: 28,
                height: 28,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  color: done
                      ? kGreen
                      : active
                          ? kPink
                          : kSlate200,
                ),
                child: Center(
                  child: done
                      ? const Icon(Icons.check, color: Colors.white, size: 14)
                      : Icon(_getStepIcon(steps[i ~/ 2]),
                          color: active ? Colors.white : kSlate500, size: 14),
                ),
              ),
              const SizedBox(height: 4),
              Text(steps[i ~/ 2],
                  style: TextStyle(
                      fontSize: 9,
                      color: active
                          ? kPink
                          : done
                              ? kGreen
                              : kSlate400,
                      fontWeight:
                          active ? FontWeight.bold : FontWeight.normal)),
            ],
          );
        }),
      ),
    );
  }
}

// ==========================================
// STEP 2: SCHEDULE SELECT
// ==========================================
class ScheduleSelectScreen extends StatefulWidget {
  final BookingData booking;
  const ScheduleSelectScreen({super.key, required this.booking});

  @override
  State<ScheduleSelectScreen> createState() => _ScheduleSelectScreenState();
}

class _ScheduleSelectScreenState extends State<ScheduleSelectScreen> {
  List<dynamic> _schedules = [];
  List<dynamic> _returnSchedules = [];
  Map<String, dynamic> _baggageRules = {};
  bool _isLoading = true;
  String? _error;

  static const _steps = ['Route', 'Schedule', 'Discount', 'Hotels', 'Submit'];

  @override
  void initState() {
    super.initState();
    _fetchSchedules();
  }

  void _fetchSchedules() async {
    setState(() {
      _isLoading = true;
      _error = null;
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
        Uri.parse('$baseUrl/api/schedules'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: jsonEncode({
          'origin': widget.booking.origin,
          'destination': widget.booking.destination,
          'date': widget.booking.departureDate,
          'mode': widget.booking.mode,
          if (widget.booking.operator != null)
            'operator': widget.booking.operator,
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        _schedules = parseAndFilterSchedules(
            data['schedules'], widget.booking.departureDate);
      } else {
        setState(() => _error = data['message'] ?? 'Failed to load schedules.');
        return;
      }

      if (widget.booking.tripType == 'round_trip' &&
          widget.booking.returnDate != null) {
        final returnRes = await http.post(
          Uri.parse('$baseUrl/api/schedules'),
          headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
          },
          body: jsonEncode({
            'origin': widget.booking.destination,
            'destination': widget.booking.origin,
            'date': widget.booking.returnDate,
            'mode': widget.booking.mode,
            if (widget.booking.operator != null)
              'operator': widget.booking.operator,
          }),
        );
        final returnData = jsonDecode(returnRes.body);
        if (returnRes.statusCode == 200 && returnData['status'] == 'success') {
          _returnSchedules = parseAndFilterSchedules(
              returnData['schedules'], widget.booking.returnDate);
        } else {
          setState(() => _error =
              returnData['message'] ?? 'Failed to load returning schedules.');
          return;
        }
      }

      if (widget.booking.mode == 'airline') {
        final baggageRes =
            await http.get(Uri.parse('$baseUrl/api/baggage-rules'));
        if (baggageRes.statusCode == 200) {
          final bData = jsonDecode(baggageRes.body);
          if (bData['status'] == 'success') {
            _baggageRules = bData['rules'];
          }
        }
      }

      setState(() {});
    } catch (e) {
      setState(() => _error = 'Error connecting to server: $e');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Select Schedule')),
      body: Column(
        children: [
          _StepProgress(
              currentStep: 2, steps: _steps, mode: widget.booking.mode),
          Container(
            margin: const EdgeInsets.all(16),
            padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
            decoration: BoxDecoration(
                color: kGreen.withOpacity(0.07),
                borderRadius: BorderRadius.circular(12)),
            child: Row(
              children: [
                Icon(
                    widget.booking.mode == 'ferry'
                        ? Icons.directions_boat
                        : Icons.flight,
                    color: kGreen,
                    size: 20),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    '${widget.booking.origin} → ${widget.booking.destination}  ·  ${widget.booking.departureDate}',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        color: kGreen,
                        fontSize: 13),
                  ),
                ),
              ],
            ),
          ),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: kGreen))
                : _error != null
                    ? Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(_error!,
                                style: const TextStyle(color: Colors.red),
                                textAlign: TextAlign.center),
                            const SizedBox(height: 16),
                            ElevatedButton(
                              onPressed: _fetchSchedules,
                              style: ElevatedButton.styleFrom(
                                  backgroundColor: kGreen),
                              child: const Text('Retry',
                                  style: TextStyle(color: Colors.white)),
                            ),
                          ],
                        ),
                      )
                    : SingleChildScrollView(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            if (widget.booking.tripType == 'round_trip') ...[
                              _buildBaggageReminder(),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16.0, vertical: 8.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('Departure Trip',
                                        style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: kSlate800)),
                                    const SizedBox(height: 4),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                          color: const Color(0xFFE0EFFF),
                                          borderRadius:
                                              BorderRadius.circular(8)),
                                      child: Text(
                                          'From ${widget.booking.origin} • To ${widget.booking.destination}',
                                          style: const TextStyle(
                                              color: Color(0xFF5C1C85),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13)),
                                    ),
                                  ],
                                ),
                              ),
                              _buildHorizontalScheduleList(_schedules,
                                  isReturn: false),
                              if (widget.booking.selectedSchedule != null &&
                                  widget.booking.selectedSchedule![
                                          'promotional_ticket'] !=
                                      null)
                                _buildPromoTicketBanner(widget.booking
                                    .selectedSchedule!['promotional_ticket']),
                              if (widget.booking.selectedSchedule != null)
                                _buildTransportClassesSelection(isReturn: false),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16.0, vertical: 8.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('Returning Trip',
                                        style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: kSlate800)),
                                    const SizedBox(height: 4),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                          color: const Color(0xFFE0EFFF),
                                          borderRadius:
                                              BorderRadius.circular(8)),
                                      child: Text(
                                          'From ${widget.booking.destination} • To ${widget.booking.origin}',
                                          style: const TextStyle(
                                              color: Color(0xFF5C1C85),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13)),
                                    ),
                                  ],
                                ),
                              ),
                              _buildHorizontalScheduleList(_returnSchedules,
                                  isReturn: true),
                              if (widget.booking.selectedReturnSchedule !=
                                      null &&
                                  widget.booking.selectedReturnSchedule![
                                          'promotional_ticket'] !=
                                      null)
                                _buildPromoTicketBanner(
                                    widget.booking.selectedReturnSchedule![
                                        'promotional_ticket'],
                                    isReturn: true),
                              _buildTransportClassesSelection(isReturn: true),
                              const SizedBox(height: 20),
                              _buildBaggageSelection(),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16.0),
                                child: ElevatedButton(
                                  onPressed: (widget.booking.selectedSchedule !=
                                              null &&
                                          widget.booking
                                                  .selectedReturnSchedule !=
                                              null)
                                      ? () {
                                          widget.booking.savedStep = 2;
                                          widget.booking.saveToPrefs(2);
                                          Navigator.push(
                                                  context,
                                                  MaterialPageRoute(
                                                      builder: (_) =>
                                                          DiscountScreen(
                                                              booking: widget
                                                                  .booking)))
                                              .then((_) {
                                            if (mounted) {
                                              widget.booking.savedStep = 1;
                                              widget.booking.saveToPrefs(1);
                                            }
                                          });
                                        }
                                      : null,
                                  style: ElevatedButton.styleFrom(
                                    backgroundColor: kPink,
                                    minimumSize:
                                        const Size(double.infinity, 50),
                                    shape: RoundedRectangleBorder(
                                        borderRadius:
                                            BorderRadius.circular(12)),
                                  ),
                                  child: const Text('Continue to Discounts',
                                      style: TextStyle(
                                          color: Colors.white,
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold)),
                                ),
                              ),
                            ] else ...[
                              _buildBaggageReminder(),
                              Padding(
                                padding: const EdgeInsets.symmetric(
                                    horizontal: 16.0, vertical: 8.0),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    const Text('Available Schedules',
                                        style: TextStyle(
                                            fontSize: 18,
                                            fontWeight: FontWeight.bold,
                                            color: kSlate800)),
                                    const SizedBox(height: 4),
                                    Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 12, vertical: 6),
                                      decoration: BoxDecoration(
                                          color: const Color(0xFFE0EFFF),
                                          borderRadius:
                                              BorderRadius.circular(8)),
                                      child: Text(
                                          'From ${widget.booking.origin} • To ${widget.booking.destination}',
                                          style: const TextStyle(
                                              color: Color(0xFF5C1C85),
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13)),
                                    ),
                                  ],
                                ),
                              ),
                              _buildHorizontalScheduleList(_schedules,
                                  isReturn: false),
                              if (widget.booking.selectedSchedule != null &&
                                  widget.booking.selectedSchedule![
                                          'promotional_ticket'] !=
                                      null)
                                _buildPromoTicketBanner(widget.booking
                                    .selectedSchedule!['promotional_ticket']),
                              if (widget.booking.selectedSchedule != null) ...[
                                _buildTransportClassesSelection(
                                    isReturn: false),
                                _buildBaggageSelection(),
                                Padding(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 16.0, vertical: 12.0),
                                  child: ElevatedButton(
                                    onPressed: () {
                                      widget.booking.savedStep = 2;
                                      widget.booking.saveToPrefs(2);
                                      Navigator.push(
                                          context,
                                          MaterialPageRoute(
                                              builder: (_) => DiscountScreen(
                                                  booking: widget
                                                      .booking))).then((_) {
                                        if (mounted) {
                                          widget.booking.savedStep = 1;
                                          widget.booking.saveToPrefs(1);
                                        }
                                      });
                                    },
                                    style: ElevatedButton.styleFrom(
                                      backgroundColor: kPink,
                                      minimumSize:
                                          const Size(double.infinity, 50),
                                      shape: RoundedRectangleBorder(
                                          borderRadius:
                                              BorderRadius.circular(12)),
                                    ),
                                    child: const Text('Continue to Discounts',
                                        style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 16,
                                            fontWeight: FontWeight.bold)),
                                  ),
                                ),
                              ],
                            ],
                            const SizedBox(height: 20),
                          ],
                        ),
                      ),
          ),
        ],
      ),
    );
  }

  Widget _buildPromoTicketBanner(Map<String, dynamic> promo,
      {bool isReturn = false}) {
    return Container(
      margin: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFdb2777).withOpacity(0.06),
        border: Border.all(color: const Color(0xFFdb2777), width: 2),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Text('✨ ', style: TextStyle(fontSize: 18)),
              Expanded(
                child: Text(
                  isReturn
                      ? 'Return Trip Promo Ticket Available!'
                      : 'Promotional Ticket Available!',
                  style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 16,
                      color: kSlate800),
                ),
              ),
              Switch(
                value: widget.booking.usePromoTicket,
                activeColor: const Color(0xFFdb2777),
                onChanged: (val) {
                  setState(() {
                    widget.booking.usePromoTicket = val;
                    widget.booking.promotionalTicketId =
                        val ? promo['id'] : null;
                  });
                },
              ),
            ],
          ),
          const SizedBox(height: 6),
          Text(
            'Promo price: ₱${promo['promo_price']}   •   Remaining: ${promo['quantity_remaining']} tickets',
            style: const TextStyle(
                fontWeight: FontWeight.w600,
                fontSize: 13,
                color: Color(0xFFdb2777)),
          ),
          const SizedBox(height: 4),
          Text(
            widget.booking.usePromoTicket
                ? 'Using promotional fare (₱${promo['promo_price']} / pax)'
                : 'Using regular ticket fare',
            style: const TextStyle(fontSize: 12, color: kSlate600),
          ),
        ],
      ),
    );
  }

  Widget _buildTransportClassesSelection({required bool isReturn}) {
    final schedule = isReturn
        ? widget.booking.selectedReturnSchedule
        : widget.booking.selectedSchedule;
    if (schedule == null) return const SizedBox.shrink();

    final isAirline = widget.booking.mode == 'airline';
    final classes = schedule['transport_classes'] as List<dynamic>? ?? [];
    final accommodations = schedule['accommodations'] as List<dynamic>? ?? [];

    if (classes.isNotEmpty) {
      return _buildClassesSelection(classes, isReturn: isReturn);
    } else if (accommodations.isNotEmpty) {
      return _buildAccommodationsSelection(accommodations, isReturn: isReturn);
    }
    return const SizedBox.shrink();
  }

  Widget _buildClassesSelection(List<dynamic> classes,
      {required bool isReturn}) {
    final isFerry = widget.booking.mode == 'ferry';
    final val = isFerry
        ? (isReturn
            ? widget.booking.selectedReturnFerryAccommodationId
            : widget.booking.selectedFerryAccommodationId)
        : (isReturn
            ? widget.booking.selectedReturnAirlineClassId
            : widget.booking.selectedAirlineClassId);
    final schedule = isReturn
        ? widget.booking.selectedReturnSchedule
        : widget.booking.selectedSchedule;
    final schedulePrice =
        schedule != null ? _parseDouble(schedule['price']) : 0.0;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Text('Select travel class for this trip:',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.4,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: classes.length,
            itemBuilder: (context, index) {
              final c = classes[index];
              final isSelected = c['id'] == val;

              final isPromo = c['is_promo'] == true || c['is_promo'] == 1;

              return GestureDetector(
                onTap: () async {
                  if (isPromo) {
                    final bool? proceed = await showDialog<bool>(
                      context: context,
                      builder: (ctx) => AlertDialog(
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                        ),
                        icon: const Icon(Icons.info_outline,
                            color: Color(0xFFF59E0B), size: 48),
                        title: const Text('Promotional Ticket',
                            style: TextStyle(
                                fontWeight: FontWeight.bold, fontSize: 18)),
                        content: const Text(
                          'This is a promotional ticket and is STRICTLY non-refundable. It cannot be cancelled or rebooked.\n\nDo you wish to proceed?',
                          textAlign: TextAlign.center,
                          style: TextStyle(fontSize: 14),
                        ),
                        actionsAlignment: MainAxisAlignment.center,
                        actions: [
                          TextButton(
                            onPressed: () => Navigator.pop(ctx, false),
                            style: TextButton.styleFrom(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 24, vertical: 12),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8)),
                            ),
                            child: const Text('Cancel',
                                style: TextStyle(color: Colors.black54)),
                          ),
                          ElevatedButton(
                            onPressed: () => Navigator.pop(ctx, true),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFFF59E0B),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 24, vertical: 12),
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(8)),
                            ),
                            child: const Text('Proceed',
                                style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ],
                      ),
                    );
                    if (proceed != true) return;
                  }

                  setState(() {
                    if (widget.booking.mode == 'ferry') {
                      // Ferry uses transport_classes but must store in ferry fields
                      if (isReturn) {
                        widget.booking.selectedReturnFerryAccommodationId =
                            c['id'];
                        widget.booking.selectedReturnFerryAccommodationName =
                            c['name'];
                        widget.booking.selectedReturnFerryAccommodationPrice =
                            _parseDouble(c['price']);
                      } else {
                        widget.booking.selectedFerryAccommodationId = c['id'];
                        widget.booking.selectedFerryAccommodationName =
                            c['name'];
                        widget.booking.selectedFerryAccommodationPrice =
                            _parseDouble(c['price']);
                      }
                    } else {
                      // Airline
                      if (isReturn) {
                        widget.booking.selectedReturnAirlineClassId = c['id'];
                        widget.booking.selectedReturnAirlineClassName =
                            c['name'];
                        widget.booking.selectedReturnAirlineClassPrice =
                            _parseDouble(c['price']);
                      } else {
                        widget.booking.selectedAirlineClassId = c['id'];
                        widget.booking.selectedAirlineClassName = c['name'];
                        widget.booking.selectedAirlineClassPrice =
                            _parseDouble(c['price']);
                      }
                    }
                  });
                },
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isPromo
                        ? const Color(0xFFFFF7E6)
                        : (isSelected
                            ? kPink.withOpacity(0.05)
                            : Colors.white),
                    border: Border.all(
                        color: isPromo
                            ? const Color(0xFFF59E0B)
                            : (isSelected ? kPink : Colors.grey.shade300),
                        width: isPromo || isSelected ? 2 : 1),
                    borderRadius: BorderRadius.circular(8),
                    boxShadow: isPromo && !isSelected
                        ? [
                            BoxShadow(
                                color: const Color(0xFFF59E0B)
                                    .withOpacity(0.1),
                                blurRadius: 4,
                                spreadRadius: 1)
                          ]
                        : null,
                  ),
                  child: Stack(
                    clipBehavior: Clip.none,
                    children: [
                      if (isPromo)
                        Positioned(
                          top: -24,
                          right: -10,
                          child: Container(
                            padding: const EdgeInsets.symmetric(
                                horizontal: 6, vertical: 4),
                            decoration: const BoxDecoration(
                              color: Color(0xFFF59E0B),
                              borderRadius: BorderRadius.only(
                                topLeft: Radius.circular(8),
                                topRight: Radius.circular(8),
                                bottomLeft: Radius.circular(8),
                                bottomRight: Radius.circular(0),
                              ),
                            ),
                            child: const Text('PROMO',
                                style: TextStyle(
                                    color: Colors.white,
                                    fontSize: 9,
                                    fontWeight: FontWeight.bold)),
                          ),
                        ),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Row(
                            children: [
                              Expanded(
                                child: Text(
                                  c['name'] ?? '',
                                  style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      fontSize: 13,
                                      color: isPromo
                                          ? const Color(0xFF92400E)
                                          : Colors.black87),
                                  maxLines: 2,
                                  overflow: TextOverflow.ellipsis,
                                ),
                              ),
                            ],
                          ),
                          if (isPromo && c['promo_duration_end'] != null) ...[
                            const SizedBox(height: 4),
                            Row(
                              children: [
                                const Icon(Icons.access_time,
                                    size: 10, color: Color(0xFFB45309)),
                                const SizedBox(width: 4),
                                Text(
                                  'Until ${DateFormat('MMM dd, yyyy hh:mm a').format(DateTime.parse(c['promo_duration_end']).toLocal())}',
                                  style: const TextStyle(
                                      fontSize: 9, color: Color(0xFFB45309)),
                                ),
                              ],
                            ),
                          ],
                          const SizedBox(height: 8),
                          Row(
                            children: [
                              Text(
                                '₱${(_parseDouble(c['price']) + schedulePrice).toStringAsFixed(2)}',
                                style: TextStyle(
                                    color: isPromo
                                        ? const Color(0xFFEA580C)
                                        : kPink, // orange-600 vs pink
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16),
                              ),
                              if (isPromo && c['sale_price'] != null) ...[
                                const SizedBox(width: 4),
                                Text(
                                  '₱${(_parseDouble(c['price']) + _parseDouble(c['sale_price']) + schedulePrice).toStringAsFixed(2)}',
                                  style: const TextStyle(
                                      color: Colors.grey,
                                      decoration: TextDecoration.lineThrough,
                                      fontSize: 10),
                                ),
                              ]
                            ],
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildAccommodationsSelection(List<dynamic> accommodations,
      {required bool isReturn}) {
    final val = isReturn
        ? widget.booking.selectedReturnFerryAccommodationId
        : widget.booking.selectedFerryAccommodationId;
    final schedule = isReturn
        ? widget.booking.selectedReturnSchedule
        : widget.booking.selectedSchedule;
    final schedulePrice =
        schedule != null ? _parseDouble(schedule['price']) : 0.0;
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Padding(
          padding: EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
          child: Text('Select travel class for this trip:',
              style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        ),
        Padding(
          padding: const EdgeInsets.symmetric(horizontal: 16.0),
          child: GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.4,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: accommodations.length,
            itemBuilder: (context, index) {
              final c = accommodations[index];
              final isSelected = c['id'] == val;

              return GestureDetector(
                onTap: () {
                  setState(() {
                    if (isReturn) {
                      widget.booking.selectedReturnFerryAccommodationId =
                          c['id'];
                      widget.booking.selectedReturnFerryAccommodationName =
                          c['name'];
                      widget.booking.selectedReturnFerryAccommodationPrice =
                          _parseDouble(c['price']);
                    } else {
                      widget.booking.selectedFerryAccommodationId = c['id'];
                      widget.booking.selectedFerryAccommodationName = c['name'];
                      widget.booking.selectedFerryAccommodationPrice =
                          _parseDouble(c['price']);
                    }
                  });
                },
                child: Container(
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isSelected
                        ? kPink.withOpacity(0.05)
                        : Colors.white,
                    border: Border.all(
                        color: isSelected ? kPink : Colors.grey.shade300,
                        width: isSelected ? 2 : 1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Expanded(
                            child: Text(
                              c['name'] ?? '',
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold, fontSize: 13),
                              maxLines: 2,
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '₱${(_parseDouble(c['price']) + schedulePrice).toStringAsFixed(2)}',
                        style: const TextStyle(
                            color: kPink,
                            fontWeight: FontWeight.bold,
                            fontSize: 16),
                      ),
                    ],
                  ),
                ),
              );
            },
          ),
        ),
      ],
    );
  }

  Widget _buildBaggageSelection() {
    if (widget.booking.mode != 'airline') return const SizedBox.shrink();
    if (widget.booking.selectedSchedule == null) return const SizedBox.shrink();

    final serviceName = widget.booking.selectedSchedule!['operator']
            ?.toString()
            .toLowerCase() ??
        '';

    String matchedOperator = 'ceb_pac';
    if (serviceName.contains('pal') || serviceName.contains('philippine')) {
      matchedOperator = 'pal';
    } else if (serviceName.contains('cebu') ||
        serviceName.contains('pacific') ||
        serviceName.contains('ceb_pac')) {
      matchedOperator = 'ceb_pac';
    } else if (serviceName.contains('airasia')) {
      matchedOperator = 'airasia';
    } else if (serviceName.contains('sunlight')) {
      matchedOperator = 'sunlight';
    }

    final operatorRules = (_baggageRules['local'] != null
            ? _baggageRules['local'][matchedOperator]
            : null) ??
        (_baggageRules['international'] != null
            ? _baggageRules['international'][matchedOperator]
            : null) ??
        _baggageRules[matchedOperator];
    if (operatorRules == null ||
        operatorRules['options'] == null ||
        (operatorRules['options'] as List).isEmpty) {
      return const SizedBox.shrink();
    }

    final options = operatorRules['options'] as List;

    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade300)),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Row(
              children: [
                Icon(Icons.business_center, color: kGreen, size: 18),
                SizedBox(width: 8),
                Text('Extra Baggage (Optional)',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        color: kSlate800)),
              ],
            ),
            const SizedBox(height: 8),
            const Text(
                'Select extra baggage weight. The price will be applied per passenger.',
                style: TextStyle(fontSize: 12, color: kSlate500)),
            const SizedBox(height: 12),
            DropdownButtonFormField<int?>(
              value: widget.booking.hasExtraBaggage
                  ? widget.booking.extraBaggageKg
                  : null,
              decoration: const InputDecoration(
                border: OutlineInputBorder(),
                contentPadding:
                    EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              ),
              hint: const Text('No extra baggage'),
              isExpanded: true,
              items: [
                const DropdownMenuItem<int?>(
                  value: null,
                  child: Text('No extra baggage (7kg Hand Carry Only)',
                      style: TextStyle(fontSize: 14)),
                ),
                ...options.map((opt) {
                  int kg = int.tryParse(opt['weight']
                          .toString()
                          .replaceAll(RegExp(r'[^0-9]'), '')) ??
                      0;
                  return DropdownMenuItem<int?>(
                    value: kg,
                    child: Text('${opt['weight']} (+ ₱${opt['price']})',
                        style: const TextStyle(fontSize: 14)),
                  );
                }),
              ],
              onChanged: (val) {
                setState(() {
                  if (val == null) {
                    widget.booking.hasExtraBaggage = false;
                    widget.booking.extraBaggageKg = null;
                    widget.booking.extraBaggagePrice = 0.0;
                  } else {
                    widget.booking.hasExtraBaggage = true;
                    widget.booking.extraBaggageKg = val;
                    final selectedOpt = options.firstWhere((o) {
                      int k = int.tryParse(o['weight']
                              .toString()
                              .replaceAll(RegExp(r'[^0-9]'), '')) ??
                          0;
                      return k == val;
                    });
                    widget.booking.extraBaggagePrice =
                        double.tryParse(selectedOpt['price'].toString()) ?? 0.0;
                  }
                });
              },
            ),
          ],
        ),
      ),
    );
  }

  Widget _buildBaggageReminder() {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 16.0, vertical: 8.0),
      child: Container(
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
            color: const Color(0xFFFFF7ED),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: const Color(0xFFFED7AA))),
        child: const Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Icon(Icons.luggage, color: Color(0xFFEA580C), size: 18),
                SizedBox(width: 8),
                Text('Baggage Rules & Reminders',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                        color: Color(0xFF9A3412))),
              ],
            ),
            SizedBox(height: 8),
            Text('• Standard ticket includes 1 hand carry bag (up to 7kg).',
                style: TextStyle(fontSize: 12, color: Color(0xFF9A3412))),
            SizedBox(height: 4),
            Text('• Ensure valuables are kept with you at all times.',
                style: TextStyle(fontSize: 12, color: Color(0xFF9A3412))),
          ],
        ),
      ),
    );
  }

  Widget _buildHorizontalScheduleList(List<dynamic> schedules,
      {required bool isReturn}) {
    if (schedules.isEmpty) {
      return const Padding(
        padding: EdgeInsets.all(16.0),
        child: Text('No trips available for this date.',
            style: TextStyle(color: kSlate500)),
      );
    }

    return SizedBox(
      height: 220,
      child: ListView.builder(
        scrollDirection: Axis.horizontal,
        padding: const EdgeInsets.symmetric(horizontal: 16),
        itemCount: schedules.length,
        itemBuilder: (context, index) {
          final s = schedules[index];
          final bool isSelected = isReturn
              ? (widget.booking.selectedReturnSchedule?['id'] == s['id'])
              : (widget.booking.selectedSchedule?['id'] == s['id']);

          return Container(
            width: 300,
            margin: const EdgeInsets.only(right: 12),
            child: Card(
              color: isSelected ? kPink : Colors.white,
              elevation: 2,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
              child: InkWell(
                onTap: () {
                  setState(() {
                    if (isReturn) {
                      widget.booking.selectedReturnSchedule =
                          Map<String, dynamic>.from(s);

                      if (widget.booking.mode != 'airline') {
                        final accommodations =
                            s['accommodations'] as List<dynamic>? ?? [];
                        if (accommodations.isNotEmpty) {
                          // _showFerryAccommodationPicker(context, accommodations, isReturn: true);
                        }
                      }
                    } else {
                      widget.booking.selectedSchedule =
                          Map<String, dynamic>.from(s);
                      // Store promotional ticket ID so it is passed to the booking API
                      final promo = s['promotional_ticket'];
                      widget.booking.promotionalTicketId =
                          promo != null ? promo['id'] as int? : null;
                      widget.booking.passengers = BookingData.buildPassengerEntries(
                        adults: widget.booking.adults,
                        children: widget.booking.children,
                        minors: widget.booking.minors,
                        infants: widget.booking.infants,
                        hasVehicle: widget.booking.hasVehicle,
                        vehicleDriverName:
                            '${widget.booking.vehicleDriverFirstName} ${widget.booking.vehicleDriverMiddleName} ${widget.booking.vehicleDriverLastName}'
                                .replaceAll(RegExp(r'\s+'), ' ')
                                .trim(),
                        vehicleDriverBirthday: widget.booking.vehicleDriverBirthday,
                        isAirline: widget.booking.mode == 'airline',
                      );

                      if (widget.booking.mode != 'airline') {
                        final accommodations =
                            s['accommodations'] as List<dynamic>? ?? [];
                        if (accommodations.isNotEmpty) {
                          // _showFerryAccommodationPicker(context, accommodations, isReturn: false);
                        }
                      } else {
                        final classes =
                            s['transport_classes'] as List<dynamic>? ?? [];
                        if (classes.isNotEmpty) {
                          // _showAirlineClassPicker(context, classes);
                        }
                      }
                    }
                  });
                },
                borderRadius: BorderRadius.circular(16),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          const SizedBox(),
                          Flexible(
                            child: Row(
                              mainAxisSize: MainAxisSize.min,
                              children: [
                                // PROMO badge
                                if (s['promotional_ticket'] != null)
                                  Container(
                                    margin: const EdgeInsets.only(right: 6),
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 7, vertical: 3),
                                    decoration: BoxDecoration(
                                        color: Colors.orange,
                                        borderRadius: BorderRadius.circular(6)),
                                    child: const Text('PROMO',
                                        style: TextStyle(
                                            color: Colors.white,
                                            fontSize: 9,
                                            fontWeight: FontWeight.bold)),
                                  ),
                                if (getOperatorLogoUrl(s['operator'] ?? '')
                                    .isNotEmpty)
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.center,
                                      children: [
                                        SizedBox(
                                            height: 60,
                                            child: Image.network(
                                              getOperatorLogoUrl(
                                                  s['operator'] ?? ''),
                                              fit: BoxFit.contain,
                                              errorBuilder: (context, error,
                                                      stackTrace) =>
                                                  const SizedBox(),
                                            )),
                                        const SizedBox(height: 8),
                                        Text(
                                          s['operator'] ?? 'Operator',
                                          style: TextStyle(
                                              color: isSelected
                                                  ? Colors.white
                                                  : kGreen,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 12),
                                          textAlign: TextAlign.center,
                                          maxLines: 1,
                                          overflow: TextOverflow.ellipsis,
                                        ),
                                      ],
                                    ),
                                  ),
                                if (getOperatorLogoUrl(s['operator'] ?? '')
                                    .isEmpty)
                                  Flexible(
                                    child: Container(
                                      padding: const EdgeInsets.symmetric(
                                          horizontal: 8, vertical: 4),
                                      decoration: BoxDecoration(
                                          color: isSelected
                                              ? Colors.white24
                                              : kGreen.withOpacity(0.08),
                                          borderRadius:
                                              BorderRadius.circular(8)),
                                      child: Text(
                                        s['operator'] ?? 'Operator',
                                        style: TextStyle(
                                            color: isSelected
                                                ? Colors.white
                                                : kGreen,
                                            fontWeight: FontWeight.bold,
                                            fontSize: 11),
                                        maxLines: 1,
                                        overflow: TextOverflow.ellipsis,
                                      ),
                                    ),
                                  ),
                              ],
                            ),
                          ),
                        ],
                      ),
                      const SizedBox(height: 8),
                      Text(s['service'] ?? '',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                              color: isSelected ? Colors.white : kSlate800),
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis),
                      if (s['vehicle_name'] != null &&
                          s['vehicle_name'].toString().trim().isNotEmpty) ...[
                        const SizedBox(height: 2),
                        Text(s['vehicle_name'],
                            style: TextStyle(
                                color: isSelected ? Colors.white70 : kSlate500,
                                fontSize: 12)),
                      ],
                      const Spacer(),
                      Text('${s['departure']} → ${s['arrival']}',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: isSelected ? Colors.white : kSlate800)),
                      Text('Duration: ${s['duration'] ?? 'N/A'}',
                          style: TextStyle(
                              fontSize: 11,
                              color: isSelected ? Colors.white70 : kSlate500)),
                      if (!isReturn &&
                          widget.booking.selectedTransportClass != null &&
                          isSelected) ...[
                        const SizedBox(height: 4),
                        Text(
                            '${widget.booking.selectedTransportClass?['name']}',
                            style: const TextStyle(
                                fontSize: 11,
                                color: Colors.white,
                                fontWeight: FontWeight.bold)),
                      ]
                    ],
                  ),
                ),
              ),
            ),
          );
        },
      ),
    );
  }
}

// ==========================================
// STEP 3: DISCOUNT (Passenger Details + Discount)
// ==========================================
class DiscountScreen extends StatefulWidget {
  final BookingData booking;
  const DiscountScreen({super.key, required this.booking});

  @override
  State<DiscountScreen> createState() => _DiscountScreenState();
}

class _DiscountScreenState extends State<DiscountScreen> {
  final _formKey = GlobalKey<FormState>();
  List<Map<String, dynamic>> _discounts = [];
  List<TextEditingController> _nameControllers = [];
  List<TextEditingController> _birthdateControllers = [];
  List<TextEditingController> _idControllers = [];
  List<String?> _idFrontBase64 = [];
  List<String?> _idBackBase64 = [];

  static const _steps = ['Route', 'Schedule', 'Discount', 'Hotels', 'Submit'];

  @override
  void initState() {
    super.initState();
    _nameControllers = List.generate(widget.booking.passengers.length, (i) {
      return TextEditingController(
          text: widget.booking.passengers[i]['name'] ?? '');
    });
    _birthdateControllers =
        List.generate(widget.booking.passengers.length, (i) {
      return TextEditingController(
          text: widget.booking.passengers[i]['birthdate'] ?? '');
    });
    _idControllers = List.generate(widget.booking.passengers.length, (i) {
      return TextEditingController(
          text: widget.booking.passengers[i]['id_number'] ?? '');
    });
    _idFrontBase64 = List.generate(widget.booking.passengers.length, (i) {
      return widget.booking.passengers[i]['id_image_front'];
    });
    _idBackBase64 = List.generate(widget.booking.passengers.length, (i) {
      return widget.booking.passengers[i]['id_image_back'];
    });
    _fetchDiscounts();
  }

  @override
  void dispose() {
    for (var c in _nameControllers) {
      c.dispose();
    }
    for (var c in _birthdateControllers) {
      c.dispose();
    }
    for (var c in _idControllers) {
      c.dispose();
    }
    super.dispose();
  }

  Future<void> _pickIdImage(int index, bool isFront) async {
    final picker = ImagePicker();
    final picked = await picker.pickImage(
        source: ImageSource.gallery,
        imageQuality: 70,
        maxWidth: 800,
        maxHeight: 800);
    if (picked != null) {
      final bytes = await picked.readAsBytes();
      final b64 = 'data:image/jpeg;base64,${base64Encode(bytes)}';
      setState(() {
        if (isFront) {
          _idFrontBase64[index] = b64;
          widget.booking.passengers[index]['id_image_front'] = b64;
        } else {
          _idBackBase64[index] = b64;
          widget.booking.passengers[index]['id_image_back'] = b64;
        }
      });
    }
  }

  Widget _buildIdImagePicker(int i, String label, bool isFront) {
    final b64 = isFront ? _idFrontBase64[i] : _idBackBase64[i];
    final hasImg = b64 != null && b64.isNotEmpty;
    return InkWell(
      onTap: () => _pickIdImage(i, isFront),
      borderRadius: BorderRadius.circular(10),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 14),
        decoration: BoxDecoration(
          color: hasImg ? kGreen.withOpacity(0.08) : Colors.grey.shade50,
          border: Border.all(
              color: hasImg ? kGreen : Colors.grey.shade300,
              width: hasImg ? 1.5 : 1),
          borderRadius: BorderRadius.circular(10),
        ),
        child: Row(
          children: [
            Icon(
              hasImg
                  ? Icons.check_circle
                  : (isFront
                      ? Icons.badge_outlined
                      : Icons.credit_card_outlined),
              color: hasImg ? kGreen : kSlate600,
              size: 24,
            ),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    label,
                    style: TextStyle(
                      fontSize: 13,
                      fontWeight: FontWeight.w600,
                      color: hasImg ? kGreen : kSlate700,
                    ),
                  ),
                  const SizedBox(height: 2),
                  Text(
                    hasImg
                        ? 'Image attached • Tap to change'
                        : 'Tap to upload picture',
                    style: TextStyle(
                      fontSize: 11,
                      color: hasImg ? kGreen.withOpacity(0.8) : kSlate400,
                    ),
                  ),
                ],
              ),
            ),
            if (hasImg)
              const Icon(Icons.refresh, color: kGreen, size: 18)
            else
              const Icon(Icons.add_a_photo_outlined,
                  color: kSlate500, size: 20),
          ],
        ),
      ),
    );
  }

  void _fetchDiscounts() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/discounts'));
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success') {
          setState(() =>
              _discounts = List<Map<String, dynamic>>.from(data['discounts']));
        }
      }
    } catch (_) {}
  }

  void _goNext() {
    if (!_formKey.currentState!.validate()) return;
    for (int i = 0; i < widget.booking.passengers.length; i++) {
      widget.booking.passengers[i]['name'] = _nameControllers[i].text.trim();
      widget.booking.passengers[i]['birthdate'] =
          _birthdateControllers[i].text.trim();

      final discId = widget.booking.passengers[i]['discount_id'];
      final disc =
          _discounts.firstWhere((d) => d['id'] == discId, orElse: () => {});
      final discName = disc['name']?.toString().toLowerCase() ?? '';

      if (discId != null && discName != 'infant') {
        final idNumber = _idControllers[i].text.trim();
        widget.booking.passengers[i]['id_number'] = idNumber;

        if (discName == 'student') {
          if (_idFrontBase64[i] == null || _idBackBase64[i] == null) {
            showTopSnack(
              context,
              SnackBar(
                content: Text(
                    'Please upload both Front and Back ID images for Passenger #${i + 1}.'),
                backgroundColor: Colors.red,
              ),
            );
            return;
          }
          widget.booking.passengers[i]['id_image_front'] = _idFrontBase64[i];
          widget.booking.passengers[i]['id_image_back'] = _idBackBase64[i];
        } else {
          widget.booking.passengers[i]['id_image_front'] = null;
          widget.booking.passengers[i]['id_image_back'] = null;
        }
        widget.booking.passengers[i]['school_name'] = null;
      } else {
        widget.booking.passengers[i]['school_name'] = null;
        widget.booking.passengers[i]['id_number'] = null;
        widget.booking.passengers[i]['id_image_front'] = null;
        widget.booking.passengers[i]['id_image_back'] = null;
      }
    }
    widget.booking.savedStep = 3;
    widget.booking.saveToPrefs(3);
    Navigator.push(
        context,
        MaterialPageRoute(
            builder: (_) => StayScreen(booking: widget.booking))).then((_) {
      if (mounted) {
        widget.booking.savedStep = 2;
        widget.booking.saveToPrefs(2);
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    final s = widget.booking.selectedSchedule!;
    final pax = widget.booking.passengers;

    return Scaffold(
      appBar: AppBar(title: const Text('Passenger & Discount')),
      body: Column(
        children: [
          _StepProgress(
              currentStep: 3, steps: _steps, mode: widget.booking.mode),
          Expanded(
            child: Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Schedule summary
                  Container(
                    padding: const EdgeInsets.all(14),
                    decoration: BoxDecoration(
                        color: kGreen.withOpacity(0.06),
                        borderRadius: BorderRadius.circular(12)),
                    child: Text(
                      '${widget.booking.origin} → ${widget.booking.destination}  ·  ${s['service']}  ·  ₱${(_parseDouble(s['price']) + (widget.booking.mode == 'ferry' ? (widget.booking.selectedFerryAccommodationPrice ?? 0) : (widget.booking.selectedAirlineClassPrice ?? 0))).toStringAsFixed(2)} / person',
                      style: const TextStyle(
                          fontWeight: FontWeight.bold,
                          color: kGreen,
                          fontSize: 13),
                    ),
                  ),
                  const SizedBox(height: 16),

                  ...List.generate(pax.length, (i) {
                    final type = pax[i]['type'] as String;
                    return Card(
                      color: Colors.white,
                      margin: const EdgeInsets.only(bottom: 14),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(14)),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                      color: ['adult', 'driver'].contains(type)
                                          ? kGreen.withOpacity(0.1)
                                          : kPink.withOpacity(0.1),
                                      borderRadius: BorderRadius.circular(20)),
                                  child: Text(
                                    BookingData.passengerTypeLabel(type, i + 1),
                                    style: TextStyle(
                                        color: ['adult', 'driver'].contains(type)
                                            ? kGreen
                                            : kPink,
                                        fontSize: 11,
                                        fontWeight: FontWeight.bold),
                                  ),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            TextFormField(
                              controller: _nameControllers[i],
                              readOnly: type == 'driver',
                              decoration: InputDecoration(
                                labelText: 'Full Name',
                                filled: type == 'driver',
                                fillColor: type == 'driver'
                                    ? Colors.grey.shade100
                                    : null,
                                border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                              validator: (v) => (v == null || v.trim().isEmpty)
                                  ? 'Full name is required'
                                  : null,
                            ),
                            const SizedBox(height: 10),
                            TextFormField(
                              controller: _birthdateControllers[i],
                              readOnly: true,
                              validator: (v) => (v == null || v.trim().isEmpty)
                                  ? 'Birthdate is required'
                                  : null,
                              onTap: type == 'driver'
                                  ? null
                                  : () async {
                                      final d = await showDatePicker(
                                        context: context,
                                        initialDate: DateTime.now().subtract(
                                            const Duration(days: 365 * 10)),
                                        firstDate: DateTime(1900),
                                        lastDate: DateTime.now(),
                                      );
                                      if (d != null) {
                                        final selectedDate =
                                            "${d.year}-${d.month.toString().padLeft(2, '0')}-${d.day.toString().padLeft(2, '0')}";
                                        setState(() => _birthdateControllers[i]
                                            .text = selectedDate);

                                        // Infant check
                                        final age = DateTime.now()
                                                .difference(d)
                                                .inDays /
                                            365.25;
                                        if (age < 2 && type == 'child') {
                                          if (mounted) {
                                            showDialog(
                                              context: context,
                                              builder: (c) => AlertDialog(
                                                title: const Text(
                                                    'Minor / Infant Notice'),
                                                content: const Text(
                                                    'Please note: If the passenger is an infant (under 2 years old), additional requirements and fees may apply depending on the operator.'),
                                                actions: [
                                                  TextButton(
                                                    onPressed: () =>
                                                        Navigator.pop(c),
                                                    child: const Text(
                                                        'Acknowledge'),
                                                  ),
                                                ],
                                              ),
                                            );
                                          }
                                        }
                                      }
                                    },
                              decoration: InputDecoration(
                                labelText: 'Birthdate *',
                                hintText: 'YYYY-MM-DD',
                                filled: type == 'driver',
                                fillColor: type == 'driver'
                                    ? Colors.grey.shade100
                                    : null,
                                suffixIcon:
                                    const Icon(Icons.calendar_today, size: 20),
                                border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10)),
                              ),
                            ),
                            if (widget.booking.usePromoTicket &&
                                widget.booking.promotionalTicketId != null) ...[
                              const SizedBox(height: 10),
                              Container(
                                padding: const EdgeInsets.all(10),
                                decoration: BoxDecoration(
                                  color: const Color(0xFFFFF3CD),
                                  borderRadius: BorderRadius.circular(8),
                                  border: Border.all(
                                      color: const Color(0xFFFFD700)),
                                ),
                                child: const Row(
                                  children: [
                                    Icon(Icons.local_activity,
                                        color: Color(0xFFC08000), size: 18),
                                    SizedBox(width: 8),
                                    Expanded(
                                      child: Text(
                                        'Promotional ticket applied — passenger discounts are not applicable.',
                                        style: TextStyle(
                                            fontSize: 12,
                                            color: Color(0xFF7B5800),
                                            fontWeight: FontWeight.w500),
                                      ),
                                    ),
                                  ],
                                ),
                              ),
                            ] else if (_discounts.isNotEmpty) ...[
                              const SizedBox(height: 10),
                              DropdownButtonFormField<int?>(
                                value: pax[i]['discount_id'],
                                hint: const Text('No Discount'),
                                items: [
                                  const DropdownMenuItem<int?>(
                                      value: null, child: Text('No Discount')),
                                  ..._discounts
                                      .where((d) =>
                                          d['name'].toString().toLowerCase() !=
                                          'infant')
                                      .map((d) => DropdownMenuItem<int?>(
                                            value: d['id'] as int,
                                            child: Text('${d['name']}'),
                                          )),
                                ],
                                onChanged: (v) {
                                  setState(() {
                                    pax[i]['discount_id'] = v;
                                  });
                                  if (v != null) {
                                    showDialog(
                                      context: context,
                                      builder: (c) => AlertDialog(
                                        title: const Text('Discount Applied'),
                                        content: const Text(
                                            'Your discount has been applied.\n\nKindly present a valid ID upon boarding to verify and enjoy your discount'),
                                        actions: [
                                          TextButton(
                                            onPressed: () => Navigator.pop(c),
                                            child: const Text('Okay'),
                                          ),
                                        ],
                                      ),
                                    );
                                  }
                                },
                                decoration: InputDecoration(
                                  labelText: 'Discount',
                                  prefixIcon: const Icon(Icons.local_offer,
                                      color: kGreen, size: 18),
                                  border: OutlineInputBorder(
                                      borderRadius: BorderRadius.circular(10)),
                                ),
                              ),
                              Builder(
                                builder: (context) {
                                  final discId = pax[i]['discount_id'];
                                  final disc = _discounts.firstWhere(
                                      (d) => d['id'] == discId,
                                      orElse: () => {});
                                  final discName =
                                      disc['name']?.toString().toLowerCase() ??
                                          '';

                                  if (discId != null && discName != 'infant') {
                                    String idLabel = 'ID Number *';
                                    if (discName == 'student') {
                                      idLabel = 'Student ID Number *';
                                    } else if (discName == 'senior citizen')
                                      idLabel = 'Senior Citizen ID Number *';
                                    else if (discName == 'pwd')
                                      idLabel = 'PWD ID Number *';

                                    return Column(
                                      children: [
                                        const SizedBox(height: 10),
                                        TextFormField(
                                          controller: _idControllers[i],
                                          decoration: InputDecoration(
                                            labelText: idLabel,
                                            border: OutlineInputBorder(
                                                borderRadius:
                                                    BorderRadius.circular(10)),
                                          ),
                                          validator: (v) =>
                                              (v == null || v.trim().isEmpty)
                                                  ? 'ID number is required'
                                                  : null,
                                        ),
                                        if (discName == 'student') ...[
                                          const SizedBox(height: 12),
                                          _buildIdImagePicker(
                                              i, 'ID Image (Front) *', true),
                                          const SizedBox(height: 10),
                                          _buildIdImagePicker(
                                              i, 'ID Image (Back) *', false),
                                        ],
                                      ],
                                    );
                                  }
                                  return const SizedBox.shrink();
                                },
                              ),
                            ],
                          ],
                        ),
                      ),
                    );
                  }),

                  const SizedBox(height: 8),
                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton(
                      onPressed: _goNext,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: kPink,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        elevation: 4,
                      ),
                      child: const Text('Next: Select Stay',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 16)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// STEP 4: STAY
// ==========================================
class StayScreen extends StatefulWidget {
  final BookingData booking;
  const StayScreen({super.key, required this.booking});

  @override
  State<StayScreen> createState() => _StayScreenState();
}

class _StayScreenState extends State<StayScreen> {
  List<Map<String, dynamic>> _accommodations = [];
  bool _isLoading = true;

  static const _steps = ['Route', 'Schedule', 'Discount', 'Hotels', 'Submit'];

  @override
  void initState() {
    super.initState();
    _fetchData();
  }

  void _fetchData() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final destination = widget.booking.destination.trim();
      final uri = destination.isEmpty
          ? Uri.parse('$baseUrl/api/accommodations')
          : Uri.parse(
              '$baseUrl/api/accommodations?destination=${Uri.encodeComponent(destination)}');
      final res = await http.get(uri);
      final accData = jsonDecode(res.body);
      if (res.statusCode == 200 && accData['status'] == 'success') {
        _accommodations =
            List<Map<String, dynamic>>.from(accData['accommodations']);
        widget.booking.availableAccommodations = _accommodations;
      }
    } catch (_) {
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Hotels')),
      body: Column(
        children: [
          _StepProgress(
              currentStep: 4, steps: _steps, mode: widget.booking.mode),
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: kGreen))
                : ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // ── Hotel / Accommodation Add-ons ──
                      const Text(
                        'Hotels',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: kSlate800),
                      ),
                      const SizedBox(height: 4),
                      const Text(
                          'Select one or more stays to add to your booking.',
                          style: TextStyle(color: kSlate500, fontSize: 12)),
                      const SizedBox(height: 16),

                      if (_accommodations.isEmpty)
                        const Center(
                          child: Padding(
                            padding: EdgeInsets.symmetric(vertical: 24),
                            child: Text(
                                'No accommodations available at this time.',
                                style: TextStyle(color: kSlate400)),
                          ),
                        )
                      else
                        ..._accommodations.map((a) {
                          final id = a['id'] as int;
                          final selected = widget
                              .booking.selectedAccommodationIds
                              .contains(id);
                          return Card(
                            color: selected
                                ? kGreen.withOpacity(0.05)
                                : Colors.white,
                            margin: const EdgeInsets.only(bottom: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(14),
                              side: BorderSide(
                                  color: selected ? kGreen : kSlate200,
                                  width: selected ? 2 : 1),
                            ),
                            child: InkWell(
                              onTap: () {
                                setState(() {
                                  if (selected) {
                                    widget.booking.selectedAccommodationIds
                                        .remove(id);
                                  } else {
                                    widget.booking.selectedAccommodationIds
                                        .add(id);
                                  }
                                });
                              },
                              borderRadius: BorderRadius.circular(14),
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Row(
                                  children: [
                                    if (a['cover_image'] != null)
                                      ClipRRect(
                                        borderRadius: BorderRadius.circular(10),
                                        child: Image.network(
                                            a['cover_image'] as String,
                                            width: 60,
                                            height: 60,
                                            fit: BoxFit.cover,
                                            errorBuilder: (_, __, ___) =>
                                                Container(
                                                    width: 60,
                                                    height: 60,
                                                    color: kSlate100,
                                                    child: const Icon(
                                                        Icons.hotel,
                                                        color: kSlate400))),
                                      )
                                    else
                                      Container(
                                          width: 60,
                                          height: 60,
                                          decoration: BoxDecoration(
                                              color: kSlate100,
                                              borderRadius:
                                                  BorderRadius.circular(10)),
                                          child: const Icon(Icons.hotel,
                                              color: kSlate400)),
                                    const SizedBox(width: 14),
                                    Expanded(
                                      child: Column(
                                        crossAxisAlignment:
                                            CrossAxisAlignment.start,
                                        children: [
                                          Text(a['name'] as String,
                                              style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 14,
                                                  color: kSlate800)),
                                          if (a['description'] != null)
                                            Text(a['description'] as String,
                                                style: const TextStyle(
                                                    color: kSlate500,
                                                    fontSize: 12),
                                                maxLines: 2,
                                                overflow:
                                                    TextOverflow.ellipsis),
                                          const SizedBox(height: 4),
                                          Row(
                                            children: [
                                              Text('₱${a['price']}',
                                                  style: const TextStyle(
                                                      color: kPink,
                                                      fontWeight:
                                                          FontWeight.bold,
                                                      fontSize: 14)),
                                            ],
                                          ),
                                        ],
                                      ),
                                    ),
                                    Checkbox(
                                      value: selected,
                                      onChanged: (_) {
                                        setState(() {
                                          if (selected) {
                                            widget.booking
                                                .selectedAccommodationIds
                                                .remove(id);
                                          } else {
                                            widget.booking
                                                .selectedAccommodationIds
                                                .add(id);
                                          }
                                        });
                                      },
                                      activeColor: kGreen,
                                    ),
                                  ],
                                ),
                              ),
                            ),
                          );
                        }),

                      const SizedBox(height: 16),
                      SizedBox(
                        width: double.infinity,
                        height: 52,
                        child: ElevatedButton(
                          onPressed: () {
                            widget.booking.savedStep = 4;
                            widget.booking.saveToPrefs(4);
                            Navigator.push(
                                context,
                                MaterialPageRoute(
                                    builder: (_) => BookingSubmitScreen(
                                        booking: widget.booking))).then((_) {
                              if (mounted) {
                                widget.booking.savedStep = 3;
                                widget.booking.saveToPrefs(3);
                              }
                            });
                          },
                          style: ElevatedButton.styleFrom(
                            backgroundColor: kPink,
                            foregroundColor: Colors.white,
                            shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12)),
                            elevation: 4,
                          ),
                          child: const Text('Next: Review & Submit',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold, fontSize: 16)),
                        ),
                      ),
                    ],
                  ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// STEP 5: SUBMIT (Review + Contact Info)
// ==========================================
class BookingSubmitScreen extends StatefulWidget {
  final BookingData booking;
  const BookingSubmitScreen({super.key, required this.booking});

  @override
  State<BookingSubmitScreen> createState() => _BookingSubmitScreenState();
}

class _BookingSubmitScreenState extends State<BookingSubmitScreen> {
  final _formKey = GlobalKey<FormState>();
  late TextEditingController _clientNameCtrl;
  late TextEditingController _clientEmailCtrl;
  late TextEditingController _clientPhoneCtrl;
  bool _isSubmitting = false;

  // Payment / QR
  String? _qrCodeUrl;
  bool _loadingPaymentSettings = true;
  double _feePerPerson = 0.0;
  double _feePerAccommodation = 0.0;
  double _transactionFee = 0.0;

  // Points
  bool _usePoints = false;
  double _availablePoints = 0.0;
  bool _fetchingPoints = false;

  static const _steps = ['Route', 'Schedule', 'Discount', 'Hotels', 'Submit'];

  @override
  void initState() {
    super.initState();
    _clientNameCtrl = TextEditingController(
        text: UserSession.isLoggedIn
            ? UserSession.username
            : widget.booking.clientName);
    _clientEmailCtrl = TextEditingController(
        text: UserSession.isLoggedIn
            ? UserSession.email
            : widget.booking.clientEmail);
    _clientPhoneCtrl = TextEditingController(
        text: UserSession.isLoggedIn
            ? UserSession.phone
            : widget.booking.clientPhone);
    // If promo ticket is active, clear any voucher that may have been applied
    if (widget.booking.usePromoTicket &&
        widget.booking.promotionalTicketId != null) {
      widget.booking.voucherCode = null;
      widget.booking.voucherData = null;
    }
    _fetchPaymentSettings();
    _fetchPoints();
    _autoApplySavedVoucher();
  }

  Future<void> _autoApplySavedVoucher() async {
    // Don't auto-apply vouchers when promo ticket is active
    if (widget.booking.usePromoTicket &&
        widget.booking.promotionalTicketId != null) return;
    final code = UserSession.autoApplyVoucherCode;
    if (code == null || code.isEmpty) return;
    UserSession.autoApplyVoucherCode = null;
    try {
      final booking = widget.booking;
      final body = {
        'voucher_code': code.trim().toUpperCase(),
        'schedule_id': booking.selectedSchedule?['id'] ?? 0,
        'origin': booking.origin,
        'destination': booking.destination,
        'trip_type': booking.tripType,
        'client_email': booking.clientEmail.isNotEmpty
            ? booking.clientEmail
            : UserSession.email,
        'passengers': booking.passengers.isNotEmpty
            ? booking.passengers
                .map(
                    (p) => {'type': p['type'], 'discount_id': p['discount_id']})
                .toList()
            : [
                {'type': 'adult', 'discount_id': null}
              ],
        'accommodation_ids': booking.selectedAccommodationIds,
        'has_vehicle': booking.hasVehicle,
        if (booking.hasVehicle) 'vehicle_price': booking.vehiclePrice,
        // Include accommodation/class IDs so backend calculates correct base amount
        if (booking.mode == 'ferry' &&
            booking.selectedFerryAccommodationId != null)
          ...(() {
            final depClasses = (booking.selectedSchedule?['transport_classes']
                    as List<dynamic>? ??
                []);
            if (depClasses.isNotEmpty) {
              return {
                'selected_transport_class_id':
                    booking.selectedFerryAccommodationId
              };
            } else {
              return {
                'selected_schedule_accommodation_id':
                    booking.selectedFerryAccommodationId
              };
            }
          }())
        else if (booking.mode != 'ferry' &&
            booking.selectedAirlineClassId != null)
          'selected_transport_class_id': booking.selectedAirlineClassId,
        if (booking.tripType == 'round_trip' &&
            booking.selectedReturnFerryAccommodationId != null)
          ...(() {
            final retClasses =
                (booking.selectedReturnSchedule?['transport_classes']
                        as List<dynamic>? ??
                    []);
            if (retClasses.isNotEmpty) {
              return {
                'return_selected_transport_class_id':
                    booking.selectedReturnFerryAccommodationId
              };
            } else {
              return {
                'selected_return_schedule_accommodation_id':
                    booking.selectedReturnFerryAccommodationId
              };
            }
          }())
        else if (booking.tripType == 'round_trip' &&
            booking.selectedReturnAirlineClassId != null)
          'return_selected_transport_class_id':
              booking.selectedReturnAirlineClassId,
      };
      final res = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/vouchers/validate'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
        body: jsonEncode(body),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        final d = data['data'];
        if (mounted) {
          setState(() {
            widget.booking.voucherCode = code.trim().toUpperCase();
            widget.booking.voucherData = {
              'name': d['voucher_name'],
              'discount_type': d['discount_type'],
              'discount_value': d['discount_value'],
              'eligible_scope': d['eligible_scope'],
              'discount_amount': d['discount_amount'],
              'final_total': d['final_total'],
              'original_subtotal': d['original_subtotal'],
            };
          });
          showTopSnack(
            context,
            SnackBar(
                content: Text('Voucher "$code" applied automatically!'),
                backgroundColor: kGreen),
          );
        }
      }
    } catch (_) {}
  }

  @override
  void dispose() {
    _clientNameCtrl.dispose();
    _clientEmailCtrl.dispose();
    _clientPhoneCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchPoints() async {
    if (!UserSession.isLoggedIn || UserSession.token.isEmpty) return;
    setState(() => _fetchingPoints = true);
    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/gracia-points'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _availablePoints = double.parse(data['current_points'].toString());
          UserSession.graciaPoints = data['current_points'] ?? 0;
          final activeRule = data['active_rule'];
          if (activeRule != null) {
            UserSession.pointsAwarded = activeRule['points_awarded'] ?? 0;
            UserSession.spendThreshold =
                activeRule['spend_threshold_centavos'] ?? 0;
          }
        });
        UserSession.save();
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _fetchingPoints = false);
    }
  }

  void _fetchPaymentSettings() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/payment-settings'),
          headers: {'Accept': 'application/json'});
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success') {
          setState(() {
            _qrCodeUrl = data['qr_code_url'];
            _feePerPerson = _parseDouble(data['web_admin_fee']);
            _feePerAccommodation = _parseDouble(data['fee_per_accommodation']);
            _transactionFee = _parseDouble(data['transaction_fee']);
          });
        }
      }
    } catch (_) {
    } finally {
      setState(() => _loadingPaymentSettings = false);
    }
  }

  Future<void> _submit() async {
    if (_isSubmitting) return;
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    final phone = _clientPhoneCtrl.text.trim();
    if (phone.isNotEmpty) {
      UserSession.phone = phone;
      await UserSession.save();

      if (UserSession.isLoggedIn && UserSession.token.isNotEmpty) {
        try {
          await http.post(
            Uri.parse('${UserSession.getBaseUrl()}/api/profile/update'),
            headers: {
              'Accept': 'application/json',
              'Content-Type': 'application/json',
              'Authorization': 'Bearer ${UserSession.token}',
            },
            body: jsonEncode({
              'name': UserSession.username,
              'phone': UserSession.phone,
            }),
          );
        } catch (_) {}
      }
    }

    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
        Uri.parse('$baseUrl/api/bookings'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.isLoggedIn && UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
        body: jsonEncode({
          'schedule_id': widget.booking.selectedSchedule!['id'],
          'origin': widget.booking.origin,
          'destination': widget.booking.destination,
          'departure_date': widget.booking.departureDate,
          'trip_type': widget.booking.tripType,
          'return_date': widget.booking.returnDate,
          'client_name': _clientNameCtrl.text.trim(),
          'client_email': _clientEmailCtrl.text.trim(),
          'client_phone': _clientPhoneCtrl.text.trim(),
          'passengers': widget.booking.passengers,
          'accommodation_ids': widget.booking.selectedAccommodationIds,
          // Vehicle
          'has_vehicle': widget.booking.hasVehicle,
          if (widget.booking.hasVehicle) ...{
            'vehicle_rate_id': widget.booking.selectedVehicleRateId,
            'vehicle_type': widget.booking.vehicleType,
            'vehicle_plate_number': widget.booking.vehiclePlateNumber,
            'vehicle_price': widget.booking.vehiclePrice,
            'driver_first_name': widget.booking.vehicleDriverFirstName,
            'driver_middle_name': widget.booking.vehicleDriverMiddleName,
            'driver_last_name': widget.booking.vehicleDriverLastName,
            'driver_birthday': widget.booking.vehicleDriverBirthday,
          },
          // Determine which API field to use for the ferry class:
          // - schedules that have transport_classes → send as selected_transport_class_id
          // - schedules that have accommodations    → send as selected_schedule_accommodation_id
          if (widget.booking.mode == 'ferry' &&
              widget.booking.selectedFerryAccommodationId != null)
            ...(() {
              final depClasses =
                  (widget.booking.selectedSchedule?['transport_classes']
                          as List<dynamic>? ??
                      []);
              if (depClasses.isNotEmpty) {
                // Ferry using transport_classes (e.g. Starlite, 2GO that have TransportClass rows)
                return {
                  'selected_transport_class_id':
                      widget.booking.selectedFerryAccommodationId,
                  'selected_schedule_accommodation_id': null,
                };
              } else {
                // Ferry using schedule_accommodations
                return {
                  'selected_transport_class_id': null,
                  'selected_schedule_accommodation_id':
                      widget.booking.selectedFerryAccommodationId,
                };
              }
            }())
          else if (widget.booking.mode != 'ferry') ...{
            'selected_transport_class_id':
                widget.booking.selectedAirlineClassId,
            'selected_schedule_accommodation_id': null,
          },
          if (widget.booking.tripType == 'round_trip' &&
              widget.booking.selectedReturnSchedule != null)
            'return_schedule_id': widget.booking.selectedReturnSchedule!['id'],
          // Return leg: same routing logic
          if (widget.booking.tripType == 'round_trip' &&
              widget.booking.selectedReturnFerryAccommodationId != null)
            ...(() {
              final retClasses =
                  (widget.booking.selectedReturnSchedule?['transport_classes']
                          as List<dynamic>? ??
                      []);
              if (retClasses.isNotEmpty) {
                return {
                  'return_selected_transport_class_id':
                      widget.booking.selectedReturnFerryAccommodationId,
                  'selected_return_schedule_accommodation_id': null,
                };
              } else {
                return {
                  'return_selected_transport_class_id': null,
                  'selected_return_schedule_accommodation_id':
                      widget.booking.selectedReturnFerryAccommodationId,
                };
              }
            }())
          else if (widget.booking.tripType == 'round_trip' &&
              widget.booking.selectedReturnAirlineClassId != null) ...{
            'return_selected_transport_class_id':
                widget.booking.selectedReturnAirlineClassId,
          },
          if (widget.booking.voucherCode != null &&
              !(widget.booking.usePromoTicket &&
                  widget.booking.promotionalTicketId != null))
            'voucher_code': widget.booking.voucherCode,
          if (widget.booking.promotionalTicketId != null)
            'promotional_ticket_id': widget.booking.promotionalTicketId,
          if (_usePoints &&
              !(widget.booking.usePromoTicket &&
                  widget.booking.promotionalTicketId != null))
            'use_points': true,
          // Extra baggage
          'has_extra_baggage': widget.booking.hasExtraBaggage,
          if (widget.booking.hasExtraBaggage) ...{
            if (widget.booking.extraBaggageKg != null) ...{
              'extra_baggage_kg': widget.booking.extraBaggageKg,
              'extra_baggage_price': widget.booking.extraBaggagePrice,
            },
            if (widget.booking.extraBaggageType != null)
              'extra_baggage_type': widget.booking.extraBaggageType,
            if (widget.booking.extraBaggageSpecify != null)
              'extra_baggage_specify': widget.booking.extraBaggageSpecify,
          },
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        BookingData.clearPrefs();
        BookingData.activeSession = null;
        AppEventBus.emit('booking_created');
        AppEventBus.emit('fetch_global_data');

        if (!mounted) return;
        Navigator.of(context).pushReplacement(MaterialPageRoute(
          builder: (_) => PaymentProofScreen(
            bookingId: data['booking_id'],
            transactionNumber: data['transaction_number'],
            totalPrice: _parseDouble(data['total_price']),
            qrCodeUrl: _qrCodeUrl,
            paymentDeadlineAt: data['payment_deadline_at'] != null
                ? DateTime.tryParse(data['payment_deadline_at'])
                : null,
          ),
        ));
      } else {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
              content: Text(data['message'] ?? 'Booking failed.'),
              backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    // ── STEP A: Review form before submitting ──
    final s = widget.booking.selectedSchedule!;
    final pax = widget.booking.passengers;

    // Auto-populate missing class prices (if loaded from older SharedPreferences session)
    if (widget.booking.mode == 'ferry') {
      if ((widget.booking.selectedFerryAccommodationPrice ?? 0) == 0 &&
          widget.booking.selectedFerryAccommodationId != null) {
        final classes = (s['schedule_accommodations'] ??
            s['transport_classes'] ??
            s['accommodations'] ??
            []) as List<dynamic>;
        for (var c in classes) {
          if (c['id'] == widget.booking.selectedFerryAccommodationId) {
            widget.booking.selectedFerryAccommodationPrice =
                _parseDouble(c['price']);
            break;
          }
        }
      }
      if (widget.booking.tripType == 'round_trip' &&
          widget.booking.selectedReturnSchedule != null) {
        if ((widget.booking.selectedReturnFerryAccommodationPrice ?? 0) == 0 &&
            widget.booking.selectedReturnFerryAccommodationId != null) {
          final retS = widget.booking.selectedReturnSchedule!;
          final classes = (retS['schedule_accommodations'] ??
              retS['transport_classes'] ??
              retS['accommodations'] ??
              []) as List<dynamic>;
          for (var c in classes) {
            if (c['id'] == widget.booking.selectedReturnFerryAccommodationId) {
              widget.booking.selectedReturnFerryAccommodationPrice =
                  _parseDouble(c['price']);
              break;
            }
          }
        }
      }
    } else {
      if ((widget.booking.selectedAirlineClassPrice ?? 0) == 0 &&
          widget.booking.selectedAirlineClassId != null) {
        final classes = (s['airline_classes'] ?? s['transport_classes'] ?? [])
            as List<dynamic>;
        for (var c in classes) {
          if (c['id'] == widget.booking.selectedAirlineClassId) {
            widget.booking.selectedAirlineClassPrice = _parseDouble(c['price']);
            break;
          }
        }
      }
      if (widget.booking.tripType == 'round_trip' &&
          widget.booking.selectedReturnSchedule != null) {
        if ((widget.booking.selectedReturnAirlineClassPrice ?? 0) == 0 &&
            widget.booking.selectedReturnAirlineClassId != null) {
          final retS = widget.booking.selectedReturnSchedule!;
          final classes = (retS['airline_classes'] ??
              retS['transport_classes'] ??
              []) as List<dynamic>;
          for (var c in classes) {
            if (c['id'] == widget.booking.selectedReturnAirlineClassId) {
              widget.booking.selectedReturnAirlineClassPrice =
                  _parseDouble(c['price']);
              break;
            }
          }
        }
      }
    }

    return Scaffold(
      resizeToAvoidBottomInset: false,
      appBar: AppBar(title: const Text('Review & Submit')),
      body: Column(
        children: [
          _StepProgress(
              currentStep: 5, steps: _steps, mode: widget.booking.mode),
          Expanded(
            child: Form(
              key: _formKey,
              child: ListView(
                padding: const EdgeInsets.all(16),
                children: [
                  // Trip Summary
                  _SummarySection(title: 'Trip Details', children: [
                    _SummaryRow('Route',
                        '${widget.booking.origin} → ${widget.booking.destination}'),
                    _SummaryRow('Mode',
                        widget.booking.mode == 'ferry' ? 'Ferry' : 'Airline'),
                    _SummaryRow('Date', widget.booking.departureDate),
                    _SummaryRow(
                        'Trip Type',
                        widget.booking.tripType == 'one_way'
                            ? 'One-Way'
                            : 'Round Trip'),
                    if (widget.booking.tripType == 'round_trip') ...[
                      _SummaryRow('Departure Schedule',
                          '${s['service']}  ${s['departure']} – ${s['arrival']}'),
                      _SummaryRow('Departure Ticket & Class',
                          '₱${(_parseDouble(s['price']) + (widget.booking.mode == 'ferry' ? (widget.booking.selectedFerryAccommodationPrice ?? 0) : (widget.booking.selectedAirlineClassPrice ?? 0))).toStringAsFixed(2)} / pax'),
                      if (widget.booking.selectedReturnSchedule != null) ...[
                        _SummaryRow('Return Schedule',
                            '${widget.booking.selectedReturnSchedule!['service']}  ${widget.booking.selectedReturnSchedule!['departure']} – ${widget.booking.selectedReturnSchedule!['arrival']}'),
                        _SummaryRow('Return Ticket & Class',
                            '₱${(_parseDouble(widget.booking.selectedReturnSchedule!['price']) + (widget.booking.mode == 'ferry' ? (widget.booking.selectedReturnFerryAccommodationPrice ?? 0) : (widget.booking.selectedReturnAirlineClassPrice ?? 0))).toStringAsFixed(2)} / pax'),
                      ],
                    ] else ...[
                      _SummaryRow('Schedule',
                          '${s['service']}  ${s['departure']} – ${s['arrival']}'),
                      _SummaryRow('Departure Tickets & Class',
                          '₱${(_parseDouble(s['price']) + (widget.booking.mode == 'ferry' ? (widget.booking.selectedFerryAccommodationPrice ?? 0) : (widget.booking.selectedAirlineClassPrice ?? 0))).toStringAsFixed(2)}'),
                    ],
                    if (widget.booking.hasExtraBaggage &&
                        widget.booking.mode == 'airline')
                      _SummaryRow('Extra Baggage',
                          '${widget.booking.extraBaggageKg ?? 20} kg (₱${widget.booking.extraBaggagePrice.toStringAsFixed(0)}/pax)'),
                    if (widget.booking.hasExtraBaggage &&
                        widget.booking.mode == 'ferry') ...[
                      _SummaryRow('Extra Baggage Category',
                          widget.booking.extraBaggageType ?? 'Specified'),
                      if ((widget.booking.extraBaggageSpecify ?? '').isNotEmpty)
                        _SummaryRow('Baggage Details',
                            widget.booking.extraBaggageSpecify!),
                    ],
                  ]),
                  const SizedBox(height: 16),

                  // Passengers
                  _SummarySection(title: 'Passengers', children: [
                    ...List.generate(
                        pax.length,
                        (i) => _SummaryRow(
                              BookingData.passengerTypeLabel(
                                  pax[i]['type']?.toString() ?? 'adult', i + 1),
                              pax[i]['name'] as String? ?? '',
                            )),
                  ]),
                  const SizedBox(height: 16),

                  // Hotel stays
                  if (widget.booking.selectedAccommodationIds.isNotEmpty) ...[
                    _SummarySection(title: 'Hotel Stays', children: [
                      ...widget.booking.selectedAccommodationIds.map((id) {
                        final acc =
                            widget.booking.availableAccommodations.firstWhere(
                          (a) => a['id'] == id,
                          orElse: () =>
                              {'name': 'Accommodation #$id', 'price': '0'},
                        );
                        final p = double.tryParse(acc['price'].toString()) ?? 0;
                        return _SummaryRow(
                            acc['name'] as String, '₱${p.toStringAsFixed(2)}');
                      }),
                    ]),
                    const SizedBox(height: 16),
                  ],

                  // Vehicle (ferry only)
                  if (widget.booking.hasVehicle) ...[
                    _SummarySection(title: 'Vehicle / Car Booking', children: [
                      _SummaryRow(
                          'Vehicle Type',
                          widget.booking.vehicleType.isEmpty
                              ? '—'
                              : widget.booking.vehicleType),
                      _SummaryRow(
                          'Plate Number',
                          widget.booking.vehiclePlateNumber.isEmpty
                              ? '—'
                              : widget.booking.vehiclePlateNumber),
                      _SummaryRow('Vehicle Fee', () {
                        final p = widget.booking.vehiclePrice;
                        final pts = UserSession.calculateEarnedPoints(p);
                        return pts > 0
                            ? '₱${p.toStringAsFixed(2)}  (+${pts}pts)'
                            : '₱${p.toStringAsFixed(2)}';
                      }()),
                    ]),
                    const SizedBox(height: 16),
                  ],

                  // Contact
                  Card(
                    color: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(14)),
                    child: Padding(
                      padding: const EdgeInsets.all(16),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Contact Details',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 14,
                                  color: kSlate800)),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _clientNameCtrl,
                            decoration: InputDecoration(
                                labelText: 'Contact Name',
                                border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10))),
                            validator: (v) => (v == null || v.trim().isEmpty)
                                ? 'Required'
                                : null,
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _clientEmailCtrl,
                            keyboardType: TextInputType.emailAddress,
                            decoration: InputDecoration(
                                labelText: 'Email',
                                border: OutlineInputBorder(
                                    borderRadius: BorderRadius.circular(10))),
                            validator: (v) => (v == null || v.trim().isEmpty)
                                ? 'Required'
                                : null,
                          ),
                          const SizedBox(height: 12),
                          TextFormField(
                            controller: _clientPhoneCtrl,
                            keyboardType: TextInputType.phone,
                            decoration: InputDecoration(
                              labelText: 'Mobile Phone Number',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)),
                            ),
                            validator: (v) => (v == null || v.trim().isEmpty)
                                ? 'Required'
                                : null,
                          ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 16),

                  if (widget.booking.usePromoTicket &&
                      widget.booking.promotionalTicketId != null) ...[
                    Container(
                      padding: const EdgeInsets.all(12),
                      decoration: BoxDecoration(
                        color: const Color(0xFFFFF3CD),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: const Color(0xFFFFD700)),
                      ),
                      child: const Row(
                        children: [
                          Icon(Icons.info_outline,
                              color: Color(0xFFC08000), size: 20),
                          SizedBox(width: 10),
                          Expanded(
                            child: Text(
                              'Promotional ticket is active. Vouchers, Gracia Points, and passenger discounts cannot be combined with a promo fare.',
                              style: TextStyle(
                                  fontSize: 12,
                                  color: Color(0xFF7B5800),
                                  fontWeight: FontWeight.w500),
                            ),
                          ),
                        ],
                      ),
                    ),
                    const SizedBox(height: 16),
                  ] else ...[
                    const Text('Discount Coupon (Optional)',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            color: kSlate600)),
                    const SizedBox(height: 8),
                    _VoucherSection(
                        booking: widget.booking,
                        scopeFilter: null,
                        onVoucherChanged: () => setState(() {})),
                    const SizedBox(height: 16),
                    if (UserSession.isLoggedIn) ...[
                      const Text('Gracia Points',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 13,
                              color: kSlate600)),
                      const SizedBox(height: 8),
                      Container(
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(8),
                          border: Border.all(color: kSlate200),
                        ),
                        child: SwitchListTile(
                          title: const Text('Use Gracia Points',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold, fontSize: 14)),
                          subtitle: _fetchingPoints
                              ? const Text('Loading...',
                                  style: TextStyle(fontSize: 12))
                              : Text(
                                  'Available: ${_availablePoints.toInt()} pts',
                                  style: const TextStyle(
                                      fontSize: 12, color: kSlate500)),
                          value: _usePoints,
                          onChanged: _availablePoints > 0
                              ? (val) => setState(() => _usePoints = val)
                              : null,
                          activeColor: kGreen,
                        ),
                      ),
                      const SizedBox(height: 24),
                    ],
                  ],

                  Builder(builder: (ctx) {
                    try {
                      double ticketPrice = 0.0;
                      double scheduleAccommodationCost = 0.0;
                      final totalAirlinePassengers = widget.booking.mode == 'airline'
                          ? widget.booking.adults + widget.booking.children + widget.booking.minors + widget.booking.infants
                          : widget.booking.adults + widget.booking.children;
                      int payingAdults = widget.booking.hasVehicle
                          ? (widget.booking.adults - 1).clamp(0, 999)
                          : widget.booking.adults;
                      int payingChildren = widget.booking.children;
                      int payingMinors = widget.booking.mode == 'airline' ? widget.booking.minors : 0;
                      int payingInfants = widget.booking.mode == 'airline' ? widget.booking.infants : 0;
                      int payingPax = payingAdults + payingChildren + payingMinors + payingInfants;

                      if (widget.booking.selectedSchedule != null) {
                        final adultP =
                            (widget.booking.selectedSchedule!['adult_price'] ??
                                widget.booking.selectedSchedule!['price'] ??
                                0);
                        final childP =
                            (widget.booking.selectedSchedule!['child_price'] ??
                                widget.booking.selectedSchedule!['price'] ??
                                0);
                        final minorP =
                            (widget.booking.selectedSchedule!['minor_price'] ??
                                widget.booking.selectedSchedule!['child_price'] ??
                                widget.booking.selectedSchedule!['price'] ??
                                0);
                        final infantP =
                            (widget.booking.selectedSchedule!['infant_price'] ??
                                widget.booking.selectedSchedule!['child_price'] ??
                                widget.booking.selectedSchedule!['price'] ??
                                0);
                        ticketPrice += (payingAdults *
                                (adultP is num
                                    ? adultP.toDouble()
                                    : double.tryParse(adultP.toString()) ??
                                        0)) +
                            (payingChildren *
                                (childP is num
                                    ? childP.toDouble()
                                    : double.tryParse(childP.toString()) ?? 0)) +
                            (payingMinors *
                                (minorP is num
                                    ? minorP.toDouble()
                                    : double.tryParse(minorP.toString()) ?? 0)) +
                            (payingInfants *
                                (infantP is num
                                    ? infantP.toDouble()
                                    : double.tryParse(infantP.toString()) ?? 0));
                        if (widget.booking.selectedScheduleAccommodation !=
                            null) {
                          final accPrice = widget.booking
                                  .selectedScheduleAccommodation!['price'] ??
                              0;
                          scheduleAccommodationCost += (payingPax *
                              (accPrice is num
                                  ? accPrice.toDouble()
                                  : double.tryParse(accPrice.toString()) ?? 0));
                        }
                      }
                      if (widget.booking.tripType == 'round_trip' &&
                          widget.booking.selectedReturnSchedule != null) {
                        final adultP = (widget.booking
                                .selectedReturnSchedule!['adult_price'] ??
                            widget.booking.selectedReturnSchedule!['price'] ??
                            0);
                        final childP = (widget.booking
                                .selectedReturnSchedule!['child_price'] ??
                            widget.booking.selectedReturnSchedule!['price'] ??
                            0);
                        final minorP = (widget.booking
                                .selectedReturnSchedule!['minor_price'] ??
                            widget.booking.selectedReturnSchedule!['child_price'] ??
                            widget.booking.selectedReturnSchedule!['price'] ??
                            0);
                        final infantP = (widget.booking
                                .selectedReturnSchedule!['infant_price'] ??
                            widget.booking.selectedReturnSchedule!['child_price'] ??
                            widget.booking.selectedReturnSchedule!['price'] ??
                            0);
                        ticketPrice += (payingAdults *
                                (adultP is num
                                    ? adultP.toDouble()
                                    : double.tryParse(adultP.toString()) ??
                                        0)) +
                            (payingChildren *
                                (childP is num
                                    ? childP.toDouble()
                                    : double.tryParse(childP.toString()) ?? 0)) +
                            (payingMinors *
                                (minorP is num
                                    ? minorP.toDouble()
                                    : double.tryParse(minorP.toString()) ?? 0)) +
                            (payingInfants *
                                (infantP is num
                                    ? infantP.toDouble()
                                    : double.tryParse(infantP.toString()) ?? 0));
                        if (widget
                                .booking.selectedReturnScheduleAccommodation !=
                            null) {
                          final accPrice = widget.booking
                                      .selectedReturnScheduleAccommodation![
                                  'price'] ??
                              0;
                          scheduleAccommodationCost += (payingPax *
                              (accPrice is num
                                  ? accPrice.toDouble()
                                  : double.tryParse(accPrice.toString()) ?? 0));
                        }
                      }

                      final bool isPromo = widget.booking.usePromoTicket &&
                          widget.booking.promotionalTicketId != null;

                      double passengerDiscount = 0.0;
                      if (!isPromo) {
                        for (var p in widget.booking.passengers) {
                          if (p['discount_id'] != null &&
                              widget.booking.selectedSchedule != null) {
                            final ap = widget
                                    .booking.selectedSchedule!['adult_price'] ??
                                widget.booking.selectedSchedule!['price'] ??
                                0;
                            passengerDiscount += ((ap is num
                                    ? ap.toDouble()
                                    : double.tryParse(ap.toString()) ?? 0) *
                                0.20);
                            if (widget.booking.tripType == 'round_trip' &&
                                widget.booking.selectedReturnSchedule != null) {
                              final rp = widget.booking
                                      .selectedReturnSchedule!['adult_price'] ??
                                  widget.booking
                                      .selectedReturnSchedule!['price'] ??
                                  0;
                              passengerDiscount += ((rp is num
                                      ? rp.toDouble()
                                      : double.tryParse(rp.toString()) ?? 0) *
                                  0.20);
                            }
                          }
                        }
                      }

                      double vehicleCost = 0.0;
                      if (widget.booking.hasVehicle &&
                          widget.booking.mode == 'ferry') {
                        vehicleCost = widget.booking.vehiclePrice;
                      }

                      double accommodationCost = 0.0;
                      if (widget.booking.selectedAccommodationIds.isNotEmpty) {
                        for (var acc
                            in widget.booking.availableAccommodations) {
                          if (widget.booking.selectedAccommodationIds
                              .contains(acc['id'])) {
                            accommodationCost += (acc['price'] ?? 0).toDouble();
                          }
                        }
                      }

                      // Extra baggage cost (airline only — prepaid per pax)
                      double extraBaggageCost = 0.0;
                      if (widget.booking.hasExtraBaggage &&
                          widget.booking.mode == 'airline' &&
                          widget.booking.extraBaggagePrice > 0) {
                        extraBaggageCost = widget.booking.extraBaggagePrice *
                            (widget.booking.adults + widget.booking.children);
                      }

                      int travelers = totalAirlinePassengers;
                      int multiplier = travelers < 1 ? 1 : travelers;

                      double transportClassCost = 0.0;
                      if (widget.booking.mode == 'ferry') {
                        transportClassCost +=
                            widget.booking.selectedFerryAccommodationPrice ?? 0;
                        if (widget.booking.tripType == 'round_trip') {
                          transportClassCost += widget.booking
                                  .selectedReturnFerryAccommodationPrice ??
                              0;
                        }
                      } else {
                        transportClassCost +=
                            widget.booking.selectedAirlineClassPrice ?? 0;
                        if (widget.booking.tripType == 'round_trip') {
                          transportClassCost +=
                              widget.booking.selectedReturnAirlineClassPrice ??
                                  0;
                        }
                      }
                      transportClassCost = transportClassCost * payingPax;

                      double calculationFee = (multiplier * _feePerPerson) +
                          (accommodationCost > 0 ? _feePerAccommodation : 0);
                      double transactionFeeTotal = multiplier * _transactionFee;

                      double subtotal = ticketPrice +
                          scheduleAccommodationCost +
                          transportClassCost +
                          vehicleCost +
                          accommodationCost +
                          calculationFee +
                          extraBaggageCost +
                          transactionFeeTotal -
                          passengerDiscount;
                      if (subtotal < 0) subtotal = 0.0;

                      // Voucher and points are blocked when promo ticket is active
                      final discount = (!isPromo &&
                              widget.booking.voucherData != null)
                          ? _parseDouble(
                              widget.booking.voucherData!['discount_amount'])
                          : 0.0;
                      double totalBeforePoints = subtotal - discount;
                      if (totalBeforePoints < 0) totalBeforePoints = 0.0;

                      double pointsDiscount = 0.0;
                      if (!isPromo && _usePoints) {
                        pointsDiscount = _availablePoints > totalBeforePoints
                            ? totalBeforePoints.ceilToDouble()
                            : _availablePoints;
                      }

                      final finalTotal =
                          (totalBeforePoints - pointsDiscount) > 0
                              ? (totalBeforePoints - pointsDiscount)
                              : 0.0;
                      final webAdminFee = multiplier * _feePerPerson;
                      final eligiblePointsTotal =
                          (finalTotal - webAdminFee - transactionFeeTotal)
                              .clamp(0.0, double.infinity);

                      return Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          _SummarySection(title: 'Payment Summary', children: [
                            if (widget.booking.tripType == 'round_trip') ...[
                              // Departure leg cost
                              Builder(builder: (_) {
                                double depTicket = 0;
                                double depClass = 0;
                                if (widget.booking.selectedSchedule != null) {
                                  final adultP = widget.booking.selectedSchedule!['adult_price'] ?? widget.booking.selectedSchedule!['price'] ?? 0;
                                  final childP = widget.booking.selectedSchedule!['child_price'] ?? widget.booking.selectedSchedule!['price'] ?? 0;
                                  final minorP = widget.booking.selectedSchedule!['minor_price'] ?? widget.booking.selectedSchedule!['child_price'] ?? widget.booking.selectedSchedule!['price'] ?? 0;
                                  final infantP = widget.booking.selectedSchedule!['infant_price'] ?? widget.booking.selectedSchedule!['child_price'] ?? widget.booking.selectedSchedule!['price'] ?? 0;
                                  depTicket = (payingAdults * (adultP is num ? adultP.toDouble() : double.tryParse(adultP.toString()) ?? 0)) +
                                      (payingChildren * (childP is num ? childP.toDouble() : double.tryParse(childP.toString()) ?? 0)) +
                                      (payingMinors * (minorP is num ? minorP.toDouble() : double.tryParse(minorP.toString()) ?? 0)) +
                                      (payingInfants * (infantP is num ? infantP.toDouble() : double.tryParse(infantP.toString()) ?? 0));
                                }
                                if (widget.booking.mode == 'ferry') {
                                  depClass = (widget.booking.selectedFerryAccommodationPrice ?? 0) * payingPax;
                                } else {
                                  depClass = (widget.booking.selectedAirlineClassPrice ?? 0) * payingPax;
                                }
                                return _SummaryRow(
                                    'Departure Tickets & Class (${payingPax}x)',
                                    '₱${(depTicket + depClass).toStringAsFixed(2)}');
                              }),
                              // Return leg cost
                              Builder(builder: (_) {
                                double retTicket = 0;
                                double retClass = 0;
                                if (widget.booking.selectedReturnSchedule != null) {
                                  final adultP = widget.booking.selectedReturnSchedule!['adult_price'] ?? widget.booking.selectedReturnSchedule!['price'] ?? 0;
                                  final childP = widget.booking.selectedReturnSchedule!['child_price'] ?? widget.booking.selectedReturnSchedule!['price'] ?? 0;
                                  final minorP = widget.booking.selectedReturnSchedule!['minor_price'] ?? widget.booking.selectedReturnSchedule!['child_price'] ?? widget.booking.selectedReturnSchedule!['price'] ?? 0;
                                  final infantP = widget.booking.selectedReturnSchedule!['infant_price'] ?? widget.booking.selectedReturnSchedule!['child_price'] ?? widget.booking.selectedReturnSchedule!['price'] ?? 0;
                                  retTicket = (payingAdults * (adultP is num ? adultP.toDouble() : double.tryParse(adultP.toString()) ?? 0)) +
                                      (payingChildren * (childP is num ? childP.toDouble() : double.tryParse(childP.toString()) ?? 0)) +
                                      (payingMinors * (minorP is num ? minorP.toDouble() : double.tryParse(minorP.toString()) ?? 0)) +
                                      (payingInfants * (infantP is num ? infantP.toDouble() : double.tryParse(infantP.toString()) ?? 0));
                                }
                                if (widget.booking.mode == 'ferry') {
                                  retClass = (widget.booking.selectedReturnFerryAccommodationPrice ?? 0) * payingPax;
                                } else {
                                  retClass = (widget.booking.selectedReturnAirlineClassPrice ?? 0) * payingPax;
                                }
                                return _SummaryRow(
                                    'Return Tickets & Class (${payingPax}x)',
                                    '₱${(retTicket + retClass).toStringAsFixed(2)}');
                              }),
                            ] else ...[
                              _SummaryRow(
                                  'Departure Tickets & Class (${payingPax}x)',
                                  '₱${(ticketPrice + transportClassCost).toStringAsFixed(2)}'),
                            ],
                            if (scheduleAccommodationCost > 0)
                              _SummaryRow('Accommodation',
                                  '₱${scheduleAccommodationCost.toStringAsFixed(2)}'),
                            if (passengerDiscount > 0)
                              _SummaryRow('Passenger Discount',
                                  '-₱${passengerDiscount.toStringAsFixed(2)}'),
                            if (vehicleCost > 0)
                              _SummaryRow('Vehicle Freight',
                                  '₱${vehicleCost.toStringAsFixed(2)}'),
                            if (accommodationCost > 0)
                              _SummaryRow('Stay',
                                  '₱${accommodationCost.toStringAsFixed(2)}'),
                            if (extraBaggageCost > 0)
                              _SummaryRow(
                                  'Extra Baggage (${widget.booking.extraBaggageKg} kg)',
                                  '₱${extraBaggageCost.toStringAsFixed(2)}'),
                            if (widget.booking.hasExtraBaggage &&
                                widget.booking.mode == 'ferry')
                              _SummaryRow(
                                  'Extra Baggage (${widget.booking.extraBaggageType ?? 'Specified'})',
                                  'Settled at Terminal'),
                            if (calculationFee > 0)
                              _SummaryRow('Web Admin Fee',
                                  '₱${calculationFee.toStringAsFixed(2)}'),
                            if (transactionFeeTotal > 0)
                              _SummaryRow('Transaction Fee',
                                  '₱${transactionFeeTotal.toStringAsFixed(2)}'),
                            const Divider(height: 16),
                            _SummaryRow(
                                'Subtotal', '₱${subtotal.toStringAsFixed(2)}'),
                            if (discount > 0)
                              _SummaryRow('Voucher Discount',
                                  '-₱${discount.toStringAsFixed(2)}'),
                            if (pointsDiscount > 0)
                              _SummaryRow(
                                  'Points Discount (${pointsDiscount.toInt()} pts)',
                                  '-₱${pointsDiscount.toStringAsFixed(2)}'),
                            const Divider(height: 16),
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                const Text('Grand Total',
                                    style: TextStyle(
                                        fontWeight: FontWeight.bold,
                                        fontSize: 16,
                                        color: kSlate800)),
                                Text('₱${finalTotal.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                        fontWeight: FontWeight.w900,
                                        color: kPink,
                                        fontSize: 18)),
                              ],
                            ),
                          ]),
                          const SizedBox(height: 16),
                          Builder(builder: (ctx) {
                            final pts = UserSession.calculateEarnedPoints(
                                eligiblePointsTotal);
                            if (pts > 0) {
                              return Padding(
                                padding: const EdgeInsets.only(bottom: 16),
                                child: Container(
                                  padding: const EdgeInsets.all(12),
                                  decoration: BoxDecoration(
                                    color: kPink.withOpacity(0.08),
                                    borderRadius: BorderRadius.circular(10),
                                    border: Border.all(
                                        color: kPink.withOpacity(0.3)),
                                  ),
                                  child: Row(
                                    mainAxisAlignment: MainAxisAlignment.center,
                                    children: [
                                      const Icon(Icons.star_rounded,
                                          color: kPink, size: 20),
                                      const SizedBox(width: 8),
                                      Flexible(
                                        child: Text(
                                          'You will earn $pts Gracia Points for this booking!',
                                          style: const TextStyle(
                                              color: kPink,
                                              fontWeight: FontWeight.bold,
                                              fontSize: 13),
                                        ),
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            }
                            return const SizedBox();
                          }),
                        ],
                      );
                    } catch (e) {
                      return Container(
                        padding: const EdgeInsets.all(16),
                        decoration: BoxDecoration(
                            color: Colors.orange.shade50,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: Colors.orange.shade200)),
                        child: Text('Could not calculate price summary: $e',
                            style: const TextStyle(
                                color: Colors.orange, fontSize: 12)),
                      );
                    }
                  }),

                  const SizedBox(height: 16),

                  SizedBox(
                    width: double.infinity,
                    height: 52,
                    child: ElevatedButton(
                      onPressed: _isSubmitting ? null : _submit,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: kGreen,
                        foregroundColor: Colors.white,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(12)),
                        elevation: 4,
                      ),
                      child: _isSubmitting
                          ? const SizedBox(
                              width: 22,
                              height: 22,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2.5))
                          : const Text('Submit Booking',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold, fontSize: 16)),
                    ),
                  ),
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _SummarySection extends StatelessWidget {
  final String title;
  final List<Widget> children;
  const _SummarySection({required this.title, required this.children});

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.white,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
      child: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(title,
                style: const TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 14,
                    color: kSlate800)),
            const Divider(height: 16),
            ...children,
          ],
        ),
      ),
    );
  }
}

class _SummaryRow extends StatelessWidget {
  final String label;
  final String value;
  final bool showDivider;
  const _SummaryRow(this.label, this.value, {this.showDivider = true});

  @override
  Widget build(BuildContext context) {
    return Column(
      children: [
        Padding(
          padding: const EdgeInsets.symmetric(vertical: 4),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Expanded(
                flex: 1,
                child: Text(label,
                    style: const TextStyle(color: kSlate500, fontSize: 13)),
              ),
              const SizedBox(width: 16),
              Expanded(
                flex: 2,
                child: Text(
                  value,
                  style: const TextStyle(
                      color: kSlate800, fontSize: 13, fontWeight: FontWeight.bold),
                  textAlign: TextAlign.end,
                ),
              ),
            ],
          ),
        ),
        if (showDivider)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 2),
            child: Divider(color: kSlate200, height: 1),
          ),
      ],
    );
  }
}

// ==========================================
// BOOKING SUCCESS SCREEN
// ==========================================
class PaymentProofScreen extends StatefulWidget {
  final int bookingId;
  final String transactionNumber;
  final double totalPrice;
  final DateTime? paymentDeadlineAt;
  final String? qrCodeUrl;

  const PaymentProofScreen({
    super.key,
    required this.bookingId,
    required this.transactionNumber,
    required this.totalPrice,
    this.paymentDeadlineAt,
    this.qrCodeUrl,
  });

  @override
  State<PaymentProofScreen> createState() => _PaymentProofScreenState();
}

class _PaymentProofScreenState extends State<PaymentProofScreen> {
  String? _qrCodeUrl;
  bool _loadingPaymentSettings = true;
  XFile? _proofImage;
  bool _isUploadingProof = false;
  bool _proofUploaded = false;
  bool _isExpired = false;
  Timer? _countdownTimer;
  String _countdownText = '--:--:--';
  final TextEditingController _refController = TextEditingController();

  @override
  void initState() {
    super.initState();
    _qrCodeUrl = widget.qrCodeUrl;
    if (_qrCodeUrl == null) {
      _fetchPaymentSettings();
    } else {
      _loadingPaymentSettings = false;
    }
    if (widget.paymentDeadlineAt != null) {
      _startCountdown();
    }
  }

  @override
  void dispose() {
    _countdownTimer?.cancel();
    _refController.dispose();
    super.dispose();
  }

  void _fetchPaymentSettings() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/payment-settings'),
          headers: {'Accept': 'application/json'});
      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        if (data['status'] == 'success') {
          if (mounted) setState(() => _qrCodeUrl = data['qr_code_url']);
        }
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingPaymentSettings = false);
    }
  }

  void _startCountdown() {
    _updateCountdown();
    _countdownTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      _updateCountdown();
    });
  }

  void _updateCountdown() {
    if (widget.paymentDeadlineAt == null) return;
    final now = DateTime.now();
    final diff = widget.paymentDeadlineAt!.difference(now);
    if (diff.isNegative) {
      _countdownTimer?.cancel();
      if (mounted) {
        setState(() {
          _isExpired = true;
          _countdownText = '00:00:00';
        });
      }
    } else {
      if (mounted) {
        setState(() {
          final h = diff.inHours.toString().padLeft(2, '0');
          final m = (diff.inMinutes % 60).toString().padLeft(2, '0');
          final s = (diff.inSeconds % 60).toString().padLeft(2, '0');
          _countdownText = '$h:$m:$s';
        });
      }
    }
  }

  Future<void> _pickProofImage() async {
    final ImagePicker picker = ImagePicker();
    try {
      final XFile? image =
          await picker.pickImage(source: ImageSource.gallery, imageQuality: 70);
      if (image != null && mounted) {
        setState(() => _proofImage = image);
      }
    } catch (e) {
      if (mounted) {
        showTopSnack(
            context, SnackBar(content: Text('Error picking image: $e')));
      }
    }
  }

  Future<void> _uploadProof() async {
    if (_proofImage == null) return;
    if (_refController.text.trim().isEmpty) {
      showTopSnack(
          context,
          const SnackBar(
              content: Text('Please enter the reference number.'),
              backgroundColor: Colors.red));
      return;
    }
    setState(() => _isUploadingProof = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final url = Uri.parse('$baseUrl/api/bookings/${widget.bookingId}/proof');
      final request = http.MultipartRequest('POST', url);
      if (UserSession.token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer ${UserSession.token}';
      }
      request.headers['Accept'] = 'application/json';
      request.fields['email'] = UserSession.email;
      request.fields['reference_number'] = _refController.text.trim();
      request.files
          .add(await http.MultipartFile.fromPath('proof', _proofImage!.path));
      final streamed = await request.send();
      final res = await http.Response.fromStream(streamed);
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        if (mounted) {
          setState(() {
            _proofUploaded = true;
            _countdownTimer?.cancel();
          });
          showTopSnack(
            context,
            const SnackBar(
                content: Text(
                    'Proof of payment uploaded! We will verify it shortly.'),
                backgroundColor: Colors.green),
          );
        }
      } else {
        if (mounted) {
          showTopSnack(
              context,
              SnackBar(
                  content: Text(data['message'] ?? 'Upload failed.'),
                  backgroundColor: Colors.red));
        }
      }
    } catch (e) {
      if (mounted) {
        showTopSnack(
            context,
            SnackBar(
                content: Text('Upload error: $e'),
                backgroundColor: Colors.red));
      }
    } finally {
      if (mounted) setState(() => _isUploadingProof = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
          title: const Text('Payment'), automaticallyImplyLeading: false),
      body: ListView(
        padding: const EdgeInsets.all(20),
        children: [
          // Success banner
          Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(
                color: kGreen.withOpacity(0.08),
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: kGreen.withOpacity(0.3))),
            child: Column(
              children: [
                const Icon(Icons.check_circle, color: kGreen, size: 48),
                const SizedBox(height: 8),
                const Text('Booking Confirmed!',
                    style: TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 20,
                        color: kGreen)),
                const SizedBox(height: 4),
                Text('Transaction #: ${widget.transactionNumber}',
                    style: const TextStyle(color: kSlate600, fontSize: 13)),
                const SizedBox(height: 4),
                Text('Total: ₱${widget.totalPrice.toStringAsFixed(2)}',
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: kPink)),
              ],
            ),
          ),
          const SizedBox(height: 24),

          if (widget.paymentDeadlineAt != null && !_proofUploaded)
            Card(
              color: _isExpired ? Colors.red.shade50 : Colors.orange.shade50,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14)),
              child: Padding(
                padding: const EdgeInsets.all(18),
                child: Column(
                  children: [
                    Text(
                      _isExpired
                          ? 'Payment Window Expired'
                          : 'Time Remaining to Pay',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          color: _isExpired
                              ? Colors.red.shade800
                              : Colors.orange.shade900),
                    ),
                    const SizedBox(height: 8),
                    Text(
                      _countdownText,
                      style: TextStyle(
                          fontSize: 32,
                          fontWeight: FontWeight.w900,
                          color:
                              _isExpired ? Colors.red : Colors.orange.shade800,
                          letterSpacing: 2),
                    ),
                  ],
                ),
              ),
            ),

          const SizedBox(height: 20),

          // QR Code section
          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            child: Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Payment QR Code',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                          color: kSlate800)),
                  const SizedBox(height: 4),
                  const Text(
                      'Scan the QR code below to pay via GCash, Maya, or bank transfer.',
                      style: TextStyle(fontSize: 12, color: kSlate500)),
                  const SizedBox(height: 16),
                  Center(
                    child: _loadingPaymentSettings
                        ? const CircularProgressIndicator(color: kGreen)
                        : _qrCodeUrl != null
                            ? ClipRRect(
                                borderRadius: BorderRadius.circular(12),
                                child: Image.network(
                                  _qrCodeUrl!,
                                  width: 220,
                                  height: 220,
                                  fit: BoxFit.contain,
                                  errorBuilder: (_, __, ___) => Container(
                                    width: 220,
                                    height: 220,
                                    decoration: BoxDecoration(
                                        color: kSlate100,
                                        borderRadius:
                                            BorderRadius.circular(12)),
                                    child: const Column(
                                      mainAxisAlignment:
                                          MainAxisAlignment.center,
                                      children: [
                                        Icon(Icons.qr_code,
                                            size: 64, color: kSlate400),
                                        SizedBox(height: 8),
                                        Text('QR Code unavailable',
                                            style: TextStyle(
                                                color: kSlate400,
                                                fontSize: 12)),
                                      ],
                                    ),
                                  ),
                                ))
                            : Container(
                                width: 220,
                                height: 220,
                                decoration: BoxDecoration(
                                    color: kSlate100,
                                    borderRadius: BorderRadius.circular(12)),
                                child: const Column(
                                  mainAxisAlignment: MainAxisAlignment.center,
                                  children: [
                                    Icon(Icons.qr_code,
                                        size: 64, color: kSlate400),
                                    SizedBox(height: 8),
                                    Text('No QR code set',
                                        style: TextStyle(
                                            color: kSlate400, fontSize: 12)),
                                    SizedBox(height: 4),
                                    Text('Please contact the admin.',
                                        style: TextStyle(
                                            color: kSlate400, fontSize: 11)),
                                  ],
                                ),
                              ),
                  ),
                ],
              ),
            ),
          ),
          const SizedBox(height: 20),

          // Proof of payment upload section
          Card(
            color: Colors.white,
            shape:
                RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
            child: Padding(
              padding: const EdgeInsets.all(18),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Attach Proof of Payment',
                      style: TextStyle(
                          fontWeight: FontWeight.bold,
                          fontSize: 14,
                          color: kSlate800)),
                  const SizedBox(height: 4),
                  const Text(
                      'Upload a screenshot or photo of your payment receipt.',
                      style: TextStyle(fontSize: 12, color: kSlate500)),
                  const SizedBox(height: 16),
                  if (_proofUploaded)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                          color: Colors.green.withOpacity(0.08),
                          borderRadius: BorderRadius.circular(10)),
                      child: const Row(
                        children: [
                          Icon(Icons.check_circle, color: Colors.green),
                          SizedBox(width: 10),
                          Expanded(
                              child: Text(
                                  'Proof uploaded! Our team will verify your payment within 24 hours.',
                                  style: TextStyle(
                                      color: Colors.green, fontSize: 13))),
                        ],
                      ),
                    )
                  else if (_isExpired)
                    Container(
                      padding: const EdgeInsets.all(14),
                      decoration: BoxDecoration(
                          color: Colors.red.withOpacity(0.08),
                          borderRadius: BorderRadius.circular(10)),
                      child: const Row(
                        children: [
                          Icon(Icons.cancel, color: Colors.red),
                          SizedBox(width: 10),
                          Expanded(
                              child: Text(
                                  'Payment time expired. Your booking has been cancelled.',
                                  style: TextStyle(
                                      color: Colors.red, fontSize: 13))),
                        ],
                      ),
                    )
                  else ...[
                    // Image preview
                    if (_proofImage != null) ...[
                      ClipRRect(
                        borderRadius: BorderRadius.circular(10),
                        child: Image.file(File(_proofImage!.path),
                            height: 180,
                            width: double.infinity,
                            fit: BoxFit.cover),
                      ),
                      const SizedBox(height: 12),
                    ] else
                      GestureDetector(
                        onTap: _pickProofImage,
                        child: Container(
                          height: 120,
                          width: double.infinity,
                          decoration: BoxDecoration(
                            color: kSlate50,
                            border: Border.all(color: kSlate200, width: 2),
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: const Column(
                            mainAxisAlignment: MainAxisAlignment.center,
                            children: [
                              Icon(Icons.add_photo_alternate_outlined,
                                  size: 40, color: kSlate400),
                              SizedBox(height: 8),
                              Text('Tap to select image',
                                  style: TextStyle(
                                      color: kSlate400, fontSize: 13)),
                            ],
                          ),
                        ),
                      ),

                    const SizedBox(height: 16),
                    const Text('Reference Number',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: kSlate800)),
                    const SizedBox(height: 6),
                    TextField(
                      controller: _refController,
                      decoration: InputDecoration(
                        hintText: 'e.g. 000123456789',
                        hintStyle:
                            const TextStyle(color: kSlate400, fontSize: 13),
                        filled: true,
                        fillColor: kSlate50,
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: kSlate200)),
                        enabledBorder: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10),
                            borderSide: const BorderSide(color: kSlate200)),
                      ),
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        if (_proofImage != null) ...[
                          SizedBox(
                            width: 52,
                            child: IconButton.filledTonal(
                              tooltip: 'Change image',
                              onPressed: _pickProofImage,
                              icon: const Icon(Icons.image_outlined, size: 20),
                              style: IconButton.styleFrom(
                                foregroundColor: kSlate600,
                                side: const BorderSide(color: kSlate200),
                                backgroundColor: kSlate50,
                              ),
                            ),
                          ),
                          const SizedBox(width: 10),
                        ],
                        Expanded(
                          flex: 2,
                          child: ElevatedButton.icon(
                            onPressed: (_proofImage == null ||
                                    _isUploadingProof ||
                                    _isExpired)
                                ? null
                                : _uploadProof,
                            icon: _isUploadingProof
                                ? const SizedBox(
                                    width: 16,
                                    height: 16,
                                    child: CircularProgressIndicator(
                                        strokeWidth: 2, color: Colors.white))
                                : const Icon(Icons.upload, size: 16),
                            label: Text(_isUploadingProof
                                ? 'Uploading...'
                                : 'Upload Proof'),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: kGreen,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ),
                      ],
                    ),
                  ],
                ],
              ),
            ),
          ),
          const SizedBox(height: 24),

          // Done button
          SizedBox(
            width: double.infinity,
            height: 52,
            child: ElevatedButton(
              onPressed: () {
                if (!_proofUploaded && !_isExpired) {
                  showTopSnack(
                    context,
                    const SnackBar(
                        content: Text(
                            'Please upload proof of payment before proceeding. Or press back if you wish to do it later.')),
                  );
                  return;
                }
                if (_proofUploaded || _isExpired) {
                  Navigator.popUntil(context, (route) => route.isFirst);
                } else {
                  Navigator.pushAndRemoveUntil(
                    context,
                    MaterialPageRoute(
                      builder: (_) => BookingSuccessScreen(
                        transactionNumber: widget.transactionNumber,
                        totalPrice: widget.totalPrice,
                      ),
                    ),
                    (route) => route.isFirst,
                  );
                }
              },
              style: ElevatedButton.styleFrom(
                backgroundColor: kPink,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 4,
              ),
              child: const Text('Done',
                  style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
            ),
          ),
        ],
      ),
    );
  }
}

class BookingSuccessScreen extends StatelessWidget {
  final String transactionNumber;
  final double totalPrice;

  const BookingSuccessScreen(
      {super.key, required this.transactionNumber, required this.totalPrice});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
          title: const Text('Booking Success'),
          automaticallyImplyLeading: false),
      body: Padding(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            const Icon(Icons.check_circle, color: kGreen, size: 96),
            const SizedBox(height: 24),
            const Text('Booking Submitted!',
                style: TextStyle(
                    fontSize: 24, fontWeight: FontWeight.bold, color: kGreen)),
            const SizedBox(height: 8),
            const Text(
              'Your booking has been submitted.\n\nCancellation is free within 5 minutes after providing proof of payment.',
              textAlign: TextAlign.center,
              style: TextStyle(color: kSlate600, fontSize: 14),
            ),
            const SizedBox(height: 32),
            Card(
              color: kSlate100,
              elevation: 0,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
              child: Padding(
                padding: const EdgeInsets.all(20),
                child: Column(
                  children: [
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Transaction #',
                            style: TextStyle(color: kSlate600)),
                        Text(transactionNumber,
                            style: const TextStyle(
                                fontWeight: FontWeight.bold, color: kSlate800)),
                      ],
                    ),
                    const Divider(height: 24),
                    Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        const Text('Total Amount',
                            style: TextStyle(color: kSlate600)),
                        Text('₱${totalPrice.toStringAsFixed(2)}',
                            style: const TextStyle(
                                fontWeight: FontWeight.w900,
                                color: kPink,
                                fontSize: 18)),
                      ],
                    ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 40),
            SizedBox(
              width: double.infinity,
              height: 52,
              child: ElevatedButton(
                onPressed: () =>
                    Navigator.popUntil(context, (route) => route.isFirst),
                style: ElevatedButton.styleFrom(
                    backgroundColor: kGreen,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12))),
                child: const Text('Back to Home',
                    style:
                        TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ==========================================
// VOUCHER SECTION WIDGET (for booking flow)
// ==========================================
class _VoucherSection extends StatefulWidget {
  final BookingData booking;
  final String? scopeFilter;
  final VoidCallback onVoucherChanged;

  const _VoucherSection({
    required this.booking,
    this.scopeFilter,
    required this.onVoucherChanged,
  });

  @override
  State<_VoucherSection> createState() => _VoucherSectionState();
}

class _VoucherSectionState extends State<_VoucherSection> {
  @override
  Widget build(BuildContext context) {
    final vCode = widget.booking.voucherCode;
    final vData = widget.booking.voucherData;

    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        border: Border.all(color: kSlate200),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        mainAxisSize: MainAxisSize.min,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    Text('Discount Coupon',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 15,
                            color: kSlate800)),
                    Text('Apply your discount coupon here',
                        style: TextStyle(color: kSlate500, fontSize: 12)),
                  ],
                ),
              ),
              const SizedBox(width: 8),
              OutlinedButton.icon(
                onPressed: () async {
                  final result = await Navigator.push(
                    context,
                    MaterialPageRoute(
                      builder: (_) => VoucherPickerScreen(
                          booking: widget.booking, scopeFilter: null),
                    ),
                  );
                  if (mounted) {
                    setState(() {});
                    if (result != null || widget.booking.voucherCode == null) {
                      widget.onVoucherChanged();
                    }
                  }
                },
                icon: const Icon(Icons.card_giftcard, size: 16, color: kPink),
                label: Text(
                  vCode != null ? 'Change' : 'Select',
                  style: const TextStyle(
                      color: kPink, fontWeight: FontWeight.bold, fontSize: 13),
                ),
                style: OutlinedButton.styleFrom(
                  side: const BorderSide(color: kPink, width: 1.5),
                  shape: RoundedRectangleBorder(
                      borderRadius: BorderRadius.circular(8)),
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  minimumSize: Size.zero,
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
              ),
            ],
          ),
          if (vCode != null) ...[
            const SizedBox(height: 14),
            _DiscountCouponCard(
              voucher: vData,
              isSelected: true,
            ),
            const SizedBox(height: 4),
            GestureDetector(
              onTap: () {
                setState(() {
                  widget.booking.voucherCode = null;
                  widget.booking.voucherData = null;
                });
                widget.onVoucherChanged();
              },
              child: const Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Icon(Icons.close, size: 14, color: kSlate500),
                  SizedBox(width: 4),
                  Text('Remove coupon',
                      style: TextStyle(color: kSlate500, fontSize: 12)),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}

// ==========================================
// VOUCHER PICKER SCREEN (booking context)
// ==========================================
class VoucherPickerScreen extends StatefulWidget {
  final BookingData booking;
  final String?
      scopeFilter; // null = all scopes, 'ticket_fare', 'vehicle', 'accommodation', 'booking_total'
  const VoucherPickerScreen(
      {super.key, required this.booking, this.scopeFilter});

  @override
  State<VoucherPickerScreen> createState() => _VoucherPickerScreenState();
}

class _VoucherPickerScreenState extends State<VoucherPickerScreen> {
  List<dynamic> _vouchers = [];
  bool _isLoading = true;
  bool _isValidating = false;
  final TextEditingController _codeCtrl = TextEditingController();
  String? _errorMsg;

  @override
  void initState() {
    super.initState();
    _fetchVouchers();
    _codeCtrl.text = widget.booking.voucherCode ?? '';
  }

  @override
  void dispose() {
    _codeCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchVouchers() async {
    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/vouchers'),
        headers: {
          'Accept': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        var vouchers = data['vouchers'] as List<dynamic>? ?? [];
        if (widget.scopeFilter != null) {
          vouchers = vouchers
              .where((v) => v['eligible_scope'] == widget.scopeFilter)
              .toList();
        }
        if (mounted) {
          setState(() {
            _vouchers = vouchers;
            _isLoading = false;
          });
        }
      } else {
        if (mounted) setState(() => _isLoading = false);
      }
    } catch (_) {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _applyCode(String code) async {
    if (code.trim().isEmpty) return;
    setState(() {
      _isValidating = true;
      _errorMsg = null;
    });
    try {
      final booking = widget.booking;
      final body = {
        'voucher_code': code.trim().toUpperCase(),
        'schedule_id': booking.selectedSchedule?['id'] ?? 0,
        'origin': booking.origin,
        'destination': booking.destination,
        'trip_type': booking.tripType,
        'client_email': booking.clientEmail.isNotEmpty
            ? booking.clientEmail
            : UserSession.email,
        'passengers': booking.passengers.isNotEmpty
            ? booking.passengers
                .map(
                    (p) => {'type': p['type'], 'discount_id': p['discount_id']})
                .toList()
            : [
                {'type': 'adult', 'discount_id': null}
              ],
        'accommodation_ids': booking.selectedAccommodationIds,
        'has_vehicle': booking.hasVehicle,
        if (booking.hasVehicle) 'vehicle_price': booking.vehiclePrice,
        // Include accommodation/class IDs so backend calculates correct base amount
        if (booking.mode == 'ferry' &&
            booking.selectedFerryAccommodationId != null)
          ...(() {
            final depClasses = (booking.selectedSchedule?['transport_classes']
                    as List<dynamic>? ??
                []);
            if (depClasses.isNotEmpty) {
              return {
                'selected_transport_class_id':
                    booking.selectedFerryAccommodationId
              };
            } else {
              return {
                'selected_schedule_accommodation_id':
                    booking.selectedFerryAccommodationId
              };
            }
          }())
        else if (booking.mode != 'ferry' &&
            booking.selectedAirlineClassId != null)
          'selected_transport_class_id': booking.selectedAirlineClassId,
        if (booking.tripType == 'round_trip' &&
            booking.selectedReturnFerryAccommodationId != null)
          ...(() {
            final retClasses =
                (booking.selectedReturnSchedule?['transport_classes']
                        as List<dynamic>? ??
                    []);
            if (retClasses.isNotEmpty) {
              return {
                'return_selected_transport_class_id':
                    booking.selectedReturnFerryAccommodationId
              };
            } else {
              return {
                'selected_return_schedule_accommodation_id':
                    booking.selectedReturnFerryAccommodationId
              };
            }
          }())
        else if (booking.tripType == 'round_trip' &&
            booking.selectedReturnAirlineClassId != null)
          'return_selected_transport_class_id':
              booking.selectedReturnAirlineClassId,
      };
      final res = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/vouchers/validate'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
        body: jsonEncode(body),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        final d = data['data'];
        widget.booking.voucherCode = code.trim().toUpperCase();
        widget.booking.voucherData = {
          'name': d['voucher_name'],
          'discount_type': d['discount_type'],
          'discount_value': d['discount_value'],
          'eligible_scope': d['eligible_scope'],
          'discount_amount': d['discount_amount'],
          'final_total': d['final_total'],
          'original_subtotal': d['original_subtotal'],
        };
        if (mounted) Navigator.pop(context, widget.booking.voucherData);
      } else {
        setState(() => _errorMsg = data['message'] ?? 'Invalid voucher code.');
      }
    } catch (e) {
      setState(() => _errorMsg = 'Error: $e');
    } finally {
      if (mounted) setState(() => _isValidating = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final currentCode = widget.booking.voucherCode;
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Select Discount Coupon'),
        backgroundColor: kGreen,
        foregroundColor: Colors.white,
      ),
      body: Column(
        children: [
          // Input code bar
          Container(
            padding: const EdgeInsets.fromLTRB(16, 16, 16, 12),
            color: Colors.white,
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    Expanded(
                      child: TextField(
                        controller: _codeCtrl,
                        textCapitalization: TextCapitalization.characters,
                        decoration: InputDecoration(
                          hintText: 'Enter voucher / promo code',
                          prefixIcon: const Icon(
                              Icons.confirmation_num_outlined,
                              color: kPink),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10)),
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 12),
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    ElevatedButton(
                      onPressed: _isValidating
                          ? null
                          : () => _applyCode(_codeCtrl.text),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: kPink,
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(
                            horizontal: 18, vertical: 14),
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(10)),
                      ),
                      child: _isValidating
                          ? const SizedBox(
                              width: 18,
                              height: 18,
                              child: CircularProgressIndicator(
                                  color: Colors.white, strokeWidth: 2))
                          : const Text('Apply',
                              style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
                if (_errorMsg != null) ...[
                  const SizedBox(height: 8),
                  Text(_errorMsg!,
                      style: const TextStyle(color: Colors.red, fontSize: 13))
                ],
                if (currentCode != null &&
                    widget.booking.voucherData != null) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                    decoration: BoxDecoration(
                        color: kGreen.withOpacity(0.08),
                        borderRadius: BorderRadius.circular(8),
                        border:
                            Border.all(color: kGreen.withOpacity(0.3))),
                    child: Row(
                      children: [
                        const Icon(Icons.check_circle, color: kGreen, size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            'Applied: $currentCode — Saves ₱${_parseDouble(widget.booking.voucherData!['discount_amount']).toStringAsFixed(2)}',
                            style: const TextStyle(
                                color: kGreen,
                                fontWeight: FontWeight.bold,
                                fontSize: 13),
                          ),
                        ),
                        GestureDetector(
                          onTap: () {
                            widget.booking.voucherCode = null;
                            widget.booking.voucherData = null;
                            _codeCtrl.clear();
                            setState(() {});
                          },
                          child: const Icon(Icons.close,
                              color: kSlate500, size: 20),
                        ),
                      ],
                    ),
                  ),
                ],
              ],
            ),
          ),
          const Divider(height: 1),
          // Voucher list
          Expanded(
            child: _isLoading
                ? const Center(child: CircularProgressIndicator(color: kPink))
                : _vouchers.isEmpty
                    ? const Center(
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.local_activity,
                                size: 60, color: kSlate200),
                            SizedBox(height: 12),
                            Text('No applicable vouchers available',
                                style:
                                    TextStyle(color: kSlate400, fontSize: 15)),
                          ],
                        ),
                      )
                    : ListView.builder(
                        padding: const EdgeInsets.all(16),
                        itemCount: _vouchers.length,
                        itemBuilder: (context, i) {
                          final v = _vouchers[i];
                          final isSelected =
                              widget.booking.voucherCode == v['code'];
                          return _DiscountCouponCard(
                            voucher: v,
                            isSelected: isSelected,
                            onTap: () => _applyCode(v['code'] as String),
                          );
                        },
                      ),
          ),
          // Remove voucher button at bottom
          if (currentCode != null)
            Padding(
              padding: const EdgeInsets.all(16),
              child: SizedBox(
                width: double.infinity,
                height: 48,
                child: OutlinedButton(
                  onPressed: () {
                    widget.booking.voucherCode = null;
                    widget.booking.voucherData = null;
                    Navigator.pop(context, null);
                  },
                  style: OutlinedButton.styleFrom(
                    side: const BorderSide(color: Colors.red),
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(10)),
                  ),
                  child: const Text('Remove Voucher',
                      style: TextStyle(
                          color: Colors.red, fontWeight: FontWeight.bold)),
                ),
              ),
            ),
        ],
      ),
    );
  }
}

// ==========================================
// ABOUT SCREEN
// ==========================================
class AboutScreen extends StatelessWidget {
  const AboutScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('About')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Hero
            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(24),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                    colors: [kGreen, Color(0xFF14400e)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight),
                borderRadius: BorderRadius.circular(20),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('About Us',
                      style: TextStyle(
                          color: Colors.white70,
                          fontSize: 12,
                          fontWeight: FontWeight.bold,
                          letterSpacing: 1.2)),
                  SizedBox(height: 6),
                  Text('Our Journey & Mission',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 22,
                          fontWeight: FontWeight.w900,
                          height: 1.2)),
                  SizedBox(height: 8),
                  Text(
                      'Discover the story behind Amiga Gracia Travel Services and our dedication to making every journey hassle-free.',
                      style: TextStyle(color: Colors.white70, fontSize: 13)),
                ],
              ),
            ),
            const SizedBox(height: 20),
            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16)),
              child: const Padding(
                padding: EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('Backed by Experience, Driven by Excellence',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                            color: kSlate800)),
                    SizedBox(height: 12),
                    Text(
                      'Amiga Gracia was established in July 2017. Its humble beginning was born from the dedication of its founder, Mrs. MGA-Ting, whose extensive experience with 2GO laid the foundation for the company\'s first-class standard of service.',
                      style: TextStyle(
                          color: kSlate600, fontSize: 13, height: 1.6),
                    ),
                    SizedBox(height: 12),
                    Text(
                      'What started in the municipality of Roxas, Oriental Mindoro has expanded. Following the challenges of the pandemic, our main office relocated to the thriving City of Calapan, positioned to serve travelers better than ever.',
                      style: TextStyle(
                          color: kSlate600, fontSize: 13, height: 1.6),
                    ),
                    SizedBox(height: 20),
                    Text('Core Values',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: kSlate800)),
                    SizedBox(height: 12),
                    _AboutFact(
                        number: 'G',
                        title: 'Growth & Innovation',
                        desc:
                            'Continuously growing and innovating our services for travelers.'),
                    SizedBox(height: 10),
                    _AboutFact(
                        number: 'R',
                        title: 'Responsibility & Integrity',
                        desc:
                            'Operating with honesty, transparency, and accountability.'),
                    SizedBox(height: 10),
                    _AboutFact(
                        number: 'A',
                        title: 'Accountability',
                        desc:
                            'Taking ownership of every booking, transaction, and commitment.'),
                    SizedBox(height: 10),
                    _AboutFact(
                        number: 'C',
                        title: 'Customer Excellence',
                        desc:
                            'Delivering first-class service that exceeds customer expectations.'),
                    SizedBox(height: 10),
                    _AboutFact(
                        number: 'I',
                        title: 'Inclusivity & Collaboration',
                        desc:
                            'Welcoming all travelers and working together as one team.'),
                    SizedBox(height: 10),
                    _AboutFact(
                        number: 'A',
                        title: 'Assurance of Quality & Safety',
                        desc:
                            'Ensuring every journey meets the highest safety and quality standards.'),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 16),
            Container(
              padding: const EdgeInsets.all(20),
              decoration: BoxDecoration(
                gradient: const LinearGradient(
                    colors: [kGreen, Color(0xFF14400e)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight),
                borderRadius: BorderRadius.circular(16),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text('Our Main 5 Operators',
                      style: TextStyle(
                          color: Colors.white,
                          fontWeight: FontWeight.bold,
                          fontSize: 15)),
                  const SizedBox(height: 14),
                  // Row 1: Ferry — 2GO, Starlite
                  Row(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      _OperatorLogoCard(
                          name: '2GO',
                          logoUrl:
                              '${UserSession.getBaseUrl()}/images/2GO-Logo.png'),
                      const SizedBox(width: 8),
                      _OperatorLogoCard(
                          name: 'Starlite',
                          logoUrl:
                              '${UserSession.getBaseUrl()}/images/Starlite_Logo.png'),
                    ],
                  ),
                  const SizedBox(height: 8),
                  // Row 2: Airlines — Cebu Pacific, Philippine Airlines, AirAsia
                  Row(
                    children: [
                      _OperatorLogoCard(
                          name: 'Cebu Pacific',
                          logoUrl:
                              '${UserSession.getBaseUrl()}/images/CebuPecific-Logo.png'),
                      const SizedBox(width: 8),
                      _OperatorLogoCard(
                          name: 'Philippine Airlines',
                          logoUrl:
                              '${UserSession.getBaseUrl()}/images/Pal-Logo.jfif'),
                      const SizedBox(width: 8),
                      _OperatorLogoCard(
                          name: 'AirAsia',
                          logoUrl:
                              '${UserSession.getBaseUrl()}/images/AirAsia-Logo.png'),
                    ],
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            const Text('Kay Amiga, Hassle Free Ka!',
                style: TextStyle(
                    fontWeight: FontWeight.bold,
                    fontSize: 16,
                    color: kSlate800),
                textAlign: TextAlign.center),
          ],
        ),
      ),
    );
  }
}

class _AboutFact extends StatelessWidget {
  final String number;
  final String title;
  final String desc;
  const _AboutFact(
      {required this.number, required this.title, required this.desc});

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        Container(
          width: 38,
          height: 38,
          decoration: BoxDecoration(
              color: kGreen.withOpacity(0.1),
              borderRadius: BorderRadius.circular(10)),
          child: Center(
              child: Text(number,
                  style: const TextStyle(
                      color: kGreen,
                      fontWeight: FontWeight.bold,
                      fontSize: 12))),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title,
                  style: const TextStyle(
                      fontWeight: FontWeight.bold,
                      fontSize: 13,
                      color: kSlate800)),
              Text(desc,
                  style: const TextStyle(color: kSlate500, fontSize: 12)),
            ],
          ),
        ),
      ],
    );
  }
}

class _OperatorLogoCard extends StatelessWidget {
  final String name;
  final String logoUrl;
  const _OperatorLogoCard({required this.name, required this.logoUrl});

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Container(
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Image.network(
              logoUrl,
              height: 36,
              fit: BoxFit.contain,
              errorBuilder: (_, __, ___) => Text(
                name,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: kGreen,
                  fontWeight: FontWeight.w900,
                  fontSize: 10,
                ),
              ),
            ),
            const SizedBox(height: 4),
            Text(
              name,
              textAlign: TextAlign.center,
              style: const TextStyle(
                color: kSlate700,
                fontWeight: FontWeight.w700,
                fontSize: 9,
              ),
            ),
          ],
        ),
      ),
    );
  }
}

// ==========================================
// CONTACT SCREEN
// ==========================================
class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _subjectCtrl = TextEditingController();
  final _msgCtrl = TextEditingController();
  bool _submitted = false;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _subjectCtrl.dispose();
    _msgCtrl.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Contact Us')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(20),
        child: Column(
          children: [
            // Info cards
            const _ContactInfoCard(
                icon: Icons.phone,
                color: kPink,
                title: 'Phone Numbers',
                lines: ['Mobile: 0930-928-4278', 'Landline: (043) 738-2989']),
            const SizedBox(height: 12),
            const _ContactInfoCard(
                icon: Icons.email,
                color: kGreen,
                title: 'Email Addresses',
                lines: [
                  'agt.salesmarketing1103@gmail.com',
                  'amigagracia.travelservices@gmail.com'
                ]),
            const SizedBox(height: 12),
            const _ContactInfoCard(
                icon: Icons.location_on,
                color: Color(0xFF1565C0),
                title: 'Office Location',
                lines: [
                  'Roxas Drive, Libis, Calapan City,',
                  'Oriental Mindoro, 5200'
                ]),
            const SizedBox(height: 12),
            const _ContactInfoCard(
                icon: Icons.facebook,
                color: Color(0xFF7B1FA2),
                title: 'Social Media',
                lines: ['Facebook: Amiga Gracia']),
            const SizedBox(height: 20),

            // Form
            if (_submitted)
              Container(
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(
                    color: kGreen.withOpacity(0.08),
                    borderRadius: BorderRadius.circular(16),
                    border: Border.all(color: kGreen.withOpacity(0.2))),
                child: Column(
                  children: [
                    const Icon(Icons.check_circle, color: kGreen, size: 48),
                    const SizedBox(height: 12),
                    const Text('Inquiry Sent!',
                        style: TextStyle(
                            color: kGreen,
                            fontWeight: FontWeight.bold,
                            fontSize: 16)),
                    const Text('Our team will get back to you shortly.',
                        style: TextStyle(color: kSlate500, fontSize: 13),
                        textAlign: TextAlign.center),
                    const SizedBox(height: 12),
                    TextButton(
                        onPressed: () => setState(() => _submitted = false),
                        child: const Text('Send another',
                            style: TextStyle(color: kPink))),
                  ],
                ),
              )
            else
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Send an Inquiry',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                              color: kSlate800)),
                      const SizedBox(height: 14),
                      TextField(
                          controller: _nameCtrl,
                          decoration: InputDecoration(
                              labelText: 'Your Name *',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)))),
                      const SizedBox(height: 10),
                      TextField(
                          controller: _emailCtrl,
                          keyboardType: TextInputType.emailAddress,
                          decoration: InputDecoration(
                              labelText: 'Email Address *',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)))),
                      const SizedBox(height: 10),
                      TextField(
                          controller: _subjectCtrl,
                          decoration: InputDecoration(
                              labelText: 'Subject',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)))),
                      const SizedBox(height: 10),
                      TextField(
                          controller: _msgCtrl,
                          maxLines: 4,
                          decoration: InputDecoration(
                              labelText: 'Message *',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)))),
                      const SizedBox(height: 14),
                      SizedBox(
                        width: double.infinity,
                        height: 48,
                        child: ElevatedButton(
                          onPressed: () {
                            if (_nameCtrl.text.isEmpty ||
                                _emailCtrl.text.isEmpty ||
                                _msgCtrl.text.isEmpty) {
                              showTopSnack(
                                  context,
                                  const SnackBar(
                                      content:
                                          Text('Please fill required fields'),
                                      backgroundColor: Colors.red));
                              return;
                            }
                            setState(() => _submitted = true);
                          },
                          style: ElevatedButton.styleFrom(
                              backgroundColor: kGreen,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(10))),
                          child: const Text('Send Message',
                              style: TextStyle(fontWeight: FontWeight.bold)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
          ],
        ),
      ),
    );
  }
}

class _ContactInfoCard extends StatelessWidget {
  final IconData icon;
  final Color color;
  final String title;
  final List<String> lines;
  const _ContactInfoCard(
      {required this.icon,
      required this.color,
      required this.title,
      required this.lines});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(14),
          border: Border.all(color: kSlate200)),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
                color: color.withOpacity(0.1),
                borderRadius: BorderRadius.circular(12)),
            child: Icon(icon, color: color, size: 22),
          ),
          const SizedBox(width: 14),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title,
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: kSlate800)),
                const SizedBox(height: 4),
                ...lines.map((l) => Text(l,
                    style: const TextStyle(color: kSlate600, fontSize: 12))),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

// ==========================================
// SERVICES SCREEN
// ==========================================
class ServicesScreen extends StatelessWidget {
  const ServicesScreen({super.key});

  static const List<Map<String, dynamic>> _services = [
    {
      'title': '2GO Travel Booking',
      'desc':
          'Book premier overnight ship accommodation and fast cargo transits with 2GO Travel. Ideal for family retreats, business logistics, and leisure trips.',
      'icon': Icons.directions_boat,
      'color': Color(0xFFEE018D),
      'tag': 'Available Online',
    },
    {
      'title': 'Starlite & Supercat',
      'desc':
          'Affordable regional transits between Batangas, Calapan, and Roxas. We manage standard ferry bookings, roll-on/roll-off (RoRo) cargo slots, and fastcraft ticketing.',
      'icon': Icons.sailing,
      'color': Color(0xFF216417),
      'tag': 'Available Online',
    },
    {
      'title': '2GO Onboarding Training',
      'desc':
          'Comprehensive onboarding and orientation programs for individuals joining 2GO operations.',
      'icon': Icons.directions_boat,
      'color': Color(0xFFD81B60),
      'tag': 'For New Hires & Trainees',
    },
    {
      'title': 'Educ Tour',
      'desc':
          'Educational tours for students and academic groups, featuring visits to travel facilities and cultural sites.',
      'icon': Icons.school,
      'color': Color(0xFF00796B),
      'tag': 'For Schools & Groups',
    },
    {
      'title': 'Stay and Learn',
      'desc':
          'Combined accommodation and learning packages, perfect for workshops, seminars, and training sessions.',
      'icon': Icons.hotel,
      'color': Color(0xFF1E88E5),
      'tag': 'Workshops & Seminars',
    },
    {
      'title': 'Marine Related Trainings',
      'desc':
          'Specialized training programs for maritime professionals, including safety, navigation, and vessel operations.',
      'icon': Icons.anchor,
      'color': Color(0xFF8E24AA),
      'tag': 'For Mariners & Seafarers',
    },
    {
      'title': 'Transport',
      'desc':
          'Reliable transport solutions including ferry, airline, and land transfers for individuals, groups, and corporate needs.',
      'icon': Icons.emoji_transportation,
      'color': Color(0xFFF4511E),
      'tag': 'Multi-Modal Transport',
    },
    {
      'title': 'Visa & Passport Assistance',
      'desc':
          'Complete assistance with visa applications and passport processing, helping you prepare required documents.',
      'icon': Icons.article,
      'color': Color(0xFF00897B),
      'tag': 'Document Processing',
    },
  ];

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Our Services')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // CTA Banner
          Container(
            margin: const EdgeInsets.only(bottom: 20),
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                  colors: [kGreen, Color(0xFF14400e)],
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight),
              borderRadius: BorderRadius.circular(18),
            ),
            child: const Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text('Book Ferry Tickets Directly Online',
                    style: TextStyle(
                        color: Colors.white,
                        fontWeight: FontWeight.w900,
                        fontSize: 17)),
                SizedBox(height: 6),
                Text(
                    'Quickly check available schedules, fares, and cabins. Complete your passenger credentials instantly.',
                    style: TextStyle(color: Colors.white70, fontSize: 12)),
              ],
            ),
          ),
          ..._services.map((s) => Card(
                color: Colors.white,
                margin: const EdgeInsets.only(bottom: 14),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16)),
                child: Padding(
                  padding: const EdgeInsets.all(18),
                  child: Row(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                            color: (s['color'] as Color).withOpacity(0.1),
                            borderRadius: BorderRadius.circular(14)),
                        child: Icon(s['icon'] as IconData,
                            color: s['color'] as Color, size: 26),
                      ),
                      const SizedBox(width: 14),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(s['title'] as String,
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                    color: kSlate800)),
                            const SizedBox(height: 6),
                            Text(s['desc'] as String,
                                style: const TextStyle(
                                    color: kSlate500,
                                    fontSize: 12,
                                    height: 1.5)),
                            const SizedBox(height: 8),
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 8, vertical: 3),
                              decoration: BoxDecoration(
                                  color: kSlate100,
                                  borderRadius: BorderRadius.circular(10)),
                              child: Text(s['tag'] as String,
                                  style: const TextStyle(
                                      color: kSlate500,
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold)),
                            ),
                          ],
                        ),
                      ),
                    ],
                  ),
                ),
              )),
        ],
      ),
    );
  }
}

// ==========================================
// TOUR PACKAGES SCREEN
// ==========================================
class TourPackagesScreen extends StatefulWidget {
  const TourPackagesScreen({super.key});

  @override
  State<TourPackagesScreen> createState() => _TourPackagesScreenState();
}

class _TourPackagesScreenState extends State<TourPackagesScreen>
    with SingleTickerProviderStateMixin {
  late TabController _tabController;

  List<Map<String, dynamic>> _domestic = [];
  List<Map<String, dynamic>> _international = [];
  bool _loadingTours = true;

  Future<void> _fetchTours() async {
    try {
      final res =
          await http.get(Uri.parse('${UserSession.getBaseUrl()}/api/tours'));
      if (res.statusCode == 200) {
        final List<dynamic> tours = json.decode(res.body) as List<dynamic>;
        List<Map<String, dynamic>> normalized = tours.map((e) {
          final m = Map<String, dynamic>.from(e as Map);
          return _normalizeTour(m);
        }).toList();

        final dom = normalized
            .where((t) => (t['country'] ?? '')
                .toString()
                .toLowerCase()
                .contains('philipp'))
            .toList();
        final intl = normalized
            .where((t) => !((t['country'] ?? '')
                .toString()
                .toLowerCase()
                .contains('philipp')))
            .toList();
        setState(() {
          _domestic = dom.cast<Map<String, dynamic>>();
          _international = intl.cast<Map<String, dynamic>>();
          _loadingTours = false;
        });
      } else {
        setState(() => _loadingTours = false);
      }
    } catch (e) {
      setState(() => _loadingTours = false);
    }
  }

  Map<String, dynamic> _normalizeTour(Map raw) {
    return {
      'name': raw['tour_name'] ?? raw['tour'] ?? raw['name'] ?? '',
      'detail': raw['duration'] ?? raw['detail'] ?? '',
      'desc': raw['highlights'] ?? raw['desc'] ?? raw['inclusions'] ?? '',
      'price': raw['price_per_pax'] ?? raw['price'] ?? '',
      'tag': raw['promo'] ?? raw['tag'] ?? '',
      'country': raw['country'] ?? '',
      'destinations': raw['destinations'] ?? '',
      'available_dates': raw['available_dates'] ?? raw['departure'] ?? '',
      'hotel': raw['hotel'] ?? '',
      'inclusions': raw['inclusions'] ?? '',
      'exclusions': raw['exclusions'] ?? '',
      'remarks': raw['remarks'] ?? '',
      'image': raw['image'] ?? '',
      'raw': raw,
      'gradient': [const Color(0xFF1565C0), const Color(0xFF42A5F5)],
    };
  }

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: 2, vsync: this);
    _fetchTours();
  }

  @override
  void dispose() {
    _tabController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Tour Packages')),
      body: Column(
        children: [
          Container(
            color: Colors.white,
            child: TabBar(
              controller: _tabController,
              indicatorColor: kPink,
              labelColor: kPink,
              unselectedLabelColor: kSlate600,
              indicatorWeight: 3,
              tabs: const [Tab(text: 'Domestic'), Tab(text: 'International')],
            ),
          ),
          Expanded(
            child: _loadingTours
                ? const Center(child: CircularProgressIndicator())
                : TabBarView(
                    controller: _tabController,
                    children: [
                      _domestic.isEmpty
                          ? _buildEmptyState('No Domestic Packages')
                          : _PackageList(packages: _domestic),
                      _international.isEmpty
                          ? _buildEmptyState('No International Packages')
                          : _PackageList(packages: _international),
                    ],
                  ),
          ),
        ],
      ),
    );
  }

  Widget _buildEmptyState(String message) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.landscape, size: 80, color: kSlate200),
          const SizedBox(height: 16),
          Text(message,
              style: const TextStyle(
                  fontSize: 18, fontWeight: FontWeight.bold, color: kSlate400)),
          const SizedBox(height: 8),
          const Text('Packages will be available soon.',
              style: TextStyle(color: kSlate400)),
        ],
      ),
    );
  }
}

class _PackageList extends StatelessWidget {
  final List<Map<String, dynamic>> packages;
  const _PackageList({required this.packages});

  void _showPackageDetailsModal(BuildContext context, Map<String, dynamic> p) {
    final gradient = p['gradient'] as List<Color>;
    showDialog(
      context: context,
      builder: (BuildContext context) {
        return Dialog(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
          insetPadding: const EdgeInsets.all(16),
          child: Stack(
            children: [
              SingleChildScrollView(
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Container(
                      height: 180,
                      decoration: BoxDecoration(
                        gradient: p['image'].toString().isEmpty
                            ? LinearGradient(
                                colors: gradient,
                                begin: Alignment.topLeft,
                                end: Alignment.bottomRight)
                            : null,
                        image: p['image'].toString().isNotEmpty
                            ? DecorationImage(
                                image: NetworkImage(
                                    '${UserSession.getBaseUrl()}/storage/${p['image']}'),
                                fit: BoxFit.cover,
                                colorFilter: ColorFilter.mode(
                                    Colors.black.withOpacity(0.3),
                                    BlendMode.darken),
                              )
                            : null,
                        borderRadius: const BorderRadius.vertical(
                            top: Radius.circular(18)),
                      ),
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Container(
                              padding: const EdgeInsets.symmetric(
                                  horizontal: 10, vertical: 4),
                              decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(20)),
                              child: Text(p['tag'] as String,
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 10,
                                      fontWeight: FontWeight.bold)),
                            ),
                            const Spacer(),
                            Text(p['name'] as String,
                                style: const TextStyle(
                                    color: Colors.white,
                                    fontWeight: FontWeight.w900,
                                    fontSize: 22)),
                            Text(p['detail'] as String,
                                style: const TextStyle(
                                    color: Colors.white70, fontSize: 13)),
                          ],
                        ),
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          const Text('Description / Details',
                              style: TextStyle(
                                  color: kSlate700,
                                  fontWeight: FontWeight.bold,
                                  fontSize: 16)),
                          const SizedBox(height: 8),
                          if (p['desc'].toString().isNotEmpty) ...[
                            Text(p['desc'] as String,
                                style: const TextStyle(
                                    color: kSlate600,
                                    fontSize: 14,
                                    height: 1.5)),
                            const SizedBox(height: 16),
                          ],
                          if (p['inclusions'].toString().isNotEmpty) ...[
                            const Text('Inclusions:',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: kSlate700)),
                            const SizedBox(height: 4),
                            Text(p['inclusions'] as String,
                                style: const TextStyle(
                                    color: kSlate600,
                                    fontSize: 13,
                                    height: 1.5)),
                            const SizedBox(height: 16),
                          ],
                          if (p['exclusions'].toString().isNotEmpty) ...[
                            const Text('Exclusions:',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: kSlate700)),
                            const SizedBox(height: 4),
                            Text(p['exclusions'] as String,
                                style: const TextStyle(
                                    color: kSlate600,
                                    fontSize: 13,
                                    height: 1.5)),
                            const SizedBox(height: 16),
                          ],
                          if (p['remarks'].toString().isNotEmpty) ...[
                            const Text('Remarks:',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: kSlate700)),
                            const SizedBox(height: 4),
                            Text(p['remarks'] as String,
                                style: const TextStyle(
                                    color: kSlate600,
                                    fontSize: 13,
                                    height: 1.5)),
                            const SizedBox(height: 16),
                          ],
                          if (p['raw']['day1'] != null &&
                              p['raw']['day1'].toString().isNotEmpty) ...[
                            const Text('Itinerary:',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: kSlate700)),
                            const SizedBox(height: 8),
                            for (int i = 1; i <= 6; i++)
                              if (p['raw']['day$i'] != null &&
                                  p['raw']['day$i'].toString().isNotEmpty)
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 8),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text('Day $i',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              color: kPink,
                                              fontSize: 13)),
                                      Text(p['raw']['day$i'].toString(),
                                          style: const TextStyle(
                                              color: kSlate600,
                                              fontSize: 13,
                                              height: 1.5)),
                                    ],
                                  ),
                                ),
                            const SizedBox(height: 16),
                          ],
                          const SizedBox(height: 8),
                          const Text('Starting from',
                              style: TextStyle(color: kSlate400, fontSize: 12)),
                          Text(p['price'] as String,
                              style: const TextStyle(
                                  color: kGreen,
                                  fontWeight: FontWeight.w900,
                                  fontSize: 20)),
                          const SizedBox(height: 60), // Space for button
                        ],
                      ),
                    ),
                  ],
                ),
              ),
              Positioned(
                top: 8,
                right: 8,
                child: IconButton(
                  icon: const Icon(Icons.close, color: Colors.white),
                  onPressed: () => Navigator.of(context).pop(),
                  style: IconButton.styleFrom(
                    backgroundColor: Colors.black.withOpacity(0.3),
                  ),
                ),
              ),
              Positioned(
                bottom: 16,
                right: 16,
                child: ElevatedButton(
                  onPressed: () {
                    Navigator.of(context).pop();
                    Navigator.push(
                        context,
                        MaterialPageRoute(
                            builder: (_) => RequestBookingScreen(package: p)));
                  },
                  style: ElevatedButton.styleFrom(
                      backgroundColor: kPink,
                      foregroundColor: Colors.white,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20)),
                      padding: const EdgeInsets.symmetric(
                          horizontal: 24, vertical: 12)),
                  child: const Text('Book Now',
                      style:
                          TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                ),
              ),
            ],
          ),
        );
      },
    );
  }

  @override
  Widget build(BuildContext context) {
    return ListView.builder(
      padding: const EdgeInsets.all(16),
      itemCount: packages.length,
      itemBuilder: (context, i) {
        final p = packages[i];
        final gradient = p['gradient'] as List<Color>;
        return Card(
          margin: const EdgeInsets.only(bottom: 12),
          shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(12),
              side: const BorderSide(color: kSlate200)),
          elevation: 0,
          clipBehavior: Clip.antiAlias,
          child: Container(
            decoration: BoxDecoration(
              gradient: LinearGradient(
                  colors: gradient,
                  begin: Alignment.topLeft,
                  end: Alignment.bottomRight),
            ),
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(p['name'] as String,
                            style: const TextStyle(
                                color: Colors.white,
                                fontWeight: FontWeight.bold,
                                fontSize: 15)),
                        const SizedBox(height: 4),
                        Text(p['detail'] as String,
                            style: const TextStyle(
                                color: Colors.white70, fontSize: 12)),
                      ],
                    ),
                  ),
                  ElevatedButton(
                    onPressed: () => _showPackageDetailsModal(context, p),
                    style: ElevatedButton.styleFrom(
                      backgroundColor: Colors.white,
                      foregroundColor: kPink,
                      elevation: 0,
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(20)),
                    ),
                    child: const Text('View',
                        style: TextStyle(fontWeight: FontWeight.bold)),
                  ),
                ],
              ),
            ),
          ),
        );
      },
    );
  }
}

// ==========================================
// REQUEST BOOKING SCREEN (Slide Form)
// ==========================================
class RequestBookingScreen extends StatefulWidget {
  final Map<String, dynamic>? package;
  final Map<String, dynamic>? initialData;
  const RequestBookingScreen({super.key, this.package, this.initialData});

  @override
  State<RequestBookingScreen> createState() => _RequestBookingScreenState();
}

class _RequestBookingScreenState extends State<RequestBookingScreen> {
  final PageController _pageCtrl = PageController();
  int _page = 0;

  // Form fields
  final _nameCtrl = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _phoneCtrl = TextEditingController();
  String _serviceType = 'Ferry Ticket';
  final String _tripType = 'One Way';
  final _fromCtrl = TextEditingController();
  final _toCtrl = TextEditingController();
  final _dateCtrl = TextEditingController();
  final _passengersCtrl = TextEditingController(text: '1');
  final _notesCtrl = TextEditingController();
  bool _submitted = false;
  bool _isSubmitting = false;

  static const _services = [
    'Ferry Ticket',
    'Airline Ticket',
    'Tour Package',
    'Custom Group Package',
    'Apprenticeship / Educational Tour'
  ];
  static const _tripTypes = ['One Way', 'Round Trip'];

  @override
  void initState() {
    super.initState();
    // Always pre-fill from UserSession
    _nameCtrl.text = UserSession.username;
    _emailCtrl.text = UserSession.email;
    _phoneCtrl.text = UserSession.phone;

    final pkg = widget.package;
    if (pkg != null) {
      _serviceType = 'Tour Package';
      _fromCtrl.text = pkg['destinations'] ?? '';
      _toCtrl.text = pkg['name'] ?? '';
      _dateCtrl.text = pkg['available_dates'] ?? '';
      _passengersCtrl.text = '1';
      _notesCtrl.text = pkg['inclusions'] ?? '';
    } else if (widget.initialData != null) {
      final init = widget.initialData!;
      _serviceType =
          init['mode'] == 'airline' ? 'Airline Ticket' : 'Ferry Ticket';
      _fromCtrl.text = init['origin'] ?? '';
      _toCtrl.text = init['destination'] ?? '';
      _dateCtrl.text = init['departure_date'] ?? '';
      if (init['operator'] != null) {
        _notesCtrl.text = 'Preferred Operator: ${init['operator']}';
      }
    }
  }

  Future<void> _next() async {
    if (_page < 1) {
      _pageCtrl.nextPage(
          duration: const Duration(milliseconds: 300), curve: Curves.easeInOut);
      setState(() => _page++);
    } else {
      if (_nameCtrl.text.isEmpty || _emailCtrl.text.isEmpty || _notesCtrl.text.isEmpty) {
        showTopSnack(context, const SnackBar(content: Text('Please fill out Name, Email, and Message.'), backgroundColor: Colors.red));
        return;
      }
      setState(() => _isSubmitting = true);
      try {
        final res = await http.post(
          Uri.parse('${UserSession.getBaseUrl()}/api/inquiries'),
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'Authorization': 'Bearer ${UserSession.token}',
          },
          body: jsonEncode({
            'name': _nameCtrl.text,
            'email': _emailCtrl.text,
            'subject': 'Booking Inquiry: $_serviceType',
            'message': _notesCtrl.text,
          }),
        );
        if (res.statusCode == 201 || res.statusCode == 200) {
          setState(() {
            _isSubmitting = false;
            _submitted = true;
          });
        } else {
          throw Exception('Failed to submit');
        }
      } catch (e) {
        setState(() => _isSubmitting = false);
        showTopSnack(context, const SnackBar(content: Text('Failed to submit request. Please try again.'), backgroundColor: Colors.red));
      }
    }
  }

  void _prev() {
    if (_page > 0) {
      _pageCtrl.previousPage(
          duration: const Duration(milliseconds: 300), curve: Curves.easeInOut);
      setState(() => _page--);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Request Travel Booking')),
      body: _submitted
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    const Icon(Icons.check_circle, color: kGreen, size: 80),
                    const SizedBox(height: 20),
                    const Text('Request Submitted!',
                        style: TextStyle(
                            fontSize: 22,
                            fontWeight: FontWeight.bold,
                            color: kGreen)),
                    const SizedBox(height: 12),
                    const Text(
                        'Our travel consultants will contact you within 24-48 hours to confirm your booking.',
                        textAlign: TextAlign.center,
                        style: TextStyle(
                            color: kSlate500, fontSize: 14, height: 1.6)),
                    const SizedBox(height: 32),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context),
                      style: ElevatedButton.styleFrom(
                          backgroundColor: kGreen,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12))),
                      child: const Text('Back to Home',
                          style: TextStyle(fontWeight: FontWeight.bold)),
                    ),
                  ],
                ),
              ),
            )
          : Column(
              children: [
                // Step indicator
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: List.generate(
                        2,
                        (i) => Expanded(
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Container(
                                      height: 4,
                                      decoration: BoxDecoration(
                                        color: i <= _page ? kGreen : kSlate200,
                                        borderRadius: BorderRadius.circular(2),
                                      ),
                                    ),
                                  ),
                                  if (i < 1) const SizedBox(width: 4),
                                ],
                              ),
                            )),
                  ),
                ),
                Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: Text(
                    ['Contact Info', 'Message & Submit'][_page],
                    style: const TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 16,
                        color: kSlate800),
                  ),
                ),
                const SizedBox(height: 12),
                Expanded(
                  child: PageView(
                    controller: _pageCtrl,
                    physics: const NeverScrollableScrollPhysics(),
                    children: [
                      // Page 1: Contact
                      _FormPage(children: [
                        _Field(
                            ctrl: _nameCtrl,
                            label: 'Full Name *',
                            icon: Icons.person),
                        _Field(
                            ctrl: _emailCtrl,
                            label: 'Email Address *',
                            icon: Icons.email,
                            keyboard: TextInputType.emailAddress),
                        _Field(
                            ctrl: _phoneCtrl,
                            label: 'Phone Number',
                            icon: Icons.phone,
                            keyboard: TextInputType.phone),
                      ]),
                      // Page 2: Notes
                      _FormPage(children: [
                        Padding(
                          padding: const EdgeInsets.only(bottom: 14),
                          child: TextField(
                            controller: _notesCtrl,
                            maxLines: 7,
                            decoration: InputDecoration(
                              labelText: 'Travel Details or Message *',
                              hintText: 'e.g. Origin, Destination, Travel Date, Passengers, Service Type...',
                              prefixIcon: const Icon(Icons.note, color: kGreen),
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)),
                            ),
                          ),
                        ),
                      ]),
                    ],
                  ),
                ),
                // Nav buttons
                Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      if (_page > 0)
                        Expanded(
                          child: OutlinedButton(
                            onPressed: _prev,
                            style: OutlinedButton.styleFrom(
                                foregroundColor: kGreen,
                                side: const BorderSide(color: kGreen),
                                shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(12)),
                                padding:
                                    const EdgeInsets.symmetric(vertical: 14)),
                            child: const Text('Back',
                                style: TextStyle(fontWeight: FontWeight.bold)),
                          ),
                        ),
                      if (_page > 0) const SizedBox(width: 12),
                      Expanded(
                        flex: 2,
                        child: ElevatedButton(
                          onPressed: _isSubmitting ? null : _next,
                          style: ElevatedButton.styleFrom(
                              backgroundColor: kPink,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                  borderRadius: BorderRadius.circular(12)),
                              padding:
                                  const EdgeInsets.symmetric(vertical: 14)),
                          child: _isSubmitting
                              ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                              : Text(_page < 1 ? 'Next' : 'Submit Request',
                                  style: const TextStyle(
                                      fontWeight: FontWeight.bold, fontSize: 15)),
                        ),
                      ),
                    ],
                  ),
                ),
              ],
            ),
    );
  }
}

class _FormPage extends StatelessWidget {
  final List<Widget> children;
  const _FormPage({required this.children});

  @override
  Widget build(BuildContext context) {
    return SingleChildScrollView(
      padding: const EdgeInsets.symmetric(horizontal: 16),
      child: Column(children: children),
    );
  }
}

class _Field extends StatelessWidget {
  final TextEditingController ctrl;
  final String label;
  final IconData icon;
  final TextInputType keyboard;

  const _Field(
      {required this.ctrl,
      required this.label,
      required this.icon,
      this.keyboard = TextInputType.text});

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 14),
      child: TextField(
        controller: ctrl,
        keyboardType: keyboard,
        decoration: InputDecoration(
          labelText: label,
          prefixIcon: Icon(icon, color: kGreen),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
        ),
      ),
    );
  }
}

// ==========================================
// NOTIFICATIONS SCREEN
// ==========================================
class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});
  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _isLoading = true;
  List<dynamic> _notifications = [];
  String _error = '';
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    _fetchNotifications();
    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'notifications_updated') {
        _fetchNotifications();
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    super.dispose();
  }

  Future<void> _fetchNotifications() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/notifications'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _notifications = data['notifications'] ?? [];
          UserSession.unreadNotificationsCount = data['unread_count'] ?? 0;
        });
      } else {
        setState(
            () => _error = data['message'] ?? 'Failed to load notifications.');
      }
    } catch (e) {
      setState(() => _error = 'Network error: $e');
    } finally {
      if (mounted) setState(() => _isLoading = false);
    }
  }

  Future<void> _markAsRead(dynamic id) async {
    try {
      await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/notifications/$id/read'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      _fetchNotifications();
    } catch (_) {}
  }

  Future<void> _markAllAsRead() async {
    try {
      await http.post(
        Uri.parse(
            '${UserSession.getBaseUrl()}/api/notifications/mark-all-read'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      _fetchNotifications();
    } catch (_) {}
  }

  Future<void> _deleteNotification(dynamic id) async {
    try {
      await http.delete(
        Uri.parse('${UserSession.getBaseUrl()}/api/notifications/$id'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      _fetchNotifications();
    } catch (_) {}
  }

  Future<void> _deleteAllNotifications() async {
    try {
      await http.delete(
        Uri.parse('${UserSession.getBaseUrl()}/api/notifications'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}'
        },
      );
      _fetchNotifications();
    } catch (_) {}
  }

  String _formatDate(String isoString) {
    try {
      final DateTime dt = DateTime.parse(isoString).toLocal();
      return DateFormat('MMM dd, yyyy hh:mm a').format(dt);
    } catch (_) {
      return isoString;
    }
  }

  void _handleNotificationTap(Map<String, dynamic> notif) async {
    final String type = notif['type'] ?? 'general';
    final String targetId = notif['target_id']?.toString() ?? '';

    // Mark as read immediately when tapped
    if (notif['is_read'] != true && notif['is_read'] != 1) {
      _markAsRead(notif['id']);
    }

    if (type == 'booking' || type == 'payment') {
      if (targetId.isNotEmpty) {
        _fetchBookingAndNavigate(targetId);
      }
    } else if (type == 'promo') {
      // Navigate to Schedules tab (Tab 1)
      Navigator.popUntil(context, (route) => route.isFirst);
      final mainState = context.findAncestorStateOfType<_MainScreenState>();
      mainState?.switchTab(1);
    } else if (type == 'voucher') {
      // Navigate to Vouchers tab (Tab 3)
      Navigator.popUntil(context, (route) => route.isFirst);
      final mainState = context.findAncestorStateOfType<_MainScreenState>();
      mainState?.switchTab(3);
    } else if (type == 'announcement') {
      showDialog(
        context: context,
        builder: (ctx) => AlertDialog(
          title: Text(notif['title'] ?? 'Announcement'),
          content: Text(notif['body'] ?? ''),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Close', style: TextStyle(color: kGreen)),
            )
          ],
        ),
      );
    }
  }

  Future<void> _fetchBookingAndNavigate(String transactionNumber) async {
    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (context) =>
          const Center(child: CircularProgressIndicator(color: kGreen)),
    );

    try {
      final response = await http.get(
        Uri.parse(
            '${UserSession.getBaseUrl()}/api/bookings/$transactionNumber'),
        headers: {
          'Authorization': 'Bearer ${UserSession.token}',
          'Accept': 'application/json',
        },
      );

      if (!mounted) return;
      Navigator.pop(context); // hide loading

      if (response.statusCode == 200) {
        final resData = jsonDecode(response.body);
        if (resData['status'] == 'success' && resData['booking'] != null) {
          Navigator.push(
            context,
            MaterialPageRoute(
              builder: (_) => BookingDetailsScreen(booking: resData['booking']),
            ),
          );
        } else {
          showTopSnack(context,
              const SnackBar(content: Text('Booking details not found.')));
        }
      } else {
        showTopSnack(context,
            const SnackBar(content: Text('Failed to load booking details.')));
      }
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);
      showTopSnack(context, SnackBar(content: Text('Error: $e')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Notifications'),
        backgroundColor: kGreen,
        foregroundColor: Colors.white,
        actions: [
          if (_notifications.isNotEmpty)
            PopupMenuButton<String>(
              onSelected: (value) {
                if (value == 'read_all') {
                  _markAllAsRead();
                } else if (value == 'delete_all') {
                  showDialog(
                    context: context,
                    builder: (ctx) => AlertDialog(
                      title: const Text('Delete All'),
                      content: const Text(
                          'Are you sure you want to delete all notifications?'),
                      actions: [
                        TextButton(
                          onPressed: () => Navigator.pop(ctx),
                          child: const Text('Cancel'),
                        ),
                        TextButton(
                          onPressed: () {
                            Navigator.pop(ctx);
                            _deleteAllNotifications();
                          },
                          child: const Text('Delete All',
                              style: TextStyle(color: Colors.red)),
                        ),
                      ],
                    ),
                  );
                }
              },
              itemBuilder: (BuildContext context) => <PopupMenuEntry<String>>[
                const PopupMenuItem<String>(
                  value: 'read_all',
                  child: ListTile(
                    leading: Icon(Icons.done_all, color: kGreen),
                    title: Text('Mark all as read'),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
                const PopupMenuItem<String>(
                  value: 'delete_all',
                  child: ListTile(
                    leading: Icon(Icons.delete_sweep, color: Colors.red),
                    title:
                        Text('Delete all', style: TextStyle(color: Colors.red)),
                    contentPadding: EdgeInsets.zero,
                  ),
                ),
              ],
            ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: kGreen))
          : _error.isNotEmpty
              ? Center(
                  child:
                      Text(_error, style: const TextStyle(color: Colors.red)))
              : _notifications.isEmpty
                  ? const Center(
                      child: Text('You have no notifications.',
                          style: TextStyle(color: kSlate500)))
                  : RefreshIndicator(
                      onRefresh: _fetchNotifications,
                      color: kGreen,
                      child: ListView.builder(
                        itemCount: _notifications.length,
                        itemBuilder: (context, i) {
                          final notif = _notifications[i];
                          final bool isRead =
                              notif['is_read'] == 1 || notif['is_read'] == true;
                          return ListTile(
                            tileColor:
                                isRead ? null : kGreen.withOpacity(0.05),
                            leading: CircleAvatar(
                              backgroundColor: kGreen.withOpacity(0.2),
                              child: const Icon(Icons.notifications_active,
                                  color: kGreen),
                            ),
                            title: Text(notif['title'] ?? '',
                                style: TextStyle(
                                    fontWeight: isRead
                                        ? FontWeight.normal
                                        : FontWeight.bold)),
                            subtitle: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              children: [
                                const SizedBox(height: 4),
                                Text(notif['body'] ?? ''),
                                const SizedBox(height: 8),
                                if (notif['created_at'] != null)
                                  Text(
                                    _formatDate(notif['created_at']),
                                    style: const TextStyle(
                                        fontSize: 12, color: Colors.grey),
                                  ),
                              ],
                            ),
                            trailing: PopupMenuButton<String>(
                              icon: const Icon(Icons.more_vert),
                              onSelected: (value) {
                                if (value == 'read') {
                                  _markAsRead(notif['id']);
                                } else if (value == 'delete') {
                                  _deleteNotification(notif['id']);
                                }
                              },
                              itemBuilder: (BuildContext context) =>
                                  <PopupMenuEntry<String>>[
                                if (!isRead)
                                  const PopupMenuItem<String>(
                                    value: 'read',
                                    child: Text('Mark as read'),
                                  ),
                                const PopupMenuItem<String>(
                                  value: 'delete',
                                  child: Text('Delete',
                                      style: TextStyle(color: Colors.red)),
                                ),
                              ],
                            ),
                          );
                        },
                      ),
                    ),
    );
  }
}

// ==========================================
// GRACIA POINTS SCREEN
// ==========================================
class GraciaPointsScreen extends StatefulWidget {
  const GraciaPointsScreen({super.key});

  @override
  State<GraciaPointsScreen> createState() => _GraciaPointsScreenState();
}

class _GraciaPointsScreenState extends State<GraciaPointsScreen> {
  bool _isLoading = true;
  String _error = '';
  int _currentPoints = 0;
  int _unconvertedSpend = 0;
  Map<String, dynamic>? _activeRule;
  List<dynamic> _history = [];
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    _fetchPoints();
    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'points_updated' || event == 'booking_created') {
        _fetchPoints();
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    super.dispose();
  }

  Future<void> _fetchPoints() async {
    if (!UserSession.isLoggedIn || UserSession.token.isEmpty) {
      setState(() {
        _error = 'Please log in to view your Gracia Points.';
        _isLoading = false;
      });
      return;
    }

    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/gracia-points'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}',
        },
      );

      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _currentPoints = data['current_points'] ?? 0;
          _unconvertedSpend = data['unconverted_spend_centavos'] ?? 0;
          _activeRule = data['active_rule'];
          _history = data['history'] ?? [];
          _isLoading = false;

          UserSession.graciaPoints = _currentPoints;
          if (_activeRule != null) {
            UserSession.pointsAwarded = _activeRule!['points_awarded'] ?? 0;
            UserSession.spendThreshold =
                _activeRule!['spend_threshold_centavos'] ?? 0;
          }
          UserSession.save();
        });
      } else {
        setState(() {
          _error = 'Failed to load points data.';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Network error occurred.';
        _isLoading = false;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    if (!UserSession.isLoggedIn) {
      return Scaffold(
        appBar: AppBar(title: const Text('Gracia Points')),
        body: const Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(Icons.star_rounded, size: 80, color: kSlate300),
              SizedBox(height: 16),
              Text('Gracia Points',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold)),
              SizedBox(height: 8),
              Text('Sign in to view and use your Gracia Points.',
                  style: TextStyle(color: kSlate500)),
            ],
          ),
        ),
      );
    }

    if (_error == 'Please log in to view your Gracia Points.' &&
        UserSession.isLoggedIn) {
      _error = '';
      _isLoading = true;
      Future.microtask(() => _fetchPoints());
    }

    return Scaffold(
      appBar: AppBar(
        title: const Text('Gracia Points'),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: kPink))
          : _error.isNotEmpty
              ? Center(
                  child:
                      Text(_error, style: const TextStyle(color: Colors.red)))
              : RefreshIndicator(
                  onRefresh: _fetchPoints,
                  color: kPink,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      // Balance Card
                      Card(
                        color: kPink,
                        elevation: 4,
                        shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(16)),
                        child: Padding(
                          padding: const EdgeInsets.all(24),
                          child: Column(
                            children: [
                              const Text('CURRENT BALANCE',
                                  style: TextStyle(
                                      color: Colors.white70,
                                      fontWeight: FontWeight.bold,
                                      letterSpacing: 1.2)),
                              const SizedBox(height: 8),
                              Text('$_currentPoints pts',
                                  style: const TextStyle(
                                      color: Colors.white,
                                      fontSize: 40,
                                      fontWeight: FontWeight.w900)),
                              const SizedBox(height: 16),
                              if (_activeRule != null) ...[
                                Text(
                                    'Unconverted Spend: ₱${(_unconvertedSpend / 100).toStringAsFixed(2)}',
                                    style:
                                        const TextStyle(color: Colors.white)),
                                const SizedBox(height: 4),
                                Text(
                                    'Earn ${_activeRule!['points_awarded']} pts for every ₱${(_activeRule!['spend_threshold_centavos'] / 100).toStringAsFixed(0)}',
                                    style: const TextStyle(
                                        color: Colors.white70, fontSize: 12)),
                                const SizedBox(height: 12),
                                LinearProgressIndicator(
                                  value: _unconvertedSpend /
                                      _activeRule!['spend_threshold_centavos'],
                                  backgroundColor: Colors.white24,
                                  valueColor:
                                      const AlwaysStoppedAnimation<Color>(
                                          Colors.white),
                                ),
                              ] else ...[
                                const Text('No active earning rule.',
                                    style: TextStyle(color: Colors.white70)),
                              ]
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(height: 16),
                      // Share & Earn
                      if (UserSession.referralCode != null &&
                          UserSession.referralCode!.isNotEmpty)
                        Card(
                          elevation: 2,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16)),
                          child: Padding(
                            padding: const EdgeInsets.all(20.0),
                            child: Column(
                              children: [
                                const Text('SHARE & EARN',
                                    style: TextStyle(
                                        color: Colors.blueGrey,
                                        fontWeight: FontWeight.bold,
                                        letterSpacing: 1.2)),
                                const SizedBox(height: 8),
                                const Text(
                                    'Invite friends using your referral code and you both earn Gracia Coins!',
                                    textAlign: TextAlign.center,
                                    style: TextStyle(
                                        fontSize: 13, color: Colors.black87)),
                                const SizedBox(height: 16),
                                Container(
                                  padding: const EdgeInsets.symmetric(
                                      vertical: 12, horizontal: 16),
                                  decoration: BoxDecoration(
                                    color: Colors.blue.shade50,
                                    borderRadius: BorderRadius.circular(8),
                                    border:
                                        Border.all(color: Colors.blue.shade200),
                                  ),
                                  child: Row(
                                    mainAxisAlignment:
                                        MainAxisAlignment.spaceBetween,
                                    children: [
                                      Text(
                                        UserSession.referralCode!,
                                        style: const TextStyle(
                                            fontSize: 20,
                                            fontWeight: FontWeight.bold,
                                            letterSpacing: 2),
                                      ),
                                      IconButton(
                                        icon: const Icon(Icons.copy,
                                            color: Colors.blue),
                                        onPressed: () {
                                          Clipboard.setData(ClipboardData(
                                              text: UserSession.referralCode!));
                                          showTopSnack(
                                            context,
                                            const SnackBar(
                                                content: Text(
                                                    'Referral code copied to clipboard!')),
                                          );
                                        },
                                      )
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                        ),
                      const SizedBox(height: 16),
                      // Learn More
                      GestureDetector(
                        onTap: () {
                          showDialog(
                            context: context,
                            builder: (ctx) => AlertDialog(
                              title: const Row(
                                children: [
                                  Icon(Icons.info_outline, color: kPink),
                                  SizedBox(width: 8),
                                  Text('Rules & Guidelines',
                                      style: TextStyle(fontSize: 18)),
                                ],
                              ),
                              content: const Column(
                                mainAxisSize: MainAxisSize.min,
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text('Gracia coins Guidelines'),
                                  SizedBox(height: 8),
                                  Text(
                                      '• Book your ferry or flight and earn points along the way.'),
                                  SizedBox(height: 8),
                                  Text(
                                      '• For every ₱1,000 spent, you will earn 5 Gracia coins.'),
                                  SizedBox(height: 8),
                                  Text(
                                      '• Web Admin Fee and Transaction Fee are excluded from the eligible spend. If a discount voucher is applied, points are calculated based on the discounted amount.'),
                                  SizedBox(height: 8),
                                  Text(
                                      '• Points will be automatically credited once your booking has been paid and verified.'),
                                  SizedBox(height: 8),
                                  Text(
                                      '• Redeem your Gracia coins to enjoy exciting rewards and discounts on your future travels.'),
                                ],
                              ),
                              actions: [
                                TextButton(
                                    onPressed: () => Navigator.pop(ctx),
                                    child: const Text('Close')),
                              ],
                            ),
                          );
                        },
                        child: const Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(Icons.help_outline,
                                color: kSlate500, size: 16),
                            SizedBox(width: 6),
                            Text('Learn more about Gracia Points',
                                style: TextStyle(
                                    color: kSlate500,
                                    decoration: TextDecoration.underline,
                                    fontSize: 13)),
                          ],
                        ),
                      ),
                      const SizedBox(height: 24),
                      const Text('RECENT ACTIVITY',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 16,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      if (_history.isEmpty)
                        const Padding(
                            padding: EdgeInsets.all(32),
                            child: Center(
                                child: Text('No points activity yet.',
                                    style: TextStyle(color: kSlate400))))
                      else
                        ..._history.map((entry) {
                          final isEarned = entry['entry_type'] == 'earned';
                          final isReversed = entry['entry_type'] == 'reversed';
                          final points = entry['points'];
                          return Card(
                            margin: const EdgeInsets.only(bottom: 8),
                            child: ListTile(
                              leading: Icon(
                                isEarned
                                    ? Icons.add_circle
                                    : isReversed
                                        ? Icons.remove_circle
                                        : Icons.admin_panel_settings,
                                color: isEarned
                                    ? Colors.green
                                    : isReversed
                                        ? Colors.red
                                        : Colors.orange,
                              ),
                              title: Text(entry['reason'] ?? 'Point adjustment',
                                  style: const TextStyle(fontSize: 14)),
                              subtitle: Text(entry['created_at'] != null
                                  ? entry['created_at']
                                      .toString()
                                      .substring(0, 10)
                                  : ''),
                              trailing: Text('${points > 0 ? '+' : ''}$points',
                                  style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: points > 0
                                          ? Colors.green
                                          : Colors.red,
                                      fontSize: 16)),
                            ),
                          );
                        }),
                    ],
                  ),
                ),
    );
  }
}

String getOperatorLogoUrl(String operatorName) {
  if (operatorName.isEmpty) return '';
  final lower = operatorName.toLowerCase();
  String logo = '';
  if (lower.contains('2go')) {
    logo = '2GO-Logo.png';
  } else if (lower.contains('starlite'))
    logo = 'Starlite_Logo.png';
  else if (lower.contains('cebu'))
    logo = 'CebuPecific-Logo.png';
  else if (lower.contains('pal') || lower.contains('philippine airlines'))
    logo = 'Pal-Logo.jfif';
  else if (lower.contains('airasia')) {
    logo = 'AirAsia-Logo.png';
  }

  if (logo.isEmpty) return '';
  return '${UserSession.getBaseUrl()}/images/$logo';
}

// ==========================================
// SCHEDULES SCREEN
// ==========================================
class SchedulesScreen extends StatefulWidget {
  const SchedulesScreen({super.key});
  @override
  State<SchedulesScreen> createState() => _SchedulesScreenState();
}

class _SchedulesScreenState extends State<SchedulesScreen> {
  bool _loading = true;
  List<dynamic> _routes = [];
  String _filterMode = 'all'; // all, ferry, airline
  String? _originFilter;
  String? _destinationFilter;
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    _fetchSchedules();
    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'booking_created' || event == 'booking_cancelled') {
        _fetchSchedules();
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    super.dispose();
  }

  Future<void> _fetchSchedules() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.get(Uri.parse('$baseUrl/api/all-schedules'));
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        if (mounted) {
          setState(() {
            _routes = parseJsonList(data['routes']);
            _loading = false;
          });
        }
      } else {
        if (mounted) setState(() => _loading = false);
      }
    } catch (e) {
      if (mounted) setState(() => _loading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    if (_loading) {
      return const Center(child: CircularProgressIndicator(color: kGreen));
    }

    final filteredRoutes = _routes.where((r) {
      if (_filterMode != 'all') {
        final mode = r['mode'] ?? 'ferry';
        if (mode != _filterMode) return false;
      }
      if (_originFilter != null && r['origin'] != _originFilter) return false;
      if (_destinationFilter != null &&
          r['destination'] != _destinationFilter) {
        return false;
      }
      return true;
    }).toList();

    final allOrigins = _routes
        .map((r) => r['origin']?.toString() ?? '')
        .where((s) => s.isNotEmpty)
        .toSet()
        .toList();
    final allDestinations = _routes
        .map((r) => r['destination']?.toString() ?? '')
        .where((s) => s.isNotEmpty)
        .toSet()
        .toList();

    return RefreshIndicator(
      onRefresh: _fetchSchedules,
      color: kGreen,
      child: CustomScrollView(
        slivers: [
          SliverToBoxAdapter(
            child: Container(
              color: kGreen,
              padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 30),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Container(
                    padding:
                        const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: Colors.white.withOpacity(0.2),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: const Row(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        Icon(Icons.bolt, color: Colors.greenAccent, size: 16),
                        SizedBox(width: 4),
                        Text('Real-time schedules',
                            style: TextStyle(
                                color: Colors.white,
                                fontSize: 12,
                                fontWeight: FontWeight.bold)),
                      ],
                    ),
                  ),
                  const SizedBox(height: 16),
                  const Text('Schedule and Routes',
                      style: TextStyle(
                          color: Colors.white,
                          fontSize: 28,
                          fontWeight: FontWeight.w900)),
                  const SizedBox(height: 8),
                  const Text(
                      'Browse available ferry and airline routes with live pricing, departure times, and accommodation options.',
                      style: TextStyle(color: Colors.white70, fontSize: 14)),
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(
                children: [
                  _buildFilterBtn('All Routes', 'all', Icons.map),
                  const SizedBox(width: 8),
                  _buildFilterBtn('Ferry', 'ferry', Icons.directions_boat),
                  const SizedBox(width: 8),
                  _buildFilterBtn('Airline', 'airline', Icons.flight),
                ],
              ),
            ),
          ),
          SliverToBoxAdapter(
            child: Padding(
              padding: const EdgeInsets.symmetric(horizontal: 16),
              child: Row(
                children: [
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      value: allOrigins.contains(_originFilter)
                          ? _originFilter
                          : null,
                      hint:
                          const Text('Origin', style: TextStyle(fontSize: 13)),
                      isExpanded: true,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                            borderSide: const BorderSide(color: kSlate200)),
                        filled: true,
                        fillColor: Colors.white,
                      ),
                      items: allOrigins
                          .map((o) => DropdownMenuItem(
                              value: o,
                              child: Text(o,
                                  style: const TextStyle(fontSize: 13))))
                          .toList(),
                      onChanged: (val) => setState(() => _originFilter = val),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Expanded(
                    child: DropdownButtonFormField<String>(
                      value: allDestinations.contains(_destinationFilter)
                          ? _destinationFilter
                          : null,
                      hint: const Text('Destination',
                          style: TextStyle(fontSize: 13)),
                      isExpanded: true,
                      decoration: InputDecoration(
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 8),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(8),
                            borderSide: const BorderSide(color: kSlate200)),
                        filled: true,
                        fillColor: Colors.white,
                      ),
                      items: allDestinations
                          .map((d) => DropdownMenuItem(
                              value: d,
                              child: Text(d,
                                  style: const TextStyle(fontSize: 13))))
                          .toList(),
                      onChanged: (val) =>
                          setState(() => _destinationFilter = val),
                    ),
                  ),
                  if (_originFilter != null || _destinationFilter != null) ...[
                    const SizedBox(width: 8),
                    IconButton(
                      icon: const Icon(Icons.clear, color: Colors.red),
                      onPressed: () => setState(() {
                        _originFilter = null;
                        _destinationFilter = null;
                      }),
                    )
                  ]
                ],
              ),
            ),
          ),
          const SliverToBoxAdapter(child: SizedBox(height: 16)),
          if (filteredRoutes.isEmpty)
            const SliverToBoxAdapter(
              child: Padding(
                padding: EdgeInsets.all(32),
                child: Center(
                  child: Text('No active schedules found.',
                      style: TextStyle(color: kSlate500)),
                ),
              ),
            )
          else
            SliverPadding(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              sliver: SliverList(
                delegate: SliverChildBuilderDelegate(
                  (context, index) {
                    final route = filteredRoutes[index];
                    final schedules =
                        parseAndFilterSchedules(route['schedules']);
                    final isFerry = (route['mode'] ?? 'ferry') == 'ferry';

                    return Card(
                      elevation: 3,
                      margin: const EdgeInsets.only(bottom: 24),
                      shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(16),
                          side: const BorderSide(color: kSlate200)),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Container(
                            padding: const EdgeInsets.all(16),
                            decoration: const BoxDecoration(
                              color: kSlate50,
                              borderRadius: BorderRadius.vertical(
                                  top: Radius.circular(16)),
                              border:
                                  Border(bottom: BorderSide(color: kSlate200)),
                            ),
                            child: Row(
                              children: [
                                // Large operator logo
                                if (getOperatorLogoUrl(route['operator'] ?? '')
                                    .isNotEmpty)
                                  ClipRRect(
                                    borderRadius: BorderRadius.circular(10),
                                    child: Image.network(
                                      getOperatorLogoUrl(
                                          route['operator'] ?? ''),
                                      height: 52,
                                      width: 84,
                                      fit: BoxFit.contain,
                                      errorBuilder: (ctx, err, stack) =>
                                          CircleAvatar(
                                        backgroundColor: isFerry
                                            ? Colors.blue.withOpacity(0.1)
                                            : Colors.amber
                                                .withOpacity(0.1),
                                        child: Icon(
                                            isFerry
                                                ? Icons.directions_boat
                                                : Icons.flight,
                                            color: isFerry
                                                ? Colors.blue
                                                : Colors.amber),
                                      ),
                                    ),
                                  )
                                else
                                  CircleAvatar(
                                    backgroundColor: isFerry
                                        ? Colors.blue.withOpacity(0.1)
                                        : Colors.amber.withOpacity(0.1),
                                    child: Icon(
                                        isFerry
                                            ? Icons.directions_boat
                                            : Icons.flight,
                                        color: isFerry
                                            ? Colors.blue
                                            : Colors.amber),
                                  ),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        children: [
                                          Text(route['origin'],
                                              style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 16)),
                                          const Padding(
                                            padding: EdgeInsets.symmetric(
                                                horizontal: 8),
                                            child: Icon(Icons.arrow_forward,
                                                size: 16, color: kSlate400),
                                          ),
                                          Text(route['destination'],
                                              style: const TextStyle(
                                                  fontWeight: FontWeight.bold,
                                                  fontSize: 16)),
                                        ],
                                      ),
                                      const SizedBox(height: 4),
                                      Text(
                                          route['vehicle']?['full_name'] ??
                                              route['operator'] ??
                                              'Amiga Gracia',
                                          style: const TextStyle(
                                              fontSize: 12, color: kSlate500)),
                                    ],
                                  ),
                                ),
                              ],
                            ),
                          ),
                          SizedBox(
                            height: 280,
                            child: ListView.separated(
                              scrollDirection: Axis.horizontal,
                              padding: const EdgeInsets.all(16),
                              itemCount: schedules.length,
                              separatorBuilder: (_, __) =>
                                  const SizedBox(width: 16),
                              itemBuilder: (context, sIndex) {
                                final s = schedules[sIndex];

                                DateTime? depTime;
                                DateTime? arrTime;
                                String formattedDepTime =
                                    s['formatted_departure'] ??
                                        s['departure_time']
                                            .toString()
                                            .substring(11, 16);
                                String formattedArrTime =
                                    s['formatted_arrival'] ??
                                        s['arrival_time']
                                            .toString()
                                            .substring(11, 16);
                                String exactDate = s['departure_time']
                                    .toString()
                                    .substring(0, 10);
                                String durationStr = '';

                                try {
                                  depTime = DateTime.parse(
                                      s['departure_time'].toString());
                                  arrTime = DateTime.parse(
                                      s['arrival_time'].toString());
                                  formattedDepTime =
                                      DateFormat('h:mm a').format(depTime);
                                  formattedArrTime =
                                      DateFormat('h:mm a').format(arrTime);
                                  exactDate =
                                      '${DateFormat('MMM d, yyyy').format(depTime)} - ${DateFormat('MMM d, yyyy').format(arrTime)}';

                                  final diff = arrTime.difference(depTime);
                                  final hours = diff.inHours;
                                  final mins = diff.inMinutes.remainder(60);
                                  if (hours > 0) durationStr += '${hours}h ';
                                  if (mins > 0) durationStr += '${mins}m';
                                } catch (_) {}

                                return Container(
                                  width: schedules.length == 1
                                      ? MediaQuery.of(context).size.width - 64
                                      : 260,
                                  padding: const EdgeInsets.all(16),
                                  decoration: BoxDecoration(
                                    color: Colors.white,
                                    border: Border.all(color: kSlate200),
                                    borderRadius: BorderRadius.circular(12),
                                  ),
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Expanded(
                                              child: Text(
                                                  s['service_name'] ??
                                                      'Economy',
                                                  style: const TextStyle(
                                                      fontWeight:
                                                          FontWeight.bold),
                                                  maxLines: 1,
                                                  overflow:
                                                      TextOverflow.ellipsis)),
                                          Text(
                                              '$formattedDepTime - $formattedArrTime',
                                              style: const TextStyle(
                                                  fontSize: 12,
                                                  fontWeight: FontWeight.bold,
                                                  color: kGreen)),
                                        ],
                                      ),
                                      const Spacer(),
                                      Row(
                                        mainAxisAlignment:
                                            MainAxisAlignment.spaceBetween,
                                        children: [
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.start,
                                            children: [
                                              Text(formattedDepTime,
                                                  style: const TextStyle(
                                                      fontSize: 18,
                                                      fontWeight:
                                                          FontWeight.bold)),
                                              const Text('DEPART',
                                                  style: TextStyle(
                                                      fontSize: 10,
                                                      color: kSlate400,
                                                      fontWeight:
                                                          FontWeight.bold)),
                                            ],
                                          ),
                                          Column(
                                            children: [
                                              if (durationStr.isNotEmpty)
                                                Text(durationStr,
                                                    style: const TextStyle(
                                                        fontSize: 10,
                                                        color: kSlate500)),
                                              Row(
                                                children: [
                                                  Container(
                                                      width: 8,
                                                      height: 2,
                                                      color: kSlate300),
                                                  const Icon(
                                                      Icons.arrow_forward_ios,
                                                      color: kGreen,
                                                      size: 12),
                                                ],
                                              ),
                                            ],
                                          ),
                                          Column(
                                            crossAxisAlignment:
                                                CrossAxisAlignment.end,
                                            children: [
                                              Text(formattedArrTime,
                                                  style: const TextStyle(
                                                      fontSize: 18,
                                                      fontWeight:
                                                          FontWeight.bold)),
                                              const Text('ARRIVE',
                                                  style: TextStyle(
                                                      fontSize: 10,
                                                      color: kSlate400,
                                                      fontWeight:
                                                          FontWeight.bold)),
                                            ],
                                          ),
                                        ],
                                      ),
                                      const Spacer(),
                                      Row(
                                        children: [
                                          const Icon(Icons.event,
                                              size: 14, color: kSlate400),
                                          const SizedBox(width: 4),
                                          Expanded(
                                              child: Text(exactDate,
                                                  style: const TextStyle(
                                                      fontSize: 12,
                                                      color: kSlate600))),
                                        ],
                                      ),
                                      const Spacer(),
                                      SizedBox(
                                        width: double.infinity,
                                        child: ElevatedButton.icon(
                                          onPressed: () {
                                            Navigator.push(
                                                context,
                                                MaterialPageRoute(
                                                    builder: (_) => Scaffold(
                                                          appBar: AppBar(
                                                            title: const Text(
                                                                'Book Trip',
                                                                style: TextStyle(
                                                                    color: Colors
                                                                        .white,
                                                                    fontWeight:
                                                                        FontWeight
                                                                            .bold)),
                                                            backgroundColor:
                                                                kGreen,
                                                            iconTheme:
                                                                const IconThemeData(
                                                                    color: Colors
                                                                        .white),
                                                          ),
                                                          body: TravelScreen(
                                                            initialMode:
                                                                route['mode'] ??
                                                                    'ferry',
                                                            initialOperator:
                                                                route[
                                                                    'operator'],
                                                            initialOrigin:
                                                                route['origin'],
                                                            initialDestination:
                                                                route[
                                                                    'destination'],
                                                            initialDate:
                                                                s['departure_time']
                                                                    .toString()
                                                                    .substring(
                                                                        0, 10),
                                                          ),
                                                        )));
                                          },
                                          icon: const Icon(Icons.book_online,
                                              size: 16),
                                          label: const Text('Book Now'),
                                          style: ElevatedButton.styleFrom(
                                            backgroundColor: kGreen,
                                            foregroundColor: Colors.white,
                                            shape: RoundedRectangleBorder(
                                                borderRadius:
                                                    BorderRadius.circular(8)),
                                            padding: const EdgeInsets.symmetric(
                                                vertical: 10),
                                          ),
                                        ),
                                      ),
                                    ],
                                  ),
                                );
                              },
                            ),
                          ),
                        ],
                      ),
                    );
                  },
                  childCount: filteredRoutes.length,
                ),
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildFilterBtn(String label, String value, IconData icon) {
    final isActive = _filterMode == value;
    return Expanded(
      child: GestureDetector(
        onTap: () => setState(() => _filterMode = value),
        child: Container(
          padding: const EdgeInsets.symmetric(vertical: 12),
          decoration: BoxDecoration(
            color: isActive ? kGreen : Colors.white,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: isActive ? kGreen : kSlate200),
            boxShadow: isActive
                ? [
                    const BoxShadow(
                        color: Colors.black12,
                        blurRadius: 4,
                        offset: Offset(0, 2))
                  ]
                : [],
          ),
          child: Column(
            children: [
              Icon(icon, size: 20, color: isActive ? Colors.white : kSlate600),
              const SizedBox(height: 4),
              Text(label,
                  style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: isActive ? Colors.white : kSlate600)),
            ],
          ),
        ),
      ),
    );
  }
}

// ==========================================
// VOUCHERS SCREEN
// ==========================================
class VouchersScreen extends StatefulWidget {
  final VoidCallback? onUseVoucher;
  const VouchersScreen({super.key, this.onUseVoucher});

  @override
  State<VouchersScreen> createState() => _VouchersScreenState();
}

class _VouchersScreenState extends State<VouchersScreen> {
  bool _isLoading = true;
  bool _isClaiming = false;
  String _error = '';
  List<dynamic> _vouchers = [];
  final TextEditingController _promoCtrl = TextEditingController();
  StreamSubscription<String>? _eventSub;

  @override
  void initState() {
    super.initState();
    if (UserSession.isLoggedIn) {
      _fetchVouchers();
    } else {
      setState(() => _isLoading = false);
    }
    _eventSub = AppEventBus.stream.listen((event) {
      if (event == 'booking_created') {
        if (UserSession.isLoggedIn) {
          _fetchVouchers();
        }
      }
    });
  }

  @override
  void dispose() {
    _eventSub?.cancel();
    _promoCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchVouchers() async {
    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/vouchers'),
        headers: {
          'Accept': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        if (mounted) {
          setState(() {
            _vouchers = data['vouchers'] ?? [];
            _isLoading = false;
          });
        }
      } else {
        if (mounted) {
          setState(() {
            _error = 'Failed to load vouchers.';
            _isLoading = false;
          });
        }
      }
    } catch (e) {
      if (mounted) {
        setState(() {
          _error = 'Network error occurred.';
          _isLoading = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Discount Coupons'),
        backgroundColor: kGreen,
        foregroundColor: Colors.white,
      ),
      body: !UserSession.isLoggedIn
          ? Center(
              child: Padding(
                padding: const EdgeInsets.all(32),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Container(
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        color: kPink.withOpacity(0.1),
                        shape: BoxShape.circle,
                      ),
                      child: const Icon(Icons.card_giftcard,
                          size: 56, color: kPink),
                    ),
                    const SizedBox(height: 20),
                    const Text('Login Required',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 18,
                            color: kSlate800)),
                    const SizedBox(height: 8),
                    const Text(
                        'Please log in to view your available discount coupons.',
                        style: TextStyle(color: kSlate500, fontSize: 14),
                        textAlign: TextAlign.center),
                    const SizedBox(height: 24),
                    ElevatedButton(
                      onPressed: () => Navigator.pop(context),
                      style: ElevatedButton.styleFrom(
                          backgroundColor: kGreen,
                          foregroundColor: Colors.white,
                          shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12))),
                      child: const Text('Go Back'),
                    ),
                  ],
                ),
              ),
            )
          : _isLoading
              ? const Center(child: CircularProgressIndicator(color: kPink))
              : _error.isNotEmpty
                  ? Center(
                      child: Text(_error,
                          style: const TextStyle(color: Colors.red)))
                  : RefreshIndicator(
                      onRefresh: _fetchVouchers,
                      color: kPink,
                      child: ListView(
                        padding: const EdgeInsets.all(16),
                        children: [
                          // Input Promo Code at top
                          Container(
                            margin: const EdgeInsets.only(bottom: 20),
                            padding: const EdgeInsets.symmetric(
                                horizontal: 16, vertical: 4),
                            decoration: BoxDecoration(
                              color: Colors.white,
                              borderRadius: BorderRadius.circular(8),
                              border: Border.all(color: kSlate200),
                            ),
                            child: Row(
                              children: [
                                const Icon(Icons.card_giftcard,
                                    color: kPink, size: 20),
                                const SizedBox(width: 12),
                                Expanded(
                                  child: TextField(
                                    controller: _promoCtrl,
                                    textCapitalization:
                                        TextCapitalization.characters,
                                    decoration: const InputDecoration(
                                      border: InputBorder.none,
                                      hintText:
                                          'Input promo code / discount coupon',
                                      hintStyle: TextStyle(
                                          color: kSlate400, fontSize: 14),
                                    ),
                                  ),
                                ),
                                TextButton(
                                  onPressed: _isClaiming
                                      ? null
                                      : () async {
                                          final code = _promoCtrl.text.trim();
                                          if (code.isEmpty) return;
                                          setState(() => _isClaiming = true);
                                          try {
                                            final res = await http.post(
                                              Uri.parse(
                                                  '${UserSession.getBaseUrl()}/api/vouchers/claim'),
                                              headers: {
                                                'Accept': 'application/json',
                                                'Content-Type':
                                                    'application/json',
                                                'Authorization':
                                                    'Bearer ${UserSession.token}'
                                              },
                                              body: jsonEncode({'code': code}),
                                            );
                                            final data = jsonDecode(res.body);
                                            if (res.statusCode == 200 &&
                                                data['status'] == 'success') {
                                              _promoCtrl.clear();
                                              if (!mounted) return;
                                              showTopSnack(
                                                  context,
                                                  SnackBar(
                                                      content: Text(data[
                                                              'message'] ??
                                                          'Discount coupon added!'),
                                                      backgroundColor: kGreen));
                                              _fetchVouchers();
                                            } else {
                                              if (!mounted) return;
                                              showTopSnack(
                                                  context,
                                                  SnackBar(
                                                      content: Text(data[
                                                              'message'] ??
                                                          'Invalid coupon code.'),
                                                      backgroundColor:
                                                          Colors.red));
                                            }
                                          } catch (e) {
                                            if (!mounted) return;
                                            showTopSnack(
                                                context,
                                                const SnackBar(
                                                    content:
                                                        Text('Network error.'),
                                                    backgroundColor:
                                                        Colors.red));
                                          } finally {
                                            if (mounted) {
                                              setState(
                                                  () => _isClaiming = false);
                                            }
                                          }
                                        },
                                  child: _isClaiming
                                      ? const SizedBox(
                                          width: 16,
                                          height: 16,
                                          child: CircularProgressIndicator(
                                              strokeWidth: 2))
                                      : const Text('Apply',
                                          style: TextStyle(
                                              color: kGreen,
                                              fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                          ),

                          const Text('Available Discount Coupons',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  fontSize: 18,
                                  color: kSlate800)),
                          const SizedBox(height: 12),
                          if (_vouchers.isEmpty)
                            const Center(
                              child: Padding(
                                padding: EdgeInsets.symmetric(vertical: 48),
                                child: Column(
                                  children: [
                                    Icon(Icons.card_giftcard,
                                        size: 64, color: kSlate200),
                                    SizedBox(height: 16),
                                    Text('No discount coupons available',
                                        style: TextStyle(
                                            color: kSlate400, fontSize: 16)),
                                  ],
                                ),
                              ),
                            )
                          else
                            ..._vouchers.map((v) {
                              return _DiscountCouponCard(
                                voucher: v,
                                isSelected: false,
                                onTap: () {
                                  UserSession.autoApplyVoucherCode = v['code'];
                                  if (widget.onUseVoucher != null) {
                                    widget.onUseVoucher!();
                                  } else {
                                    showDialog(
                                      context: context,
                                      builder: (ctx) => AlertDialog(
                                        shape: RoundedRectangleBorder(
                                            borderRadius:
                                                BorderRadius.circular(16)),
                                        title: const Text('Use Discount Coupon',
                                            style: TextStyle(
                                                fontWeight: FontWeight.bold,
                                                fontSize: 16)),
                                        content: Text(
                                            'Discount Coupon "${v['code']}" (${_getVoucherPercentage(v)}% OFF) will be automatically applied on your booking submission.\n\nProceed to booking?',
                                            style:
                                                const TextStyle(fontSize: 14)),
                                        actions: [
                                          TextButton(
                                              onPressed: () =>
                                                  Navigator.pop(ctx),
                                              child: const Text('Cancel')),
                                          ElevatedButton(
                                            onPressed: () {
                                              Navigator.pop(ctx);
                                              Navigator.pushAndRemoveUntil(
                                                context,
                                                MaterialPageRoute(
                                                    builder: (_) =>
                                                        const MainScreen()),
                                                (route) => false,
                                              );
                                            },
                                            style: ElevatedButton.styleFrom(
                                                backgroundColor: kGreen,
                                                foregroundColor: Colors.white,
                                                shape: RoundedRectangleBorder(
                                                    borderRadius:
                                                        BorderRadius.circular(
                                                            8))),
                                            child: const Text('Go to Booking'),
                                          ),
                                        ],
                                      ),
                                    );
                                  }
                                },
                              );
                            }),
                        ],
                      ),
                    ),
    );
  }
}

String _getVoucherLabel(dynamic v) {
  if (v == null) return '30% OFF';

  if (v['discount_type'] == 'percentage' && v['discount_value'] != null) {
    final val = double.tryParse(v['discount_value'].toString());
    if (val != null && val > 0) {
      return '${val.toInt()}% OFF';
    }
  }

  if (v['discount_type'] != 'percentage' && v['discount_value'] != null) {
    final val = double.tryParse(v['discount_value'].toString());
    if (val != null && val > 0) {
      return '₱${val.toStringAsFixed(0)} OFF';
    }
  }

  final name = v['name']?.toString() ?? '';
  final match = RegExp(r'(\d+)\s*%').firstMatch(name);
  if (match != null) {
    final p = int.tryParse(match.group(1) ?? '');
    if (p != null && p > 0 && p <= 100) return '$p% OFF';
  }

  return '30% OFF';
}

int _getVoucherPercentage(dynamic v) {
  if (v == null) return 30;

  if (v['discount_type'] == 'percentage' && v['discount_value'] != null) {
    final val = double.tryParse(v['discount_value'].toString());
    if (val != null && val > 0) {
      return val.toInt();
    }
  }

  final name = v['name']?.toString() ?? '';
  final match = RegExp(r'(\d+)\s*%').firstMatch(name);
  if (match != null) {
    final p = int.tryParse(match.group(1) ?? '');
    if (p != null && p > 0 && p <= 100) return p;
  }

  return 30;
}

String _formatVoucherExpiry(String? dateStr) {
  if (dateStr == null || dateStr.isEmpty) return 'Expiring: 0 hour left';
  try {
    final dt = DateTime.parse(dateStr);
    final now = DateTime.now();
    if (dt.isBefore(now)) return 'Expiring: 0 hour left';
    final diff = dt.difference(now);
    final hours = diff.inHours;
    if (hours < 1) return 'Expiring: 0 hour left';
    if (hours < 24) return 'Expiring: $hours hour${hours == 1 ? '' : 's'} left';
    final days = diff.inDays;
    if (days < 30) return 'Expiring: $days day${days == 1 ? '' : 's'} left';
    final months = (days / 30).floor();
    return 'Expiring: $months month${months == 1 ? '' : 's'} left';
  } catch (_) {
    return 'Expiring: 0 hour left';
  }
}

// ── Ticket Outline Clipper ───────────────────────────────────────────────
class _CouponCardClipper extends CustomClipper<Path> {
  const _CouponCardClipper({
    required this.seamX,
    required this.cornerRadius,
    required this.notchRadius,
  });

  final double seamX;
  final double cornerRadius;
  final double notchRadius;

  @override
  Path getClip(Size size) {
    final outer = Path()
      ..addRRect(
        RRect.fromRectAndRadius(
          Rect.fromLTWH(0, 0, size.width, size.height),
          Radius.circular(cornerRadius),
        ),
      );
    final topNotch = Path()
      ..addOval(Rect.fromCircle(center: Offset(seamX, 0), radius: notchRadius));
    final bottomNotch = Path()
      ..addOval(Rect.fromCircle(
          center: Offset(seamX, size.height), radius: notchRadius));
    final withTop = Path.combine(PathOperation.difference, outer, topNotch);
    return Path.combine(PathOperation.difference, withTop, bottomNotch);
  }

  @override
  bool shouldReclip(covariant _CouponCardClipper old) =>
      old.seamX != seamX ||
      old.cornerRadius != cornerRadius ||
      old.notchRadius != notchRadius;
}

// ── Zigzag Fill Painter ──────────────────────────────────────────────────
class _ZigzagFillPainter extends CustomPainter {
  const _ZigzagFillPainter({
    required this.seamX,
    required this.color,
    this.toothHeight = 7.0,
    this.amplitude = 5.5,
  });

  final double seamX;
  final Color color;
  final double toothHeight;
  final double amplitude;

  @override
  void paint(Canvas canvas, Size size) {
    final paint = Paint()..color = color;
    final path = Path()..moveTo(seamX, 0);
    double y = 0;
    bool tipOut = true;
    while (y < size.height) {
      final segEnd =
          (y + toothHeight) > size.height ? size.height : y + toothHeight;
      final midY = (y + segEnd) / 2;
      final tipX = seamX + (tipOut ? amplitude : -amplitude);
      path.lineTo(tipX, midY);
      path.lineTo(seamX, segEnd);
      y = segEnd;
      tipOut = !tipOut;
    }
    path
      ..lineTo(size.width, size.height)
      ..lineTo(size.width, 0)
      ..close();
    canvas.drawPath(path, paint);
  }

  @override
  bool shouldRepaint(covariant _ZigzagFillPainter old) =>
      old.seamX != seamX || old.color != color;
}

// ── Gift Box Painter ─────────────────────────────────────────────────────
class _GiftBoxPainter extends CustomPainter {
  const _GiftBoxPainter({required this.color});
  final Color color;

  @override
  void paint(Canvas canvas, Size size) {
    final sw = size.width * 0.036;
    final paint = Paint()
      ..color = color
      ..style = PaintingStyle.stroke
      ..strokeWidth = sw
      ..strokeCap = StrokeCap.round
      ..strokeJoin = StrokeJoin.round;
    final w = size.width;
    final h = size.height;
    // Box body (open top — no bottom lid line to keep it clean)
    canvas.drawRRect(
        RRect.fromRectAndRadius(
            Rect.fromLTWH(w * 0.08, h * 0.42, w * 0.84, h * 0.50),
            Radius.circular(w * 0.05)),
        paint);
    // Lid
    canvas.drawRRect(
        RRect.fromRectAndRadius(
            Rect.fromLTWH(w * 0.01, h * 0.30, w * 0.98, h * 0.15),
            Radius.circular(w * 0.04)),
        paint);
    // NOTE: No vertical ribbon line through the body (matches image 2)
    // Bow loops
    canvas.drawOval(
        Rect.fromCenter(
            center: Offset(w * 0.35, h * 0.16),
            width: w * 0.32,
            height: h * 0.26),
        paint);
    canvas.drawOval(
        Rect.fromCenter(
            center: Offset(w * 0.65, h * 0.16),
            width: w * 0.32,
            height: h * 0.26),
        paint);
    // Knot
    canvas.drawCircle(
        Offset(w * 0.5, h * 0.30), w * 0.05, Paint()..color = color);
  }

  @override
  bool shouldRepaint(covariant _GiftBoxPainter old) => old.color != color;
}

// ── Mini Stat (Min. Spend / Max off) ─────────────────────────────────────
class _VoucherMiniStat extends StatelessWidget {
  const _VoucherMiniStat({
    required this.label,
    required this.value,
    required this.height,
  });
  final String label;
  final String value;
  final double height;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      mainAxisSize: MainAxisSize.min,
      children: [
        Text(label,
            style: TextStyle(
                color: Colors.white,
                fontSize: height * 0.032,
                fontWeight: FontWeight.w600)),
        Text(value,
            style: TextStyle(
                color: Colors.white,
                fontSize: height * 0.05,
                fontWeight: FontWeight.w800)),
      ],
    );
  }
}

// ── Main Coupon Card ─────────────────────────────────────────────────────
class _DiscountCouponCard extends StatelessWidget {
  final Map<String, dynamic>? voucher;
  final bool isSelected;
  final VoidCallback? onTap;

  const _DiscountCouponCard({
    this.voucher,
    this.isSelected = false,
    this.onTap,
  });

  static const Color _brandGreen = Color(0xFF1B7A3B);
  static const Color _brandPink = Color(0xFFEC1E96);
  static const double _seamFraction = 0.625;
  static const double _cornerFraction = 0.12;
  static const double _notchFraction = 0.085;

  /// Returns the display label for the discount, e.g. "20%" or "₱100"
  String _discountLabel() {
    if (voucher == null) return '30%';
    final type = voucher!['discount_type']?.toString() ?? '';
    final raw = voucher!['discount_value'];
    if (raw != null) {
      final val = double.tryParse(raw.toString());
      if (val != null && val > 0) {
        if (type == 'percentage') {
          final isWhole = val == val.roundToDouble();
          return isWhole ? '${val.toInt()}%' : '$val%';
        } else {
          // flat discount
          return '₱${val.toStringAsFixed(0)}';
        }
      }
    }
    return '30%';
  }

  @override
  Widget build(BuildContext context) {
    final String discountLabel = _discountLabel();
    final String expiryLabel =
        _formatVoucherExpiry(voucher?['end_at']?.toString());
    final double? minVal = voucher?['min_booking_amount'] != null
        ? double.tryParse(voucher!['min_booking_amount'].toString())
        : null;
    final String minSpend =
        minVal != null && minVal > 0 ? '₱${minVal.toStringAsFixed(0)}' : '₱0';
    final double? maxVal = voucher?['max_discount'] != null
        ? double.tryParse(voucher!['max_discount'].toString())
        : null;
    final String maxOff =
        maxVal != null && maxVal > 0 ? '₱${maxVal.toStringAsFixed(0)}' : '—';

    return GestureDetector(
      onTap: onTap,
      child: Container(
        margin: const EdgeInsets.only(bottom: 20),
        decoration: BoxDecoration(
          borderRadius: BorderRadius.circular(16),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.14),
              blurRadius: 14,
              offset: const Offset(0, 6),
            ),
          ],
        ),
        child: LayoutBuilder(
          builder: (ctx, constraints) {
            final width = constraints.maxWidth.clamp(0.0, 480.0);
            final height = width / 2.35;
            final seamX = width * _seamFraction;
            final cornerRadius = height * _cornerFraction;
            final notchRadius = height * _notchFraction;
            final leftPad = width * 0.045;
            final greenContentRight = (width - seamX) - width * 0.04;

            return SizedBox(
              width: width,
              height: height,
              child: ClipPath(
                clipper: _CouponCardClipper(
                  seamX: seamX,
                  cornerRadius: cornerRadius,
                  notchRadius: notchRadius,
                ),
                child: Stack(
                  children: [
                    // Base green fill
                    Positioned.fill(child: Container(color: _brandGreen)),
                    // Pink zigzag right half
                    Positioned.fill(
                      child: CustomPaint(
                        painter:
                            _ZigzagFillPainter(seamX: seamX, color: _brandPink),
                      ),
                    ),

                    // ── Green side ──

                    // Logo top-left (smaller)
                    Positioned(
                      left: leftPad,
                      top: height * 0.06,
                      child: Image.network(
                        '${UserSession.getBaseUrl()}/images/amiga_logo_white_outline.png',
                        height: height * 0.20,
                        fit: BoxFit.contain,
                        errorBuilder: (_, __, ___) => Image.asset(
                          'assets/icon/amiga_logo_white_outline.png',
                          height: height * 0.20,
                          fit: BoxFit.contain,
                          errorBuilder: (_, __, ___) => Container(
                            width: height * 0.20,
                            height: height * 0.20,
                            decoration: BoxDecoration(
                              color: Colors.white.withOpacity(0.14),
                              borderRadius:
                                  BorderRadius.circular(height * 0.04),
                              border: Border.all(
                                  color: Colors.white.withOpacity(0.65),
                                  width: 1.4),
                            ),
                            alignment: Alignment.center,
                            child: Icon(Icons.image_outlined,
                                color: Colors.white.withOpacity(0.85),
                                size: height * 0.10),
                          ),
                        ),
                      ),
                    ),

                    // DISCOUNT COUPON headline (centered in green section)
                    Positioned(
                      left: leftPad,
                      right: greenContentRight,
                      top: height * 0.30,
                      child: Text.rich(
                        TextSpan(children: [
                          TextSpan(
                              text: 'DISCOUNT\n',
                              style: _headlineStyle(height)),
                          TextSpan(
                              text: 'COUPON', style: _headlineStyle(height)),
                        ]),
                      ),
                    ),

                    // Expiry label
                    Positioned(
                      left: leftPad,
                      right: greenContentRight,
                      top: height * 0.685,
                      child: Text(
                        expiryLabel,
                        style: TextStyle(
                          color: Colors.white,
                          fontSize: height * 0.052,
                          fontWeight: FontWeight.w800,
                        ),
                      ),
                    ),

                    // Discount label + Min/Max inline in one row
                    Positioned(
                      left: leftPad,
                      right: greenContentRight,
                      bottom: height * 0.06,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Row(
                            mainAxisSize: MainAxisSize.min,
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              _VoucherMiniStat(
                                  label: 'Min. Spend',
                                  value: minSpend,
                                  height: height),
                              SizedBox(width: width * 0.04),
                              _VoucherMiniStat(
                                  label: 'Max off',
                                  value: maxOff,
                                  height: height),
                              SizedBox(width: width * 0.06),
                              _VoucherMiniStat(
                                  label: 'Discount',
                                  value: '$discountLabel OFF',
                                  height: height),
                            ],
                          ),
                        ],
                      ),
                    ),

                    // ── Pink side ──

                    // Gift box with discount label inside (aligned near bottom stats, right gap added)
                    Positioned(
                      left: seamX,
                      right: width * 0.04,
                      top: height * 0.04,
                      bottom: height * 0.28,
                      child: Center(
                        child: AspectRatio(
                          aspectRatio: 1,
                          child: Stack(
                            alignment: Alignment.center,
                            children: [
                              const CustomPaint(size: Size.infinite, painter: _GiftBoxPainter(color: Colors.white),),
                              Padding(
                                padding: EdgeInsets.only(top: height * 0.21),
                                child: FittedBox(
                                  fit: BoxFit.scaleDown,
                                  child: Column(
                                    mainAxisSize: MainAxisSize.min,
                                    children: [
                                      Text(discountLabel,
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: height * 0.145,
                                            fontWeight: FontWeight.w900,
                                            height: 1.0,
                                          )),
                                      Text('OFF',
                                          style: TextStyle(
                                            color: Colors.white,
                                            fontSize: height * 0.115,
                                            fontWeight: FontWeight.w900,
                                            height: 0.95,
                                          )),
                                    ],
                                  ),
                                ),
                              ),
                            ],
                          ),
                        ),
                      ),
                    ),

                    // Brand text bottom-right
                    Positioned(
                      left: seamX + width * 0.02,
                      right: width * 0.02,
                      bottom: height * 0.07,
                      child: Column(
                        children: [
                          Text(
                            'AMIGA GRACIA TRAVEL SERVICES',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: height * 0.036,
                              fontWeight: FontWeight.w700,
                            ),
                          ),
                          SizedBox(height: height * 0.015),
                          Text(
                            'ONLINE COUPON',
                            textAlign: TextAlign.center,
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: height * 0.034,
                              fontWeight: FontWeight.w600,
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Selected check overlay
                    if (isSelected)
                      Positioned(
                        top: 10,
                        right: 10,
                        child: Container(
                          padding: const EdgeInsets.all(4),
                          decoration: const BoxDecoration(
                              color: Colors.white, shape: BoxShape.circle),
                          child: const Icon(Icons.check_circle,
                              color: kGreen, size: 20),
                        ),
                      ),
                  ],
                ),
              ),
            );
          },
        ),
      ),
    );
  }

  TextStyle _headlineStyle(double height) {
    return TextStyle(
      color: Colors.white,
      fontSize: height * 0.175,
      fontWeight: FontWeight.w900,
      height: 0.92,
      letterSpacing: -0.5,
    );
  }
}

// ==========================================
// SERVICE CANCELLATION SCREEN (In-app reschedule/refund)
// ==========================================
class ServiceCancellationScreen extends StatefulWidget {
  final Map<String, dynamic> booking;
  const ServiceCancellationScreen({super.key, required this.booking});

  @override
  State<ServiceCancellationScreen> createState() =>
      _ServiceCancellationScreenState();
}

class _ServiceCancellationScreenState extends State<ServiceCancellationScreen> {
  bool _showRefundForm = false;
  bool _isSubmitting = false;
  bool _submitted = false;
  String _feedback = '';

  // Reschedule state
  String _depDate = '';
  String _retDate = '';
  List<dynamic> _availableSchedules = [];
  List<dynamic> _returnSchedules = [];
  bool _loadingSchedules = false;
  bool _loadingReturnSchedules = false;
  int? _selectedScheduleId;
  int? _selectedReturnScheduleId;
  Map<String, dynamic>? _selectedSchedule;
  Map<String, dynamic>? _selectedReturnSchedule;
  int? _selectedAccommodationId;
  double _selectedAccommodationPrice = 0.0;
  String _selectedAccommodationName = '';
  int? _selectedReturnAccommodationId;
  double _selectedReturnAccommodationPrice = 0.0;
  String _selectedReturnAccommodationName = '';
  XFile? _priceProofFile;

  // Refund form state
  String _refundMethod = 'GCash';
  final _accountNumberCtrl = TextEditingController();
  final _accountNameCtrl = TextEditingController();
  final _bankNameCtrl = TextEditingController();

  @override
  void dispose() {
    _accountNumberCtrl.dispose();
    _accountNameCtrl.dispose();
    _bankNameCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchSchedules(String date) async {
    if (date.isEmpty) return;
    setState(() {
      _loadingSchedules = true;
      _availableSchedules = [];
      _selectedScheduleId = null;
      _selectedSchedule = null;
      _selectedAccommodationId = null;
      _selectedAccommodationPrice = 0.0;
      _selectedAccommodationName = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final origin = widget.booking['origin'] ?? '';
      final dest = widget.booking['destination'] ?? '';
      final res = await http.post(
        Uri.parse('$baseUrl/api/schedules'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}'
        },
        body: jsonEncode({
          'origin': origin,
          'destination': dest,
          'date': date,
          if (widget.booking['mode'] != null) 'mode': widget.booking['mode'],
          if (widget.booking['operator'] != null)
            'operator': widget.booking['operator'],
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() => _availableSchedules =
            parseAndFilterSchedules(data['schedules'], date));
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingSchedules = false);
    }
  }

  String get _refundDestination {
    final parts = ['Method: $_refundMethod'];
    if (_refundMethod != 'GCash' && _bankNameCtrl.text.trim().isNotEmpty) {
      parts.add('Institution: ${_bankNameCtrl.text.trim()}');
    }
    parts.add('Account: ${_accountNumberCtrl.text.trim()}');
    parts.add('Name: ${_accountNameCtrl.text.trim()}');
    return parts.join(' | ');
  }

  Future<void> _submitRefund() async {
    if (_accountNumberCtrl.text.trim().isEmpty ||
        _accountNameCtrl.text.trim().isEmpty) {
      setState(() => _feedback = 'Please fill in all refund fields.');
      return;
    }
    setState(() => _isSubmitting = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final bookingId = widget.booking['id'];
      final res = await http.post(
        Uri.parse('$baseUrl/api/bookings/$bookingId/disruption-refund'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}',
        },
        body: jsonEncode({
          'email': widget.booking['client_email'],
          'refund_destination': _refundDestination,
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _submitted = true;
          _feedback = data['message'] ?? 'Refund request submitted!';
        });
      } else {
        setState(() =>
            _feedback = data['message'] ?? 'Failed to submit refund request.');
      }
    } catch (e) {
      setState(() => _feedback = 'Network error: $e');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  int get _passengerCount {
    final passengers = widget.booking['passengers'];
    if (passengers is List) return passengers.length;
    final adults = widget.booking['adults'] as int? ?? 0;
    final children = widget.booking['children'] as int? ?? 0;
    return (adults + children) > 0 ? (adults + children) : 1;
  }

  double get _oldTotalPrice => _parseDouble(widget.booking['total_price']);

  double get _selectedDepartureCost {
    if (_selectedSchedule == null) return 0.0;
    final schedulePrice = _parseDouble(_selectedSchedule!['price']);
    return (schedulePrice + _selectedAccommodationPrice) * _passengerCount;
  }

  double get _selectedReturnCost {
    if (_selectedReturnSchedule == null) return 0.0;
    final schedulePrice = _parseDouble(_selectedReturnSchedule!['price']);
    return (schedulePrice + _selectedReturnAccommodationPrice) *
        _passengerCount;
  }

  double get _newTotalPrice {
    final vehiclePrice = (widget.booking['has_vehicle'] as bool? ?? false)
        ? _parseDouble(widget.booking['vehicle_price'])
        : 0.0;
    return _selectedDepartureCost + _selectedReturnCost + vehiclePrice;
  }

  double get _priceDiff => (_newTotalPrice - _oldTotalPrice) > 0
      ? (_newTotalPrice - _oldTotalPrice)
      : 0.0;

  bool get _isReturnTrip => widget.booking['return_date'] != null;

  String? get _resumeDate => (widget.booking['service_cancellation']
          as Map<String, dynamic>?)?['resume_date']
      ?.toString();

  DateTime? get _resumeDateTime {
    if (_resumeDate == null || _resumeDate!.isEmpty) return null;
    try {
      return DateTime.parse(_resumeDate!);
    } catch (_) {
      return null;
    }
  }

  bool get _isResumeTba => _resumeDate == null || _resumeDate!.isEmpty;

  bool get _isBeforeResumeDate {
    final resume = _resumeDateTime;
    if (resume == null || _depDate.isEmpty) return false;
    try {
      final chosen = DateTime.parse(_depDate);
      return chosen.isBefore(resume);
    } catch (_) {
      return false;
    }
  }

  bool get _isReturnBeforeResumeDate {
    final resume = _resumeDateTime;
    if (resume == null || _retDate.isEmpty) return false;
    try {
      final chosen = DateTime.parse(_retDate);
      return chosen.isBefore(resume);
    } catch (_) {
      return false;
    }
  }

  bool get _requiresDepartureAccommodation {
    final accommodations =
        (_selectedSchedule?['accommodations'] as List<dynamic>?) ?? [];
    return accommodations.isNotEmpty;
  }

  bool get _requiresReturnAccommodation {
    final accommodations =
        (_selectedReturnSchedule?['accommodations'] as List<dynamic>?) ?? [];
    return accommodations.isNotEmpty;
  }

  Future<void> _fetchReturnSchedules(String date) async {
    if (date.isEmpty) return;
    setState(() {
      _loadingReturnSchedules = true;
      _returnSchedules = [];
      _selectedReturnScheduleId = null;
      _selectedReturnSchedule = null;
      _selectedReturnAccommodationId = null;
      _selectedReturnAccommodationPrice = 0.0;
      _selectedReturnAccommodationName = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final origin = widget.booking['destination'] ?? '';
      final dest = widget.booking['origin'] ?? '';
      final res = await http.post(
        Uri.parse('$baseUrl/api/schedules'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          if (UserSession.token.isNotEmpty)
            'Authorization': 'Bearer ${UserSession.token}'
        },
        body: jsonEncode({
          'origin': origin,
          'destination': dest,
          'date': date,
          if (widget.booking['mode'] != null) 'mode': widget.booking['mode'],
          if (widget.booking['operator'] != null)
            'operator': widget.booking['operator'],
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() =>
            _returnSchedules = parseAndFilterSchedules(data['schedules']));
      }
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loadingReturnSchedules = false);
    }
  }

  Future<void> _pickPriceProof() async {
    try {
      final proof = await ImagePicker()
          .pickImage(source: ImageSource.gallery, imageQuality: 80);
      if (proof != null) {
        setState(() => _priceProofFile = proof);
      }
    } catch (_) {
      // ignore
    }
  }

  Future<void> _submitReschedule() async {
    if (_isResumeTba) {
      setState(() => _feedback =
          'Rescheduling is temporarily unavailable until the operator publishes a resume date.');
      return;
    }
    if (_selectedScheduleId == null || _depDate.isEmpty) return;
    if (_isBeforeResumeDate) {
      setState(() => _feedback =
          'Selected departure date cannot be earlier than the operator resume date.');
      return;
    }
    if (_isReturnTrip && _retDate.isNotEmpty && _isReturnBeforeResumeDate) {
      setState(() => _feedback =
          'Selected return date cannot be earlier than the operator resume date.');
      return;
    }
    if (_requiresDepartureAccommodation && _selectedAccommodationId == null) {
      setState(() => _feedback = 'Please select a departure accommodation.');
      return;
    }
    if (_isReturnTrip && _retDate.isEmpty) {
      setState(() => _feedback = 'Please select a return date.');
      return;
    }
    if (_isReturnTrip && _selectedReturnScheduleId == null) {
      setState(() => _feedback = 'Please select a return schedule.');
      return;
    }
    if (_isReturnTrip &&
        _requiresReturnAccommodation &&
        _selectedReturnAccommodationId == null) {
      setState(() => _feedback = 'Please select a return accommodation.');
      return;
    }
    if (_newTotalPrice != _oldTotalPrice) {
      setState(() => _feedback =
          'For service cancellations, you can only select a replacement of the exact same price as your original ticket.');
      return;
    }

    setState(() => _isSubmitting = true);
    try {
      final baseUrl = UserSession.getBaseUrl();
      final bookingId = widget.booking['id'];
      final request = http.MultipartRequest('POST',
          Uri.parse('$baseUrl/api/bookings/$bookingId/submit-replacement'));
      request.headers['Accept'] = 'application/json';
      if (UserSession.token.isNotEmpty) {
        request.headers['Authorization'] = 'Bearer ${UserSession.token}';
      }
      request.fields['email'] =
          widget.booking['client_email']?.toString() ?? '';
      request.fields['dep_date'] = _depDate;
      request.fields['dep_schedule_id'] = _selectedScheduleId.toString();
      if (_selectedAccommodationId != null) {
        request.fields['dep_accommodation_id'] =
            _selectedAccommodationId.toString();
      }
      if (_isReturnTrip) {
        request.fields['ret_date'] = _retDate;
        request.fields['ret_schedule_id'] =
            _selectedReturnScheduleId.toString();
        if (_selectedReturnAccommodationId != null) {
          request.fields['ret_accommodation_id'] =
              _selectedReturnAccommodationId.toString();
        }
      }
      request.fields['price_diff'] = _priceDiff.toStringAsFixed(2);
      if (_priceDiff > 0 && _priceProofFile != null) {
        request.files.add(
            await http.MultipartFile.fromPath('proof', _priceProofFile!.path));
      }

      final streamedResponse = await request.send();
      final response = await http.Response.fromStream(streamedResponse);
      final data = jsonDecode(response.body);
      if (response.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _submitted = true;
          _feedback = data['message'] ?? 'Reschedule submitted successfully!';
        });
      } else {
        setState(() =>
            _feedback = data['message'] ?? 'Failed to submit reschedule.');
      }
    } catch (e) {
      setState(() => _feedback = 'Network error: $e');
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final cancellation =
        widget.booking['service_cancellation'] as Map<String, dynamic>?;
    final resumeDate = cancellation?['resume_date'];
    final carrier = cancellation?['carrier'] ?? 'the operator';
    final reason = cancellation?['reason_category'] ?? 'service disruption';
    final customerMsg = cancellation?['customer_message'];
    final totalPrice = widget.booking['total_price'];

    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Disruption Options'),
        backgroundColor: Colors.red.shade700,
        foregroundColor: Colors.white,
      ),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          // ── Disruption Banner ──
          Container(
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: LinearGradient(
                  colors: [Colors.amber.shade50, Colors.orange.shade50]),
              borderRadius: BorderRadius.circular(20),
              border: Border.all(color: Colors.amber.shade200),
            ),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  padding:
                      const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                  decoration: BoxDecoration(
                      color: Colors.amber.shade200,
                      borderRadius: BorderRadius.circular(20)),
                  child: const Row(
                    mainAxisSize: MainAxisSize.min,
                    children: [
                      Icon(Icons.warning_amber_rounded,
                          size: 16, color: Color(0xFF78350F)),
                      SizedBox(width: 6),
                      Text('Unavoidable Schedule Disruption',
                          style: TextStyle(
                              fontWeight: FontWeight.w800,
                              fontSize: 11,
                              color: Color(0xFF78350F),
                              letterSpacing: 0.5)),
                    ],
                  ),
                ),
                const SizedBox(height: 12),
                const Text('Select Replacement Travel Date',
                    style: TextStyle(
                        fontWeight: FontWeight.w900,
                        fontSize: 22,
                        color: kSlate800)),
                const SizedBox(height: 6),
                Text(
                  'Your original voyage on ${widget.booking['departure_date'] ?? '—'} was cancelled by $carrier due to ${reason.toString().replaceAll('_', ' ')}.',
                  style: const TextStyle(fontSize: 13, color: kSlate700),
                ),
                if (customerMsg != null) ...[
                  const SizedBox(height: 10),
                  Container(
                    padding: const EdgeInsets.all(12),
                    decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.8),
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(color: Colors.amber.shade200)),
                    child: Text.rich(
                      TextSpan(children: [
                        const TextSpan(
                            text: 'Operator Statement: ',
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: Color(0xFF78350F))),
                        TextSpan(
                            text: customerMsg,
                            style: const TextStyle(color: Color(0xFF78350F))),
                      ]),
                    ),
                  ),
                ],
                const SizedBox(height: 16),
                Row(
                  children: [
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: kSlate200)),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Booking Reference',
                                style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: kSlate400,
                                    letterSpacing: 1)),
                            const SizedBox(height: 4),
                            Text(widget.booking['transaction_number'] ?? '—',
                                style: const TextStyle(
                                    fontWeight: FontWeight.w900,
                                    color: kSlate800)),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Container(
                        padding: const EdgeInsets.all(12),
                        decoration: BoxDecoration(
                            color: Colors.white,
                            borderRadius: BorderRadius.circular(12),
                            border: Border.all(color: kSlate200)),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            const Text('Service Resume Date',
                                style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.bold,
                                    color: kSlate400,
                                    letterSpacing: 1)),
                            const SizedBox(height: 4),
                            Text(
                              resumeDate ?? 'To Be Announced',
                              style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  color: resumeDate != null
                                      ? kGreen
                                      : Colors.amber.shade700),
                            ),
                          ],
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(height: 16),

          // ── Feedback ──
          if (_feedback.isNotEmpty)
            Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(14),
              decoration: BoxDecoration(
                color: _submitted
                    ? kGreen.withOpacity(0.08)
                    : Colors.red.shade50,
                borderRadius: BorderRadius.circular(12),
                border: Border.all(
                    color: _submitted
                        ? kGreen.withOpacity(0.3)
                        : Colors.red.shade200),
              ),
              child: Text(_feedback,
                  style: TextStyle(
                      fontWeight: FontWeight.w600,
                      color: _submitted ? kGreen : Colors.red.shade800)),
            ),

          if (_isResumeTba)
            Container(
              margin: const EdgeInsets.only(bottom: 16),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.orange.shade50,
                borderRadius: BorderRadius.circular(14),
                border: Border.all(color: Colors.orange.shade200),
              ),
              child: const Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('Rescheduling not available yet',
                      style: TextStyle(
                          fontSize: 15,
                          fontWeight: FontWeight.w800,
                          color: Color(0xFF7A4A04))),
                  SizedBox(height: 8),
                  Text(
                      'The operator has not announced a resume date. Please wait until a confirmed service resume date is available before selecting a replacement travel date.',
                      style: TextStyle(fontSize: 13, color: Color(0xFF7A4A04))),
                ],
              ),
            ),

          if (_submitted) ...[
            const SizedBox(height: 8),
            SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: () {
                  Navigator.of(context).pushAndRemoveUntil(
                    MaterialPageRoute(
                        builder: (_) => const MainScreen(initialTab: 4)),
                    (route) => false,
                  );
                },
                style: ElevatedButton.styleFrom(
                    backgroundColor: kGreen,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12))),
                child: const Text('Back to Booking Details',
                    style: TextStyle(fontWeight: FontWeight.bold)),
              ),
            ),
          ] else ...[
            // ── Refund Form ──
            if (_showRefundForm) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(16),
                    side: BorderSide(color: Colors.red.shade200)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Row(
                        mainAxisAlignment: MainAxisAlignment.spaceBetween,
                        children: [
                          Text('Cancel & Request 100% Refund',
                              style: TextStyle(
                                  fontWeight: FontWeight.w900,
                                  fontSize: 16,
                                  color: Colors.red.shade700)),
                          IconButton(
                              icon: const Icon(Icons.close),
                              onPressed: () =>
                                  setState(() => _showRefundForm = false)),
                        ],
                      ),
                      Text(
                        'Since this cancellation was caused by the operator, you are entitled to a full refund of ₱${totalPrice?.toString() ?? '—'}.',
                        style: const TextStyle(fontSize: 13, color: kSlate600),
                      ),
                      const SizedBox(height: 16),
                      // Refund Method
                      const Text('Refund Method',
                          style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: kSlate500)),
                      const SizedBox(height: 6),
                      DropdownButtonFormField<String>(
                        value: _refundMethod,
                        decoration: InputDecoration(
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 10)),
                        items: const [
                          DropdownMenuItem(
                              value: 'GCash', child: Text('GCash')),
                          DropdownMenuItem(
                              value: 'Online Wallet',
                              child: Text('Other Online Wallet')),
                          DropdownMenuItem(
                              value: 'Bank Account',
                              child: Text('Bank Account')),
                        ],
                        onChanged: (v) => setState(() => _refundMethod = v!),
                      ),
                      if (_refundMethod != 'GCash') ...[
                        const SizedBox(height: 12),
                        const Text('Institution Name',
                            style: TextStyle(
                                fontSize: 12,
                                fontWeight: FontWeight.bold,
                                color: kSlate500)),
                        const SizedBox(height: 6),
                        TextFormField(
                          controller: _bankNameCtrl,
                          decoration: InputDecoration(
                              hintText: 'e.g. BDO, Maya',
                              border: OutlineInputBorder(
                                  borderRadius: BorderRadius.circular(10)),
                              contentPadding: const EdgeInsets.symmetric(
                                  horizontal: 12, vertical: 10)),
                        ),
                      ],
                      const SizedBox(height: 12),
                      const Text('Account Number / Mobile',
                          style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: kSlate500)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _accountNumberCtrl,
                        keyboardType: TextInputType.number,
                        decoration: InputDecoration(
                            hintText: 'e.g. 09123456789',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 10)),
                      ),
                      const SizedBox(height: 12),
                      const Text('Account Name',
                          style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.bold,
                              color: kSlate500)),
                      const SizedBox(height: 6),
                      TextFormField(
                        controller: _accountNameCtrl,
                        decoration: InputDecoration(
                            hintText: 'e.g. Juan Dela Cruz',
                            border: OutlineInputBorder(
                                borderRadius: BorderRadius.circular(10)),
                            contentPadding: const EdgeInsets.symmetric(
                                horizontal: 12, vertical: 10)),
                      ),
                      const SizedBox(height: 20),
                      Row(
                        children: [
                          Expanded(
                            child: OutlinedButton(
                              onPressed: () =>
                                  setState(() => _showRefundForm = false),
                              style: OutlinedButton.styleFrom(
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10))),
                              child: const Text('Cancel'),
                            ),
                          ),
                          const SizedBox(width: 12),
                          Expanded(
                            flex: 2,
                            child: ElevatedButton(
                              onPressed: _isSubmitting ? null : _submitRefund,
                              style: ElevatedButton.styleFrom(
                                  backgroundColor: Colors.red.shade700,
                                  foregroundColor: Colors.white,
                                  shape: RoundedRectangleBorder(
                                      borderRadius: BorderRadius.circular(10))),
                              child: _isSubmitting
                                  ? const SizedBox(
                                      width: 18,
                                      height: 18,
                                      child: CircularProgressIndicator(
                                          color: Colors.white, strokeWidth: 2))
                                  : const Text('Submit Refund Request',
                                      style: TextStyle(
                                          fontWeight: FontWeight.bold)),
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // ── Reschedule header ──
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text('Replacement Booking',
                    style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 18,
                        color: kSlate800)),
                if (!_showRefundForm)
                  TextButton(
                    onPressed: () => setState(() => _showRefundForm = true),
                    child: Text('Or Cancel & Refund instead',
                        style: TextStyle(
                            color: Colors.red.shade700,
                            fontWeight: FontWeight.bold,
                            fontSize: 13)),
                  ),
              ],
            ),
            const SizedBox(height: 12),

            // ── Date pickers ──
            Card(
              color: Colors.white,
              shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(14)),
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    const Text('Step 1: Pick new travel dates',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 14,
                            color: kSlate800)),
                    const SizedBox(height: 12),
                    TextFormField(
                      readOnly: true,
                      enabled: !_isResumeTba,
                      decoration: InputDecoration(
                        hintText: _isResumeTba
                            ? 'Awaiting resume date'
                            : 'Select departure date',
                        prefixIcon:
                            const Icon(Icons.calendar_today, color: kGreen),
                        border: OutlineInputBorder(
                            borderRadius: BorderRadius.circular(10)),
                        contentPadding: const EdgeInsets.symmetric(
                            horizontal: 12, vertical: 10),
                        suffixText: _depDate.isNotEmpty ? _depDate : null,
                      ),
                      onTap: _isResumeTba
                          ? () {
                              setState(() => _feedback =
                                  'Rescheduling is temporarily unavailable until the operator publishes a resume date.');
                            }
                          : () async {
                              final minDate = _resumeDateTime ?? DateTime.now();
                              final picked = await showDatePicker(
                                context: context,
                                initialDate: minDate,
                                firstDate: minDate,
                                lastDate: DateTime.now()
                                    .add(const Duration(days: 365)),
                              );
                              if (picked != null) {
                                final dateStr =
                                    '${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
                                setState(() {
                                  _depDate = dateStr;
                                  _selectedScheduleId = null;
                                  _selectedSchedule = null;
                                  _selectedAccommodationId = null;
                                  _selectedAccommodationPrice = 0.0;
                                  _selectedAccommodationName = '';
                                });
                                _fetchSchedules(dateStr);
                              }
                            },
                    ),
                    if (_isReturnTrip) ...[
                      const SizedBox(height: 16),
                      TextFormField(
                        readOnly: true,
                        decoration: InputDecoration(
                          hintText: 'Select return date',
                          prefixIcon:
                              const Icon(Icons.calendar_today, color: kGreen),
                          border: OutlineInputBorder(
                              borderRadius: BorderRadius.circular(10)),
                          contentPadding: const EdgeInsets.symmetric(
                              horizontal: 12, vertical: 10),
                          suffixText: _retDate.isNotEmpty ? _retDate : null,
                        ),
                        onTap: () async {
                          if (_depDate.isEmpty) {
                            setState(() => _feedback = _isResumeTba
                                ? 'Rescheduling is not available until the operator publishes a resume date.'
                                : 'Select a departure date first.');
                            return;
                          }
                          final startDate = DateTime.parse(_depDate)
                              .add(const Duration(days: 1));
                          final picked = await showDatePicker(
                            context: context,
                            initialDate: startDate,
                            firstDate: startDate,
                            lastDate:
                                DateTime.now().add(const Duration(days: 365)),
                          );
                          if (picked != null) {
                            final dateStr =
                                '${picked.year}-${picked.month.toString().padLeft(2, '0')}-${picked.day.toString().padLeft(2, '0')}';
                            setState(() {
                              _retDate = dateStr;
                              _selectedReturnScheduleId = null;
                              _selectedReturnSchedule = null;
                              _selectedReturnAccommodationId = null;
                              _selectedReturnAccommodationPrice = 0.0;
                              _selectedReturnAccommodationName = '';
                            });
                            _fetchReturnSchedules(dateStr);
                          }
                        },
                      ),
                    ],
                  ],
                ),
              ),
            ),
            const SizedBox(height: 12),

            // ── Departure schedule list ──
            if (_depDate.isNotEmpty) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Available departure schedules for $_depDate',
                          style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      if (_loadingSchedules)
                        const Center(
                            child: Padding(
                                padding: EdgeInsets.all(16),
                                child:
                                    CircularProgressIndicator(color: kGreen)))
                      else if (_availableSchedules.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                              color: kSlate50,
                              borderRadius: BorderRadius.circular(10)),
                          child: const Text(
                              'No schedules available on this date for this route.',
                              style: TextStyle(color: kSlate500, fontSize: 13)),
                        )
                      else
                        ...(_availableSchedules.map((s) {
                          final sid = s['id'] as int?;
                          final isSelected = _selectedScheduleId == sid;
                          return GestureDetector(
                            onTap: () => setState(() {
                              _selectedScheduleId = sid;
                              _selectedSchedule = Map<String, dynamic>.from(s);
                              _selectedAccommodationId = null;
                              _selectedAccommodationPrice = 0.0;
                              _selectedAccommodationName = '';
                            }),
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? kGreen.withOpacity(0.06)
                                    : Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                    color: isSelected ? kGreen : kSlate200,
                                    width: isSelected ? 2 : 1),
                              ),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                            s['service'] ??
                                                s['service_name'] ??
                                                'Schedule',
                                            style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                color: kSlate800)),
                                        const SizedBox(height: 4),
                                        Text(
                                            '${s['departure'] ?? s['formatted_departure'] ?? '—'} → ${s['arrival'] ?? s['formatted_arrival'] ?? '—'}',
                                            style: const TextStyle(
                                                color: kSlate600,
                                                fontSize: 13)),
                                      ],
                                    ),
                                  ),
                                  if (isSelected)
                                    const Icon(Icons.check_circle,
                                        color: kGreen),
                                ],
                              ),
                            ),
                          );
                        }).toList()),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            if (_selectedSchedule != null &&
                _requiresDepartureAccommodation) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Step 2: Select departure accommodation',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      ...((_selectedSchedule!['accommodations']
                                  as List<dynamic>? ??
                              [])
                          .map((acc) {
                        final accId = acc['id'] as int?;
                        final isSelected = _selectedAccommodationId == accId;
                        final price = _parseDouble(acc['price']);
                        return GestureDetector(
                          onTap: () => setState(() {
                            _selectedAccommodationId = accId;
                            _selectedAccommodationPrice = price;
                            _selectedAccommodationName =
                                acc['name']?.toString() ?? '';
                          }),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? kGreen.withOpacity(0.06)
                                  : Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: isSelected ? kGreen : kSlate200,
                                  width: isSelected ? 2 : 1),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(acc['name'] ?? 'Accommodation',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              color: kSlate800)),
                                      if (acc['description'] != null) ...[
                                        const SizedBox(height: 4),
                                        Text(acc['description'],
                                            style: const TextStyle(
                                                color: kSlate600,
                                                fontSize: 12)),
                                      ],
                                    ],
                                  ),
                                ),
                                Text('+₱${price.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: kPink)),
                              ],
                            ),
                          ),
                        );
                      }).toList()),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            if (_isReturnTrip && _retDate.isNotEmpty) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text('Available return schedules for $_retDate',
                          style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      if (_loadingReturnSchedules)
                        const Center(
                            child: Padding(
                                padding: EdgeInsets.all(16),
                                child:
                                    CircularProgressIndicator(color: kGreen)))
                      else if (_returnSchedules.isEmpty)
                        Container(
                          padding: const EdgeInsets.all(14),
                          decoration: BoxDecoration(
                              color: kSlate50,
                              borderRadius: BorderRadius.circular(10)),
                          child: const Text(
                              'No return schedules available on this date for this route.',
                              style: TextStyle(color: kSlate500, fontSize: 13)),
                        )
                      else
                        ...(_returnSchedules.map((s) {
                          final sid = s['id'] as int?;
                          final isSelected = _selectedReturnScheduleId == sid;
                          return GestureDetector(
                            onTap: () => setState(() {
                              _selectedReturnScheduleId = sid;
                              _selectedReturnSchedule =
                                  Map<String, dynamic>.from(s);
                              _selectedReturnAccommodationId = null;
                              _selectedReturnAccommodationPrice = 0.0;
                              _selectedReturnAccommodationName = '';
                            }),
                            child: Container(
                              margin: const EdgeInsets.only(bottom: 10),
                              padding: const EdgeInsets.all(14),
                              decoration: BoxDecoration(
                                color: isSelected
                                    ? kGreen.withOpacity(0.06)
                                    : Colors.white,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(
                                    color: isSelected ? kGreen : kSlate200,
                                    width: isSelected ? 2 : 1),
                              ),
                              child: Row(
                                children: [
                                  Expanded(
                                    child: Column(
                                      crossAxisAlignment:
                                          CrossAxisAlignment.start,
                                      children: [
                                        Text(
                                            s['service'] ??
                                                s['service_name'] ??
                                                'Schedule',
                                            style: const TextStyle(
                                                fontWeight: FontWeight.bold,
                                                color: kSlate800)),
                                        const SizedBox(height: 4),
                                        Text(
                                            '${s['departure'] ?? s['formatted_departure'] ?? '—'} → ${s['arrival'] ?? s['formatted_arrival'] ?? '—'}',
                                            style: const TextStyle(
                                                color: kSlate600,
                                                fontSize: 13)),
                                      ],
                                    ),
                                  ),
                                  if (isSelected)
                                    const Icon(Icons.check_circle,
                                        color: kGreen),
                                ],
                              ),
                            ),
                          );
                        }).toList()),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            if (_selectedReturnSchedule != null &&
                _requiresReturnAccommodation) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Step 3: Select return accommodation',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      ...((_selectedReturnSchedule!['accommodations']
                                  as List<dynamic>? ??
                              [])
                          .map((acc) {
                        final accId = acc['id'] as int?;
                        final isSelected =
                            _selectedReturnAccommodationId == accId;
                        final price = _parseDouble(acc['price']);
                        return GestureDetector(
                          onTap: () => setState(() {
                            _selectedReturnAccommodationId = accId;
                            _selectedReturnAccommodationPrice = price;
                            _selectedReturnAccommodationName =
                                acc['name']?.toString() ?? '';
                          }),
                          child: Container(
                            margin: const EdgeInsets.only(bottom: 10),
                            padding: const EdgeInsets.all(14),
                            decoration: BoxDecoration(
                              color: isSelected
                                  ? kGreen.withOpacity(0.06)
                                  : Colors.white,
                              borderRadius: BorderRadius.circular(12),
                              border: Border.all(
                                  color: isSelected ? kGreen : kSlate200,
                                  width: isSelected ? 2 : 1),
                            ),
                            child: Row(
                              children: [
                                Expanded(
                                  child: Column(
                                    crossAxisAlignment:
                                        CrossAxisAlignment.start,
                                    children: [
                                      Text(acc['name'] ?? 'Accommodation',
                                          style: const TextStyle(
                                              fontWeight: FontWeight.bold,
                                              color: kSlate800)),
                                      if (acc['description'] != null) ...[
                                        const SizedBox(height: 4),
                                        Text(acc['description'],
                                            style: const TextStyle(
                                                color: kSlate600,
                                                fontSize: 12)),
                                      ],
                                    ],
                                  ),
                                ),
                                Text('+₱${price.toStringAsFixed(2)}',
                                    style: const TextStyle(
                                        fontWeight: FontWeight.bold,
                                        color: kPink)),
                              ],
                            ),
                          ),
                        );
                      }).toList()),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            if (_selectedSchedule != null) ...[
              Card(
                color: Colors.white,
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(14)),
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Price summary',
                          style: TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 14,
                              color: kSlate800)),
                      const SizedBox(height: 12),
                      _SummaryRow('Original booking total',
                          '₱${_oldTotalPrice.toStringAsFixed(2)}'),
                      _SummaryRow('New total estimate',
                          '₱${_newTotalPrice.toStringAsFixed(2)}'),
                      if (_priceDiff > 0)
                        _SummaryRow('Price difference',
                            '₱${_priceDiff.toStringAsFixed(2)}'),
                      if (_priceDiff == 0)
                        const _SummaryRow(
                            'Price difference', 'No extra payment required'),
                      const SizedBox(height: 12),
                      if (_priceDiff > 0) ...[
                        const Text(
                            'Upload proof of payment for the price difference',
                            style: TextStyle(
                                fontWeight: FontWeight.bold,
                                fontSize: 13,
                                color: kSlate800)),
                        const SizedBox(height: 10),
                        Row(
                          children: [
                            Expanded(
                              child: OutlinedButton.icon(
                                onPressed: _pickPriceProof,
                                icon: const Icon(Icons.upload_file),
                                label: Text(_priceProofFile == null
                                    ? 'Upload proof'
                                    : 'Change proof'),
                              ),
                            ),
                          ],
                        ),
                        if (_priceProofFile != null) ...[
                          const SizedBox(height: 8),
                          Text('Selected file: ${_priceProofFile!.name}',
                              style: const TextStyle(
                                  color: kSlate600, fontSize: 12)),
                        ],
                      ],
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 16),
            ],

            // ── Submit Reschedule Button ──
            if (_selectedScheduleId != null &&
                (!_isReturnTrip || _selectedReturnScheduleId != null))
              SizedBox(
                width: double.infinity,
                height: 50,
                child: ElevatedButton(
                  onPressed: _isSubmitting ? null : _submitReschedule,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: kGreen,
                    foregroundColor: Colors.white,
                    shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(12)),
                  ),
                  child: _isSubmitting
                      ? const SizedBox(
                          width: 20,
                          height: 20,
                          child: CircularProgressIndicator(
                              color: Colors.white, strokeWidth: 2.5))
                      : const Text('Submit Reschedule Request',
                          style: TextStyle(
                              fontWeight: FontWeight.bold, fontSize: 15)),
                ),
              ),
          ],
          const SizedBox(height: 32),
        ],
      ),
    );
  }
}

class RefundScreen extends StatefulWidget {
  final Map<String, dynamic> booking;
  const RefundScreen({super.key, required this.booking});

  @override
  State<RefundScreen> createState() => _RefundScreenState();
}

class _RefundScreenState extends State<RefundScreen> {
  bool _isLoading = true;
  bool _isSubmitting = false;
  String _error = '';
  String _success = '';
  double? _cancellationFee;
  double? _refundAmount;
  double? _transactionFee;
  double? _webAdminFee;
  double? _surchargeAmount;
  int? _surchargePct;
  double? _rebookingSurcharge;
  double? _rebookingRevalidationFee;

  String _refundMethod = 'GCash';
  final _institutionCtrl = TextEditingController();
  final _accountCtrl = TextEditingController();
  final _nameCtrl = TextEditingController();

  @override
  void initState() {
    super.initState();
    _startCancellation();
  }

  @override
  void dispose() {
    _institutionCtrl.dispose();
    _accountCtrl.dispose();
    _nameCtrl.dispose();
    super.dispose();
  }

  Future<void> _startCancellation() async {
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
          Uri.parse('$baseUrl/api/bookings/${widget.booking['id']}/cancel'),
          headers: {
            'Accept': 'application/json'
          },
          body: {
            'email': UserSession.email,
            'action': 'start',
          });
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _cancellationFee = _parseDouble(data['cancellation_fee']);
          _refundAmount = _parseDouble(data['refund_amount']);
          _transactionFee = _parseDouble(data['transaction_fee']);
          _webAdminFee = _parseDouble(data['web_admin_fee']);
          _surchargeAmount = _parseDouble(data['surcharge_amount']);
          _surchargePct = data['surcharge_pct'] != null
              ? int.tryParse(data['surcharge_pct'].toString())
              : 0;
          _rebookingSurcharge = _parseDouble(data['rebooking_surcharge']);
          _rebookingRevalidationFee =
              _parseDouble(data['rebooking_revalidation_fee']);
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = data['message'] ?? 'Unable to request refund at this time.';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Network error. Please try again.';
        _isLoading = false;
      });
    }
  }

  Future<void> _submitRefund() async {
    if (_accountCtrl.text.trim().isEmpty || _nameCtrl.text.trim().isEmpty) {
      showTopSnack(context,
          const SnackBar(content: Text('Please fill out all refund details')));
      return;
    }
    setState(() => _isSubmitting = true);
    final parts = ['Method: $_refundMethod'];
    if (_refundMethod != 'GCash' && _institutionCtrl.text.trim().isNotEmpty) {
      parts.add('Institution: ${_institutionCtrl.text.trim()}');
    }
    parts.add('Account No: ${_accountCtrl.text.trim()}');
    parts.add('Name: ${_nameCtrl.text.trim()}');
    final dest = parts.join(' | ');

    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
          Uri.parse('$baseUrl/api/bookings/${widget.booking['id']}/cancel'),
          headers: {
            'Accept': 'application/json'
          },
          body: {
            'email': UserSession.email,
            'action': 'confirm',
            'refund_destination': dest,
          });
      final data = jsonDecode(res.body);
      if (res.statusCode == 200) {
        setState(() => _success =
            'Refund requested successfully! You will receive an email confirmation shortly.');
      } else {
        if (!mounted) return;
        showTopSnack(
            context,
            SnackBar(
                content: Text(data['message'] ?? 'Error submitting refund')));
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(context,
          const SnackBar(content: Text('Network error. Please try again.')));
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  Widget _buildBreakdownRow(String label, String amount,
      {bool isSub = false,
      bool isBold = false,
      bool isNegative = false,
      Color? color}) {
    return Padding(
      padding: EdgeInsets.only(left: isSub ? 16.0 : 0, top: 2, bottom: 2),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Text(label,
              style: TextStyle(
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
                color: isSub
                    ? Colors.grey.shade600
                    : (color ?? Colors.grey.shade800),
                fontSize: isSub ? 12 : 14,
              )),
          Text('${isNegative ? '-' : ''}₱$amount',
              style: TextStyle(
                fontWeight: isBold ? FontWeight.bold : FontWeight.normal,
                color: color ?? Colors.grey.shade800,
                fontSize: isSub ? 12 : 14,
              )),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final baseTicketPrice = (_refundAmount ?? 0) + (_cancellationFee ?? 0);
    final nonRefundableFees = (_transactionFee ?? 0) +
        (_webAdminFee ?? 0) +
        (_rebookingSurcharge ?? 0) +
        (_rebookingRevalidationFee ?? 0);

    return Scaffold(
        appBar: AppBar(title: const Text('Request Refund')),
        body: _isLoading
            ? const Center(child: CircularProgressIndicator())
            : _error.isNotEmpty
                ? Center(
                    child: Padding(
                        padding: const EdgeInsets.all(20),
                        child: Text(_error,
                            style: const TextStyle(
                                color: Colors.red, fontSize: 16),
                            textAlign: TextAlign.center)))
                : _success.isNotEmpty
                    ? Center(
                        child: Padding(
                            padding: const EdgeInsets.all(20),
                            child: Column(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  const Icon(Icons.check_circle,
                                      color: Colors.green, size: 64),
                                  const SizedBox(height: 16),
                                  Text(_success,
                                      style: const TextStyle(fontSize: 16),
                                      textAlign: TextAlign.center),
                                  const SizedBox(height: 24),
                                  FilledButton(
                                      onPressed: () {
                                        Navigator.of(context)
                                            .pushAndRemoveUntil(
                                          MaterialPageRoute(
                                              builder: (_) => const MainScreen(
                                                  initialTab: 4)),
                                          (route) => false,
                                        );
                                      },
                                      child: const Text('Done'))
                                ])))
                    : ListView(
                        padding: const EdgeInsets.all(16),
                        children: [
                          Container(
                              padding: const EdgeInsets.all(16),
                              decoration: BoxDecoration(
                                color: const Color(0xFFFFFBEB),
                                border:
                                    Border.all(color: const Color(0xFFFDE68A)),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  const Text('Refund Available',
                                      style: TextStyle(
                                          color: Color(0xFF92400E),
                                          fontWeight: FontWeight.bold,
                                          fontSize: 16)),
                                  const SizedBox(height: 4),
                                  const Text(
                                      'The 100% refund window has expired. See the breakdown of your refund below.',
                                      style: TextStyle(
                                          color: Color(0xFF92400E),
                                          fontSize: 12)),
                                  const SizedBox(height: 16),
                                  _buildBreakdownRow('Base Ticket Price:',
                                      baseTicketPrice.toStringAsFixed(2)),
                                  if ((_surchargeAmount ?? 0) > 0)
                                    _buildBreakdownRow(
                                        'Surcharge (${_surchargePct ?? 0}%):',
                                        _surchargeAmount!.toStringAsFixed(2),
                                        isNegative: true),
                                  if (nonRefundableFees > 0) ...[
                                    _buildBreakdownRow('Non-Refundable Fees',
                                        nonRefundableFees.toStringAsFixed(2),
                                        isNegative: true, isBold: true),
                                    if ((_webAdminFee ?? 0) > 0)
                                      _buildBreakdownRow('Web Admin Fee',
                                          _webAdminFee!.toStringAsFixed(2),
                                          isSub: true),
                                    if ((_transactionFee ?? 0) > 0)
                                      _buildBreakdownRow('Transaction Fee',
                                          _transactionFee!.toStringAsFixed(2),
                                          isSub: true),
                                    if ((_rebookingRevalidationFee ?? 0) > 0)
                                      _buildBreakdownRow(
                                          'Revalidation Fee',
                                          _rebookingRevalidationFee!
                                              .toStringAsFixed(2),
                                          isSub: true),
                                  ],
                                  const Padding(
                                    padding:
                                        EdgeInsets.symmetric(vertical: 8.0),
                                    child: Divider(color: Color(0xFFFDE68A)),
                                  ),
                                  _buildBreakdownRow(
                                      'Total Refundable:',
                                      _refundAmount?.toStringAsFixed(2) ??
                                          '0.00',
                                      isBold: true,
                                      color: const Color(0xFF047857)),
                                ],
                              )),
                          const SizedBox(height: 24),
                          const Text('Refund Method',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blueGrey)),
                          const SizedBox(height: 8),
                          DropdownButtonFormField<String>(
                            value: _refundMethod,
                            decoration: const InputDecoration(
                                border: OutlineInputBorder()),
                            items: const [
                              DropdownMenuItem(
                                  value: 'GCash', child: Text('GCash')),
                              DropdownMenuItem(
                                  value: 'Online Wallet',
                                  child: Text('Online Wallet (Maya, etc)')),
                              DropdownMenuItem(
                                  value: 'Bank Account',
                                  child: Text('Bank Account')),
                            ],
                            onChanged: (v) =>
                                setState(() => _refundMethod = v ?? 'GCash'),
                          ),
                          const SizedBox(height: 16),
                          if (_refundMethod != 'GCash') ...[
                            const Text('Bank/Wallet Name',
                                style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    color: Colors.blueGrey)),
                            const SizedBox(height: 8),
                            TextField(
                                controller: _institutionCtrl,
                                decoration: const InputDecoration(
                                    border: OutlineInputBorder())),
                            const SizedBox(height: 16),
                          ],
                          Text(
                              _refundMethod == 'GCash'
                                  ? 'GCash Number'
                                  : 'Account Number',
                              style: const TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blueGrey)),
                          const SizedBox(height: 8),
                          TextField(
                              controller: _accountCtrl,
                              decoration: const InputDecoration(
                                  hintText: 'e.g. 0917xxxxxxx',
                                  border: OutlineInputBorder()),
                              keyboardType: TextInputType.number),
                          const SizedBox(height: 16),
                          const Text('Account Name',
                              style: TextStyle(
                                  fontWeight: FontWeight.bold,
                                  color: Colors.blueGrey)),
                          const SizedBox(height: 8),
                          TextField(
                              controller: _nameCtrl,
                              decoration: const InputDecoration(
                                  hintText: 'Full name on the account',
                                  border: OutlineInputBorder())),
                          const SizedBox(height: 24),
                          Wrap(
                            spacing: 8,
                            runSpacing: 8,
                            alignment: WrapAlignment.center,
                            children: [
                              FilledButton(
                                style: FilledButton.styleFrom(
                                    backgroundColor: const Color(0xFFdb2777),
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 24, vertical: 12)),
                                onPressed: _isSubmitting ? null : _submitRefund,
                                child: _isSubmitting
                                    ? const SizedBox(
                                        height: 20,
                                        width: 20,
                                        child: CircularProgressIndicator(
                                            color: Colors.white,
                                            strokeWidth: 2))
                                    : const Text('Confirm Cancellation'),
                              ),
                              OutlinedButton(
                                style: OutlinedButton.styleFrom(
                                    padding: const EdgeInsets.symmetric(
                                        horizontal: 24, vertical: 12)),
                                onPressed: () => Navigator.pop(context),
                                child: const Text('Cancel Request',
                                    style: TextStyle(color: Colors.blueGrey)),
                              ),
                            ],
                          )
                        ],
                      ));
  }
}

class RebookScreen extends StatefulWidget {
  final Map<String, dynamic> booking;
  const RebookScreen({super.key, required this.booking});

  @override
  State<RebookScreen> createState() => _RebookScreenState();
}

class _RebookScreenState extends State<RebookScreen> {
  int _step =
      0; // 0=dates, 1=dep_schedule, 2=ret_schedule, 3=breakdown, 4=proof
  bool _isLoading = false;
  String _error = '';

  DateTime? _depDate;
  DateTime? _retDate;

  List<dynamic> _depSchedules = [];
  List<dynamic> _retSchedules = [];

  int? _selDepSchId;
  int? _selDepAccId;
  int? _selRetSchId;
  int? _selRetAccId;

  Map<String, dynamic>? _breakdown;
  String? _qrUrl;
  XFile? _proof;
  final TextEditingController _rebookingReferenceCtrl = TextEditingController();

  bool get _isRoundTrip => widget.booking['return_date'] != null;

  Future<void> _fetchDepSchedules() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
        Uri.parse('$baseUrl/api/schedules'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: jsonEncode({
          'origin': widget.booking['origin'] ?? '',
          'destination': widget.booking['destination'] ?? '',
          'date': _depDate!.toIso8601String().split('T')[0],
          'mode': widget.booking['mode'],
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200) {
        setState(() {
          _depSchedules = data['schedules'] ?? [];
          _step = 1;
        });
      } else {
        setState(() => _error = data['message'] ?? 'Failed to fetch schedules');
      }
    } catch (e) {
      setState(() => _error = 'Network error');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _fetchRetSchedules() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
        Uri.parse('$baseUrl/api/schedules'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: jsonEncode({
          'origin': widget.booking['destination'] ?? '',
          'destination': widget.booking['origin'] ?? '',
          'date': _retDate!.toIso8601String().split('T')[0],
          'mode': widget.booking['mode'],
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200) {
        setState(() {
          _retSchedules = data['schedules'] ?? [];
          _step = 2;
        });
      } else {
        setState(() =>
            _error = data['message'] ?? 'Failed to fetch return schedules');
      }
    } catch (e) {
      setState(() => _error = 'Network error');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _calcBreakdown() async {
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final res = await http.post(
        Uri.parse(
            '$baseUrl/api/bookings/${widget.booking['id']}/rebook-calculation'),
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json'
        },
        body: jsonEncode({
          'email': UserSession.email,
          'dep_schedule_id': _selDepSchId,
          'dep_accommodation_id': _selDepAccId,
          'ret_schedule_id': _selRetSchId,
          'ret_accommodation_id': _selRetAccId,
          'is_round_trip': _isRoundTrip,
        }),
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _breakdown = data['breakdown'];
          _qrUrl = data['qr_code_url'];
          _step = 3;
        });
      } else {
        setState(() =>
            _error = data['message'] ?? 'Failed to calculate rebooking cost');
      }
    } catch (e) {
      setState(() => _error = 'Network error');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Future<void> _submitRebook() async {
    final reference = _rebookingReferenceCtrl.text.trim();
    if (_proof == null) {
      setState(() => _error = 'Please upload a proof of payment first.');
      return;
    }
    if (reference.isEmpty) {
      setState(() => _error = 'Please enter the payment reference number.');
      return;
    }
    setState(() {
      _isLoading = true;
      _error = '';
    });
    try {
      final baseUrl = UserSession.getBaseUrl();
      final req = http.MultipartRequest('POST',
          Uri.parse('$baseUrl/api/bookings/${widget.booking['id']}/rebook'));
      req.headers['Accept'] = 'application/json';
      req.fields['email'] = UserSession.email;
      req.fields['reference_number'] = reference;
      req.fields['departure_date'] = _depDate!.toIso8601String().split('T')[0];
      if (_isRoundTrip)
        req.fields['return_date'] = _retDate!.toIso8601String().split('T')[0];
      req.fields['dep_schedule_id'] = _selDepSchId.toString();
      if (_selDepAccId != null)
        req.fields['dep_accommodation_id'] = _selDepAccId.toString();
      if (_selRetSchId != null)
        req.fields['ret_schedule_id'] = _selRetSchId.toString();
      if (_selRetAccId != null)
        req.fields['ret_accommodation_id'] = _selRetAccId.toString();
      req.fields['rate_diff'] = _breakdown!['rate_diff'].toString();
      req.fields['surcharge'] = _breakdown!['surcharge'].toString();
      req.fields['revalidation_fee'] =
          _breakdown!['revalidation_fee'].toString();
      req.fields['total_paid'] = _breakdown!['total_to_pay'].toString();

      final bytes = await _proof!.readAsBytes();
      req.files.add(
          http.MultipartFile.fromBytes('proof', bytes, filename: _proof!.name));

      final res = await req.send();
      if (res.statusCode == 200) {
        if (!mounted) return;
        showTopSnack(context,
            const SnackBar(content: Text('Rebooking requested successfully')));
        Navigator.of(context).pushAndRemoveUntil(
          MaterialPageRoute(builder: (_) => const MainScreen(initialTab: 4)),
          (route) => false,
        );
      } else {
        final b = await res.stream.bytesToString();
        final data = jsonDecode(b);
        setState(() => _error = data['message'] ?? 'Error submitting');
      }
    } catch (e) {
      setState(() => _error = 'Network error');
    } finally {
      setState(() => _isLoading = false);
    }
  }

  Widget _buildDateStep() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Text('Select New Travel Dates',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 16),
        ListTile(
          title: const Text('New Departure Date'),
          subtitle: Text(_depDate == null
              ? 'Not selected'
              : _depDate!.toIso8601String().split('T')[0]),
          trailing: const Icon(Icons.calendar_today),
          onTap: () async {
            final d = await showDatePicker(
                context: context,
                initialDate: DateTime.now().add(const Duration(days: 1)),
                firstDate: DateTime.now().add(const Duration(days: 1)),
                lastDate: DateTime.now().add(const Duration(days: 365)));
            if (d != null)
              setState(() {
                _depDate = d;
                _retDate = null;
              });
          },
        ),
        if (_isRoundTrip)
          ListTile(
            title: const Text('New Return Date'),
            subtitle: Text(_retDate == null
                ? 'Not selected'
                : _retDate!.toIso8601String().split('T')[0]),
            trailing: const Icon(Icons.calendar_today),
            onTap: () async {
              final d = await showDatePicker(
                  context: context,
                  initialDate: _depDate!.add(const Duration(days: 1)),
                  firstDate: _depDate!,
                  lastDate: DateTime.now().add(const Duration(days: 365)));
              if (d != null) setState(() => _retDate = d);
            },
          ),
        const SizedBox(height: 24),
        FilledButton(
          onPressed: _depDate != null && (!_isRoundTrip || _retDate != null)
              ? _fetchDepSchedules
              : null,
          child: const Text('Next'),
        )
      ],
    );
  }

  Widget _buildScheduleStep(bool isReturn) {
    final schs = isReturn ? _retSchedules : _depSchedules;
    final selSchId = isReturn ? _selRetSchId : _selDepSchId;
    final selAccId = isReturn ? _selRetAccId : _selDepAccId;
    final isAirline = widget.booking['mode'] == 'airline';

    if (selSchId == null) {
      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.arrow_back),
                onPressed: () => setState(() {
                  if (isReturn) {
                    _step = 1;
                  } else {
                    _step = 0;
                  }
                }),
              ),
              Expanded(
                child: Text(
                    isReturn
                        ? 'Select New Return Schedule'
                        : 'Select New Departure Schedule',
                    style: const TextStyle(
                        fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 16),
          ...schs.map((s) {
            return GestureDetector(
              onTap: () {
                setState(() {
                  if (isReturn) {
                    _selRetSchId = s['id'];
                    _selRetAccId = null;
                  } else {
                    _selDepSchId = s['id'];
                    _selDepAccId = null;
                  }
                });
              },
              child: Card(
                margin: const EdgeInsets.only(bottom: 12),
                shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(12)),
                elevation: 2,
                child: Padding(
                  padding: const EdgeInsets.all(16),
                  child: Row(
                    children: [
                      Builder(builder: (context) {
                        final logoUrl = getOperatorLogoUrl(s['operator'] ?? '');
                        return Container(
                          width: 50,
                          height: 50,
                          padding: const EdgeInsets.all(8),
                          decoration: BoxDecoration(
                              color: Colors.blue.shade50,
                              borderRadius: BorderRadius.circular(8)),
                          child: logoUrl.isNotEmpty
                              ? Image.network(logoUrl, fit: BoxFit.contain)
                              : Icon(
                                  isAirline
                                      ? Icons.flight
                                      : Icons.directions_boat,
                                  color: Colors.blue),
                        );
                      }),
                      const SizedBox(width: 16),
                      Expanded(
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text('${s['departure']}',
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold, fontSize: 14)),
                            const SizedBox(height: 2),
                            Text('To: ${s['arrival']}',
                                style: const TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 14,
                                    color: Colors.black54)),
                            if ((s['service_name'] ?? '').isNotEmpty) ...[
                              const SizedBox(height: 4),
                              Text('${s['service_name']}',
                                  style: const TextStyle(color: Colors.grey)),
                            ],
                          ],
                        ),
                      ),
                      const Icon(Icons.chevron_right, color: Colors.grey),
                    ],
                  ),
                ),
              ),
            );
          }),
        ],
      );
    } else {
      final selectedSch = schs.firstWhere((s) => s['id'] == selSchId);
      final subList = isAirline
          ? (selectedSch['transport_classes'] as List? ?? [])
          : (selectedSch['accommodations'] as List? ?? []);

      return ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Row(
            children: [
              IconButton(
                icon: const Icon(Icons.arrow_back),
                onPressed: () => setState(() {
                  if (isReturn) {
                    _selRetSchId = null;
                  } else {
                    _selDepSchId = null;
                  }
                }),
              ),
              Expanded(
                child: Text(
                    'Select ${isAirline ? 'Travel Class' : 'Accommodation'}',
                    style: const TextStyle(
                        fontSize: 18, fontWeight: FontWeight.bold)),
              ),
            ],
          ),
          const SizedBox(height: 16),
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              childAspectRatio: 1.2,
              crossAxisSpacing: 12,
              mainAxisSpacing: 12,
            ),
            itemCount: subList.length,
            itemBuilder: (context, index) {
              final tc = subList[index];
              final isAccSel = selAccId == tc['id'];

              final ticketPrice = _parseDouble(selectedSch['price']);
              final combinedPrice = ticketPrice + _parseDouble(tc['price']);

              final isAirline = widget.booking['mode'] == 'airline';

              final tcs = widget.booking['transport_classes'] as List? ?? [];
              final origTcPrice = (tcs.isNotEmpty && isReturn && tcs.length > 1)
                  ? _parseDouble(tcs[1]['pivot']['price'])
                  : (tcs.isNotEmpty
                      ? _parseDouble(tcs[0]['pivot']['price'])
                      : 0.0);

              final originalSchPrice = isReturn
                  ? _parseDouble(widget.booking['return_schedule_price'])
                  : _parseDouble(widget.booking['schedule_price']);
              final originalAccPrice = isReturn
                  ? _parseDouble(
                      widget.booking['return_schedule_accommodation_price'])
                  : _parseDouble(
                      widget.booking['schedule_accommodation_price']);

              final originalPerPax =
                  originalSchPrice + origTcPrice + originalAccPrice;

              final newPerPax =
                  isAirline ? _parseDouble(tc['price']) : combinedPrice;

              final isTooLow = newPerPax < originalPerPax;

              return GestureDetector(
                onTap: isTooLow
                    ? null
                    : () {
                        setState(() {
                          if (isReturn) {
                            _selRetAccId = tc['id'];
                          } else {
                            _selDepAccId = tc['id'];
                          }
                        });
                      },
                child: Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(12),
                  decoration: BoxDecoration(
                    color: isTooLow
                        ? Colors.grey.shade100
                        : (isAccSel
                            ? const Color(0xFFdb2777).withOpacity(0.05)
                            : Colors.white),
                    border: Border.all(
                        color: isAccSel
                            ? const Color(0xFFdb2777)
                            : Colors.grey.shade300,
                        width: isAccSel ? 2 : 1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.center,
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Text(
                        tc['name'] ?? '',
                        style: TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                            color: isTooLow ? Colors.grey : Colors.black),
                        maxLines: 2,
                        overflow: TextOverflow.ellipsis,
                        textAlign: TextAlign.center,
                      ),
                      const SizedBox(height: 8),
                      Text(
                        '₱${combinedPrice.toStringAsFixed(2)}',
                        style: TextStyle(
                            color: isTooLow
                                ? Colors.grey
                                : const Color(0xFFdb2777),
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                            decoration:
                                isTooLow ? TextDecoration.lineThrough : null),
                        textAlign: TextAlign.center,
                      ),
                      if (isTooLow)
                        const Text('Price lower than original',
                            style: TextStyle(color: Colors.red, fontSize: 10)),
                    ],
                  ),
                ),
              );
            },
          ),
          const SizedBox(height: 24),
          FilledButton(
            onPressed: selAccId != null
                ? () {
                    if (isReturn) {
                      _calcBreakdown();
                    } else if (_isRoundTrip) {
                      _fetchRetSchedules();
                    } else
                      _calcBreakdown();
                  }
                : null,
            child: const Text('Next'),
          )
        ],
      );
    }
  }

  Widget _buildBreakdownStep() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        Row(
          children: [
            IconButton(
              icon: const Icon(Icons.arrow_back),
              onPressed: () => setState(() => _step = _isRoundTrip ? 2 : 1),
            ),
            const Expanded(
              child: Text('Rebooking Breakdown',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
            ),
          ],
        ),
        const SizedBox(height: 16),
        Card(
          shape:
              RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
          elevation: 2,
          child: Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text('Summary of Fees',
                    style:
                        TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                const SizedBox(height: 16),
                if (_breakdown!['original_ticket_price'] != null) ...[
                  _buildBreakdownRow(
                      'Original Ticket Price',
                      _breakdown!['original_ticket_price']?.toString() ??
                          '0.00'),
                  const SizedBox(height: 8),
                ],
                if (_breakdown!['new_ticket_price'] != null) ...[
                  _buildBreakdownRow('New Ticket Price',
                      _breakdown!['new_ticket_price']?.toString() ?? '0.00'),
                  const SizedBox(height: 8),
                ],
                _buildBreakdownRow('Revalidation Fee',
                    _breakdown!['total_to_pay']?.toString() ?? '0.00'),
                if (_breakdown!['transaction_fee'] != null) ...[
                  const SizedBox(height: 8),
                  _buildBreakdownRow('Transaction Fee',
                      _breakdown!['transaction_fee']?.toString() ?? '0.00'),
                ],
                if (_breakdown!['web_admin_fee'] != null) ...[
                  const SizedBox(height: 8),
                  _buildBreakdownRow('Web Admin Fee',
                      _breakdown!['web_admin_fee']?.toString() ?? '0.00'),
                ],
                const Padding(
                  padding: EdgeInsets.symmetric(vertical: 12),
                  child: Divider(),
                ),
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text('Total to Pay',
                        style: TextStyle(
                            fontWeight: FontWeight.bold, fontSize: 16)),
                    Text('₱${_breakdown!['total_to_pay']}',
                        style: const TextStyle(
                            fontWeight: FontWeight.bold,
                            fontSize: 20,
                            color: Color(0xFFdb2777))),
                  ],
                ),
              ],
            ),
          ),
        ),
        const SizedBox(height: 24),
        const Text('Reference Number',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
        const SizedBox(height: 8),
        TextField(
          controller: _rebookingReferenceCtrl,
          decoration: InputDecoration(
            hintText: 'e.g. 000123456789',
            filled: true,
            fillColor: kSlate50,
            border: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: kSlate200),
            ),
            enabledBorder: OutlineInputBorder(
              borderRadius: BorderRadius.circular(10),
              borderSide: const BorderSide(color: kSlate200),
            ),
          ),
        ),
        const SizedBox(height: 24),
        _qrUrl != null
            ? Center(child: Image.network(_qrUrl!, height: 150))
            : const Center(
                child: Icon(Icons.qr_code, size: 100, color: kSlate300)),
        const SizedBox(height: 24),
        OutlinedButton.icon(
          onPressed: () async {
            final p = await ImagePicker()
                .pickImage(source: ImageSource.gallery, imageQuality: 80);
            if (p != null)
              setState(() {
                _proof = p;
                _step = 4;
              });
          },
          icon: const Icon(Icons.upload),
          label: const Text('Upload Payment Proof'),
        )
      ],
    );
  }

  Widget _buildBreakdownRow(String label, String amount) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(label, style: TextStyle(color: Colors.grey.shade700)),
        Text('₱$amount', style: const TextStyle(fontWeight: FontWeight.w500)),
      ],
    );
  }

  Widget _buildProofStep() {
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        const Text('Submit Rebooking',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 16),
        const Text('Payment proof uploaded successfully.'),
        const SizedBox(height: 24),
        FilledButton(
          onPressed: _submitRebook,
          child: const Text('Confirm Rebooking'),
        )
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Rebook Booking')),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : _error.isNotEmpty
              ? Center(
                  child: Padding(
                      padding: const EdgeInsets.all(20),
                      child: Column(mainAxisSize: MainAxisSize.min, children: [
                        Text(_error,
                            style: const TextStyle(
                                color: Colors.red, fontSize: 16),
                            textAlign: TextAlign.center),
                        const SizedBox(height: 16),
                        OutlinedButton(
                            onPressed: () => setState(() {
                                  _error = '';
                                  if (_step > 0) _step--;
                                }),
                            child: const Text('Back'))
                      ])))
              : _step == 0
                  ? _buildDateStep()
                  : _step == 1
                      ? _buildScheduleStep(false)
                      : _step == 2
                          ? _buildScheduleStep(true)
                          : _step == 3
                              ? _buildBreakdownStep()
                              : _buildProofStep(),
    );
  }
}
