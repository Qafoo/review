<?php
/**
 * This file is part of the Puppeteer Commons Component.
 *
 * @version $Revision$
 * @license APGLv3
 */

namespace Puppeteer\Commons\DIC;

use \Puppeteer\Commons\Throwable;

/**
 * This exception will be thrown if something bad happens inside the DIC.
 *
 * @version $Revision$
 * @license APGLv3
 */
class RuntimeException extends \RuntimeException implements Throwable
{

}
