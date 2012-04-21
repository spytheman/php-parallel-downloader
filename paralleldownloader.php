<?php
/*
The MIT License (MIT)
Copyright © 2012 <Delyan Angelov (delian66@gmail.com)>

Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated documentation files (the “Software”), to deal in the Software without restriction, including without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to permit persons to whom the Software is furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED “AS IS”, WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
*/


/////////////////////////////////////////////////////////////////////////////
// See test_real_with_public_proxies_and_urls.php for an example usage of this class.
/////////////////////////////////////////////////////////////////////////////

function __tlog($s){
    $d = date("Y/m/d H:i:s");
    echo "$d| $s\n";
}

class ParallelProxyCrawler{
    var $cookiefile = '/tmp/cookie.txt';
    var $referrer   = 'http://www.google.com/';
    var $useragent  = array(
                            //Chromes:
                            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
                            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.6 (KHTML, like Gecko) Chrome/20.0.1092.0 Safari/536.6",
                            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7",
                            "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/536.5 (KHTML, like Gecko) Chrome/19.0.1084.9 Safari/536.5",
                            "Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
                            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_8_0) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1063.0 Safari/536.3",
                            "Mozilla/5.0 (Windows NT 6.2) AppleWebKit/536.3 (KHTML, like Gecko) Chrome/19.0.1061.0 Safari/536.3",
                            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_7_3) AppleWebKit/535.20 (KHTML, like Gecko) Chrome/19.0.1036.7 Safari/535.20",
                            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.19 (KHTML, like Gecko) Chrome/18.0.1025.11 Safari/535.19",
                            "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_6_8) AppleWebKit/535.2 (KHTML, like Gecko) Chrome/15.0.874.54 Safari/535.2",
                            "Mozilla/5.0 (Windows NT 6.2; WOW64) AppleWebKit/535.11 (KHTML, like Gecko) Chrome/17.0.963.65 Safari/535.11",
                            
                            //IEs:
                            "Mozilla/5.0 (compatible; MSIE 10.6; Windows NT 6.1; Trident/5.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727) 3gpp-gba UNTRUSTED/1.0",
                            "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; WOW64; Trident/6.0)",
                            "Mozilla/5.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/4.0; InfoPath.2; SV1; .NET CLR 2.0.50727; WOW64)",
                            "Mozilla/4.0 (compatible; MSIE 10.0; Windows NT 6.1; Trident/5.0)",
                            "Mozilla/5.0 (Windows; U; MSIE 9.0; WIndows NT 9.0; en-US))",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 2.0.50727; Media Center PC 6.0)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Win64; x64; Trident/5.0",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.0; Trident/5.0; chromeframe/11.0.696.57)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; chromeframe/11.0.696.57)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; FunWebProducts)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; Trident/5.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; Media Center PC 6.0; InfoPath.2; .NET CLR 1.1.4322; .NET4.0C; Tablet PC 2.0)",
                            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; InfoPath.2)",
                            "Mozilla/5.0 (compatible; MSIE 9.0; Windows NT 6.1; WOW64; Trident/5.0; SLCC2; Media Center PC 6.0; InfoPath.3; MS-RTC LM 8; Zune 4.7)",
                            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 6.0; Trident/4.0; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET CLR 1.0.3705; .NET CLR 1.1.4322)",
                            "Mozilla/5.0 (compatible; MSIE 8.0; Windows NT 5.1; Trident/4.0; InfoPath.2; SLCC1; .NET CLR 3.0.4506.2152; .NET CLR 3.5.30729; .NET CLR 2.0.50727)",
                            "Mozilla/4.0 (compatible; MSIE 8.0; Windows NT 6.1; WOW64; Trident/4.0; SLCC2; .NET CLR 2.0.50727; Media Center PC 6.0; .NET CLR 3.5.30729; .NET CLR 3.0.30729; .NET4.0C)",

                            //Firefoxes:
                            "Mozilla/5.0 (Windows NT 6.1; rv:12.0) Gecko/20120403211507 Firefox/12.0",
                            "Mozilla/5.0 (compatible; Windows; U; Windows NT 6.2; WOW64; en-US; rv:12.0) Gecko/20120403211507 Firefox/12.0",
                            "Mozilla/5.0 (Windows NT 6.1; WOW64; rv:11.0) Gecko Firefox/11.0",
                            "Mozilla/5.0 (Windows NT 6.2; rv:9.0.1) Gecko/20100101 Firefox/9.0.1",
                            "Mozilla/5.0 (X11; Linux i686; rv:6.0) Gecko/20100101 Firefox/6.0",
                            "Mozilla/5.0 (Windows NT 5.0; WOW64; rv:6.0) Gecko/20100101 Firefox/6.0",
                            "Mozilla/5.0 (X11; Linux x86_64) Gecko Firefox/5.0",
                            "Mozilla/5.0 (Windows; U; Windows NT 6.1; ru; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
                            "Mozilla/5.0 (Windows; U; Windows NT 5.1; fr; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
                            "Mozilla/5.0 (Windows; U; Windows NT 6.1; en; rv:1.9.2b5) Gecko/20091204 Firefox/3.6b5",
                            "Mozilla/5.0 (Macintosh; U; Intel Mac OS X 10.6; en-US; rv:1.9.2) Gecko/20091218 Firefox 3.6b5",

                            ); // NB: Change this to something more popular :-)
    var $verbose_curl = false; // set this to true to enable very verbose curl debugging info on strerr
    var $default_proxy = '57.90.36.24:80'; // the default proxy ... '' means DO NOT USE proxy, so try to retrieve the URL directly
    var $minimumdbglevel = 10;
    var $seconds_to_sleep_between_cycles_min = 1; 
    var $seconds_to_sleep_between_cycles_max = 10;

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generic utilities:
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function tlog($s){
        __tlog($s);
    }
    
    function dlog($s, $dbglevel=0){
        if($dbglevel>=$this->minimumdbglevel){
            $this->tlog($s);
        }
    }

    function pretty_array($a){
        $res = var_export($a, true);
        //$res = str_replace("\n", " ", $res);
        return $res;
    }
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    
    function get_new_channel($url, $proxy=""){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);

        if($proxy!=""){
            list($h,$p) = explode(':', $proxy);
            $p = (int) $p;
            curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_HTTP);
            curl_setopt($ch, CURLOPT_PROXY,     $h);
            curl_setopt($ch, CURLOPT_PROXYPORT, $p);
        }

        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->cookiefile);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->cookiefile);

        curl_setopt($ch, CURLOPT_AUTOREFERER, true);
        curl_setopt($ch, CURLOPT_REFERER,   $this->referrer);
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent[ array_rand( $this->useragent, 1 ) ]);

        curl_setopt($ch, CURLOPT_FAILONERROR, true);
        curl_setopt($ch, CURLOPT_FORBID_REUSE, false);
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, false);
        curl_setopt($ch, CURLOPT_VERBOSE,       $this->verbose_curl);   
        curl_setopt($ch, CURLOPT_HTTPGET, true);
        curl_setopt($ch, CURLOPT_HEADER,  false);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);

        curl_setopt($ch, CURLOPT_TIMEOUT, 600);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 600);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_BUFFERSIZE, 2048);
        curl_setopt($ch, CURLOPT_CLOSEPOLICY, CURLCLOSEPOLICY_OLDEST);
        curl_setopt($ch, CURLOPT_LOW_SPEED_LIMIT, 200);
        curl_setopt($ch, CURLOPT_LOW_SPEED_TIME, 30);
        curl_setopt($ch, CURLOPT_MAXCONNECTS, 200);

        return $ch;
    }

    function log_current_lvl_proxies_and_urls($lvl, $proxies, $urls){
        // do nothing in the basic version
    }

    function onFinished(){
        return '';
    }
    
    function parallel_download_using_proxies($proxies, $urls, $lvl = 20){
        $this->_parallel_download_using_proxies( $proxies, $urls, $lvl );
        return $this->onFinished();
    }
    
    function _parallel_download_using_proxies($proxies, $urls, $lvl = 20){
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  1]      PROXIES: ".str_replace("\n", " ", var_export($proxies,true)), 10);
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  2]         URLS: ".str_replace("\n", " ", var_export($urls,true)), 10);
        if($lvl < 0)return;
        
        $proxies = array_unique( $proxies );
        if(empty($proxies)) $proxies = array($this->default_proxy); // If no usable proxies has been given, fall back to the default one.

        $urls = array_unique( $urls );

        $this->log_current_lvl_proxies_and_urls( $lvl, $proxies, $urls);
        
        // The following array will be slowly saturated with data:
        $results = array();
        $channels = array();

        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        //Setup curl handlers:
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $master = curl_multi_init();
        foreach($urls as $url){
            $proxy = $proxies[ array_rand( $proxies, 1 ) ];
            $results[$url]  = array(
                                    'url'=>$url,
                                    'proxy'=>$proxy,
                                    'meta'=>'',
                                    );
            $channels[$url] = $this->get_new_channel($url, $proxy);
            curl_multi_add_handle($master, $channels[$url]);
            $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  2.5] : new channel by proxy: '$proxy' ; url: '$url' ", 5);
        }
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  3] : curl handlers had been setup", 10);
                      
        // The curl event loop: process the data of the channels.
        $running = null;
        $ready = 0;
        do {
            $exec_status = curl_multi_exec($master,&$running);
            $ready=curl_multi_select($master, 1);

            if($ready>-1){
                while($info=curl_multi_info_read($master)){
                    $res = curl_getinfo($info['handle']);
                    $url = $res['url'];
                    $this->dlog("META INFO FOR URL: '{$url}'", 1);
                    $this->dlog("                        ".var_export($res,true), 1);
                }
            }
            //$this->dlog("EXEC_STATUS:{$exec_status} running:{$running} ready:{$ready}");
        } while( $exec_status == CURLM_CALL_MULTI_PERFORM || $running > 0);

        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  4] : curl event loop finished", 10);
        
        // Get the accumulated content for each of the channels, and cleanup the curl stuff:
        foreach($channels as $url=>$ch){
            $results[$url]['content'] = '';
            $results[$url]['meta'] = '';
            $results[$url]['error'] = '';
            $results[$url]['errno'] = curl_errno($ch);
            if( $results[$url]['errno'] === 0 ){
                $results[$url]['content'] = curl_multi_getcontent($ch);
                $results[$url]['meta'] = curl_getinfo($ch);
            }else{
                $results[$url]['error'] = curl_error($ch);
            }
            curl_multi_remove_handle($master, $ch);
        }
        curl_multi_close($master);
        
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  5] : curl processing is done", 10);
        ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

        $failedproxies = array();
        $failedurls = array();
        foreach($urls as $url){
            $proxy = $results[$url]['proxy'];
            $sproxy = $proxy;
            if($proxy!='')$sproxy = $proxy;
            else $sproxy = '127.0.0.1:0000';

            $sstring = substr($results[$url]['content'], 0, 5);
            $slen = strlen( $results[$url]['content'] );

            
            $meta = $results[$url]['meta'];
            if($meta!=''){
                /*
                $content_type = $meta['content_type'];
                $http_code = $meta['http_code'];
                $total_time = $meta['total_time'];
                $download_content_length = $meta['download_content_length'];
                //            $this->dlog("INFO: META:yes proxy:'{$sproxy}' url:'{$url}'  len:{$slen}  res:'{$sstring}'...     code:'{$http_code}'  ctype:'{$content_type}'  length:{$download_content_length}  ttime:{$total_time}");
                */
                $cbresult = $this->process_result($results[$url]);
                if($cbresult){
                    // TODO: use the knowledge that this proxy worked to give it more weight in the future:
                    //        $workingproxies[]=$proxy;
                }else{
                    $failedurls[]=$url;
                    $failedproxies[]=$proxy;
                    $this->on_failed_url($url, $proxy);
                }                
            }else{
                $ec = $results[$url]['errno'];
                $e  = $results[$url]['error'];
                $this->dlog("INFO: META:no  ec:{$ec}  error:'{$e}'  failed proxy:'{$sproxy}' failed url:'{$url}'  len:{$slen}  res:'{$sstring}'...", 8);
                $failedurls[]=$url;
                $failedproxies[]=$proxy;
                $this->on_failed_url($url, $proxy);
            }
        }
        
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  6] : checking for failed URLS", 10);
        // The common sense says, that a failed proxy should not be used again in the reiteration of the same batch,
        // since it is highly probable that it will fail again:
        $newproxies = array_diff( $proxies ,  $failedproxies );
        $newurls    = array_unique( $failedurls );
        //        $newurls    = $failedurls;
        if(count($newurls)>0){
            $s = rand( $this->seconds_to_sleep_between_cycles_min, $this->seconds_to_sleep_between_cycles_max);
            $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  6.5] : sleeping for {$s} seconds between cycles", 9);
            usleep($s*10000000);
            $this->_parallel_download_using_proxies( $newproxies, $newurls, $lvl - 1);
        }
        $this->dlog("_parallel_download_using_proxies  [LEVEL: {$lvl},  7] : checking for failed URLS", 10);
    }


    function on_failed_url($url='', $proxy='-:-'){
        $this->dlog("on_failed_url('{$url}', '{$proxy}')", 100);
    }
    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // NB: most probably, you want to override this function:    
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // process_result( $result = array() )
    // An example custom callback function. This function will be called after a given $url has been downloaded.
    //
    // The $result parameter is a hash with the following keys:
    //    'url'     => the url from which the result had been retrieved.
    //    'proxy'   => the proxy used for the retrieving; it is in the form 'IP:portnum', or '' if no proxy has been used to retrieve the url data.
    //    'meta'    => meta information for the retrieving (content type, size, http status code and so on).
    //    'content' => the data retrieved from the url.
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    //
    // This function should return true, if it accepts the result, in which case the given $url will not be tried again.
    // It should return false, if it rejects the result (for example when a corrupted proxy returned an ad, or when it returned nothing at all).
    //
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function process_result($result){
        $http_code = $result['meta']['http_code'];
        $url = $result['url'];
        $proxy = $result['proxy'];
        $this->dlog("process_result called| code: $http_code url:'{$url}' proxy:'{$proxy}'", 7);
        if($http_code == 200){
            return true;
        }else{
            return false;
        }
    }

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/// Just for example, lets make a new class, with less verbose logging:
class DownloadAndShow extends ParallelProxyCrawler{

