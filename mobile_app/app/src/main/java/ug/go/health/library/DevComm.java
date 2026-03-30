package ug.go.health.library;

import android.app.Activity;
import android.content.Context;
import android.hardware.usb.UsbManager;
import android.util.Log;

import java.io.IOException;
import java.util.Arrays;
import java.util.LinkedList;
import java.util.Queue;

import cn.wch.ch34xuartdriver.CH34xUARTDriver;
import android_serialport_api.ComBean;
import android_serialport_api.SerialHelper;

/**
 * Handles the low-level communication protocol for the fingerprint scanner.
 * Supports both USB-Serial (CH34x) and standard Serial Port.
 */
public class DevComm {

    private static final String TAG = "DevComm";

    // --- Packet Prefix Constants ---
    public static final int CMD_PREFIX_CODE = 0xAA55;
    public static final int RCM_PREFIX_CODE = 0x55AA;
    public static final int CMD_DATA_PREFIX_CODE = 0xA55A;
    public static final int RCM_DATA_PREFIX_CODE = 0x5AA5;

    // --- Command Code Constants ---
    public static final int CMD_VERIFY_CODE = 0x0101;
    public static final int CMD_IDENTIFY_CODE = 0x0102;
    public static final int CMD_ENROLL_CODE = 0x0103;
    public static final int CMD_ENROLL_ONETIME_CODE = 0x0104;
    public static final int CMD_CLEAR_TEMPLATE_CODE = 0x0105;
    public static final int CMD_CLEAR_ALLTEMPLATE_CODE = 0x0106;
    public static final int CMD_GET_EMPTY_ID_CODE = 0x0107;
    public static final int CMD_GET_BROKEN_TEMPLATE_CODE = 0x0109;
    public static final int CMD_READ_TEMPLATE_CODE = 0x010A;
    public static final int CMD_WRITE_TEMPLATE_CODE = 0x010B;
    public static final int CMD_GET_FW_VERSION_CODE = 0x0112;
    public static final int CMD_FINGER_DETECT_CODE = 0x0113;
    public static final int CMD_FEATURE_OF_CAPTURED_FP_CODE = 0x011A;
    public static final int CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE = 0x011C;
    public static final int CMD_SLED_CTRL_CODE = 0x0124;
    public static final int CMD_IDENTIFY_FREE_CODE = 0x0125;
    public static final int CMD_SET_DEVPASS_CODE = 0x0126;
    public static final int CMD_VERIFY_DEVPASS_CODE = 0x0127;
    public static final int CMD_GET_ENROLL_COUNT_CODE = 0x0128;
    public static final int CMD_CHANGE_TEMPLATE_CODE = 0x0129;
    public static final int CMD_UP_IMAGE_CODE = 0x012C;
    public static final int CMD_VERIFY_WITH_DOWN_TMPL_CODE = 0x012D;
    public static final int CMD_IDENTIFY_WITH_DOWN_TMPL_CODE = 0x012E;
    public static final int CMD_FP_CANCEL_CODE = 0x0130;
    public static final int CMD_ADJUST_SENSOR_CODE = 0x0137;
    public static final int CMD_IDENTIFY_WITH_IMAGE_CODE = 0x0138;
    public static final int CMD_VERIFY_WITH_IMAGE_CODE = 0x0139;
    public static final int CMD_SET_PARAMETER_CODE = 0x013A;
    public static final int CMD_EXIT_DEVPASS_CODE = 0x013B;
    public static final int CMD_TEST_CONNECTION_CODE = 0x0150;
    public static final int CMD_ENTERSTANDBY_CODE = 0x0155;
    public static final int RCM_INCORRECT_COMMAND_CODE = 0x0160;
    public static final int CMD_ENTER_ISPMODE_CODE = 0x0171;

