Bug discovered in Doctrine. Steps to reproduce:

	$ git clone git@github.com:mmenozzi/doctrine-test.git somedir/
	$ cd somedir/
	$ bin/composer.phar install
	$ phpunit
	
You should see a failure. If assumptions in _DoubleFlushTest_ are right, this is a defect in Doctrine.