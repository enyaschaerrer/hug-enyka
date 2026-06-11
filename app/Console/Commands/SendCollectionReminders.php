<?php

namespace App\Console\Commands;

use App\Mail\CollectionReminderMail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendCollectionReminders extends Command
{
    protected $signature = 'reminders:send';

    protected $description = 'Envoie les rappels de don dont la date (reminder_at) est atteinte, puis vide les champs de rappel.';

    public function handle(): int
    {
        $due = DB::table('collections_users')
            ->join('collections', 'collections.id', '=', 'collections_users.collection_id')
            ->join('companies', 'companies.id', '=', 'collections.company_id')
            ->whereNotNull('collections_users.reminder_at')
            ->whereNotNull('collections_users.reminder_email')
            ->where('collections_users.reminder_at', '<=', now())
            ->select(
                'collections_users.id as pivot_id',
                'collections_users.reminder_email',
                'companies.name as company_name',
                'companies.slug',
                'collections.access_token',
            )
            ->get();

        foreach ($due as $row) {
            $eligibilityUrl = route('public.collecte.cobranded.eligibility', [
                'brand' => $row->slug,
                'token' => $row->access_token,
            ]);

            Mail::send(new CollectionReminderMail(
                $row->reminder_email,
                $row->company_name,
                $eligibilityUrl,
            ));

            // Rappel envoyé → on vide les deux champs (pas de colonne « sent »).
            DB::table('collections_users')->where('id', $row->pivot_id)->update([
                'reminder_at'    => null,
                'reminder_email' => null,
            ]);
        }

        $this->info($due->count() . ' rappel(s) envoyé(s).');

        return self::SUCCESS;
    }
}