    function log_current_lvl_proxies_and_urls($lvl, $proxies, $urls){
        $this->tlog("Start downloading ( remaining tries:{$lvl} ) ...");
        $this->tlog("Using proxies : ". $this->pretty_array( $proxies ) );
        $this->tlog("Using urls    : ". $this->pretty_array( $urls    ) );
        $this->tlog("================================================================================================================");
    }
    
    // If this function returns false for a given result, then its url will be retried with a different proxy.
    // If it returns true, then the url is considered processed, and it will not be tried again in the current batch. 
    // You can put some code to mark it as processed in the DB too, to save it as a file and so on.
    function process_result($result){
        $http_code  = $result['meta']['http_code'];
        $url        = sprintf("%-40s", $result['url']);
        $proxy      = sprintf("%-21s", $result['proxy']);
        $total_time = sprintf("%7.3f", (float) $result['meta']['total_time']);
        
        if($http_code==200){
            $content    = str_replace("\n", ' \n ', substr($result['content'], 0, 10));
            $length     = sprintf("%6d", strlen( $result['content'] ));
            if( ((int)$length) > 0 ){
                $this->tlog("D.time: {$total_time} sec | Proxy: {$proxy} | url: {$url} | len:{$length} | start: '{$content}' ...");
                return true;
            }
        }   
        $this->tlog("D.time: {$total_time} sec | Proxy: {$proxy} | url: {$url} FAILED. Will be retried with another proxy.");
        return false;
    }
}


