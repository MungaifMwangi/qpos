<?php

namespace Tests\Unit;

use App\Services\LedgerService;
use Exception;
use Tests\TestCase;

class LedgerServiceTest extends TestCase
{
    public function test_unbalanced_entry_is_rejected_before_database_write(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Unbalanced Journal Entry');

        app(LedgerService::class)->postJournalEntry('Invalid entry', [
            ['account_code' => '1010', 'debit' => 100, 'credit' => 0],
            ['account_code' => '4000', 'debit' => 0, 'credit' => 99],
        ]);
    }

    public function test_line_cannot_have_both_a_debit_and_credit(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('either a positive debit or a positive credit');

        app(LedgerService::class)->postJournalEntry('Invalid line', [
            ['account_code' => '1010', 'debit' => 100, 'credit' => 1],
            ['account_code' => '4000', 'debit' => 0, 'credit' => 99],
        ]);
    }
}
