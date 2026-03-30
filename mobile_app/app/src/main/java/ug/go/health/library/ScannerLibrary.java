package ug.go.health.library;

import ug.go.health.ihrisbiometric.utils.BitmapUtils;
import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Arrays;
import java.util.concurrent.ExecutorService;
import java.util.concurrent.Executors;

import android.app.Activity;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Environment;
import android.util.Log;

import ug.go.health.ihrisbiometric.ScannerEventListener;
import ug.go.health.ihrisbiometric.StatusHandler;

/**
 * High-level SDK for managing fingerprint scanner operations.
 */
public class ScannerLibrary {
    private static final String TAG = "ScannerLibrary";
    private final DevComm devComm;
    private final ScannerEventListener eventListener;
    private final StatusHandler statusHandler;
    private final ExecutorService executor = Executors.newSingleThreadExecutor();

    private byte[] templateBuffer = new byte[DevComm.GD_MAX_RECORD_SIZE];
    private byte[] imageBuffer = new byte[1024 * 200];
    private int templateSize = 0;
    private int imageWidth = 0;
    private int imageHeight = 0;
    private int imageDataReceived = 0;

    public ScannerLibrary(Activity parentActivity, ScannerEventListener eventListener) {
        this.devComm = new DevComm(parentActivity, eventListener);
        this.eventListener = eventListener;
        this.statusHandler = new StatusHandler();
    }

        public void init(android.app.Activity activity, ug.go.health.ihrisbiometric.ScannerEventListener listener) { /* Legacy wrapper */ }

public int OpenDevice(String devicePath, int baudRate) {
        return devComm.OpenComm(devicePath, baudRate) == DevComm.ERR_SUCCESS ? 0 : -1;
    }

    public void CloseDevice() {
        devComm.CloseComm();
        executor.shutdownNow();
    }

