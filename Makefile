all: clean downloadresults showresults

#################################################################################################

downloadresults:
	php filedownloader.php --urlsfile=urls.txt --proxiesfile=proxies.txt --verbose=100 --skipalreadyexistingfiles

showresults:
	@for i in results/* ; do c=`cat $$i`; /bin/echo -ne "FILE: $$i | CONTENT: $$c\n"; done

clean:
	rm -rf results; mkdir results; chmod 777 results;
