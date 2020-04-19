<?php
error_reporting(0);
include('_authCheck.php');
include('_common.php');

$query="SELECT * FROM thalilist LEFT JOIN transporters on thalilist.Transporter = transporters.Name where Email_id = '".$_SESSION['email']."'";

$values = mysqli_fetch_assoc(mysqli_query($link,$query));

$musaid_details = mysqli_fetch_assoc(mysqli_query($link,"SELECT NAME, CONTACT FROM thalilist where Email_id = '".$values['musaid']."'")); 

$_SESSION['thaliid'] = $values['id'];
$_SESSION['thali'] = $values['Thali'];

// Check if users gmail id is registered with us and has got a thali number against it 
if(empty($values['Thali']))
{
  $some_email = $_SESSION['email'];
  session_unset();  
  session_destroy();

  $status = "Sorry! Either $some_email is not registered with us OR your thali is not active. Send and email to help@faizstudents.com";
  header("Location: login.php?status=$status");
  exit;
}

// Check if takhmeen is done for the year
if(empty($values['yearly_hub']))
{
  header("Location: selectyearlyhub.php");
  exit;
}

// Redirect users to update details page if any details are missing
if (empty($values['ITS_No']) || empty($values['fathersNo']) || empty($values['fathersITS']) || empty($values['CONTACT']) || empty($values['WhatsApp']) || empty($values['Full_Address'])) { 
  header("Location: update_details.php?update_pending_info"); 
  exit;
}

// Check if there is any enabled event that needs users response
$enabled_events_query=mysqli_query($link,"SELECT * FROM events where enabled='1' order by id limit 1");
$enabled_events_values = mysqli_fetch_assoc($enabled_events_query);

if (!empty($enabled_events_values) && !isResponseReceived($enabled_events_values['id']))
{
  header("Location: events.php"); 
  exit;
}

// show the index page with hub miqaat breakdown
if(!empty($values['yearly_hub']))
{
  $reciepts_query_result_total = mysqli_fetch_assoc(mysqli_query($link,"SELECT sum(`Amount`) as total FROM `receipts` where Thali_No = '".$_SESSION['thali']."'"));
  $total_amount_paid = $reciepts_query_result_total['total'];
  $thaliactivedate_query = mysqli_fetch_assoc(mysqli_query($link,"SELECT DATE(datetime) as datetime FROM `change_table` where userid = '".$_SESSION['thaliid']."' AND operation = 'Start Thali' ORDER BY id limit 1"));
  $thaliactivedate = $thaliactivedate_query['datetime'];

  $sql = mysqli_query($link,"select miqat_date,miqat_description from sms_date");

  while($record = mysqli_fetch_assoc($sql))
    {
      $_miqaats[$record['miqat_date']] = $record['miqat_description'];
    } 

  $installment = (int)($values['Total_Pending'] + $values['Paid'])/count($_miqaats);
  $todays_date = date("Y-m-d");
  $miqaat_gone = 0;
  
  $miqaats = array();
  $miqaats_past = array();
  foreach ($_miqaats as $mdate => $miqaat) {
    
    if($mdate < $todays_date)
    {
      $miqaats_past[$mdate] = $miqaat;
    }
    else
    {

      $month_installment = $installment;
      $miqaats[] = array(
                        $mdate,$miqaat,ceil($month_installment)
                        );
    }
  }

 
 $hub_baki = ((count($miqaats_past) - $miqaat_gone) * $installment) - $total_amount_paid;

 $miqaats[0][2] += $hub_baki;

 if ($miqaats[0][2] > 0) {
 $miqaats[0][2] = round($miqaats[0][2],-2);
 }
 $next_install = $miqaats[0][2];
 mysqli_query($link,"UPDATE thalilist set next_install ='$next_install' WHERE Email_id = '".$_SESSION['email']."'") or die(mysqli_error($link));
 mysqli_query($link,"UPDATE thalilist set prev_install_pending ='$hub_baki' WHERE Email_id = '".$_SESSION['email']."'") or die(mysqli_error($link));
}
?>
<!DOCTYPE html>

