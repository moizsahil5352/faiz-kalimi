<?php

do_for_post('handle_sabeel_search');

function content_display()
{
    ?>
    <div class="card">
        <div class="card-body">
            <h4 class="card-title">Sabeel Search</h4>
            <p class="card-description"> Enter sabeel number and enter </p>
            <form method="post" action="" class="forms-sample">
                <div class="form-group">
                    <label class="col-sm-3 col-form-label">Sabeel ID / HOF ID (Numbers only)</label>
                    <div class="input-group col-xs-12">
                        <input type="text" class="form-control" placeholder="Recipient's username" pattern="^[0-9]{1,8}$"
                            id="sabeel" name="sabeel" aria-label="Recipient's username" aria-describedby="button-addon2">
                        <button class="btn btn-outline-primary" type="submit" id="button-addon2">Search</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function handle_sabeel_search()
{
    $sabeel = $_POST['sabeel'];
    $sabeel_data = get_thaalilist_data($sabeel);
    if (is_null($sabeel_data)) {
        setSessionData(TRANSIT_DATA, 'No records found for input ' . $sabeel . '. Enter correct sabeel number or HOF ITS.');
    }

    $enc_sabeel = do_encrypt($sabeel);
    do_redirect('\input-attendees\\' . $enc_sabeel);
}