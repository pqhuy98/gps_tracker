#!/bin/bash
echo "Changing data base configuration"
PATH_DOTENV=./application/.env
PATH_PRIVATE_KEY=private_key.txt
PATH_PHP_CONFIG=./application/config/database.php

read -p 'Host [mysql.cc.puv.fi]: ' host
host=${host:-mysql.cc.puv.fi}
read -p 'Username [e1601124]: ' username
username=${username:-e1601124}
read -sp 'Password: ' password
echo
read -p "Database name [$username""_gps_tracker]: " dbName
dbName=${dbName:-"$username""_gps_tracker"}

startCap=35
endCap=60
a=$((10 + $RANDOM % ($startCap-10))) # random int in range [10, $startCap)
b=$((10 + $RANDOM % ($startCap-10))) # random int in range [10, $startCap)
c=$((35 + $RANDOM % ($endCap-$startCap))) # random int in range [$startCap, $endCap)
d=$((35 + $RANDOM % ($endCap-$startCap))) # random int in range [$startCap, $endCap)

# clean up private key file
rm $PATH_PRIVATE_KEY 2> /dev/null
touch $PATH_PRIVATE_KEY
chmod 777 $PATH_PRIVATE_KEY

cmd="
makePrivateKey(\"$host\", \"$username\", \"$password\", \"$dbName\", $a, $b, $c, $d);"
echo $cmd >> $PATH_PHP_CONFIG # add this line to be run when API is called

# Make request to server to update
curl -s http://localhost/~e1601124/gps_tracker/api/user > /dev/null
curl -s https://www.cc.puv.fi/~e1601124/gps_tracker/api/user > /dev/null

# delete the last 2 lines created earlier
sed -i '$d' $PATH_PHP_CONFIG

# delete old private key from .env file
sed -i -r '/^PRIVATE_KEY="[0-9a-f]+"$/d' $PATH_DOTENV

# echo new private key to .env file
privateKey=$(<$PATH_PRIVATE_KEY)
configLine="PRIVATE_KEY=\"$privateKey\""
echo $configLine >> $PATH_DOTENV
rm $PATH_PRIVATE_KEY

echo
echo Credential has been successfully changed!
echo Host $host
echo Username $username
echo Password $password
echo Datbase $dbName