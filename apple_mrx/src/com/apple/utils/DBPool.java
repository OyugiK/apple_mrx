package com.apple.utils;

import com.mchange.v2.c3p0.ComboPooledDataSource;
import com.mchange.v2.c3p0.DataSources;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.sql.SQLException;
import java.sql.Statement;
import java.util.logging.Level;
import javax.naming.Context;
import javax.naming.InitialContext;
import javax.naming.NamingException;
import javax.sql.DataSource;
import org.apache.log4j.Logger;

/**
 * This is the wrapper for the database methods and class
 *
 * @author Kevin
 */
public class DBPool {

    /**
     * Logger
     */
    private static final Logger ls = Logger.getLogger(DBPool.class);
    
    /**
     * The datasources
     */
    private static DataSource dsUnPooled, dsPooled ;
    
    /**
     * Static ones
     */
    private static ComboPooledDataSource dsc3p0;
    
    /**
     * Has the Pool been initialized?
     */
    private static boolean poolInit = false;
    
    /**
     * C3p0
     */
    private static boolean c3p0Init = false;

    /**
     * We have the datasource
     */
    public static boolean initDatasource() {
        // first we locate the driver
        String pgDriver = "org.postgresql.Driver";
        try {
            ls.debug("Looking for Class : " + pgDriver);
            Class.forName(pgDriver);
        } catch (Exception e) {
            ls.fatal("Cannot find postgres driver (" + pgDriver + ")in class path", e);
            return false;
        }

        try{        
            String url = "jdbc:postgresql://inuatestdb.cctm7tiltceo.us-west-2.rds.amazonaws.com:5432/inua?user=inua&password=jw8s0F4";
        dsUnPooled =DataSources.unpooledDataSource(url);
        dsPooled = DataSources.pooledDataSource(dsUnPooled);
        
        poolInit = true;
    }
    catch (Exception e){ 
        ls.fatal("SQL Exception",e);
        System.out.println("initDataSource" + e);
        return false;
    }
        
    return true;
}
    
    public static boolean initPooledDataSource(){
        try { // Create initial context
           
        	System.out.println("starting");
            Context initCtx = new InitialContext();
            Context envCtx = (Context) initCtx.lookup("java:comp/env");
            System.out.println(envCtx);
            System.out.println("env?");

            // Look up our data source
            dsc3p0 = (ComboPooledDataSource) envCtx.lookup("jdbc/postgres-ec2");
            System.out.println(dsc3p0);
            ls.info(dsc3p0);
            c3p0Init = true;
            return true;
            
        } catch (NamingException ex) {
        	System.out.println("initPooled DataSurce " + ex);
            ls.fatal(ex);
        }
        
        return false;
    }
    
    public static Connection getConnection() throws SQLException{
        if(!poolInit){
            initDatasource();
        }
        return dsPooled.getConnection();
    }
    
    public static Connection getPooledConnection() throws SQLException{
        if(!c3p0Init){
            initPooledDataSource();            
        }
        return dsc3p0.getConnection();
        
    }
    
    /**
     * Closing the connection, statement and results set
     * @param  
     */
    public static void attemptClose(Connection c, PreparedStatement p, ResultSet o)
    {
        attemptClose(c);
        attemptClose(p);
        attemptClose(o);
    }
    
    /**
     * Closing the result set
     * @param o 
     */
    public static void attemptClose(ResultSet o)
    {
        try
            { ls.debug("Closing "+o); if (o != null) o.close();}
        catch (Exception e)
            { ls.fatal(e);}
    }
    
    /**
     * Closing the statement
     * @param o 
     */
    public static void attemptClose(Statement o)
    {
        try
            {ls.debug("Closing "+o); if (o != null) o.close();}
        catch (Exception e)
            { ls.fatal(e);}
    }
    
    /**
     * Closing the connection
     * @param o 
     */
    public static void attemptClose(Connection o)
    {
        try
            
            { ls.debug("Closing "+o); if (o != null) o.close();}
        catch (Exception e)
            { ls.fatal(e);}
    }
}

/**/
