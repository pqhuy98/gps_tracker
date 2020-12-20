# Backend for GPS Tracker


This repository is written as a backend component for a course project. The repository provides a MVC-based backend with an external REST API which is used by an Android mobile client app.  
  
The backend module is written using PHP and the PHP framework [CodeIgniter](https://codeigniter.com/). It receives data from the client
app, then saves and retrieves the data to/from a MySQL database.

## Application requirement  
The project's requirement written by our teacher Ghodrat Moghadampour:
> The application allows registering and following GPS locations of users and displaying them on the map. This means that the application should allow a user to register and set the interval (like 5 minutes), after which the application will get the current GPS location of the user and will record new GPS locations of the user at the given intervals until the user tells the app to stop recording . The application should also display the travelled path of the user on the map. The application should also allow following the travelled path by other users. This means that if a user gives the name (which can also be a secrete long code) of another registered user and the date (like 27.2.2020) and time interval (like 10-12), the application will display on the map, the path travelled by the user on the given date and time interval.

## Installation  
### Build from source
1. Clone project and put it in PHP's web folder `www` (or `public_html` for VAMK server).
2. Create a MySQL database. Create tables using this [database schema](https://github.com/pqhuy98/gps_tracker/blob/master/database-schema.sql).
3. Create an `.env` file inside the folder `[project_path]/application` with following content (you will need to change their values):
```
# This is the configuration file in which initial settings are defined and used by the application.
# Do not change the content of this file if you are not sure what you are doing.

PRIVATE_KEY="..."

AUTHENTICATION=true   # whether the application performs password check or not (teacher's requirement).
ERROR_REPORTING=false # if true, PHP's error message will thrown, else otherwise. Set it to false in production.
```
4. The value of `PRIVATE_KEY` must be generated with the method described in [this section](https://github.com/pqhuy98/gps_tracker#private_key-generation).
5. After having a valid PRIVATE_KEY, the REST server should be functioning on `localhost/gps_tracker/api/...`. See Swagger documentation below for API specification.

## Architecture
Model-View-Controller architecture is employed in the whole project. This backend repository implements Model and Controller component, while the frontend Android client app implements the View component.

### Model
Model Diagram:  
![Model Diagram](https://raw.githubusercontent.com/pqhuy98/gps_tracker/master/model-diagram.PNG)

The model component contains two classes: User and Point.  
- Class User is a pair of (username, password) and is used for authentication and authorization in the REST API.  
- Class Point contains the GPS coordinate, i.e. longitude and latitude, owner's username and timestamp.

Source code files to be concerned:
```
  application/models/User_model.php
  application/models/Point_model.php
```

### Controller:
The controller implements REST methods to create and retrieve User and Point. See section [REST API Documentation](https://github.com/pqhuy98/gps_tracker#rest-api-documentation) below for detailed end points.

Source code files to be concerned:
```
  application/controller/api/User.php
  application/controller/api/Point.php
```

## REST API Documentation
[Documentation of REST API](https://app.swaggerhub.com/apis-docs/pqhuy98/GPS-Tracker/1.0.0).

## Database Credentials Retrieval
Database credentials is stored in the .env file. However, instead of storing host name, username, password and database name in plaintext,they are stored in a single symmetrically encrypted string in the .env file. To retrieve back the plaintext information, the string is then decrypted using a passphrase that is also hidden in the .env file. The symmetric encryption algorithm to be used in this project is a combination AES-256 and HMAC. This section describes the scheme to encrypt and decrypt the database credentials from the .env file.

Example of the .env file:
```
# This is the configuration file in which initial settings are defined and used by the application.
# Do not change the content of this file if you are not sure what you are doing.

PRIVATE_KEY="14164260f9816ad5c652c056f..."
```

The value of PRIVATE_KEY is the encrypted string that contains database credentials. The passphrase to decrypt PRIVATE_KEY is derived from first 8 character of PRIVATE_KEY and the .env file's first two lines (**# This is the configuration...** and **# Do not change...**).

Firstly, we split the PRIVATE_KEY string into 2 parts: the first part contains first 8 character, the second part contains the rest of the string. Using the example of .env file above, we have:

PRIVATE_KEY string is `14164260f9816ad5c652c056f...`  
First part is `14164260`  
Second part is `f9816ad5c652c056f...`  

Next, we split first part `14164260` into 4 pairs of consecutive numbers: `14`, `16`, `42` and `60`, Let's call them `a`, `b`, `c` and `d`.

Next, we take the substring of the .env file's first line from position `a` to position `c` (i.e. from postion 14 to 42):  
First line: `# This is the configuration file in which initial settings are defined and used by the application.`
Substring from position 14 to 42 : `configuration file in which `.

Next, we take the substring of the .env file's second line from position `b` to position `d` (i.e. from postion 16 to 60):  
First line: `# Do not change the content of this file if you are not sure what you are doing.`
Substring from position 16 to 60 : `the content of this file if you are not sure`.

Now we concatenate the two substring to get the passphrase: `configuration file in which the content of this file if you are not sure`. We use this phase phrase to decrypt the PRIVATE_KEY's second part `f9816ad5c652c056f...` with the encryption algorithn to retrieve the database credentials `mysql.vamk.fi:e123456:password:database_name` (the host name, username, password and database name is concatenated by character `:`).

Implementation of the scheme can be found in [this part of the source code](https://github.com/pqhuy98/gps_tracker/blob/master/application/config/database.php#L79).

The encryption and decryption algorithm is a combination of AES-256 and HMAC which is originated from [here](https://stackoverflow.com/a/46872528).

## PRIVATE_KEY Generation
To generate the PRIVATE_KEY string, the server admin must:
1) Pick 4 integer numbers `a`, `b`, `c` and `d` from 10 to 99. For example: 14, 16, 42 and 60.
2) Open [this file](https://github.com/pqhuy98/gps_tracker/blob/master/application/config/database.php#L82) in the server.
3) Navigate to line 82 and change the value of `host`, `username`, `password`, `db_name`, `a`, `b`, `c` and `d` to the corresponding values of his database server. Values of `a`, `b`, `c` and `d` are what the admin chose in step 1.
4) Use the web browser to visit `localhost/gps_tracker/api/user`. There might be some errors show up because server couldn't connect to database. This is expected since we haven't set a valid PRIVATE_KEY yet. Just ignore the errors.
5) After the previous step, a file named `private_key.txt` will be created in the project's root directory in the server.
6) Copy the content of `private_key.txt` and put it into the variable PRIVATE_KEY inside the .env file. I.e. `PRIVATE_KEY="content of private_key.txt goes here..."`
