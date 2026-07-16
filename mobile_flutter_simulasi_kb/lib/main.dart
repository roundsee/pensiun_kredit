import 'dart:async';
import 'dart:convert';
import 'dart:io';

import 'package:flutter/material.dart';
import 'package:http/http.dart' as http;
import 'package:intl/intl.dart';
import 'package:open_filex/open_filex.dart';
import 'package:path_provider/path_provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  runApp(const SimulationApp());
}

class SimulationApp extends StatelessWidget {
  const SimulationApp({super.key});

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      debugShowCheckedModeBanner: false,
      title: 'Simulasi KB Mobile',
      theme: ThemeData(
        colorScheme: ColorScheme.fromSeed(seedColor: const Color(0xFF184E77)),
        scaffoldBackgroundColor: const Color(0xFFF4F7FB),
      ),
      home: const SimulationPage(),
    );
  }
}

enum FieldType { select, text, date, number, integer, output, section, blank }

class RowDef {
  const RowDef({
    required this.label,
    required this.type,
    this.key,
    this.optionsKey,
    this.allowEmpty = false,
    this.onlyRoleCanEditPricing = false,
    this.format,
  });

  final String label;
  final FieldType type;
  final String? key;
  final String? optionsKey;
  final bool allowEmpty;
  final bool onlyRoleCanEditPricing;
  final String? format;
}

class SimulationPage extends StatefulWidget {
  const SimulationPage({super.key});

  @override
  State<SimulationPage> createState() => _SimulationPageState();
}

class _SimulationPageState extends State<SimulationPage> {
  static const _baseUrlPrefKey = 'kb_simulasi_base_url';
  static const _loginEmailPrefKey = 'kb_simulasi_login_email';
  static const _defaultBaseUrl = 'https://kredit.natabuanapasundan.com/api/mobile/kb-simulasi';
  static const _adminEmail = 'admin@nbp.com';

  final DateFormat _dateFormat = DateFormat('yyyy-MM-dd');
  final NumberFormat _idrFormat = NumberFormat.currency(
    locale: 'id_ID',
    symbol: 'Rp ',
    decimalDigits: 0,
  );

  final TextEditingController _baseUrlController = TextEditingController(
    text: _defaultBaseUrl,
  );

  final Map<String, dynamic> _form = {
    'produk': '',
    'jenis_pensiun': 'Sendiri',
    'mutasi': 'Non Mutasi',
    'bank_asal': '',
    'bank_tujuan': '',
    'keterangan': '',
    'nama_debitur': '',
    'tanggal_simulasi': DateFormat('yyyy-MM-dd').format(DateTime.now()),
    'tanggal_lahir': '',
    'nomor_pensiun': '',
    'instansi': 'TASPEN',
    'gaji_pensiun': '',
    'angsuran_lainnya': '',
    'blokir_angsuran': '1',
    'rate_percent_override': '',
    'admin_angsuran_percent_override': '',
    'tenor': '',
    'plafond': '',
    'pelunasan': '',
    'nama_marketing': '',
    'kode_area': '',
  };

