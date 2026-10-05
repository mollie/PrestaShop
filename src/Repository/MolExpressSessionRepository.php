<?php
/**
 * Mollie       https://www.mollie.nl
 *
 * @author      Mollie B.V. <info@mollie.nl>
 * @copyright   Mollie B.V.
 * @license     https://github.com/mollie/PrestaShop/blob/master/LICENSE.md
 *
 * @see        https://github.com/mollie/PrestaShop
 * @codingStandardsIgnoreStart
 */

namespace Mollie\Repository;

use Mollie\Shared\Infrastructure\Repository\AbstractRepository;

if (!defined('_PS_VERSION_')) {
    exit;
}

class MolExpressSessionRepository extends AbstractRepository implements MolExpressSessionRepositoryInterface
{
    public function __construct()
    {
        parent::__construct(\MolExpressSession::class);
    }

    public function findLatestByCartId(int $cartId): ?\MolExpressSession
    {
        /** @var \PrestaShopCollection|null $sessions */
        $sessions = $this->findAllBy(['id_cart' => $cartId]);

        if (!$sessions) {
            return null;
        }

        $sessions->orderBy('id_mol_express_session', 'desc');

        /** @var \MolExpressSession|false $session */
        $session = $sessions->getFirst();

        return $session ?: null;
    }

    public function persist(\MolExpressSession $session): void
    {
        $session->save();
    }

    public function deleteByCartId(int $cartId): void
    {
        \Db::getInstance()->delete('mol_express_session', 'id_cart = ' . (int) $cartId);
    }
}