    // --- Error Code Constants ---
    public static final int ERR_SUCCESS = 0;
    public static final int ERR_FAIL = 1;
    public static final int ERR_CONTINUE = 2;
    public static final int ERR_COMM_FAIL = 3;
    public static final int ERR_VERIFY = 0x11;
    public static final int ERR_IDENTIFY = 0x12;
    public static final int ERR_TMPL_EMPTY = 0x13;
    public static final int ERR_TMPL_NOT_EMPTY = 0x14;
    public static final int ERR_ALL_TMPL_EMPTY = 0x15;
    public static final int ERR_EMPTY_ID_NOEXIST = 0x16;
    public static final int ERR_BROKEN_ID_NOEXIST = 0x17;
    public static final int ERR_INVALID_TMPL_DATA = 0x18;
    public static final int ERR_DUPLICATION_ID = 0x19;
    public static final int ERR_TOO_FAST = 0x20;
    public static final int ERR_BAD_QUALITY = 0x21;
    public static final int ERR_SMALL_LINES = 0x22;
    public static final int ERR_TIME_OUT = 0x23;
    public static final int ERR_NOT_AUTHORIZED = 0x24;
    public static final int ERR_GENERALIZE = 0x30;
    public static final int ERR_COM_TIMEOUT = 0x40;
    public static final int ERR_FP_CANCEL = 0x41;
    public static final int ERR_INTERNAL = 0x50;
    public static final int ERR_MEMORY = 0x51;
    public static final int ERR_EXCEPTION = 0x52;
    public static final int ERR_INVALID_TMPL_NO = 0x60;
    public static final int ERR_INVALID_PARAM = 0x70;
    public static final int ERR_NO_RELEASE = 0x71;
    public static final int ERR_INVALID_OPERATION_MODE = 0x72;
    public static final int ERR_NOT_SET_PWD = 0x74;
    public static final int ERR_FP_NOT_DETECTED = 0x75;
    public static final int ERR_ADJUST_SENSOR = 0x76;

    // --- Protocol Limits ---
    public static final int CMD_PACKET_LEN = 26;
    public static final int IMAGE_RECEIVE_UINT = 498;
    public static final int MAX_DATA_LEN = 600;
    public static final int GD_RECORD_SIZE = 498;
    public static final int GD_MAX_RECORD_SIZE = 900;

    // --- Enrollment Status Codes ---
    public static final int GD_NEED_FIRST_SWEEP = 0xFFF1;
    public static final int GD_NEED_SECOND_SWEEP = 0xFFF2;
    public static final int GD_NEED_THIRD_SWEEP = 0xFFF3;
    public static final int GD_NEED_RELEASE_FINGER = 0xFFF4;

    // --- Buffers ---
    public byte[] m_abyPacket = new byte[64 * 1024];

    // --- Circular Buffer for UART reads ---
    private final byte[] m_circularBuffer = new byte[128 * 1024];
    private int m_head = 0;
    private int m_tail = 0;
    private int m_count = 0;
    private final Object m_bufferLock = new Object();

    // --- Communication Interface ---
    public int m_nConnected = 0; // 0: None, 1: USB-Serial (CH34x), 3: Standard Serial
    private final CH34xUARTDriver m_uartDriver;
    private final SerialControl m_SerialPort;

    private UART_ReadThread m_readThread;
    private DispQueueThread m_dispQueueThread;

    public DevComm(Activity parentActivity, ug.go.health.ihrisbiometric.ScannerEventListener eventListener) {
        m_uartDriver = new CH34xUARTDriver((UsbManager) parentActivity.getSystemService(Context.USB_SERVICE), parentActivity, "cn.wch.ch34xuartdriver.ACTION_USB_PERMISSION");
        m_SerialPort = new SerialControl();
    }

    private void pushToBuffer(byte[] data, int length) {
        synchronized (m_bufferLock) {
            int toCopy = Math.min(length, m_circularBuffer.length - m_count);
            for (int i = 0; i < toCopy; i++) {
                m_circularBuffer[m_head] = data[i];
                m_head = (m_head + 1) % m_circularBuffer.length;
            }
            m_count += toCopy;
            m_bufferLock.notifyAll();
        }
    }

