docker run --name postgres-db \
  --network postgres-network \
  -e POSTGRES_PASSWORD=admin123 \
  -e POSTGRES_USER=admin \
  -e POSTGRES_DB=laravel-db \
  -p 5432:5432 \
  -v postgres-data:/var/lib/postgresql/data \
  -d postgres:latest


docker run --name pgadmin \
  --network postgres-network \
  -e PGADMIN_DEFAULT_EMAIL=admin@admin.com \
  -e PGADMIN_DEFAULT_PASSWORD=admin123 \
  -p 5051:80 \
  -v pgadmin-data:/var/lib/pgadmin \
  -d dpage/pgadmin4

docker run --name redis-db \
  --network postgres-network \
  -p 6379:6379 \
  -v redis-data:/data \
  -d redis:latest

docker run --name redis-commander \
  --network postgres-network \
  -p 8081:8081 \
  -e REDIS_HOSTS=default:redis-db:6379 \
  -d rediscommander/redis-commander:latest