class SingleUrlRetriever extends ParallelProxyCrawler{
    var $verbose_curl = false;
    var $retrievedcontent = '';
    var $_proxies = array();
    function process_result($result){
        $http_code  = $result['meta']['http_code'];
        $this->retrievedcontent = $result['content'];
        if(  ($http_code==200)  &&  ((int)strlen($this->retrievedcontent) > 0)  ) return true;
        return false;
    }
    function onFinished(){
        return $this->retrievedcontent;
    }
    
    function SingleUrlRetriever($proxies){ //The constructor
        $this->_proxies = $proxies;
    }
    function get($url){
        $this->retrievedcontent = '';
        return $this->parallel_download_using_proxies( $this->_proxies, array($url));
    }
}

function url_get_contents_by_multiple_proxies($url, $proxies=array()){
    $r = new SingleUrlRetriever($proxies);
    return $r->get( $url );
}

/////////////////////////////////////////////////////////////////////////////////////////////////////////

class ParallelProxyDownloader extends ParallelProxyCrawler{
    var $_proxies = array();
    var $url2path = array();
    var $path2url = array();
    var $finished = 0; //How many urls are finished so far
    
    /// Setup (misc overrides):
    function ParallelProxyDownloader( $proxies ){ //The constructor
        $this->_proxies = $proxies;
    }    
    function add_url($url, $path){
        $this->path2url[$path]=$url;
        $this->url2path[$url]=$path;
    }
    function add_many_urls($upaths = array()){
        foreach($upaths as $url => $path ){
            $this->add_url( $url, $path );
        }        
    }
    function start_all(){
        $this->finished = 0;
        $this->parallel_download_using_proxies( $this->_proxies, $this->path2url );
    }    
    function onFinished(){
        $this->on_finish_all();
    }
    function process_result($result){    
        $http_code  = $result['meta']['http_code'];
        $content    = $result['content'];
        $url        = $result['url'];
        $proxy      = $result['proxy'];
        if(  ($http_code==200)  &&  ((int)strlen($content) > 0)  ) {
            $this->on_finish_one( $url, $proxy, $this->url2path[$url], $content );
            return true;
        }
        return false;
    }
    ///////////////////////////////////////////////////////////////////////////////////////////
    
    function on_finish_one($url, $proxy, $path, $content=''){
        $this->finished++;
        $this->dlog("on_finish_one('{$url}', '{$proxy}', '{$path}') | finished so far:{$this->finished}", 100);
        //error_log( "{$content}\n", 3,  $path);
        file_put_contents( $path, $content );
    }
    function on_finish_all(){
        $this->dlog("on_finish_all | finished so far:{$this->finished}", 100);
    }
}
