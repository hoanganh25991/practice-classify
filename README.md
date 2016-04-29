### Load dependent modules

    composer update

### PHP CLI server

    php -S 0.0.0.0:8080 -t public public/index.php

This will start the cli-server on port 8080, and bind it to all network
interfaces.

**Note:** The built-in CLI server is *for development only*.

### Database

    in /data/{module}{database}.sql folder, run SQL command to create table in your database 
    in /config/autoload/local.php, provide {username}, {password} to access mysql
    
### log

    at 34th commit, basic work:
[review on YouTube](https://youtu.be/AlBCfXm1DzU)
