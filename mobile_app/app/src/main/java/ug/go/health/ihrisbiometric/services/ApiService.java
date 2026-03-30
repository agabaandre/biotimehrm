package ug.go.health.ihrisbiometric.services;

import android.content.Context;
import android.net.ConnectivityManager;
import android.net.NetworkCapabilities;
import android.util.Log;

import java.io.File;
import java.security.SecureRandom;
import java.security.cert.X509Certificate;
import java.util.concurrent.TimeUnit;

import javax.net.ssl.SSLContext;
import javax.net.ssl.SSLSocketFactory;
import javax.net.ssl.TrustManager;
import javax.net.ssl.X509TrustManager;

import okhttp3.Cache;
import okhttp3.CacheControl;
import okhttp3.Interceptor;
import okhttp3.MultipartBody;
import okhttp3.OkHttpClient;
import okhttp3.Request;
import okhttp3.Response;
import okhttp3.logging.HttpLoggingInterceptor;
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;
import ug.go.health.ihrisbiometric.models.DeviceSettings;

public class ApiService {

    private static final String TAG = ApiService.class.getSimpleName();
    private static final int CACHE_SIZE = 10 * 1024 * 1024; // 10 MB
    private static final int MAX_AGE = 60; // 1 minute
    private static final int MAX_STALE = 7 * 24 * 60 * 60; // 7 days

    private static OkHttpClient okHttpClient;
    private static TokenInterceptor tokenInterceptor;

    private ApiService() {
        // Private constructor to prevent instantiation
    }

    public static ApiInterface getApiInterface(Context context) {
        // Always recreate Retrofit instance with updated URL
        okHttpClient = buildOkHttpClient(context);
        String baseUrl = getBaseUrl(context);

        SessionService sessionService = new SessionService(context);
        String token = sessionService.getToken();
        if (token != null && !token.isEmpty()) {
            setToken(token);
        }

        Retrofit retrofit = new Retrofit.Builder()
                .baseUrl(baseUrl)
                .addConverterFactory(GsonConverterFactory.create())
                .client(okHttpClient)
                .build();

        return retrofit.create(ApiInterface.class);
    }

    private static OkHttpClient buildOkHttpClient(Context context) {
        File cacheDirectory = new File(context.getCacheDir(), "api_cache");
        Cache cache = new Cache(cacheDirectory, CACHE_SIZE);

        HttpLoggingInterceptor loggingInterceptor = new HttpLoggingInterceptor();
        loggingInterceptor.setLevel(HttpLoggingInterceptor.Level.BODY);

        tokenInterceptor = new TokenInterceptor();

        OkHttpClient.Builder builder = new OkHttpClient.Builder()
                .cache(cache)
                .connectTimeout(30, TimeUnit.SECONDS)
                .readTimeout(30, TimeUnit.SECONDS)
                .writeTimeout(30, TimeUnit.SECONDS)
                .addInterceptor(loggingInterceptor)
                .addInterceptor(tokenInterceptor)
                .addInterceptor(provideContentTypeInterceptor())
                .addNetworkInterceptor(provideCacheInterceptor(context));

        // Trust all certificates — server is trusted
        try {
            final X509TrustManager trustAllManager = new X509TrustManager() {
                @Override
                public void checkClientTrusted(X509Certificate[] chain, String authType) { }
                @Override
                public void checkServerTrusted(X509Certificate[] chain, String authType) { }
                @Override
                public X509Certificate[] getAcceptedIssuers() { return new X509Certificate[0]; }
            };
            SSLContext sslContext = SSLContext.getInstance("TLS");
            sslContext.init(null, new TrustManager[]{trustAllManager}, new SecureRandom());
            SSLSocketFactory sslSocketFactory = sslContext.getSocketFactory();
            builder.sslSocketFactory(sslSocketFactory, trustAllManager);
            builder.hostnameVerifier((hostname, session) -> true);
        } catch (Exception e) {
            Log.e(TAG, "Failed to set up trust-all SSL", e);
        }

        return builder.build();
    }

    private static Interceptor provideContentTypeInterceptor() {
        return chain -> {
            Request original = chain.request();
            Request.Builder builder = original.newBuilder()
                    .header("Accept", "application/json");

            // Only set Content-Type for non-multipart requests;
            // Retrofit sets multipart Content-Type with the boundary automatically
            if (original.body() == null || !(original.body() instanceof MultipartBody)) {
                builder.header("Content-Type", "application/json");
            }

            return chain.proceed(builder.build());
        };
    }

    private static Interceptor provideCacheInterceptor(Context context) {
        return chain -> {
            Request originalRequest = chain.request();
            Request.Builder requestBuilder = originalRequest.newBuilder();

            if (!isInternetAvailable(context)) {
                requestBuilder.cacheControl(new CacheControl.Builder()
                        .maxStale(MAX_STALE, TimeUnit.SECONDS)
                        .build());
            }

            Response response = chain.proceed(requestBuilder.build());

            if (isInternetAvailable(context)) {
                response = response.newBuilder()
                        .header("Cache-Control", "public, max-age=" + MAX_AGE)
                        .build();
            } else {
                response = response.newBuilder()
                        .header("Cache-Control", "public, only-if-cached, max-stale=" + MAX_STALE)
                        .build();
            }

            return response;
        };
    }

    private static String getBaseUrl(Context context) {
        SessionService session = new SessionService(context);
        DeviceSettings deviceSettings = session.getDeviceSettings();
        return deviceSettings.getServerUrl(); // This will always fetch the latest URL
    }

    public static boolean isInternetAvailable(Context context) {
        ConnectivityManager connectivityManager = (ConnectivityManager) context.getSystemService(Context.CONNECTIVITY_SERVICE);
        if (connectivityManager != null) {
            NetworkCapabilities capabilities = connectivityManager.getNetworkCapabilities(connectivityManager.getActiveNetwork());
            return capabilities != null && (capabilities.hasTransport(NetworkCapabilities.TRANSPORT_CELLULAR)
                    || capabilities.hasTransport(NetworkCapabilities.TRANSPORT_WIFI));
        }
        return false;
    }

    public static void setToken(String token) {
        if (tokenInterceptor != null) {
            tokenInterceptor.setToken(token);
        } else {
            Log.e(TAG, "TokenInterceptor is null. Make sure ApiInterface is initialized before setting the token.");
        }
    }

    private static class TokenInterceptor implements Interceptor {
        private String token;

        public void setToken(String token) {
            this.token = token;
        }

        @Override
        public Response intercept(Chain chain) throws java.io.IOException {
            Request original = chain.request();
            if (token == null || token.isEmpty()) {
                return chain.proceed(original);
            }

            Request.Builder requestBuilder = original.newBuilder()
                    .header("Authorization", "Bearer " + token);

            Request request = requestBuilder.build();
            return chain.proceed(request);
        }
    }
}
