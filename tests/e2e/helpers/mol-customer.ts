import { runSql, querySingleValue } from './db';

/**
 * `ps_mol_customer` is where the module records the Mollie customer it creates
 * for single-click payments (`CustomerService::processCustomerCreation`). Its
 * `customer_id` is Mollie's own `cst_…` id — the one the next payment is
 * created against, which is what "the customerId is passed on the second
 * payment" means in observable terms.
 */
function quote(value: string): string {
  return value.replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

export function findMollieCustomerId(email: string): string | null {
  return querySingleValue(
    `SELECT customer_id FROM ps_mol_customer WHERE email = '${quote(email)}' ORDER BY id_mol_customer DESC LIMIT 1`
  );
}

export function deleteMollieCustomers(email: string): void {
  runSql(`DELETE FROM ps_mol_customer WHERE email = '${quote(email)}'`);
}

/**
 * The most recent Mollie transaction the module created for one of this
 * customer's carts. `ps_mollie_payments` gets its row when the payment is
 * created — before the customer has done anything on Mollie's side — so a new id
 * appearing here is proof that Mollie ACCEPTED the payment the module built,
 * which is the only observable the shop has for a payment created against a
 * saved customer id.
 */
export function latestTransactionIdForCustomer(email: string): string | null {
  return querySingleValue(
    `SELECT mp.transaction_id FROM ps_mollie_payments mp ` +
    `JOIN ps_cart c ON c.id_cart = mp.cart_id ` +
    `JOIN ps_customer cu ON cu.id_customer = c.id_customer ` +
    `WHERE cu.email = '${quote(email)}' ORDER BY mp.created_at DESC, mp.transaction_id DESC LIMIT 1`
  );
}
