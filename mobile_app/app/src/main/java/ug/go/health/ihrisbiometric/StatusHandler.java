package ug.go.health.ihrisbiometric;

import android.os.Handler;
import android.os.Looper;

/**
 * Ensures scanner status updates and events are posted to the main UI thread.
 */
public class StatusHandler {
    private final Handler mainHandler = new Handler(Looper.getMainLooper());

    /**
     * Posts a runnable to the main thread.
     * @param runnable
     */
    public void post(Runnable runnable) {
        mainHandler.post(runnable);
    }
}
