#filedownloader.php
   A command line script, that will download a list of URLs 
(given in a file), using http proxies (given in another file).
   The goal is to download **_many URLs_** using *different proxies* in **parallel**,
in order to avoid automated blocking and detection from the targeted
hosts.

   If you are on a Posix system like MacOS or Linux, you can invoke the
script directly, without prefixing it with the php interpreter.

   Example usage: 

      php filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt --verbose=100 --skipalreadyexistingfiles
   
      php filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt -v  
   
      php filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt -v  --referrer=http://www.dir.bg/
   
   #### download maximum 20 urls at once
   
      php filedownloader.php  --proxiesfile=proxies.txt --urlsfile=urls.txt --chunksize=20
      
   #### use shorter options, specifying a different list of proxies (real anonymizers):
   
      php filedownloader.php  -p=notraceproxies.txt -u=urls.txt


--------------

#ParallelProxyCrawler
   A PHP class to download many urls in parallel using different http proxies.

   See the provided **test_real_with_public_proxies_and_urls.php** and the **filedownloader.php** 
files for example invokations.

--------------

   After the class has downloaded an URL, an overideable callback function 
named '*process_result*' or an overrideable callback '*on_failed_url*' will be invoked. 
You can easily override them to do anything you want, like: store the content 
in a file, put it in a DB, parse it and extract some info ... 

   Just ensure that you return **false** from '*process_result*' when you want 
the URL to be retried again, using a different proxy (perhapse because the 
proxy that was used, returned an ad, instead of the real content, or an network 
error occured, and so on ...)

   See the end of the paralleldownloader.php for examples of
subclassing and overiding the callback functions.

--------------

#slowproxy

   The folder slowproxy contains a simple _http://nodejs.org/_ based http proxy with
configurable latency (minimum latency time and a variable random
jitter time).

   Example invokation of the slowproxy:

```bash
node slowproxy/slowproxy.js 3128 2200 1000
```



   The above line will start a new http proxy listening on port 3128, with a baseline
latency of 2200ms and a jitter of maximum 1000ms ( so the actual
latency added for each response from the proxy will vary between  2200ms
and 2200ms + 1000ms = 3200ms ).


--------------

The MIT License (MIT)
Copyright © 2012 <Delyan Angelov>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
