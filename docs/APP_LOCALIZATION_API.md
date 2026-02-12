# Mkataba wa Lugha: Mobile App ↔ Backend API

Backend inarudisha **title**, **description**, na **labels** kulingana na lugha. Ili App ipate maudhui kwa lugha sahihi, inabidi ifuate mkataba huu.

---

## 1. Global Header (lazima)

Kila API call (GET/POST n.k.) inayopata maudhui ya kazi/labels inapaswa kuwa na header ya lugha. Tumia **Interceptor** au **BaseOptions** ili kila request ipeleke lugha iliyochaguliwa na mtumiaji.

| Header             | Thamani      | Maelezo                    |
|--------------------|-------------|----------------------------|
| `Accept-Language`  | `sw` au `en` | Lugha inayotakiwa kwenye response |
| `Accept`           | `application/json` | (kawaida tayari)        |

### Flutter (Dio) – BaseOptions

```dart
// Variable ya lugha (utaiseti kutoka Settings / SharedPreferences)
final String currentLanguageCode = 'en'; // au 'sw'

final dio = Dio(BaseOptions(
  baseUrl: 'https://tendapoa.com/api',
  headers: {
    'Accept-Language': currentLanguageCode, // en au sw
    'Accept': 'application/json',
  },
));
```

Wakati mtumiaji akibadilisha lugha, **sasisha** `dio.options.headers['Accept-Language']` kwa thamani mpya (au ujenga Dio upya na BaseOptions mpya), kisha **piga tena** fetch za data.

### JavaScript (axios)

```javascript
axios.defaults.headers.common['Accept-Language'] = currentLanguageCode; // 'sw' | 'en'
```

### Swift (URLRequest)

```swift
request.setValue("sw", forHTTPHeaderField: "Accept-Language")
```

- Ikiwa **hamweki** header: backend inatumia **fallback** (kwa mfano `en`).
- **Accept-Language: sw** → `title`, `description`, labels (umbali, status) kwa **Kiswahili**.
- **Accept-Language: en** → kwa **English**.

---

## 2. State Management ya Lugha (Change Language)

Mtumiaji anapobadilisha lugha kutoka Kiswahili kwenda Kiingereza (au kinyume) kwenye **Settings**:

1. **Hifadhi** — tunza `languageCode` kwenye **SharedPreferences** (au storage yoyote).
2. **Update Header** — badilisha header ya `Accept-Language` kwenye API client (Dio/axios) kwa thamani mpya.
3. **Refresh UI** — piga tena (fetch) data za home screen, feed, profile ili `title` na `description` zije kwa lugha mpya.

**Mfano (Flutter):** baada ya kubadilisha lugha, weka `SharedPreferences` → sasisha `dio.options.headers['Accept-Language']` → piga `fetchJobs()`, `fetchProfile()` (au `refetch` / `setState`) ili UI ionyeshe maudhui kwa Kiingereza/Kiswahili.

Bila refresh, data iliyokwisha load itabaki kwa lugha ya zamani.

---

## 3. "The Magic" – JSON nyepesi, hakuna if/else

API **hairudishi** `title_sw` wala `title_en`. Inarudisha **key moja**: `title` (na `description`). Thamani yake inabadilika kulingana na **Accept-Language**:

| App inaomba (header) | JSON inayorudishwa (mfano)        |
|-----------------------|------------------------------------|
| `Accept-Language: en` | `"title": "Washing clothes"`       |
| `Accept-Language: sw` | `"title": "Kufua nguo"`            |

Hii inafanya App yako **nyepesi** (lighter JSON) na **huna haja ya kuandika if/else** nyingi kwenye code — unatumia `title` na `description` moja kwa moja.

---

## 4. Endpoints zinazotumia lugha

Kila endpoint inayorudisha **job** au **category** au **labels** (umbali, status, n.k.) inachagua lugha kutoka **Accept-Language**:

- `GET /api/feed` — jobs (title, description, distance labels)
- `GET /api/jobs/{id}` — job details
- `GET /api/jobs/my` — kazi zangu
- `GET /api/dashboard` — data ya dashboard (jobs, labels)
- `GET /api/categories` — majina ya categories (name)
- Na endpoints zingine zinazorudisha jobs/categories/labels

Hakikisha **kila request** kwa endpoints hizi inabeba **Accept-Language** na kwamba **refresh** inafanywa baada ya kubadilisha lugha.

---

## 5. Muhtasari kwa App

| Kitu                         | Utabiri wa App                                      |
|-----------------------------|------------------------------------------------------|
| **Global header**           | `Accept-Language: sw` au `Accept-Language: en` kwa API calls |
| **Change Language**        | Hifadhi lugha → set header → **refresh** data (feed, profile, n.k.) |
| **Response**                | `title`, `description`, `name` (category), na labels — zote kwa lugha iliyochaguliwa |

