var http = require('http');

console.log("http://localhost:7900/should_be_post");
console.log("http://localhost:7900/should_be_put");

http.createServer(function (req, res) {
    console.log(req.url);
    var data = "";
    if (req.url == '/should_be_post') {
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

    if (req.url == '/should_be_put') {

        req.on("data", function (chunk) {
            data += chunk;
        });

        req.on("end", function () {
            if (req.method == 'PUT' && data == "data") {
                res.writeHead(200);
                res.end("ok");
            } else {
                res.writeHead(400);
                res.end("bad request");
            }
        });

    }


    if (req.url == '/should_be_post_application_json') {
        if (req.method == 'POST' && req.headers['content-type'] === 'application/json') {
            res.writeHead(200);
            res.end("ok");
        } else {
            res.writeHead(400);
            res.end("bad request");
        }

    }
}).listen(7900);

codes = {
    0: {port: 7800, message: ""},
    200: {port: 7820, message: "ok"},
    500: {port: 7850, message: "internal error"},
    404: {port: 7844, message: "not found"},
    302: {port: 7832, message: "redirect"}
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