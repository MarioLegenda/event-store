<?php

namespace EventStore\Event;

use DocBlockReader\Reader;

class MetadataFactory implements \IteratorAggregate
{
    /**
     * @var array $tempMetadata
     */
    private $tempMetadata = [];
    /**
     * MetadataFactory constructor.
     * @param $object
     * @throws \Exception
     */
    public function __construct($object)
    {
        $eventStoreNames = $this->extractEventStoreName($object);

        foreach ($eventStoreNames as $eventStoreName) {
            $this->tempMetadata[$eventStoreName] = [
                'event' => $eventStoreName,
                'object' => $object,
            ];
        }
    }
    /**
     * @return array
     */
    public function getIterator(): array
    {
        return $this->tempMetadata;
    }
    /**
     * @param string $event
     * @param object $object
     * @return Metadata
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function create(string $event, $object): Metadata
    {
        return new Metadata($event, $object);
    }
    /**
     * @return array
     * @throws \Exception
     * @throws \ReflectionException
     */
    public function createAll(): array
    {
        $metadataObjects = [];
        foreach ($this->tempMetadata as $key => $metadata) {
            $metadataObjects[] = $this->create(
                $metadata['event'],
                $metadata['object']
            );
        }

        return $metadataObjects;
    }
    /**
     * @param $object
     * @return array
     * @throws \Exception
     */
    private function extractEventStoreName($object): array
    {
        $reader = new Reader($object);

        $eventStoreParameter = $reader->getParameter('EventStore');

        $this->validateEventStoreParameter($eventStoreParameter);

        $eventNames = explode(',', $eventStoreParameter);

        return $this->resolveEventNames($eventNames, $object);
    }
    /**
     * @param array|null $parameter
     * @throws \RuntimeException
     */
    private function validateEventStoreParameter($parameter)
    {
        if (!is_string($parameter)) {
            $message = sprintf('Invalid annotations. There is no \'EventStore\' annotation.');
            throw new \RuntimeException($message);
        }
    }
    /**
     * @param array $eventNames
     * @param $object
     * @throws \RuntimeException
     * @return array
     */
    private function resolveEventNames(array $eventNames, $object): array
    {
        $temp = [];
        foreach ($eventNames as $eventName) {
            if (empty($eventName)) {
                $message = sprintf('You provided no event names for object \'%s\'', get_class($object));
                throw new \RuntimeException($message);
            }

            $temp[] = trim($eventName);
        }

        return $temp;
    }
}