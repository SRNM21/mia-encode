<?php

namespace App\Http\Controllers;

use App\Core\Controllers\Controller;
use App\Core\Facades\Auth;
use App\Http\Request\Request;
use App\Models\Bank;
use App\Models\RequestEdit;
use App\Services\RequestEditService;
use Exception;

class RequestEditController extends Controller
{
    public function __construct(
        private RequestEditService $requestEditService
    ) {}

    public function show(Request $request) 
    {
        $order = strtolower($request->get('order') ?? 'desc');
        $filter = strtolower($request->get('filter') ?? 'all');

        if (!in_array($order, ['asc', 'desc'], true)) 
        {
            $order = 'desc';
        }

        if (!in_array($filter, ['all', 'pending', 'approved', 'rejected'], true)) 
        {
            $filter = 'all';
        }

        $requests = $this->requestEditService->fetchRequest($order, $filter);
        $banks = Bank::getAll();

        return $this->view('requests', [
            'requests' => $requests,
            'order' => $order,
            'filter' => $filter,
            'banks' => $banks
        ]);
    }

    public function read(Request $request) 
    {
        $id = $request->input('id');

        try
        {
            RequestEdit::update(['id' => $id], [
                'is_read' => true
            ]);
            
            $this->responseJson([]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Read request failed. [' . $e->getCode() .']'
            ], 500);
        }
    }

    public function store(Request $request) 
    {
        /** @var \App\Models\User */
        $user = Auth::user();

        if ($user->isEncoder()) 
        {
            $encoder =  $user->username;
        }

        try
        {
            $request = RequestEdit::create([
                'encoder' => $encoder ?? 'Anonymous',
                'app_id' => $request->post('app_id'),
                'old' => $request->post('old_agent'),
                'new' => $request->post('new_agent')
            ]);

            $this->responseJson($request->toArray());
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Request edit failed. [' . $e->getCode() .']'
            ], 500);
        }
    }
    
    public function destroy(Request $request)
    {
        try
        {
            RequestEdit::delete($request->input('id'));

            $this->responseJson([
                'title' => 'Edit Request Cancelled',
                'message' => 'Edit request is successfully cancelled.',
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Request edit failed. [' . $e->getCode() .']'
            ], 500);
        }
    }

    // Prevent accessing cancelled/removed edit request
    public function requestExistGuard(string $id)
    {
        $stillExist = $this->requestEditService->stillExists($id);

        if (!$stillExist)
        {
            $this->responseJson([
                'title' => 'Edit Request Not Found',
                'message' => 'Edit Request must be deleted or cancelled by the encoder'
            ], 404);
        }
    }

    public function approve(Request $request) 
    {
        try
        {
            $id = $request->input('id');
            $data = $request->input('data');

            $this->requestExistGuard($id);
            $this->requestEditService->update($id, $data);
            
            $this->responseJson([
                'title' => 'Edit Request Approved',
                'message' => 'Edit request is successfully approved.',
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Approve request edit failed. [' . $e->getCode() .']'
            ], 500);
        }
    }

    public function reject(Request $request) 
    {
        try
        {
            $this->requestExistGuard($request->input('id'));

            RequestEdit::update(['id' => $request->input('id')], [
                'status' => 'rejected',
                'datetime_action' => date('Y-m-d H:i:s')
            ]);

            $this->responseJson([
                'title' => 'Edit Request Rejected',
                'message' => 'Edit request is successfully rejected.',
            ]);
        }
        catch (Exception $e)
        {
            $this->responseJson([
                'title' => 'Error Occured',
                'message' => 'Reject request edit failed. [' . $e->getCode() .']'
            ], 500);
        }
    }
}