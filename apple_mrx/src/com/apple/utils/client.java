package com.apple.utils;

import java.security.MessageDigest;
import java.security.SecureRandom;
import java.sql.Connection;
import java.sql.PreparedStatement;
import java.sql.ResultSet;
import java.text.DateFormat;
import java.text.SimpleDateFormat;
import java.util.Date;
import java.util.UUID;

import com.google.gson.Gson;
import com.apple.utils.DBConnectionHandler;
import com.apple.utils.Users;

public class client {
		public boolean authenticate(String token, String msisdn){

			Connection con = DBConnectionHandler.getConnection();
			String sql = "SELECT uuid, android_id,  msisdn, status FROM tbl_client_sessions where uuid=? and android_id=?";

			try {

				PreparedStatement ps = con.prepareStatement(sql);
				ps.setString(1, token);
				ps.setString(2, msisdn);
				ResultSet rs = ps.executeQuery();
				if (rs.next()) {
					String sMSISDN = rs.getString("uuid");
					String sToken = rs.getString("status");
					String sStatus = rs.getString("androdID");

					if (sStatus.equals("1")) {
						/*
						 * user is active first we check how many password tried the
						 * user has initiatd more than 5 has to call customer care
						 */						/*
						 * user has not exceeded try encrypt and login
						 */
						if (token.equalsIgnoreCase(sToken)) {

							if (sMSISDN.equalsIgnoreCase(msisdn)) {
								/*
								 * succesful auth
								 */
								return true;
							} else {
								/*
								 * failed login
								 */
								return false;

							}

						}

						else {
							return false;
						}
					} else {
						return false;
					}

				} else {
					return false;

				}

			} catch (Exception ex) {
				ex.printStackTrace();
				return false;


			} finally {
				try {
					con.close();
				} catch (Exception ex) {
					System.out.println(ex);
				}

			}
		}
}
