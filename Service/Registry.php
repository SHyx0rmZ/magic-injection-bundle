<?php

namespace SHyx0rmZ\MagicInjection\Service;

class Registry implements RegistryInterface
{
    protected $injectables = array();

    /**
     * @inheritdoc
     */
    public function registerInjectable($injectable, $type = null)
    {
        $name = explode('\\', get_class($injectable));
        $name = $name[count($name) - 1];

        if ($type === null) {
            $this->injectables[$name] = $injectable;
        } else {
            if (!isset($this->injectables[$type])) {
                $this->injectables[$type] = array();
            }

            $this->injectables[$type][$name] = $injectable;
        }
    }

    /**
     * @inheritdoc
     */
    public function getInjectable($name, $type = null)
    {
        if ($type === null) {
            if (!isset($this->injectables[$name])) {
                return null;
            }

            return $this->injectables[$name];
        } else {
            if (!isset($this->injectables[$type], $this->injectables[$type][$name])) {
                return null;
            }

            return $this->injectables[$type][$name];
        }
    }
}
