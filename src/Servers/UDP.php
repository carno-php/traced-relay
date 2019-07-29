<?php
/**
 * UDP Server
 * User: moyo
 * Date: Jul 22, 2019
 * Time: 15:13
 */

namespace Carno\Traced\Relays\Servers;

use Carno\Net\Address;
use Carno\Net\Events;
use Carno\Serv\Powered\Swoole\ServerBase;
use Carno\Traced\Relays\Transfer;
use Swoole\Server;

class UDP extends ServerBase implements Adaptor
{
    /**
     * @var string
     */
    protected $serviced = 'traced:relays:udp';

    /**
     * @var array
     */
    protected $acceptEvs = ['packet'];

    /**
     * @var Address
     */
    private $listen = null;

    /**
     * @var int
     */
    private $workers = null;

    /**
     * @var Server
     */
    private $server = null;

    /**
     * @var Transfer
     */
    private $transfer = null;

    /**
     * UDP constructor.
     * @param Address $listen
     * @param int $workers
     */
    public function __construct(Address $listen, int $workers = 2)
    {
        $this->listen = $listen;
        $this->workers = $workers;
    }

    /**
     * @param Transfer $transfer
     * @return Adaptor
     */
    public function transfer(Transfer $transfer) : Adaptor
    {
        $this->transfer = $transfer;
        return $this;
    }

    /**
     * @param Events $events
     */
    public function running(Events $events) : void
    {
        $this->server = $this->standardServerCreate(
            $this->listen,
            $events,
            Server::class,
            ['workers_num' => $this->workers],
            substr($this->listen->host(), 0, 1) === '/'
                ? SWOOLE_SOCK_UNIX_DGRAM
                : SWOOLE_SOCK_UDP
        );
        $this->serve();
    }

    /**
     */
    public function exiting() : void
    {
        $this->server->stop();
        $this->shutdown();
    }

    /**
     */
    public function serve() : void
    {
        $this->server->start();
    }

    /**
     */
    public function shutdown() : void
    {
        $this->server->shutdown();
    }

    /**
     * @param Server $serv
     * @param string $data
     * @param array $client
     */
    public function evPacket(Server $serv, string $data, array $client) : void
    {
        debug() && logger('traced')->debug(
            'Received packet',
            [
                'from' => sprintf(
                    '%s:%d',
                    $client['address'] ?: 'ux-sock',
                    $client['port'] ?? $client['server_socket']
                ),
                'size' => strlen($data),
                'data' => $data,
            ]
        );

        $this->transfer->loading($data);
    }
}
