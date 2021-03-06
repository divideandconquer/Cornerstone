<?php
/**
 *
 * @author    Oakensoul (http://www.oakensoul.com/)
 * @link      https://github.com/web-masons/Cornerstone for the canonical source repository
 * @copyright Copyright (c) 2013, github.com/web-masons Contributors
 * @license   http://opensource.org/licenses/Apache-2.0 Apache-2.0-Clause
 * @package   Cornerstone
 */
namespace Cornerstone\Http\Listener;

use Zend\EventManager\EventManagerInterface;
use Zend\Mvc\MvcEvent;
use Zend\EventManager\AbstractListenerAggregate;
use Zend\Http\PhpEnvironment\Request;

class Scheme extends AbstractListenerAggregate
{

    protected $mServiceLocator;

    /**
     * {@inheritDoc}
     */
    public function attach (EventManagerInterface $events)
    {
        /**
         * add onDispatch event to Dispatcher
         */
        $options = array ();
        $options[] = $this;
        $options[] = 'onDispatch';

        $this->listeners[] = $events->attach(MvcEvent::EVENT_DISPATCH, $options, 100);
    }

    public function onDispatch (MvcEvent $pEvent)
    {
        $request = $pEvent->getRequest();

        // Make sure that we are not running in a console
        if ($request instanceof Request)
        {
            /* @var \Zend\Mvc\Router\Http\RouteMatch $match */
            $match = $pEvent->getRouteMatch();

            /**
             * This code basically just makes sure that when we dispatch
             * a route the user is forced to SSL if the route is configured
             * to enable the feature
             */
            if (true === $match->getParam('force_https_scheme', false))
            {
                $uri = $request->getUri();

                if ($uri->getScheme() !== "https")
                {
                    $uri->setScheme('https');

                    /* @var \Zend\Http\PhpEnvironment\Response $response */
                    $response = $pEvent->getResponse();

                    $response->setStatusCode(302);
                    $response->getHeaders()->addHeaderLine('Location', $uri);
                    $response->sendHeaders();
                    return $response;
                }
            }
        }

        return NULL;
    }
}
