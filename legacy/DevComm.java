package com.dulles.odoo.utils;

import android.content.Context;
import android.hardware.usb.UsbManager;
import android.widget.ArrayAdapter;
import android.widget.Spinner;
import android.widget.Toast;

import com.dulles.odoo.SerialHelper;
import com.dulles.odoo.SerialPortFinder;
import com.dulles.odoo.bean.ComBean;

import java.io.IOException;
import java.security.InvalidParameterException;
import java.util.ArrayList;
import java.util.Arrays;
import java.util.LinkedList;
import java.util.List;
import java.util.Queue;

import cn.wch.ch34xuartdriver.CH34xUARTDriver;

public class DevComm {

    // Packet Prefix
    public static final int CMD_PREFIX_CODE = (0xAA55);
    public static final int RCM_PREFIX_CODE = (0x55AA);
    public static final int CMD_DATA_PREFIX_CODE = (0xA55A);
    public static final int RCM_DATA_PREFIX_CODE = (0x5AA5);

    // Command
    public static final int CMD_VERIFY_CODE = (0x0101);
    public static final int CMD_IDENTIFY_CODE = (0x0102);
    public static final int CMD_ENROLL_CODE = (0x0103);
    public static final int CMD_ENROLL_ONETIME_CODE = (0x0104);
    public static final int CMD_CLEAR_TEMPLATE_CODE = (0x0105);
    public static final int CMD_CLEAR_ALLTEMPLATE_CODE = (0x0106);
    public static final int CMD_GET_EMPTY_ID_CODE = (0x0107);
    public static final int CMD_GET_BROKEN_TEMPLATE_CODE = (0x0109);
    public static final int CMD_READ_TEMPLATE_CODE = (0x010A);
    public static final int CMD_WRITE_TEMPLATE_CODE = (0x010B);
    public static final int CMD_GET_FW_VERSION_CODE = (0x0112);
    public static final int CMD_FINGER_DETECT_CODE = (0x0113);
    public static final int CMD_FEATURE_OF_CAPTURED_FP_CODE = (0x011A);
    public static final int CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE = (0x011C);
    public static final int CMD_SLED_CTRL_CODE = (0x0124);
    public static final int CMD_IDENTIFY_FREE_CODE = (0x0125);
    public static final int CMD_SET_DEVPASS_CODE = (0x0126);
    public static final int CMD_VERIFY_DEVPASS_CODE = (0x0127);
    public static final int CMD_GET_ENROLL_COUNT_CODE = (0x0128);
    public static final int CMD_CHANGE_TEMPLATE_CODE = (0x0129);
    public static final int CMD_UP_IMAGE_CODE = (0x012C);
    public static final int CMD_VERIFY_WITH_DOWN_TMPL_CODE = (0x012D);
    public static final int CMD_IDENTIFY_WITH_DOWN_TMPL_CODE = (0x012E);
    public static final int CMD_FP_CANCEL_CODE = (0x0130);
    public static final int CMD_ADJUST_SENSOR_CODE = (0x0137);
    public static final int CMD_IDENTIFY_WITH_IMAGE_CODE = (0x0138);
    public static final int CMD_VERIFY_WITH_IMAGE_CODE = (0x0139);
    public static final int CMD_SET_PARAMETER_CODE = (0x013A);
    public static final int CMD_EXIT_DEVPASS_CODE = (0x013B);
    // public static final int     CMD_SET_COMMNAD_VALID_FLAG_CODE			     = (0x013C);
// public static final int     CMD_GET_COMMNAD_VALID_FLAG_CODE			     = (0x013D);
    public static final int CMD_TEST_CONNECTION_CODE = (0x0150);
    public static final int CMD_ENTERSTANDBY_CODE = (0x0155);
    public static final int RCM_INCORRECT_COMMAND_CODE = (0x0160);
    public static final int CMD_ENTER_ISPMODE_CODE = (0x0171);

    // Error Code
    public static final int ERR_SUCCESS = (0);
    public static final int ERR_FAIL = (1);
    public static final int ERR_CONTINUE = (2);
    public static final int ERR_COMM_FAIL = (3);
    public static final int ERR_VERIFY = (0x11);
    public static final int ERR_IDENTIFY = (0x12);
    public static final int ERR_TMPL_EMPTY = (0x13);
    public static final int ERR_TMPL_NOT_EMPTY = (0x14);
    public static final int ERR_ALL_TMPL_EMPTY = (0x15);
    public static final int ERR_EMPTY_ID_NOEXIST = (0x16);
    public static final int ERR_BROKEN_ID_NOEXIST = (0x17);
    public static final int ERR_INVALID_TMPL_DATA = (0x18);
    public static final int ERR_DUPLICATION_ID = (0x19);
    public static final int ERR_TOO_FAST = (0x20);
    public static final int ERR_BAD_QUALITY = (0x21);
    public static final int ERR_SMALL_LINES = (0x22);
    public static final int ERR_TIME_OUT = (0x23);
    public static final int ERR_NOT_AUTHORIZED = (0x24);
    public static final int ERR_GENERALIZE = (0x30);
    public static final int ERR_COM_TIMEOUT = (0x40);
    public static final int ERR_FP_CANCEL = (0x41);
    public static final int ERR_INTERNAL = (0x50);
    public static final int ERR_MEMORY = (0x51);
    public static final int ERR_EXCEPTION = (0x52);
    public static final int ERR_INVALID_TMPL_NO = (0x60);
    public static final int ERR_INVALID_PARAM = (0x70);
    public static final int ERR_NO_RELEASE = (0x71);
    public static final int ERR_INVALID_OPERATION_MODE = (0x72);
    public static final int ERR_NOT_SET_PWD = (0x74);
    public static final int ERR_FP_NOT_DETECTED = (0x75);
    public static final int ERR_ADJUST_SENSOR = (0x76);

