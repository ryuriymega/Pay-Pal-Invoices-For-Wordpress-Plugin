<?php


if(get_current_user_id()!=USER_WITH_ACCESS){
	wp_die("<h1>You don't have permissions for this page!</h1>");
}

/*
define('MERCHANT_EMAIL',file_get_contents('PayPal_Settings/MERCHANT_EMAIL'));
define('PAYPAL_CLIENT_ID', file_get_contents('PayPal_Settings/PAYPAL_CLIENT_ID'));
define('PAYPAL_CLIENT_SECRET',file_get_contents('PayPal_Settings/PAYPAL_CLIENT_SECRET'));
define('DOMAIN_NAME',file_get_contents('PayPal_Settings/DOMAIN_NAME'));
define('END_POINT_PAYPAL',file_get_contents('PayPal_Settings/END_POINT_PAYPAL'));
*/

global $wpdb;
$table=$wpdb->prefix."sendPayPalInvoice_withdrawRequests";
/////get all data into table
//where status like Active
$result=$wpdb->get_results("SELECT * FROM ".$table." WHERE statusOfRequest='Active'");

//print_r($result);exit;
$tableBody="";
foreach($result as $requestUsr){
	
	$paypalUrl="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick".
				"&business=".$requestUsr->WithdrawPayPalEmail.
				"&item_name=Whouter, Inc WaigMe USA".
				"&item_number=1".
				"&amount=".$requestUsr->moneyForWithdraw.
				"&currency_code=USD";
	
	//get user info by id
	$user_info = get_userdata($requestUsr->userID);
	
	$tableBody=$tableBody.
		"<tr>".
		"<th><input type=\"checkbox\" class=\"SelectedActiveRequests\" value=\"".$requestUsr->id."\"></th>".
		"<th>".$requestUsr->id."</th>".
		"<th>".$requestUsr->userID."</th>".
		"<th>".$user_info->user_login."</th>".
		"<th>".$requestUsr->WithdrawPayPalEmail."</th>".
		"<th>".$requestUsr->dateOfRequest."</th>".
		"<th>".$requestUsr->moneyForWithdraw."</th>".
		"<th>".$requestUsr->statusOfRequest."</th>".
		"<th><button onclick='sendConfirm(\"".$requestUsr->id."\");'>Confirm</button></th>".
		//"<th><button onclick='sendReject(\"".$requestUsr->id."\");'>Reject</button></th>".
		"<th><button onclick='window.open(\"".$paypalUrl."\",\"__blank\");' id='buttonPay_".$requestUsr->id."' value='".$paypalUrl."'>Pay</button></th>".
		"</tr>";
}






/////get all data into table
//where status like Confirmed
$result=$wpdb->get_results("SELECT * FROM ".$table." WHERE statusOfRequest!='Active'");

//print_r($result);exit;
$tableBody1="";
foreach($result as $requestUsr){
	
	$paypalUrl="https://www.paypal.com/cgi-bin/webscr?cmd=_xclick".
				"&business=".$requestUsr->WithdrawPayPalEmail.
				"&item_name=Whouter, Inc WaigMe USA".
				"&item_number=1".
				"&amount=".$requestUsr->moneyForWithdraw.
				"&currency_code=USD";
	
	//get user info by id
	$user_info = get_userdata($requestUsr->userID);
	
	$tableBody1=$tableBody1.
		"<tr>".
		"<th>".$requestUsr->id."</th>".
		"<th>".$requestUsr->userID."</th>".
		"<th>".$user_info->user_login."</th>".
		"<th>".$requestUsr->WithdrawPayPalEmail."</th>".
		"<th>".$requestUsr->dateOfRequest."</th>".
		"<th>".$requestUsr->moneyForWithdraw."</th>".
		"<th>".$requestUsr->statusOfRequest."</th>".
		"</tr>";
}
?>

<script type="text/javascript" src="https://code.jquery.com/jquery-3.3.1.js"></script>
<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.css"/>
<script type="text/javascript" src="https://cdn.datatables.net/v/dt/dt-1.10.18/datatables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/1.10.19/js/jquery.dataTables.min.js"></script>
<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.3/js/dataTables.responsive.min.js"></script>

