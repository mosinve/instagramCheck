<?php
/**
 * Created by PhpStorm.
 * User: MosinVE
 * Date: 18.04.2017
 * Time: 23:10
 */


require "vendor/autoload.php";
require_once 'checker.config.php';
use instaCheck\SessionStorage;
use instaCheck\Checker;


$data = new SessionStorage();
$data->set('config', $config);

$checker = new Checker($config, $data);

$loop = React\EventLoop\Factory::create();

$server = stream_socket_server('tcp://127.0.0.1:8080');
stream_set_blocking($server, 0);

$loop->addReadStream($server, function ($server) use ($loop) {
    $conn = stream_socket_accept($server);
    $data = "HTTP/1.1 200 OK\r\nContent-Length: 3\r\n\r\nHi\n";
    $loop->addWriteStream($conn, function ($conn) use (&$data, $loop) {
        $written = fwrite($conn, $data);
        if ($written === strlen($data)) {
            fclose($conn);
            $loop->removeStream($conn);
        } else {
            $data = substr($data, $written);
        }
    });
});

$loop->addPeriodicTimer(86400, function () {
    global $checker;
    global $config;
    echo $checker->sortFollowers($config['followersGrp']);
});

$loop->run();