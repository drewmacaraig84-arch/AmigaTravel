// ignore_for_file: deprecated_member_use, use_build_context_synchronously
import 'dart:async';
import 'dart:convert';
import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'main.dart';

class AccountDeletionScreen extends StatefulWidget {
  const AccountDeletionScreen({super.key});

  @override
  State<AccountDeletionScreen> createState() => _AccountDeletionScreenState();
}

class _AccountDeletionScreenState extends State<AccountDeletionScreen> {
  bool _isLoadingEligibility = true;
  String? _errorMessage;

  bool _isEligible = true;
  int _activeBookingsCount = 0;
  List<dynamic> _activeBookings = [];
  num _pointsForfeited = 0;
  num _pointsWorthPhp = 0;
  int _vouchersCount = 0;

  bool _isAlreadyScheduled = false;
  String? _scheduledDate;
  int _daysRemaining = 0;

  // Form controllers
  final _passwordCtrl = TextEditingController();
  final _otpCtrl = TextEditingController();
  final _confirmTextCtrl = TextEditingController();
  final _feedbackCtrl = TextEditingController();

  bool _obscurePassword = true;
  bool _isSendingOtp = false;
  bool _otpSent = false;
  int _otpCountdown = 0;
  Timer? _otpTimer;
  bool _isSubmitting = false;

  String _selectedReason = 'No longer traveling or booking trips';
  final List<String> _reasons = [
    'No longer traveling or booking trips',
    'I have a duplicate or second account',
    'Privacy or data protection concerns',
    'Ticket pricing or booking fee concerns',
    'Technical issues or app bugs',
    'Dissatisfied with customer service',
    'Other reason',
  ];

  @override
  void initState() {
    super.initState();
    _fetchEligibility();
  }

  @override
  void dispose() {
    _otpTimer?.cancel();
    _passwordCtrl.dispose();
    _otpCtrl.dispose();
    _confirmTextCtrl.dispose();
    _feedbackCtrl.dispose();
    super.dispose();
  }

