import 'package:flutter_test/flutter_test.dart';
import 'package:amiga_gracia/main.dart';

void main() {
  test('airline booking keeps infant and minor counts in summary data', () {
    final booking = BookingData()
      ..mode = 'airline'
      ..adults = 1
      ..children = 1
      ..minors = 1
      ..infants = 1;

    expect(booking.totalPassengers, 4);
    expect(BookingData.passengerTypeLabel('adult', 1), 'Adult 1');
    expect(BookingData.passengerTypeLabel('minor', 1), 'Minor 1');
    expect(BookingData.passengerTypeLabel('child', 1), 'Child 1');
    expect(BookingData.passengerTypeLabel('infant', 1), 'Infant 1');

    final json = booking.toJson();
    final restored = BookingData.fromJson(json);

    expect(restored.adults, 1);
    expect(restored.children, 1);
    expect(restored.minors, 1);
    expect(restored.infants, 1);
    expect(restored.totalPassengers, 4);
  });
}
