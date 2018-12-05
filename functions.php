<?php
/**
 * Functions.php
 *
 * @package  sendPayPalInvoice
 * @author   WooThemes
 * @since    1.0.0
 */
  
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

define("USER_WITH_ACCESS","1");
    
/**
 * functions.php
 * Add PHP snippets here
 */
 

// Хук событие 'admin_menu', запуск нашей функции
add_action( 'admin_menu', 'sendPayPalInvoice_AdminPanel_Add_My_Admin_Link' );

 
function sendPayPalInvoice_AdminPanel_Add_My_Admin_Link(){
 add_menu_page(
 'PayPal Invoices Setup', // Название страниц (Title)
 'PayPal Invoices Setup', // Текст ссылки в меню
 'manage_options', // Требование к возможности видеть ссылку
 'sendPayPalInvoice/includes/adminpanel-page.php' // 'slug' - файл отобразится по нажатию на ссылку
 );
 
 //check if table exist
 global $wpdb;
 $table = $wpdb->prefix . 'sendPayPalInvoice_withdrawRequests';
 if($wpdb->get_var("SHOW TABLES LIKE '$table'") != $table) {
	 $charset_collate = $wpdb->get_charset_collate();
	 $sql = "CREATE TABLE " . $table . " (".
				  "`id` bigint(20) NOT NULL AUTO_INCREMENT,".
				  "`userID` bigint(20) NOT NULL COMMENT 'connected with table wp_users',".
				  "`moneyForWithdraw` float(12,2) NOT NULL,".
				  "`WithdrawPayPalEmail` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,".
				  "`dateOfRequest` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,".
				  "`statusOfRequest` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,".
				  
				  /*
				  "`ip` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL COMMENT 'ip address from where request',".				  
				  */
	  
				  "UNIQUE KEY id (id)".
				  ") ". $charset_collate.";";
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
 }

}



//test for add withdraw requests via page
add_filter('the_content', 'sendPayPalInvoice_the_content');
////test for add withdraw requests via page
function sendPayPalInvoice_the_content($content) {
	
	$output = $content;
	
	//check if there are text for replace
	//for out plugin
	if(stristr($output,'[button_invoices_1]')!==FALSE){
		if(isset($_GET['withdraw'])){
			if($_GET['withdraw']=='1'){
				if(is_user_logged_in()){
					$current_user = wp_get_current_user();
					//ADD NEW WITHDRAW REQUEST INTO TABLE
					global $wpdb;
					$table = $wpdb->prefix . 'sendPayPalInvoice_withdrawRequests';
					//check if there are active withdraw in a table
					$alreadyActiveWithdraws = 
					$wpdb->get_results("SELECT * FROM ".$table." ".
									   "WHERE userID = ".$current_user->ID.
									   " AND statusOfRequest='Active'"
									   );
					if(count($alreadyActiveWithdraws) > 0) {
						$newContent= '<h1>You already have withdraws, please wait!</h1>';
					}else{
						$wpdb->insert(
							$table, 
							array(
								'userID' => $current_user->ID,
								'moneyForWithdraw' => round(132),
								'WithdrawPayPalEmail' => $current_user->user_email,
								'statusOfRequest' => 'Active'	
							), 
							array( 
								'%d',
								'%f',
								'%s',
								'%s'
							)
						);
						$newContent= '<h1>withdraw request completed '.$_GET['withdraw'].'</h1>';
					}
				}else{
					//NOT login
					$newContent='';
				}
			}
		}else{
			//$newContent= '<p><a href="блог/?withdraw=1">Add one more money withdraw request</a></p>';
			$newContent='<a class="button button-orange" href="http://waigme.com/money-jar-2?withdraw=1&moneyOut=1">Cash Out Money</a>';
		}
		$output = str_replace("[button_invoices_1]",$newContent,$output);
	}else{
		/*
		 * DO NOTHING
		 * text of [button_invoices_1]
		 * NOT FOUND
		 * */
	}	
	
	return $output;
}