  Future<void> _fetchEligibility() async {
    setState(() {
      _isLoadingEligibility = true;
      _errorMessage = null;
    });

    try {
      final res = await http.get(
        Uri.parse('${UserSession.getBaseUrl()}/api/profile/delete-eligibility'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}',
        },
      ).timeout(const Duration(seconds: 12));

      if (res.statusCode == 200) {
        final data = jsonDecode(res.body);
        setState(() {
          _isEligible = data['eligible'] ?? true;
          _activeBookingsCount = data['active_bookings_count'] ?? 0;
          _activeBookings = data['active_bookings'] ?? [];
          _pointsForfeited = data['points_forfeited'] ?? 0;
          _pointsWorthPhp = data['points_worth_php'] ?? 0;
          _vouchersCount = data['vouchers_count'] ?? 0;
          _isAlreadyScheduled = data['deletion_scheduled'] ?? false;
          _scheduledDate = data['deletion_scheduled_at'];
          _daysRemaining = data['days_remaining'] ?? 0;
          _isLoadingEligibility = false;
        });
      } else {
        setState(() {
          _errorMessage = 'Failed to verify account deletion eligibility.';
          _isLoadingEligibility = false;
        });
      }
    } catch (e) {
      setState(() {
        _errorMessage = 'Network error: $e';
        _isLoadingEligibility = false;
      });
    }
  }

  Future<void> _sendOtp() async {
    if (_otpCountdown > 0 || _isSendingOtp) return;

    setState(() => _isSendingOtp = true);

    try {
      final res = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/profile/delete/request-otp'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}',
        },
      ).timeout(const Duration(seconds: 12));

      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _otpSent = true;
          _otpCountdown = 60;
        });
        _startOtpTimer();
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
            content: Text(data['message'] ?? 'Verification code sent to your email.'),
            backgroundColor: kGreen,
          ),
        );
      } else {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(
            content: Text(data['message'] ?? 'Failed to send verification code.'),
            backgroundColor: Colors.red,
          ),
        );
      }
    } catch (e) {
      if (!mounted) return;
      showTopSnack(
        context,
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isSendingOtp = false);
    }
  }

  void _startOtpTimer() {
    _otpTimer?.cancel();
    _otpTimer = Timer.periodic(const Duration(seconds: 1), (timer) {
      if (!mounted) return;
      if (_otpCountdown > 0) {
        setState(() => _otpCountdown--);
      } else {
        timer.cancel();
      }
    });
  }

  Future<void> _cancelScheduledDeletion() async {
    setState(() => _isSubmitting = true);
    try {
      final res = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/profile/delete/cancel'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}',
        },
      ).timeout(const Duration(seconds: 12));

      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        if (!mounted) return;
        showTopSnack(
          context,
          const SnackBar(
            content: Text('Account deletion cancelled! Your account is fully active.'),
            backgroundColor: kGreen,
          ),
        );
        Navigator.pop(context);
      } else {
        if (!mounted) return;
        showTopSnack(
          context,
          SnackBar(content: Text(data['message'] ?? 'Failed to cancel deletion.'), backgroundColor: Colors.red),
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

  bool get _canSubmit {
    return _passwordCtrl.text.isNotEmpty &&
        _otpCtrl.text.trim().length == 6 &&
        _confirmTextCtrl.text.trim() == 'DELETE' &&
        !_isSubmitting;
  }

  Future<void> _handleConfirmDeletion() async {
    final password = _passwordCtrl.text;
    final otp = _otpCtrl.text.trim();
    final confirmText = _confirmTextCtrl.text.trim();

    if (password.isEmpty) {
      showTopSnack(context, const SnackBar(content: Text('Please enter your password.'), backgroundColor: Colors.red));
      return;
    }
    if (otp.length != 6) {
      showTopSnack(context, const SnackBar(content: Text('Please enter the 6-digit verification code.'), backgroundColor: Colors.red));
      return;
    }
    if (confirmText != 'DELETE') {
      showTopSnack(context, const SnackBar(content: Text('Please type DELETE in all capital letters.'), backgroundColor: Colors.red));
      return;
    }

    // Modal Confirmation Dialog
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Row(
          children: [
            Icon(Icons.warning_amber_rounded, color: Colors.red, size: 28),
            SizedBox(width: 8),
            Expanded(
              child: Text(
                'Confirm Account Deletion',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
              ),
            ),
          ],
        ),
        content: const Text(
          'Your account will be deactivated immediately and you will be signed out.\n\n'
          'You have a 14-day grace period to restore your account simply by logging back in. '
          'After 14 days, all your personal data, points, and vouchers will be permanently erased.',
          style: TextStyle(color: kSlate700, fontSize: 14, height: 1.4),
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(ctx, false),
            child: const Text('Go Back', style: TextStyle(color: kSlate600)),
          ),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, true),
            style: ElevatedButton.styleFrom(
              backgroundColor: Colors.red,
              foregroundColor: Colors.white,
              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
            ),
            child: const Text('Deactivate & Schedule Deletion'),
          ),
        ],
      ),
    );

    if (confirmed != true) return;

    setState(() => _isSubmitting = true);

    try {
      final res = await http.post(
        Uri.parse('${UserSession.getBaseUrl()}/api/profile/delete/confirm'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer ${UserSession.token}',
        },
        body: {
          'password': password,
          'otp': otp,
          'confirmation_text': confirmText,
          'reason': _selectedReason,
          'feedback': _feedbackCtrl.text.trim(),
        },
      ).timeout(const Duration(seconds: 15));

      final data = jsonDecode(res.body);

      if (res.statusCode == 200 && data['status'] == 'success') {
        await UserSession.clear();

        if (!mounted) return;

        await showDialog(
          context: context,
          barrierDismissible: false,
          builder: (ctx) => AlertDialog(
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
            title: const Row(
              children: [
                Icon(Icons.check_circle_outline, color: Colors.orange, size: 28),
                SizedBox(width: 8),
                Expanded(
                  child: Text(
                    'Account Deactivated',
                    style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18),
                  ),
                ),
              ],
            ),
            content: Text(
              data['message'] ??
                  'Your account has been deactivated. You have 14 days to change your mind by logging back in.',
              style: const TextStyle(color: kSlate700, fontSize: 14, height: 1.4),
            ),
            actions: [
              ElevatedButton(
                onPressed: () => Navigator.pop(ctx),
                style: ElevatedButton.styleFrom(
                  backgroundColor: kSlate800,
                  foregroundColor: Colors.white,
                ),
                child: const Text('OK'),
              ),
            ],
          ),
        );

        if (!mounted) return;
        Navigator.of(context).popUntil((route) => route.isFirst);

        showTopSnack(
          context,
          const SnackBar(
            content: Text('Account deactivated and scheduled for deletion in 14 days.'),
            backgroundColor: Colors.orange,
          ),
        );
      } else {
        final msg = data['message'] ?? 'Failed to delete account.';
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
        SnackBar(content: Text('Error: $e'), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('Delete Account', style: TextStyle(fontWeight: FontWeight.bold, color: kSlate800)),
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: kSlate800),
          onPressed: () => Navigator.pop(context),
        ),
      ),
      body: _isLoadingEligibility
          ? const Center(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  CircularProgressIndicator(color: Colors.red),
                  SizedBox(height: 16),
                  Text('Checking account eligibility...', style: TextStyle(color: kSlate600)),
                ],
              ),
            )
          : _errorMessage != null
              ? Center(
                  child: Padding(
                    padding: const EdgeInsets.all(24),
                    child: Column(
                      mainAxisSize: MainAxisSize.min,
                      children: [
                        const Icon(Icons.error_outline, color: Colors.red, size: 48),
                        const SizedBox(height: 16),
                        Text(_errorMessage!, textAlign: TextAlign.center, style: const TextStyle(color: kSlate700)),
                        const SizedBox(height: 16),
                        ElevatedButton(
                          onPressed: _fetchEligibility,
                          style: ElevatedButton.styleFrom(backgroundColor: kGreen),
                          child: const Text('Try Again', style: TextStyle(color: Colors.white)),
                        ),
                      ],
                    ),
                  ),
                )
              : SingleChildScrollView(
                  padding: const EdgeInsets.all(16),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      // If already scheduled for deletion
                      if (_isAlreadyScheduled) ...[
                        _buildScheduledBanner(),
                        const SizedBox(height: 16),
                      ],

                      // If blocked due to active bookings
                      if (!_isEligible) ...[
                        _buildBlockedView(),
                      ] else ...[
                        // Consequence & Impact Card
                        _buildConsequencesCard(),
                        const SizedBox(height: 20),

                        // Reasons section
                        _buildReasonCard(),
                        const SizedBox(height: 20),

                        // Security Verification Card (Password + OTP + Type DELETE)
                        _buildVerificationCard(),
                        const SizedBox(height: 24),

                        // Deletion CTA button
                        _buildSubmitButton(),
                        const SizedBox(height: 32),
                      ],
                    ],
                  ),
                ),
    );
  }

  Widget _buildScheduledBanner() {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: const Color(0xFFFFFBEB),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: const Color(0xFFFDE68A)),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(Icons.hourglass_top, color: Color(0xFFD97706)),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  'Deletion Grace Period Active ($_daysRemaining days left)',
                  style: const TextStyle(fontWeight: FontWeight.bold, color: Color(0xFF92400E), fontSize: 15),
                ),
              ),
            ],
          ),
          const SizedBox(height: 8),
          if (_scheduledDate != null) ...[
            Text(
              'Permanent Deletion Date: ${_scheduledDate!.split("T").first}',
              style: const TextStyle(fontWeight: FontWeight.w600, color: Color(0xFF92400E), fontSize: 12),
            ),
            const SizedBox(height: 4),
          ],
          const Text(
            'Your account is currently deactivated and scheduled for permanent deletion. '
            'You can cancel this request right now to keep your account, points, and travel history.',
            style: TextStyle(color: Color(0xFFB45309), fontSize: 13, height: 1.4),
          ),
          const SizedBox(height: 12),
          SizedBox(
            width: double.infinity,
            child: ElevatedButton(
              onPressed: _isSubmitting ? null : _cancelScheduledDeletion,
              style: ElevatedButton.styleFrom(
                backgroundColor: kGreen,
                foregroundColor: Colors.white,
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
              child: _isSubmitting
                  ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                  : const Text('Cancel Deletion & Restore Account'),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildBlockedView() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        border: Border.all(color: Colors.red.shade200),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                padding: const EdgeInsets.all(10),
                decoration: BoxDecoration(
                  color: Colors.red.shade50,
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.block_rounded, color: Colors.red, size: 30),
              ),
              const SizedBox(width: 12),
              const Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      'Account Deletion Blocked',
                      style: TextStyle(fontWeight: FontWeight.bold, fontSize: 18, color: kSlate800),
                    ),
                    Text(
                      'Active travel bookings detected',
                      style: TextStyle(color: Colors.red, fontSize: 13, fontWeight: FontWeight.w500),
                    ),
                  ],
                ),
              ),
            ],
          ),
          const SizedBox(height: 16),
          Text(
            'You currently have $_activeBookingsCount upcoming travel booking(s) or pending refund request(s). '
            'To protect your travel arrangements, tickets, and financial refunds, accounts cannot be deleted while active trips exist.',
            style: const TextStyle(color: kSlate700, fontSize: 14, height: 1.4),
          ),
          const SizedBox(height: 16),
          const Divider(),
          const SizedBox(height: 8),
          const Text(
            'Active Bookings & Requests:',
            style: TextStyle(fontWeight: FontWeight.bold, color: kSlate800, fontSize: 14),
          ),
          const SizedBox(height: 10),
          ..._activeBookings.map((b) {
            final transNo = b['transaction_number'] ?? 'N/A';
            final route = '${b['origin'] ?? ''} → ${b['destination'] ?? ''}';
            final depDate = b['departure_date'] ?? '';
            final status = (b['status'] ?? '').toString().toUpperCase();
            final refundStatus = b['refund_status'];

            return Container(
              margin: const EdgeInsets.only(bottom: 10),
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: BorderRadius.circular(10),
                border: Border.all(color: const Color(0xFFCBD5E1)),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Text(
                        'Ref: $transNo',
                        style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kSlate800),
                      ),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                        decoration: BoxDecoration(
                          color: status == 'CONFIRMED' ? Colors.green.shade100 : Colors.amber.shade100,
                          borderRadius: BorderRadius.circular(6),
                        ),
                        child: Text(
                          status,
                          style: TextStyle(
                            fontSize: 11,
                            fontWeight: FontWeight.bold,
                            color: status == 'CONFIRMED' ? Colors.green.shade900 : Colors.amber.shade900,
                          ),
                        ),
                      ),
                    ],
                  ),
                  if (route.trim() != '→') ...[
                    const SizedBox(height: 4),
                    Text(route, style: const TextStyle(fontSize: 13, color: kSlate700)),
                  ],
                  if (depDate.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text('Departure: $depDate', style: const TextStyle(fontSize: 12, color: kSlate500)),
                  ],
                  if (refundStatus != null && refundStatus != 'none') ...[
                    const SizedBox(height: 4),
                    Text(
                      'Refund Status: ${refundStatus.toString().toUpperCase()}',
                      style: const TextStyle(fontSize: 12, color: Colors.deepOrange, fontWeight: FontWeight.bold),
                    ),
                  ],
                ],
              ),
            );
          }),
          const SizedBox(height: 16),
          SizedBox(
            width: double.infinity,
            child: OutlinedButton.icon(
              onPressed: () => Navigator.pop(context),
              icon: const Icon(Icons.arrow_back),
              label: const Text('Back to Profile'),
              style: OutlinedButton.styleFrom(
                foregroundColor: kSlate800,
                side: const BorderSide(color: Color(0xFFCBD5E1)),
                shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
              ),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildConsequencesCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.warning_rounded, color: Colors.orange, size: 24),
              SizedBox(width: 8),
              Text(
                'What Happens If You Delete',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kSlate800),
              ),
            ],
          ),
          const SizedBox(height: 14),
          _buildImpactItem(
            icon: Icons.stars_rounded,
            color: Colors.amber.shade700,
            title: 'Gracia Points Forfeited',
            subtitle: '$_pointsForfeited Gracia Points (worth ₱${_pointsWorthPhp.toStringAsFixed(2)}) will be permanently forfeited.',
          ),
          const SizedBox(height: 12),
          _buildImpactItem(
            icon: Icons.confirmation_number_outlined,
            color: Colors.purple,
            title: 'Vouchers & Discounts',
            subtitle: '$_vouchersCount active promo codes and claimed vouchers will be immediately invalidated.',
          ),
          const SizedBox(height: 12),
          _buildImpactItem(
            icon: Icons.history,
            color: Colors.blueGrey,
            title: 'Travel History & Invoices',
            subtitle: 'Your travel history, e-tickets, and official receipts will no longer be accessible.',
          ),
          const SizedBox(height: 12),
          _buildImpactItem(
            icon: Icons.schedule,
            color: Colors.teal,
            title: '14-Day Grace Period',
            subtitle: 'Your account will be deactivated now. You have 14 days to change your mind by logging in before all personal data is permanently wiped.',
          ),
        ],
      ),
    );
  }

  Widget _buildImpactItem({
    required IconData icon,
    required Color color,
    required String title,
    required String subtitle,
  }) {
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Container(
          padding: const EdgeInsets.all(8),
          decoration: BoxDecoration(
            color: color.withOpacity(0.12),
            borderRadius: BorderRadius.circular(8),
          ),
          child: Icon(icon, color: color, size: 20),
        ),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(title, style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 14, color: kSlate800)),
              const SizedBox(height: 2),
              Text(subtitle, style: const TextStyle(fontSize: 12, color: kSlate600, height: 1.3)),
            ],
          ),
        ),
      ],
    );
  }

  Widget _buildReasonCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Text(
            'Why are you leaving? (Optional)',
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 15, color: kSlate800),
          ),
          const SizedBox(height: 12),
          DropdownButtonFormField<String>(
            value: _selectedReason,
            isExpanded: true,
            decoration: InputDecoration(
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
            items: _reasons.map((r) {
              return DropdownMenuItem(value: r, child: Text(r, style: const TextStyle(fontSize: 13)));
            }).toList(),
            onChanged: (val) {
              if (val != null) setState(() => _selectedReason = val);
            },
          ),
          const SizedBox(height: 12),
          TextField(
            controller: _feedbackCtrl,
            maxLines: 2,
            decoration: InputDecoration(
              hintText: 'Tell us how we could improve (optional)',
              hintStyle: const TextStyle(fontSize: 13, color: kSlate400),
              contentPadding: const EdgeInsets.all(12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildVerificationCard() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          const Row(
            children: [
              Icon(Icons.shield_outlined, color: Colors.red, size: 22),
              SizedBox(width: 8),
              Text(
                'Security Verification',
                style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16, color: kSlate800),
              ),
            ],
          ),
          const SizedBox(height: 6),
          const Text(
            'To protect your identity, verify both your password and email code:',
            style: TextStyle(fontSize: 13, color: kSlate600),
          ),
          const SizedBox(height: 16),

          // 1. Password Field
          const Text('1. Account Password', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kSlate700)),
          const SizedBox(height: 6),
          TextField(
            controller: _passwordCtrl,
            obscureText: _obscurePassword,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              prefixIcon: const Icon(Icons.lock_outline, size: 20),
              suffixIcon: IconButton(
                icon: Icon(_obscurePassword ? Icons.visibility_off : Icons.visibility, size: 20),
                onPressed: () => setState(() => _obscurePassword = !_obscurePassword),
              ),
              hintText: 'Enter your password',
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
          const SizedBox(height: 18),

          // 2. Email OTP Field
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text('2. Email Verification Code', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kSlate700)),
              TextButton(
                onPressed: (_otpCountdown > 0 || _isSendingOtp) ? null : _sendOtp,
                style: TextButton.styleFrom(
                  padding: EdgeInsets.zero,
                  minimumSize: const Size(50, 30),
                  tapTargetSize: MaterialTapTargetSize.shrinkWrap,
                ),
                child: Text(
                  _isSendingOtp
                      ? 'Sending...'
                      : _otpCountdown > 0
                          ? 'Resend in ${_otpCountdown}s'
                          : (_otpSent ? 'Resend Code' : 'Send Code'),
                  style: TextStyle(
                    fontSize: 12,
                    fontWeight: FontWeight.bold,
                    color: (_otpCountdown > 0 || _isSendingOtp) ? kSlate400 : kGreen,
                  ),
                ),
              ),
            ],
          ),
          const SizedBox(height: 6),
          TextField(
            controller: _otpCtrl,
            keyboardType: TextInputType.number,
            maxLength: 6,
            onChanged: (_) => setState(() {}),
            decoration: InputDecoration(
              counterText: '',
              prefixIcon: const Icon(Icons.mark_email_read_outlined, size: 20),
              hintText: '6-digit OTP code',
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
          const SizedBox(height: 4),
          Text(
            'Code will be sent to: ${UserSession.email}',
            style: const TextStyle(fontSize: 11, color: kSlate500),
          ),
          const SizedBox(height: 18),

          // 3. Type DELETE
          const Text('3. Type DELETE to confirm', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 13, color: kSlate700)),
          const SizedBox(height: 6),
          TextField(
            controller: _confirmTextCtrl,
            onChanged: (_) => setState(() {}),
            textCapitalization: TextCapitalization.characters,
            decoration: InputDecoration(
              prefixIcon: const Icon(Icons.edit_note, size: 20),
              hintText: 'Type DELETE',
              contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              border: OutlineInputBorder(borderRadius: BorderRadius.circular(10)),
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildSubmitButton() {
    return ElevatedButton(
      onPressed: _canSubmit ? _handleConfirmDeletion : null,
      style: ElevatedButton.styleFrom(
        backgroundColor: Colors.red.shade700,
        disabledBackgroundColor: Colors.red.shade200,
        foregroundColor: Colors.white,
        padding: const EdgeInsets.symmetric(vertical: 16),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        elevation: _canSubmit ? 2 : 0,
      ),
      child: _isSubmitting
          ? const SizedBox(
              height: 20,
              width: 20,
              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
            )
          : const Text(
              'Schedule Account Deletion',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
            ),
    );
  }
}
