import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'dart:convert';
import 'package:image_picker/image_picker.dart';

import 'main.dart';

class ReplacementBookingScreen extends StatefulWidget {
  final dynamic booking;
  const ReplacementBookingScreen({super.key, required this.booking});

  @override
  State<ReplacementBookingScreen> createState() => _ReplacementBookingScreenState();
}

class _ReplacementBookingScreenState extends State<ReplacementBookingScreen> {
  bool _isLoading = true;
  String _error = '';
  List<dynamic> _schedules = [];
  dynamic _selectedSchedule;
  dynamic _selectedAccommodation;
  
  double _originalFare = 0.0;
  int _passengerCount = 1;
  double _priceDiff = 0.0;
  XFile? _proofImage;

  int _step = 1;

  @override
  void initState() {
    super.initState();
    _fetchAvailableSchedules();
  }

  Future<void> _fetchAvailableSchedules() async {
    try {
      final res = await http.get(
        Uri.parse('/api/bookings/${widget.booking['id']}/eligible-replacements?email=${widget.booking['client_email']}'),
        headers: {
          'Accept': 'application/json',
          'Authorization': 'Bearer '
        },
      );
      final data = jsonDecode(res.body);
      if (res.statusCode == 200 && data['status'] == 'success') {
        setState(() {
          _schedules = data['schedules'] ?? [];
          _originalFare = (data['original_fare'] ?? 0).toDouble();
          _passengerCount = (data['passengers_count'] ?? 1).toInt();
          _isLoading = false;
        });
      } else {
        setState(() {
          _error = data['message'] ?? 'Failed to load schedules.';
          _isLoading = false;
        });
      }
    } catch (e) {
      setState(() {
        _error = 'Network error.';
        _isLoading = false;
      });
    }
  }

  Future<void> _pickImage() async {
    final picker = ImagePicker();
    final pickedFile = await picker.pickImage(source: ImageSource.gallery);
    if (pickedFile != null) {
      setState(() {
        _proofImage = pickedFile;
      });
    }
  }

