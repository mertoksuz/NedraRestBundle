<?php

namespace MertOksuz\ApiBundle\Component;

interface MetadataInterface
{
    /**
     * @return string
     */
    public function getAlias();

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
    public function getHumanizedName();

    /**
     * @return string
     */
    public function getPluralName();

    /**
     * @return string
     */
    public function getDriver();

    /**
     * @param string $name
     *
     * @return string|array
     *
     * @throws \InvalidArgumentException
     */
    public function getParameter($name);

    /**
     * Return all the metadata parameters.
     *
     * @return array
     */
    public function getParameters();

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasParameter($name);

    /**
     * @param string $name
     *
     * @return string|array
     *
     * @throws \InvalidArgumentException
     */
    public function getClass($name);

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasClass($name);

    /**
     * @param string $serviceName
     *
     * @return string
     */
    public function getServiceId($serviceName);

    /**
     * @param string $permissionName
     *
     * @return string
     */
    public function getPermissionCode($permissionName);
}
