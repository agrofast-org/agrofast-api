# PHP HTTP Request Handler Template with Nginx
This GitHub repository provides a comprehensive Nginx server setup optimized for PHP applications, specifically tailored for deploying web APIs. It includes a ready-to-use template designed to streamline the development and deployment process on Railway platforms. This setup is ideal for developers looking to quickly and efficiently launch PHP-based APIs with Nginx, ensuring compatibility and performance on Railway deployments.

*Informações traduzidas para português em `./info/`*

# Dependencies
This template has some dependencies that need to be installed on your current workspace:
- **Docker**: Mainly used for local testing
- **PHP 8.1**: The current used programming language and it's minimum version
- **Composer**: PHP package manager

# 1st Step: Domain name
In your local host machine add the following lines to your `/etc/hosts` file in order to find the application.
```
127.0.0.1   choir.api.com
```

# 2nd Step: Preparing the environment
Build your custom Docker Image running `./docker/build.sh`

### Exceptions
- In case you are having some permission troubles, use `sudo` to execute the fallowing files


# 3rd Step: Running Docker
`docker-compose up`: Standalone version

`docker-compose up -d`: Daemon version

### Exceptions
- If the daemon is not allawing you to use the `0.0.0.0:80` port, change the `docker-compose.yml` file to expose the port 81:
```yml
    expose:
      - 3000
      - 81
    ports:
      - 3000:3000
      - 81:81
```

# 4th Step: Check your browser
Open http://choir.api.com/ and check the headers on your devtools, and you should see this entry `ping: "pong"`.

# Studying how it works
The most important scripts are:
- `docker/nginx/Dockerfile`: that compiles the Docker Image. Here you can find the packages installed on Linux to make
  this experiment work.
- `docker/nginx/start.sh`: This is the startup script what is executed when the container is activated.
- `docker/apply-config.sh`: Just a shortcut to apply your changes on NGinx config files. You should run this shell script
  from inside your container.
- `docker/nginx/ssh.sh`: Easy way to go into your container instance using SSH. This allows you to execute tests and apply
  your configuration experiments.
- `config/nginx`: Where all NGinx config files resides and can be edited as your will.
- `config/php8`: Where all PHP FPM config files resides and can be edited as your will.
