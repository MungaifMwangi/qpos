<?php

namespace Database\Seeders;

use App\Models\ChartOfAccount;
use Illuminate\Database\Seeder;

class ChartOfAccountsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accounts = [
            [
                'code' => '1010',
                'name' => 'Cash on Hand',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'Physical cash register and till balance',
            ],
            [
                'code' => '1020',
                'name' => 'M-Pesa Clearing',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'M-Pesa STK Push settlements clearing account',
            ],
            [
                'code' => '1030',
                'name' => 'Bank Account',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'Primary commercial bank account',
            ],
            [
                'code' => '1100',
                'name' => 'Accounts Receivable (Debtors)',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'Customer credit accounts outstanding',
            ],
            [
                'code' => '1200',
                'name' => 'Merchandise Inventory',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'Valuation of store inventory',
            ],
            [
                'code' => '1300',
                'name' => 'Input VAT Receivable',
                'type' => 'asset',
                'normal_balance' => 'debit',
                'description' => 'VAT paid on supplier purchases/expenses',
            ],
            [
                'code' => '2100',
                'name' => 'Accounts Payable (Creditors)',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'description' => 'Supplier trade credit balances',
            ],
            [
                'code' => '2200',
                'name' => 'Output VAT Payable',
                'type' => 'liability',
                'normal_balance' => 'credit',
                'description' => 'VAT collected on POS sales',
            ],
            [
                'code' => '3000',
                'name' => "Owner's Equity",
                'type' => 'equity',
                'normal_balance' => 'credit',
                'description' => 'Capital invested in business',
            ],
            [
                'code' => '4000',
                'name' => 'POS Sales Revenue',
                'type' => 'revenue',
                'normal_balance' => 'credit',
                'description' => 'Income generated from retail/restaurant sales',
            ],
            [
                'code' => '5000',
                'name' => 'Cost of Goods Sold (COGS)',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'description' => 'Direct cost of inventory sold',
            ],
            [
                'code' => '6000',
                'name' => 'General & Operating Expenses',
                'type' => 'expense',
                'normal_balance' => 'debit',
                'description' => 'Utilities, rent, salaries, and operational costs',
            ],
        ];

        foreach ($accounts as $account) {
            ChartOfAccount::updateOrCreate(['code' => $account['code']], $account);
        }
    }
}