<!-- saved from url=(0029)http://bootswatch.com/flatly/ -->

<html lang="en">
    <head>
  <?php include('_head.php'); ?>
  </head>

  <body>

    <?php include('_nav.php'); ?>

    <div class="container">
        <br />
        <br />

        <div class="row">
          <div class="col-xs-6 col-sm-3 col-md-2">

              <?php
                if($values['yearly_hub'] != 0) {
                  if($values['Active'] == 0)
                  {
                    if($values['hardstop'] == 1)
                    {
                      ?>
                <input type="button" onclick="alert('<?php echo "You are not allowed to start your thali: ".$values['hardstop_comment'] ?>')" name="start_thali" value="Start Thaali"  class="btn btn-success"/>
               <?php

                    }
                    else
                    {
              ?>

              <form method="POST" action="start_thali.php" onsubmit='return confirm("Are you sure?");' data-key="LazyLoad" class="hidden">
                <input type="submit" name="start_thali" value="Start Thaali"  class="btn btn-success"/>
              </form>

              <?php
                    }
                    }
                    else
                    {
              ?>
       
              <form method="POST" action="stop_thali.php" onsubmit='return confirm("Are you sure?");' data-key="LazyLoad" class="hidden">
                <input type="submit" name="stop_thali" value="Stop Thaali"  class="btn btn-danger"/>
              </form>

              <?php } ?>

          </div>

          <div class="col-xs-6 col-sm-3 col-md-2">

              <?php
                  if($values['Transporter'] == 'Pick Up' && $values['Active'] == 1)
                  {
              ?>

              <form method="POST" action="start_transport.php" onsubmit='return confirm("Are you sure?");' data-key="LazyLoad" class="hidden">
                <input type="submit" name="start_transport" value="Request Transport"  class="btn btn-success"/>
              </form>

              <?php
                }
                else if($values['Active'] == 1)
                {
              ?>

              <form method="POST" action="stop_transport.php" onsubmit='return confirm("Are you sure?");' data-key="LazyLoad" class="hidden">
                <input type="submit" name="stop_transport" value="Request Pickup"  class="btn btn-danger"/>
              </form>

              <?php
                }} else {
              ?>
               <form>
                <h5>You have not done hub takhmeen and so will not see START THALI button</h5>
              </form>
              <?php }
              ?>
          </div>
        </div>

        <br />

        <div class="row">
            <div class="panel-group" id="accordion" role="tablist" aria-multiselectable="true">
              <div class="panel panel-default">
                <div class="panel-heading" role="tab" id="headingOne">
                  <h4 class="panel-title">
                    <a role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                      Thaali Details  <span class="text-muted" style="font-size: 12px; float: right;">(Click to Expand/Collapse)</span>
                    </a>
                  </h4>
                </div>
                <div id="collapseOne" class="panel-collapse collapse" role="tabpanel" aria-labelledby="headingOne">
                  <div class="panel-body">
                    <ul class="list-group col">
                      <li class="list-group-item">
                          <h6 class="list-group-item-head ing text-muted">Thaali Number</h6>
                          <p class="list-group-item-text"><strong><?php echo $values['Thali']; ?></strong></p>
                      </li>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Name</h6>
                          <p class="list-group-item-text"><strong><?php echo $values['NAME']; ?></strong></p>
                      </li>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Mobile Number</h6>
                          <p class="list-group-item-text"><strong><?php echo $values['CONTACT']; ?></strong></p>
                      </li>
                      <?php if($musaid_details) { ?>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Musaid</h6>
                          <p class="list-group-item-text"><strong><?php echo $musaid_details['NAME']; ?> | <a href="tel:<?php echo $musaid_details['CONTACT']; ?>"><?php echo $musaid_details['CONTACT']; ?></a></strong></p>
                      </li>
                      <?php } ?>
                      <li class="list-group-item">
                          <h5 class="list-group-item-heading text-muted">Pending Hoob</h5>
                          <p class="list-group-item-text"><?php echo $values['Total_Pending'] + $values['Paid']; ?> - <a href="hoobHistory.php"><strong><?php echo $values['Paid']; ?></strong></a> = <?php echo $values['Total_Pending']; ?></p>
                      </li>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Is Active?</h6>
                          <p class="list-group-item-text"><strong><?php echo ($values['Active'] == '1') ? 'Yes' : 'No'; ?></strong></p>
                      </li>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Transporter</h6>
                          <p class="list-group-item-text">
                          <?php 
                              echo "".$values['Transporter']." | ".$values['Mobile']."" ;
                          ?>
                          </p>
                      </li>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Address</h6>
                          <p class="list-group-item-text"><?php echo $values['Full_Address']; ?></p>
                      </li>

                      <?php
                        if($values['Active'] == 1)
                          {
                      ?>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Start Date</h6>
                          <p class="list-group-item-text hijridate"><?php echo $values['Thali_Start_Date']; ?></p>
                      </li>

                      <?php
                        }
                        else
                        {
                        ?>
                      <li class="list-group-item">
                          <h6 class="list-group-item-heading text-muted">Stop Date</h6>
                          <p class="list-group-item-text hijridate"><?php echo $values['Thali_Stop_Date']; ?></p>
                      </li>

                      <?php } ?>
                    </ul>  
                  </div>
                </div>
              </div>
              
              <!-- Break down -->
              <?php
                if(isset($installment)):
              ?>

                <div class="panel panel-default" style="margin-top: 20px;">
                  <div class="panel-heading" role="tab" id="headingTwo">
                    <h4 class="panel-title">
                      <a class="collapsed" role="button" data-toggle="collapse" data-parent="#accordion" href="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                        Hoob Breakdown  <span class="text-muted" style="font-size: 12px; float: right;">(Click to Expand/Collapse)</span>
                      </a>
                    </h4>
                  </div>
                  <div id="collapseTwo" class="panel-collapse collapse in" role="tabpanel" aria-labelledby="headingTwo">
                    <div class="panel-body">
                      <h5 class="col-xs-12">The niyaaz amount will be payable throughout the year on the following 3 miqaats. If possible do contribute the whole amount in Lailat ul Qadr</h5>
                      <table class='table table-striped'>
                        <thead>
                          <tr>
                            <th>Date</th>
                            <th>Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <?php foreach ($miqaats_past as $miqaat) {
                          ?>
                          <tr>
                            <td><?php echo $miqaat; ?></td>
                            <td>--</td>
                          </tr>
                         <?php } ?>

                          <?php
                          $i = true;
                          foreach ($miqaats as $miqaat) {
                          ?>
                          <tr>
                            <td><?php echo $miqaat[1];?></td>

                            <?php if ($miqaat[2] < 0) { ?>
                            <td>0</td>
                            <?php }else{ ?>
                            <td><?php echo $miqaat[2]; ?></td>  
                            <?php } ?>
                          </tr>
                         <?php
                            $i = false;
                                }
                       ?>
                        </tbody>
                      </table>
                    </div>
                  </div>
                </div>

              <?php endif;?>
              <!-- Break down -->
            </div>
        </div>
    </div>

    <?php
      if(isset($_GET['status']))
      {
    ?>
        <script type="text/javascript">
            <?php
                if($_GET['status'] == 'Start Thali Successful') {
                    $message = $_GET['status'].'. '.'Your pending hoob : "'.$values['Total_Pending'].'"';
                } else {
                    $message = $_GET['status'];
                }
            ?>

            alert('<?php echo $message; ?>');
        </script>
    <?php } ?>

    <?php include('_bottomJS.php'); ?>

    <div align="center">
    <a href="mailto:help@faizstudents.com">help@faizstudents.com</a><br><br>
    </div>
  </body>
</html>
