#!/usr/bin/env bash

APP_PATH="$1"
APP_GIT_PATH="$1.git"

if [ ! "$APP_PATH" ]; then
  echo "Oups! No app directory specified."
  exit 1
fi
if [ -d "$APP_GIT_PATH" ]; then
  echo "Oups! Directory $APP_GIT_PATH already exists."
  exit 1
fi
if [ -d "$APP_PATH" ]; then
  echo "Oups! Directory $APP_PATH already exists."
  exit 1
fi

# git dir
echo "Creating $APP_GIT_PATH..."
mkdir -p "$APP_GIT_PATH"
cd "$APP_GIT_PATH"
git init --bare

# app dir
echo "Creating $APP_PATH..."
mkdir -p "$APP_PATH"
cd "$APP_PATH"
git init
git remote add origin "$APP_GIT_PATH"

