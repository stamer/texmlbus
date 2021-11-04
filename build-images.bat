docker pull alpine:3.12
docker pull alpine:3.13
docker compose build latexml_base
docker compose build latexml_git
docker compose build latexml
docker compose build latexml_dmake
docker compose up

