<?php

namespace App\Console\Commands;

use App\Services\DiscountService;
use Illuminate\Console\Command;

class ProcessDiscountPlans extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'discounts:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process discount plans - activate scheduled plans and expire active plans';

    /**
     * Execute the console command.
     */
    public function handle(DiscountService $discount_service): int
    {
        $this->info('Processing discount plans...');

        $discount_service->processPlans();

        $this->info('Discount plans processed successfully.');

        return Command::SUCCESS;
    }
}
