ParallelDownloader - A PHP class to download many urls in parallel using different http proxies.

--------------

After the class has downloaded an URL, an overideable callback function 
named 'process_result' will be invoked. You can easily override it to do
anything you want, like: store the content in a file, put it in a DB,
parse it and extract some info ... 

Just ensure that you return false when you want the URL to be retried again, 
using a different proxy (perhapse because the proxy that was used, returned 
an ad, instead of the real content, or an network error occured, and so on ...)

See the provided test_real_with_public_proxies_and_urls.php for an
example invokation. 

See the end of the paralleldownloader.php for an example of
subclassing and overiding the process_result function.

--------------

The folder slowproxy contains a simple NodeJS based http proxy with
configurable latency (minimum latency time and a variable random
jitter time).

Example invokation of the slowproxy:
node slowproxy/slowproxy.js 3128 2200 1000

The above line will start a new http proxy listening on port 3128, with a baseline
latency of 2200ms and a jitter of maximum 1000ms ( so the actual
latency added for each response from the proxy will vary between  2200ms
and 2200ms + 1000ms = 3200ms ).
