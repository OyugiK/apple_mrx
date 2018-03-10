package com.apple.authericate;

import java.io.IOException;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.concurrent.TimeoutException;

import javax.servlet.ServletException;
import javax.servlet.annotation.WebServlet;
import javax.servlet.http.HttpServlet;
import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpServletResponse;

import org.json.simple.JSONObject;

import com.google.gson.Gson;

import com.apple.utils.DBConnectionHandler;
import com.apple.utils.Users;
import com.apple.utils.Status;


/**
 * Servlet implementation class autheticate
 */
@WebServlet("/autheticate")
public class autheticate extends HttpServlet {
	private static final long serialVersionUID = 1L;
       
    /**
     * @see HttpServlet#HttpServlet()
     */
    public autheticate() {
        super();
        // TODO Auto-generated constructor stub
    }
    
	private String replaceAll(String source, String pattern, String replacement) {
		if (source == null) {
			return "";
		}
		StringBuffer sb = new StringBuffer();
		int index;
		int patIndex = 0;
		while ((index = source.indexOf(pattern, patIndex)) != -1) {
			sb.append(source.substring(patIndex, index));
			sb.append(replacement);
			patIndex = index + pattern.length();
		}
		sb.append(source.substring(patIndex));
		return sb.toString();
	}
    
   
    protected void processRequest(HttpServletRequest request, HttpServletResponse response)
			throws ServletException, IOException {
    	response.setContentType("application/json");
    	Gson gson = new Gson();
		// start the db connections
		Connection con = DBConnectionHandler.getConnection();
		String sql = "select * from account where current_session = ? and msisdn = ?";

		try {

			StringBuilder sb = new StringBuilder();
			String s;
			while ((s = request.getReader().readLine()) != null) {
				sb.append(s);
			}
			// load users class
			Users users = (Users) gson.fromJson(sb.toString(), Users.class);

			/*
			 * this needs some more security considerations clean and move on
			 * with the rest of the authentication
			 */
			String token = replaceAll(users.getToken(), "\\<.*?>", "").trim();
			String msisdn = replaceAll(users.getMsisdn(), "\\<.*?>", "").trim();

			PreparedStatement ps = con.prepareStatement(sql);
			ps.setString(1, token);
			ps.setString(2, msisdn);
			ResultSet rs = ps.executeQuery();
			Status status = new Status();
			if (rs.next()) {
				String sToken = rs.getString("current_session");
				Integer activeFlags = rs.getInt("active_flags");
				Integer passwordTries = rs.getInt("pin_tries");
				String uuid = rs.getString("user_id");
				String sMSISDN = rs.getString("msisdn");


				if (activeFlags.equals(1)) {
					/*
					 * user is active first we check how many password tried the
					 * user has initiatd more than 5 has to call customer care
					 */
					if (passwordTries <= 4) {
						/*
						 * user has not exceeded try encrypt and login
						 */
						if (msisdn.equalsIgnoreCase(sMSISDN)) {
							/*
							 * proceed to check the db password againt the
							 * inserted password
							 */
							if (sToken.equalsIgnoreCase(token)) {
								/*
								 * succesful login
								 */

								status.setSuccess(true);
								status.setDescription("verified");
							} else {
								/*
								 * failed login
								 */
								status.setSuccess(false);
								status.setDescription("invalid");

							}

						} else {
							status.setSuccess(false);
							status.setDescription("invalid");
						}

					}

					else {
						/*
						 * means the user has exceeded the number of password
						 * tries ask the user to contact customer care
						 */
						status.setSuccess(false);
						status.setDescription("user has exceeded password tries");
					}
				} else {
					/*
					 * user is inactive and therefore should contact customer
					 * care for activation
					 */
					status.setSuccess(false);
					status.setDescription("user in inactive");
				}

			} else {
				status.setSuccess(false);
				status.setDescription("invalid credentials. user does not exist in db");

			}
			response.getOutputStream().print(gson.toJson(status));
			response.getOutputStream().flush();
		} catch (Exception ex) {
			ex.printStackTrace();
			Status status = new Status();
			status.setSuccess(false);
			status.setDescription(ex.getMessage());
			response.getOutputStream().print(gson.toJson(status));
			response.getOutputStream().flush();
		} finally {
			try {
				con.close();
			} catch (Exception ex) {
				System.out.println(ex);
			}

		}
	}

	/**
	 * @see HttpServlet#doGet(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doGet(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub
		response.getWriter().append("Served at: ").append(request.getContextPath());
	}

	/**
	 * @see HttpServlet#doPost(HttpServletRequest request, HttpServletResponse response)
	 */
	protected void doPost(HttpServletRequest request, HttpServletResponse response) throws ServletException, IOException {
		// TODO Auto-generated method stub
		processRequest(request, response);
	}

}
