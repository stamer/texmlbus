#!/bin/bash

# determines whether docker compose v2 is used
is_dc_v2() {
   # docker-compose v1: _1 at end.
   # docker-compose v2: -1 at end
   docker container ls --format={{.Names}} | grep texmlbus | grep '_1$' > /dev/null
   return $?
}