    private int popFromBuffer(byte[] target, int offset, int length) {
        synchronized (m_bufferLock) {
            int toRead = Math.min(length, m_count);
            for (int i = 0; i < toRead; i++) {
                target[offset + i] = m_circularBuffer[m_tail];
                m_tail = (m_tail + 1) % m_circularBuffer.length;
            }
            m_count -= toRead;
            return toRead;
        }
    }

    public int OpenComm(String devicePath, int baudRate) {
        if (m_uartDriver.UsbFeatureSupported()) {
            if (m_uartDriver.ResumeUsbList()) {
                if (m_uartDriver.UartInit()) {
                    m_uartDriver.SetConfig(baudRate, (byte) 8, (byte) 1, (byte) 0, (byte) 0);
                    m_nConnected = 1;
                    m_readThread = new UART_ReadThread();
                    m_readThread.start();
                    return ERR_SUCCESS;
                }
            }
        }

        m_SerialPort.setPort(devicePath);
        m_SerialPort.setBaudRate(baudRate);
        try {
            m_SerialPort.open();
            m_nConnected = 3;
            m_dispQueueThread = new DispQueueThread();
            m_dispQueueThread.start();
            return ERR_SUCCESS;
        } catch (Exception e) {
            return ERR_FAIL;
        }
    }

    public void CloseComm() {
        if (m_nConnected == 1) {
            m_nConnected = 0;
            if (m_readThread != null) m_readThread.interrupt();
            m_uartDriver.CloseDevice();
        } else if (m_nConnected == 3) {
            m_nConnected = 0;
            if (m_dispQueueThread != null) m_dispQueueThread.interrupt();
            m_SerialPort.close();
        }
    }

    public short GetRetCode() { return MAKEWORD(m_abyPacket[6], m_abyPacket[7]); }
    public short GetDataLen() { return MAKEWORD(m_abyPacket[4], m_abyPacket[5]); }
    public void SetDataLen(short dataLen) {
        m_abyPacket[4] = LOBYTE(dataLen);
        m_abyPacket[5] = HIBYTE(dataLen);
    }

    public void InitPacket(short commandCode, boolean isCommand) {
        Arrays.fill(m_abyPacket, (byte) 0);
        short prefix = isCommand ? (short) CMD_PREFIX_CODE : (short) CMD_DATA_PREFIX_CODE;
        m_abyPacket[0] = LOBYTE(prefix);
        m_abyPacket[1] = HIBYTE(prefix);
        m_abyPacket[2] = LOBYTE(commandCode);
        m_abyPacket[3] = HIBYTE(commandCode);
    }

    public void AddCheckSum(boolean isCommandPacket) {
        int length = isCommandPacket ? CMD_PACKET_LEN : GetDataLen() + 6;
        short sum = 0;
        for (int i = 0; i < length; i++) sum += (m_abyPacket[i] & 0xFF);
        m_abyPacket[length] = LOBYTE(sum);
        m_abyPacket[length + 1] = HIBYTE(sum);
    }

    public boolean CheckReceive(short expectedPrefix, short expectedCommand) {
        short receivedPrefix = MAKEWORD(m_abyPacket[0], m_abyPacket[1]);
        short receivedCommand = MAKEWORD(m_abyPacket[2], m_abyPacket[3]);
        if (receivedPrefix != expectedPrefix || receivedCommand != expectedCommand) return false;
        int length = (expectedPrefix == (short) RCM_PREFIX_CODE) ? CMD_PACKET_LEN : GetDataLen() + 6;
        short sum = 0;
        for (int i = 0; i < length; i++) sum += (m_abyPacket[i] & 0xFF);
        return sum == MAKEWORD(m_abyPacket[length], m_abyPacket[length + 1]);
    }

    public boolean Send_Command(short commandCode) {
        int packetSize = CMD_PACKET_LEN + 2;
        if (m_nConnected == 1) {
            if (m_uartDriver.WriteData(m_abyPacket, packetSize) < 0) return false;
        } else if (m_nConnected == 3) {
            m_SerialPort.send(Arrays.copyOf(m_abyPacket, packetSize));
        } else return false;
        return UART_ReceiveAck(commandCode, true);
    }

