import 'package:flutter/material.dart';
import 'locale_service.dart';

/// Screen ya kuchagua lugha. Ina-trigger onLanguageChanged ili refetch data.
class LanguageSwitcherScreen extends StatelessWidget {
  final VoidCallback? onLanguageChanged;

  const LanguageSwitcherScreen({super.key, this.onLanguageChanged});

  @override
  Widget build(BuildContext context) {
    final localeService = LocaleService();

    return ValueListenableBuilder<String>(
      valueListenable: localeService.languageNotifier,
      builder: (context, languageCode, _) {
        return Scaffold(
          appBar: AppBar(
            title: const Text('Language / Lugha'),
          ),
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
        SnackBar(
          content: Text(
            code == LocaleService.en
                ? 'Language set to English'
                : 'Lugha imewekwa Kiswahili',
          ),
        ),
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
      trailing: isSelected
          ? const Icon(Icons.check_circle, color: Colors.green)
          : null,
      selected: isSelected,
      onTap: onTap,
      shape: RoundedRectangleBorder(
        borderRadius: BorderRadius.circular(12),
      ),
      tileColor: isSelected ? Colors.green.shade50 : null,
    );
  }
}
