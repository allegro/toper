var http = require('http');

codes = {
    0: {port: 7800, message: ""},
    200: {port: 7820, message: "ok"},
    500: {port: 7850, message: "internal error"},
    404: {port: 7844, message: "not found"}
};

for (var code in codes) {
    console.log("start server on port: " + codes[code].port);
    createServer(code);
}

function createServer(code) {
    http.createServer(function (req, res) {
        res.writeHead(code);
        res.end(codes[code].message)
    }).listen(codes[code].port);
}