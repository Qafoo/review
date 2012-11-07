<?php
/**
 * This file is part of qaReview
 *
 * @version $Revision$
 * @license APGLv3
 * @copyright Qafoo GmbH
 */

namespace Qafoo\Review\View;
use Qafoo\Review\Struct;
use Qafoo\RMF\Request;

/**
 * Base MySQLi connection class
 *
 * @version $Revision$
 * @license APGLv3
 */
class Twig extends \Qafoo\RMF\View
{
    /**
     * Twig environment
     *
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * Construct from twig environment
     *
     * @param \Twig_Environment $twig
     * @return void
     */
    public function __construct( \Twig_Environment $twig )
    {
        $this->twig = $twig;
    }

    /**
     * Display the controller result
     *
     * @param Request $request
     * @param mixed $result
     * @return void
     */
    public function display( Request $request, $result )
    {
        if ( $result instanceof \Exception )
        {
            echo $this->twig->render(
                'error.twig',
                array(
                    'exception' => $result,
                )
            );
            return;
        }

        if ( !$result instanceof Struct\Response )
        {
            echo json_encode( $result );
            return;
        }

        echo $this->twig->render(
            $result->template,
            $result->data
        );
    }
}

