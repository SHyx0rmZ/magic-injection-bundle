<?php

namespace SHyx0rmZ\MagicInjection\Service;

interface RegistryInterface
{
    public function registerInjectable($injectable, $type = null);

    public function getInjectable($name, $type = null);
}