  final List<RowDef> _rows = const [
    RowDef(label: 'Produk', type: FieldType.select, key: 'produk', optionsKey: 'produk', allowEmpty: true),
    RowDef(label: 'Jenis Pensiun', type: FieldType.select, key: 'jenis_pensiun', optionsKey: 'jenis_pensiun'),
    RowDef(label: 'Mutasi', type: FieldType.select, key: 'mutasi', optionsKey: 'mutasi'),
    RowDef(label: 'Bank Asal', type: FieldType.select, key: 'bank_asal', optionsKey: 'bank_asal', allowEmpty: true),
    RowDef(label: 'Bank Tujuan', type: FieldType.select, key: 'bank_tujuan', optionsKey: 'bank_tujuan', allowEmpty: true),
    RowDef(label: '', type: FieldType.blank),
    RowDef(label: 'INPUT DATA', type: FieldType.section),
    RowDef(label: 'Tanggal Simulasi', type: FieldType.date, key: 'tanggal_simulasi'),
    RowDef(label: 'Nama Debitur', type: FieldType.text, key: 'nama_debitur'),
    RowDef(label: 'Tanggal Lahir', type: FieldType.date, key: 'tanggal_lahir'),
    RowDef(label: 'Umur', type: FieldType.output, key: 'umur_text', format: 'text'),
    RowDef(label: 'Nomor Pensiun', type: FieldType.text, key: 'nomor_pensiun'),
    RowDef(label: 'Instansi', type: FieldType.select, key: 'instansi', optionsKey: 'instansi'),
    RowDef(label: 'Gaji Pensiun', type: FieldType.integer, key: 'gaji_pensiun'),
    RowDef(label: 'Angsuran Lainnya', type: FieldType.integer, key: 'angsuran_lainnya'),
    RowDef(label: 'Sisa Gaji saat Pengajuan', type: FieldType.output, key: 'sisa_gaji_saat_pengajuan', format: 'currency'),
    RowDef(label: 'Tenor Max', type: FieldType.output, key: 'tenor_max', format: 'months'),
    RowDef(label: 'Rate (%) Override', type: FieldType.number, key: 'rate_percent_override', onlyRoleCanEditPricing: true),
    RowDef(label: 'Adm Angsuran (%) Override', type: FieldType.integer, key: 'admin_angsuran_percent_override', onlyRoleCanEditPricing: true),
    RowDef(label: 'Tenor', type: FieldType.integer, key: 'tenor'),
    RowDef(label: 'Plafond Max', type: FieldType.output, key: 'plafond_max', format: 'currency'),
    RowDef(label: 'Plafond', type: FieldType.integer, key: 'plafond'),
    RowDef(label: 'Blokir', type: FieldType.select, key: 'blokir_angsuran', optionsKey: 'blokir'),
    RowDef(label: 'ANGSURAN', type: FieldType.output, key: 'angsuran', format: 'currency'),
    RowDef(label: 'Biaya Adm Angs', type: FieldType.output, key: 'biaya_adm_angs', format: 'currency'),
    RowDef(label: 'Total Angsuran', type: FieldType.output, key: 'total_angsuran', format: 'currency'),
    RowDef(label: 'RINCIAN PEMBIAYAAN', type: FieldType.section),
    RowDef(label: 'PROVISI', type: FieldType.output, key: 'provisi', format: 'currency'),
    RowDef(label: 'ADMINISTRASI', type: FieldType.output, key: 'administrasi', format: 'currency'),
    RowDef(label: 'ASURANSI', type: FieldType.output, key: 'asuransi', format: 'currency'),
    RowDef(label: 'Extra Premi', type: FieldType.output, key: 'extra_premi', format: 'currency'),
    RowDef(label: 'BLOKIR AMOUNT', type: FieldType.output, key: 'amount_blokir_angsuran', format: 'currency'),
    RowDef(label: 'TATA LAKSANA', type: FieldType.output, key: 'tata_laksana', format: 'currency'),
    RowDef(label: 'PELUNASAN', type: FieldType.integer, key: 'pelunasan'),
    RowDef(label: 'Nama Marketing', type: FieldType.text, key: 'nama_marketing'),
    RowDef(label: 'Kode Area', type: FieldType.select, key: 'kode_area', optionsKey: 'area', allowEmpty: true),
    RowDef(label: 'USIA LUNAS', type: FieldType.output, key: 'usia_lunas_text', format: 'text'),
    RowDef(label: 'TGL PERMOHONAN', type: FieldType.output, key: 'tgl_permohonan', format: 'date'),
    RowDef(label: 'TGL LUNAS', type: FieldType.output, key: 'tgl_lunas', format: 'date'),
    RowDef(label: 'TOTAL BIAYA', type: FieldType.output, key: 'total_biaya', format: 'currency'),
    RowDef(label: 'SISA GAJI AKHIR', type: FieldType.output, key: 'sisa_gaji_akhir', format: 'currency'),
    RowDef(label: 'TERIMA BERSIH', type: FieldType.output, key: 'terima_bersih', format: 'currency'),
  ];

