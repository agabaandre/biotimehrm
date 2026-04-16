#!/usr/bin/env bash
# setup.sh — HRM Attend project setup for AI agents and developers
# Downloads dependencies, configures OpenCV, and verifies the build environment.

set -euo pipefail

OPENCV_VERSION="4.9.0"
OPENCV_DEFAULT_DIR="opencv-sdk"
REQUIRED_JAVA_VERSION="1.8"
ANDROID_COMPILE_SDK=34

# ─── Colors ───────────────────────────────────────────────────────────────────
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

info()  { echo -e "${GREEN}[INFO]${NC} $*"; }
warn()  { echo -e "${YELLOW}[WARN]${NC} $*"; }
error() { echo -e "${RED}[ERROR]${NC} $*"; }

# ─── Check Java ───────────────────────────────────────────────────────────────
check_java() {
    info "Checking Java installation..."
    if ! command -v java &>/dev/null; then
        error "Java not found. Install JDK 8+ and ensure 'java' is on PATH."
        exit 1
    fi
    java_version=$(java -version 2>&1 | head -1 | awk -F '"' '{print $2}')
    info "Found Java: $java_version"
}

# ─── Check ANDROID_HOME / ANDROID_SDK_ROOT ────────────────────────────────────
check_android_sdk() {
    info "Checking Android SDK..."
    local sdk_dir="${ANDROID_HOME:-${ANDROID_SDK_ROOT:-}}"
    if [ -z "$sdk_dir" ]; then
        # Try common default locations
        if [ -d "$HOME/Android/Sdk" ]; then
            sdk_dir="$HOME/Android/Sdk"
        elif [ -d "$HOME/Library/Android/sdk" ]; then
            sdk_dir="$HOME/Library/Android/sdk"
        elif [ -d "/c/Users/$USERNAME/AppData/Local/Android/Sdk" ]; then
            sdk_dir="/c/Users/$USERNAME/AppData/Local/Android/Sdk"
        fi
    fi

    if [ -z "$sdk_dir" ] || [ ! -d "$sdk_dir" ]; then
        warn "Android SDK not found. Set ANDROID_HOME or ANDROID_SDK_ROOT."
        warn "Build will rely on local.properties or Android Studio defaults."
        return
    fi

    info "Android SDK found at: $sdk_dir"

    # Write local.properties if missing
    if [ ! -f "local.properties" ]; then
        # Normalize path for Gradle (forward slashes)
        local normalized
        normalized=$(echo "$sdk_dir" | sed 's|\\|/|g')
        echo "sdk.dir=$normalized" > local.properties
        info "Created local.properties with sdk.dir=$normalized"
    else
        info "local.properties already exists, skipping."
    fi
}

