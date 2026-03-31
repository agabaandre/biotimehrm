package com.dulles.odoo.utils;

import android.content.Context;
import android.graphics.Bitmap;
import android.graphics.BitmapFactory;
import android.os.Environment;
import android.os.SystemClock;
import android.util.Log;
import android.widget.ImageView;
import android.widget.Spinner;
import android.widget.TextView;

import java.io.File;
import java.io.FileInputStream;
import java.io.FileOutputStream;
import java.io.IOException;
import java.util.Arrays;

public class FingerLib {

    /**
     * Called when the activity is first created.
     */
    public static DevComm m_devComm;
    public static short m_dwCode;
    public static boolean m_bThreadWork;
    public static boolean m_bCmdDone;
    public static boolean m_bSendResult;

    boolean m_bParamGet;

    byte[] m_TemplateData = new byte[DevComm.GD_MAX_RECORD_SIZE];
    byte[] m_TemplateData2 = new byte[DevComm.GD_MAX_RECORD_SIZE];

    int m_nTemplateSize = 0;

    int m_nImgWidth, m_nImgHeight;
    byte[] m_binImage, m_bmpImage;
    int m_nImageBufOffset = 0;
    String m_strPost;

    TextView m_txtStatus;
    ImageView m_FpImageViewer;
    Runnable m_runEnableCtrl;

    private Context mContext;

    public FingerLib(Context context, TextView p_pStatusView, ImageView p_FpImageViewer, Runnable p_runEnableCtrl, Spinner p_spDevice) {
        m_bThreadWork = false;
        mContext = context;
        if (m_devComm == null) {
            m_devComm = new DevComm(context, p_spDevice);
        }

        m_binImage = new byte[1024 * 100];
        m_bmpImage = new byte[1024 * 100];

        m_txtStatus = p_pStatusView;
        m_FpImageViewer = p_FpImageViewer;
        m_runEnableCtrl = p_runEnableCtrl;
    }

    public int SZOEMHost_Lib_Init(Context context, TextView p_pStatusView, ImageView p_FpImageViewer, Runnable p_runEnableCtrl, Spinner p_spDevice) {
        m_bThreadWork = false;

        if (m_devComm == null) {
            m_devComm = new DevComm(context, p_spDevice);
        } else {
            m_devComm.DevComm_Init(context, p_spDevice);
        }

        if (m_binImage == null) {
            m_binImage = new byte[1024 * 100];
        }
        if (m_bmpImage == null) {
            m_bmpImage = new byte[1024 * 100];
        }

        m_txtStatus = p_pStatusView;
        m_FpImageViewer = p_FpImageViewer;
        m_runEnableCtrl = p_runEnableCtrl;

        return 0;
    }

	/*
	@Override
	public boolean onCreateOptionsMenu(Menu menu) {
		// Inflate the menu; this adds items to the action bar if it is present.
		getMenuInflater().inflate(R.menu.finger_activity, menu);
		return true;
	}
	*/

    public int OpenDevice(String p_szDevice, int p_nBaudrate) {
        Log.d("OpenDevice", "dev：" + p_szDevice + " BaudRate：" + p_nBaudrate);
        if (m_devComm != null) {
            if (!m_devComm.IsInit()) {
                if (!m_devComm.OpenComm(p_szDevice, p_nBaudrate)) {
                    m_txtStatus.setText("Failed init device!");
                    return 1;
                }
            }
            if (m_devComm.Run_TestConnection() == (short) DevComm.ERR_SUCCESS) {
                if (m_devComm.Run_GetDeviceInfo() == (short) DevComm.ERR_SUCCESS) {
                    m_txtStatus.setText("Open Device Success");
                    return 0;
                } else {
                    m_txtStatus.setText("Can not connect to device!");
                    return 1;
                }
            } else {
                m_txtStatus.setText("Can not connect to device!");
                m_devComm.CloseComm();
                return 1;
            }
        }
        return 1;
    }

    public int CloseDevice() {
        m_devComm.CloseComm();
        return 0;
    }

    public void StartSendThread() {
        m_bCmdDone = false;

        while (m_bThreadWork) {
            SystemClock.sleep(1);
        }

        new Thread(new Runnable() {
            public void run() {
                boolean w_blRet = false;
                short w_wPrefix = 0;

                m_bThreadWork = true;

                w_wPrefix =
                        (short) (((m_devComm.m_abyPacket[1] << 8) & 0x0000FF00) | (m_devComm.m_abyPacket[0]
                                & 0x000000FF));
                if (w_wPrefix == (short) (DevComm.CMD_PREFIX_CODE)) {
                    if (m_dwCode != (short) (DevComm.CMD_FP_CANCEL_CODE)) {
                        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                            w_blRet = m_devComm.UART_SendCommand(m_dwCode);
                        }
                    } else {
                        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                            w_blRet = m_devComm.UART_ReceiveAck(m_dwCode, true);
                        }
                    }
                } else if (w_wPrefix == (short) (DevComm.CMD_DATA_PREFIX_CODE)) {
                    if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                        w_blRet = m_devComm.UART_SendDataPacket(m_dwCode);
                    }
                } else {
                    if (m_dwCode != (short) (DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE)) {
                        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                            w_blRet = m_devComm.UART_ReceiveAck(m_dwCode, true);
                        }
                    } else {
                        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                            w_blRet =
                                    m_devComm.UART_ReceiveDataPacket((short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE);
                        }
                    }
                }
                m_bSendResult = w_blRet;
                m_txtStatus.post(procRspPacket);