    public boolean UART_ReceiveAck(short commandCode, boolean isCmdResponse) {
        int bytesRead = 0;
        int totalExpected = isCmdResponse ? CMD_PACKET_LEN + 2 : GetDataLen() + 8;
        long startTime = System.currentTimeMillis();
        while (bytesRead < totalExpected) {
            if (System.currentTimeMillis() - startTime > 10000) {
                synchronized (m_bufferLock) { m_count = m_head = m_tail = 0; }
                return false;
            }
            synchronized (m_bufferLock) {
                if (m_count <= 0) {
                    try { m_bufferLock.wait(100); } catch (InterruptedException e) { return false; }
                    continue;
                }
                bytesRead += popFromBuffer(m_abyPacket, bytesRead, totalExpected - bytesRead);
            }
        }
        return CheckReceive(isCmdResponse ? (short) RCM_PREFIX_CODE : (short) RCM_DATA_PREFIX_CODE, commandCode);
    }

    public boolean UART_ReceiveDataPacket(short commandCode) {
        if (!UART_ReadDataN(m_abyPacket, 0, 6)) return false;
        if (!UART_ReadDataN(m_abyPacket, 6, GetDataLen() + 2)) return false;
        return CheckReceive((short) RCM_DATA_PREFIX_CODE, commandCode);
    }

    public boolean UART_SendDataPacket(short commandCode) {
        int totalLen = GetDataLen() + 8;
        if (m_nConnected == 1) {
            if (m_uartDriver.WriteData(m_abyPacket, totalLen) < 0) return false;
        } else if (m_nConnected == 3) {
            m_SerialPort.send(Arrays.copyOf(m_abyPacket, totalLen));
        } else return false;
        return UART_ReceiveDataPacket(commandCode);
    }

    boolean UART_ReadDataN(byte[] targetBuffer, int offset, int length) {
        int receivedCount = 0;
        long startTime = System.currentTimeMillis();
        while (receivedCount < length) {
            if (System.currentTimeMillis() - startTime > 10000) {
                synchronized (m_bufferLock) { m_count = m_head = m_tail = 0; }
                return false;
            }
            synchronized (m_bufferLock) {
                if (m_count <= 0) {
                    try { m_bufferLock.wait(100); } catch (InterruptedException e) { return false; }
                    continue;
                }
                receivedCount += popFromBuffer(targetBuffer, offset + receivedCount, length - receivedCount);
            }
        }
        return true;
    }

    public class UART_ReadThread extends Thread {
        public void run() {
            while (!isInterrupted() && m_nConnected == 1) {
                byte[] tempBuffer = new byte[4096];
                int count = m_uartDriver.ReadData(tempBuffer, tempBuffer.length);
                if (count > 0) pushToBuffer(tempBuffer, count);
                try { Thread.sleep(10); } catch (InterruptedException e) { break; }
            }
        }
    }

    public short MAKEWORD(byte low, byte high) { return (short) (((high & 0xFF) << 8) | (low & 0xFF)); }
    public byte LOBYTE(short s) { return (byte) (s & 0xFF); }
    public byte HIBYTE(short s) { return (byte) ((s >> 8) & 0xFF); }

    private class SerialControl extends SerialHelper {
        @Override protected void onDataReceived(final ComBean ComRecData) {
            if (m_dispQueueThread != null) m_dispQueueThread.AddQueue(ComRecData);
        }
    }

    private class DispQueueThread extends Thread {
        private final Queue<ComBean> queue = new LinkedList<>();
        @Override public void run() {
            while (!isInterrupted()) {
                ComBean data = null;
                synchronized (this) { if (!queue.isEmpty()) data = queue.poll(); }
                if (data != null) pushToBuffer(data.bRec, data.nSize);
                else { try { Thread.sleep(10); } catch (InterruptedException e) { break; } }
            }
        }
        public synchronized void AddQueue(ComBean data) { queue.add(data); }
    }
}
