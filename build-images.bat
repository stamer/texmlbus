:: Use this script on windows, if you are
:: using docker compose v2 and images do not build
docker pull alpine:3.12
docker pull alpine:3.13
docker compose build latexml_base
docker compose build latexml_git
docker compose build latexml
docker compose build latexml_dmake
docker compose up

