package ug.go.health.library;

import android.app.Activity;
import android.content.Context;
import android.hardware.usb.UsbManager;
import android.util.Log;

import java.io.IOException;
import java.security.InvalidParameterException;
import java.util.Arrays;
import java.util.LinkedList;
import java.util.Queue;

import cn.wch.ch34xuartdriver.CH34xUARTDriver;
import android_serialport_api.ComBean;
import android_serialport_api.SerialHelper;

/**
 * Low-level communication protocol for the fingerprint scanner.
 * Ported from the original manufacturer implementation to preserve
 * SerialBuf-based header alignment (FixData) which prevents Comm Failure
 * caused by reading stale/garbage bytes from the serial buffer.
 */
public class DevComm {

    private static final String TAG = "DevComm";

    // --- Packet Prefix Constants ---
    public static final int CMD_PREFIX_CODE          = 0xAA55;
    public static final int RCM_PREFIX_CODE          = 0x55AA;
    public static final int CMD_DATA_PREFIX_CODE     = 0xA55A;
    public static final int RCM_DATA_PREFIX_CODE     = 0x5AA5;

    // --- Command Code Constants ---
    public static final int CMD_VERIFY_CODE                      = 0x0101;
    public static final int CMD_IDENTIFY_CODE                    = 0x0102;
    public static final int CMD_ENROLL_CODE                      = 0x0103;
    public static final int CMD_ENROLL_ONETIME_CODE              = 0x0104;
    public static final int CMD_CLEAR_TEMPLATE_CODE              = 0x0105;
    public static final int CMD_CLEAR_ALLTEMPLATE_CODE           = 0x0106;
    public static final int CMD_GET_EMPTY_ID_CODE                = 0x0107;
    public static final int CMD_GET_BROKEN_TEMPLATE_CODE         = 0x0109;
    public static final int CMD_READ_TEMPLATE_CODE               = 0x010A;
    public static final int CMD_WRITE_TEMPLATE_CODE              = 0x010B;
    public static final int CMD_GET_FW_VERSION_CODE              = 0x0112;
    public static final int CMD_FINGER_DETECT_CODE               = 0x0113;
    public static final int CMD_FEATURE_OF_CAPTURED_FP_CODE      = 0x011A;
    public static final int CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE   = 0x011C;
    public static final int CMD_SLED_CTRL_CODE                   = 0x0124;
    public static final int CMD_IDENTIFY_FREE_CODE               = 0x0125;
    public static final int CMD_SET_DEVPASS_CODE                 = 0x0126;
    public static final int CMD_VERIFY_DEVPASS_CODE              = 0x0127;
    public static final int CMD_GET_ENROLL_COUNT_CODE            = 0x0128;
    public static final int CMD_CHANGE_TEMPLATE_CODE             = 0x0129;
    public static final int CMD_UP_IMAGE_CODE                    = 0x012C;
    public static final int CMD_VERIFY_WITH_DOWN_TMPL_CODE       = 0x012D;
    public static final int CMD_IDENTIFY_WITH_DOWN_TMPL_CODE     = 0x012E;
    public static final int CMD_FP_CANCEL_CODE                   = 0x0130;
    public static final int CMD_ADJUST_SENSOR_CODE               = 0x0137;
    public static final int CMD_IDENTIFY_WITH_IMAGE_CODE         = 0x0138;
    public static final int CMD_VERIFY_WITH_IMAGE_CODE           = 0x0139;
    public static final int CMD_SET_PARAMETER_CODE               = 0x013A;
    public static final int CMD_EXIT_DEVPASS_CODE                = 0x013B;
    public static final int CMD_TEST_CONNECTION_CODE             = 0x0150;
    public static final int CMD_ENTERSTANDBY_CODE                = 0x0155;
    public static final int RCM_INCORRECT_COMMAND_CODE           = 0x0160;
    public static final int CMD_ENTER_ISPMODE_CODE               = 0x0171;

