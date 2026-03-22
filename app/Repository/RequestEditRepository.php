<?php

namespace App\Repository;

use App\Models\RequestEdit;

class RequestEditRepository
{
    public function fetchRequest(string $order, string $filter) 
    {
        $query = RequestEdit::query()
            ->select(
                'request_edit_tbl.*',
                'bank_application_tbl.id AS bank_application_id',
                'bank_application_tbl.client_id',
                'bank_application_tbl.bank_submitted_id',
                'bank_application_tbl.date_submitted',

                'client_tbl.first_name',
                'client_tbl.middle_name',
                'client_tbl.last_name',
                'client_tbl.birthdate',
                'client_tbl.mobile_num'
            )
            ->join('bank_application_tbl', 'request_edit_tbl.app_id', '=', 'bank_application_tbl.id')
            ->join('client_tbl', 'bank_application_tbl.client_id', '=', 'client_tbl.id')
            ->orderBy('request_edit_tbl.datetime_request', $order);

        if ($filter != 'all')
        {
            $query = $query->where('status', '=', $filter);
        }

        return $query->get();
    }
}