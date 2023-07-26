docker build -t kpes . --no-cache 
docker run -dit -p 8090:8080  --name kpes -v C:/CETAapps/kPES:/var/www/html --env-file C:/CETAapps/kPES/dev_env.list kpes
