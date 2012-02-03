#!/usr/bin/env node

var http = require('http');
var util = require('util');

//console.log("Process params length:"+process.argv.length);
//console.log("Process params:");
//process.argv.forEach(function (val, index, array) {  console.log(index + ': ' + val); });

var slowport    = 3128; if(process.argv.length>2) slowport    = 1*process.argv[2];
var time_base   = 2000; if(process.argv.length>3) time_base   = 1*process.argv[3];
var time_jitter = 1000; if(process.argv.length>4) time_jitter = 1*process.argv[4];

/////////////////////////////////////////////////////////////////////////////////////////////

function handleError(e){
    util.log("handleError called");
    util.debug("handleError: "+e);
}

process.on('error', handleError);


var nresponses = 0;
var server = http.createServer(
    function(request, response) {        
        request.addListener('error', handleError );

        var r=nresponses++;

        var d = time_base;
        d += Math.floor( time_jitter * Math.random() );
        var buffer = new Buffer(2*1024);
        var offset = 0;
        var done = false;

        var tid = setInterval(function(){
                                  if(done){
                                      clearInterval(tid);
                                      setTimeout(function(){
                                                     response.end( buffer.slice(0,offset) , 'binary');
                                                     util.log("Request: "+r+" DONE");                                                     
                                                 }, d);
                                  }
                              }, 100);


        var proxy = http.createClient(80, request.headers['host']);
        var proxy_request = proxy.request(request.method, request.url, request.headers);
        proxy.addListener('error', handleError);
        proxy_request.addListener('error', handleError);


        proxy_request.addListener('response', 
                                  function (proxy_response) {

                                      proxy_response.addListener('data',
                                                                 function(chunk) {
                                                                     if(chunk.length>0){
                                                                       // Double the size of the buffer, instead of overflowing it:
                                                                       if( offset + chunk.length > buffer.length ){
                                                                         var buf2 = new Buffer( buffer.length*2 );
                                                                         buffer.copy(buf2, 0, 0);
                                                                         buffer = buf2;
                                                                         util.log("Request: "+r+" growing buffer. New size:"+buffer.length);
                                                                       }
                                                                       
                                                                       var cstart=chunk.slice(0, Math.min(3, chunk.length));
                                                                       util.log("Response "+r+"  clength:"+chunk.length+"  offset:"+offset+" returned some chunk starting with:'"+cstart+"'...");
                                                                       
                                                                       chunk.copy(buffer, offset, 0);
                                                                       offset+=chunk.length;
                                                                     }
                                                                 });
                                      proxy_response.addListener('end', 
                                                                 function(){
                                                                     done = true;
                                                                 });

                                      response.writeHead(proxy_response.statusCode, proxy_response.headers);

                                  });

        request.addListener('data', 
                            function(chunk) {
                                proxy_request.write(chunk, 'binary');
                            });
        request.addListener('end',  
                            function(){
                                proxy_request.end();
                            });

        util.log("New request :"+r+" will be delayed by "+d+"ms. Details: "+request.connection.remoteAddress + ": " + request.method + " " + request.url);
        util.log("request :"+r+" starting ...");

    });
server.addListener('error', handleError);
server.maxConnections = 100;
server.listen(slowport);
util.log("Slow http proxy server is started with timebase = "+time_base+"ms, and timejitter = +-"+time_jitter+"ms");
util.log("URL: http://127.0.0.1:"+slowport);


process.on('exit', 
           function() {
               util.log("Exiting...");
           });

