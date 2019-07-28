<?php
/**
 * Tracer server -> start
 * User: moyo
 * Date: Jul 22, 2019
 * Time: 14:29
 */

namespace Carno\Traced\Relays\Commands;

use Carno\Console\Based;
use Carno\Console\Configure;
use Carno\Console\Contracts\Application;
use Carno\Net\Address;
use Carno\Serving\Options\Common;
use Carno\Traced\Relays\Servers\UDP;
use Carno\Traced\Relays\Serving;
use Symfony\Component\Console\Input\InputOption;
use Exception;

class ServerStart extends Based
{
    use Common;

    private const OPT_SOCK_BIND = 'socket-bind';
    private const OPT_TRACING_ADDR = 'tracing-addr';

    /**
     * @var bool
     */
    protected $ready = false;

    /**
     * @var string
     */
    protected $name = 'server:start';

    /**
     * @var string
     */
    protected $description = 'Start the Traced relays server';

    /**
     * @param Configure $conf
     */
    protected function options(Configure $conf) : void
    {
        $conf->addOption(
            self::OPT_SOCK_BIND,
            null,
            InputOption::VALUE_REQUIRED,
            'Socket listen',
            'udp://~/tmp/traced-relays.sock'
        );

        $conf->addOption(
            self::OPT_TRACING_ADDR,
            null,
            InputOption::VALUE_REQUIRED,
            'Tracing Addr',
            'zipkin://127.0.0.1'
        );
    }

    /**
     * @param Application $app
     * @throws Exception
     */
    protected function firing(Application $app)
    {
        $serving = new Serving($app->conf(), $app->starting(), $app->input()->getOption(self::OPT_TRACING_ADDR));

        $parsed = parse_url($app->input()->getOption(self::OPT_SOCK_BIND));
        switch ($parsed['scheme']) {
            case 'udp':
                $server = new UDP(
                    $parsed['host'] === '~'
                        ? new Address($parsed['path'])
                        : new Address($parsed['host'], $parsed['port'])
                );
                goto START;
                break;
            default:
                throw new Exception(sprintf('Unknown %s scheme', self::OPT_SOCK_BIND));
        }

        START:

        $serving
            ->bootstrap($this->bootstrap())
            ->wants($app->starting(), $app->stopping())
            ->start($server)
        ;
    }
}
