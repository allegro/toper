var http = require('http');

console.log("http://localhost:7900/should_be_post");

http.createServer(function (req, res) {
    console.log(req.url);
    if (req.url == '/should_be_post') {
        var data = "";
        req.on("data", function (chunk) {
            data += chunk;
        });

        req.on("end", function () {
            if (req.method == 'POST' && data == "data") {
                res.writeHead(200);
                res.end("ok");
            } else {
                res.writeHead(400);
                res.end("bad request");
            }
        });

    }
}).listen(7900);

codes = {
    0: {port: 7800, message: ""},
    200: {port: 7820, message: "ok"},
    500: {port: 7850, message: "internal error"},
    404: {port: 7844, message: "not found"}
};

for (var code in codes) {
    console.log("start server on port: " + codes[code].port);
    createPortServer(code);
}

function createPortServer(code) {
    http.createServer(function (req, res) {
        res.writeHead(code);
        res.end(codes[code].message)
    }).listen(codes[code].port);
}