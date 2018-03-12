# apple_mrx

This is a simple site that does authentication and authorization.
The main components are :
1. PHP mini site
2. Java rest web service

url :
http://ec2-34-245-152-122.eu-west-1.compute.amazonaws.com/apple/index.php


endpoint : 
http://ec2-34-245-152-122.eu-west-1.compute.amazonaws.com:8080/apple_mrx/autheticate

Sample Request:

{
	"token":"517ad557b5f710c5b3661cfd4cf3b83d",
	"msisdn":"9696868"
}

Sample Respinse :

{
    "success": true,
    "description": "verified"
}