    // Return Value
    public static final int GD_NEED_FIRST_SWEEP = (0xFFF1);
    public static final int GD_NEED_SECOND_SWEEP = (0xFFF2);
    public static final int GD_NEED_THIRD_SWEEP = (0xFFF3);
    public static final int GD_NEED_RELEASE_FINGER = (0xFFF4);
    public static final int GD_TEMPLATE_NOT_EMPTY = (0x01);
    public static final int GD_TEMPLATE_EMPTY = (0x00);
    public static final int GD_DETECT_FINGER = (0x01);
    public static final int GD_NO_DETECT_FINGER = (0x00);
    public static final int GD_DOWNLOAD_SUCCESS = (0xA1);

    // Packet
    public static final int MAX_DATA_LEN = (610); /*512*/
    public static final int CMD_PACKET_LEN = (22);
    public static final int ST_COMMAND_LEN = (66);
    public static final int IMAGE_RECEIVE_UINT = (498);
    public static final int DATA_SPLIT_UNIT = (498);
    public static final int ID_USER_TEMPLATE_SIZE = (498);

    // Template
    public static final int GD_MAX_RECORD_COUNT = (5000);
    public static final int GD_TEMPLATE_SIZE = (570);
    public static final int GD_RECORD_SIZE = (GD_TEMPLATE_SIZE);// + 2)	// CkeckSum len = 2
    public static final int GD_MAX_RECORD_SIZE = (900);


    // uart
    public static final int UART_COMM_TIMEOUT = (1500);


    public byte[] m_abyPacket = new byte[64 * 1024];
    public byte[] m_abyPacket2 = new byte[MAX_DATA_LEN + 10];
    //--------------------------------------------------//

    private final Context mApplicationContext;
    private Context m_Context;


    // UART ch34xuartdriver
    private static final String UART_ACTION_USB_PERMISSION = "cn.wch.wchusbdriver.USB_PERMISSION";
    public static CH34xUARTDriver m_uartDriver;
    public byte[] m_pWriteBuffer;
    public byte[] m_pReadBuffer;
    public SerialBuf m_pSerialBuf;


    // Serial Port
    SerialPortFinder mSerialPortFinder;
    DispQueueThread DispQueue;
    SerialControl m_SerialPort;

    // Connection
    public byte m_nConnected;    // 0 : Not Connected, 1 : UART, 2 : USB, 3 : ttyUART


    public DevComm(Context context, Spinner p_spDevice) {

//    	LibDebugManage.DeleteLog();

        m_Context = context;
        mApplicationContext = context;

        // UART Driver Init
        m_uartDriver = new CH34xUARTDriver((UsbManager) mApplicationContext.getSystemService(Context.USB_SERVICE), m_Context, UART_ACTION_USB_PERMISSION);

        // Buffer Init
        m_nConnected = 0;
        m_pWriteBuffer = new byte[DevComm.MAX_DATA_LEN];
        m_pReadBuffer = new byte[DevComm.MAX_DATA_LEN];
        m_pSerialBuf = new SerialBuf();

        DispQueue = new DispQueueThread();
        DispQueue.start();
        m_SerialPort = new SerialControl();

        mSerialPortFinder = new SerialPortFinder();
        String[] entryValues = mSerialPortFinder.getAllDevicesPath();
        List<String> allDevices = new ArrayList<String>();
        allDevices.add("CH34xUART");
        for (int i = 0; i < entryValues.length; i++) {
            allDevices.add(entryValues[i]);
//			LibDebugManage.WriteLog2(entryValues[i]);
        }
        ArrayAdapter<String> aspnDevices = new ArrayAdapter<String>(m_Context, android.R.layout.simple_spinner_item, allDevices);
        p_spDevice.setAdapter(aspnDevices);
    }

