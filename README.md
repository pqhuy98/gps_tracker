# Backend for GPS Tracker


This repository is written as a backend component for a course project. The repository provides a MVC-based backend with an external REST API which is used by an Android mobile client app.  
  
The backend module is written using PHP and the PHP framework [CodeIgniter](https://codeigniter.com/). It receives data from the client
app, then saves and retrieves the data to/from a MySQL database.

## Application requirement  
The project's requirement written by our teacher Ghodrat Moghadampour:
> The application allows registering and following GPS locations of users and displaying them on the map. This means that the application should allow a user to register and set the interval (like 5 minutes), after which the application will get the current GPS location of the user and will record new GPS locations of the user at the given intervals until the user tells the app to stop recording . The application should also display the travelled path of the user on the map. The application should also allow following the travelled path by other users. This means that if a user gives the name (which can also be a secrete long code) of another registered user and the date (like 27.2.2020) and time interval (like 10-12), the application will display on the map, the path travelled by the user on the given date and time interval.

## Installation  
### Build from source
1. Install [Composer](https://getcomposer.org/download/) - a PHP dependency manager.
2. Clone project and put it in PHP's web folder `www` (or `public_html` for VAMK server).
3. In the project folder, run command line `composer install`.
4. Create a MySQL database. Create tables using this [database schema](https://github.com/pqhuy98/gps_tracker/blob/master/database-schema.sql).
5. Create an `.env` file inside the folder `[project_path]/application` with following content (you will need to change their values):
```
DB_HOST="your database host, e.g. mysql.cc.puv.fi"
DB_USERNAME="your username, e.g. admin"
DB_PASSWORD="your password, e.g. password123"
DB_DATABASE="your database name, e.g. gps_tracker"

AUTHENTICATION=true   # whether the application performs password check or not (teacher's requirement).
ERROR_REPORTING=false # if true, PHP's error message will thrown, else otherwise. Set it to false in production.
```
6. Now the REST server should be functioning on `localhost/gps_tracker/api/...`. See Swagger documentation below for API specification.

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

