PROJECT_NAME="yii_demo"

.PHONY: help install init

help: Makefile
	@echo "Select option "$(PROJECT_NAME)":"
	@sed -n 's/^##//p' $< | column -s ':' |  sed -e 's/^/ /'

## install: Install YII2
install:
	mkdir -p src &\
	composer create-project --prefer-dist yiisoft/yii2-app-advanced src

## init-dev: Init Development YII2
init-dev:
	php src/init --env=Development --overwrite=All --delete=All

## init-prod: Init Production YII2
init-prod:
	php src/init --env=Production --overwrite=All --delete=All
