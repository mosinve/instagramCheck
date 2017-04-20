#!/usr/bin/env php
<?php
// bin/react.php

require 'vendor/autoload.php';

$loop = React\EventLoop\Factory::create();
$socket = new React\Socket\Server(1337,$loop);
$http = new React\Http\Server($socket);

$callback = function (\React\Http\Request $request, \React\Http\Response $response) {
    echo $request->getMethod();
    echo $path = $request->getPath();
    switch ($path) {
        case '/subscription':
            $query = $request->getQueryParams();
            print_r($query);
            $headers = array(
                'Content-Type: application/json',
                $query
            );
            $content = json_encode($query);
            break;
        default:
            $headers = array(
                'Content-Type: text/plain'
            );
            $content = 'Hello World!';
            break;

    }

    $statusCode = 200;


    $response->writeHead($statusCode, $headers);
    $response->end($content);
};

$http->on('request', $callback);

$loop->run();