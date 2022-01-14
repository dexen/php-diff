MKSHELL=rc

all:VQ: build

build:VQ: lib_dexen_diff.php

lib_dexen_diff.php:Q: src/CompareTwo.php
	php -l $prereq
	cat $prereq > lib_dexen_diff.php

clean:VQ:
	rm -f lib_dexen_diff.php
