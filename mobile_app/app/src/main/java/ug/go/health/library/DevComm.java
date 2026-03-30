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

    // --- Shared UART read buffer (original manufacturer approach) ---
    public byte[] m_pUARTReadBuf = new byte[MAX_DATA_LEN];
    public byte[] m_abyPacketTmp = new byte[64 * 1024];
    public int m_nUARTReadLen = 0;
    public volatile boolean m_bBufferHandle = false;

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
                m_nUARTReadLen = 0;
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
        m_nUARTReadLen = 0;
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
     * Receive a command-response ACK packet — original manufacturer implementation.
     * Uses m_pUARTReadBuf shared buffer with m_bBufferHandle spin-lock.
     */
    public boolean UART_ReceiveAck(short commandCode, boolean isCmdResponse) {
        int w_nReadLen = 0;
        int w_nTotalLen = CMD_PACKET_LEN + 2;
        int w_nTmpLen;
        long w_nTime = System.currentTimeMillis();

        while (w_nReadLen < w_nTotalLen) {
            if (System.currentTimeMillis() - w_nTime > 10000) {
                m_nUARTReadLen = 0;
                return false;
            }

            int i = 0;
            while (m_bBufferHandle) {
                if (++i > 10000) break;
            }
            m_bBufferHandle = true;

            if (m_nUARTReadLen <= 0) {
                m_bBufferHandle = false;
                continue;
            }

            if (w_nTotalLen - w_nReadLen < m_nUARTReadLen) {
                w_nTmpLen = w_nTotalLen - w_nReadLen;
                System.arraycopy(m_pUARTReadBuf, 0, m_abyPacket, w_nReadLen, w_nTmpLen);
                w_nReadLen += w_nTmpLen;
                m_nUARTReadLen -= w_nTmpLen;
                System.arraycopy(m_pUARTReadBuf, w_nTmpLen, m_abyPacketTmp, 0, m_nUARTReadLen);
                System.arraycopy(m_abyPacketTmp, 0, m_pUARTReadBuf, 0, m_nUARTReadLen);
            } else {
                System.arraycopy(m_pUARTReadBuf, 0, m_abyPacket, w_nReadLen, m_nUARTReadLen);
                w_nReadLen += m_nUARTReadLen;
                m_nUARTReadLen = 0;
            }
            m_bBufferHandle = false;
        }

        return CheckReceive(
                isCmdResponse ? (short) RCM_PREFIX_CODE : (short) RCM_DATA_PREFIX_CODE,
                commandCode);
    }

    /**
     * Receive a data packet — delegates to UART_ReceiveDataAck (original manufacturer).
     */
    public boolean UART_ReceiveDataPacket(short commandCode) {
        return UART_ReceiveDataAck(commandCode);
    }

    public boolean UART_ReceiveDataAck(short commandCode) {
        if (!UART_ReadDataN(m_abyPacket, 0, 6)) return false;
        if (!UART_ReadDataN(m_abyPacket, 6, GetDataLen() + 2)) return false;
        return CheckReceive((short) RCM_DATA_PREFIX_CODE, commandCode);
    }

    /**
     * Read exactly p_nLen bytes — original manufacturer implementation.
     */
    boolean UART_ReadDataN(byte[] p_pData, int p_nStart, int p_nLen) {
        int w_nRecvLen = p_nLen;
        int w_nTotalRecvLen = 0;
        int w_nTmpLen;
        long w_nTime = System.currentTimeMillis();

        while (w_nTotalRecvLen < p_nLen) {
            if (System.currentTimeMillis() - w_nTime > 10000) {
                m_nUARTReadLen = 0;
                return false;
            }
            if (m_nUARTReadLen <= 0) continue;

            if (p_nLen - w_nTotalRecvLen < m_nUARTReadLen) {
                w_nTmpLen = p_nLen - w_nTotalRecvLen;
                System.arraycopy(m_pUARTReadBuf, 0, p_pData, p_nStart + w_nTotalRecvLen, w_nTmpLen);
                w_nRecvLen -= w_nTmpLen;
                w_nTotalRecvLen += w_nTmpLen;
                m_nUARTReadLen -= w_nTmpLen;
                System.arraycopy(m_pUARTReadBuf, w_nTmpLen, m_abyPacketTmp, 0, m_nUARTReadLen);
                System.arraycopy(m_abyPacketTmp, 0, m_pUARTReadBuf, 0, m_nUARTReadLen);
            } else {
                System.arraycopy(m_pUARTReadBuf, 0, p_pData, p_nStart + w_nTotalRecvLen, m_nUARTReadLen);
                w_nRecvLen -= m_nUARTReadLen;
                w_nTotalRecvLen += m_nUARTReadLen;
                m_nUARTReadLen = 0;
            }
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

    /** Write a 16-bit value into the command data area of the packet.
     *  first=true  → bytes [6,7];  first=false → bytes [8,9] */
    public void SetCmdData(short value, boolean first) {
        int offset = first ? 6 : 8;
        m_abyPacket[offset]     = (byte) (value & 0xFF);
        m_abyPacket[offset + 1] = (byte) ((value >> 8) & 0xFF);
    }

    /** Send a data packet (CMD_DATA_PREFIX_CODE frame) and wait for ACK. */
    public boolean Send_DataPacket(short commandCode) {
        int len = GetDataLen() + 8; // header(6) + data + checksum(2)
        if (m_nConnected == 1) {
            if (m_uartDriver.WriteData(m_abyPacket, len) < 0) return false;
        } else if (m_nConnected == 3) {
            byte[] buf = new byte[len];
            System.arraycopy(m_abyPacket, 0, buf, 0, len);
            m_SerialPort.send(buf);
        } else {
            return false;
        }
        return UART_ReceiveDataAck(commandCode);
    }

    public void memset(byte[] buf, byte val, int len) {
        Arrays.fill(buf, 0, len, val);
    }

    // -------------------------------------------------------------------------
    // USB CH34x read thread — original manufacturer implementation
    // -------------------------------------------------------------------------

    public class UART_ReadThread extends Thread {
        @Override
        public void run() {
            while (true) {
                if (m_nConnected != 1) break;
                if (m_nUARTReadLen > 0) continue;
                m_nUARTReadLen = m_uartDriver.ReadData(m_pUARTReadBuf, MAX_DATA_LEN);
            }
        }
    }

    // -------------------------------------------------------------------------
    // ttySerial dispatch queue — original manufacturer implementation
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
                while (true) {
                    final ComBean data;
                    synchronized (this) { data = queue.poll(); }
                    if (data == null) break;

                    int i = 0;
                    while (m_bBufferHandle) {
                        if (++i > 10000) break;
                    }
                    m_bBufferHandle = true;
                    System.arraycopy(data.bRec, 0, m_pUARTReadBuf, m_nUARTReadLen, data.nSize);
                    m_nUARTReadLen += data.nSize;
                    m_bBufferHandle = false;
                }
                try { Thread.sleep(10); } catch (InterruptedException e) { interrupt(); return; }
            }
        }

        public synchronized void AddQueue(ComBean data) {
            queue.add(data);
        }
    }
}
