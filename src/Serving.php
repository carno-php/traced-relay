<?php
/**
 * Serving ins
 * User: moyo
 * Date: Jul 22, 2019
 * Time: 15:32
 */

namespace Carno\Traced\Relays;

use Carno\Config\Config;
use Carno\Console\Boot\Waited;
use Carno\Container\DI;
use Carno\Net\Events\Worker;
use Carno\Serving\Chips\Boots;
use Carno\Serving\Chips\Events;
use Carno\Serving\Chips\Plugins;
use Carno\Serving\Chips\Wants;
use Carno\Traced\Relays\Servers\Adaptor;
use Carno\Tracing\Contracts\Platform;

class Serving
{
    use Plugins;
    use Events;
    use Wants;
    use Boots;

    /**
     * @var Config
     */
    private $conf = null;

    /**
     * @var Waited
     */
    private $starts = null;

    /**
     * @var string
     */
    private $scheme = null;

    /**
     * @var string
     */
    private $endpoint = null;

    /**
     * Serving constructor.
     * @param Config $conf
     * @param Waited $starts
     * @param string $endpoint
     */
    public function __construct(Config $conf, Waited $starts, string $endpoint)
    {
        $this->conf = $conf;
        $this->starts = $starts;
        $this->scheme = parse_url($endpoint)['scheme'] ?? 'unknown';
        $this->endpoint = $endpoint;
    }

    /**
     * @param Adaptor $adaptor
     */
    public function start(Adaptor $adaptor) : void
    {
        $config = $this->conf;
        $endpoint = $this->endpoint;

        $this->starts->add(static function () use ($config, $endpoint) {
            $config->set('tracing.addr', $endpoint);
        });

        $adaptor
            ->transfer(new Transfer(DI::get(Platform::class), $this->scheme))
            ->running(
                $this->events()->attach(Worker::STOPPED, static function () use ($adaptor) {
                    $adaptor->exiting();
                })
            )
        ;
    }
}
