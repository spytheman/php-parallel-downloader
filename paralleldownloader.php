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

class ParallelDownloader{
    var $cookiefile = '/tmp/cookie.txt';
    var $referrer   = 'http://www.google.com/';
    var $useragent  = "Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/535.7 (KHTML, like Gecko) Chrome/16.0.912.77 Safari/535.7"; // NB: Change this to something more popular :-)
    var $debug = true;
    var $verbose_curl = false; // set this to true to enable very verbose curl debugging info on strerr

    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    // Generic utilities:
    //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
    function tlog($s){
        $d = date("Y/m/d H:i:s");
        echo "$d| $s\n";
    }
    
    function dlog($s){
        if($this->debug){
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
        curl_setopt($ch, CURLOPT_USERAGENT, $this->useragent);

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

    
    function parallel_download_using_proxies($proxies, $urls, $lvl = 20){
        if($lvl < 0)return;

        $proxies = array_unique( $proxies );
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
        }

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
                    $this->dlog("META INFO FOR URL: '{$url}'");
                }
            }
            //$this->dlog("EXEC_STATUS:{$exec_status} running:{$running} ready:{$ready}");
        } while( $exec_status == CURLM_CALL_MULTI_PERFORM || $running > 0);

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
        $this->dlog("Curl processing is done");
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
                }                
            }else{
                $ec = $results[$url]['errno'];
                $e  = $results[$url]['error'];
                $this->dlog("INFO: META:no  ec:{$ec}  error:'{$e}'  failed proxy:'{$sproxy}' failed url:'{$url}'  len:{$slen}  res:'{$sstring}'...");
                $failedurls[]=$url;
                $failedproxies[]=$proxy;
            }
        }
        
        // The common sense says, that a failed proxy should not be used again in the reiteration of the same batch,
        // since it is highly probable that it will fail again:
        $newproxies = array_diff( $proxies ,  $failedproxies );
        $newurls    = array_unique( $failedurls );
        if(count($newurls)>0){
            $this->parallel_download_using_proxies( $newproxies, $newurls, $lvl - 1);
        }
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
        $this->dlog("process_result called| code: $http_code url:'{$url}' proxy:'{$proxy}'");
        if($http_code == 200){
            return true;
        }else{
            return false;
        }
    }

}

/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

/// Just for example, lets make a new class, with less verbose logging:
class DownloadAndShow extends ParallelDownloader{
    var $debug = false;

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

