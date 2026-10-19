# Owner pricing authority - 2026-10-19

| Requirement | Implementation / acceptance |
| --- | --- |
| Exact owner USD prices | Native product prices and bulk import accept any valid cents. Optional .99 generated sale rounding. |
| Editable exchange rate | Instapass dashboard rate panel, validation and stale-form guard. |
| Automatic PKR | USD regular/sale independently converted at saved rate; unit rounded nearest100. |
| Independent PKR exception | Product editor or PKR bulk/Excel action creates fixed regular/sale values. USD/rate changes preserve them. |
| Return to automatic PKR | Selected restore-auto action or Excel PKR mode auto; preview quote, guarded apply and undo restore raw override absence. |
| Independent GBP | Separate manually entered regular/sale/discount; never inferred from USD/PKR. |
| Location and choice | Existing GB=>GBP, PK=>PKR, elsewhere USD; manual selector wins. Blank GBP offers remain unavailable with USD switch. |
| Discounts | Global/selected/Excel USD/PKR/GBP preview/apply/history/undo. Existing owner bundle code settings preserved. |
| Monthly display | Full-term prices drive monthly equivalents and best eligible configured bundle presentation. |
| Integrity | Preserve stock/status/orders/rate/coupons/gateways during controls deployment. Isolated native and real-browser tests before live readback. |

Pricing batches change sale prices; existing bundle codes can apply additionally when eligible. They remain one code per order with configured selection thresholds. No automatic coupon application or new account requirements are introduced. Editing the rate affects new automatic PKR quotes; historical orders retain their saved currency/amount/rate. No sample bulk changes may be applied to production during verification.
