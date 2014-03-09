var http = require('http');

var server = http.createServer(function(req, res) {
    console.log(req.url);
    if(req.url == '/ok') {
        res.writeHead(200);
        res.end("ok");
    }

    res.writeHead(404);
    res.end("Not found.")
}).listen(7878);