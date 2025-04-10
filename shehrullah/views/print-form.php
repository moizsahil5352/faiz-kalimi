<?php

$en_sabeel = getAppData('arg1');
$sabeel = do_decrypt($en_sabeel);

$sabeel_data = get_thaalilist_data($sabeel);
if (is_null($sabeel_data)) {
    //do_redirect_with_message('\home', 'Sabeel not found. Please enter correct Sabeel.');
    $hof_id = $sabeel;
    $hof_data = get_hof_data($hof_id);
    if( is_null($hof_data) ) {
        do_redirect_with_message('\home', "HOF ID ($hof_id) not found");
    } else {
        $sabeel_data = $hof_data;
    }

} else {
    $hof_id = $sabeel_data->ITS_No;       
}


setAppData('sabeel_data', $sabeel_data);
setAppData('hof_id', $hof_id);

// $sabeel_data = get_thaalilist_data($sabeel);
// if (is_null($sabeel_data)) {
//     do_redirect_with_message('/', 'Sabeel or HOF ID not found.');
// }
//setAppData('sabeel_data', $sabeel_data);


// $hof_id = $sabeel_data->ITS_No;
$hijri_year = get_current_hijri_year();

$last_year_takhmeen = get_last_year_takhmeen($hof_id);
setAppData('last_year_takhmeen' , $last_year_takhmeen);

// $attendees_data = get_attendees_data_for_nonsab($hof_id, $hijri_year, false);
$takhmeen_data = get_shehrullah_takhmeen_for($hof_id, $hijri_year);
if (is_null($takhmeen_data)) {
    do_redirect_with_message('/', 'Oops! Seems form is not filled.');
}
setAppData('takhmeen_data', $takhmeen_data);

function content_display()
{
    $hijri_year = get_current_hijri_year();
    $takhmeen_data = getAppData('takhmeen_data');
    $thalidata = getAppData('sabeel_data');
    $last_year_takhmeen = getAppData('last_year_takhmeen');
    $hof_id = getAppData('hof_id');

    $name = $thalidata->NAME ?? $thalidata->full_name;
    $sabeel = $thalidata->Thali ?? 'NONE';
    $email = $thalidata->Email_ID ?? '';
    $address = $thalidata->Full_Address ?? '';

    $get_only_attends = true;
    $attendees_records = get_attendees_data_for_nonsab($hof_id, $hijri_year, true);

    //$attendees_records = get_attendees_data_for($thalidata->ITS_No, $hijri_year, $get_only_attends);
    $shehrullah_data = get_shehrullah_data_for($hijri_year);

    $date = date("d/m/Y");

    $uri = getAppData('APP_BASE_URI');

    $print = getAppData('print') ?? false;
    ?>
    <style>
        .smalltext {
            font-size: 11px;
        }
    </style>
    <?php if(!$print) { ?>
        <h2>Shukran, Your data is submitted, collect printout visiting jamaat office.</h2>
    <?php } ?>
    <div class="card" id="printableArea">
        <div class="card-body">
            <table class='table table-bordered'>
                <tr>
                    <td><img src="<?= $uri ?>/_assets/images/anjuman_e_kalimi.png" /></td>
                    <td>
                        <table class='table table-bordered'>
                            <tr>
                                <th style='font-size: 12px'>SHEHRULLAH <?= $hijri_year ?>H / Kalimi Masjid, KALIMI MOHALLAH
                                </th>
                                <td><?=$date?></td>
                            </tr>                            
                        </table>
                    </td>

                </tr>
            </table>

            <table class='table table-bordered'>
                <tr>
                    <th style='font-size: 12px'>HOF</th>
                    <td style='font-size: 12px' colspan="5">[<?= $hof_id ?>] <?= $name ?></td>                                        
                </tr>
                <tr>
                    <th style='font-size: 12px'>Sabil</th>
                    <td style='font-size: 12px'><?= $sabeel ?></td>
                    <th style='font-size: 12px'>WApp</th>
                    <td style='font-size: 12px'><?= $takhmeen_data->whatsapp ?></td>
                    <th style='font-size: 12px'>Prev. Takhmeen</th>
                    <td style='font-size: 12px'><?=$last_year_takhmeen?></td>
                </tr>
                <tr>
                    <th style='font-size: 12px'>Addr:</th>
                    <td style='font-size: 12px' colspan="5"><?= $address ?></td>
                </tr>
            </table>

            <table class='table table-bordered'>
                <tr>
                    <th style='font-size: 12px'>SN</th>
                    <th style='font-size: 12px'>ITS - NAME</th>
                    <th style='font-size: 12px'>Gender/Age</th>
                    <th style='font-size: 12px'>Chair</th>
                    <th style='font-size: 12px'>Mohallah</th>
                </tr>
                <?php
                $index = 0;
                foreach ($attendees_records as $attendees) {
                    $its = $attendees->its_id;
                    $name = $attendees->full_name;
                    $index++;
                    //$index = ((int) $index) + 1;
                    // return "$index. [$its] $name";
            
                    $age = $attendees->age;
                    $gender = $attendees->gender;
                    $gender = substr($gender, 0, 1);

                    $chair_preference = $attendees->chair_preference;
                    $mohalla = substr($attendees->mohallah ?? "Other", 0, 1);
                    // return "$gender/$age";

                    $photo_pending = '';
                    if( $attendees->photo_pending == 'Y' ) {
                        $photo_pending = 'PP -';
                    }
                
                    
                    echo "<tr>
                        <td style='font-size: 12px'>$index</td>
                        <td style='font-size: 12px'>$photo_pending $its - $name</td>
                        <td style='font-size: 12px'>$gender/$age</td>
                        <td style='font-size: 12px'>$chair_preference</td>
                        <td style='font-size: 12px'><b>$mohalla</b></td>                        
                        </tr>";
                }
                ?>
            </table>
            
            <?php __display_niyaz_section($takhmeen_data, $shehrullah_data); ?>

            <table class='table table-bordered small-text'>
                <tr>
                    <th style='font-size: 12px' colspan=3>Kindly submit form to receive izan card & carry izan card for our
                        convenience.</th>
                </tr>
                <tr>
                    <th style='font-size: 12px'>Committed Hub Amount</th>
                    <th style='font-size: 12px'>Receipt No.</th>
                    <th style='font-size: 12px'>Date</th>
                </tr>
            </table>
            <table class='table table-bordered'>
                <tr>
                    <th style='font-size: 12px'>HOF Signature</th>
                    <th style='font-size: 12px'>Auth. Signature</th>
                </tr>
            </table>
        </div>
    </div>
    <?php if($print) { ?>
    <div class="card">
        <div class='card-footer row' id='print_button_section'>
            <div class='col-12'>
                <button class='btn btn-primary' id='Print'>Print</button>
            </div>            
        </div>
    </div>
    <script>
        function the_script() {
            $('#Print').click(function () {
                var printContents = document.getElementById('printableArea').innerHTML;
                var originalContents = document.body.innerHTML;

                var htmlToPrint = '' +
        '<style type="text/css">' +
        'table th, table tr, table td {' +
        'border:1px solid #000;' +
        'padding:0.5em;' +
        '}' +
        '</style>';
    htmlToPrint += printContents;


                document.body.innerHTML = htmlToPrint;
                window.print();
                document.body.innerHTML = originalContents;
            });
        }
    </script>    
    <?php
    } else {
        ?>
        <style type="text/css" media="print">
            * { display: none; }
        </style>
        <script>
            document.addEventListener('keydown', function(event) {
            if (event.ctrlKey && event.key === 'p') {
            event.preventDefault(); 
            }
            });
        </script>
        <?php
    }
}