    // --- Error Code Constants ---
    public static final int ERR_SUCCESS              = 0;
    public static final int ERR_FAIL                 = 1;
    public static final int ERR_CONTINUE             = 2;
    public static final int ERR_COMM_FAIL            = 3;
    public static final int ERR_VERIFY               = 0x11;
    public static final int ERR_IDENTIFY             = 0x12;
    public static final int ERR_TMPL_EMPTY           = 0x13;
    public static final int ERR_TMPL_NOT_EMPTY       = 0x14;
    public static final int ERR_ALL_TMPL_EMPTY       = 0x15;
    public static final int ERR_EMPTY_ID_NOEXIST     = 0x16;
    public static final int ERR_BROKEN_ID_NOEXIST    = 0x17;
    public static final int ERR_INVALID_TMPL_DATA    = 0x18;
    public static final int ERR_DUPLICATION_ID       = 0x19;
    public static final int ERR_TOO_FAST             = 0x20;
    public static final int ERR_BAD_QUALITY          = 0x21;
    public static final int ERR_SMALL_LINES          = 0x22;
    public static final int ERR_TIME_OUT             = 0x23;
    public static final int ERR_NOT_AUTHORIZED       = 0x24;
    public static final int ERR_GENERALIZE           = 0x30;
    public static final int ERR_COM_TIMEOUT          = 0x40;
    public static final int ERR_FP_CANCEL            = 0x41;
    public static final int ERR_INTERNAL             = 0x50;
    public static final int ERR_MEMORY               = 0x51;
    public static final int ERR_EXCEPTION            = 0x52;
    public static final int ERR_INVALID_TMPL_NO      = 0x60;
    public static final int ERR_INVALID_PARAM        = 0x70;
    public static final int ERR_NO_RELEASE           = 0x71;
    public static final int ERR_INVALID_OPERATION_MODE = 0x72;
    public static final int ERR_NOT_SET_PWD          = 0x74;
    public static final int ERR_FP_NOT_DETECTED      = 0x75;
    public static final int ERR_ADJUST_SENSOR        = 0x76;

    // --- Protocol Limits ---
    public static final int CMD_PACKET_LEN      = 22;
    public static final int MAX_DATA_LEN        = 610;
    public static final int IMAGE_RECEIVE_UINT  = 498;
    public static final int DATA_SPLIT_UNIT     = 498;
    public static final int GD_RECORD_SIZE      = 498;
    public static final int GD_MAX_RECORD_SIZE  = 900;
    public static final int GD_TEMPLATE_SIZE    = 570;

    // --- Enrollment Status Codes ---
    public static final int GD_NEED_FIRST_SWEEP    = 0xFFF1;
    public static final int GD_NEED_SECOND_SWEEP   = 0xFFF2;
    public static final int GD_NEED_THIRD_SWEEP    = 0xFFF3;
    public static final int GD_NEED_RELEASE_FINGER = 0xFFF4;

    // --- Timeout (ms) ---
    public static final int UART_COMM_TIMEOUT = 1500;

    // --- Packet Buffer ---
    public byte[] m_abyPacket = new byte[64 * 1024];

    // --- Connection state: 0=none, 1=USB CH34x, 3=ttySerial ---
    public int m_nConnected = 0;

    // --- Hardware drivers ---
    private final CH34xUARTDriver m_uartDriver;
    private final SerialControl m_SerialPort;

    // --- Serial buffer (manufacturer-style, with FixData alignment) ---
    public final SerialBuf m_pSerialBuf = new SerialBuf();

    private UART_ReadThread m_readThread;
    private DispQueueThread m_dispQueueThread;

    public DevComm(Activity parentActivity, ug.go.health.ihrisbiometric.ScannerEventListener eventListener) {
        m_uartDriver = new CH34xUARTDriver(
                (UsbManager) parentActivity.getSystemService(Context.USB_SERVICE),
                parentActivity,
                "cn.wch.ch34xuartdriver.ACTION_USB_PERMISSION");
        m_SerialPort = new SerialControl();
    }

    // -------------------------------------------------------------------------
    // Open / Close
    // -------------------------------------------------------------------------

