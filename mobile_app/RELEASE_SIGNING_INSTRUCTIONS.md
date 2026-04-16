# Release Signing Instructions

To enable signed release builds in GitHub Actions, you need to follow these steps:

## 1. Generate a Keystore (if you don't have one)

If you already have a release keystore, skip to step 2.

```bash
keytool -genkey -v -keystore release.jks -keyalg RSA -keysize 2048 -validity 10000 -alias my-key-alias
```

## 2. Encode the Keystore to Base64

Run the following command to get the base64 string of your keystore:

```bash
base64 -w 0 release.jks
```

## 3. Add GitHub Secrets

Go to your GitHub repository settings: **Settings > Secrets and variables > Actions**.

Add the following **Repository Secrets**:

| Secret Name | Description |
|---|---|
| `RELEASE_KEYSTORE_BASE64` | The base64 string from step 2 |
| `RELEASE_KEYSTORE_PASSWORD` | The password for the keystore |
| `RELEASE_KEY_ALIAS` | The alias you used (e.g., `my-key-alias`) |
| `RELEASE_KEY_PASSWORD` | The password for the key |

## 4. How it works

The `Auto Release` workflow will:
1. Decode the `RELEASE_KEYSTORE_BASE64` secret into `app/release.jks`.
2. Use the other secrets to sign the release APK during the Gradle build.
3. If these secrets are missing, the workflow will still run but will produce an **unsigned** release APK.
