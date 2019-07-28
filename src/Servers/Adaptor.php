<?php
/**
 * Servers adaptor
 * User: moyo
 * Date: Jul 22, 2019
 * Time: 17:07
 */

namespace Carno\Traced\Relays\Servers;

use Carno\Net\Events;
use Carno\Traced\Relays\Transfer;

interface Adaptor
{
    /**
     * @param Transfer $transfer
     * @return static
     */
    public function transfer(Transfer $transfer) : self;

    /**
     * @param Events $events
     */
    public function running(Events $events) : void;

    /**
     */
    public function exiting() : void;
}
