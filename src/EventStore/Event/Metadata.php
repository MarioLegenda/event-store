<?php

namespace EventStore\Event;

use DocBlockReader\Reader;

class Metadata
{
    /**
     * @var string $objectHash
     */
    private $objectHash;
    /**
     * @var string $name
     */
    private $name;
    /**
     * @var array $metadata
     */
    private $metadata = [];
    /**
     * Metadata constructor.
     * @param string $name
     * @param $object
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function __construct(
        string $name,
        $object
    ) {
        $this->name = $name;
        $this->objectHash = spl_object_hash($object);

        $properties = (new \ReflectionClass($object))->getProperties();

        $this->resolveMetadata($properties, $object);
    }
    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }
    /**
     * @return string
     */
    public function getObjectHash(): string
    {
        return $this->objectHash;
    }
    /**
     * @return array
     */
    public function getMetadata(): array
    {
        $values = [];
        foreach ($this->metadata as $propertyName => $m) {
            $values[$propertyName] = $m['value'];
        }

        return $values;
    }
    /**
     * @param string $propertyName
     * @return bool
     */
    public function hasProperty(string $propertyName): bool
    {
        return array_key_exists($propertyName, $this->metadata);
    }
    /**
     * @param string $propertyName
     * @return bool|null
     */
    public function getValueForProperty(string $propertyName)
    {
        if (!$this->hasProperty($propertyName)) {
            return null;
        }

        return $this->metadata[$propertyName];
    }
    /**
     * @param string $propertyName
     * @return array|null
     */
    public function getMetadataForProperty(string $propertyName): ?array
    {
        if (!$this->hasProperty($propertyName)) {
            return null;
        }

        return [
            $propertyName => $this->getValueForProperty($propertyName),
        ];
    }
    /**
     * @param array $reflectionProperties
     * @param $object
     * @throws \Exception
     */
    private function resolveMetadata(
        array $reflectionProperties,
        $object
    ) {
        /** @var \ReflectionProperty $property */
        foreach ($reflectionProperties as $property) {
            $property->setAccessible(true);
            $reader = new Reader($object, $property->getName(), 'property');

            if ($reader->getParameter('EventPayload') !== null) {
                $event = $this->resolveEvent($reader->getParameter('EventPayload'));

                if ($event === null) {
                    continue;
                }

                $this->metadata[$property->getName()] = [
                    'value' => $property->getValue($object),
                    'event' => $event,
                ];
            }
        }
    }
    /**
     * @param string $events
     * @return string
     */
    private function resolveEvent(string $events): ?string
    {
        $eventNames = explode(',', $events);

        foreach ($eventNames as $eventName) {
            if (empty($eventName)) {
                $message = sprintf('You provided no event names for object \'%s\'', get_class($object));
                throw new \RuntimeException($message);
            }

            $eventName = trim($eventName);
            if ($eventName === $this->getName()) {
                return $eventName;
            }
        }

        return null;
    }
}