<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Item;
use Illuminate\Database\Seeder;

class ItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $itemsByCategory = [
            'Vanilla Flavors' => [
                [
                    'name' => 'Madagascar Vanilla Bean',
                    'description' => 'Classic rich vanilla crafted with real bourbon vanilla beans.',
                    'price' => 3.50,
                    'quantity' => 50,
                ],
                [
                    'name' => 'French Vanilla Custard',
                    'description' => 'Silky, rich egg custard-style traditional vanilla ice cream.',
                    'price' => 3.80,
                    'quantity' => 40,
                ],
            ],
            'Chocolate Flavors' => [
                [
                    'name' => 'Belgian Dark Chocolate',
                    'description' => 'Intense and velvety 70% dark Belgian cocoa ice cream.',
                    'price' => 4.20,
                    'quantity' => 45,
                ],
                [
                    'name' => 'Chocolate Fudge Brownie',
                    'description' => 'Rich chocolate ice cream packed with chewy homemade brownie chunks.',
                    'price' => 4.50,
                    'quantity' => 35,
                ],
            ],
            'Fruit & Berry Flavors' => [
                [
                    'name' => 'Wild Strawberry Swirl',
                    'description' => 'Fresh strawberry ice cream blended with sweet wild strawberry ribbons.',
                    'price' => 3.90,
                    'quantity' => 40,
                ],
                [
                    'name' => 'Alphonso Mango Sorbet',
                    'description' => 'Refreshing tropical Alphonso mango sorbet with vibrant natural sweetness.',
                    'price' => 4.00,
                    'quantity' => 30,
                ],
            ],
            'Nutty & Caramel Flavors' => [
                [
                    'name' => 'Salted Caramel Crunch',
                    'description' => 'Sweet and salty caramel cream swirled with buttery praline brittle.',
                    'price' => 4.50,
                    'quantity' => 35,
                ],
                [
                    'name' => 'Sicilian Roasted Pistachio',
                    'description' => 'Creamy whole milk ice cream flavored with authentic roasted Sicilian pistachios.',
                    'price' => 4.80,
                    'quantity' => 25,
                ],
            ],
            'Coffee & Mint Flavors' => [
                [
                    'name' => 'Espresso Mocha Chip',
                    'description' => 'Bold dark-roast espresso cream loaded with bittersweet dark chocolate chips.',
                    'price' => 4.20,
                    'quantity' => 30,
                ],
                [
                    'name' => 'Cool Mint Chocolate Chip',
                    'description' => 'Refreshing natural garden mint ice cream packed with fine dark chocolate flakes.',
                    'price' => 3.90,
                    'quantity' => 40,
                ],
            ],
        ];

        foreach ($itemsByCategory as $categoryName => $items) {
            $category = Category::where('name', $categoryName)->first();

            if (! $category) {
                continue;
            }

            foreach ($items as $itemData) {
                Item::updateOrCreate(
                    [
                        'category_id' => $category->id,
                        'name' => $itemData['name'],
                    ],
                    [
                        'description' => $itemData['description'],
                        'price' => $itemData['price'],
                        'quantity' => $itemData['quantity'],
                    ]
                );
            }
        }
    }
}
