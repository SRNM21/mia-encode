<?php

namespace App\Services;

use App\Models\Bank;
use App\Models\BankApplication;
use App\Models\Client;
use App\Models\RequestEdit;
use DateTime;

class RequestEditService
{
    public function __construct(
        private BankApplicationService $bankApplicationService
    ) {}

    public function stillExists(string $id)
    {
        return RequestEdit::get($id);
    }

    public function update(string $id, array $data) 
    {
        // Check if the updated data is an existing client
        $client = Client::query()
            ->where('first_name', '=', $data['first_name'])
            ->where('middle_name', '=', $data['middle_name'])
            ->where('last_name', '=', $data['last_name'])
            ->where('birthdate', '=', $data['birthdate'])
            ->where('mobile_num', '=', $data['mobile'])
            ->first();

        $bank_app = BankApplication::get($data['app_id']);

        if ($client) 
        {
            // Handle duplicate bank applications. Remove the application
            // if the replaced client already has an application to that 
            // bank, while keeping the available bank applications
            
            $banks = Bank::getAll();
            $apps = $this->bankApplicationService->getClientApplications($client->id);

            $expiry_map = [];
            foreach ($banks as $bank) 
            {
                $expiry_map[$bank->id] = $bank->expiry_months;
            }

            // submitted bank applications of the current client to update
            $banks_submitted = json_decode($bank_app->bank_submitted_id, true) ?? [];

            // latest submitted bank applications of an existing client
            $lates_submittion_of_client = json_decode($apps->bank_submitted_id, true) ?? [];

            $latestSubmittedAt = $apps->date_submitted ? new DateTime($apps->date_submitted) : null;

            if ($latestSubmittedAt && !empty($lates_submittion_of_client)) 
            {
                foreach ($banks_submitted as $key => $bankId) 
                {
                    // check if bank exists in latest submission
                    if (in_array($bankId, $lates_submittion_of_client)) 
                    {
                        // check expiry config exists
                        if (!isset($expiry_map[$bankId])) continue;

                        $expiryMonths = $expiry_map[$bankId];

                        // compute expiry date
                        $expiryDate = (clone $latestSubmittedAt)->modify("+{$expiryMonths} months");
                        $now = new DateTime();

                        // if NOT expired, remove from banks_submitted
                        if ($now < $expiryDate) 
                        {
                            unset($banks_submitted[$key]);
                        }
                    }
                }

                // Reindex array after unset
                $banks_submitted = array_values($banks_submitted);
            }

            if (empty($banks_submitted))
            {
                // remove application if bank submitted applications 
                // is empty due to application deduplication
                BankApplication::delete($data['app_id']);
            }
            else 
            {
                // delete the previous client
                Client::delete($bank_app->client_id);

                // replace client id and applications on bank_application_tbl
                BankApplication::update(['id' => $bank_app->id], [
                    'client_id' => $client->id,
                    'bank_submitted_id' => json_encode($banks_submitted)
                ]);
            }
        }
        else 
        {
            // if not exist, update client details in client_table
            Client::update(['id' => $bank_app->client_id], [
                'first_name' => $data['first_name'],
                'middle_name' => $data['middle_name'],
                'last_name' => $data['last_name'],
                'birthdate' => $data['birthdate'],
                'mobile_num' => $data['mobile'],
            ]);
        }

        RequestEdit::update(['id' => $id], [
            'status' => 'approved',
            'datetime_action' => date('Y-m-d H:i:s')
        ]);

        BankApplication::update(['id' => $bank_app->id], [
            'agent' => $data['agent']
        ]);
    }

}