                m_bThreadWork = false;
            }
        }).start();
    }

    private void Run_Command_NP(short p_wCmd) {
        //. Assemble command packet
        m_devComm.InitPacket(p_wCmd, true);
        m_devComm.AddCheckSum(true);

        m_dwCode = p_wCmd;
        StartSendThread();
    }

    private void Run_Command_1P(short p_wCmd, short p_wData) {
        //. Assemble command packet
        m_devComm.InitPacket(p_wCmd, true);
        m_devComm.SetDataLen((short) 0x0002);
        m_devComm.SetCmdData(p_wData, true);
        m_devComm.AddCheckSum(true);

        m_dwCode = p_wCmd;
        StartSendThread();
    }

    public int Run_CmdEnroll(int p_nTmpNo) {
        int w_nTemplateNo = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }
        w_nTemplateNo = p_nTmpNo;

        Run_Command_1P((short) DevComm.CMD_ENROLL_CODE, (short) w_nTemplateNo);

        return 0;
    }

    public int Run_CmdIdentify() {
        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        Run_Command_NP((short) DevComm.CMD_IDENTIFY_CODE);

        return 0;
    }

    public int Run_CmdIdentifyFree() {
        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        Run_Command_NP((short) DevComm.CMD_IDENTIFY_FREE_CODE);

        return 0;
    }

    public int Run_CmdVerify(int p_nTmpNo) {
        int w_nTemplateNo = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;
        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        Run_Command_1P((short) DevComm.CMD_VERIFY_CODE, (short) w_nTemplateNo);

        return 0;
    }

    public int Run_CmdEnrollOneTime(int p_nTmpNo) {
        int w_nTemplateNo = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;
        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        Run_Command_1P((short) DevComm.CMD_ENROLL_ONETIME_CODE, (short) w_nTemplateNo);

        return 0;
    }

    public int Run_CmdChangeTemplate(int p_nTmpNo) {
        int w_nTemplateNo = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;

        Run_Command_1P((short) DevComm.CMD_CHANGE_TEMPLATE_CODE, (short) w_nTemplateNo);

        return 0;
    }

    public int Run_CmdDeleteID(int p_nTmpNo) {
        int w_nTemplateNo = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;
        Run_Command_1P((short) DevComm.CMD_CLEAR_TEMPLATE_CODE, (short) w_nTemplateNo);

        return 0;
    }

    public int Run_CmdDeleteAll() {
        Run_Command_NP((short) DevComm.CMD_CLEAR_ALLTEMPLATE_CODE);

        return 0;
    }

    public int Run_CmdGetEmptyID() {
        Run_Command_NP((short) DevComm.CMD_GET_EMPTY_ID_CODE);

        return 0;
    }

    public int Run_CmdGetUserCount() {
        Run_Command_NP((short) DevComm.CMD_GET_ENROLL_COUNT_CODE);

        return 0;
    }

    public int Run_CmdGetBrokenTemplate() {
        Run_Command_NP((short) DevComm.CMD_GET_BROKEN_TEMPLATE_CODE);

        return 0;
    }

    public int Run_CmdReadTemplate(int p_nTmpNo) {
        boolean w_blRet = false;
        int w_nTemplateNo = 0;
        int w_nLen = 0;
        int w_nBufOffset = 0;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;
        m_devComm.memset(m_TemplateData, (byte) 0, DevComm.GD_MAX_RECORD_SIZE);

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_READ_TEMPLATE_CODE, true);
        m_devComm.SetDataLen((short) 0x0002);
        m_devComm.SetCmdData((short) w_nTemplateNo, true);
        m_devComm.AddCheckSum(true);

        m_dwCode = DevComm.CMD_READ_TEMPLATE_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_READ_TEMPLATE_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }
        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_READ_TEMPLATE_CODE);
            return 1;
        }

        if (m_devComm.GetCmdData(false) == DevComm.GD_TEMPLATE_SIZE) {
            w_blRet = m_devComm.Receive_DataPacket((short) DevComm.CMD_READ_TEMPLATE_CODE);
            w_nLen = DevComm.GD_TEMPLATE_SIZE;
            System.arraycopy(m_devComm.m_abyPacket, 10, m_TemplateData, 0, DevComm.GD_TEMPLATE_SIZE);
        } else {
            w_nLen = m_devComm.GetCmdData(false);
            w_nBufOffset = 0;

            while (true) {
                w_blRet = m_devComm.Receive_DataPacket((short) DevComm.CMD_READ_TEMPLATE_CODE);

                if (w_blRet == false) {
                    break;
                } else {
                    if (m_devComm.GetRetCode() == DevComm.ERR_SUCCESS) {
                        if (m_devComm.GetDataLen() > (DevComm.DATA_SPLIT_UNIT + 4)) {
                            m_devComm.SetCmdData((short) DevComm.ERR_FAIL, true);
                            m_devComm.SetCmdData((short) DevComm.ERR_INVALID_PARAM, false);
                            w_blRet = false;
                            break;
                        } else {
                            System.arraycopy(m_devComm.m_abyPacket, 10, m_TemplateData, w_nBufOffset,
                                    m_devComm.GetDataLen() - 4);
                            w_nBufOffset = w_nBufOffset + (m_devComm.GetDataLen() - 4);
                            if (w_nBufOffset == w_nLen) {
                                break;
                            }
                        }
                    } else {
                        w_blRet = false;
                        break;
                    }
                }
            }
        }

        if (w_blRet == false) {
            return 2;
        } else {
            m_nTemplateSize = w_nLen;
            DisplayResponsePacket((short) DevComm.CMD_READ_TEMPLATE_CODE);
        }

        return 0;
    }

    public int Run_CmdWriteTemplate(int p_nTmpNo) {
        boolean w_blRet = false;
        int w_nTemplateNo = 0;
        int i, n, r;

        //. Check inputed template no and Read template file
        if (CheckInputTemplateNo(p_nTmpNo) == false || ReadTemplateFile(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, true);
        m_devComm.SetDataLen((short) 0x0002);
        m_devComm.SetCmdData((short) m_nTemplateSize, true);
        m_devComm.AddCheckSum(true);

        //. Send command packet to target
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }

        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
            return 1;
        }

        if ((m_nTemplateSize == DevComm.GD_RECORD_SIZE) || (m_nTemplateSize
                == DevComm.ID_USER_TEMPLATE_SIZE)) {
            //. Assemble data packet
            m_devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
            m_devComm.SetDataLen((short) (m_nTemplateSize + 2));
            m_devComm.SetCmdData((short) w_nTemplateNo, true);
            System.arraycopy(m_TemplateData, 0, m_devComm.m_abyPacket, 8, m_nTemplateSize);
            m_devComm.AddCheckSum(false);

            //. Send data packet to target
            w_blRet = m_devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
            if (w_blRet == false) {
                return 2;
            }
        } else {
            n = m_nTemplateSize / DevComm.DATA_SPLIT_UNIT;
            r = m_nTemplateSize % DevComm.DATA_SPLIT_UNIT;

            for (i = 0; i < n; i++) {
                //. Assemble data packet
                m_devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
                m_devComm.SetDataLen((short) (DevComm.DATA_SPLIT_UNIT + 4));
                m_devComm.SetCmdData((short) w_nTemplateNo, true);
                m_devComm.SetCmdData((short) DevComm.DATA_SPLIT_UNIT, false);
                System.arraycopy(m_TemplateData, i * DevComm.DATA_SPLIT_UNIT, m_devComm.m_abyPacket, 10,
                        DevComm.DATA_SPLIT_UNIT);
                m_devComm.AddCheckSum(false);

                //. Send data packet to target
                w_blRet = m_devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
                if (w_blRet == false) {
                    return 2;
                }
            }

            if (r > 0) {
                m_devComm.InitPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE, false);
                m_devComm.SetDataLen((short) (r + 4));
                m_devComm.SetCmdData((short) w_nTemplateNo, true);
                m_devComm.SetCmdData((short) (r & 0xFFFF), false);
                System.arraycopy(m_TemplateData, i * DevComm.DATA_SPLIT_UNIT, m_devComm.m_abyPacket, 10, r);
                m_devComm.AddCheckSum(false);

                //. Send data packet to target
                w_blRet = m_devComm.Send_DataPacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
                if (w_blRet == false) {
                    return 2;
                }
            }
        }

        //. Display response packet
        DisplayResponsePacket((short) DevComm.CMD_WRITE_TEMPLATE_CODE);
        return 0;
    }

    public int Run_CmdSetParameter() {
        int w_nMode = 0, w_nIndex = 0, w_nValue = 0;

        if (w_nMode == 0) {
            m_bParamGet = true;
        } else {
            m_bParamGet = false;
        }

        m_devComm.InitPacket((short) DevComm.CMD_SET_PARAMETER_CODE, true);
        m_devComm.SetDataLen((short) 0x0006);
        m_devComm.m_abyPacket[6] = (byte) (w_nMode & 0xFF);
        m_devComm.m_abyPacket[7] = (byte) (w_nIndex & 0xFF);
        m_devComm.m_abyPacket[8] = (byte) (w_nValue & 0xFF);
        m_devComm.m_abyPacket[9] = (byte) (((w_nValue & 0x0000FF00) >> 8) & 0xFF);
        m_devComm.m_abyPacket[10] = (byte) (((w_nValue & 0x00FF0000) >> 16) & 0xFF);
        m_devComm.m_abyPacket[11] = (byte) (((w_nValue & 0xFF000000) >> 24) & 0xFF);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_SET_PARAMETER_CODE;
        StartSendThread();

        return 0;
    }

    public int Run_CmdGetFwVersion() {
        Run_Command_NP((short) DevComm.CMD_GET_FW_VERSION_CODE);

        return 0;
    }

    public int Run_CmdDetectFinger() {
        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        Run_Command_NP((short) DevComm.CMD_FINGER_DETECT_CODE);

        return 0;
    }

    public int Run_CmdSetDevPass(String p_szPassword) {
        int w_nI;

        if (p_szPassword.length() != 0 && p_szPassword.length() != 14) {
            m_strPost = "Invalid Device Password. \nPlease input valid device password(length=14)!";
            m_txtStatus.setText(m_strPost);
            return 1;
        }

        m_devComm.InitPacket((short) DevComm.CMD_SET_DEVPASS_CODE, true);
        m_devComm.SetDataLen((short) 0x000E); // 14
        if (p_szPassword.length() == 0) {
            for (w_nI = 0; w_nI < 14; w_nI++)
                m_devComm.m_abyPacket[6 + w_nI] = 0x00;
        } else {
            for (w_nI = 0; w_nI < 14; w_nI++)
                m_devComm.m_abyPacket[6 + w_nI] = (byte) (p_szPassword.charAt(w_nI) & 0xFF);
        }
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_SET_DEVPASS_CODE;
        StartSendThread();

        return 0;
    }

    public int Run_CmdVerifyPass(String p_szPassword) {
        int w_nI;

        if (p_szPassword.length() != 14) {
            m_strPost = "Invalid Device Password. \nPlease input valid device password(length=14)!";
            m_txtStatus.setText(m_strPost);
            return 1;
        }

        m_devComm.InitPacket((short) DevComm.CMD_VERIFY_DEVPASS_CODE, true);
        m_devComm.SetDataLen((short) 0x000E); // 14
        for (w_nI = 0; w_nI < 14; w_nI++)
            m_devComm.m_abyPacket[6 + w_nI] = (byte) (p_szPassword.charAt(w_nI) & 0xFF);
        //    	System.arraycopy(m_editDevPassword.toString().toCharArray(), 0, m_devComm.m_abyPacket, 6, 14);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_VERIFY_DEVPASS_CODE;
        StartSendThread();

        return 0;
    }

    public int Run_CmdExitDevPass() {
        Run_Command_NP((short) DevComm.CMD_EXIT_DEVPASS_CODE);

        return 0;
    }

    public int Run_CmdAdjustSensor() {
        m_strPost = "Adjusting sensor...";
        m_txtStatus.setText(m_strPost);

        Run_Command_NP((short) DevComm.CMD_ADJUST_SENSOR_CODE);

        return 0;
    }

    public int Run_CmdEnterStandByMode() {
        m_strPost = "Enter Standby Mode...";
        m_txtStatus.setText(m_strPost);

        Run_Command_NP((short) DevComm.CMD_ENTERSTANDBY_CODE);

        return 0;
    }

    public int Run_CmdCancel() {

        new Thread(new Runnable() {
            //    		@Override
            public void run() {
                boolean w_bRet;

                //. Init Packet
                m_devComm.InitPacket2((short) DevComm.CMD_FP_CANCEL_CODE, true);
                m_devComm.SetDataLen2((short) 0x00);
                m_devComm.AddCheckSum2(true);

                //. Send Packet
                w_bRet = false;
                if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                    w_bRet = m_devComm.UART_SendCommand2((short) DevComm.CMD_FP_CANCEL_CODE);
                }
                if (w_bRet != true) {
                    m_strPost = "Result : Cancel Send Failed\r\n";
                    m_txtStatus.post(runShowStatus);
                    m_txtStatus.post(m_runEnableCtrl);
                    return;
                }

                //. Wait while processing cmd exit
                while (m_bCmdDone == false) {
                    SystemClock.sleep(1);
                }

                if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                    w_bRet = m_devComm.UART_ReceiveAck2((short) DevComm.CMD_FP_CANCEL_CODE);
                }
                if (w_bRet == true) {
                    m_strPost = "Result : FP Cancel Success.";
                } else {
                    m_strPost = "Result : Cancel Failed\r\n";
                }

                m_txtStatus.post(runShowStatus);
                m_txtStatus.post(m_runEnableCtrl);
            }
        }).start();

        return 0;
    }

    public int Run_CmdGetFeatureOfCapturedFP() {
        new Thread(new Runnable() {
            public void run() {
                boolean w_blRet;
                int w_nLen = 0;
                int w_nBufOffset = 0;

                m_devComm.memset(m_TemplateData, (byte) 0, DevComm.GD_RECORD_SIZE);

                //. Assemble command packet
                m_devComm.InitPacket((short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE, true);
                m_devComm.AddCheckSum(true);

                m_strPost = "Input your finger";
                m_txtStatus.post(runShowStatus);

                m_dwCode = DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE;
                w_blRet = m_devComm.Send_Command((short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE);
                if (w_blRet == false) {
                    Run_CmdCancel();
                    SystemClock.sleep(100);
                    m_devComm.m_pSerialBuf.ClearBuf();
                    return;
                }
                if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
                    m_bSendResult = w_blRet;
                    m_txtStatus.post(procRspPacket);
                    return;
                }

                // Receive template data
                if (m_devComm.GetCmdData(false) == DevComm.GD_TEMPLATE_SIZE) {
                    w_blRet = m_devComm.Receive_DataPacket((short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE);
                    w_nLen = DevComm.GD_TEMPLATE_SIZE;
                    System.arraycopy(m_devComm.m_abyPacket, 8, m_TemplateData, 0, DevComm.GD_TEMPLATE_SIZE);
                } else {
                    w_nLen = m_devComm.GetCmdData(false);
                    w_nBufOffset = 0;

                    while (true) {
                        w_blRet = m_devComm.Receive_DataPacket((short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE);

                        if (w_blRet == false) {
                            break;
                        } else {
                            if (m_devComm.GetRetCode() == DevComm.ERR_SUCCESS) {
                                if (m_devComm.GetDataLen() > (DevComm.DATA_SPLIT_UNIT + 2)) {
                                    m_devComm.SetCmdData((short) DevComm.ERR_FAIL, true);
                                    m_devComm.SetCmdData((short) DevComm.ERR_INVALID_PARAM, false);
                                    w_blRet = false;
                                    break;
                                } else {
                                    System.arraycopy(m_devComm.m_abyPacket, 8, m_TemplateData, w_nBufOffset,
                                            m_devComm.GetDataLen() - 2);
                                    w_nBufOffset = w_nBufOffset + (m_devComm.GetDataLen() - 2);
                                    if (w_nBufOffset == w_nLen) {
                                        break;
                                    }
                                }
                            } else {
                                w_blRet = false;
                                break;
                            }
                        }
                    }
                }
                if (w_blRet) {
                    m_nTemplateSize = w_nLen;
                }
                m_bSendResult = w_blRet;
                m_txtStatus.post(procRspPacket);
            }
        }).start();

        return 0;
    }

    public int Run_CmdIdentifyWithTemplate2() {
        boolean w_blRet = false;

        //. Read template file
        if (ReadTemplateFile(0) == false || ReadTemplateFile2() == false) {
            return 1;
        }

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE, true);
        m_devComm.SetDataLen((short) 0x0002);
        m_devComm.SetCmdData((short) DevComm.GD_RECORD_SIZE, true);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }
        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE);
            return 1;
        }

        m_strPost = "Input your finger";
        m_txtStatus.setText(m_strPost);

        //. Assemble data packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE, false);
        m_devComm.SetDataLen((short) (DevComm.GD_RECORD_SIZE + 2));
        m_devComm.m_abyPacket[6] = 0;  // Template Index
        m_devComm.m_abyPacket[7] = 0;  // Mode (0 : Set Buffer, 1 : Identify)
        System.arraycopy(m_TemplateData, 0, m_devComm.m_abyPacket, 8, DevComm.GD_RECORD_SIZE);
        m_devComm.AddCheckSum(false);

        //. Send data packet to target
        w_blRet = m_devComm.Send_DataPacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }

        if (m_devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE);
            return 1;
        }

        //. Assemble data packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE, false);
        m_devComm.SetDataLen((short) (DevComm.GD_RECORD_SIZE + 2));
        m_devComm.m_abyPacket[6] = 1;  // Template Index
        m_devComm.m_abyPacket[7] = 1;  // Mode (0 : Set Buffer, 1 : Set Buffer and Identify)
        System.arraycopy(m_TemplateData2, 0, m_devComm.m_abyPacket, 8, DevComm.GD_RECORD_SIZE);
        m_devComm.AddCheckSum(false);

        //. Send data packet to target
        StartSendThread();
        SystemClock.sleep(500);

        return 0;
    }

    public int Run_CmdUpImage() {
        m_bCmdDone = false;
        m_nImageBufOffset = 0;

        while (m_bThreadWork) {
            SystemClock.sleep(1);
        }

        new Thread(new Runnable() {
            //    		@Override
            public void run() {
                boolean w_blRet = false;

                m_bThreadWork = true;

                m_strPost = "Input your finger";
                m_txtStatus.post(runShowStatus);

                //. Assemble command packet
                m_devComm.InitPacket((short) DevComm.CMD_UP_IMAGE_CODE, true);
                m_devComm.SetDataLen((short) 0x00);
                m_devComm.AddCheckSum(true);

                m_dwCode = (short) DevComm.CMD_UP_IMAGE_CODE;

                w_blRet = m_devComm.Send_Command((short) DevComm.CMD_UP_IMAGE_CODE);
                if (w_blRet == false) {
                    LibDebugManage.WriteLog2("SZ_OEMHost_Lib::Run_CmdUpImage() -> 887: SendCommand Error");

                    m_bSendResult = w_blRet;
                    m_txtStatus.post(procRspPacket);
                    m_bThreadWork = false;
                    Run_CmdCancel();
                    SystemClock.sleep(100);
                    m_devComm.m_pSerialBuf.ClearBuf();
                    return; // goto
                }

                if (m_devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
                    LibDebugManage.WriteLog2("SZ_OEMHost_Lib::Run_CmdUpImage() -> 900: SendCommand Failed");

                    m_bSendResult = w_blRet;
                    m_txtStatus.post(procRspPacket);
                    m_bThreadWork = false;
                    return; // goto
                }

                m_nImgWidth = (short) (((m_devComm.m_abyPacket[9] << 8) & 0x0000FF00) | (m_devComm.m_abyPacket[8]
                        & 0x000000FF));
                m_nImgHeight = (short) (((m_devComm.m_abyPacket[11] << 8) & 0x0000FF00) | (m_devComm.m_abyPacket[10]
                        & 0x000000FF));

                //  if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
                while (true) {
                    w_blRet = m_devComm.UART_ReceiveDataPacket((short) DevComm.CMD_UP_IMAGE_CODE);

                    if (w_blRet == false) {
                        LibDebugManage.WriteLog2(
                                "SZ_OEMHost_Lib::Run_CmdUpImage() -> 919: UART_ReceiveDataPacket Error");

                        m_bSendResult = w_blRet;
                        m_txtStatus.post(procRspPacket);
                        m_bThreadWork = false;
                        Run_CmdCancel();
                        SystemClock.sleep(100);
                        m_devComm.m_pSerialBuf.ClearBuf();
                        return; // goto
                    } else {
                        if (m_devComm.GetRetCode() == DevComm.ERR_SUCCESS) {
                            if (m_devComm.GetDataLen() > (DevComm.IMAGE_RECEIVE_UINT + 2)) {
                                LibDebugManage.WriteLog2(
                                        "SZ_OEMHost_Lib::Run_CmdUpImage() -> 935: UART_ReceiveDataPacket Size Error");

                                m_bSendResult = w_blRet;
                                m_txtStatus.post(procRspPacket);
                                m_bThreadWork = false;
                                return; // goto
                            } else {
                                if (m_nImageBufOffset == 0) {
                                    m_strPost = "Uploading image...";
                                    m_txtStatus.post(runShowStatus);
                                }

                                System.arraycopy(m_devComm.m_abyPacket, 8, m_binImage, m_nImageBufOffset, m_devComm.GetDataLen() - 2);
                                m_nImageBufOffset = m_nImageBufOffset + (m_devComm.GetDataLen() - 2);

                                Log.d("m_nImageBufOffset", "m_nImageBufOffset " + m_nImgWidth + " " + m_nImgHeight);
                                // 73728
                                if (m_nImageBufOffset == m_nImgWidth * m_nImgHeight) {
                                    m_bSendResult = w_blRet;
                                    m_txtStatus.post(procRspPacket);
                                    m_bThreadWork = false;
                                    return; // goto
                                }
                            }
                        } else {
                            LibDebugManage.WriteLog2(
                                    "SZ_OEMHost_Lib::Run_CmdUpImage() -> 964: UART_ReceiveDataPacket Failed");

                            m_bSendResult = w_blRet;
                            m_txtStatus.post(procRspPacket);
                            m_bThreadWork = false;
                            return; // goto
                        }


                    }
                }

//                } else {
//                    w_blRet = m_devComm.USB_ReceiveImage(m_binImage, m_nImgWidth * m_nImgHeight);
//                }

//                m_bSendResult = w_blRet;
//                m_txtStatus.post(procRspPacket);
//
//                m_bThreadWork = false;
            }
        }).start();

        return 0;
    }

    public int Run_CmdIdentifyWithImage() {
        int i, r, n, w_nImgSize;
        boolean w_blRet = false;

        //. Read image file
        if (!ReadImage(m_binImage)) {
            return 1;
        }

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE, true);
        m_devComm.SetDataLen((short) 0x0004);
        m_devComm.SetCmdData((short) m_nImgWidth, true);
        m_devComm.SetCmdData((short) m_nImgHeight, false);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }

        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE);
            return 1;
        }

        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
            m_strPost = "Downloading image...";
            m_txtStatus.post(runShowStatus);

            w_nImgSize = m_nImgWidth * m_nImgHeight;
            n = w_nImgSize / DevComm.IMAGE_RECEIVE_UINT;
            r = w_nImgSize % DevComm.IMAGE_RECEIVE_UINT;

            for (i = 0; i < n; i++) {
                //. Assemble data packet
                m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE, false);
                m_devComm.SetDataLen((short) (0x0004 + DevComm.GD_RECORD_SIZE));
                m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) i);
                m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) i);
                m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) DevComm.IMAGE_RECEIVE_UINT);
                m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) DevComm.IMAGE_RECEIVE_UINT);
                System.arraycopy(m_binImage, i * DevComm.IMAGE_RECEIVE_UINT, m_devComm.m_abyPacket, 10,
                        DevComm.IMAGE_RECEIVE_UINT);
                m_devComm.AddCheckSum(false);

                w_blRet = m_devComm.UART_SendDataPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE);
                if (!w_blRet) {
                    CloseDevice();
                    return 1;
                }

                m_strPost =
                        String.format("%d%%...", (i + 1) * DevComm.IMAGE_RECEIVE_UINT * 100 / w_nImgSize);
                m_txtStatus.post(runShowStatus);
            }

            if (r > 0) {
                m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE, false);
                m_devComm.SetDataLen((short) (0x0004 + DevComm.GD_RECORD_SIZE));
                m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) i);
                m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) i);
                m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) r);
                m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) r);
                System.arraycopy(m_binImage, i * DevComm.IMAGE_RECEIVE_UINT, m_devComm.m_abyPacket, 10, r);
                m_devComm.AddCheckSum(false);

                w_blRet = m_devComm.UART_SendDataPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE);
                if (!w_blRet) {
                    CloseDevice();
                    return 1;
                }
            }

            m_strPost = "100%...";
            m_txtStatus.post(runShowStatus);
        }

        // Identify
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE, false);
        m_devComm.SetDataLen((short) 0x0004);
        m_devComm.m_abyPacket[6] = 0;
        m_devComm.m_abyPacket[7] = 0;
        m_devComm.m_abyPacket[8] = 0;
        m_devComm.m_abyPacket[9] = 0;
        m_devComm.AddCheckSum(false);

        StartSendThread();
        SystemClock.sleep(200);

        return 0;
    }

    public int Run_CmdVerifyWithImage(int p_nTmpNo) {
        int i, r, n, w_nImgSize, w_nTemplateNo;
        boolean w_blRet = false;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        //. Read image file
        if (!ReadImage(m_binImage)) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE, true);
        m_devComm.SetDataLen((short) 0x0006);
        m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) w_nTemplateNo);
        m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) w_nTemplateNo);
        m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) m_nImgWidth);
        m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) m_nImgWidth);
        m_devComm.m_abyPacket[10] = m_devComm.LOBYTE((short) m_nImgHeight);
        m_devComm.m_abyPacket[11] = m_devComm.HIBYTE((short) m_nImgHeight);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }

        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE);
            return 1;
        }

        if ((m_devComm.m_nConnected == 1) || (m_devComm.m_nConnected == 3)) {
            m_strPost = "Downloading image...";
            m_txtStatus.post(runShowStatus);

            w_nImgSize = m_nImgWidth * m_nImgHeight;
            n = w_nImgSize / DevComm.IMAGE_RECEIVE_UINT;
            r = w_nImgSize % DevComm.IMAGE_RECEIVE_UINT;

            for (i = 0; i < n; i++) {
                //. Assemble data packet
                m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE, false);
                m_devComm.SetDataLen((short) (0x0004 + DevComm.GD_RECORD_SIZE));
                m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) i);
                m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) i);
                m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) DevComm.IMAGE_RECEIVE_UINT);
                m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) DevComm.IMAGE_RECEIVE_UINT);
                System.arraycopy(m_binImage, i * DevComm.IMAGE_RECEIVE_UINT, m_devComm.m_abyPacket, 10,
                        DevComm.IMAGE_RECEIVE_UINT);
                m_devComm.AddCheckSum(false);

                w_blRet = m_devComm.UART_SendDataPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE);
                if (!w_blRet) {
                    CloseDevice();
                }

                m_strPost =
                        String.format("%d%%...", (i + 1) * DevComm.IMAGE_RECEIVE_UINT * 100 / w_nImgSize);
                runShowStatus.run();
                m_txtStatus.post(runShowStatus);
            }

            if (r > 0) {
                m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE, false);
                m_devComm.SetDataLen((short) (0x0004 + DevComm.GD_RECORD_SIZE));
                m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) i);
                m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) i);
                m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) r);
                m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) r);
                System.arraycopy(m_binImage, i * DevComm.IMAGE_RECEIVE_UINT, m_devComm.m_abyPacket, 10, r);
                m_devComm.AddCheckSum(false);

                w_blRet = m_devComm.UART_SendDataPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE);
                if (!w_blRet) {
                    CloseDevice();
                }
            }

            m_strPost = "100%...";
            m_txtStatus.post(runShowStatus);
        }

        // Identify
        m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE, false);
        m_devComm.SetDataLen((short) 0x0004);
        m_devComm.m_abyPacket[6] = 0;
        m_devComm.m_abyPacket[7] = 0;
        m_devComm.m_abyPacket[8] = 0;
        m_devComm.m_abyPacket[9] = 0;
        m_devComm.AddCheckSum(false);

        StartSendThread();
        SystemClock.sleep(200);

        return 0;
    }

    public int Run_CmdVerifyWithDownTmpl(int p_nTmpNo) {
        int w_nTemplateNo;
        boolean w_blRet = false;

        //. Check inputed template no
        if (CheckInputTemplateNo(p_nTmpNo) == false) {
            return 1;
        }

        w_nTemplateNo = p_nTmpNo;

        //. Read template file
        w_blRet = ReadTemplateFile(0);
        if (w_blRet == false) {
            return 1;
        }
        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE, true);
        m_devComm.SetDataLen((short) 0x0004);
        m_devComm.m_abyPacket[6] = m_devComm.LOBYTE((short) w_nTemplateNo);
        m_devComm.m_abyPacket[7] = m_devComm.HIBYTE((short) w_nTemplateNo);
        m_devComm.m_abyPacket[8] = m_devComm.LOBYTE((short) DevComm.GD_RECORD_SIZE);
        m_devComm.m_abyPacket[9] = m_devComm.HIBYTE((short) DevComm.GD_RECORD_SIZE);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }
        if (m_devComm.GetRetCode() != DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE);
            return 1;
        }

        //. Assemble data packet
        m_devComm.InitPacket((short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE, false);
        m_devComm.SetDataLen((short) DevComm.GD_RECORD_SIZE);
        System.arraycopy(m_TemplateData, 0, m_devComm.m_abyPacket, 6, DevComm.GD_RECORD_SIZE);
        m_devComm.AddCheckSum(false);

        StartSendThread();
        SystemClock.sleep(200);

        return 0;
    }

    public int Run_CmdIdentifyWithDownTmpl() {
        boolean w_blRet = false;

        //. Read template file
        w_blRet = ReadTemplateFile(0);
        if (w_blRet == false) {
            return 1;
        }

        //. Assemble command packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE, true);
        m_devComm.SetDataLen((short) 0x0002);
        m_devComm.SetCmdData((short) DevComm.GD_RECORD_SIZE, true);
        m_devComm.AddCheckSum(true);

        m_dwCode = (short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE;
        w_blRet = m_devComm.Send_Command((short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE);
        if (w_blRet == false) {
            CloseDevice();
            return 1;
        }
        if (m_devComm.GetRetCode() != (short) DevComm.ERR_SUCCESS) {
            DisplayResponsePacket((short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE);
            return 1;
        }

        //. Assemble data packet
        m_devComm.InitPacket((short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE, false);
        m_devComm.SetDataLen((short) DevComm.GD_RECORD_SIZE);
        System.arraycopy(m_TemplateData, 0, m_devComm.m_abyPacket, 6, DevComm.GD_RECORD_SIZE);
        m_devComm.AddCheckSum(false);

        StartSendThread();
        SystemClock.sleep(200);

        return 0;
    }
    /***************************************************************************/
    /***************************************************************************/
    public void Run_CmdEnterISPMode() {
        Run_Command_NP((short) DevComm.CMD_ENTER_ISPMODE_CODE);
    }

    public boolean CheckInputTemplateNo(int p_nTmpNo) {
        if (p_nTmpNo > (DevComm.GD_MAX_RECORD_COUNT) || p_nTmpNo < 1) {
            m_txtStatus.setText(
                    "Please input correct user id(1~" + (short) DevComm.GD_MAX_RECORD_COUNT + ")");
            return false;
        }

        return true;
    }

    //    private void StopOperation(){
    //        m_strPost = "Canceled";
    //        m_FpImageViewer.post(runShowStatus);
    //        m_FpImageViewer.post(runEnableCtrl);
    //    }

    private void DisplayResponsePacket(short p_nCode) {
        short w_nRet;
        short w_nData, w_nData2, w_nSize/*, w_wPrefix*/;

        m_strPost = "";
        m_txtStatus.setText(m_strPost);

        //    	w_wPrefix = m_devComm.MAKEWORD(m_devComm.m_abyPacket[0], m_devComm.m_abyPacket[1]);
        w_nRet = m_devComm.MAKEWORD(m_devComm.m_abyPacket[6], m_devComm.m_abyPacket[7]);
        w_nData = m_devComm.MAKEWORD(m_devComm.m_abyPacket[8], m_devComm.m_abyPacket[9]);
        w_nData2 = m_devComm.MAKEWORD(m_devComm.m_abyPacket[10], m_devComm.m_abyPacket[11]);
        w_nSize = m_devComm.MAKEWORD(m_devComm.m_abyPacket[4], m_devComm.m_abyPacket[5]);

        switch (p_nCode) {
            case (short) DevComm.CMD_CLEAR_TEMPLATE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nTemplate No : %d", w_nData);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_UP_IMAGE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Receive Image Success");
                    m_txtStatus.post(runDrawImage);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_READ_TEMPLATE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nTemplate No : %d", w_nData);
                    WriteTemplateFile(w_nData, m_TemplateData);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_WRITE_TEMPLATE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nTemplate No : %d", w_nData);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);

                    if (w_nData == DevComm.ERR_DUPLICATION_ID) {
                        m_strPost += String.format(" %d.", w_nData2);
                    }
                }
                break;

            case (short) DevComm.CMD_GET_EMPTY_ID_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nEmpty ID : %d", w_nData);
                    //    				m_editUserID.setText(String.format("%d", w_nData));
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_GET_ENROLL_COUNT_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nEnroll Count : %d", w_nData);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_VERIFY_WITH_DOWN_TMPL_CODE:
            case (short) DevComm.CMD_IDENTIFY_WITH_DOWN_TMPL_CODE:
            case (short) DevComm.CMD_VERIFY_CODE:
            case (short) DevComm.CMD_IDENTIFY_CODE:
            case (short) DevComm.CMD_IDENTIFY_FREE_CODE:
            case (short) DevComm.CMD_ENROLL_CODE:
            case (short) DevComm.CMD_ENROLL_ONETIME_CODE:
            case (short) DevComm.CMD_CHANGE_TEMPLATE_CODE:
            case (short) DevComm.CMD_IDENTIFY_WITH_IMAGE_CODE:
            case (short) DevComm.CMD_VERIFY_WITH_IMAGE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    switch (w_nData) {
                        case (short) DevComm.GD_NEED_RELEASE_FINGER:
                            m_strPost = "Release your finger";
                            break;
                        case (short) DevComm.GD_NEED_FIRST_SWEEP:
                            m_strPost = "Input your finger";
                            break;
                        case (short) DevComm.GD_NEED_SECOND_SWEEP:
                            m_strPost = "Two More";
                            break;
                        case (short) DevComm.GD_NEED_THIRD_SWEEP:
                            m_strPost = "One More";
                            break;
                        default:
                            //    					if( p_nCode != (short)DevComm.CMD_IDENTIFY_FREE_CODE || m_devComm.LOBYTE(w_nData) == DevComm.ERR_FP_CANCEL )
                            //    						m_btnCloseDevice.setEnabled(true);
                            m_strPost = String.format("Result : Success\r\nTemplate No : %d", w_nData);
                            break;
                    }
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                    if (m_devComm.LOBYTE(w_nData) == DevComm.ERR_BAD_QUALITY) {
                        m_strPost += "\r\nAgain... !";
                    } else {
                        if (w_nData == DevComm.ERR_DUPLICATION_ID) {
                            m_strPost += String.format(" %d.", w_nData2);
                        }
                    }
                    //    				if( p_nCode != (short)DevComm.CMD_IDENTIFY_FREE_CODE || m_devComm.LOBYTE(w_nData) == DevComm.ERR_FP_CANCEL ||
                    //						m_devComm.LOBYTE(w_nData) == DevComm.ERR_ALL_TMPL_EMPTY || m_devComm.LOBYTE(w_nData) == DevComm.ERR_INVALID_OPERATION_MODE ||
                    //    					m_devComm.LOBYTE(w_nData) == DevComm.ERR_NOT_AUTHORIZED)
                    //    					m_btnCloseDevice.setEnabled(true);
                }
                break;

            case (short) DevComm.CMD_CLEAR_ALLTEMPLATE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nCleared Template Count : %d", w_nData);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_GET_BROKEN_TEMPLATE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format(
                            "Result : Success\r\nBroken Template Count : %d\r\nFirst Broken Template ID : %d",
                            w_nData, w_nData2);
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_VERIFY_DEVPASS_CODE:
            case (short) DevComm.CMD_SET_DEVPASS_CODE:
            case (short) DevComm.CMD_EXIT_DEVPASS_CODE:
                //    		case (short)DevComm.CMD_SET_COMMNAD_VALID_FLAG_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success.");
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_SET_PARAMETER_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    if (m_bParamGet) {
                        m_strPost = String.format("Result : Success\r\nParameter Value = %d",
                                (m_devComm.m_abyPacket[8] & 0x000000FF)
                                        + ((m_devComm.m_abyPacket[9] << 8)
                                        & 0x0000FF00)
                                        + ((m_devComm.m_abyPacket[10] << 16) & 0x00FF0000)
                                        + ((m_devComm.m_abyPacket[24] << 8) & 0xFF000000));
                    } else {
                        m_strPost = String.format("Result : Success\r\n");
                    }
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_ADJUST_SENSOR_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Adjust Success");
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_ENTERSTANDBY_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Enter Standby Mode Success");
                } else {
                    m_strPost = String.format("Result : Enter Standby Mode Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_GET_FW_VERSION_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nFirmware Version: %d.%d",
                            m_devComm.LOBYTE(w_nData), m_devComm.HIBYTE(w_nData));
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_FINGER_DETECT_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    if (w_nData == (short) DevComm.GD_DETECT_FINGER) {
                        m_strPost = String.format("Finger Detected.");
                    } else if (w_nData == (short) DevComm.GD_NO_DETECT_FINGER) {
                        m_strPost = String.format("Finger not Detected.");
                    }
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_FP_CANCEL_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : FP Cancel Success.");
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_FEATURE_OF_CAPTURED_FP_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    if (w_nSize != (short) DevComm.GD_RECORD_SIZE + 2) {
                        m_strPost = String.format("Result : Fail\r\nCommunication Error");
                    } else {
                        System.arraycopy(m_devComm.m_abyPacket, 8, m_TemplateData, 0,
                                (short) DevComm.GD_RECORD_SIZE);
                        m_strPost = String.format("Result : Success");
                    }
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.CMD_IDENTIFY_TEMPLATE_WITH_FP_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    if (m_devComm.LOBYTE(w_nData) == (short) DevComm.GD_DOWNLOAD_SUCCESS) {
                        m_strPost = String.format("Result : Download Success\r\nInput your finger");
                        m_txtStatus.setText(m_strPost);
                        return;
                    } else {
                        m_strPost = String.format("Result : Identify OK.");
                        m_txtStatus.setText(m_strPost);
                    }
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            case (short) DevComm.RCM_INCORRECT_COMMAND_CODE:
                m_strPost = String.format("Received incorrect command !");
                break;

            case (short) DevComm.CMD_ENTER_ISPMODE_CODE:
                if (w_nRet == (short) DevComm.ERR_SUCCESS) {
                    m_strPost = String.format("Result : Success\r\nRunning ISP. Can you programming.");
                } else {
                    m_strPost = String.format("Result : Fail\r\n");
                    m_strPost += GetErrorMsg(w_nData);
                }
                break;

            default:
                break;
        }

        if ((p_nCode == (short) DevComm.CMD_IDENTIFY_FREE_CODE)) {
            if (w_nRet == (short) DevComm.ERR_SUCCESS ||
                    m_devComm.LOBYTE(w_nData) != DevComm.ERR_NOT_AUTHORIZED &&
                            m_devComm.LOBYTE(w_nData) != DevComm.ERR_FP_CANCEL &&
                            m_devComm.LOBYTE(w_nData) != DevComm.ERR_INVALID_OPERATION_MODE &&
                            m_devComm.LOBYTE(w_nData) != DevComm.ERR_ALL_TMPL_EMPTY) {
                m_txtStatus.setText(m_strPost);
                m_devComm.memset(m_devComm.m_abyPacket, (byte) 0, 64 * 1024);
                StartSendThread();
                return;
            }
        }
        if ((p_nCode == (short) DevComm.CMD_ENROLL_CODE) ||
                (p_nCode == (short) DevComm.CMD_CHANGE_TEMPLATE_CODE)) {
            switch (w_nData) {
                case (short) DevComm.GD_NEED_RELEASE_FINGER:
                case (short) DevComm.GD_NEED_FIRST_SWEEP:
                case (short) DevComm.GD_NEED_SECOND_SWEEP:
                case (short) DevComm.GD_NEED_THIRD_SWEEP:
                case (short) DevComm.ERR_BAD_QUALITY:
                    m_txtStatus.setText(m_strPost);
                    m_devComm.memset(m_devComm.m_abyPacket, (byte) 0, 64 * 1024);
                    StartSendThread();
                    return;
                default:
                    break;
            }
        }
        if ((p_nCode == (short) DevComm.CMD_ENROLL_ONETIME_CODE) || (p_nCode
                == (short) DevComm.CMD_VERIFY_CODE) ||
                (p_nCode == (short) DevComm.CMD_IDENTIFY_CODE) || (p_nCode
                == (short) DevComm.CMD_IDENTIFY_FREE_CODE)) {
            switch (w_nData) {
                case (short) DevComm.GD_NEED_RELEASE_FINGER:
                    m_txtStatus.setText(m_strPost);
                    m_devComm.memset(m_devComm.m_abyPacket, (byte) 0, 64 * 1024);
                    StartSendThread();
                    return;
                default:
                    break;
            }
        }

        m_txtStatus.post(m_runEnableCtrl);

        m_txtStatus.setText(m_strPost);

        m_devComm.memset(m_devComm.m_abyPacket, (byte) 0, 64 * 1024);
        m_bCmdDone = true;
    }

    private String GetErrorMsg(short p_wErrorCode) {
        String w_ErrMsg;
        switch (p_wErrorCode & 0xFF) {
            case DevComm.ERR_VERIFY:
                w_ErrMsg = "Verify NG";
                break;
            case DevComm.ERR_IDENTIFY:
                w_ErrMsg = "Identify NG";
                break;
            case DevComm.ERR_EMPTY_ID_NOEXIST:
                w_ErrMsg = "Empty Template no Exist";
                break;
            case DevComm.ERR_BROKEN_ID_NOEXIST:
                w_ErrMsg = "Broken Template no Exist";
                break;
            case DevComm.ERR_TMPL_NOT_EMPTY:
                w_ErrMsg = "Template of this ID Already Exist";
                break;
            case DevComm.ERR_TMPL_EMPTY:
                w_ErrMsg = "This Template is Already Empty";
                break;
            case DevComm.ERR_INVALID_TMPL_NO:
                w_ErrMsg = "Invalid Template No";
                break;
            case DevComm.ERR_ALL_TMPL_EMPTY:
                w_ErrMsg = "All Templates are Empty";
                break;
            case DevComm.ERR_INVALID_TMPL_DATA:
                w_ErrMsg = "Invalid Template Data";
                break;
            case DevComm.ERR_DUPLICATION_ID:
                //    		w_ErrMsg.Format("Duplicated ID : %d.", HIBYTE(p_wErrorCode));
                w_ErrMsg = "Duplicated ID : ";
                break;
            case DevComm.ERR_BAD_QUALITY:
                w_ErrMsg = "Bad Quality Image";
                break;
            case DevComm.ERR_SMALL_LINES:
                w_ErrMsg = "Small line Image";
                break;
            case DevComm.ERR_TOO_FAST:
                w_ErrMsg = "Too fast swiping";
                break;
            case DevComm.ERR_TIME_OUT:
                w_ErrMsg = "Time Out";
                break;
            case DevComm.ERR_GENERALIZE:
                w_ErrMsg = "Fail to Generalize";
                break;
            case DevComm.ERR_NOT_AUTHORIZED:
                w_ErrMsg = "Device not authorized.";
                break;
            case DevComm.ERR_EXCEPTION:
                w_ErrMsg = "Exception Error ";
                break;
            case DevComm.ERR_MEMORY:
                w_ErrMsg = "Memory Error ";
                break;
            case DevComm.ERR_INVALID_PARAM:
                w_ErrMsg = "Invalid Parameter";
                break;
            case DevComm.ERR_NO_RELEASE:
                w_ErrMsg = "No Release Finger Fail";
                break;
            case DevComm.ERR_INTERNAL:
                w_ErrMsg = "Internal Error.";
                break;
            case DevComm.ERR_FP_CANCEL:
                w_ErrMsg = "Canceled.";
                break;
            case DevComm.ERR_INVALID_OPERATION_MODE:
                w_ErrMsg = "Invalid Operation Mode";
                break;
            case DevComm.ERR_NOT_SET_PWD:
                w_ErrMsg = "Password was not set.";
                break;
            case DevComm.ERR_FP_NOT_DETECTED:
                w_ErrMsg = "Finger is not detected.";
                break;
            case DevComm.ERR_ADJUST_SENSOR:
                w_ErrMsg = "Failed to adjust sensor.";
                break;
            default:
                w_ErrMsg = "Fail";
                break;
        }
        return w_ErrMsg;
    }

    public boolean ReadTemplateFile(int p_nUserID) {
        // Load Template from (mnt/sdcard/sz_template)
        int w_nLen;
        int i;
        short w_nChkSum = 0;
        short w_nCalcChkSum = 0;

        // Open Template File
        String w_szSaveDirPath =
                Environment.getExternalStorageDirectory().getAbsolutePath() + "/sz_template";
        File w_fpTemplate = new File(w_szSaveDirPath + "/" + String.valueOf(p_nUserID) + ".fpt");
        if (!w_fpTemplate.exists()) {
            // Show Save Path
            m_strPost = "Can't load " + w_szSaveDirPath + "/" + String.valueOf(p_nUserID) + ".fpt";
            return false;
        }

        // Get File Length
        w_nLen = (int) w_fpTemplate.length();
        if (w_nLen > DevComm.GD_MAX_RECORD_SIZE) {
            m_strPost = "Invalid template file.";
            return false;
        }

        // Load Template Data
        FileInputStream w_fiTemplate = null;
        try {
            w_fiTemplate = new FileInputStream(w_fpTemplate);
            w_fiTemplate.read(m_TemplateData, 0, w_nLen);
            w_fiTemplate.close();
        } catch (Exception e) {
            e.printStackTrace();
            return false;
        }

        // Set Template Length
        if (w_nLen == DevComm.GD_RECORD_SIZE) {
            for (i = 0; i < w_nLen - 2; i++) {
                w_nChkSum += (m_TemplateData[i] & 0xFF);
            }
            w_nCalcChkSum =
                    m_devComm.MAKEWORD((byte) m_TemplateData[w_nLen - 2], (byte) m_TemplateData[w_nLen - 1]);
            if (w_nChkSum != w_nCalcChkSum) {
                m_strPost = "Invalid template data.";
                return false;
            }
        }

        m_nTemplateSize = w_nLen;

        return true;
    }

    public boolean ReadTemplateFile2() {
        //    	int				i = 0;
        //    	WORD			w_nChkSum = 0, w_nCaclChkSum = 0;
        //    	CFile			w_clsFile;
        //    	CFileDialog		w_dlgOpen(TRUE , _T("First Template") , NULL, OFN_HIDEREADONLY, "Template File(*.fpt)|*.fpt|");
        //
        //    	if (w_dlgOpen.DoModal() == IDOK)
        //    	{
        //    		if (!w_dlgOpen.GetPathName().IsEmpty())
        //    		{
        //    			if (!w_clsFile.Open(w_dlgOpen.GetPathName(), CFile::modeRead))
        //    			{
        //    				AfxMessageBox(_T("Failed to read template!"));
        //    				return FALSE;
        //    			}
        //
        //    			if (w_clsFile.GetLength() != GD_RECORD_SIZE)
        //    			{
        //    				AfxMessageBox(_T("Invalid template data !"));
        //    				return FALSE;
        //    			}
        //
        //    			w_clsFile.Read(m_TemplateData2, GD_RECORD_SIZE);
        //    			w_clsFile.Close();
        //
        //    			for (i = 0; i < GD_TEMPLATE_SIZE - 2 ; i++){
        //    				w_nChkSum += m_TemplateData2[i];
        //    			}
        //    			w_nCaclChkSum = MAKEWORD(m_TemplateData2[GD_TEMPLATE_SIZE - 2], m_TemplateData2[GD_TEMPLATE_SIZE - 1]);
        //
        //    			if (w_nChkSum != w_nCaclChkSum)
        //    			{
        //    				AfxMessageBox(_T("Invalid template data !"));
        //    				return FALSE;
        //    			}
        //
        //    			return TRUE;
        //    		}
        //    	}

        return false;
    }
    /***************************************************************************/
    /***************************************************************************/
    public boolean WriteTemplateFile(int p_nUserID, byte[] pTemplate) {
        // Save Template to (mnt/sdcard/sz_template)
        // Create Directory
        String w_szSaveDirPath =
                Environment.getExternalStorageDirectory().getAbsolutePath() + "/sz_template";
        File w_fpDir = new File(w_szSaveDirPath);
        if (!w_fpDir.exists()) {
            w_fpDir.mkdirs();
        }

        // Create Template File
        File w_fpTemplate = new File(w_szSaveDirPath + "/" + String.valueOf(p_nUserID) + ".fpt");
        if (!w_fpTemplate.exists()) {
            try {
                w_fpTemplate.createNewFile();
            } catch (IOException e) {
                e.printStackTrace();
                return false;
            }
        }

        // Save Template Data
        FileOutputStream w_foTemplate = null;
        try {
            w_foTemplate = new FileOutputStream(w_fpTemplate);
            w_foTemplate.write(pTemplate, 0, m_nTemplateSize);
            w_foTemplate.close();

            // Show Save Path
            m_strPost +=
                    "\nSaved file path = " + w_szSaveDirPath + "/" + String.valueOf(p_nUserID) + ".fpt";
        } catch (Exception e) {
            e.printStackTrace();
            return false;
        }

        return true;
    }

    public boolean ReadImage(byte[] pImage) {
        //    	int				i = 0, w_nWidth, w_nHeight;
        //    	BYTE*			w_pBuf;
        //    	WORD			w_nChkSum = 0, w_nCaclChkSum = 0;
        //    	CString			w_strPath;
        //    	CFile			w_clsFile;
        //    	CFileDialog		w_dlgOpen(TRUE , _T("FingerPrint Image") , NULL, OFN_HIDEREADONLY, "Image File(*.bmp)|*.bmp|");
        //
        //    	if (w_dlgOpen.DoModal() == IDOK)
        //    	{
        //    		if (!w_dlgOpen.GetPathName().IsEmpty())
        //    		{
        //    			w_strPath = w_dlgOpen.GetPathName();
        //
        //    			if (FCLoadImage(w_strPath.GetBuffer(0), &w_pBuf, &w_nWidth, &w_nHeight, 0) != 0)
        //    			{
        //    				AfxMessageBox(_T("Load Fail!!!"));
        //    				return FALSE;
        //    			}
        //
        //    			if ( !((w_nWidth == 242 && w_nHeight == 266) ||
        //    				   (w_nWidth == 202 && w_nHeight == 258) ||
        //    				   (w_nWidth == 256 && w_nHeight == 288)))
        //    			{
        //    				AfxMessageBox(_T("Image size is not correct!"));
        //    				goto l_exit;
        //    			}
        //
        //    			g_nImageWidth = w_nWidth;
        //    			g_nImageHeight = w_nHeight;
        //
        //    			memcpy(pImage, w_pBuf, w_nWidth*w_nHeight);
        //
        //    			delete[] w_pBuf;
        //
        //    			return TRUE;
        //    		}
        //    	}
        //
        //    	return FALSE;
        //
        //    l_exit:
        //
        //    	delete[] w_pBuf;

        if ((m_nImgWidth > 0) && (m_nImgHeight > 0)) {
            return true;
        }

        return false;
    }

    public boolean WriteImage(byte[] pImage) {
        return true;
    }

    Runnable procRspPacket = new Runnable() {
        public void run() {
            short w_wCmd;

            if (m_bSendResult == false) {
                m_strPost = "Fail to receive response! \n Please check the connection to target.";
                m_txtStatus.setText(m_strPost);

                m_txtStatus.post(m_runEnableCtrl);

                m_bCmdDone = true;

                return;
            }
            //. Display response packet
            w_wCmd = (short) (((m_devComm.m_abyPacket[3] << 8) & 0x0000FF00) | (m_devComm.m_abyPacket[2]
                    & 0x000000FF));
            DisplayResponsePacket(w_wCmd);
        }
    };

    Runnable runShowStatus = new Runnable() {
        public void run() {
            m_txtStatus.setText(m_strPost);
        }
    };

    Runnable runDrawImage = new Runnable() {
        public void run() {
            int nSize;

            MakeBMPBuf(m_binImage, m_bmpImage, m_nImgWidth, m_nImgHeight);

            if ((m_nImgWidth % 4) != 0) {
                nSize = m_nImgWidth + (4 - (m_nImgWidth % 4));
            } else {
                nSize = m_nImgWidth;
            }

            nSize = 1078 + nSize * m_nImgHeight;

            //            DebugManage.WriteBmp(m_bmpImage, nSize);

            Bitmap image = BitmapFactory.decodeByteArray(m_bmpImage, 0, nSize);
            boolean isSaveSuccess = ImgUtils.saveImageToGallery(mContext, image);
            String path = "";
            if (isSaveSuccess) {
                m_strPost = "save image success.";
                path = (String) SPUtils.get(mContext, "path", "");
            } else {
                m_strPost = "save image error.";
            }
            m_txtStatus.setText(m_strPost + " imgPath：" + path);
            m_FpImageViewer.setImageBitmap(image);
        }
    };

    private void MakeBMPBuf(byte[] Input, byte[] Output, int iImageX, int iImageY) {
        Log.d("MakeBMPBuf", "MakeBMPBuf: " + iImageX + " " + iImageY);
        byte[] w_bTemp = new byte[4];
        byte[] head = new byte[1078];
        byte[] head2 = {
                /***************************/
                //file header
                0x42, 0x4d,//file type
                //0x36,0x6c,0x01,0x00, //file size***
                0x0, 0x0, 0x0, 0x00, //file size***
                0x00, 0x00, //reserved
                0x00, 0x00,//reserved
                0x36, 0x4, 0x00, 0x00,//head byte***
                /***************************/
                //infoheader
                0x28, 0x00, 0x00, 0x00,//struct size

                //0x00,0x01,0x00,0x00,//map width***
                0x00, 0x00, 0x0, 0x00,//map width***
                //0x68,0x01,0x00,0x00,//map height***
                0x00, 0x00, 0x00, 0x00,//map height***

                0x01, 0x00,//must be 1
                0x08, 0x00,//color count***
                0x00, 0x00, 0x00, 0x00, //compression
                //0x00,0x68,0x01,0x00,//data size***
                0x00, 0x00, 0x00, 0x00,//data size***
                0x00, 0x00, 0x00, 0x00, //dpix
                0x00, 0x00, 0x00, 0x00, //dpiy
                0x00, 0x00, 0x00, 0x00,//color used
                0x00, 0x00, 0x00, 0x00,//color important
        };

        int i, j, num, iImageStep;

        Arrays.fill(w_bTemp, (byte) 0);

        System.arraycopy(head2, 0, head, 0, head2.length);

        if ((iImageX % 4) != 0) {
            iImageStep = iImageX + (4 - (iImageX % 4));
        } else {
            iImageStep = iImageX;
        }

        num = iImageX;
        head[18] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[19] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[20] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[21] = (byte) (num & (byte) 0xFF);

        num = iImageY;
        head[22] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[23] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[24] = (byte) (num & (byte) 0xFF);
        num = num >> 8;
        head[25] = (byte) (num & (byte) 0xFF);

        j = 0;
        for (i = 54; i < 1078; i = i + 4) {
            head[i] = head[i + 1] = head[i + 2] = (byte) j;
            head[i + 3] = 0;
            j++;
        }

        System.arraycopy(head, 0, Output, 0, 1078);

        if (iImageStep == iImageX) {
            for (i = 0; i < iImageY; i++) {
                System.arraycopy(Input, i * iImageX, Output, 1078 + i * iImageX, iImageX);
            }
        } else {
            iImageStep = iImageStep - iImageX;

            for (i = 0; i < iImageY; i++) {
                System.arraycopy(Input, i * iImageX, Output, 1078 + i * (iImageX + iImageStep), iImageX);
                System.arraycopy(w_bTemp, 0, Output, 1078 + i * (iImageX + iImageStep) + iImageX,
                        iImageStep);
            }
        }
    }

}
