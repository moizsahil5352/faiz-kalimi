<?php
if (!is_user_a(SUPER_ADMIN)) {
    do_redirect_with_message('/home', 'Redirected as tried to access unauthorized area.');
}

$itsid = getAppData('arg1');
$action = is_null($itsid) ? 'list' : ($itsid > 0 ? 'edit' : 'new');
setAppData('action', $action);

$user_data = (object) [];
if ($itsid > 0) {
    $result = get_slot_details($itsid);
    if (is_null($result)) {
        do_redirect_with_message('/vjb.slot', "Slot ID ($itsid) not found.");
    }
    $user_data = $result;
}

setAppData('user_data', $user_data);

do_for_post('_handle_add_user');

function content_display()
{
    $itsid = getAppData('arg1');
    $action = getAppData('action');
    switch ($action) {
        case 'list':
            show_user_list();
            break;
        case 'edit':
        case 'new':
            show_input_user();
            break;
    }
}

function show_input_user()
{
    $itsid = getAppData('arg1');
    $action = getAppData('action');

    $user_data = getAppData('user_data');
    ?>
    <div class="card">
        <div class="card-body">
            <form action="" method="post">
                <input type='hidden' name='req_itsid' value="<?= $itsid ?>" />
                <div class="form-group row">
                    <label for="itsid" class="col-sm-2 col-form-label">ID</label>
                    <div class="col-sm-10">
                    <input type='hidden' name='id' value="<?= $user_data->id ?? -1 ?>" />
                    <?= $user_data->id ?? -1 ?>
                    </div>
                </div>
                <?php 
                get_text_field('title', 'Title', $user_data->title ?? '', 'text',true);
                get_text_field('date', 'Date', $user_data->date ?? '', 'date',true); 
                get_text_field('capacity', 'Capacity', $user_data->capacity ?? '', 'text',true);
                ?>                                                
                <div class="form-group row">
                    <div class="col-sm-10">
                        <button type="submit" class="btn btn-gradient-success btn-rounded btn-fw">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <?php
}

function show_user_list()
{
    $user_data = get_slot_records();
    $url = getAppData('BASE_URI');
    ?>
    <div class="card">
        <div class="card-header row">
            <div class="col-6">User Data</div>
            <div class="col-6"><a href="<?= $url ?>/vjb.slot/0" class="btn btn-gradient-success btn-rounded btn-fw">Add New</a>
            </div>
        </div>

        <div class="card-body">
            <?= __display_user_list([$user_data]) ?>
        </div>
    </div>
    <?php
}

function __display_user_list($data)
{
    $records = $data[0];
    util_show_data_table($records, [
        '__show_row_sequence' => 'S/no',
        // 'id' => 'ID',
        'title' => 'Title',
        'capacity' => 'Capacity',
        'registered' => 'Registered',
        'date' => 'Date',
        '__show_link2markaz_list' => 'Action'
    ]);
}

function __show_link2markaz_list($row, $index)
{
    $id = $row->id;
    $uri = getAppData('BASE_URI');
    
    return "<a href='$uri/vjb.slot/$id' class='btn btn-gradient-success btn-rounded btn-fw'>Edit</a>
    <a href='$uri/report/vjb_registration/$id' class='btn btn-gradient-warning btn-rounded btn-fw'>Report</a>
    ";
}


function _handle_add_user()
{
    $req_itsid = $_POST['req_itsid'];

    $id = $_POST['id'];
    $title = $_POST['title'];
    $date = $_POST['date'];
    $capacity = $_POST['capacity'];

    $msg = 'User added successfully!';
    $result = add_vajebaat_slot($id, $title, $date, $capacity);
    if (!is_null($result)) {
        $msg = 'Failed with error: ' . $result;
    }
    do_redirect_with_message('/vjb.slot', $msg);
}