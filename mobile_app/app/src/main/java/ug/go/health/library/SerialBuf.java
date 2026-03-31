package ug.go.health.library;

/**
 * Circular serial buffer matching the original manufacturer implementation.
 * Provides thread-safe push/pop and header-alignment (FixData) used by DevComm.
 */
public class SerialBuf {
    private int m_nReadPos = 0;
    private int m_nWritePos = 0;
    private int m_nPushedSize = 0;
    private final byte[] m_pSerialBuf;

    public static final int MAX_SERIAL_BUF_SIZE = 85 * 1024;

    public SerialBuf() {
        m_pSerialBuf = new byte[MAX_SERIAL_BUF_SIZE];
    }

    public synchronized int GetPushedSize() {
        return m_nPushedSize;
    }

    public synchronized void ClearBuf() {
        m_nPushedSize = 0;
        m_nReadPos = 0;
        m_nWritePos = 0;
    }

    public synchronized int PushData(byte[] p_pBuf, int p_nOffset, int p_nSize) {
        if (m_nPushedSize + p_nSize > MAX_SERIAL_BUF_SIZE)
            return -1;

        if (m_nWritePos + p_nSize >= MAX_SERIAL_BUF_SIZE) {
            int w_nTmpSize = MAX_SERIAL_BUF_SIZE - m_nWritePos;
            System.arraycopy(p_pBuf, p_nOffset, m_pSerialBuf, m_nWritePos, w_nTmpSize);
            System.arraycopy(p_pBuf, p_nOffset + w_nTmpSize, m_pSerialBuf, 0, p_nSize - w_nTmpSize);
            m_nWritePos = p_nSize - w_nTmpSize;
        } else {
            System.arraycopy(p_pBuf, p_nOffset, m_pSerialBuf, m_nWritePos, p_nSize);
            m_nWritePos += p_nSize;
        }
        m_nPushedSize += p_nSize;
        return p_nSize;
    }

    public synchronized int PopData(byte[] p_pBuf, int p_nOffset, int p_nSize) {
        int w_nReadSize = Math.min(p_nSize, m_nPushedSize);

        if (m_nReadPos + w_nReadSize >= MAX_SERIAL_BUF_SIZE) {
            int w_nTmpSize = MAX_SERIAL_BUF_SIZE - m_nReadPos;
            System.arraycopy(m_pSerialBuf, m_nReadPos, p_pBuf, p_nOffset, w_nTmpSize);
            System.arraycopy(m_pSerialBuf, 0, p_pBuf, p_nOffset + w_nTmpSize, w_nReadSize - w_nTmpSize);
            m_nReadPos = w_nReadSize - w_nTmpSize;
        } else {
            System.arraycopy(m_pSerialBuf, m_nReadPos, p_pBuf, p_nOffset, w_nReadSize);
            m_nReadPos += w_nReadSize;
        }
        m_nPushedSize -= w_nReadSize;
        return w_nReadSize;
    }

    /**
     * Advances the read pointer until the two-byte header (p_nHead1, p_nHead2) is found.
     * Returns 0 on success, -1 if header not found.
     */
    public synchronized int FixData(byte p_nHead1, byte p_nHead2) {
        int w_nPushedSize = m_nPushedSize;
        int i;
        for (i = 0; i < w_nPushedSize - 1; i++) {
            if (m_pSerialBuf[m_nReadPos] == p_nHead1 && m_pSerialBuf[(m_nReadPos + 1) % MAX_SERIAL_BUF_SIZE] == p_nHead2) {
                break;
            }
            m_nReadPos = (m_nReadPos + 1) % MAX_SERIAL_BUF_SIZE;
        }

        if (i >= w_nPushedSize - 1) {
            if (m_pSerialBuf[m_nReadPos] == p_nHead1) {
                m_nPushedSize -= (w_nPushedSize - 1);
            } else {
                m_nPushedSize -= w_nPushedSize;
            }
            return -1;
        }

        m_nPushedSize -= i;
        return 0;
    }
}
