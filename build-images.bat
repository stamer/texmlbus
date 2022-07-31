:: Use this script on windows, if you are
:: using docker compose v2 and images do not build
docker pull alpine:3.12
docker pull alpine:3.13
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_base
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_git
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml
docker compose -f docker-compose.yml -f docker-compose-build.yml build latexml_dmake

