<?php
/**
 * This file is part of the Puppeteer Commons Component.
 *
 * @version $Revision$
 */

namespace Puppeteer\Commons\DIC;

use \Puppeteer\Commons\Throwable;

/**
 * This exception will be thrown if something bad happens inside the DIC.
 *
 * @version $Revision$
 */
class RuntimeException extends \RuntimeException implements Throwable
{

}