    public int OpenComm(String devicePath, int baudRate) {
        if (m_nConnected != 0) return ERR_FAIL;

        if (m_uartDriver.UsbFeatureSupported() && m_uartDriver.ResumeUsbList()) {
            if (m_uartDriver.UartInit()) {
                m_uartDriver.SetConfig(baudRate, (byte) 8, (byte) 1, (byte) 0, (byte) 0);
                m_nConnected = 1;
                m_pSerialBuf.ClearBuf();
                m_readThread = new UART_ReadThread();
                m_readThread.start();
                return ERR_SUCCESS;
            }
        }

        m_SerialPort.setPort(devicePath);
        m_SerialPort.setBaudRate(baudRate);
        try {
            m_SerialPort.open();
            m_nConnected = 3;
            m_dispQueueThread = new DispQueueThread();
            m_dispQueueThread.start();
            m_pSerialBuf.ClearBuf();
            return ERR_SUCCESS;
        } catch (SecurityException | IOException | InvalidParameterException e) {
            Log.e(TAG, "OpenComm failed", e);
            return ERR_FAIL;
        }
    }

    public void CloseComm() {
        if (m_nConnected == 1) {
            m_nConnected = 0;
            if (m_readThread != null) { m_readThread.interrupt(); m_readThread = null; }
            m_uartDriver.CloseDevice();
        } else if (m_nConnected == 3) {
            m_nConnected = 0;
            if (m_dispQueueThread != null) { m_dispQueueThread.interrupt(); m_dispQueueThread = null; }
            m_SerialPort.close();
        }
        m_pSerialBuf.ClearBuf();
    }

    // -------------------------------------------------------------------------
    // Packet helpers
    // -------------------------------------------------------------------------

    public short GetRetCode() {
        return (short) (((m_abyPacket[7] << 8) & 0xFF00) | (m_abyPacket[6] & 0xFF));
    }

    public short GetDataLen() {
        return (short) (((m_abyPacket[5] << 8) & 0xFF00) | (m_abyPacket[4] & 0xFF));
    }

    public void SetDataLen(short dataLen) {
        m_abyPacket[4] = (byte) (dataLen & 0xFF);
        m_abyPacket[5] = (byte) ((dataLen >> 8) & 0xFF);
    }

    public void InitPacket(short commandCode, boolean isCommand) {
        Arrays.fill(m_abyPacket, 0, CMD_PACKET_LEN, (byte) 0);
        int prefix = isCommand ? CMD_PREFIX_CODE : CMD_DATA_PREFIX_CODE;
        m_abyPacket[0] = (byte) (prefix & 0xFF);
        m_abyPacket[1] = (byte) ((prefix >> 8) & 0xFF);
        m_abyPacket[2] = (byte) (commandCode & 0xFF);
        m_abyPacket[3] = (byte) ((commandCode >> 8) & 0xFF);
    }

    public void AddCheckSum(boolean isCommandPacket) {
        int length = isCommandPacket ? CMD_PACKET_LEN : (GetDataLen() + 6);
        short sum = 0;
        for (int i = 0; i < length; i++) sum += (m_abyPacket[i] & 0xFF);
        m_abyPacket[length]     = (byte) (sum & 0xFF);
        m_abyPacket[length + 1] = (byte) ((sum >> 8) & 0xFF);
    }