  Map<String, List<String>> _options = {};
  Map<String, dynamic> _result = {};
  Map<String, dynamic>? _limits;
  bool _loadingConfig = false;
  bool _calculating = false;
  bool _saving = false;
  bool _downloading = false;
  bool _loggingIn = false;
  bool _canEditPricing = true;
  String _loggedInEmail = '';
  String _message = '';
  String _error = '';
  Timer? _debounce;

  bool get _isAdmin => _loggedInEmail.toLowerCase() == _adminEmail;
  bool get _isLoggedIn => _loggedInEmail.isNotEmpty;

  @override
  void initState() {
    super.initState();
    _bootstrapSettingsAndConfig();
  }

  @override
  void dispose() {
    _debounce?.cancel();
    _baseUrlController.dispose();
    super.dispose();
  }

  String get _baseUrl => _baseUrlController.text.trim().replaceAll(RegExp(r'/$'), '');

  String get _mobileLoginUrl {
    if (_baseUrl.endsWith('/kb-simulasi')) {
      final root = _baseUrl.substring(0, _baseUrl.length - '/kb-simulasi'.length);
      return '$root/auth/login';
    }
    return '$_baseUrl/login';
  }

  Future<void> _bootstrapSettingsAndConfig() async {
    await _loadSavedLogin();
    await _loadSavedBaseUrl();
    await _loadConfig();
  }

