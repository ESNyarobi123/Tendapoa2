import 'package:dio/dio.dart';
import 'locale_service.dart';

/// Dio client yenye Accept-Language kutoka LocaleService.
/// Kila request inabeba lugha ya sasa (en/sw).
Dio createApiClient() {
  final localeService = LocaleService();
  final dio = Dio(BaseOptions(
    baseUrl: 'https://tendapoa.com/api',
    connectTimeout: const Duration(seconds: 15),
    receiveTimeout: const Duration(seconds: 15),
    headers: {
      'Accept': 'application/json',
      'Accept-Language': localeService.currentLanguage,
    },
  ));

  dio.interceptors.add(InterceptorsWrapper(
    onRequest: (options, handler) {
      options.headers['Accept-Language'] = LocaleService().currentLanguage;
      return handler.next(options);
    },
  ));

  return dio;
}
