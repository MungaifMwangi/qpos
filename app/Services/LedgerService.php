<?php

namespace App\Services;

use App\Models\ChartOfAccount;
use App\Models\JournalEntry;
use App\Models\JournalEntryLine;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;

class LedgerService
{
    /**
     * Post a balanced double-entry journal entry.
     *
     * @param string $narration
     * @param array $lines Array of ['account_code' => '1010', 'debit' => 100, 'credit' => 0, 'memo' => '...']
     * @param string|null $referenceType
     * @param int|null $referenceId
     * @param int|null $userId
     * @return JournalEntry
     * @throws Exception
     */
    public function postJournalEntry(
        string $narration,
        array $lines,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $userId = null
    ): JournalEntry {
        // Enforce double-entry rule: total debits must equal total credits
        $totalDebitCents = 0;
        $totalCreditCents = 0;

        foreach ($lines as $line) {
            if (empty($line['account_code'])) {
                throw new Exception('Every journal line must identify an account code.');
            }
            $debitCents = (int) round(((float) ($line['debit'] ?? 0)) * 100);
            $creditCents = (int) round(((float) ($line['credit'] ?? 0)) * 100);
            if ($debitCents < 0 || $creditCents < 0 || ($debitCents > 0 && $creditCents > 0) || ($debitCents === 0 && $creditCents === 0)) {
                throw new Exception('Each journal line must contain either a positive debit or a positive credit.');
            }
            $totalDebitCents += $debitCents;
            $totalCreditCents += $creditCents;
        }

        if ($totalDebitCents !== $totalCreditCents) {
            throw new Exception("Unbalanced Journal Entry: Debits (" . number_format($totalDebitCents / 100, 2) . ") do not equal Credits (" . number_format($totalCreditCents / 100, 2) . ").");
        }

        if (count($lines) < 2) {
            throw new Exception("Journal Entry must contain at least two line items (debit and credit).");
        }

        return DB::transaction(function () use ($narration, $lines, $referenceType, $referenceId, $userId) {
            $entryNumber = 'JE-' . date('Ymd') . '-' . strtoupper(Str::random(10));

            $entry = JournalEntry::create([
                'entry_number' => $entryNumber,
                'entry_date' => date('Y-m-d'),
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'narration' => $narration,
                'status' => 'posted',
                'created_by' => $userId ?? auth()->id(),
            ]);

            foreach ($lines as $line) {
                $account = ChartOfAccount::where('code', $line['account_code'])->first();
                if (!$account) {
                    throw new Exception("Chart of Account with code '{$line['account_code']}' not found.");
                }

                JournalEntryLine::create([
                    'journal_entry_id' => $entry->id,
                    'chart_of_account_id' => $account->id,
                    'debit' => floatval($line['debit'] ?? 0),
                    'credit' => floatval($line['credit'] ?? 0),
                    'memo' => $line['memo'] ?? null,
                ]);
            }

            return $entry;
        });
    }

    /**
     * Create a reversing journal entry to reverse a previously posted entry.
     * Never delete general ledger entries.
     */
    public function reverseJournalEntry(JournalEntry $entry, string $reason, ?int $userId = null): JournalEntry
    {
        if ($entry->status === 'reversed') {
            throw new Exception("Journal entry {$entry->entry_number} is already reversed.");
        }

        $reversingLines = [];
        foreach ($entry->lines as $line) {
            $reversingLines[] = [
                'account_code' => $line->account->code,
                'debit' => $line->credit, // Swap debit and credit
                'credit' => $line->debit,
                'memo' => "Reversal of {$entry->entry_number}: " . ($line->memo ?? ''),
            ];
        }

        return DB::transaction(function () use ($entry, $reversingLines, $reason, $userId) {
            $entry->update(['status' => 'reversed']);

            return $this->postJournalEntry(
                "REVERSAL: " . $entry->narration . " (Reason: {$reason})",
                $reversingLines,
                $entry->reference_type,
                $entry->reference_id,
                $userId
            );
        });
    }
}