    public void enroll(int templateId) {
        executeCommand((short) DevComm.CMD_ENROLL_CODE, "Place your finger on the sensor", () -> {
            devComm.InitPacket((short) DevComm.CMD_ENROLL_CODE, true);
            devComm.SetDataLen((short) 2);
            devComm.m_abyPacket[6] = devComm.LOBYTE((short) templateId);
            devComm.m_abyPacket[7] = devComm.HIBYTE((short) templateId);
            devComm.AddCheckSum(true);

            while (true) {
                boolean commOk = devComm.Send_Command((short) DevComm.CMD_ENROLL_CODE);
                if (!commOk) {
                    postResult(ScannerResult.error((short) DevComm.CMD_ENROLL_CODE, "Comm Failure"));
                    return;
                }

                short ret  = devComm.GetRetCode();
                short data = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]);

                if (ret == DevComm.ERR_SUCCESS) {
                    switch (data & 0xFFFF) {
                        case DevComm.GD_NEED_FIRST_SWEEP:
                            postResult(ScannerResult.waiting((short) DevComm.CMD_ENROLL_CODE, "Place your finger on the sensor"));
                            break;
                        case DevComm.GD_NEED_SECOND_SWEEP:
                            postResult(ScannerResult.waiting((short) DevComm.CMD_ENROLL_CODE, "Place your finger again (2nd)"));
                            break;
                        case DevComm.GD_NEED_THIRD_SWEEP:
                            postResult(ScannerResult.waiting((short) DevComm.CMD_ENROLL_CODE, "Place your finger again (3rd)"));
                            break;
                        case DevComm.GD_NEED_RELEASE_FINGER:
                            postResult(ScannerResult.waiting((short) DevComm.CMD_ENROLL_CODE, "Release your finger"));
                            break;
                        default:
                            // Final success — data is the assigned template number
                            postResult(ScannerResult.success((short) DevComm.CMD_ENROLL_CODE, "Enrolled", data, null));
                            return;
                    }
                } else {
                    // ERR_BAD_QUALITY — retry the current sweep (legacy behaviour)
                    if (data == (short) DevComm.ERR_BAD_QUALITY ||
                        data == (short) DevComm.ERR_SMALL_LINES ||
                        data == (short) DevComm.ERR_TOO_FAST) {
                        postResult(ScannerResult.waiting((short) DevComm.CMD_ENROLL_CODE, "Poor quality scan. Place your finger firmly and try again."));
                    } else {
                        // Genuine failure (generalization failed, duplicate, etc.)
                        postResult(ScannerResult.failure((short) DevComm.CMD_ENROLL_CODE, "Enrollment failed. Please try again."));
                        return;
                    }
                }

                // Rebuild packet for next send
                devComm.memset(devComm.m_abyPacket, (byte) 0, 64 * 1024);
                devComm.InitPacket((short) DevComm.CMD_ENROLL_CODE, true);
                devComm.SetDataLen((short) 2);
                devComm.m_abyPacket[6] = devComm.LOBYTE((short) templateId);
                devComm.m_abyPacket[7] = devComm.HIBYTE((short) templateId);
                devComm.AddCheckSum(true);
            }
        });
    }

    public void identify() {
        executeCommand((short) DevComm.CMD_IDENTIFY_CODE, "Place your finger on the sensor", () -> {
            devComm.InitPacket((short) DevComm.CMD_IDENTIFY_CODE, true);
            devComm.SetDataLen((short) 0);
            devComm.AddCheckSum(true);

            while (true) {
                boolean commOk = devComm.Send_Command((short) DevComm.CMD_IDENTIFY_CODE);
                if (!commOk) {
                    postResult(ScannerResult.error((short) DevComm.CMD_IDENTIFY_CODE, "Comm Failure"));
                    return;
                }

                short ret  = devComm.GetRetCode();
                short data = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]);

                if (ret == DevComm.ERR_SUCCESS) {
                    switch (data & 0xFFFF) {
                        case DevComm.GD_NEED_RELEASE_FINGER:
                            // Scanner needs finger released before it can identify — re-send
                            postResult(ScannerResult.waiting((short) DevComm.CMD_IDENTIFY_CODE, "Release your finger"));
                            devComm.memset(devComm.m_abyPacket, (byte) 0, 64 * 1024);
                            devComm.InitPacket((short) DevComm.CMD_IDENTIFY_CODE, true);
                            devComm.SetDataLen((short) 0);
                            devComm.AddCheckSum(true);
                            break;
                        default:
                            // Final result — data is the matched template number
                            postResult(ScannerResult.success((short) DevComm.CMD_IDENTIFY_CODE, "Identified", data, null));
                            return;
                    }
                } else {
                    postResult(ScannerResult.failure((short) DevComm.CMD_IDENTIFY_CODE, "Fingerprint not recognised. Please try again."));
                    return;
                }
            }
        });
    }

    public void verify(int templateId) {
        executeCommand((short) DevComm.CMD_VERIFY_CODE, "Place your finger on the sensor", () -> {
            devComm.InitPacket((short) DevComm.CMD_VERIFY_CODE, true);
            devComm.SetDataLen((short) 2);
            devComm.m_abyPacket[6] = devComm.LOBYTE((short) templateId);
            devComm.m_abyPacket[7] = devComm.HIBYTE((short) templateId);
            devComm.AddCheckSum(true);

            while (true) {
                boolean commOk = devComm.Send_Command((short) DevComm.CMD_VERIFY_CODE);
                if (!commOk) {
                    postResult(ScannerResult.error((short) DevComm.CMD_VERIFY_CODE, "Comm Failure"));
                    return;
                }

                short ret  = devComm.GetRetCode();
                short data = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]);

                if (ret == DevComm.ERR_SUCCESS) {
                    switch (data & 0xFFFF) {
                        case DevComm.GD_NEED_RELEASE_FINGER:
                            postResult(ScannerResult.waiting((short) DevComm.CMD_VERIFY_CODE, "Release your finger"));
                            devComm.memset(devComm.m_abyPacket, (byte) 0, 64 * 1024);
                            devComm.InitPacket((short) DevComm.CMD_VERIFY_CODE, true);
                            devComm.SetDataLen((short) 2);
                            devComm.m_abyPacket[6] = devComm.LOBYTE((short) templateId);
                            devComm.m_abyPacket[7] = devComm.HIBYTE((short) templateId);
                            devComm.AddCheckSum(true);
                            break;
                        default:
                            postResult(ScannerResult.success((short) DevComm.CMD_VERIFY_CODE, "Verified", data, null));
                            return;
                    }
                } else {
                    postResult(ScannerResult.failure((short) DevComm.CMD_VERIFY_CODE, "Fingerprint not recognised. Please try again."));
                    return;
                }
            }
        });
    }

    public void captureImage() {
        executeCommand((short) DevComm.CMD_UP_IMAGE_CODE, "Place your finger on the sensor", () -> {
            devComm.InitPacket((short) DevComm.CMD_UP_IMAGE_CODE, true);
            devComm.SetDataLen((short) 0);
            devComm.AddCheckSum(true);
            if (!devComm.Send_Command((short) DevComm.CMD_UP_IMAGE_CODE)) {
                postResult(ScannerResult.error((short) DevComm.CMD_UP_IMAGE_CODE, "Capture initiation failed"));
                return;
            }
            if (devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
                handleStandardResponse((short) DevComm.CMD_UP_IMAGE_CODE, true);
                return;
            }
            imageWidth = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]);
            imageHeight = devComm.MAKEWORD(devComm.m_abyPacket[10], devComm.m_abyPacket[11]);
            int totalExpected = imageWidth * imageHeight;
            imageDataReceived = 0;
            while (imageDataReceived < totalExpected) {
                if (!devComm.UART_ReceiveDataPacket((short) DevComm.CMD_UP_IMAGE_CODE)) break;
                int chunkLen = devComm.GetDataLen() - 2;
                System.arraycopy(devComm.m_abyPacket, 8, imageBuffer, imageDataReceived, chunkLen);
                imageDataReceived += chunkLen;
            }
            byte[] capturedImage = Arrays.copyOf(imageBuffer, imageDataReceived);
            byte[] pngImage = BitmapUtils.grayscaleToPng(capturedImage, imageWidth, imageHeight);
            postResult(ScannerResult.success((short) DevComm.CMD_UP_IMAGE_CODE, "Capture Success", 0, pngImage));
        });
    }

    public void getEmptyId() {
        executeCommand((short) DevComm.CMD_GET_EMPTY_ID_CODE, "Checking slot...", () -> {
            devComm.InitPacket((short) DevComm.CMD_GET_EMPTY_ID_CODE, true);
            devComm.SetDataLen((short) 0);
            devComm.AddCheckSum(true);
            handleStandardResponse((short) DevComm.CMD_GET_EMPTY_ID_CODE, devComm.Send_Command((short) DevComm.CMD_GET_EMPTY_ID_CODE));
        });
    }

    /**
     * Synchronous version of getEmptyId — blocks until the scanner responds.
     * Returns the empty slot number, or -1 on failure.
     * Must NOT be called from the main thread.
     */
    public int getEmptyIdSync() {
        devComm.InitPacket((short) DevComm.CMD_GET_EMPTY_ID_CODE, true);
        devComm.SetDataLen((short) 0);
        devComm.AddCheckSum(true);

        if (!devComm.Send_Command((short) DevComm.CMD_GET_EMPTY_ID_CODE)) {
            Log.e(TAG, "getEmptyIdSync: Comm Failure");
            return -1;
        }
        if (devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
            Log.e(TAG, "getEmptyIdSync: scanner error ret=" + devComm.GetRetCode());
            return -1;
        }
        int emptyId = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]) & 0xFFFF;
        Log.d(TAG, "getEmptyIdSync: next empty slot = " + emptyId);
        return emptyId;
    }

    public void deleteId(int templateId) {
        executeCommand((short) DevComm.CMD_CLEAR_TEMPLATE_CODE, "Deleting...", () -> {
            devComm.InitPacket((short) DevComm.CMD_CLEAR_TEMPLATE_CODE, true);
            devComm.SetDataLen((short) 2);
            devComm.m_abyPacket[6] = devComm.LOBYTE((short) templateId);
            devComm.m_abyPacket[7] = devComm.HIBYTE((short) templateId);
            devComm.AddCheckSum(true);
            handleStandardResponse((short) DevComm.CMD_CLEAR_TEMPLATE_CODE, devComm.Send_Command((short) DevComm.CMD_CLEAR_TEMPLATE_CODE));
        });
    }

    private void executeCommand(short commandCode, String msg, Runnable task) {
        statusHandler.post(() -> eventListener.onScannerEvent(ScannerResult.inProgress(commandCode, msg)));
        executor.execute(task);
    }

    private void handleStandardResponse(short code, boolean commSuccess) {
        if (!commSuccess) { postResult(ScannerResult.error(code, "Comm Failure")); return; }
        short ret = devComm.GetRetCode();
        short data = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]);
        if (ret == DevComm.ERR_SUCCESS) postResult(ScannerResult.success(code, "Success", data, null));
        else if (data >= (short)0xFFF1 && data <= (short)0xFFF4) postResult(ScannerResult.waiting(code, "Action required"));
        else postResult(ScannerResult.failure(code, "Error: " + ret));
    }

    private void postResult(ScannerResult res) {
        // Only fire the legacy onEvent string callback for results that need
        // string-based handling (EMPTY_ID). All other results are handled
        // exclusively via onScannerEvent to avoid double-processing and
        // leaking internal messages like "Success" to the UI.
        statusHandler.post(() -> {
            eventListener.onScannerEvent(res);
            if (res.getCommandCode() == (short) DevComm.CMD_GET_EMPTY_ID_CODE
                    && res.getType() == ScannerResult.Type.SUCCESS) {
                eventListener.onEvent("EMPTY_ID :: " + res.getValue());
            }
        });
    }

    public int Run_CmdGetEmptyID() { getEmptyId(); return 0; }
    public int Run_CmdIdentify() { identify(); return 0; }
    public int Run_CmdEnroll(int tid) { enroll(tid); return 0; }
    public int Run_CmdVerify(int tid) { verify(tid); return 0; }
    public int Run_CmdDeleteID(int tid) { deleteId(tid); return 0; }

    /**
     * Read a template from the scanner synchronously.
     * Returns the raw template bytes, or null on failure.
     * Must NOT be called from the main thread.
     */
    public byte[] readTemplateSync(int templateNo) {
        devComm.memset(devComm.m_abyPacket, (byte) 0, 64 * 1024);
        devComm.InitPacket((short) DevComm.CMD_READ_TEMPLATE_CODE, true);
        devComm.SetDataLen((short) 0x0002);
        devComm.SetCmdData((short) templateNo, true);
        devComm.AddCheckSum(true);

        if (!devComm.Send_Command((short) DevComm.CMD_READ_TEMPLATE_CODE)) {
            Log.e(TAG, "readTemplateSync: Comm Failure");
            return null;
        }
        if (devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
            Log.e(TAG, "readTemplateSync: scanner error ret=" + devComm.GetRetCode());
            return null;
        }

        int templateLen = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]) & 0xFFFF;
        byte[] result = new byte[templateLen];

        if (templateLen == DevComm.GD_TEMPLATE_SIZE) {
            // Single data packet
            if (!devComm.UART_ReceiveDataPacket((short) DevComm.CMD_READ_TEMPLATE_CODE)) {
                Log.e(TAG, "readTemplateSync: failed to receive single data packet");
                return null;
            }
            System.arraycopy(devComm.m_abyPacket, 10, result, 0, DevComm.GD_TEMPLATE_SIZE);
        } else {
            // Multi-packet read
            int offset = 0;
            while (offset < templateLen) {
                if (!devComm.UART_ReceiveDataPacket((short) DevComm.CMD_READ_TEMPLATE_CODE)) {
                    Log.e(TAG, "readTemplateSync: failed to receive data packet at offset " + offset);
                    return null;
                }
                if (devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
                    Log.e(TAG, "readTemplateSync: error in data packet");
                    return null;
                }
                int chunkLen = devComm.GetDataLen() - 4;
                System.arraycopy(devComm.m_abyPacket, 10, result, offset, chunkLen);
                offset += chunkLen;
            }
        }

        Log.d(TAG, "readTemplateSync: read " + templateLen + " bytes from slot " + templateNo);
        return result;
    }

    /** Legacy wrapper — async, fires onScannerEvent. Not used for file saving. */
    public int Run_CmdReadTemplate(int tid) { return 0; }

    /**
     * Write a template from templateBuffer into scanner slot p_nTmpNo.
     * Must call WriteTemplateFile(templateId, bytes) first to load the buffer.
     * Returns 0 on success, non-zero on failure.
     */
    public int Run_CmdWriteTemplate(int p_nTmpNo) {
        if (templateBuffer == null || templateSize <= 0) {
            Log.e(TAG, "Run_CmdWriteTemplate: no template data loaded");
            return 1;
        }

        final int[] result = {-1};
        final Object lock = new Object();

        executor.execute(() -> {
            int ret = writeTemplateToScanner(p_nTmpNo);
            synchronized (lock) {
                result[0] = ret;
                lock.notifyAll();
            }
        });

        synchronized (lock) {
            try {
                lock.wait(10000); // 10s timeout
            } catch (InterruptedException e) {
                Thread.currentThread().interrupt();
                return 1;
            }
        }
        return result[0];
    }

    private int writeTemplateToScanner(int templateNo) {
        // Step 1: Send command packet with template size
        devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, true);
        devComm.SetDataLen((short) 0x0002);
        devComm.SetCmdData((short) templateSize, true);
        devComm.AddCheckSum(true);

        if (!devComm.Send_Command((short) DevComm.CMD_WRITE_TEMPLATE_CODE)) {
            Log.e(TAG, "writeTemplate: Send_Command failed");
            return 1;
        }
        if (devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
            Log.e(TAG, "writeTemplate: scanner rejected command, ret=" + devComm.GetRetCode());
            return 1;
        }

        // Step 2: Send template data packet(s)
        if (templateSize <= DevComm.GD_RECORD_SIZE) {
            // Single packet
            devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
            devComm.SetDataLen((short) (templateSize + 2));
            devComm.SetCmdData((short) templateNo, true);
            System.arraycopy(templateBuffer, 0, devComm.m_abyPacket, 8, templateSize);
            devComm.AddCheckSum(false);

            if (!devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE)) {
                Log.e(TAG, "writeTemplate: Send_DataPacket failed (single)");
                return 2;
            }
        } else {
            // Multi-packet for large templates
            int n = templateSize / DevComm.DATA_SPLIT_UNIT;
            int r = templateSize % DevComm.DATA_SPLIT_UNIT;

            for (int i = 0; i < n; i++) {
                devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
                devComm.SetDataLen((short) (DevComm.DATA_SPLIT_UNIT + 4));
                devComm.SetCmdData((short) templateNo, true);
                devComm.SetCmdData((short) DevComm.DATA_SPLIT_UNIT, false);
                System.arraycopy(templateBuffer, i * DevComm.DATA_SPLIT_UNIT, devComm.m_abyPacket, 10, DevComm.DATA_SPLIT_UNIT);
                devComm.AddCheckSum(false);

                if (!devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE)) {
                    Log.e(TAG, "writeTemplate: Send_DataPacket failed (chunk " + i + ")");
                    return 2;
                }
            }

            if (r > 0) {
                devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
                devComm.SetDataLen((short) (r + 4));
                devComm.SetCmdData((short) templateNo, true);
                devComm.SetCmdData((short) (r & 0xFFFF), false);
                System.arraycopy(templateBuffer, n * DevComm.DATA_SPLIT_UNIT, devComm.m_abyPacket, 10, r);
                devComm.AddCheckSum(false);

                if (!devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE)) {
                    Log.e(TAG, "writeTemplate: Send_DataPacket failed (remainder)");
                    return 2;
                }
            }
        }

        postResult(ScannerResult.success((short) DevComm.CMD_WRITE_TEMPLATE_CODE,
                "Template written to slot " + templateNo, templateNo, null));
        return 0;
    }

    /**
     * Load template bytes into the internal buffer so Run_CmdWriteTemplate can send them.
     * Call this before Run_CmdWriteTemplate.
     */
    public boolean WriteTemplateFile(int templateId, byte[] data) {
        if (data == null || data.length == 0) {
            Log.e(TAG, "WriteTemplateFile: null or empty data");
            return false;
        }
        if (data.length > DevComm.GD_MAX_RECORD_SIZE) {
            Log.e(TAG, "WriteTemplateFile: data too large (" + data.length + " > " + DevComm.GD_MAX_RECORD_SIZE + ")");
            return false;
        }
        System.arraycopy(data, 0, templateBuffer, 0, data.length);
        templateSize = data.length;
        Log.d(TAG, "WriteTemplateFile: loaded " + templateSize + " bytes for slot " + templateId);
        return true;
    }

    public int Run_CmdDeleteAll() {
        deleteId(0xFF);
        return 0;
    }

    /**
     * Get an empty slot and write a template to it — all on the executor thread.
     * Callback fires on the main thread with the assigned slot (>0) or -1 on failure.
     */
    public void registerTemplateAsync(byte[] templateBytes, RegisterTemplateCallback callback) {
        if (templateBytes == null || templateBytes.length == 0) {
            statusHandler.post(() -> callback.onResult(-1, "Empty template data"));
            return;
        }
        executor.execute(() -> {
            // Step 1: get empty slot
            devComm.InitPacket((short) DevComm.CMD_GET_EMPTY_ID_CODE, true);
            devComm.SetDataLen((short) 0);
            devComm.AddCheckSum(true);
            if (!devComm.Send_Command((short) DevComm.CMD_GET_EMPTY_ID_CODE)
                    || devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
                statusHandler.post(() -> callback.onResult(-1, "Failed to get empty slot"));
                return;
            }
            int slot = devComm.MAKEWORD(devComm.m_abyPacket[8], devComm.m_abyPacket[9]) & 0xFFFF;
            if (slot <= 0) {
                statusHandler.post(() -> callback.onResult(-1, "No empty slot available"));
                return;
            }

            // Step 2: load bytes into buffer
            if (!WriteTemplateFile(slot, templateBytes)) {
                statusHandler.post(() -> callback.onResult(-1, "Failed to load template buffer"));
                return;
            }

            // Step 3: write to scanner
            int ret = writeTemplateToScanner(slot);
            if (ret == 0) {
                statusHandler.post(() -> callback.onResult(slot, null));
            } else {
                statusHandler.post(() -> callback.onResult(-1, "writeTemplate failed: " + ret));
            }
        });
    }

    public interface RegisterTemplateCallback {
        void onResult(int assignedSlot, String error);
    }
}