    public int DevComm_Init(Context context, Spinner p_spDevice) {
        m_nConnected = 0;
        m_pSerialBuf.ClearBuf();

        String[] entryValues = mSerialPortFinder.getAllDevicesPath();
        List<String> allDevices = new ArrayList<String>();
        allDevices.add("USB");
        allDevices.add("CH34xUART");
        for (int i = 0; i < entryValues.length; i++) {
            allDevices.add(entryValues[i]);
        }
        ArrayAdapter<String> aspnDevices = new ArrayAdapter<String>(m_Context, android.R.layout.simple_spinner_item, allDevices);
        p_spDevice.setAdapter(aspnDevices);

        return 0;
    }

    public boolean IsInit() {
        if (m_nConnected == 0)
            return false;
        else if (m_nConnected == 1)
            return true;
        else
            return true;
    }

    public boolean OpenComm(String p_szDevice, int p_nBaudrate) {
        if (m_nConnected != 0)
            return false;

        if (p_szDevice == "CH34xUART") // UART
        {
            if (!m_uartDriver.ResumeUsbList()) {
                Toast.makeText(mApplicationContext, "Open UART device failed!", Toast.LENGTH_SHORT).show();
                m_uartDriver.CloseDevice();
                return false;
            } else {
                if (!m_uartDriver.UartInit()) {
                    Toast.makeText(mApplicationContext, "Initialize UART device failed!", Toast.LENGTH_SHORT).show();
                    Toast.makeText(mApplicationContext, "Open UART device failed!", Toast.LENGTH_SHORT).show();
                    return false;
                }

                if (!m_uartDriver.SetConfig(p_nBaudrate, (byte) 8, (byte) 1, (byte) 0, (byte) 0)) {
                    Toast.makeText(mApplicationContext, "Configuration UART device failed!", Toast.LENGTH_SHORT).show();
                    Toast.makeText(mApplicationContext, "Open UART device failed!", Toast.LENGTH_SHORT).show();
                    return false;
                }

                Toast.makeText(mApplicationContext, "Open UART device success!", Toast.LENGTH_SHORT).show();
                m_nConnected = 1;
                m_pSerialBuf.ClearBuf();
                new UART_ReadThread().start();
            }
        } else    // ttyUART
        {
            m_SerialPort.setPort(p_szDevice);
            m_SerialPort.setBaudRate(p_nBaudrate);
            try {
                m_SerialPort.open();
            } catch (SecurityException e) {
                Toast.makeText(mApplicationContext, "Open ttyUART device failed!", Toast.LENGTH_SHORT).show();
                return false;
            } catch (IOException e) {
                Toast.makeText(mApplicationContext, "Open ttyUART device failed!", Toast.LENGTH_SHORT).show();
                return false;
            } catch (InvalidParameterException e) {
                Toast.makeText(mApplicationContext, "Open ttyUART device failed!", Toast.LENGTH_SHORT).show();
                return false;
            }
            m_nConnected = 3;
        }

        return true;
    }

    public boolean CloseComm() {
        if (m_nConnected == 0) {
            return false;
        } else if (m_nConnected == 1)    // UART
        {
            m_uartDriver.CloseDevice();
            m_nConnected = 0;
        } else    // ttyUART
        {
            m_SerialPort.stopSend();
            m_SerialPort.close();
            m_nConnected = 0;
        }
        return true;
    }
    /************************************************************************/
    /************************************************************************/
    int Run_TestConnection() {
        boolean	w_bRet;

        InitPacket((short)CMD_TEST_CONNECTION_CODE, true);
        AddCheckSum(true);

        w_bRet = Send_Command((short)CMD_TEST_CONNECTION_CODE);
        if(!w_bRet)
        {
            return ERR_COMM_FAIL;
        }

        if (GetRetCode() != ERR_SUCCESS)
        {
            return ERR_FAIL;
        }

        return ERR_SUCCESS;
    }
    /************************************************************************/
    /************************************************************************/
    int Run_GetDeviceInfo() {
        return ERR_SUCCESS;
    }

    public boolean GetDeviceInformation(String[] deviceInfo) {
        int[] w_nRecvLen = new int[1];
        byte[] w_abyPCCmd = new byte[6];
        byte[] w_abyData = new byte[32];

        String w_strTmp;
        boolean w_bRet;

        Arrays.fill(w_abyPCCmd, (byte) 0);

        w_abyPCCmd[2] = 0x04;

        //  w_bRet = SendPackage(w_abyPCCmd, w_abyData);

        //Toast.makeText(mApplicationContext, "GetDeviceInformation, SendPackage ret = " + w_bRet, Toast.LENGTH_SHORT).show();

        /*if (!w_bRet) {
            return false;
        }*/

        w_bRet = RecvPackage(w_abyData, w_nRecvLen);

        //Toast.makeText(mApplicationContext, "GetDeviceInformation, RecvPackage : " + w_bRet, Toast.LENGTH_SHORT).show();

        if (!w_bRet) {
            return false;
        }

        w_strTmp = new String(w_abyData);
        deviceInfo[0] = w_strTmp;

        //Toast.makeText(mApplicationContext, "GetDeviceInformation, Recv Data : " + w_strTmp, Toast.LENGTH_SHORT).show();

        return true;
    }

