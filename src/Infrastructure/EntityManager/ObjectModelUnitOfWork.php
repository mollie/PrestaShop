<?php

namespace Mollie\Infrastructure\EntityManager;

/** In memory entity manager object model unit of work */
class ObjectModelUnitOfWork
{
    public const UNIT_OF_WORK_SAVE = 'UNIT_OF_WORK_SAVE';
    public const UNIT_OF_WORK_DELETE = 'UNIT_OF_WORK_DELETE';

    private $work = [];

    public function setWork(\ObjectModel $objectModel, string $unitOfWorkType, ?string $specificKey): void
    {
        $work = [
            'unit_of_work_type' => $unitOfWorkType,
            'object' => $objectModel,
        ];

        if (!is_null($specificKey)) {
            $this->work[$specificKey] = $work;
        } else {
            $this->work[] = $work;
        }
    }

    /**
     * @return array<string, \ObjectModel>
     */
    public function getWork(): array
    {
        return $this->work;
    }

    public function clearWork(): void
    {
        $this->work = [];
    }
}
