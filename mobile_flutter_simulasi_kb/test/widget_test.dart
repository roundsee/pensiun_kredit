import 'package:flutter_test/flutter_test.dart';

import 'package:mobile_flutter_simulasi_kb/main.dart';

void main() {
  test('MANTAP with eligible source bank defaults to 5 when unset', () {
    expect(
      resolveBlokirAngsuranCount(
        bankAsal: 'BANK BTPN',
        bankTujuan: 'MANTAP',
        currentBlokir: '',
      ),
      5,
    );

    expect(
      resolveBlokirAngsuranCount(
        bankAsal: 'BANK MANDIRI',
        bankTujuan: 'MANTAP',
        currentBlokir: '',
      ),
      1,
    );
  });

  test('MANTAP with non-special source bank keeps manual blokir choice unless unset', () {
    expect(
      resolveBlokirAngsuranCount(
        bankAsal: 'BANK MANDIRI',
        bankTujuan: 'MANTAP',
        currentBlokir: '3',
      ),
      3,
    );

    expect(
      resolveBlokirAngsuranCount(
        bankAsal: 'BANK MANDIRI',
        bankTujuan: 'MANTAP',
        currentBlokir: '',
      ),
      1,
    );
  });

  testWidgets('simulation page renders', (WidgetTester tester) async {
    await tester.pumpWidget(const SimulationApp());

    expect(find.text('NBP_Simulasi'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
