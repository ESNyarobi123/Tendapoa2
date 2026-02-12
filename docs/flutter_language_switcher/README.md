# Flutter Language Switcher – Copy into your app

Faili hizi zina implement kamili ya:
- **LocaleService** – hifadhi lugha (SharedPreferences) + notifier
- **Dio client** – `Accept-Language` header kutoka lugha ya sasa
- **Language Switcher screen** – UI + callback ya refresh

## Dependencies (pubspec.yaml)

```yaml
dependencies:
  dio: ^5.4.0
  shared_preferences: ^2.2.2
```

## Kuingiza kwenye Flutter project

1. Copy `locale_service.dart`, `api_client.dart`, `language_switcher_screen.dart` kwenye lib/ (k.m. `lib/services/`, `lib/screens/`).
2. **App start:** wito `LocaleService().init()` (k.m. kwenye `main()` au `MaterialApp` builder).
3. **API:** tumia `createApiClient()` kwa requests zote (feed, jobs, profile).
4. **Settings:** onyesha `LanguageSwitcherScreen` na `onLanguageChanged`:
   - refetch feed, my jobs, profile (na setState au state management yako).

## Mfano: kuunganisha na refresh

```dart
// Kwenye Settings au Drawer
Navigator.push(
  context,
  MaterialPageRoute(
    builder: (context) => LanguageSwitcherScreen(
      onLanguageChanged: () {
        refetchFeed();
        refetchMyJobs();
        refetchProfile();
        setState(() {});
      },
    ),
  ),
);
```

## Backend

Backend inarudisha `title`, `description`, na labels kulingana na **Accept-Language** (en/sw). Hakikisha Dio inabeba header hiyo – iko tayari kwenye `api_client.dart`.
