<?php

namespace SHyx0rmZ\MagicInjection\Service\Annotation;

use Doctrine\Common\Annotations\Annotation;

/**
 * Class MagicInjection
 * @package SHyx0rmZ\MagicInjection\Services\Annotation
 * @Annotation
 */
class MagicInjection
{
    public $type;

    public function getType()
    {
        return $this->type;
    }
}
