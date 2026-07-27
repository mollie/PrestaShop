// Stub registry — replaced by the full, shape-based registry in Task 10.
export const paymentMethods: Array<{
  id: string;
  apis: ReadonlyArray<'orders' | 'payments'>;
}> = [
  { id: 'bancontact', apis: ['orders'] },
  { id: 'ideal', apis: ['payments'] },
];
