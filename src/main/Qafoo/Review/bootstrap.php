<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

// @codeCoverageIgnoreStart
// @codingStandardsIgnoreStart

require __DIR__ . '/../../../library/autoload.php';

spl_autoload_register(
    function ( $class )
    {
        if ( 0 === strpos( $class, __NAMESPACE__ ) )
        {
            include __DIR__ . '/../../' . strtr( $class, '\\', '/' ) . '.php';
        }
    }
);

// @codingStandardsIgnoreEnd
// @codeCoverageIgnoreEnd
