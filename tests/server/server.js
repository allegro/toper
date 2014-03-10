var http = require('http');

http.createServer(function(req, res) {
    console.log(req.url);
    var codePattern = new RegExp(/^\/code\/([0-9]+)/);
    var matches = req.url.match(codePattern);

    if(matches != null) {
        res.writeHead(matches[1]);
        res.end(matches[1]);
    }

    res.writeHead(404);
    res.end("Not found.")
}).listen(7878);

http.createServer(function(req, res) {
    console.log(req.url);


    res.writeHead(500);
    res.end("Internal server error.")
}).listen(7850);
