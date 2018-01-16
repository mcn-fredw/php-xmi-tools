###
#
#

test:
	bin/test-run

push:
	git push -u origin `git rev-parse --abbrev-ref HEAD`

