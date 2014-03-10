var http = require('http');

var server = http.createServer(function(req, res) {
    console.log(req.url);
    var codePattern = new RegExp(/^\/code\/(.*)$/);
    var matches = req.url.match(codePattern);

    if(matches != null) {
        res.writeHead(matches[1]);
        res.end(matches[1]);
    }

    res.writeHead(404);
    res.end("Not found.")
}).listen(7878);