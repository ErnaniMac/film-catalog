# Parar os containers em execucao
docker-compose stop || true

# Remover conteineres se eles existirem
docker rm film-catalog-mysql film-catalog-redis film-catalog-laravel film-catalog-node || true

# Remove 
docker-compose down -v --rmi all

# Limpar cache do Docker
docker system prune -a

# não é bom usar devido a excluir todos os volumes da maquina, pode prejudicar outros projetos.
docker container prune -f
docker volume prune -f

# não é bom usar pelo fato de remover todas images da maquina, até mesmo as que não são do seu projeto atual.
docker image prune -f

