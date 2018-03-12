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

{"token":"d37c23216aafaf8b7e4832e0369ddb11","msisdn":"07223345678"}

Sample Respinse :

{
    "success": true,
    "description": "Verified,welcome kevin"
}
