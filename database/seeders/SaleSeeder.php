<?php

namespace Database\Seeders;

use App\Models\Sale;
use App\Models\Flock;
use App\Models\FarmProduce;
use App\Models\User;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class SaleSeeder extends Seeder
{
    public function run(): void
    {
        // ── 1. Resolve creator (first admin, fallback to any user) ────────
        $creatorId = User::where('role', 'admin')->value('id')
            ?? User::value('id');

        if (!$creatorId) {
            $this->command->error('No users found. Run UserSeeder first.');
            return;
        }

        // ── 2. Resolve flocks ─────────────────────────────────────────────
        $flocks = Flock::all();

        if ($flocks->isEmpty()) {
            $this->command->error('No flocks found. Run FlockSeeder first.');
            return;
        }

        // ── 3. Resolve product types from actual produce records ──────────
        // FarmProduce::getActiveProductTypes() returns the exact strings
        // stored in farm_produces.product_type — free text, no fixed enum.
        $produceTypes = FarmProduce::getActiveProductTypes()->toArray();

        if (empty($produceTypes)) {
            // Fallback: use sensible defaults matching FarmProduce::defaultUnit()
            $produceTypes = ['eggs', 'live_bird', 'meat', 'manure'];
            $this->command->warn(
                'farm_produces table is empty. Seeding with fallback types: '
                . implode(', ', $produceTypes)
                . '. For accurate data, record produce first then re-run this seeder.'
            );
        } else {
            $this->command->info(
                'Found produce types: ' . implode(', ', $produceTypes)
            );
        }

        // ── 4. Pricing per product type ───────────────────────────────────
        // Keyed by the exact strings FarmProduce uses (free-text, lowercased).
        // min/max_qty = quantity range per transaction
        // min/max_price = unit price in GHS
        $pricingMap = [
            // egg variants
            'eggs'           => ['min_qty' => 10,  'max_qty' => 300,  'min_price' => 0.50,  'max_price' => 1.20],
            'eggs_tray'      => ['min_qty' => 1,   'max_qty' => 20,   'min_price' => 12.00, 'max_price' => 18.00],
            'eggs_crate'     => ['min_qty' => 1,   'max_qty' => 8,    'min_price' => 130.00,'max_price' => 180.00],
            'eggs_box'       => ['min_qty' => 1,   'max_qty' => 5,    'min_price' => 155.00,'max_price' => 200.00],
            // birds
            'live_bird'      => ['min_qty' => 1,   'max_qty' => 50,   'min_price' => 35.00, 'max_price' => 80.00],
            'live_sale'      => ['min_qty' => 1,   'max_qty' => 30,   'min_price' => 35.00, 'max_price' => 75.00],
            'breeding_stock' => ['min_qty' => 1,   'max_qty' => 10,   'min_price' => 80.00, 'max_price' => 200.00],
            // meat
            'meat'           => ['min_qty' => 2,   'max_qty' => 30,   'min_price' => 25.00, 'max_price' => 45.00],
            'meat_kg'        => ['min_qty' => 2,   'max_qty' => 30,   'min_price' => 25.00, 'max_price' => 45.00],
            // dairy
            'milk'           => ['min_qty' => 5,   'max_qty' => 80,   'min_price' => 5.00,  'max_price' => 9.00],
            // other produce
            'manure'         => ['min_qty' => 1,   'max_qty' => 20,   'min_price' => 8.00,  'max_price' => 25.00],
            'honey'          => ['min_qty' => 1,   'max_qty' => 15,   'min_price' => 40.00, 'max_price' => 90.00],
            'wool'           => ['min_qty' => 1,   'max_qty' => 10,   'min_price' => 30.00, 'max_price' => 60.00],
            // catch-all for any free-text type not listed above
            '_default'       => ['min_qty' => 1,   'max_qty' => 20,   'min_price' => 10.00, 'max_price' => 50.00],
        ];

        // ── 5. Reference data ─────────────────────────────────────────────
        $customers = [
            'Kofi Mensah',
            'Ama Asante',
            'Yaw Boateng',
            'Akosua Darko',
            'Kwame Osei',
            'Abena Frimpong',
            'Kojo Appiah',
            'Efua Agyeman',
            'Emmanuel Tetteh',
            'Gifty Owusu',
            null, // walk-in
            null,
            null,
        ];

        $paymentMethods = [
            'cash',
            'cash',
            'cash',           // cash is most common
            'mobile_money',
            'mobile_money',
            'bank_transfer',
        ];

        // ── 6. Generate 12 months of sales ───────────────────────────────
        $now         = Carbon::now();
        $totalCreated = 0;

        for ($monthsBack = 11; $monthsBack >= 0; $monthsBack--) {
            $monthStart = $now->copy()->subMonths($monthsBack)->startOfMonth();
            $monthEnd   = $monthsBack === 0
                ? $now->copy()
                : $now->copy()->subMonths($monthsBack)->endOfMonth();

            // Each month: sell 2–4 of the available product types
            $typesThisMonth = collect($produceTypes)
                ->shuffle()
                ->take(min(rand(2, 4), count($produceTypes)))
                ->values();

            foreach ($typesThisMonth as $productType) {
                $pricing = $pricingMap[$productType] ?? $pricingMap['_default'];

                // 2–5 transactions per product type per month
                $txCount = rand(2, 5);

                for ($i = 0; $i < $txCount; $i++) {
                    // Spread sale dates randomly across the month
                    $daysAvailable = max(1, (int) $monthStart->diffInDays($monthEnd));
                    $saleDate      = $monthStart->copy()->addDays(rand(0, $daysAvailable));

                    // Quantity — rounded to 2dp as the model casts expect
                    $quantity = round(
                        rand($pricing['min_qty'] * 100, $pricing['max_qty'] * 100) / 100,
                        2
                    );

                    // Unit price — random within range
                    $unitPrice = round(
                        rand((int)($pricing['min_price'] * 100), (int)($pricing['max_price'] * 100)) / 100,
                        2
                    );

                    // total_amount must match quantity × unit_price exactly
                    $totalAmount = round($quantity * $unitPrice, 2);

                    // 80% chance to tie to a flock, 20% general sale
                    $flockId = (rand(1, 5) > 1) ? $flocks->random()->id : null;

                    // Receipt number: RCP-{TYPE_PREFIX}-{YYYYMM}-{SEQ}
                    $typePrefix = strtoupper(substr(str_replace('_', '', $productType), 0, 4));
                    $receiptNo  = 'RCP-' . $typePrefix . '-' . $saleDate->format('Ym') . '-' . str_pad(rand(1, 999), 3, '0', STR_PAD_LEFT);

                    Sale::create([
                        'flock_id'       => $flockId,
                        'product_type'   => $productType,
                        'quantity'       => $quantity,
                        'unit_price'     => $unitPrice,
                        'total_amount'   => $totalAmount,
                        'sale_date'      => $saleDate->toDateString(),
                        'customer_name'  => $customers[array_rand($customers)],
                        'payment_method' => $paymentMethods[array_rand($paymentMethods)],
                        'receipt_number' => $receiptNo,
                        'description'    => ucfirst(str_replace('_', ' ', $productType)) . ' sale',
                        'notes'          => null,
                        'created_by'     => $creatorId,
                        // Set created_at to match sale_date so charts are accurate
                        'created_at'     => $saleDate->copy()->setTime(rand(7, 17), rand(0, 59)),
                        'updated_at'     => $saleDate->copy()->setTime(rand(7, 17), rand(0, 59)),
                    ]);

                    $totalCreated++;
                }
            }
        }

        $this->command->info("✅ SaleSeeder complete — {$totalCreated} sale records created across 12 months.");
    }
}