  /*  private boolean SendPackage(byte[] pPCCmd, byte[] pData) {
        int nDataLen;

        pPCCmd[0] = (byte) 0xEF;
        pPCCmd[1] = 0x01;

        nDataLen = (int) ((((pPCCmd[5] & 0xFF) << 8) & 0x0000FF00) | (pPCCmd[4] & 0x000000FF));

        return m_usbBase.UsbSCSIWrite(pPCCmd, 6, pData, nDataLen, 5000);
    }*/

    private boolean RecvPackage(byte[] pData, int[] pLevRen) {
        int w_nLen;
        byte[] w_abyPCCmd = new byte[6];
        byte[] w_abyRespond = new byte[4];
        boolean w_bRet;

        w_abyPCCmd[0] = (byte) 0xEF;
        w_abyPCCmd[1] = 0x02;
        w_abyPCCmd[2] = 0;
        w_abyPCCmd[3] = 0;
        w_abyPCCmd[4] = 0;
        w_abyPCCmd[5] = 0;

        // receive data
        //w_nLen = (int)((w_abyRespond[3] << 8) | w_abyRespond[2]);
        w_nLen = (int) ((int) ((w_abyRespond[3] << 8) & 0x0000FF00) | (int) (w_abyRespond[2] & 0x000000FF));

        if (w_nLen > 0) {
            //w_nTime = SystemClock.elapsedRealtime();

            w_abyPCCmd[1] = 0x03;
            //  w_bRet = m_usbBase.UsbSCSIRead(w_abyPCCmd, 6, pData, w_nLen, 5000);

            //w_nTime = SystemClock.elapsedRealtime() - w_nTime;

          /*  if (!w_bRet)
                return false;*/

            pLevRen[0] = w_nLen;
        }

        return true;
    }

    /***************************************************************************
     * Get Return Code
     ***************************************************************************/
    public short GetRetCode() {
        return (short) ((int) ((m_abyPacket[7] << 8) & 0x0000FF00) | (int) (m_abyPacket[6] & 0x000000FF));
    }

    /***************************************************************************
     * Get Data Length
     ***************************************************************************/
    public short GetDataLen() {
        return (short) (((m_abyPacket[5] << 8) & 0x0000FF00) | (m_abyPacket[4] & 0x000000FF));
    }

    /***************************************************************************
     * Set Data Length
     ***************************************************************************/
    public void SetDataLen(short p_wDataLen) {
        m_abyPacket[4] = (byte) (p_wDataLen & 0xFF);
        m_abyPacket[5] = (byte) (((p_wDataLen & 0xFF00) >> 8) & 0xFF);
    }

    public void SetDataLen2(short p_wDataLen) {
        m_abyPacket2[4] = (byte) (p_wDataLen & 0xFF);
        m_abyPacket2[5] = (byte) (((p_wDataLen & 0xFF00) >> 8) & 0xFF);
    }

    /***************************************************************************
     * Set Command Data
     ***************************************************************************/
    public void SetCmdData(short p_wData, boolean p_bFirst) {
        if (p_bFirst) {
            m_abyPacket[6] = (byte) (p_wData & 0xFF);
            m_abyPacket[7] = (byte) (((p_wData & 0xFF00) >> 8) & 0xFF);
        } else {
            m_abyPacket[8] = (byte) (p_wData & 0xFF);
            m_abyPacket[9] = (byte) (((p_wData & 0xFF00) >> 8) & 0xFF);
        }
    }

    /***************************************************************************
     * Get Command Data
     ***************************************************************************/
    public short GetCmdData(boolean p_bFirst) {
        if (p_bFirst) {
            return (short) (((m_abyPacket[7] << 8) & 0x0000FF00) | (m_abyPacket[6] & 0x000000FF));
        } else {
            return (short) (((m_abyPacket[9] << 8) & 0x0000FF00) | (m_abyPacket[8] & 0x000000FF));
        }
    }

    /***************************************************************************
     * Get Data Packet Length
     ***************************************************************************/
    private short GetDataPacketLen() {
        return (short) (((m_abyPacket[5] << 8) & 0x0000FF00) | (m_abyPacket[4] & 0x000000FF) + 6);
    }

    /***************************************************************************
     * Make Packet
     ***************************************************************************/
    void InitPacket(short p_wCmd, boolean p_bCmdData) {
        memset(m_abyPacket, (byte) 0, CMD_PACKET_LEN);

        //g_pPacketBuffer->wPrefix = p_bCmdData?CMD_PREFIX_CODE:CMD_DATA_PREFIX_CODE;
        if (p_bCmdData) {
            m_abyPacket[0] = (byte) (CMD_PREFIX_CODE & 0xFF);
            m_abyPacket[1] = (byte) (((CMD_PREFIX_CODE & 0xFF00) >> 8) & 0xFF);
        } else {
            m_abyPacket[0] = (byte) (CMD_DATA_PREFIX_CODE & 0xFF);
            m_abyPacket[1] = (byte) (((CMD_DATA_PREFIX_CODE & 0xFF00) >> 8) & 0xFF);
        }

        //g_pPacketBuffer->wCMD_RCM = p_wCMD;
        m_abyPacket[2] = (byte) (p_wCmd & 0xFF);
        m_abyPacket[3] = (byte) (((p_wCmd & 0xFF00) >> 8) & 0xFF);
    }

