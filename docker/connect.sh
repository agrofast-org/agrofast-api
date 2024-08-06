#!/usr/bin/env bash

clear

echo -e "
\t\u001b[47m\u001b[30mYou are now in the docker container\u001b[0m
\t     \u001b[37mUse \u001b[1mexit\u001b[0m \u001b[37mcommand to leave
";

docker exec -it agrofast bash