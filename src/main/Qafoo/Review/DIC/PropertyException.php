<?php
/**
 * This file is part of the Puppeteer Commons Component.
 *
 * @version $Revision$
 */

namespace Puppeteer\Commons\DIC;

use \Puppeteer\Commons\Throwable;

/**
 * This exception will be thrown if a property does not exist in the DIC.
 *
 * @version $Revision$
 */
class PropertyException extends \InvalidArgumentException implements Throwable
{

}
