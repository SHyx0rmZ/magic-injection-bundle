<?php

namespace SHyx0rmZ\MagicInjection\Service;

interface RegistryInterface
{
    /**
     * @param object $injectable
     * @param null|string $type
     * @return void
     */
    public function registerInjectable($injectable, $type = null);

    /**
     * @param string $name
     * @param null|string $type
     * @return null|object
     */
    public function getInjectable($name, $type = null);
}
