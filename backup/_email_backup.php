<?php 

require '../users/mailgun-php/vendor/autoload.php';
use Mailgun\Mailgun;
require '_credentials.php';
    //ENTER THE RELEVANT INFO BELOW
    $mysqlUserName      = $username;
    $mysqlPassword      = $password;
    $mysqlHostName      = $servername;
    $DbName             = $dbname;
    $backup_name        = "mybackup.sql";
    $tables             = "Your tables";

   //or add 5th parameter(array) of specific tables:    array("mytable1","mytable2","mytable3") for multiple tables

    

    function Export_Database($host,$user,$pass,$name,  $tables=false, $backup_name=false )
    {
        $mysqli = new mysqli($host,$user,$pass,$name); 
        $mysqli->select_db($name); 
        $mysqli->query("SET NAMES 'utf8'");

        $queryTables    = $mysqli->query('SHOW TABLES'); 
        while($row = $queryTables->fetch_row()) 
        { 
            $target_tables[] = $row[0]; 
        }   
        if($tables !== false) 
        { 
            $target_tables = array_intersect( $target_tables, $tables); 
        }
        foreach($target_tables as $table)
        {
            $result         =   $mysqli->query('SELECT * FROM '.$table);  
            $fields_amount  =   $result->field_count;  
            $rows_num=$mysqli->affected_rows;     
            $res            =   $mysqli->query('SHOW CREATE TABLE '.$table); 
            $TableMLine     =   $res->fetch_row();
            $content        = (!isset($content) ?  '' : $content) . "\n\n".$TableMLine[1].";\n\n";

            for ($i = 0, $st_counter = 0; $i < $fields_amount;   $i++, $st_counter=0) 
            {
                while($row = $result->fetch_row())  
                { //when started (and every after 100 command cycle):
                    if ($st_counter%100 == 0 || $st_counter == 0 )  
                    {
                            $content .= "\nINSERT INTO ".$table." VALUES";
                    }
                    $content .= "\n(";
                    for($j=0; $j<$fields_amount; $j++)  
                    { 
                        $row[$j] = str_replace("\n","\\n", addslashes($row[$j]) ); 
                        if (isset($row[$j]))
                        {
                            $content .= '"'.$row[$j].'"' ; 
                        }
                        else 
                        {   
                            $content .= '""';
                        }     
                        if ($j<($fields_amount-1))
                        {
                                $content.= ',';
                        }      
                    }
                    $content .=")";
                    //every after 100 command cycle [or at last line] ....p.s. but should be inserted 1 cycle eariler
                    if ( (($st_counter+1)%100==0 && $st_counter!=0) || $st_counter+1==$rows_num) 
                    {   
                        $content .= ";";
                    } 
                    else 
                    {
                        $content .= ",";
                    } 
                    $st_counter=$st_counter+1;
                }
            } $content .="\n\n\n";
        }
        //$backup_name = $backup_name ? $backup_name : $name."___(".date('H-i-s')."_".date('d-m-Y').")__rand".rand(1,11111111).".sql";
        $backup_name = $backup_name ? $backup_name : $name.".sql";
//        header('Content-Type: application/octet-stream');   
//        header("Content-Transfer-Encoding: Binary"); 
//        header("Content-disposition: attachment; filename=\"".$backup_name."\"");  
        return $content;
        exit;
    }
    
    $content = Export_Database($mysqlHostName,$mysqlUserName,$mysqlPassword,$DbName,  $tables=false, $backup_name=false );
    $subject = 'database backup '.date('d/m/Y');
    $file_name = "backup.sql";
    $myfile = fopen($file_name, "w") or die("Unable to open file!");
    fwrite($myfile, $content);
    fclose($myfile);
    $remoteName = "backup ".date('d-m-Y').".sql";
    echo "now sending email";
    $mg = new Mailgun("key-e3d5092ee6f3ace895af4f6a6811e53a");
    $domain = "mg.faizstudents.com";
    $mg->sendMessage($domain, array('from'    => 'admin@faizstudents.com', 
                                    'to'      => 'faizstudentsbackup@gmail.com', 
                                    'cc'      => 'help@faizstudents.com',   
                                    'subject' => $subject,
                                    'html'    => "Please find the attachment named $remoteName"
                                    ), array(
                                        'attachment' => array(array('filePath' => $file_name, 'remoteName' => $remoteName))
                                    ));

    echo "##########completed##############";
?>	
