<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review;

/**
 * Return highlighted code
 *
 * @TODO: Make this extensible in a sensible way
 *
 * @version $Revision$
 * @license APGLv3
 */
class CodeProcessorFactory
{
    /**
     * Factory code processor for file
     *
     * @param string $file
     * @return CodeProcessor
     */
    public function factory( $file )
    {
        switch ( strtolower( pathinfo( $file, \PATHINFO_EXTENSION ) ) )
        {
            case 'phpt':
            case 'phps':
            case 'php':
                $codeProcessor = new CodeProcessor\Php();
                break;

            default:
                // @TODO: Instead of throwing an exception we might want to go 
                // with a dumb default…
                throw new \OutOfBoundsException( "No code processor found for $file." );
        }

        $codeProcessor->load( $file );
        return $codeProcessor;
    }
}

