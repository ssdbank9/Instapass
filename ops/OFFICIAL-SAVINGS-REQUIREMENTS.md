# Official-price savings — 2026-10-19

User requirements: calculated savings percentages rounded to nearest whole percent; savings shown at top against official100USD/month Claude Max5x,20USD/month Claude Pro and ChatGPT Plus; mention taxes absorbed and potential additional savings; add a tax FAQ.

Source facts verified2026-10-19: https://claude.com/pricing lists Pro20USD monthly (200USD annual separately), Max from100USD monthly and applicable taxes excluded. https://help.openai.com/en/articles/6950777-what-is-chatgpt-plus lists Plus20USD/month and no official annual billing. Comparison must explicitly use official US month-to-month price for the same number of months, not claim an official annual-plan price. Do not extend100USD benchmark toMax20x or other plans.

Implementation assumptions: round calculated savings percentages only, retain exact money and7.5% coupon. Published prices/coupon rules/tax configuration/order history stay unchanged. USD comparisons use actual selected-currency price; PKR benchmarks convert the US reference at the editable owner rate and label that conversion. GBP remains independent: show USD reference only, never invent a GBP conversion or savings percentage against incompatible currencies. Owner-created linked tenors inherit their confirmed family benchmark; unrelated combinations do not.

Acceptance: arithmetic, compound coupons, actual durations, owner price/rate edits, independent PKR and GBP, unsupported/unpriced/nonpositive-saving cases; actual native product render plus mobile/desktop browser order of savings above price, source/tax wording and FAQ. Guard deployment hashes and commercial data; live checks submit no orders.

Owner follow-up: Pro is offered for1month and1year; annual should match Plus annual. Current Plus source173.33USD/fixedPKR47500/GBPblank. Set existing Pro duration/family without altering its11.99regular/8.99sale; publish one new annual Pro matching those Plus prices and independent GBPblank. Owner has been told that173.33annual exceeds12purchases at Pro's current8.99monthly sale. Use official monthly comparison, not an invented saving against our monthly sale.
