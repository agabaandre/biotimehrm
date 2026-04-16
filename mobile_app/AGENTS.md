# AGENTS.md — HRM Attend

## Project Overview

HRM Attend is an Android application for biometric attendance management in Uganda's health sector. It uses facial recognition (OpenCV + ONNX models) and fingerprint scanning (serial port hardware) to clock staff in and out. Data syncs to a remote iHRIS backend via REST APIs.

- Package: `ug.go.health.ihrisbiometric`
- Language: Java (primary), Kotlin stdlib included
- Min SDK: 26 | Target SDK: 34
- Build: Gradle 8.7, AGP 8.5.1

## Repository Structure

```
app/src/main/java/ug/go/health/ihrisbiometric/
├── activities/       # Android Activities (Splash, Login, Home, Settings, DeviceSetup, OutOfStation, AboutProject)
├── adapters/         # RecyclerView/List adapters
├── converters/       # Room TypeConverters (ByteArray, FloatArray, Date, Location)
├── dao/              # Room DAO interfaces (StaffRecordDao, ClockHistoryDao)
├── database/         # Room database (AppDatabase) and Converters
├── fragments/        # UI Fragments (Camera, ClockHistory, DataSync, Enroll, Home, Notifications)
├── helpers/          # DatabaseHelper utility
├── models/           # Data models / Room entities (StaffRecord, ClockHistory, etc.)
├── services/         # API layer (Retrofit), DbService, FaceScanner, SessionService
├── utils/            # Constants, BitmapUtils, ImageConverter, UI helpers
├── viewmodels/       # MVVM ViewModels (HomeViewModel, DataSyncViewModel)
├── DeviceType.java   # Enum for device types (CAMERA, SCANNER)
├── HRMAttend.java    # Application class
├── JniHelper.java    # JNI bridge for native face recognition
├── ScannerEventListener.java
└── StatusHandler.java

app/src/main/java/android_serialport_api/   # Serial port communication for fingerprint scanners
app/src/main/assets/                         # ONNX model files for face detection/recognition
app/src/main/res/                            # Layouts, drawables, navigation, values, menus
```

## Architecture

- MVVM pattern: Activities/Fragments → ViewModels → Services/DAOs
- Local database: Room (SQLite) with `AppDatabase`, entities `StaffRecord` and `ClockHistory`
- Networking: Retrofit 2 + OkHttp with token-based auth (Bearer JWT), response caching
- Session management: `SessionService` wrapping SharedPreferences (stores user, facility, device settings)
- Face recognition pipeline: CameraX → ML Kit face detection → OpenCV/ONNX embedding → JNI matching
- Fingerprint pipeline: Serial port scanner → `android_serialport_api` → template capture/match
- Error tracking: Sentry SDK
- Background work: AndroidX WorkManager

## Build & Run

### Prerequisites

- JDK 8+ (Java 1.8 source/target compatibility)
- Android SDK with API 34 (compileSdk)
- OpenCV Android SDK installed at `C:/tools/opencv/build/android/OpenCV-android-sdk/sdk` (or update `settings.gradle` to your local path)
- Android Studio (recommended) or Gradle CLI

### Build Commands

```bash
# Debug build
./gradlew assembleDebug

# Release build
./gradlew assembleRelease

# Run unit tests
./gradlew test

# Run instrumented tests (requires connected device/emulator)
./gradlew connectedAndroidTest

# Clean build
./gradlew clean
```

### OpenCV Dependency

The `opencv` module is referenced in `settings.gradle` with a hardcoded local path. If building on a different machine, update:

```groovy
// settings.gradle
project(':opencv').projectDir = new File('<YOUR_OPENCV_SDK_PATH>/sdk')
```

## Key Dependencies

| Library | Purpose |
|---|---|
| Retrofit 2.11 + OkHttp 4.12 | REST API communication |
| Room 2.6.1 | Local SQLite database |
| CameraX 1.3.4 | Camera preview and capture |
| ML Kit Face Detection 16.1.7 | Face detection in camera frames |
| ONNX Runtime (native .so) | Face embedding inference |
| OpenCV (module) | Image processing |
| Gson 2.11 | JSON serialization |
| RxJava 2 | Reactive streams |
| AndroidX Navigation 2.8 | Fragment navigation |
| Sentry 7.14 | Error/crash reporting |
| auth0 java-jwt 4.4 | JWT token handling |
| CH34xUARTDriver.jar | USB-to-serial for fingerprint scanners |

