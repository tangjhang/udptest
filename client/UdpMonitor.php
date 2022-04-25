<?php
namespace src;

use Workerman\Connection\AsyncUdpConnection;
use Workerman\Timer;

class UdpMonitor
{
    private $path;

    private $host;

    private $port;

    private $command;

    private $systemName;

    public function __construct($path, $host, $port, $command, $systemName)
    {
        $this->path = $path;
        $this->host = $host;
        $this->port = $port;
        $this->command = $command;
        $this->systemName = $systemName;
    }

    public function onWorkerStart()
    {
        Timer::add(55, function () {
            $udp_connection = new AsyncUdpConnection($this->host . ':' . $this->port);
            $commandStr = shell_exec('php ' . $this->path . ' ' . $this->command);
            $data = json_encode(['data' => $commandStr, 'systemName' => $this->systemName]);
            $udp_connection->onConnect = function ($udp_connection) use ($data) {
                $udp_connection->send($data);
                $udp_connection->close();
            };
            $udp_connection->connect();

        });
    }
}