<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Remove old non-flavor categories
        $oldCategories = ['Cups & Cones', 'Pints & Tubs', 'Sundaes', 'Toppings', 'Beverages'];
        Category::whereIn('name', $oldCategories)->delete();

        $categories = [
            'Vanilla Flavors',
            'Chocolate Flavors',
            'Fruit & Berry Flavors',
            'Nutty & Caramel Flavors',
            'Coffee & Mint Flavors',
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(['name' => $category]);
        }
    }
}