## Database Schema

Two Room entities, database version 1 with `fallbackToDestructiveMigration()`:

- `StaffRecord` — staff info, face_data (float[]), fingerprint_data (byte[]), enrollment flags, ihris_pid, facility_id
- `ClockHistory` — attendance records with timestamps, sync status, facility_id, location

TypeConverters: `ByteArrayConverter` (byte[] ↔ Base64), `FloatArrayConverter` (float[] ↔ CSV string), `DateConverter`, `LocationConverter`

## API Layer

- Base URL: configured per-device via `DeviceSettings.getServerUrl()` (stored in SharedPreferences)
- Auth: Bearer token from `SessionService.getToken()`
- Endpoints defined in `ApiInterface.java`:
  - `POST /login` — authentication
  - `GET /staff_list` — fetch staff records (supports `?facility_name=` query)
  - `GET /staff_details/{id}` — single staff record
  - `GET /facilities` — facility list
  - `POST /enroll_user` — sync staff record to server
  - `POST /clock_user` — sync clock history to server
  - `GET /notifications_list` — notifications
  - `POST /request` — out-of-station request (multipart)

## Coding Conventions

- Java for all application code (Kotlin stdlib is a dependency but code is Java)
- Android resource naming: `activity_*.xml`, `fragment_*.xml`, `item_*.xml`
- Package-by-feature under `ug.go.health.ihrisbiometric`
- Room entities use `@Entity` annotations with explicit column names
- ViewModels extend `AndroidViewModel` for Application context access
- API calls use Retrofit `Call<T>` with `enqueue()` (async callbacks), not RxJava for most endpoints
- SharedPreferences for session/config, Room for domain data
- No dependency injection framework — manual instantiation via singletons and constructors

## Rules for AI Agents

1. Do NOT modify or delete ONNX model files in `app/src/main/assets/` or native `.so` libraries in `app/libs/`
2. Do NOT change the `android_serialport_api` package — it is a stable vendor library
3. When adding Room entity fields, increment the database version in `AppDatabase` or ensure `fallbackToDestructiveMigration()` is acceptable for the change
4. Keep Java 1.8 source compatibility — no Java 11+ features
5. All new API endpoints must be added to `ApiInterface.java` following the existing Retrofit annotation pattern
6. Preserve the existing MVVM layering: UI logic in Fragments/Activities, business logic in ViewModels, data access in DAOs/Services
7. The OpenCV module path in `settings.gradle` is machine-specific — do not hardcode a different path without noting it
8. Sentry auth token and DSN are in `app/build.gradle` and `AndroidManifest.xml` — do not remove or alter these unless explicitly asked
9. When working with biometric data (face embeddings, fingerprint templates), ensure serialization round-trip integrity through the existing TypeConverters
10. Check `.kiro/specs/` for active feature specifications before making changes to sync, biometric, or facility-related code

## Active Feature Work

Specs in `.kiro/specs/` describe in-progress features:

- `fingerprint-sync` — uploading/downloading fingerprint templates across devices
- `sync-ui-cleanup` — simplifying the DataSync screen UI
- `facility-switch-data-portability` — preserving data across facility switches with full biometric download
- `face-embedding-sync` — syncing face embeddings across devices

Consult these specs before modifying related code to stay aligned with the design.

## CI/CD

GitHub Actions workflows in `.github/workflows/`:

- `build.yml` — runs on push/PR to main, master, develop. Two parallel jobs: `build` (assembleDebug + upload APK artifact) and `unit-test` (runs `./gradlew test` + uploads report). Downloads and caches OpenCV Android SDK automatically.
- `release.yml` — runs on `v*` tags. Builds release APK, uploads artifact, and creates a GitHub Release with the APK attached.

Required GitHub secret: `SENTRY_AUTH_TOKEN` — the Sentry auth token for source upload during builds. Set this in repo Settings → Secrets and variables → Actions.

## Testing

- Unit tests: JUnit 4 (`app/src/test/`)
- Instrumented tests: AndroidX Test + Espresso (`app/src/androidTest/`)
- Run tests: `./gradlew test` (unit), `./gradlew connectedAndroidTest` (instrumented)
- Property-based testing with jqwik is planned for sync UI correctness properties (see `sync-ui-cleanup` spec)
