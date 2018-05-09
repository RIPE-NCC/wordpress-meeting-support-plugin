# Makefile to manage the updating and installing of the MPS plugin.
pull:
	@git pull
	@php composer.phar install --no-interaction --prefer-dist --optimize-autoloader


pull-dev:
	@git pull
	@php composer.phar install