    void InitPacket2(short p_wCmd, boolean p_bCmdData) {
        memset(m_abyPacket2, (byte) 0, CMD_PACKET_LEN);

        //g_pPacketBuffer->wPrefix = p_bCmdData?CMD_PREFIX_CODE:CMD_DATA_PREFIX_CODE;
        if (p_bCmdData) {
            m_abyPacket2[0] = (byte) (CMD_PREFIX_CODE & 0xFF);
            m_abyPacket2[1] = (byte) (((CMD_PREFIX_CODE & 0xFF00) >> 8) & 0xFF);
        } else {
            m_abyPacket2[0] = (byte) (CMD_DATA_PREFIX_CODE & 0xFF);
            m_abyPacket2[1] = (byte) (((CMD_DATA_PREFIX_CODE & 0xFF00) >> 8) & 0xFF);
        }

        //g_pPacketBuffer->wCMD_RCM = p_wCMD;
        m_abyPacket2[2] = (byte) (p_wCmd & 0xFF);
        m_abyPacket2[3] = (byte) (((p_wCmd & 0xFF00) >> 8) & 0xFF);
    }

    /***************************************************************************
     * Get CheckSum
     ***************************************************************************/
    short GetCheckSum(boolean p_bCmdData) {
        short w_wRet = 0;
        short w_nI = 0;

        w_wRet = 0;
        if (p_bCmdData) {
            for (w_nI = 0; w_nI < CMD_PACKET_LEN; w_nI++)
                w_wRet += (m_abyPacket[w_nI] & 0xFF);
        } else {
            for (w_nI = 0; w_nI < GetDataPacketLen(); w_nI++)
                w_wRet += (m_abyPacket[w_nI] & 0xFF);
        }
        return w_wRet;
    }

    /***************************************************************************
     * Set CheckSum
     ***************************************************************************/
    short AddCheckSum(boolean p_bCmdData) {
        short w_wRet = 0;
        short w_wLen = 0;
        int w_nI;

        if (p_bCmdData)
            w_wLen = CMD_PACKET_LEN;
        else
            w_wLen = GetDataPacketLen();

        w_wRet = 0;
        for (w_nI = 0; w_nI < w_wLen; w_nI++)
            w_wRet += (m_abyPacket[w_nI] & 0xFF);

        m_abyPacket[w_wLen] = (byte) (w_wRet & 0xFF);
        m_abyPacket[w_wLen + 1] = (byte) (((w_wRet & 0xFF00) >> 8) & 0xFF);

        return w_wRet;
    }

    short AddCheckSum2(boolean p_bCmdData) {
        short w_wRet = 0;
        short w_wLen = 0;
        int w_nI;

        if (p_bCmdData)
            w_wLen = CMD_PACKET_LEN;
        else
            w_wLen = GetDataPacketLen();

        w_wRet = 0;
        for (w_nI = 0; w_nI < w_wLen; w_nI++)
            w_wRet += (m_abyPacket2[w_nI] & 0xFF);

        m_abyPacket2[w_wLen] = (byte) (w_wRet & 0xFF);
        m_abyPacket2[w_wLen + 1] = (byte) (((w_wRet & 0xFF00) >> 8) & 0xFF);

        return w_wRet;
    }

    /***************************************************************************
     * Check Packet
     ***************************************************************************/
    boolean CheckReceive(short p_wPrefix, short p_wCmd) {
        short w_wCheckSum;
        short w_wTmpPrefix;
        short w_wTmpCmd;
        short w_wLen;

        // Check Prefix Code
        w_wTmpPrefix = (short) (((m_abyPacket[1] << 8) & 0x0000FF00) | (m_abyPacket[0] & 0x000000FF));
        w_wTmpCmd = (short) (((m_abyPacket[3] << 8) & 0x0000FF00) | (m_abyPacket[2] & 0x000000FF));

//    	if ( g_pPacketBuffer->wCMD_RCM != CMD_FP_CANCEL_CODE )
        {
            if ((p_wPrefix != w_wTmpPrefix) || (p_wCmd != w_wTmpCmd)) {
                LibDebugManage.WriteLog2(String.format("DevComm::CheckReceive() -> 673: Prefix Error = %02X, %02X, %02X, %02X", p_wPrefix, w_wTmpPrefix, p_wCmd, w_wTmpCmd));
                LibDebugManage.WriteBuffer(m_abyPacket, CMD_PACKET_LEN);
                return false;
            }
        }

        if (p_wPrefix == RCM_PREFIX_CODE)
            w_wLen = CMD_PACKET_LEN;
        else
            w_wLen = GetDataPacketLen();

        w_wCheckSum = (short) (((m_abyPacket[w_wLen + 1] << 8) & 0x0000FF00) | (m_abyPacket[w_wLen] & 0x000000FF));

        if (w_wCheckSum != GetCheckSum(p_wPrefix == RCM_PREFIX_CODE)) {
            LibDebugManage.WriteLog2(String.format("DevComm::CheckReceive() -> 688: CheckSum Error = %02X, %02X", w_wCheckSum, GetCheckSum(p_wPrefix == RCM_PREFIX_CODE)));
            LibDebugManage.WriteBuffer(m_abyPacket, w_wLen);
            return false;
        }
        return true;
    }