  Future<void> _loadSavedLogin() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_loginEmailPrefKey);
    if (saved != null && saved.trim().isNotEmpty) {
      _loggedInEmail = saved.trim();
    }
  }

  Future<void> _saveLoginEmail(String email) async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_loginEmailPrefKey, email);
  }

  Future<void> _clearLoginEmail() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.remove(_loginEmailPrefKey);
  }

  Future<void> _showLoginDialog() async {
    final emailController = TextEditingController(text: _loggedInEmail);
    final passwordController = TextEditingController();

    final shouldLogin = await showDialog<bool>(
      context: context,
      builder: (ctx) {
        return AlertDialog(
          title: const Text('Login (Opsional)'),
          content: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              TextField(
                controller: emailController,
                decoration: const InputDecoration(
                  labelText: 'Email',
                  border: OutlineInputBorder(),
                ),
              ),
              const SizedBox(height: 10),
              TextField(
                controller: passwordController,
                obscureText: true,
                decoration: const InputDecoration(
                  labelText: 'Password',
                  border: OutlineInputBorder(),
                ),
              ),
            ],
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.of(ctx).pop(false),
              child: const Text('Batal'),
            ),
            ElevatedButton(
              onPressed: () => Navigator.of(ctx).pop(true),
              child: const Text('Login'),
            ),
          ],
        );
      },
    );

    if (shouldLogin != true) return;

    final email = emailController.text.trim();
    final password = passwordController.text;
    if (email.isEmpty) {
      setState(() {
        _error = 'Email wajib diisi';
      });
      return;
    }

    if (password.isEmpty) {
      setState(() {
        _error = 'Password wajib diisi';
      });
      return;
    }

    setState(() {
      _loggingIn = true;
      _error = '';
      _message = '';
    });

    try {
      final response = await http.post(
        Uri.parse(_mobileLoginUrl),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({
          'email': email,
          'password': password,
        }),
      );

      final jsonMap = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode >= 400) {
        throw Exception('${jsonMap['message'] ?? 'Login gagal'}');
      }

      final user = jsonMap['user'] as Map<String, dynamic>?;
      final loginEmail = (user?['email'] ?? email).toString();
      await _saveLoginEmail(loginEmail);

      setState(() {
        _loggedInEmail = loginEmail;
        _message = _isAdmin
            ? 'Login admin berhasil. Pengaturan endpoint ditampilkan.'
            : 'Login berhasil sebagai $loginEmail';
      });
    } catch (e) {
      setState(() {
        _error = 'Login gagal: $e';
      });
    } finally {
      setState(() {
        _loggingIn = false;
      });
    }
  }

  Future<void> _logout() async {
    await _clearLoginEmail();
    setState(() {
      _loggedInEmail = '';
      _message = 'Logout berhasil';
      _error = '';
    });
  }

  Future<void> _loadSavedBaseUrl() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_baseUrlPrefKey);
    if (saved != null && saved.trim().isNotEmpty) {
      _baseUrlController.text = saved.trim();
    }
  }

  Future<void> _saveBaseUrl() async {
    final value = _baseUrl;
    if (value.isEmpty || !value.startsWith('http')) {
      setState(() {
        _error = 'URL endpoint harus diawali http:// atau https://';
      });
      return;
    }

    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_baseUrlPrefKey, value);
    setState(() {
      _message = 'Endpoint tersimpan: $value';
      _error = '';
    });
  }

  Future<void> _loadConfig() async {
    setState(() {
      _loadingConfig = true;
      _error = '';
      _message = '';
    });

    try {
      final response = await http.get(Uri.parse('$_baseUrl/config'));
      if (response.statusCode != 200) {
        throw Exception('Gagal load config: ${response.statusCode}');
      }
      final jsonMap = jsonDecode(response.body) as Map<String, dynamic>;
      final optionsMap = (jsonMap['options'] as Map<String, dynamic>? ?? {})
          .map((k, v) => MapEntry(k, (v as List<dynamic>).map((e) => '$e').toList()));

      setState(() {
        _options = optionsMap;
        _canEditPricing = (jsonMap['permissions']?['can_edit_pricing'] as bool?) ?? true;
        _applyDefaultValues();
        _message = 'Konfigurasi berhasil dimuat';
      });
      _scheduleCalculate();
    } catch (e) {
      setState(() {
        _error = 'Tidak bisa menghubungkan ke server: $e';
      });
    } finally {
      setState(() {
        _loadingConfig = false;
      });
    }
  }

  void _applyDefaultValues() {
    _form['produk'] = _pickDefault(_options['produk'], 'Platinum');
    _form['jenis_pensiun'] = _pickDefault(_options['jenis_pensiun'], 'Sendiri');
    _form['instansi'] = _pickDefault(['TASPEN', 'ASABRI'], 'TASPEN');
    _form['mutasi'] = _pickDefault(['Mutasi', 'Non Mutasi'], 'Non Mutasi');
    _form['blokir_angsuran'] = '1';
  }

  String _pickDefault(List<String>? values, String fallback) {
    if (values == null || values.isEmpty) return fallback;
    final found = values.firstWhere(
      (e) => e.toLowerCase() == fallback.toLowerCase(),
      orElse: () => values.first,
    );
    return found;
  }

  List<String> _getRowOptions(RowDef row) {
    switch (row.optionsKey) {
      case 'mutasi':
        return ['Mutasi', 'Non Mutasi'];
      case 'blokir':
        return ['1', '2', '3', '4', '5'];
      case 'instansi':
        return ['TASPEN', 'ASABRI'];
      default:
        return _options[row.optionsKey] ?? [];
    }
  }

  void _onFieldChanged(String key, dynamic value) {
    setState(() {
      _form[key] = value;
      if (key == 'tanggal_lahir' || key == 'tanggal_simulasi') {
        _autoSetProdukByAge();
      }
      _error = '';
      _message = '';
    });
    _scheduleCalculate();
  }

  void _autoSetProdukByAge() {
    final birthRaw = _form['tanggal_lahir'];
    if (birthRaw == null || '$birthRaw'.isEmpty) return;
    final simRaw = _form['tanggal_simulasi'];
    final birth = DateTime.tryParse('$birthRaw');
    final sim = DateTime.tryParse('$simRaw') ?? DateTime.now();
    if (birth == null) return;
    var years = sim.year - birth.year;
    if (sim.month < birth.month || (sim.month == birth.month && sim.day < birth.day)) {
      years--;
    }
    final options = _options['produk'] ?? ['Platinum', 'Regular'];
    final recommended = years < 68 ? 'Regular' : 'Platinum';
    final matched = options.firstWhere(
      (v) => v.toLowerCase() == recommended.toLowerCase(),
      orElse: () => _form['produk'] as String,
    );
    _form['produk'] = matched;
  }

  bool _readyForCalculate() {
    return '${_form['produk']}'.isNotEmpty &&
        '${_form['jenis_pensiun']}'.isNotEmpty &&
        '${_form['bank_tujuan']}'.isNotEmpty &&
        '${_form['tanggal_lahir']}'.isNotEmpty;
  }

  void _scheduleCalculate() {
    _debounce?.cancel();
    if (!_readyForCalculate()) return;
    _debounce = Timer(const Duration(milliseconds: 800), _calculate);
  }

  Map<String, dynamic> _requestPayload() {
    return {
      'produk': _form['produk'],
      'jenis_pensiun': _form['jenis_pensiun'],
      'mutasi': _form['mutasi'],
      'bank_asal': _form['bank_asal'],
      'bank_tujuan': _form['bank_tujuan'],
      'keterangan': _form['keterangan'],
      'nama_debitur': _form['nama_debitur'],
      'tanggal_simulasi': _form['tanggal_simulasi'],
      'tanggal_lahir': _form['tanggal_lahir'],
      'nomor_pensiun': _form['nomor_pensiun'],
      'instansi': _form['instansi'],
      'gaji_pensiun': _toNum(_form['gaji_pensiun']),
      'angsuran_lainnya': _toNum(_form['angsuran_lainnya']),
      'blokir_angsuran': _toInt(_form['blokir_angsuran']) ?? 1,
      'rate_percent_override': _toNullableNum(_form['rate_percent_override']),
      'admin_angsuran_percent_override': _toNullableNum(_form['admin_angsuran_percent_override']),
      'tenor': _toNullableInt(_form['tenor']),
      'plafond': _toNullableNum(_form['plafond']),
      'pelunasan': _toNum(_form['pelunasan']),
      'nama_marketing': _form['nama_marketing'],
      'kode_area': _form['kode_area'],
    };
  }

  Future<void> _calculate() async {
    if (!_readyForCalculate()) return;
    setState(() {
      _calculating = true;
      _error = '';
    });

    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/calculate'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(_requestPayload()),
      );

      final jsonMap = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode >= 400) {
        throw Exception('${jsonMap['message'] ?? 'Perhitungan gagal'}');
      }

      setState(() {
        _result = Map<String, dynamic>.from(jsonMap['data'] as Map<String, dynamic>? ?? {});
        _limits = jsonMap['limits'] as Map<String, dynamic>?;
      });
    } catch (e) {
      setState(() {
        _error = 'Gagal hitung: $e';
      });
    } finally {
      setState(() {
        _calculating = false;
      });
    }
  }

  Future<void> _save() async {
    if (_result.isEmpty) {
      setState(() {
        _error = 'Hitung dulu sebelum simpan';
      });
      return;
    }

    setState(() {
      _saving = true;
      _error = '';
      _message = '';
    });

    try {
      final payload = {..._requestPayload(), ..._result};
      final response = await http.post(
        Uri.parse('$_baseUrl/store'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode(payload),
      );
      final jsonMap = jsonDecode(response.body) as Map<String, dynamic>;
      if (response.statusCode >= 400) {
        throw Exception(jsonMap['message'] ?? 'Gagal simpan');
      }

      final savedId = jsonMap['id'];
      if (savedId != null) {
        _result['id'] = savedId;
      }

      setState(() {
        _message = 'Data berhasil disimpan ke database';
      });
    } catch (e) {
      setState(() {
        _error = 'Simpan gagal: $e';
      });
    } finally {
      setState(() {
        _saving = false;
      });
    }
  }

  Future<void> _downloadPdf() async {
    final id = _result['id'];
    if (id == null) {
      setState(() {
        _error = 'Simpan data dulu agar punya ID untuk PDF';
      });
      return;
    }

    setState(() {
      _downloading = true;
      _error = '';
      _message = '';
    });

    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/download-pdf'),
        headers: {'Content-Type': 'application/json'},
        body: jsonEncode({'id': id}),
      );
      if (response.statusCode >= 400) {
        throw Exception('Status ${response.statusCode}');
      }

      final dir = await getApplicationDocumentsDirectory();
      final filename = 'simulasi-kb-${DateTime.now().millisecondsSinceEpoch}.pdf';
      final file = File('${dir.path}/$filename');
      await file.writeAsBytes(response.bodyBytes, flush: true);

      await OpenFilex.open(file.path);
      setState(() {
        _message = 'PDF tersimpan: ${file.path}';
      });
    } catch (e) {
      setState(() {
        _error = 'Download PDF gagal: $e';
      });
    } finally {
      setState(() {
        _downloading = false;
      });
    }
  }

  String _displayValue(RowDef row) {
    final key = row.key;
    if (key == null) return '-';

    if (key == 'umur_text') {
      if (_result[key] != null && '${_result[key]}'.isNotEmpty) {
        return '${_result[key]}';
      }
      final birth = DateTime.tryParse('${_form['tanggal_lahir']}');
      final sim = DateTime.tryParse('${_form['tanggal_simulasi']}') ?? DateTime.now();
      if (birth == null) return '-';
      var years = sim.year - birth.year;
      var months = sim.month - birth.month;
      if (sim.day < birth.day) months--;
      if (months < 0) {
        years--;
        months += 12;
      }
      return '${years < 0 ? 0 : years} thn ${months < 0 ? 0 : months} bln';
    }

    if (key == 'sisa_gaji_saat_pengajuan') {
      final gaji = _toNum(_form['gaji_pensiun']);
      final lain = _toNum(_form['angsuran_lainnya']);
      return _idrFormat.format((gaji - lain).clamp(0, 1e15));
    }

    final dynamic value = _result[key];
    if (value == null || '$value'.isEmpty) return '-';

    if (row.format == 'currency') {
      return _idrFormat.format(_toNum(value));
    }
    if (row.format == 'months') {
      return '${_toInt(value) ?? 0} bulan';
    }
    return '$value';
  }

  double _toNum(dynamic value) {
    if (value == null) return 0;
    return double.tryParse('$value'.replaceAll(',', '.')) ?? 0;
  }

  double? _toNullableNum(dynamic value) {
    if (value == null || '$value'.trim().isEmpty) return null;
    return _toNum(value);
  }

  int? _toInt(dynamic value) {
    if (value == null) return null;
    return int.tryParse('$value');
  }

  int? _toNullableInt(dynamic value) {
    if (value == null || '$value'.trim().isEmpty) return null;
    return int.tryParse('$value');
  }

  bool _isInputDisabled(RowDef row) {
    if (row.key == 'produk') return true;
    if (row.onlyRoleCanEditPricing && !_canEditPricing) return true;
    return false;
  }

  Widget _buildInput(RowDef row) {
    final key = row.key!;
    switch (row.type) {
      case FieldType.select:
        final options = _getRowOptions(row);
        final current = '${_form[key] ?? ''}';
        final hasCurrent = options.contains(current);
        final selected = hasCurrent ? current : (row.allowEmpty ? '' : (options.isNotEmpty ? options.first : ''));
        return DropdownButtonFormField<String>(
          initialValue: selected.isEmpty ? null : selected,
          isExpanded: true,
          decoration: const InputDecoration(
            filled: true,
            fillColor: Color(0xFFFFF1DB),
            border: OutlineInputBorder(),
            isDense: true,
          ),
          items: [
            if (row.allowEmpty) const DropdownMenuItem(value: '', child: Text('Pilih')),
            ...options.map((e) => DropdownMenuItem(value: e, child: Text(e))),
          ],
          onChanged: _isInputDisabled(row)
              ? null
              : (value) => _onFieldChanged(key, value ?? ''),
        );
      case FieldType.date:
      case FieldType.text:
      case FieldType.number:
      case FieldType.integer:
        return TextFormField(
          initialValue: '${_form[key] ?? ''}',
          enabled: !_isInputDisabled(row),
          keyboardType: row.type == FieldType.text
              ? TextInputType.text
              : (row.type == FieldType.date
                  ? TextInputType.datetime
                  : const TextInputType.numberWithOptions(decimal: true)),
          decoration: const InputDecoration(
            filled: true,
            fillColor: Color(0xFFFFF1DB),
            border: OutlineInputBorder(),
            isDense: true,
            hintText: 'Isi nilai',
          ),
          onChanged: (value) => _onFieldChanged(key, value),
          onTap: row.type == FieldType.date
              ? () async {
                  final initial = DateTime.tryParse('${_form[key]}') ?? DateTime.now();
                  final picked = await showDatePicker(
                    context: context,
                    initialDate: initial,
                    firstDate: DateTime(1940),
                    lastDate: DateTime(2100),
                  );
                  if (picked != null) {
                    _onFieldChanged(key, _dateFormat.format(picked));
                  }
                }
              : null,
        );
      default:
        return const SizedBox.shrink();
    }
  }

  Widget _buildRow(RowDef row) {
    if (row.type == FieldType.section) {
      return Container(
        color: const Color(0xFFF2F5F9),
        padding: const EdgeInsets.all(10),
        child: Text(row.label, style: const TextStyle(fontWeight: FontWeight.w700)),
      );
    }

    if (row.type == FieldType.blank) {
      return const Divider(height: 1);
    }

    final isOutput = row.type == FieldType.output;
    return Container(
      decoration: const BoxDecoration(
        border: Border(bottom: BorderSide(color: Color(0xFFD8DEE4))),
      ),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Expanded(
            flex: 44,
            child: Container(
              padding: const EdgeInsets.all(10),
              color: const Color(0xFFFAFCFF),
              child: Text(row.label, style: const TextStyle(fontWeight: FontWeight.w600)),
            ),
          ),
          Expanded(
            flex: 56,
            child: Container(
              padding: const EdgeInsets.all(8),
              color: isOutput ? const Color(0xFFF7FBF7) : Colors.white,
              child: isOutput
                  ? Text(
                      _displayValue(row),
                      style: const TextStyle(fontWeight: FontWeight.w600),
                    )
                  : _buildInput(row),
            ),
          ),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final warning = _limits != null && _limits!['is_valid'] == false
        ? 'Data belum valid menurut rule backend. Anda tetap bisa simpan trial.'
        : '';

    return Scaffold(
      appBar: AppBar(
        title: const Text('Simulasi KB Android'),
        backgroundColor: const Color(0xFF184E77),
        foregroundColor: Colors.white,
        actions: [
          TextButton(
            onPressed: _loggingIn ? null : (_isLoggedIn ? _logout : _showLoginDialog),
            child: Text(
              _isLoggedIn ? 'Logout' : 'Login',
              style: const TextStyle(color: Colors.white, fontWeight: FontWeight.w600),
            ),
          ),
        ],
      ),
      body: SafeArea(
        child: RefreshIndicator(
          onRefresh: _loadConfig,
          child: ListView(
            padding: const EdgeInsets.all(12),
            children: [
              if (_isAdmin)
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        const Text('Server API', style: TextStyle(fontWeight: FontWeight.w700)),
                        const SizedBox(height: 8),
                        TextField(
                          controller: _baseUrlController,
                          decoration: const InputDecoration(
                            labelText: 'Base URL',
                            border: OutlineInputBorder(),
                            isDense: true,
                          ),
                        ),
                        const SizedBox(height: 8),
                        Row(
                          children: [
                            ElevatedButton(
                              onPressed: _saveBaseUrl,
                              child: const Text('Simpan Endpoint'),
                            ),
                            const SizedBox(width: 8),
                            ElevatedButton(
                              onPressed: _loadingConfig ? null : _loadConfig,
                              child: Text(_loadingConfig ? 'Memuat...' : 'Muat Ulang Config'),
                            ),
                            const SizedBox(width: 8),
                            if (_calculating)
                              const Text('Menghitung otomatis...', style: TextStyle(color: Colors.black54)),
                          ],
                        ),
                      ],
                    ),
                  ),
                )
              else
                Card(
                  child: Padding(
                    padding: const EdgeInsets.all(12),
                    child: Row(
                      children: [
                        const Icon(Icons.lock_outline, size: 18),
                        const SizedBox(width: 8),
                        Expanded(
                          child: Text(
                            _isLoggedIn
                                ? 'Endpoint disembunyikan untuk akun $_loggedInEmail'
                                : 'Endpoint disembunyikan. Login sebagai admin@nbp.com untuk melihat.',
                          ),
                        ),
                        if (_calculating)
                          const SizedBox(
                            height: 16,
                            width: 16,
                            child: CircularProgressIndicator(strokeWidth: 2),
                          ),
                      ],
                    ),
                  ),
                ),
              if (_message.isNotEmpty)
                Container(
                  margin: const EdgeInsets.only(top: 8),
                  padding: const EdgeInsets.all(10),
                  color: const Color(0xFFE9F5FF),
                  child: Text(_message),
                ),
              if (_error.isNotEmpty)
                Container(
                  margin: const EdgeInsets.only(top: 8),
                  padding: const EdgeInsets.all(10),
                  color: const Color(0xFFFFEAEA),
                  child: Text(_error, style: const TextStyle(color: Colors.red)),
                ),
              if (warning.isNotEmpty)
                Container(
                  margin: const EdgeInsets.only(top: 8),
                  padding: const EdgeInsets.all(10),
                  color: const Color(0xFFFFF4E5),
                  child: Text(warning),
                ),
              const SizedBox(height: 12),
              Card(
                child: Container(
                  decoration: BoxDecoration(
                    border: Border.all(color: const Color(0xFFD0D7DE)),
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: Column(children: _rows.map(_buildRow).toList()),
                ),
              ),
              const SizedBox(height: 12),
              Card(
                child: Padding(
                  padding: const EdgeInsets.all(12),
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      const Text('Keterangan Trial', style: TextStyle(fontWeight: FontWeight.w700)),
                      const SizedBox(height: 8),
                      TextFormField(
                        initialValue: '${_form['keterangan']}',
                        maxLines: 3,
                        decoration: const InputDecoration(
                          hintText: 'Contoh: debitur minta sisa gaji akhir minimal 250rb',
                          border: OutlineInputBorder(),
                        ),
                        onChanged: (v) => _onFieldChanged('keterangan', v),
                      ),
                      const SizedBox(height: 12),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: [
                          ElevatedButton(
                            onPressed: _calculating ? null : _calculate,
                            child: Text(_calculating ? 'Menghitung...' : 'Hitung Ulang'),
                          ),
                          ElevatedButton(
                            onPressed: _saving ? null : _save,
                            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF1D4E89)),
                            child: Text(_saving ? 'Menyimpan...' : 'Simpan ke Database'),
                          ),
                          OutlinedButton(
                            onPressed: _downloading ? null : _downloadPdf,
                            child: Text(_downloading ? 'Download...' : 'Download PDF'),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
