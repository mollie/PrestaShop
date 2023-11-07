<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 */

namespace Mollie\Infrastructure\EntityManager;

class ObjectModelManager implements EntityManagerInterface
{
    private $unitOfWork;

    public function __construct(ObjectModelUnitOfWork $unitOfWork)
    {
        $this->unitOfWork = $unitOfWork;
    }

    public function persist(
        \ObjectModel $model,
        string $unitOfWorkType,
        ?string $specificKey = null
    ): EntityManagerInterface {
        $this->unitOfWork->setWork($model, $unitOfWorkType, $specificKey);

        return $this;
    }

    public function flush(): array
    {
        $persistenceModels = $this->unitOfWork->getWork();
        $persistedModels = [];

        foreach ($persistenceModels as $externalId => $persistenceModel) {
            if ($persistenceModel['unit_of_work_type'] === ObjectModelUnitOfWork::UNIT_OF_WORK_SAVE) {
                $persistenceModel['object']->save();
            }

            if ($persistenceModel['unit_of_work_type'] === ObjectModelUnitOfWork::UNIT_OF_WORK_DELETE) {
                $persistenceModel['object']->delete();
            }

            if (!empty($externalId)) {
                $persistedModels[$externalId] = $persistenceModel['object'];
            } else {
                $persistedModels[] = $persistenceModel['object'];
            }
        }

        $this->unitOfWork->clearWork();

        return $persistedModels;
    }
}
