default:
	@make test

deploy:
	@git push origin master
	@git push origin --tags

# make BUILD_VERSION=1.0.0 build
build:
	@echo '$(BUILD_VERSION)' > VERSION.txt
	@git add .
	@git commit -a -m 'v$(BUILD_VERSION)'
	@git tag v$(BUILD_VERSION)

test:
	@echo 'Running tests...'
	@./vendor/bin/phpunit
	@echo 'Done.'