function __display_niyaz_section(...$data)
{

    $takhmeen_data = (object) $data[0];
    $markaz_data = $data[1];

    $family_hub = $takhmeen_data->family_hub;
    $niyaz_type = $takhmeen_data->niyaz_type;
    $niyaz_count = $takhmeen_data->niyaz_count;
    $niyaz_hub = get_niyaz_amount_for($niyaz_type, $family_hub, $markaz_data);
    $total_niyaz = $niyaz_hub * $niyaz_count;

    $full_niyaz_count = $niyaz_type == 'full' ? 1 : 0;
    $half_niyaz_count = $niyaz_type == 'half' ? 1 : 0;
    $family_niyaz_count = $niyaz_type == 'family' ? 1 : 0;

    $full_niyaz_total = $markaz_data->full_niyaz * $full_niyaz_count;
    $half_niyaz_total = $markaz_data->half_niyaz * $half_niyaz_count;
    $family_niyaz_total = $family_hub * $family_niyaz_count;


    $sehori_count = $takhmeen_data->sehori_count;
    $sehori_hub = $markaz_data->sehori;//SHEHRULLAH_CONFIG->SEHORI;
    $sehori_total = $sehori_count * $sehori_hub;

    $net_total = $sehori_total + $total_niyaz;

    $zabihat_count = $takhmeen_data->zabihat_count;
    $zabihat_hub = $markaz_data->zabihat;//SHEHRULLAH_CONFIG->ZABIHAT;
    $zabihat_total = $zabihat_count * $zabihat_hub;

    $iftar_count = $takhmeen_data->iftar_count;
    $iftar_hub = $markaz_data->iftar;//SHEHRULLAH_CONFIG->IFTAR;
    $iftar_total = $iftar_count * $iftar_hub;

    $iftar_fadilraat_hub = 0;//SHEHRULLAH_CONFIG->IFTAR_FADILRAAT;
    $iftar_fadilraat_count = $takhmeen_data->iftar_fadilraat;
    $iftar_fadilraat_total = $iftar_fadilraat_count * $iftar_fadilraat_hub;

    $khajoor_count = $takhmeen_data->khajoor_count;
    $khajoor_hub = $markaz_data->khajoor;//SHEHRULLAH_CONFIG->KHAJOOR;
    $khajoor_total = $khajoor_count * $khajoor_hub;


    $fateha_count = 0;//$takhmeen_data->fateha_count;
    $fateha_hub = $markaz_data->fateha;//SHEHRULLAH_CONFIG->CHAIR;
    $fateha_total = $fateha_count * $fateha_hub;

    $pirsa_count = $takhmeen_data->pirsa_count;
    $pirsa_hub = $markaz_data->pirsu;//SHEHRULLAH_CONFIG->PIRSA;
    //$pirsa_total = $pirsa_count * $pirsa_hub;
    $pirsa_selection = $pirsa_count > 0 ? '1' : '0';
    
    $chair_count = $takhmeen_data->chair_count;
    $chair_hub = $markaz_data->chair;//SHEHRULLAH_CONFIG->CHAIR;
    $chair_total = $chair_count * $chair_hub;


        echo "
    <table class='table table-bordered'>
        <tr>                            
            <th style='font-size: 12px'>Niyaz Khdimat</th><th style='font-size: 12px'>Hub</th><th style='font-size: 12px'>Count</th>
            <th style='font-size: 12px'>Other Khidmat</th><th style='font-size: 12px'>Hub</th><th style='font-size: 12px'>Count</th>
        </tr>
        <tr>            
            <th style='font-size: 12px'>Full Niyaz</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$markaz_data->full_niyaz</td><td>&nbsp;</td>
            <th style='font-size: 12px'>Iftar</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$iftar_hub</td><td>&nbsp;</td>
        </tr>
        <tr>            
            <th style='font-size: 12px'>Half Niyaz</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$markaz_data->half_niyaz</td><td>&nbsp;</td>
            <th style='font-size: 12px'>Zabihat</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$zabihat_hub</td><td>&nbsp;</td>
        </tr>
        <tr>            
            <th style='font-size: 12px'>Family Niyaz</th><td style='font-size: 12px'>&nbsp;</td><td>&nbsp;</td>
            <th style='font-size: 12px'>Fateha</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$fateha_hub</td><td>&nbsp;</td>
        </tr>
        <tr>            
            <th style='font-size: 12px' colspan=3>&nbsp</th>
            <th style='font-size: 12px'>Khajoor</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$khajoor_hub</td><td>&nbsp;</td>
        </tr>        
        <tr>               
            <th style='font-size: 12px'>Pirsa</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$pirsa_hub</td><td style='font-size: 12px'>$pirsa_selection</td>
            <th style='font-size: 12px'>Chair</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$chair_hub</td><td style='font-size: 12px'>$chair_count</td>
        </tr>  
        </table>      
    ";
    
    // echo "
    // <table class='table table-bordered'>
    //     <tr>                            
    //         <th style='font-size: 12px' colspan=9>Niyaz/Other Khdimat</th>
    //     </tr>
    //     <tr>            
    //         <th style='font-size: 12px'>Full Niyaz</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $markaz_data->full_niyaz</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    //         <th style='font-size: 12px'>Half Niyaz</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $markaz_data->half_niyaz</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    //         <th style='font-size: 12px'>Family Niyaz</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $family_hub</td><td>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</td>
    //     </tr>
    //     <tr>            
    //         <th style='font-size: 12px'>Zabihat</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $zabihat_hub</td><td>&nbsp;</td>
    //         <th style='font-size: 12px'>Iftar</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$iftar_hub</td><td>&nbsp;</td>
    //         <th style='font-size: 12px'>Fateha</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$fateha_hub</td><td>&nbsp;</td>
    //     </tr>
    //     <tr>            
    //         <th style='font-size: 12px'>Khajoor</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$khajoor_hub</td><td style='font-size: 12px'>&nbsp;</td>
    //         <th style='font-size: 12px'>Pirsa</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i>$pirsa_hub</td><td style='font-size: 12px'>$pirsa_selection</td>
    //         <th style='font-size: 12px'>Chair</th><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $chair_hub x $chair_count</td><td style='font-size: 12px'><i class='mdi mdi-currency-inr'></i> $chair_total</td>
    //     </tr>        
    // </table>
    // ";
}

function __show_its_and_name($row, $index)
{
    $its = $row->its_id;
    $name = $row->full_name;
    $index = ((int) $index) + 1;
    return "$index. [$its] $name";
}

function __show_gender_and_age($row, $index)
{
    $age = $row->age;
    $gender = $row->gender;
    $gender = substr($gender, 0, 1);

    return "$gender/$age";
}