    //--------------------------- Send, Receive Communication Packet Functions ---------------------//
    public boolean Send_Command(short p_wCmd) {
        if ((m_nConnected == 1) || (m_nConnected == 3))
            return UART_SendCommand(p_wCmd);
        else
            return false;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean Send_DataPacket(short p_wCmd) {
        if ((m_nConnected == 1) || (m_nConnected == 3))
            return UART_SendDataPacket(p_wCmd);
        else
            return false;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean Receive_DataPacket(short p_wCmd) {
        if ((m_nConnected == 1) || (m_nConnected == 3))
            return UART_ReceiveDataPacket(p_wCmd);
        else
            return false;
    }


    //------------------------------------------ UART Functions -------------------------------------//
    public boolean UART_SendCommand(short p_wCmd) {
        int	w_nResult = 0;

        if (m_nConnected == 1)
        {
            w_nResult = m_uartDriver.WriteData(m_abyPacket, CMD_PACKET_LEN + 2);
            if(w_nResult < 0){
                return false;
            }
        }
        else if (m_nConnected == 3)
        {
            byte[] w_pData = new byte[CMD_PACKET_LEN + 2];
            System.arraycopy(m_abyPacket, 0, w_pData, 0, CMD_PACKET_LEN + 2);
            m_SerialPort.send(w_pData);
        }

        return UART_ReceiveAck(p_wCmd, true);
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_SendCommand2(short wCMD) {
        int w_nResult = 0;

        if (m_nConnected == 1) {
            w_nResult = m_uartDriver.WriteData(m_abyPacket2, CMD_PACKET_LEN + 2);
            if (w_nResult < 0) {
                return false;
            }
        } else if (m_nConnected == 3) {
            byte[] w_pData = new byte[CMD_PACKET_LEN + 2];
            System.arraycopy(m_abyPacket2, 0, w_pData, 0, CMD_PACKET_LEN + 2);
            m_SerialPort.send(w_pData);
        }

        return true;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_ReceiveAck(short p_wCmd, boolean p_bCmdData) {
        int w_nReadLen = 0;
        int w_nTotalLen = CMD_PACKET_LEN + 2;
        long w_nTime;
        boolean w_bFirst;
        int w_nRet;
        int w_nUARTReadLen;

        int w_dwTimeOut = UART_COMM_TIMEOUT;

        if (p_wCmd == CMD_VERIFY_CODE ||
                p_wCmd == CMD_IDENTIFY_CODE ||
                p_wCmd == CMD_IDENTIFY_FREE_CODE ||
                p_wCmd == CMD_ENROLL_CODE ||
                p_wCmd == CMD_ENROLL_ONETIME_CODE ||
                p_wCmd == CMD_CHANGE_TEMPLATE_CODE ||
                p_wCmd == CMD_FEATURE_OF_CAPTURED_FP_CODE ||
                p_wCmd == CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE ||
                p_wCmd == CMD_UP_IMAGE_CODE ||
                p_wCmd == CMD_FINGER_DETECT_CODE) {
            w_dwTimeOut = UART_COMM_TIMEOUT * 10;
        }

        w_nTime = System.currentTimeMillis();
        w_bFirst = true;

        while (w_nReadLen < w_nTotalLen)
        {
            if (System.currentTimeMillis() - w_nTime > w_dwTimeOut)
            {
                LibDebugManage.WriteLog2("DevComm::UART_ReceiveAck() -> 1091: TimeOut Error");
                m_pSerialBuf.ClearBuf();
                return false;
            }

            // check length
            w_nUARTReadLen = m_pSerialBuf.GetPushedSize();
            if (w_nUARTReadLen <= 0)
                continue;

            // fix buffer
            if (w_bFirst) {
                w_nRet = m_pSerialBuf.FixData((byte) 0xAA, (byte) 0x55);
                if (w_nRet < 0)
                    continue;

                w_bFirst = false;
            }

            if ((w_nTotalLen - w_nReadLen) < w_nUARTReadLen) {
                w_nRet = m_pSerialBuf.PopData(m_abyPacket, w_nReadLen, w_nTotalLen - w_nReadLen);
            }
            else {
                w_nRet = m_pSerialBuf.PopData(m_abyPacket, w_nReadLen, w_nUARTReadLen);
            }
            w_nReadLen += w_nRet;
            w_nTime = System.currentTimeMillis();
        }

        if (p_bCmdData)
            return CheckReceive((short)RCM_PREFIX_CODE, p_wCmd);
        else
            return CheckReceive((short)RCM_DATA_PREFIX_CODE, p_wCmd);
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_ReceiveAck2(short p_wCmd) {
        int w_nReadLen = 0;
        int w_nTotalLen = CMD_PACKET_LEN + 2;
        long w_nTime;
        boolean w_bFirst;
        int w_nUARTReadLen;
        int w_nRet;

        w_nTime = System.currentTimeMillis();
        w_bFirst = true;

        while (w_nReadLen < w_nTotalLen) {
            if (System.currentTimeMillis() - w_nTime > UART_COMM_TIMEOUT) {
                m_pSerialBuf.ClearBuf();
                return false;
            }

            // check length
            w_nUARTReadLen = m_pSerialBuf.GetPushedSize();
            if (w_nUARTReadLen <= 0)
                continue;

            // fix buffer
            if (w_bFirst) {
                w_nRet = m_pSerialBuf.FixData((byte) 0xAA, (byte) 0x55);
                if (w_nRet < 0)
                    continue;

                w_bFirst = false;
            }

            if ((w_nTotalLen - w_nReadLen) < w_nUARTReadLen) {
                w_nRet = m_pSerialBuf.PopData(m_abyPacket2, w_nReadLen, w_nTotalLen - w_nReadLen);
            } else {
                w_nRet = m_pSerialBuf.PopData(m_abyPacket2, w_nReadLen, w_nUARTReadLen);
            }
            w_nReadLen += w_nRet;
            w_nTime = System.currentTimeMillis();
        }

        return true;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_ReceiveDataAck(short p_wCmd) {
        int w_dwTimeOut = UART_COMM_TIMEOUT;

        if (p_wCmd == CMD_VERIFY_CODE ||
                p_wCmd == CMD_IDENTIFY_CODE ||
                p_wCmd == CMD_IDENTIFY_FREE_CODE ||
                p_wCmd == CMD_ENROLL_CODE ||
                p_wCmd == CMD_ENROLL_ONETIME_CODE ||
                p_wCmd == CMD_CHANGE_TEMPLATE_CODE ||
                p_wCmd == CMD_FEATURE_OF_CAPTURED_FP_CODE ||
                p_wCmd == CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE ||
                p_wCmd == CMD_UP_IMAGE_CODE ||
                p_wCmd == CMD_FINGER_DETECT_CODE) {
            w_dwTimeOut = UART_COMM_TIMEOUT * 10;
        }

        if (!UART_ReadDataN(m_abyPacket, 0, 6, w_dwTimeOut, true)) {
            LibDebugManage.WriteLog2("DevComm::UART_ReceiveDataAck() -> 1194: Receive Data Header Error");
            m_pSerialBuf.ClearBuf();
            return false;
        }

        if (!UART_ReadDataN(m_abyPacket, 6, GetDataLen() + 2, w_dwTimeOut, false)) {
            LibDebugManage.WriteLog2("DevComm::UART_ReceiveDataAck() -> 1200: Receive Data Error");
            m_pSerialBuf.ClearBuf();
            return false;
        }

        if (!CheckReceive((short) RCM_DATA_PREFIX_CODE, p_wCmd)) {
            LibDebugManage.WriteLog2("DevComm::UART_ReceiveDataAck() -> 1206: Check Packet Error");
            m_pSerialBuf.ClearBuf();
            return false;
        }

        return true;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_SendDataPacket(short p_wCmd) {
        int w_nSendCnt = 0;

        if (m_nConnected == 1) {
            w_nSendCnt = m_uartDriver.WriteData(m_abyPacket, GetDataLen() + 8);
            if (w_nSendCnt < 0)
                return false;
        } else if (m_nConnected == 3) {
            int w_nLen = GetDataLen() + 8;
            byte[] w_pData = new byte[w_nLen];
            System.arraycopy(m_abyPacket, 0, w_pData, 0, w_nLen);
            m_SerialPort.send(w_pData);
        }

        return UART_ReceiveDataAck(p_wCmd);
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_ReceiveDataPacket(short p_wCmd) {
        return UART_ReceiveDataAck(p_wCmd);
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean UART_ReceiveData(short p_wCmd, int p_nDataLen, byte[] p_pBuffer) {
        int w_nReceivedCnt;
        int w_wPacketDataLen = 0;

        for (w_nReceivedCnt = 0; w_nReceivedCnt < p_nDataLen; w_nReceivedCnt += w_wPacketDataLen) {
            w_wPacketDataLen = p_nDataLen - w_nReceivedCnt;
            if (w_wPacketDataLen > MAX_DATA_LEN) w_wPacketDataLen = MAX_DATA_LEN;
            if (UART_ReceiveDataPacket(p_wCmd) == false)
                return false;
            System.arraycopy(m_abyPacket, 8, p_pBuffer, w_nReceivedCnt, GetDataLen() + 4);
        }
        return true;
    }
    /***************************************************************************/
    /***************************************************************************/
    boolean UART_ReadDataN(byte[] p_pData, int p_nStart, int p_nLen, int p_nTimeOut, boolean p_bFix) {
        int w_nRecvLen, w_nTotalRecvLen;
        int w_nTmpLen;
        long w_nTime;
        int i;
        boolean w_bFirst;
        int w_nUARTReadLen = 0;
        int w_nRet;

        w_nRecvLen = p_nLen;
        w_nTotalRecvLen = 0;
        w_bFirst = true;
        w_nTime = System.currentTimeMillis();

        while (w_nTotalRecvLen < p_nLen) {
            if (System.currentTimeMillis() - w_nTime > p_nTimeOut) {
                LibDebugManage.WriteLog2("DevComm::UART_ReadDataN() -> 1279: Timeout Error");
                m_pSerialBuf.ClearBuf();
                return false;
            }

            // check length
            w_nUARTReadLen = m_pSerialBuf.GetPushedSize();
            if (w_nUARTReadLen <= 0)
                continue;

            // fix buffer
            if ((w_bFirst) && (p_bFix)) {
                w_nRet = m_pSerialBuf.FixData((byte) 0xA5, (byte) 0x5A);
                if (w_nRet < 0)
                    continue;
                w_bFirst = false;
            } else if (!p_bFix) {
                w_bFirst = false;
            }

            if (p_nLen - w_nTotalRecvLen < w_nUARTReadLen) {
                w_nRet = m_pSerialBuf.PopData(p_pData, p_nStart + w_nTotalRecvLen, p_nLen - w_nTotalRecvLen);
            } else {
                w_nRet = m_pSerialBuf.PopData(p_pData, p_nStart + w_nTotalRecvLen, w_nUARTReadLen);
            }
            w_nRecvLen -= w_nRet;
            w_nTotalRecvLen += w_nRet;
            w_nTime = System.currentTimeMillis();
        }

        return true;
    }

    /***************************************************************************/
    /***************************************************************************/
    public class UART_ReadThread extends Thread {

        public void run() {

            while (true) {
                int w_nUARTReadLen;
                int w_nRet;
                byte[] w_pBuf = new byte[MAX_DATA_LEN];

                if (m_nConnected != 1)
                    break;

                w_nUARTReadLen = m_uartDriver.ReadData(w_pBuf, DevComm.MAX_DATA_LEN);

                do {
                    w_nRet = m_pSerialBuf.PushData(w_pBuf, 0, w_nUARTReadLen);

                    if (w_nRet < 0) {
                        try {
                            Thread.sleep(10);
                        } catch (InterruptedException e) {
                            e.printStackTrace();
                            break;
                        }
                    }
                } while (w_nRet < 0);
            }

        }
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean memcmp(byte[] p1, byte[] p2, int nLen) {
        int i;

        for (i = 0; i < nLen; i++) {
            if (p1[i] != p2[i])
                return false;
        }

        return true;
    }

    public void memset(byte[] p1, byte nValue, int nLen) {
        Arrays.fill(p1, 0, nLen, nValue);
    }

    public void memcpy(byte[] p1, byte nValue, int nLen) {
        Arrays.fill(p1, 0, nLen, nValue);
    }

    public short MAKEWORD(byte low, byte high) {
        short s;
        s = (short) ((((high & 0x00FF) << 8) & 0x0000FF00) | (low & 0x000000FF));
        return s;
    }

    public byte LOBYTE(short s) {
        return (byte) (s & 0xFF);
    }

    public byte HIBYTE(short s) {
        return (byte) (((s & 0xFF00) >> 8) & 0xFF);
    }

    private class SerialControl extends SerialHelper {

        //		public SerialControl(String sPort, String sBaudRate){
//			super(sPort, sBaudRate);
//		}
        public SerialControl() {
        }

        @Override
        protected void onDataReceived(final ComBean ComRecData) {
            DispQueue.AddQueue(ComRecData);
        }
    }

    //----------------------------------------------------
    private class DispQueueThread extends Thread {
        private Queue<ComBean> QueueList = new LinkedList<ComBean>();

        @Override
        public void run() {
            super.run();
            while (!isInterrupted()) {
                while (true) {
                    final ComBean ComData;
                    int w_nRet;

                    if ((ComData = QueueList.poll()) == null)
                        break;

                    do {
                        w_nRet = m_pSerialBuf.PushData(ComData.bRec, 0, ComData.nSize);

                        if (w_nRet < 0) {
                            try {
                                Thread.sleep(10);
                            } catch (InterruptedException e) {
                                e.printStackTrace();
                                break;
                            }
                        }
                    } while (w_nRet < 0);
                }

                try {
                    Thread.sleep(10);
                } catch (InterruptedException e) {
                    e.printStackTrace();
                    break;
                }
            }
        }

        public synchronized void AddQueue(ComBean ComData) {
            QueueList.add(ComData);
        }
    }
}