Ikiwa App inafanya hivi viwili (header + refresh), flow ya lugha kwenye app na backend inakaa sawa.

---

## 6. Flutter: Language Switcher (snippet kamili)

Snippet hii ina-trigger **hifadhi → update header → refresh**: mtumiaji akibadilisha lugha, API inapigwa tena na UI inasasishwa kwa lugha mpya.

### Dependencies (pubspec.yaml)

```yaml
dependencies:
  dio: ^5.4.0
  shared_preferences: ^2.2.2
  provider: ^6.1.1   # optional – unaweza kutumia setState / Riverpod n.k.
```

### 1) Locale service (hifadhi + taarifa wakati lugha inapobadilika)

```dart
import 'package:flutter/foundation.dart';
import 'package:shared_preferences/shared_preferences.dart';

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
```

### 2) Dio client (header inasasishwa kutoka LocaleService)

```dart
import 'package:dio/dio.dart';
import 'locale_service.dart';  // path yako

Dio createDio() {
  final localeService = LocaleService();
  final dio = Dio(BaseOptions(
    baseUrl: 'https://tendapoa.com/api',
    headers: {
      'Accept': 'application/json',
      'Accept-Language': localeService.currentLanguage,
    },
  ));

  // Optional: kila request iwe na lugha ya sasa (ikiwa umebadilisha bila restart)
  dio.interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) {
      options.headers['Accept-Language'] = LocaleService().currentLanguage;
      return handler.next(options);
    },
  ));

  return dio;
}
```

### 3) Language Switcher UI (Settings / Drawer)

```dart
import 'package:flutter/material.dart';
import 'locale_service.dart';
import 'api_client.dart';  // place yako ya createDio / refetch

class LanguageSwitcherScreen extends StatelessWidget {
  final VoidCallback? onLanguageChanged;  // callback ya refresh (feed, home, profile)

  const LanguageSwitcherScreen({super.key, this.onLanguageChanged});

  @override
  Widget build(BuildContext context) {
    final localeService = LocaleService();

    return ValueListenableBuilder<String>(
      valueListenable: localeService.languageNotifier,
      builder: (context, languageCode, _) {
        return Scaffold(
          appBar: AppBar(title: const Text('Language / Lugha')),
          body: Padding(
            padding: const EdgeInsets.all(24.0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                const Text(
                  'Chagua lugha / Choose language',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.w600),
                ),
                const SizedBox(height: 24),
                _LanguageTile(
                  code: LocaleService.en,
                  label: 'English',
                  isSelected: languageCode == LocaleService.en,
                  onTap: () => _changeLanguage(context, LocaleService.en, localeService),
                ),
                const SizedBox(height: 12),
                _LanguageTile(
                  code: LocaleService.sw,
                  label: 'Kiswahili',
                  isSelected: languageCode == LocaleService.sw,
                  onTap: () => _changeLanguage(context, LocaleService.sw, localeService),
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  Future<void> _changeLanguage(
    BuildContext context,
    String code,
    LocaleService localeService,
  ) async {
    if (localeService.currentLanguage == code) return;
    await localeService.setLanguage(code);
    onLanguageChanged?.call();
    if (context.mounted) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(code == 'en' ? 'Language set to English' : 'Lugha imewekwa Kiswahili')),
      );
    }
  }
}

class _LanguageTile extends StatelessWidget {
  final String code;
  final String label;
  final bool isSelected;
  final VoidCallback onTap;

  const _LanguageTile({
    required this.code,
    required this.label,
    required this.isSelected,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return ListTile(
      title: Text(label),
      trailing: isSelected ? const Icon(Icons.check_circle, color: Colors.green) : null,
      selected: isSelected,
      onTap: onTap,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      tileColor: isSelected ? Colors.green.shade50 : null,
    );
  }
}
```

### 4) Kuunganisha na refresh (Home / Feed)

Wakati wa kuseti **Settings** au **Language** screen, toa callback ili baada ya kubadilisha lugha App ipige tena API na kusasisha UI:

```dart
// Mfano: kutoka Home au main navigator
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => LanguageSwitcherScreen(
      onLanguageChanged: () {
        // Trigger refresh: feed, my jobs, profile, n.k.
        refetchFeed();
        refetchMyJobs();
        refetchProfile();
        setState(() {});  // au Provider/Riverpod notify
      },
    ),
  ),
);
```

**Muhtasari:** Switcher → `LocaleService.setLanguage(code)` → SharedPreferences + notifier → `onLanguageChanged()` → refetch data → API inarudisha JSON kwa lugha mpya (Accept-Language tayari iko kwenye Dio interceptor).
