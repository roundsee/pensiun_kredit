import 'package:flutter_test/flutter_test.dart';

import 'package:mobile_flutter_simulasi_kb/main.dart';

void main() {
  testWidgets('simulation page renders', (WidgetTester tester) async {
    await tester.pumpWidget(const SimulationApp());

    expect(find.text('NBP_Simulasi'), findsOneWidget);
    expect(find.text('Login'), findsOneWidget);
  });
}
