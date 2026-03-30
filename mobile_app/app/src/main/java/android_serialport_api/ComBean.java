package android_serialport_api;

import java.text.SimpleDateFormat;

/**
 * @author benjaminwan
 */
public class ComBean {
		public byte[] bRec=null;
		public String sRecTime="";
		public String sComPort="";
		public int nSize = 0;
		public ComBean(String sPort,byte[] buffer,int size){
			sComPort=sPort;
			bRec=new byte[size];
			for (int i = 0; i < size; i++)
			{
				bRec[i]=buffer[i];
			}
			nSize = size;
			SimpleDateFormat sDateFormat = new SimpleDateFormat("hh:mm:ss");       
			sRecTime = sDateFormat.format(new java.util.Date()); 
		}
}