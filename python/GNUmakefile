
upload-test:
	cd module; sudo python3 setup.py sdist upload -r pypi-test

tests:		install
	python3 -m pytest tests.py

install:
	sudo pip3 uninstall restruct -y || true
	cd module; sudo python3 setup.py install