// hook add_query_vars function into query_vars
add_filter('query_vars', 'add_query_vars');
///add possible for send our variables via URL $_GET
function add_query_vars($aVars) {
$aVars[] = "withdraw"; // represents the name of the product category as shown in the URL
$aVars[] = "moneyOut";
return $aVars;
}



/* Function for Reject Withdraw request
/////////////hook for ajax cancel withdraw request
add_action( 'wp_ajax_cancel_withdraw_request', 'sendPayPalInvoice_AdminPanel_cancel_withdraw_request' );
function sendPayPalInvoice_AdminPanel_cancel_withdraw_request(){
	if(get_current_user_id()==USER_WITH_ACCESS){
		 $id=$_GET['id'];
		 if(empty($id)){
			$id=esc_attr($_POST['id']); 
		 }
		 if(isset($id)){
			 //update table for cancel request
			 global $wpdb;
			 $table = $wpdb->prefix . 'sendPayPalInvoice_withdrawRequests';
			 $charset_collate = $wpdb->get_charset_collate();
			 
			 //get current value for return to users balance
			 $result = $wpdb->get_results("SELECT * FROM ".$table." WHERE id = '".$id."' AND statusOfRequest='Active'");
			 foreach($result as $usrMoney){
			 	$moneyForWithdraw = $usrMoney->moneyForWithdraw;
			 	$userID = $usrMoney->userID;
			 }
			 //check if there are numeric value
			 if(is_numeric($moneyForWithdraw)){
				///update our table with invoices
				$sql = "UPDATE " . $table . 
						" SET `statusOfRequest`= 'Rejected'".
						" WHERE id = ".$id;
				$wpdb->query($sql);
				//and return money for a user balance
				$sql = "UPDATE wp_player_data SET `money_jar2_amount`= money_jar2_amount+".
						$moneyForWithdraw." WHERE user_id = ".$userID;
				$wpdb->query($sql);
				echo "withdraw request for user ".$userID." was updated";
			}else{
				echo //"SELECT * FROM ".$table." WHERE id = '".$id."' WHERE statusOfRequest='Active' ".
					 "ERROR when withdraw, probably it already rejected.";
			}
		}else{
			echo "user not specified";
		}
	}else{
		echo "only one specified user allowed for these actions";
	}
	wp_die();
}
*/

/////////////hook for ajax confirm withdraw request
add_action( 'wp_ajax_confirm_withdraw_request', 'sendPayPalInvoice_AdminPanel_confirm_withdraw_request' );
function sendPayPalInvoice_AdminPanel_confirm_withdraw_request(){	
	if(get_current_user_id()==USER_WITH_ACCESS){
		 $id=$_GET['id'];
		 if(empty($id)){
			$id=esc_attr($_POST['id']); 
		 }
		 if(isset($id)){
			 //update table for cancel request
			 global $wpdb;
			 $table = $wpdb->prefix . 'sendPayPalInvoice_withdrawRequests';
			 $charset_collate = $wpdb->get_charset_collate();
			 
			 //get current value for return to users balance
			 $result = $wpdb->get_results("SELECT * FROM ".$table." WHERE id = '".$id."' AND statusOfRequest='Active'");
			 foreach($result as $usrMoney){
			 	$moneyForWithdraw = $usrMoney->moneyForWithdraw;
			 	$userID = $usrMoney->userID;
			 }
			 //check if there are numeric value
			 if(is_numeric($moneyForWithdraw)){
				///update our table with invoices
				$sql = "UPDATE " . $table . 
						" SET `statusOfRequest`= 'Confirmed'".
						" WHERE id = ".$id;
				$wpdb->query($sql);
				echo "withdraw request for user ".$userID." was updated";
			}else{
				echo //"SELECT * FROM ".$table." WHERE id = '".$id."' WHERE statusOfRequest='Active' ".
					 "ERROR when confirm, probably it already confirmed.";
			}
		}else{
			echo "user not specified";
		}
	}else{
		echo "only one specified user allowed for these actions";
	}
	wp_die();
}
