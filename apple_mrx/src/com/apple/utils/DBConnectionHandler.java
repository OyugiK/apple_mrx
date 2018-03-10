package com.apple.utils;


import java.sql.*;

import org.apache.log4j.Level;
import org.apache.log4j.Logger;

import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;

 
 
public class DBConnectionHandler {
 
    Connection con = null;
 
    public static Connection getConnection() {
        Connection con = null;
        try {
        	Class.forName("org.postgresql.Driver");;//postgresql Connection
        	//Class.forName("com.mysql.jdbc.Driver");  
        } catch (Exception ex) {
        	System.out.println(ex);

        	}
        try {
        	con = DriverManager.getConnection(
					//"jdbc:postgresql://inuatestdb.cctm7tiltceo.us-west-2.rds.amazonaws.com:5432/inua", "inua","jw8s0F4"); 
        			"jdbc:postgresql://localhost:5432/apple", "OyugiK",""); 

        } catch (SQLException ex) {
        	//final Logger logger = Logger.getLogger(DBConnectionHandler.class.getName());
        	//Logger.getRootLogger().setLevel(Level.DEBUG);
        	//logger.error(ex);
        	System.out.println(ex);
        	}
        return con;
    }
    
   

    
    /**
     * Enqueue SMS in tbl_sms_outgoing
     * @param dest - mesasge destination - intl format msisdn
     * @param message - the message body
     * @param source - the sender (as defined in the config)
     * @return 
     */
    public static boolean enqueueSMS(String dest,String message, String source){
    	
    	//logger
    	//final Logger logger = Logger.getLogger(DBConnectionHandler.class.getName());
    	//Logger.getRootLogger().setLevel(Level.DEBUG);
        int affected = 0;
        DateFormat dateFormat = new SimpleDateFormat("yyyy/MM/dd HH:mm:ss");
        Date date = new Date();
        Connection conn = DBConnectionHandler.getConnection();
       
        PreparedStatement st = null;
        ResultSet rs = null;
        try {
            //logger.debug("Sending SMS to (254) " + dest);
            // we add a 268 to the outgoing number
        	String simple_dest = dest.substring(dest.length() - 9);
            dest = "254".concat(simple_dest);
           
            //logger.debug("Got Connection");
            
            st =
                    conn.prepareStatement("INSERT into "
                    + "tbl_sms_outgoing(message,msisdn,create_date,send_status,send_status_info,source)"
                    + " values(?,?,?,?,?,?)");
            
            st.setString(1,message);
            st.setString(2,dest);
            st.setString(3, dateFormat.format(date));
            st.setInt(4,0);
            st.setString(5,"pending");
            st.setString(6,source);
           //logger.debug("Prepared and set params");
            affected = st.executeUpdate();
            //logger.info("Affected -> "+affected);
            System.out.println("success : sms inserted ");
            

        } catch (SQLException ex) {
            //logger.fatal("SQLEx ", ex);
        	System.out.println(message.length());
        	System.out.println(ex);
        	try{
           		conn.close();	
           	}
           	catch (Exception exp){
           		System.out.println(exp);
           	}        } finally {
            //logger.debug("Closing Connections");
           		try{
               		conn.close();	
               	}
               	catch (Exception ex){
               		System.out.println(ex);
               	}	
          }
        return affected == 1;
    }
}
