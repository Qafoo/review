<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\View\Twig;

/**
 * Custom twig extension
 *
 * @version $Revision$
 */
class Extension extends \Twig_Extension
{
    /**
     * get extension name
     *
     * @return string
     */
    public function getName()
    {
        return 'qareview';
    }

    /**
     * Returns a list of functions to add to the existing list.
     *
     * @return array An array of functions
     */
    public function getFunctions()
    {
        return array(
            'hash' => new \Twig_Function_Function( 'md5' ),
        );
    }
}

