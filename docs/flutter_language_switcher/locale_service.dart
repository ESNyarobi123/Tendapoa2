import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

/// Hifadhi lugha (en/sw) na taarifa wakati inapobadilika.
/// Tumia kwenye Dio headers na kwa refresh UI.
class LocaleService {
  static const _keyLanguage = 'app_language_code';
  static const String en = 'en';
  static const String sw = 'sw';

  final ValueNotifier<String> languageNotifier = ValueNotifier<String>(en);

  static final LocaleService _instance = LocaleService._();
  factory LocaleService() => _instance;
  LocaleService._();

  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    final saved = prefs.getString(_keyLanguage) ?? en;
    languageNotifier.value = saved;
  }

  String get currentLanguage => languageNotifier.value;

  Future<void> setLanguage(String code) async {
    if (code != en && code != sw) return;
    languageNotifier.value = code;
    final prefs = await SharedPreferences.getInstance();
    await prefs.setString(_keyLanguage, code);
  }

  Future<void> toggleLanguage() async {
    final next = currentLanguage == en ? sw : en;
    await setLanguage(next);
  }
}
