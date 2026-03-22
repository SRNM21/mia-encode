<?php
    $full_name = trim(
        $request->first_name . ' ' .
        ($request->middle_name ? $request->middle_name . ' ' : '') .
        $request->last_name
    );

    $bank_map = [];
    $banks_submitted = json_decode($request->bank_submitted_id, true) ?? [];

    foreach ($banks as $bank) 
    {
        $bank_map[$bank->id] = $bank->name;
    }

    $bank_names = array_map(function ($id) use ($bank_map) {
        return $bank_map[$id] ?? null;
    }, $banks_submitted);

    $bank_names = array_filter($bank_names);
    $bank_names = array_values($bank_names);
?>

<div 
    class="request-container card <?= $request->is_read ? '' : 'unread' ?> <?= $request->status ?>"
    data-request-edit-id='<?= $request->id ?>' 
    data-request-app-id='<?= $request->app_id ?>' 
    data-request-encoder='<?= $request->encoder ?>' 
    data-request-old='<?= $request->old ?>' 
    data-request-new='<?= $request->new ?>' 
    data-request-status='<?= $request->status ?>' 
    data-request-datetime='<?= $request->datetime_request ?>' 
    data-action-datetime='<?= $request->datetime_action ?>' 
    data-request-is-read='<?= $request->is_read ?>'

    data-bank-submitted-id='<?= $request->bank_submitted_id ?>'
    data-date-submitted='<?= $request->date_submitted ?>'

    data-client-first-name='<?= $request->first_name ?>'
    data-client-middle-name='<?= $request->middle_name ?>'
    data-client-last-name='<?= $request->last_name ?>'
    data-client-birthdate='<?= $request->birthdate ?>'
    data-client-mobile='<?= $request->mobile_num ?>'
    
    data-banks='<?= htmlspecialchars(json_encode($bank_names), ENT_QUOTES, 'UTF-8') ?>'
>
    <div class="request-container-header flex-row">
        <p class="container-title">Edit agent request</pc>
        <p class="container-past-time" title="<?= formatDate($request->datetime_request, 'M j, Y g:i A') ?>" ><?= time_ago($request->datetime_request) ?></pc>
    </div>
    <div class="request-container-body">
        <p><?= $request->encoder ?> sent an edit agent request on <strong><?= $full_name ?></strong>'s application
        (submitted on <?= formatDate($request->date_submitted, 'M j, Y') ?>) on <?= formatDate($request->datetime_request, 'M j, Y g:i A') ?></p>
    </div>
</div>