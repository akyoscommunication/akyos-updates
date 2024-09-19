<?php

namespace AkyosUpdates\Service;

use AkyosUpdates\Class\Hook;

class LoaderService
{
    public const string TYPE_ACTION = 'action';
    public const string TYPE_FILTER = 'filter';

    /**
     * @param Hook $hook
     * @return bool
     */
    public function add(Hook $hook): bool
    {
        if($hook->getType() === self::TYPE_ACTION) {
            add_action( $hook->getHook(), [$hook->getComponent(), $hook->getCallback()], $hook->getPriority(),$hook->getAcceptedArgs());
        }

        if($hook->getType() === self::TYPE_FILTER) {
            add_filter( $hook->getHook(), [$hook->getComponent(), $hook->getCallback()], $hook->getPriority(),$hook->getAcceptedArgs());
        }

        return true;
    }

    /**
     * @param Hook[] $hooks
     * @return bool
     */
    public function bulkAdd(array $hooks): bool
    {
        foreach($hooks as $hook) {
            $this->add($hook);
        }

        return true;
    }
}