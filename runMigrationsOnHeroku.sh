#!/bin/sh
heroku run:detached './vendor/bin/phinx migrate -e production'
