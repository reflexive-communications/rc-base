<?php

/**
 * Contains helper functions for basic unit-testing
 */
class CRM_RcBase_Test_UtilsBasic
{
    /**
     * Gets a restricted property
     *
     * @param mixed $object Object with restricted property
     * @param string $property Name of property
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function getProtectedProperty(&$object, string $property)
    {
        // Get reflection
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($property);

        // Set property accessible
        $property->setAccessible(true);

        // Return value
        return $property->getValue($object);
    }

    /**
     * Sets a restricted property
     *
     * @param mixed $object Object with restricted property
     * @param string $property Name of property
     * @param mixed $value Value to set
     *
     * @throws \ReflectionException
     */
    public static function setProtectedProperty(&$object, string $property, $value): void
    {
        // Get reflection
        $reflection = new ReflectionClass(get_class($object));
        $property = $reflection->getProperty($property);

        // Set property accessible
        $property->setAccessible(true);

        // Set value
        $property->setValue($object, $value);
    }

    /**
     * Invokes private/protected method
     *
     * @param mixed &$object Object with restricted method
     * @param string $method Name of method
     * @param array|null $params Parameters to method
     *
     * @return mixed
     *
     * @throws \ReflectionException
     */
    public static function invokeProtectedMethod(&$object, string $method, array $params = null)
    {
        // Gets reflection
        $reflection = new ReflectionClass(get_class($object));
        $method = $reflection->getMethod($method);

        // Set method accessible
        $method->setAccessible(true);

        // Invoke function needs an array anyway
        if (is_null($params)) {
            $params = [];
        }

        // Invoke method & return results
        return $method->invokeArgs($object, $params);
    }
}
