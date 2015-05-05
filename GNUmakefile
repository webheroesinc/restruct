
FORCE:
all:
	@echo ""
	@echo "Testing"
	@echo ""
	@echo "    test-%		run one of your defined tests"
	@echo ""

documentation.zip:		README.html
	cp README.html index.html;		\
	zip documentation.zip index.html;

python/LICENSE:
	ln -s $$(pwd)/LICENSE $@
python/COPYING:
	ln -s $$(pwd)/COPYING $@
register-package:
	cd python; python setup.py register
register-test-package:
	cd python; python setup.py register -r pypi-test
upload-package:		python/COPYING python/LICENSE
	cd python; python setup.py sdist upload
upload-test-package:	python/COPYING python/LICENSE
	cd python; python setup.py sdist upload -r pypi-test

# start and stop web handlers
../var/run/%.pid:
	make -C .. start-$*	> /dev/null 2>&1
	@touch $*.started
stop-%:
	make -C .. stop-$*	> /dev/null 2>&1
	@rm -f $*.started

# defined tests (do NOT run directly)
run-formatter:		../var/run/mysql.pid
	@../bin/trocker start --name formatter_tests -d	\
		-w /host/tests webheroes/testing	\
		py.test -v formatter_tests.py

# testing target (only run this target)
test-%:			run-%
	docker logs -f $*_tests		2> /dev/null	|| true
	@../bin/trocker kill --name $*_tests 2> /dev/null
	docker rm -f $*_tests		2> /dev/null	|| true
	@for f in $$(ls *.started 2> /dev/null); do	\
            make stop-$${f%.started};			\
        done;