  Future<void> _submitReplacement() async {
    if (_selectedSchedule == null || _selectedAccommodation == null) return;
    if (_priceDiff > 0 && _proofImage == null) {
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Proof of payment is required'), backgroundColor: Colors.red));
      return;
    }

    showDialog(
      context: context,
      barrierDismissible: false,
      builder: (ctx) => const Center(child: CircularProgressIndicator(color: kPink)),
    );

    try {
      var request = http.MultipartRequest('POST', Uri.parse('/api/bookings/${widget.booking['id']}/submit-replacement'));
      request.headers['Accept'] = 'application/json';
      request.headers['Authorization'] = 'Bearer ';
      
      request.fields['email'] = widget.booking['client_email'];
      request.fields['dep_date'] = _selectedSchedule['departure_time'].toString().substring(0, 10);
      request.fields['dep_schedule_id'] = _selectedSchedule['id'].toString();
      request.fields['dep_accommodation_id'] = _selectedAccommodation['id'].toString();
      request.fields['price_diff'] = _priceDiff.toString();

      if (_priceDiff > 0 && _proofImage != null) {
        request.files.add(await http.MultipartFile.fromPath('proof', _proofImage!.path));
      }

      var streamedResponse = await request.send();
      var res = await http.Response.fromStream(streamedResponse);
      final data = jsonDecode(res.body);
      
      if (!mounted) return;
      Navigator.pop(context); // pop loading
      
      if (res.statusCode == 200 && data['status'] == 'success') {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message']), backgroundColor: kGreen));
        Navigator.pop(context, true); // pop back to details
      } else {
        ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(data['message'] ?? 'Error occurred'), backgroundColor: Colors.red));
      }
    } catch (e) {
      if (!mounted) return;
      Navigator.pop(context);
      ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Network error'), backgroundColor: Colors.red));
    }
  }
  
  double _calculateNewFare(dynamic acc) {
    double base = (acc['price'] * _passengerCount).toDouble();
    if (widget.booking['has_vehicle'] == true) {
      base += double.parse(widget.booking['vehicle_price'].toString());
    }
    return base;
  }

  Widget _buildStep1() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text('Step 1: Select Eligible Replacement Schedule', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
        const SizedBox(height: 12),
        ..._schedules.map((s) {
          return GestureDetector(
            onTap: () => setState(() {
              _selectedSchedule = s;
              _step = 2;
            }),
            child: Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: kSlate200),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(s['service_name'] ?? 'Economy', style: const TextStyle(fontWeight: FontWeight.bold)),
                  const SizedBox(height: 8),
                  Row(
                    children: [
                      Text(s['formatted_departure'] ?? '', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      const SizedBox(width: 8),
                      const Icon(Icons.arrow_right_alt, color: kGreen),
                      const SizedBox(width: 8),
                      Text(s['formatted_arrival'] ?? '', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    ],
                  ),
                  const SizedBox(height: 8),
                  Text(s['departure_time'].toString().substring(0, 10), style: const TextStyle(color: kSlate600)),
                ],
              ),
            ),
          );
        }),
        if (_schedules.isEmpty)
          const Padding(padding: EdgeInsets.symmetric(vertical: 32), child: Center(child: Text('No available schedules found.'))),
      ],
    );
  }

  Widget _buildStep2() {
    List<dynamic> accommodations = _selectedSchedule['accommodations'] ?? [];
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            IconButton(icon: const Icon(Icons.arrow_back), onPressed: () => setState(() => _step = 1)),
            const Text('Step 2: Select Accommodation', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        const SizedBox(height: 12),
        ...accommodations.map((acc) {
          double newFare = _calculateNewFare(acc);
          bool isCheaper = newFare < _originalFare;
          
          return GestureDetector(
            onTap: () {
              if (isCheaper) {
                ScaffoldMessenger.of(context).showSnackBar(const SnackBar(content: Text('Cannot select a cheaper ticket.'), backgroundColor: Colors.red));
                return;
              }
              setState(() {
                _selectedAccommodation = acc;
                _priceDiff = newFare - _originalFare;
                _step = 3;
              });
            },
            child: Container(
              margin: const EdgeInsets.only(bottom: 12),
              padding: const EdgeInsets.all(16),
              decoration: BoxDecoration(
                color: isCheaper ? Colors.grey.shade200 : Colors.white,
                borderRadius: BorderRadius.circular(8),
                border: Border.all(color: kSlate200),
              ),
              child: Row(
                mainAxisAlignment: MainAxisAlignment.spaceBetween,
                children: [
                  Text(acc['name'], style: TextStyle(fontWeight: FontWeight.bold, color: isCheaper ? Colors.grey : Colors.black)),
                  Text('Php ${newFare.toStringAsFixed(2)}', style: TextStyle(color: isCheaper ? Colors.grey : kGreen, fontWeight: FontWeight.bold)),
                ],
              ),
            ),
          );
        }),
      ],
    );
  }

  Widget _buildStep3() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          children: [
            IconButton(icon: const Icon(Icons.arrow_back), onPressed: () => setState(() => _step = 2)),
            const Text('Step 3: Confirm & Pay', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
          ],
        ),
        const SizedBox(height: 12),
        Container(
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(8), border: Border.all(color: kSlate200)),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('Schedule: ${_selectedSchedule['formatted_departure']} - ${_selectedSchedule['formatted_arrival']}'),
              const SizedBox(height: 8),
              Text('Accommodation: ${_selectedAccommodation['name']}'),
              const SizedBox(height: 8),
              Text('Original Fare: Php ${_originalFare.toStringAsFixed(2)}'),
              const SizedBox(height: 8),
              Text('New Fare: Php ${(_originalFare + _priceDiff).toStringAsFixed(2)}', style: const TextStyle(fontWeight: FontWeight.bold)),
              if (_priceDiff > 0) ...[
                const Divider(height: 32),
                Text('Price Difference: Php ${_priceDiff.toStringAsFixed(2)}', style: const TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 16)),
                const SizedBox(height: 16),
                const Text('Please upload your proof of payment for the price difference.'),
                const SizedBox(height: 12),
                ElevatedButton.icon(
                  onPressed: _pickImage,
                  icon: const Icon(Icons.upload_file),
                  label: Text(_proofImage == null ? 'Upload Proof' : 'Image Selected'),
                ),
              ]
            ],
          ),
        ),
        const SizedBox(height: 24),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: _submitReplacement,
            style: ElevatedButton.styleFrom(backgroundColor: kGreen, foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 16)),
            child: const Text('Confirm Replacement', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          ),
        ),
      ],
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: kBgLight,
      appBar: AppBar(
        title: const Text('Replacement Booking'),
        backgroundColor: kGreen,
        foregroundColor: Colors.white,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator(color: kPink))
          : _error.isNotEmpty
              ? Center(child: Text(_error, style: const TextStyle(color: Colors.red)))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.red.shade50, border: Border.all(color: Colors.red.shade200), borderRadius: BorderRadius.circular(8)),
                      child: const Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Icon(Icons.warning_amber_rounded, color: Colors.red),
                              SizedBox(width: 8),
                              Text('Unavoidable Schedule Disruption', style: TextStyle(color: Colors.red, fontWeight: FontWeight.bold, fontSize: 16)),
                            ],
                          ),
                          SizedBox(height: 8),
                          Text('We apologize for the inconvenience. Please select a replacement schedule and accommodation below.', style: TextStyle(color: Colors.red)),
                        ],
                      ),
                    ),
                    const SizedBox(height: 24),
                    if (_step == 1) _buildStep1(),
                    if (_step == 2) _buildStep2(),
                    if (_step == 3) _buildStep3(),
                  ],
                ),
    );
  }
}
