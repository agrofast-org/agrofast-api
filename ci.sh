#!/bin/bash
set -e

./script/migration.sh

# ./script/seed.sh

./script/queue.sh

# ./script/schedule.sh

./script/serve.sh
