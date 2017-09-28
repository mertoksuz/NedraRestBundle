<?php

namespace Nedra\RestBundle\Metadata;

interface MetadataInterface
{
    /**
     * @return string
     */
    public function getApplicationName();

    /**
     * @return string
     */
    public function getName();

    /**
     * @return string
     */
    public function getPluralName();
}
