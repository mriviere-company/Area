<?php

namespace App\EventListener;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class KernelControllerListener
 *
 *      app.listener.kernel_controller:
 *          class: AppBundle\EventListener\KernelControllerListener
 *          arguments: ['@security.token_storage', '@doctrine.orm.entity_manager']
 *          tags:
 *              - { name: kernel.event_subscriber }
 *
 *
 * @package AppBundle\EventListener
 */
class UserEvent implements EventSubscriberInterface
{
    /**
     * @var TokenStorageInterface
     */
    private $token;

    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * KernelControllerListener constructor.
     * @param TokenStorageInterface $token
     * @param EntityManagerInterface $em
     */
    public function __construct(TokenStorageInterface $token, EntityManagerInterface $em)
    {
        $this->token = $token;
        $this->em = $em;
    }

    /**
     * Returns an array of event names this subscriber wants to listen to.
     *
     * The array keys are event names and the value can be:
     *
     *  * The method name to call (priority defaults to 0)
     *  * An array composed of the method name to call and the priority
     *  * An array of arrays composed of the method names to call and respective
     *    priorities, or 0 if unset
     *
     * For instance:
     *
     *  * array('eventName' => 'methodName')
     *  * array('eventName' => array('methodName', $priority))
     *  * array('eventName' => array(array('methodName1', $priority), array('methodName2')))
     *
     * @return array The event names to listen to
     */
    public static function getSubscribedEvents(): array
    {
        return array(KernelEvents::CONTROLLER => "onCoreController");
    }

    /**
     * @param ControllerEvent $event
     */
    public function onCoreController(ControllerEvent $event)
    {
        /*if($event->getRequestType() == HttpKernel::MASTER_REQUEST)
        {
            if($this->token->getToken()) {
                $user = $this->token->getToken()->getUser();

                if ($user instanceof User) {
                }
            }
        }*/
    }
}