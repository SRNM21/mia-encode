<div 
    class="request-container card <?= $request->is_read ? '' : 'unread' ?> <?= $request->status ?>"
    data-request-edit-id='<?= $request->id ?>' 
    data-request-app-id='<?= $request->app_id ?>' 
    data-request-encoder='<?= $request->encoder ?>' 
    data-request-old-content='<?= $request->old_content ?>' 
    data-request-new-content='<?= $request->new_content ?>' 
    data-request-status='<?= $request->status ?>' 
    data-request-datetime='<?= $request->datetime_request ?>' 
    data-action-datetime='<?= $request->datetime_action ?>' 
    data-request-is-read='<?= $request->is_read ?>'
>
    <div class="request-container-header flex-row">
        <p class="container-title">Edit request</pc>
        <p class="container-past-time" title="<?= formatDate($request->datetime_request, 'M j, Y g:i A') ?>" ><?= timeAgo($request->datetime_request) ?></pc>
    </div>
    <div class="request-container-body">
        <p><?= $request->encoder ?> sent an edit request on this bank application details on <?= formatDate($request->datetime_request, 'M j, Y g:i A') ?></p>
    </div>
</div>