<?php
/**
 * Payload Transfer
 * User: moyo
 * Date: Jul 23, 2019
 * Time: 15:30
 */

namespace Carno\Traced\Relays;

use Carno\Traced\Transport\UDPRelays;
use Carno\Tracing\Contracts\Platform;

class Transfer
{
    /**
     * @var Platform
     */
    private $platform = null;

    /**
     * @var string
     */
    private $schemed = null;

    /**
     * Transfer constructor.
     * @param Platform $platform
     * @param string $schemed
     */
    public function __construct(Platform $platform, string $schemed = null)
    {
        $this->platform = $platform;
        $this->schemed = $schemed;
    }

    /**
     * @param string $packet
     */
    public function loading(string $packet) : void
    {
        if (substr($packet, 0, 4) !== UDPRelays::MAGIC) {
            return;
        }

        $sLen = unpack('N', substr($packet, 4, 4))[1];
        $sDat = substr($packet, 8, $sLen);

        if ($this->schemed && ($this->schemed !== $sDat)) {
            return;
        }

        $pLen = unpack('N', substr($packet, 8 + $sLen, 4))[1];
        $pDat = substr($packet, 12 + $sLen, $pLen);

        if ($this->platform->joined()) {
            $this->platform->transporter()->loading($pDat);
        }
    }
}
