package com.dulles.odoo.utils;

public class SerialBuf {
    private int m_nReadPos = 0;
    private int m_nWritePos = 0;
    private int m_nPushedSize = 0;
    private byte[] m_pSerialBuf;

    public static int MAX_SERIAL_BUF_SIZE       = (85 * 1024);

    public SerialBuf() {
        m_pSerialBuf = new byte[MAX_SERIAL_BUF_SIZE];
        m_nReadPos = 0;
        m_nWritePos = 0;
    }

    public int GetPushedSize() {
        return m_nPushedSize;
    }

    public void ClearBuf() {
        m_nPushedSize = 0;
        m_nReadPos = 0;
        m_nWritePos = 0;
    }

    public int PushData(byte[] p_pBuf, int p_nOffset, int p_nSize) {
        int w_nTmpSize;

        if (m_nPushedSize + p_nSize > MAX_SERIAL_BUF_SIZE)
            return -1;

        if (m_nWritePos + p_nSize >= MAX_SERIAL_BUF_SIZE) {
            w_nTmpSize = MAX_SERIAL_BUF_SIZE - m_nWritePos;
            System.arraycopy(p_pBuf, p_nOffset, m_pSerialBuf, m_nWritePos, w_nTmpSize);
            System.arraycopy(p_pBuf, p_nOffset + w_nTmpSize, m_pSerialBuf, 0, p_nSize - w_nTmpSize);
            m_nWritePos = p_nSize - w_nTmpSize;
        }
        else {
            System.arraycopy(p_pBuf, p_nOffset, m_pSerialBuf, m_nWritePos, p_nSize);
            m_nWritePos += p_nSize;
        }
        m_nPushedSize += p_nSize;

        return p_nSize;
    }

    public int PopData(byte[] p_pBuf, int p_nOffset, int p_nSize) {
        int w_nReadSize = 0;
        int w_nPushedSize = 0;
        int w_nTmpSize;

        w_nPushedSize = m_nPushedSize;

        if (p_nSize > w_nPushedSize)
            w_nReadSize = w_nPushedSize;
        else
            w_nReadSize = p_nSize;

        if (m_nReadPos + w_nReadSize >= MAX_SERIAL_BUF_SIZE) {
            w_nTmpSize = MAX_SERIAL_BUF_SIZE - m_nReadPos;
            System.arraycopy(m_pSerialBuf, m_nReadPos, p_pBuf, p_nOffset, w_nTmpSize);
            System.arraycopy(m_pSerialBuf, 0, p_pBuf, p_nOffset + w_nTmpSize, w_nReadSize - w_nTmpSize);
            m_nReadPos = w_nReadSize - w_nTmpSize;
        }
        else {
            System.arraycopy(m_pSerialBuf, m_nReadPos, p_pBuf, p_nOffset, w_nReadSize);
            m_nReadPos += w_nReadSize;
        }
        m_nPushedSize -= w_nReadSize;

        return w_nReadSize;
    }

    public int FixData(byte p_nHead1, byte p_nHead2) {
        int i;
        int w_nPushedSize;

        w_nPushedSize = m_nPushedSize;
        for (i = 0; i < w_nPushedSize - 1; i++) {
            if ((m_pSerialBuf[m_nReadPos] == p_nHead1) && (m_pSerialBuf[m_nReadPos + 1] == p_nHead2)) {
                break;
            }
            m_nReadPos++;
            if (m_nReadPos >= MAX_SERIAL_BUF_SIZE)
                m_nReadPos = 0;
        }

        if (i >= w_nPushedSize - 1) {
            if (m_pSerialBuf[m_nReadPos] == p_nHead1) {
                m_nPushedSize -= w_nPushedSize - 1;
            }
            else {
                m_nPushedSize -= w_nPushedSize;
            }
            return -1;
        }

        m_nPushedSize -= i;

        return 0;
    }
}