    public boolean CheckReceive(short expectedPrefix, short expectedCommand) {
        short receivedPrefix  = (short) (((m_abyPacket[1] << 8) & 0xFF00) | (m_abyPacket[0] & 0xFF));
        short receivedCommand = (short) (((m_abyPacket[3] << 8) & 0xFF00) | (m_abyPacket[2] & 0xFF));
        if (receivedPrefix != expectedPrefix || receivedCommand != expectedCommand) {
            Log.w(TAG, String.format("CheckReceive prefix/cmd mismatch: exp=%04X/%04X got=%04X/%04X",
                    expectedPrefix & 0xFFFF, expectedCommand & 0xFFFF,
                    receivedPrefix & 0xFFFF, receivedCommand & 0xFFFF));
            return false;
        }
        int length = (expectedPrefix == (short) RCM_PREFIX_CODE) ? CMD_PACKET_LEN : (GetDataLen() + 6);
        short sum = 0;
        for (int i = 0; i < length; i++) sum += (m_abyPacket[i] & 0xFF);
        short stored = (short) (((m_abyPacket[length + 1] << 8) & 0xFF00) | (m_abyPacket[length] & 0xFF));
        if (sum != stored) {
            Log.w(TAG, String.format("CheckReceive checksum mismatch: calc=%04X stored=%04X", sum & 0xFFFF, stored & 0xFFFF));
            return false;
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Send / Receive
    // -------------------------------------------------------------------------

    public boolean Send_Command(short commandCode) {
        if (m_nConnected == 1) {
            if (m_uartDriver.WriteData(m_abyPacket, CMD_PACKET_LEN + 2) < 0) return false;
        } else if (m_nConnected == 3) {
            m_SerialPort.send(Arrays.copyOf(m_abyPacket, CMD_PACKET_LEN + 2));
        } else {
            return false;
        }
        return UART_ReceiveAck(commandCode, true);
    }

    /**
     * Receive a command-response ACK packet.
     * Uses per-command timeouts and SerialBuf.FixData() to align on the
     * 0xAA 0x55 response header — matching the original manufacturer logic.
     */
    public boolean UART_ReceiveAck(short commandCode, boolean isCmdResponse) {
        int totalLen = CMD_PACKET_LEN + 2;
        int readLen  = 0;
        boolean first = true;

        // Finger-scan commands need a longer timeout (user must place finger)
        int timeout = UART_COMM_TIMEOUT;
        if (commandCode == CMD_VERIFY_CODE ||
            commandCode == CMD_IDENTIFY_CODE ||
            commandCode == CMD_IDENTIFY_FREE_CODE ||
            commandCode == CMD_ENROLL_CODE ||
            commandCode == CMD_ENROLL_ONETIME_CODE ||
            commandCode == CMD_CHANGE_TEMPLATE_CODE ||
            commandCode == CMD_FEATURE_OF_CAPTURED_FP_CODE ||
            commandCode == CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE ||
            commandCode == CMD_UP_IMAGE_CODE ||
            commandCode == CMD_FINGER_DETECT_CODE) {
            timeout = UART_COMM_TIMEOUT * 10;
        }

        long startTime = System.currentTimeMillis();

        while (readLen < totalLen) {
            if (System.currentTimeMillis() - startTime > timeout) {
                Log.w(TAG, "UART_ReceiveAck timeout for cmd " + String.format("0x%04X", commandCode & 0xFFFF));
                m_pSerialBuf.ClearBuf();
                return false;
            }

            int available = m_pSerialBuf.GetPushedSize();
            if (available <= 0) continue;

            // Align buffer to response header 0xAA 0x55 on first read
            if (first) {
                if (m_pSerialBuf.FixData((byte) 0xAA, (byte) 0x55) < 0) continue;
                first = false;
            }

            available = m_pSerialBuf.GetPushedSize();
            int toRead = Math.min(totalLen - readLen, available);
            int got = m_pSerialBuf.PopData(m_abyPacket, readLen, toRead);
            readLen += got;
            startTime = System.currentTimeMillis(); // reset timeout on progress
        }

        return CheckReceive(
                isCmdResponse ? (short) RCM_PREFIX_CODE : (short) RCM_DATA_PREFIX_CODE,
                commandCode);
    }

    /**
     * Receive a data packet response.
     * Reads the 6-byte header first (aligning on 0xA5 0x5A), then reads the payload.
     */
    public boolean UART_ReceiveDataPacket(short commandCode) {
        int timeout = UART_COMM_TIMEOUT;
        if (commandCode == CMD_VERIFY_CODE ||
            commandCode == CMD_IDENTIFY_CODE ||
            commandCode == CMD_IDENTIFY_FREE_CODE ||
            commandCode == CMD_ENROLL_CODE ||
            commandCode == CMD_ENROLL_ONETIME_CODE ||
            commandCode == CMD_CHANGE_TEMPLATE_CODE ||
            commandCode == CMD_FEATURE_OF_CAPTURED_FP_CODE ||
            commandCode == CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE ||
            commandCode == CMD_UP_IMAGE_CODE ||
            commandCode == CMD_FINGER_DETECT_CODE) {
            timeout = UART_COMM_TIMEOUT * 10;
        }

        // Read 6-byte header, aligning on data-response prefix 0xA5 0x5A
        if (!UART_ReadDataN(m_abyPacket, 0, 6, timeout, true)) {
            Log.w(TAG, "UART_ReceiveDataPacket: header read failed");
            m_pSerialBuf.ClearBuf();
            return false;
        }

        // Read remaining payload + 2-byte checksum
        if (!UART_ReadDataN(m_abyPacket, 6, GetDataLen() + 2, timeout, false)) {
            Log.w(TAG, "UART_ReceiveDataPacket: payload read failed");
            m_pSerialBuf.ClearBuf();
            return false;
        }

        return CheckReceive((short) RCM_DATA_PREFIX_CODE, commandCode);
    }

    public boolean UART_SendDataPacket(short commandCode) {
        int len = GetDataLen() + 8;
        if (m_nConnected == 1) {
            if (m_uartDriver.WriteData(m_abyPacket, len) < 0) return false;
        } else if (m_nConnected == 3) {
            m_SerialPort.send(Arrays.copyOf(m_abyPacket, len));
        } else {
            return false;
        }
        return UART_ReceiveDataPacket(commandCode);
    }

    /**
     * Read exactly p_nLen bytes into targetBuffer starting at offset.
     * If p_bFix is true, aligns on the data-response prefix 0xA5 0x5A first.
     */
    boolean UART_ReadDataN(byte[] targetBuffer, int offset, int length, int timeout, boolean fixHeader) {
        int received = 0;
        boolean first = fixHeader;
        long startTime = System.currentTimeMillis();

        while (received < length) {
            if (System.currentTimeMillis() - startTime > timeout) {
                Log.w(TAG, "UART_ReadDataN timeout");
                m_pSerialBuf.ClearBuf();
                return false;
            }

            int available = m_pSerialBuf.GetPushedSize();
            if (available <= 0) continue;

            if (first) {
                if (m_pSerialBuf.FixData((byte) 0xA5, (byte) 0x5A) < 0) continue;
                first = false;
            }

            available = m_pSerialBuf.GetPushedSize();
            int toRead = Math.min(length - received, available);
            int got = m_pSerialBuf.PopData(targetBuffer, offset + received, toRead);
            received += got;
            startTime = System.currentTimeMillis();
        }
        return true;
    }

    // -------------------------------------------------------------------------
    // Byte helpers
    // -------------------------------------------------------------------------

    public short MAKEWORD(byte low, byte high) {
        return (short) (((high & 0xFF) << 8) | (low & 0xFF));
    }

    public byte LOBYTE(short s) { return (byte) (s & 0xFF); }
    public byte HIBYTE(short s) { return (byte) ((s >> 8) & 0xFF); }

    public void memset(byte[] buf, byte val, int len) {
        Arrays.fill(buf, 0, len, val);
    }

    // -------------------------------------------------------------------------
    // USB CH34x read thread
    // -------------------------------------------------------------------------

    public class UART_ReadThread extends Thread {
        @Override
        public void run() {
            byte[] buf = new byte[MAX_DATA_LEN];
            while (!isInterrupted() && m_nConnected == 1) {
                int count = m_uartDriver.ReadData(buf, MAX_DATA_LEN);
                if (count > 0) {
                    int ret;
                    do {
                        ret = m_pSerialBuf.PushData(buf, 0, count);
                        if (ret < 0) {
                            try { Thread.sleep(10); } catch (InterruptedException e) { interrupt(); return; }
                        }
                    } while (ret < 0);
                }
            }
        }
    }

    // -------------------------------------------------------------------------
    // ttySerial dispatch queue
    // -------------------------------------------------------------------------

    private class SerialControl extends SerialHelper {
        @Override
        protected void onDataReceived(final ComBean ComRecData) {
            if (m_dispQueueThread != null) m_dispQueueThread.AddQueue(ComRecData);
        }
    }

    private class DispQueueThread extends Thread {
        private final Queue<ComBean> queue = new LinkedList<>();

        @Override
        public void run() {
            while (!isInterrupted()) {
                ComBean data;
                synchronized (this) {
                    while (queue.isEmpty()) {
                        try { wait(); } catch (InterruptedException e) { interrupt(); return; }
                    }
                    data = queue.poll();
                }
                if (data != null) {
                    int ret;
                    do {
                        ret = m_pSerialBuf.PushData(data.bRec, 0, data.nSize);
                        if (ret < 0) {
                            try { Thread.sleep(10); } catch (InterruptedException e) { interrupt(); return; }
                        }
                    } while (ret < 0);
                }
            }
        }

        public synchronized void AddQueue(ComBean data) {
            queue.add(data);
            notify();
        }
    }
}
