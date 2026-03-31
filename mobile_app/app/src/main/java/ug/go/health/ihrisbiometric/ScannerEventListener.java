package ug.go.health.ihrisbiometric;

import ug.go.health.library.ScannerResult;

/**
 * Interface to receive scanner results and events.
 */
public interface ScannerEventListener {
    /**
     * Called when a scanner operation results in an event or completed result.
     * @param result The result object containing details about the operation.
     */
    void onScannerEvent(ScannerResult result);

    /**
     * Legacy support for string-based messages.
     * @param message
     */
    @Deprecated
    void onEvent(String message);
}
