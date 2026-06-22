# MSEA Mobile Flutter

Cliente visual Flutter para consumir el backend Laravel existente.

## Backend

La app usa estos endpoints:

- `POST /api/mobile/login`
- `GET /api/mobile/me`
- `POST /api/mobile/practice`
- `POST /api/mobile/logout`

Por defecto apunta a `http://127.0.0.1:8000`.

En emulador Android usa `http://10.0.2.2:8000`.

## Puesta en marcha

Cuando Flutter este instalado:

```bash
cd mobile_flutter
flutter create .
flutter pub get
flutter run --dart-define=MSEA_API_URL=http://10.0.2.2:8000
```

Para Windows desktop o navegador local:

```bash
flutter run --dart-define=MSEA_API_URL=http://127.0.0.1:8000
```
