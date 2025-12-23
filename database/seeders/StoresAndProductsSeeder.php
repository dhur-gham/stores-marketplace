<?php

namespace Database\Seeders;

use App\Enums\ProductStatus;
use App\Enums\ProductType;
use App\Enums\StoreType;
use App\Models\Product;
use App\Models\Store;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class StoresAndProductsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get or create a user
        $user = User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Get existing images
        $store_image = 'stores/01KD3A96YHQ5W610DP7FCZJWN5.png';
        $product_image = 'products/01KD3AJEZSGPFBN3DWHH710FYC.png';

        // Store data with nice names
        $stores_data = [
            [
                'name' => 'TechHub Electronics',
                'bio' => 'Your one-stop shop for the latest electronics and gadgets. We offer premium quality products with excellent customer service.',
                'type' => StoreType::Physical,
            ],
            [
                'name' => 'Digital Dreams',
                'bio' => 'Premium digital products and software solutions for your business and personal needs.',
                'type' => StoreType::Digital,
            ],
            [
                'name' => 'Fashion Forward',
                'bio' => 'Trendy clothing and accessories for the modern fashion enthusiast. Stay stylish with our curated collection.',
                'type' => StoreType::Physical,
            ],
            [
                'name' => 'Home & Garden Paradise',
                'bio' => 'Everything you need to make your home beautiful. From furniture to garden tools, we have it all.',
                'type' => StoreType::Physical,
            ],
            [
                'name' => 'Bookworm Haven',
                'bio' => 'A vast collection of books, e-books, and educational materials. Feed your mind with knowledge.',
                'type' => StoreType::Digital,
            ],
        ];

        // Product names for each store
        $products_data = [
            // TechHub Electronics products
            [
                ['name' => 'Wireless Bluetooth Headphones', 'description' => 'Premium noise-cancelling headphones with 30-hour battery life and crystal-clear sound quality.', 'price' => 125000, 'stock' => 15],
                ['name' => 'Smart Watch Pro', 'description' => 'Feature-rich smartwatch with health tracking, GPS, and smartphone connectivity.', 'price' => 200000, 'stock' => 8],
                ['name' => 'Portable Power Bank', 'description' => 'High-capacity 20000mAh power bank with fast charging technology for all your devices.', 'price' => 35000, 'stock' => 25],
                ['name' => 'USB-C Hub Adapter', 'description' => 'Multi-port USB-C hub with HDMI, USB 3.0, and SD card reader support.', 'price' => 45000, 'stock' => 12],
                ['name' => 'Wireless Mouse', 'description' => 'Ergonomic wireless mouse with precision tracking and long battery life.', 'price' => 25000, 'stock' => 20],
            ],
            // Digital Dreams products
            [
                ['name' => 'Premium Photo Editing Software', 'description' => 'Professional photo editing suite with advanced tools and filters for creative professionals.', 'price' => 150000, 'stock' => 999],
                ['name' => 'Project Management Tool', 'description' => 'Complete project management solution with team collaboration and task tracking features.', 'price' => 180000, 'stock' => 999],
                ['name' => 'Cloud Storage Plan', 'description' => 'Secure cloud storage with 1TB capacity and automatic backup synchronization.', 'price' => 80000, 'stock' => 999],
                ['name' => 'Video Editing Suite', 'description' => 'Professional video editing software with 4K support and advanced effects library.', 'price' => 220000, 'stock' => 999],
                ['name' => 'Online Course Platform', 'description' => 'Comprehensive online learning platform with interactive courses and certifications.', 'price' => 120000, 'stock' => 999],
            ],
            // Fashion Forward products
            [
                ['name' => 'Classic Denim Jacket', 'description' => 'Timeless denim jacket made from premium cotton with a perfect fit for any occasion.', 'price' => 75000, 'stock' => 10],
                ['name' => 'Leather Crossbody Bag', 'description' => 'Elegant leather bag with adjustable strap and multiple compartments for your essentials.', 'price' => 95000, 'stock' => 6],
                ['name' => 'Designer Sunglasses', 'description' => 'Stylish sunglasses with UV protection and polarized lenses for eye comfort.', 'price' => 55000, 'stock' => 14],
                ['name' => 'Casual Sneakers', 'description' => 'Comfortable sneakers with cushioned insoles and breathable material for all-day wear.', 'price' => 65000, 'stock' => 18],
                ['name' => 'Silk Scarf Collection', 'description' => 'Luxurious silk scarves in various patterns and colors to complement any outfit.', 'price' => 45000, 'stock' => 9],
            ],
            // Home & Garden Paradise products
            [
                ['name' => 'Indoor Plant Set', 'description' => 'Beautiful collection of low-maintenance indoor plants perfect for home decoration.', 'price' => 35000, 'stock' => 22],
                ['name' => 'Garden Tool Kit', 'description' => 'Complete gardening tool set with ergonomic handles and rust-resistant materials.', 'price' => 85000, 'stock' => 11],
                ['name' => 'Decorative Throw Pillows', 'description' => 'Set of four premium throw pillows with various patterns to enhance your living space.', 'price' => 55000, 'stock' => 16],
                ['name' => 'LED String Lights', 'description' => 'Warm white LED string lights perfect for creating ambiance in any room or outdoor space.', 'price' => 25000, 'stock' => 30],
                ['name' => 'Ceramic Dinnerware Set', 'description' => 'Elegant 16-piece ceramic dinnerware set for everyday use and special occasions.', 'price' => 120000, 'stock' => 7],
            ],
            // Bookworm Haven products
            [
                ['name' => 'E-Book Reader Device', 'description' => 'Advanced e-reader with paper-like display and built-in library access.', 'price' => 180000, 'stock' => 999],
                ['name' => 'Digital Library Subscription', 'description' => 'Unlimited access to thousands of e-books, audiobooks, and magazines for one year.', 'price' => 90000, 'stock' => 999],
                ['name' => 'Language Learning App', 'description' => 'Interactive language learning application with personalized lessons and progress tracking.', 'price' => 70000, 'stock' => 999],
                ['name' => 'Audiobook Collection', 'description' => 'Premium collection of professionally narrated audiobooks across multiple genres.', 'price' => 110000, 'stock' => 999],
                ['name' => 'Study Planner Software', 'description' => 'Digital study planner with scheduling tools and progress tracking for students.', 'price' => 50000, 'stock' => 999],
            ],
        ];

        // Create stores and products
        foreach ($stores_data as $index => $store_data) {
            $store = Store::create([
                'name' => $store_data['name'],
                'slug' => Str::slug($store_data['name']).'-'.Str::random(8),
                'bio' => $store_data['bio'],
                'image' => $store_image,
                'type' => $store_data['type'],
                'user_id' => $user->id,
            ]);

            // Create 5 products for this store
            foreach ($products_data[$index] as $product_data) {
                Product::create([
                    'store_id' => $store->id,
                    'user_id' => $user->id,
                    'name' => $product_data['name'],
                    'slug' => Str::slug($product_data['name']).'-'.Str::random(8),
                    'image' => $product_image,
                    'description' => $product_data['description'],
                    'sku' => 'SKU-'.strtoupper(Str::random(4)).'-'.rand(1000, 9999),
                    'status' => ProductStatus::Active,
                    'type' => $store->type === StoreType::Digital ? ProductType::Digital : ProductType::Physical,
                    'price' => $product_data['price'],
                    'stock' => $product_data['stock'],
                ]);
            }
        }

        $this->command->info('Successfully created 5 stores with 5 products each!');
    }
}
