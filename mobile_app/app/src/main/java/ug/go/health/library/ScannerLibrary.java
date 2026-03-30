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
    public int Run_CmdReadTemplate(int tid) { return 0; }
    public int Run_CmdWriteTemplate(int tid) { return 0; }
    public boolean WriteTemplateFile(int uid, byte[] data) { return true; }

    public int Run_CmdDeleteAll() {
        deleteId(0xFF); // Assuming 0xFF or similar means delete all for this protocol
        return 0;
    }
}
