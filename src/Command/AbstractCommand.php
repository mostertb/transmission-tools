<?php

namespace Mostertb\TransmissionTools\Command;


use Symfony\Component\Console\Command\Command;
use Symfony\Component\Lock\LockFactory;
use Symfony\Component\Lock\LockInterface;
use Symfony\Component\Lock\Store\FlockStore;

class AbstractCommand extends Command
{
    /**
     * @var LockInterface
     */
    private $lock;

    /**
     * Intended to called during initialize(). Will get a local filesystem lock unique to this command that will prevent
     * two versions of the command from being run on the same system
     * @throws \Exception
     */
    protected function acquireLock()
    {
        $store = new FlockStore(__DIR__.'/../../var/lock');
        $factory = new LockFactory($store);

        $this->lock = $factory->createLock($this->getName());
        if(!$this->lock->acquire()){
            throw new \Exception('Could not acquire lock as the \''.$this->getName().'\' command is already executing. '.
                'Bailing out');
        }
    }
}