<!--
<div class="wrap">
 <h1>PayPal Invoices Create and Send</h1>
 <p>Setup PayPal Credentials</p>
 <br>
 END_POINT_PAYPAL<input type="text" value=<?php //echo END_POINT_PAYPAL;?>>
 <br>
 MERCHANT_EMAIL<input type="text" value=<?php //echo MERCHANT_EMAIL;?>>
 <br>
 PAYPAL_CLIENT_ID<input type="text" value=<?php //echo PAYPAL_CLIENT_ID;?>>
 <br>
 PAYPAL_CLIENT_SECRET<input type="text" value=<?php //echo PAYPAL_CLIENT_SECRET;?>>
 <br>
 DOMAIN_NAME<input type="text" value=<?php //echo DOMAIN_NAME;?>>
 <br>
   <button onclick="window.alert('not ready yet');">
	   Save PayPal Settings
	</button>
</div>
-->


<div style="margin-top:50px;">
	<h1>Table with NEW(Active) Requests:</h1>
</div>

<div>
	<button onClick="paySelected();">Pay All Selected</button>
	<button style="margin-left:50px;" onClick="confirmSelected();">Confirm All Selected</button>
</div>

<table id="example" class="display responsive nowrap" cellspacing="0" width="80%">
    <thead>
	<tr>
		<th>selected</th>
		<th>id</th>
		<th>user_id</th>
		<th>user_name</th>
		<th>email</th>
		<th>date</th>
		<th>price</th>
		<th>status</th>
		<th>Confirm</th>
		<th>Pay</th>
	</tr>
	</thead>
	<tbody>
		<?php echo $tableBody; ?>
	</tbody>
</table>



<div style="margin-top:50px;">
	<h1>Table with Confirmed(Archived) Requests:</h1>
</div>

<table id="confirmedTable" class="display responsive nowrap" cellspacing="0" width="80%">
    <thead>
	<tr>
		<th>id</th>
		<th>user_id</th>
		<th>user_name</th>
		<th>email</th>
		<th>date</th>
		<th>price</th>
		<th>status</th>
	</tr>
	</thead>
	<tbody>
		<?php echo $tableBody1; ?>
	</tbody>
</table>

<script>
	
    var table;
    $(document).ready(function() {
        
        table = $('#example').DataTable( {
			"order": [[ 0, "desc" ]],
            responsive: true,
            "bProcessing": true,
            "oLanguage": {            
                        "sSearch": "",
                        "sLoadingRecords": "Please wait - loading..."
            }
        } );
    } );
    
    
    var table1;
    $(document).ready(function() {
        
        table = $('#confirmedTable').DataTable( {
			"order": [[ 0, "desc" ]],
            responsive: true,
            "bProcessing": true,
            "oLanguage": {            
                        "sSearch": "",
                        "sLoadingRecords": "Please wait - loading..."
            }
        } );
    } );
    
    function loadRequests(){
       table.ajax.reload(fnLoadRequestsTable);
    }
    
    function fnLoadRequestsTable(){
        table.responsive.recalc();
    }
    
    /*
    ////send ajax for reject withdraw request
    function sendReject(id){
		var data = {
			action: "cancel_withdraw_request",
			id: id
		};
		$.post(ajaxurl,data,function (response){
			alert(response);
		});
	}
	*/
	
	////send ajax for confirm withdraw request
	function sendConfirm(id){
		var data = {
			action: "confirm_withdraw_request",
			id: id
		};
		$.post(ajaxurl,data,function (response){
			alert(response);
		});
		window.open("admin.php?page=sendPayPalInvoice%2Fincludes%2Fadminpanel-page.php","_self")
	}
	
	
	function paySelected(){
		x=document.getElementsByClassName("SelectedActiveRequests");		
		countOfSelected=0;
		for(i=0;i<x.length;i++){
			if(x[i].checked){
				countOfSelected++;
				console.log("Open pay page for selected : "+x[i].value);
				open(document.getElementById("buttonPay_"+x[i].value).value,"__new"+x[i].value);
			}
		}
		if(countOfSelected==0){
			window.alert("there are no selected");
		}
	}
	
	function confirmSelected(){
		x=document.getElementsByClassName("SelectedActiveRequests");
		countOfSelected=0;
		for(i=0;i<x.length;i++){
			if(x[i].checked){
				countOfSelected++;
				console.log("Confirmed for selected : "+x[i].value);
				sendConfirm(x[i].value);
			}
		}
		if(countOfSelected==0){
			window.alert("there are no selected");
		}
	}
</script>
