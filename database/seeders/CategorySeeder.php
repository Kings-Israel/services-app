<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = ['Construction', 'Computer Repair', 'Electronic Repair', 'House Cleaning', 'Mason', 'Laundry', 'Plumbing', 'Chef'];

        collect($categories)->each(fn ($category) => Category::create(['name' => $category]));
    }
}