# ─── Download & configure OpenCV Android SDK ──────────────────────────────────
setup_opencv() {
    info "Checking OpenCV Android SDK..."

    # Check if settings.gradle already points to a valid OpenCV path
    local current_path
    current_path=$(grep "project(':opencv').projectDir" settings.gradle \
        | sed "s/.*new File('\(.*\)').*/\1/" 2>/dev/null || true)

    if [ -n "$current_path" ] && [ -d "$current_path" ]; then
        info "OpenCV SDK already configured at: $current_path"
        return
    fi

    # Check if OPENCV_SDK_PATH env var is set
    if [ -n "${OPENCV_SDK_PATH:-}" ] && [ -d "$OPENCV_SDK_PATH/sdk" ]; then
        info "Using OpenCV SDK from OPENCV_SDK_PATH: $OPENCV_SDK_PATH"
        update_opencv_path "$OPENCV_SDK_PATH/sdk"
        return
    fi

    # Check common local locations
    local search_paths=(
        "$OPENCV_DEFAULT_DIR/OpenCV-android-sdk/sdk"
        "C:/tools/opencv/build/android/OpenCV-android-sdk/sdk"
        "$HOME/opencv/OpenCV-android-sdk/sdk"
        "/opt/opencv/OpenCV-android-sdk/sdk"
    )
    for p in "${search_paths[@]}"; do
        if [ -d "$p" ]; then
            info "Found OpenCV SDK at: $p"
            update_opencv_path "$p"
            return
        fi
    done

    # Download OpenCV Android SDK
    info "Downloading OpenCV Android SDK $OPENCV_VERSION..."
    local zip_file="opencv-${OPENCV_VERSION}-android-sdk.zip"
    local download_url="https://github.com/opencv/opencv/releases/download/${OPENCV_VERSION}/${zip_file}"

    if command -v curl &>/dev/null; then
        curl -L -o "$zip_file" "$download_url"
    elif command -v wget &>/dev/null; then
        wget -O "$zip_file" "$download_url"
    else
        error "Neither curl nor wget found. Install one or manually download OpenCV Android SDK."
        error "URL: $download_url"
        error "Then set OPENCV_SDK_PATH=<extracted_dir> and re-run this script."
        exit 1
    fi

    info "Extracting OpenCV SDK..."
    mkdir -p "$OPENCV_DEFAULT_DIR"
    if command -v unzip &>/dev/null; then
        unzip -q "$zip_file" -d "$OPENCV_DEFAULT_DIR"
    else
        error "'unzip' not found. Extract $zip_file manually into $OPENCV_DEFAULT_DIR/"
        exit 1
    fi
    rm -f "$zip_file"

    local sdk_path="$OPENCV_DEFAULT_DIR/OpenCV-android-sdk/sdk"
    if [ ! -d "$sdk_path" ]; then
        error "Expected OpenCV SDK directory not found after extraction: $sdk_path"
        exit 1
    fi

    update_opencv_path "$sdk_path"
    info "OpenCV SDK downloaded and configured."
}

update_opencv_path() {
    local new_path="$1"
    # Normalize to forward slashes and make absolute
    new_path=$(cd "$new_path" 2>/dev/null && pwd || echo "$new_path")
    new_path=$(echo "$new_path" | sed 's|\\|/|g')

    # Update settings.gradle
    sed -i.bak "s|project(':opencv').projectDir = new File('.*')|project(':opencv').projectDir = new File('$new_path')|" settings.gradle
    rm -f settings.gradle.bak
    info "Updated settings.gradle → opencv path: $new_path"
}

# ─── Make gradlew executable ──────────────────────────────────────────────────
setup_gradlew() {
    if [ -f "gradlew" ]; then
        chmod +x gradlew
        info "gradlew is executable."
    else
        error "gradlew not found in project root."
        exit 1
    fi
}

# ─── Gradle sync / dependency download ────────────────────────────────────────
gradle_sync() {
    info "Running Gradle dependency resolution (this may take a few minutes)..."
    ./gradlew dependencies --quiet 2>&1 || {
        warn "Gradle dependency resolution had issues. Try opening in Android Studio for full sync."
    }
    info "Gradle dependencies resolved."
}

# ─── Build verification ──────────────────────────────────────────────────────
verify_build() {
    info "Running debug build to verify setup..."
    if ./gradlew assembleDebug --quiet 2>&1; then
        info "Debug build succeeded. Project is ready."
    else
        error "Debug build failed. Check the output above for errors."
        error "Common fixes:"
        error "  - Ensure Android SDK API $ANDROID_COMPILE_SDK is installed"
        error "  - Verify OpenCV SDK path in settings.gradle"
        error "  - Run './gradlew assembleDebug' manually for full output"
        exit 1
    fi
}

# ─── Main ─────────────────────────────────────────────────────────────────────
main() {
    info "=== HRM Attend Project Setup ==="
    echo ""

    check_java
    check_android_sdk
    setup_gradlew
    setup_opencv
    gradle_sync

    echo ""
    read -rp "Run a verification build? (y/N): " run_build
    if [[ "$run_build" =~ ^[Yy]$ ]]; then
        verify_build
    else
        info "Skipping verification build. Run './gradlew assembleDebug' when ready."
    fi

    echo ""
    info "=== Setup complete ==="
    info "Quick commands:"
    info "  ./gradlew assembleDebug        # Debug APK"
    info "  ./gradlew assembleRelease      # Release APK"
    info "  ./gradlew test                 # Unit tests"
    info "  ./gradlew connectedAndroidTest # Instrumented tests"
}

main "$@"
