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
import Grid from "../../../../../admin1/themes/new-theme/js/components/grid/grid";
import ReloadListActionExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/reload-list-extension";
import ExportToSqlManagerExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/export-to-sql-manager-extension";
import FiltersResetExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/filters-reset-extension";
import SortingExtension from "../../../../../admin1/themes/new-theme/js/components/grid/extension/sorting-extension";
import LinkRowActionExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/link-row-action-extension";
import SubmitBulkExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/submit-bulk-action-extension";
import BulkActionCheckboxExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/bulk-action-checkbox-extension";
import SubmitRowActionExtension
    from "../../../../../admin1/themes/new-theme/js/components/grid/extension/action/row/submit-row-action-extension";

const $ = window.$;

$(() => {
    const attributeSubgroupGrid = new Grid('invertus_mollie_subscription');

    attributeSubgroupGrid.addExtension(new ReloadListActionExtension());
    attributeSubgroupGrid.addExtension(new ExportToSqlManagerExtension());
    attributeSubgroupGrid.addExtension(new FiltersResetExtension());
    attributeSubgroupGrid.addExtension(new SortingExtension());
    attributeSubgroupGrid.addExtension(new LinkRowActionExtension());
    attributeSubgroupGrid.addExtension(new SubmitBulkExtension());
    attributeSubgroupGrid.addExtension(new BulkActionCheckboxExtension());
    attributeSubgroupGrid.addExtension(new SubmitRowActionExtension());
});
