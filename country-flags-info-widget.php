<?php
/**
 * @package CountryFlagsInfo_widget
 * @version 1.0
 */
/*
Plugin Name: Country Flags Info Widget
Description: Enables a widget in which you can display a list of country with flags, names and misc information.
Author: Stéphane Moitry
Author URI: http://stephane.moitry.fr
Version: 1.0.0
*/

class CountryFlagsInfoWidget extends WP_Widget {
	
	public function __construct() {
		$widget_ops = array('classname' => 'widget_countryflags', 'description' => __('A list of countries with flags and misc info.'));
		parent::__construct(
			'countryflags', // Base ID
			__('Country Flags Info'), // Name
			$widget_ops
		);
		
		add_action('admin_enqueue_scripts', array($this,'smcfi_load_scripts'));
		wp_enqueue_style( 'smcfi-css', plugin_dir_url(__FILE__) .'css/smcfi.css');
	}

	public function widget( $args, $instance ) {
		extract($args);
		$title = apply_filters('widget_title', empty($instance['title']) ? __('Countries') : $instance['title']);
		$type = empty($instance['type']) ? 'unordered' : $instance['type'] ;
		$showname = isset($instance['showname']) ? $instance['showname'] : false;
		$amount = empty($instance['amount']) ? 3 : $instance['amount'];
		
		for ($i = 1; $i <= $amount; $i++) {
			$items[$i-1] = trim($instance['item'.$i]);
			$item_name[$i-1] = $instance['item_name'.$i];
			$item_text[$i-1] = $instance['item_text'.$i];
		}
		
		echo $before_widget .  $before_title . $title . $after_title;
		if ($type == "ordered") { echo "<ol ";} else { echo("<ul "); } ?> class="list">

		<?php foreach ($items as $num => $item) : 
			if (!empty($item)) :
				if ($showname) :
					echo("<li><div class='smcfi-flag'><img src='" . plugins_url('flags/' . $item . '.png', __FILE__ ) . "' /></div><span class='smcfi-name'>" . $item_name[$num] . "</span><span class='smcfi-misc'>" . $item_text[$num] . "</span></li>");
				else :
					echo("<li><div class='smcfi-flag'><img src='" . plugins_url('flags/' . $item . '.png', __FILE__ ) . "' /></div><span class='smcfi-misc'>" . $item_text[$num] . "</span></li>");
				endif;
			endif;
		endforeach;
		
      	if ($type == "ordered") { echo "</ol>";} else { echo("</ul>"); }
		
		echo $after_widget;
	}

	public function update( $new_instance, $old_instance) {
		//$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
		$amount = $new_instance['amount'];
		$new_item = empty($new_instance['new_item']) ? false : strip_tags($new_instance['new_item']);
		
		if ( isset($new_instance['position1'])) {
			for($i=1; $i<= $new_instance['amount']; $i++){
				if($new_instance['position'.$i] != -1){
					$position[$i] = $new_instance['position'.$i];
				}else{
					$amount--;
				}
			}
			if($position){
				asort($position);
				$order = array_keys($position);
				if(strip_tags($new_instance['new_item'])){
					$amount++;
					array_push($order, $amount);
				}
			}
			
		}else{
			$order = explode(',',$new_instance['order']);
			foreach($order as $key => $order_str){
				$num = strrpos($order_str,'-');
				if($num !== false){
					$order[$key] = substr($order_str,$num+1);
				}
			}
		}
		
		if($order){
			foreach ($order as $i => $item_num) {
				$instance['item'.($i+1)] = empty($new_instance['item'.$item_num]) ? '' : strip_tags($new_instance['item'.$item_num]);
				$instance['item_name'.($i+1)] = empty($new_instance['item_name'.$item_num]) ? '' : strip_tags($new_instance['item_name'.$item_num]);
				$instance['item_text'.($i+1)] = empty($new_instance['item_text'.$item_num]) ? '' : strip_tags($new_instance['item_text'.$item_num]);
			}
		}
		
		$instance['amount'] = $amount;
		$instance['type'] = strip_tags($new_instance['type']);
		$instance['showname'] = empty($new_instance['showname']) ? '' : strip_tags($new_instance['showname']);

		return $instance;
	}

	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'text' => '', 'title_link' => '' ) );
		$title = strip_tags($instance['title']);
		$amount = empty($instance['amount']) ? 3 : $instance['amount'];
		
		for ($i = 1; $i <= $amount; $i++) {
			$items[$i] = empty($instance['item'.$i]) ? '' : $instance['item'.$i];
			$item_names[$i] = empty($instance['item_name'.$i]) ? '' : $instance['item_name'.$i];
			$item_texts[$i] = empty($instance['item_text'.$i]) ? '' : $instance['item_text'.$i];
		}
		$title_link = $instance['title_link'];	
		$type = empty($instance['type']) ? 'unordered' : $instance['type'];
		$showname = empty($instance['showname']) ? '' : $instance['showname'];
?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>
		<ul class="smcfi-instructions">
			<li><?php echo __("If an item is left blank it will not be output."); ?></li>
			<li><?php echo __("The country name and misc text fields are optional and can be left blank."); ?></li>
			<li class="hide-if-no-js"><?php echo __("Reorder the list items by clicking and dragging the item number."); ?></li>
			<li class="hide-if-no-js"><?php echo __("To remove an item, simply click the 'Remove' button."); ?></li>
			<li class="hide-if-js"><?php echo __("Reorder or delete an item by using the 'Position/Action' table below."); ?></li>
			<li class="hide-if-js"><?php echo __("To add a new item, check the 'Add New Item' box and save the widget."); ?></li>
		</ul>
		<div class="country-flags-info">
		<?php foreach ($items as $num => $item) :
			$item = esc_attr($item);
			$item_name = esc_attr($item_names[$num]);
			$item_text = esc_attr($item_texts[$num]);
		?>
		
			<div id="<?php echo $this->get_field_id($num); ?>" class="list-item">
				<h5 class="smcfi-moving-handle"><span class="number"><?php echo $num; ?></span>. <span class="item-title"><?php echo $item; ?></span><a class="smcfi-action hide-if-no-js"></a></h5>
				<div class="smcfi-edit-item">
					<label for="<?php echo $this->get_field_id('item'.$num); ?>"><?php echo __("Country ISO Code (2 letters):"); ?></label>
					<select class="widefat countryisocode" id="<?php echo $this->get_field_id('item'.$num); ?>" name="<?php echo $this->get_field_name('item'.$num); ?>" type="text">
						<option value=" "><?php echo __("Select country"); ?></option>
						<option value="AD" <?php if ($item == "AD") {echo "selected";} ?>>AD - Andorra</option>
						<option value="AE" <?php if ($item == "AE") {echo "selected";} ?>>AE - United Arabian Emirates</option>
						<option value="AF" <?php if ($item == "AF") {echo "selected";} ?>>AF - Afghanestan</option>
						<option value="AG" <?php if ($item == "AG") {echo "selected";} ?>>AG - Antigua and Barbadua</option>
						<option value="AI" <?php if ($item == "AI") {echo "selected";} ?>>AI - Anguilla</option>
						<option value="AL" <?php if ($item == "AL") {echo "selected";} ?>>AL - Shqip&euml;ri (Albania)</option>
						<option value="AM" <?php if ($item == "AM") {echo "selected";} ?>>AM - Hayastan (Armenia)</option>
						<option value="AO" <?php if ($item == "AO") {echo "selected";} ?>>AO - Angola</option>
						<option value="AQ" <?php if ($item == "AQ") {echo "selected";} ?>>AQ - Antactica</option>
						<option value="AR" <?php if ($item == "AR") {echo "selected";} ?>>AR - Argentina</option>
						<option value="AS" <?php if ($item == "AS") {echo "selected";} ?>>AS - Samoa</option>
						<option value="AT" <?php if ($item == "AT") {echo "selected";} ?>>AT - &Ouml;sterreich</option>
						<option value="AU" <?php if ($item == "AU") {echo "selected";} ?>>AU - Australia</option>
						<option value="AW" <?php if ($item == "AW") {echo "selected";} ?>>AW - Aruba</option>
						<option value="AX" <?php if ($item == "AX") {echo "selected";} ?>>AX - Landskapet &Aring;land (&Aring;land Island)</option>
						<option value="AZ" <?php if ($item == "AZ") {echo "selected";} ?>>AZ - Azərbaycan (Azerba&iuml;djan)</option>
						<option value="BA" <?php if ($item == "BA") {echo "selected";} ?>>BA - Bosna i Hercegovina</option>
						<option value="BB" <?php if ($item == "BB") {echo "selected";} ?>>BB - Barbados</option>
						<option value="BD" <?php if ($item == "BD") {echo "selected";} ?>>BD - Bangladesh</option>
						<option value="BE" <?php if ($item == "BE") {echo "selected";} ?>>BE - Beli&euml; (Belgique)</option>
						<option value="BF" <?php if ($item == "BF") {echo "selected";} ?>>BF - Burkina Faso</option>
						<option value="BG" <?php if ($item == "BG") {echo "selected";} ?>>BG - Balgaria (Bulgaria)</option>
						<option value="BH" <?php if ($item == "BH") {echo "selected";} ?>>BH - Bahre&iuml;n</option>
						<option value="BI" <?php if ($item == "BI") {echo "selected";} ?>>BI - Burundi</option>
						<option value="BJ" <?php if ($item == "BJ") {echo "selected";} ?>>BJ - B&eacute;nin</option>
						<option value="BL" <?php if ($item == "BL") {echo "selected";} ?>>BL - Saint-Barth&eacute;lemy</option>
						<option value="BM" <?php if ($item == "BM") {echo "selected";} ?>>BM - Bermuda</option>
						<option value="BN" <?php if ($item == "BN") {echo "selected";} ?>>BN - Brunei</option>
						<option value="BO" <?php if ($item == "BO") {echo "selected";} ?>>BO - Bolivia</option>
						<option value="BR" <?php if ($item == "BR") {echo "selected";} ?>>BR - Brasil</option>
						<option value="BS" <?php if ($item == "BS") {echo "selected";} ?>>BS - Bahamas</option>
						<option value="BT" <?php if ($item == "BT") {echo "selected";} ?>>BT - Druk Yul (Bhoutan)</option>
						<option value="BW" <?php if ($item == "BW") {echo "selected";} ?>>BW - Botswana</option>
						<option value="BY" <?php if ($item == "BY") {echo "selected";} ?>>BY - Belarusy</option>
						<option value="BZ" <?php if ($item == "BZ") {echo "selected";} ?>>BZ - Belize</option>
						<option value="CA" <?php if ($item == "CA") {echo "selected";} ?>>CA - Canada</option>
						<option value="CC" <?php if ($item == "CC") {echo "selected";} ?>>CC - Cocos Island</option>
						<option value="CD" <?php if ($item == "CD") {echo "selected";} ?>>CD - R&eacute;publique d&eacute;mocratique du Congo</option>
						<option value="CF" <?php if ($item == "CF") {echo "selected";} ?>>CF - R&eacute;publique centrafricaine</option>
						<option value="CG" <?php if ($item == "CG") {echo "selected";} ?>>CG - R&eacute;publique du Congo</option>
						<option value="CH" <?php if ($item == "CH") {echo "selected";} ?>>CH - Schweiz</option>
						<option value="CI" <?php if ($item == "CI") {echo "selected";} ?>>CI - C&ocirc;te d'Ivoire</option>
						<option value="CK" <?php if ($item == "CK") {echo "selected";} ?>>CK - Cook Islands</option>
						<option value="CL" <?php if ($item == "CL") {echo "selected";} ?>>CL - Chili</option>
						<option value="CM" <?php if ($item == "CM") {echo "selected";} ?>>CM - Cameroun</option>
						<option value="CN" <?php if ($item == "CN") {echo "selected";} ?>>CN - China</option>
						<option value="CO" <?php if ($item == "CO") {echo "selected";} ?>>CO - Colombia</option>
						<option value="CR" <?php if ($item == "CR") {echo "selected";} ?>>CR - Costa Rica</option>
						<option value="CU" <?php if ($item == "CU") {echo "selected";} ?>>CU - Cuba</option>
						<option value="CV" <?php if ($item == "CV") {echo "selected";} ?>>CV - Cabo Verde</option>
						<option value="CW" <?php if ($item == "CW") {echo "selected";} ?>>CW - Cura&ccedil;ao</option>
						<option value="CX" <?php if ($item == "CX") {echo "selected";} ?>>CX - Christmas Islands</option>
						<option value="CY" <?php if ($item == "CY") {echo "selected";} ?>>CY - K&yacute;pros (Chypre)</option>
						<option value="CZ" <?php if ($item == "CZ") {echo "selected";} ?>>CZ - Cesk&aacute; Republika</option>
						<option value="DE" <?php if ($item == "DE") {echo "selected";} ?>>DE - Deutschland</option>
						<option value="DJ" <?php if ($item == "DJ") {echo "selected";} ?>>DJ - Djibouti</option>
						<option value="DK" <?php if ($item == "DK") {echo "selected";} ?>>DK - Danmark</option>
						<option value="DM" <?php if ($item == "DM") {echo "selected";} ?>>DM - Dominica</option>
						<option value="DO" <?php if ($item == "DO") {echo "selected";} ?>>DO - Rep&uacute;blica Dominicana</option>
						<option value="DZ" <?php if ($item == "DZ") {echo "selected";} ?>>DZ - Alg&eacute;rie</option>
						<option value="EC" <?php if ($item == "EC") {echo "selected";} ?>>EC - Ecuador</option>
						<option value="EE" <?php if ($item == "EE") {echo "selected";} ?>>EE - Eesti (Estonia)</option>
						<option value="EG" <?php if ($item == "EG") {echo "selected";} ?>>EG - Egypt</option>
						<option value="EH" <?php if ($item == "EH") {echo "selected";} ?>>EH - Sahara Occidental</option>
						<option value="ER" <?php if ($item == "ER") {echo "selected";} ?>>ER - Hagere Ertra (Eritrea)</option>
						<option value="ES" <?php if ($item == "ES") {echo "selected";} ?>>ES - Espa&ntilde;a</option>
						<option value="ET" <?php if ($item == "ET") {echo "selected";} ?>>ET - YeItyopya (Ethiopia)</option>
						<option value="FI" <?php if ($item == "FI") {echo "selected";} ?>>FI - Suomen Tasavalta (Finland)</option>
						<option value="FJ" <?php if ($item == "FJ") {echo "selected";} ?>>FJ - Fidji</option>
						<option value="FK" <?php if ($item == "FK") {echo "selected";} ?>>FK - Falkland Islands</option>
						<option value="FM" <?php if ($item == "FM") {echo "selected";} ?>>FM - Micronedia</option>
						<option value="FO" <?php if ($item == "FO") {echo "selected";} ?>>FO - F&oslash;royar (Feroe Islands)</option>
						<option value="FR" <?php if ($item == "FR") {echo "selected";} ?>>FR - France</option>
						<option value="GA" <?php if ($item == "GA") {echo "selected";} ?>>GA - Gabon</option>
						<option value="GB" <?php if ($item == "GB") {echo "selected";} ?>>GB - United Kingdom</option>
						<option value="GD" <?php if ($item == "GD") {echo "selected";} ?>>GD - Grenada</option>
						<option value="GE" <?php if ($item == "GE") {echo "selected";} ?>>GE - Sakartvelo (Georgia)</option>
						<option value="GG" <?php if ($item == "GG") {echo "selected";} ?>>GG - Guernsey</option>
						<option value="GH" <?php if ($item == "GH") {echo "selected";} ?>>GH - Ghana</option>
						<option value="GI" <?php if ($item == "GI") {echo "selected";} ?>>GI - Gibraltar</option>
						<option value="GL" <?php if ($item == "GL") {echo "selected";} ?>>GL - Gr&oslash;nland</option>
						<option value="GM" <?php if ($item == "GM") {echo "selected";} ?>>GM - Gambia</option>
						<option value="GN" <?php if ($item == "GN") {echo "selected";} ?>>GN - Guin&eacute;e</option>
						<option value="GQ" <?php if ($item == "GQ") {echo "selected";} ?>>GQ - Guin&eacute;e &eacute;quatoriale</option>
						<option value="GR" <?php if ($item == "GR") {echo "selected";} ?>>GR - Ε&lambda;&lambda;&alpha;&delta;&alpha; (Greece)</option>
						<option value="GS" <?php if ($item == "GS") {echo "selected";} ?>>GS - South Georgia and South Sandwich Islands</option>
						<option value="GT" <?php if ($item == "GT") {echo "selected";} ?>>GT - Guatemala</option>
						<option value="GU" <?php if ($item == "GU") {echo "selected";} ?>>GU - Guam</option>
						<option value="GW" <?php if ($item == "GW") {echo "selected";} ?>>GW - Guin&eacute;-Bissau</option>
						<option value="GY" <?php if ($item == "GY") {echo "selected";} ?>>GY - Co-Operative Republic of Guyana</option>
						<option value="HK" <?php if ($item == "HK") {echo "selected";} ?>>HK - Hong Kong</option>
						<option value="HN" <?php if ($item == "HN") {echo "selected";} ?>>HN - Honduras</option>
						<option value="HR" <?php if ($item == "HR") {echo "selected";} ?>>HR - Hrvatska (Croatia)</option>
						<option value="HT" <?php if ($item == "HT") {echo "selected";} ?>>HT - Ha&iuml;ti</option>
						<option value="HU" <?php if ($item == "HU") {echo "selected";} ?>>HU - Magyar (Hungary)</option>
						<option value="ID" <?php if ($item == "ID") {echo "selected";} ?>>ID - Indonesia</option>
						<option value="IE" <?php if ($item == "IE") {echo "selected";} ?>>IE - Ireland</option>
						<option value="IL" <?php if ($item == "IL") {echo "selected";} ?>>IL - Isra&euml;l</option>
						<option value="IM" <?php if ($item == "IM") {echo "selected";} ?>>IM - &Icirc;le de Man</option>
						<option value="IN" <?php if ($item == "IN") {echo "selected";} ?>>IN - India</option>
						<option value="IQ" <?php if ($item == "IQ") {echo "selected";} ?>>IQ - Al 'Ir&#257;q (Iraqi)</option>
						<option value="IR" <?php if ($item == "IR") {echo "selected";} ?>>IR - &#298;r&#257;n (Iran)</option>
						<option value="IS" <?php if ($item == "IS") {echo "selected";} ?>>IS - &Iacute;sland</option>
						<option value="IT" <?php if ($item == "IT") {echo "selected";} ?>>IT - Italia</option>
						<option value="JE" <?php if ($item == "JE") {echo "selected";} ?>>JE - Jersay</option>
						<option value="JM" <?php if ($item == "JM") {echo "selected";} ?>>JM - Jama&iuml;ca</option>
						<option value="JO" <?php if ($item == "JO") {echo "selected";} ?>>JO - Al Mamlakah al Urduniyah al Hashimiyah (Jordania)</option>
						<option value="JP" <?php if ($item == "JP") {echo "selected";} ?>>JP - Japan</option>
						<option value="KE" <?php if ($item == "KE") {echo "selected";} ?>>KE - Kenya</option>
						<option value="KG" <?php if ($item == "KG") {echo "selected";} ?>>KG - Kirgiz (Kirghizistan)</option>
						<option value="KH" <?php if ($item == "KH") {echo "selected";} ?>>KH - Cambodgia</option>
						<option value="KI" <?php if ($item == "KI") {echo "selected";} ?>>KI - Kiribati</option>
						<option value="KM" <?php if ($item == "KM") {echo "selected";} ?>>KM - Comores</option>
						<option value="KN" <?php if ($item == "KN") {echo "selected";} ?>>KN - Saint-Christophe-et-Ni&eacute;v&egrave;s</option>
						<option value="KP" <?php if ($item == "KP") {echo "selected";} ?>>KP - North Korea</option>
						<option value="KR" <?php if ($item == "KR") {echo "selected";} ?>>KR - South Korea</option>
						<option value="KW" <?php if ($item == "KW") {echo "selected";} ?>>KW - Kuwayt (Kowe&iuml;t)</option>
						<option value="KY" <?php if ($item == "KY") {echo "selected";} ?>>KY - Cayman Islands</option>
						<option value="KZ" <?php if ($item == "KZ") {echo "selected";} ?>>KZ - Qazaqstan (Kazakhstan)</option>
						<option value="LA" <?php if ($item == "LA") {echo "selected";} ?>>LA - Laos</option>
						<option value="LB" <?php if ($item == "LB") {echo "selected";} ?>>LB - Liban</option>
						<option value="LC" <?php if ($item == "LC") {echo "selected";} ?>>LC - Saint Lucia</option>
						<option value="LI" <?php if ($item == "LI") {echo "selected";} ?>>LI - Liechtenstein</option>
						<option value="LK" <?php if ($item == "LK") {echo "selected";} ?>>LK - Sri Lanka</option>
						<option value="LR" <?php if ($item == "LR") {echo "selected";} ?>>LR - Liberia</option>
						<option value="LS" <?php if ($item == "LS") {echo "selected";} ?>>LS - Lesotho</option>
						<option value="LT" <?php if ($item == "LT") {echo "selected";} ?>>LT - Lietuva (Lituania)</option>
						<option value="LU" <?php if ($item == "LU") {echo "selected";} ?>>LU - L&euml;tzebuerg (Luxembourg)</option>
						<option value="LV" <?php if ($item == "LV") {echo "selected";} ?>>LV - Latvijas (Latvia)</option>
						<option value="LY" <?php if ($item == "LY") {echo "selected";} ?>>LY - Libya</option>
						<option value="MA" <?php if ($item == "MA") {echo "selected";} ?>>MA - Al Mamlakatu'l-Maghribiya (Morocco)</option>
						<option value="MC" <?php if ($item == "MC") {echo "selected";} ?>>MC - Monaco</option>
						<option value="MD" <?php if ($item == "MD") {echo "selected";} ?>>MD - Moldova</option>
						<option value="ME" <?php if ($item == "ME") {echo "selected";} ?>>ME - Mont&eacute;n&eacute;gro</option>
						<option value="MF" <?php if ($item == "MF") {echo "selected";} ?>>MF - Saint Martin</option>
						<option value="MG" <?php if ($item == "MG") {echo "selected";} ?>>MG - Madagascar</option>
						<option value="MH" <?php if ($item == "MH") {echo "selected";} ?>>MH - Mashall Islands</option>
						<option value="MK" <?php if ($item == "MK") {echo "selected";} ?>>MK - Mac&eacute;donia</option>
						<option value="ML" <?php if ($item == "ML") {echo "selected";} ?>>ML - Mali</option>
						<option value="MM" <?php if ($item == "MM") {echo "selected";} ?>>MM - Myanmar (Birmanie)</option>
						<option value="MN" <?php if ($item == "MN") {echo "selected";} ?>>MN - Mongol</option>
						<option value="MO" <?php if ($item == "MO") {echo "selected";} ?>>MO - Macao</option>
						<option value="MP" <?php if ($item == "MP") {echo "selected";} ?>>MP - Northern Mariana Islands</option>
						<option value="MQ" <?php if ($item == "MQ") {echo "selected";} ?>>MQ - Martinique</option>
						<option value="MR" <?php if ($item == "MR") {echo "selected";} ?>>MR - Mauritania</option>
						<option value="MS" <?php if ($item == "MS") {echo "selected";} ?>>MS - Montserrat</option>
						<option value="MT" <?php if ($item == "MT") {echo "selected";} ?>>MT - Malta</option>
						<option value="MU" <?php if ($item == "MU") {echo "selected";} ?>>MU - Maurice</option>
						<option value="MV" <?php if ($item == "MV") {echo "selected";} ?>>MV - Maldives</option>
						<option value="MW" <?php if ($item == "MW") {echo "selected";} ?>>MW - Malawi</option>
						<option value="MX" <?php if ($item == "MX") {echo "selected";} ?>>MX - Mexico</option>
						<option value="MY" <?php if ($item == "MY") {echo "selected";} ?>>MY - Malaysia</option>
						<option value="MZ" <?php if ($item == "MZ") {echo "selected";} ?>>MZ - Moçambique</option>
						<option value="NA" <?php if ($item == "NA") {echo "selected";} ?>>NA - Namibia</option>
						<option value="NC" <?php if ($item == "NC") {echo "selected";} ?>>NC - Nouvelle-Cal&eacute;donie</option>
						<option value="NE" <?php if ($item == "NE") {echo "selected";} ?>>NE - Niger</option>
						<option value="NF" <?php if ($item == "NF") {echo "selected";} ?>>NF - Norfolk Island</option>
						<option value="NG" <?php if ($item == "NG") {echo "selected";} ?>>NG - Nigeria</option>
						<option value="NI" <?php if ($item == "NI") {echo "selected";} ?>>NI - Nicaragua</option>
						<option value="NL" <?php if ($item == "NL") {echo "selected";} ?>>NL - Nederland</option>
						<option value="NO" <?php if ($item == "NO") {echo "selected";} ?>>NO - Noreg (Norge)</option>
						<option value="NP" <?php if ($item == "NP") {echo "selected";} ?>>NP - N&eacute;pal</option>
						<option value="NR" <?php if ($item == "NR") {echo "selected";} ?>>NR - Nauru</option>
						<option value="NU" <?php if ($item == "NU") {echo "selected";} ?>>NU - Niue</option>
						<option value="NZ" <?php if ($item == "NZ") {echo "selected";} ?>>NZ - New Zeland</option>
						<option value="OM" <?php if ($item == "OM") {echo "selected";} ?>>OM - Uman Saltanat (Oman)</option>
						<option value="PA" <?php if ($item == "PA") {echo "selected";} ?>>PA - Panama</option>
						<option value="PE" <?php if ($item == "PE") {echo "selected";} ?>>PE - Peru</option>
						<option value="PF" <?php if ($item == "PF") {echo "selected";} ?>>PF - Polyn&eacute;sie française</option>
						<option value="PG" <?php if ($item == "PG") {echo "selected";} ?>>PG - Papouasie-Nouvelle-Guin&eacute;e</option>
						<option value="PH" <?php if ($item == "PH") {echo "selected";} ?>>PH - Pilipinas (Philippins)</option>
						<option value="PK" <?php if ($item == "PK") {echo "selected";} ?>>PK - Pakistan</option>
						<option value="PL" <?php if ($item == "PL") {echo "selected";} ?>>PL - Polska (Poland)</option>
						<option value="PN" <?php if ($item == "PN") {echo "selected";} ?>>PN - &Icirc;les Pitcairn</option>
						<option value="PR" <?php if ($item == "PR") {echo "selected";} ?>>PR - Puerto Rico</option>
						<option value="PS" <?php if ($item == "PS") {echo "selected";} ?>>PS - Palestin</option>
						<option value="PT" <?php if ($item == "PT") {echo "selected";} ?>>PT - Portugal</option>
						<option value="PW" <?php if ($item == "PW") {echo "selected";} ?>>PW - Palaos (Palau)</option>
						<option value="PY" <?php if ($item == "PY") {echo "selected";} ?>>PY - Paraguay</option>
						<option value="QA" <?php if ($item == "QA") {echo "selected";} ?>>QA - Qatar</option>
						<option value="RO" <?php if ($item == "RO") {echo "selected";} ?>>RO - Rom&acirc;nia</option>
						<option value="RS" <?php if ($item == "RS") {echo "selected";} ?>>RS - Srbija (Serbia)</option>
						<option value="RU" <?php if ($item == "RU") {echo "selected";} ?>>RU - Russia</option>
						<option value="RW" <?php if ($item == "RW") {echo "selected";} ?>>RW - Rwanda</option>
						<option value="SA" <?php if ($item == "SA") {echo "selected";} ?>>SA - Mamlakah al 'Arabiyahas Su'udiyah (Saoudian Arabia)</option>
						<option value="SB" <?php if ($item == "SB") {echo "selected";} ?>>SB - Salomons Islands</option>
						<option value="SC" <?php if ($item == "SC") {echo "selected";} ?>>SC - Sesel (Seychelles)</option>
						<option value="SD" <?php if ($item == "SD") {echo "selected";} ?>>SD - Sudan</option>
						<option value="SE" <?php if ($item == "SE") {echo "selected";} ?>>SE - Sverige (Sweden)</option>
						<option value="SG" <?php if ($item == "SG") {echo "selected";} ?>>SG - Singapura (Singapore)</option>
						<option value="SH" <?php if ($item == "SH") {echo "selected";} ?>>SH - Sainte-H&eacute;lène</option>
						<option value="SI" <?php if ($item == "SI") {echo "selected";} ?>>SI - Slovenika (Slovenia)</option>
						<option value="SK" <?php if ($item == "SK") {echo "selected";} ?>>SK - Slovenska (Slovaquia)</option>
						<option value="SL" <?php if ($item == "SL") {echo "selected";} ?>>SL - Sierra Leone</option>
						<option value="SM" <?php if ($item == "SM") {echo "selected";} ?>>SM - San Marino</option>
						<option value="SN" <?php if ($item == "SN") {echo "selected";} ?>>SN - S&eacute;n&eacute;gal</option>
						<option value="SO" <?php if ($item == "SO") {echo "selected";} ?>>SO - Soomaaliya (Somalia)</option>
						<option value="SR" <?php if ($item == "SR") {echo "selected";} ?>>SR - Suriname</option>
						<option value="SS" <?php if ($item == "SS") {echo "selected";} ?>>SS - South Sudan</option>
						<option value="ST" <?php if ($item == "ST") {echo "selected";} ?>>ST - S&atilde;o Tom&eacute; e Pr&iacute;ncipe</option>
						<option value="SV" <?php if ($item == "SV") {echo "selected";} ?>>SV - Salvador</option>
						<option value="SY" <?php if ($item == "SY") {echo "selected";} ?>>SY - S&#363;riyyah (Syria)</option>
						<option value="SZ" <?php if ($item == "SZ") {echo "selected";} ?>>SZ - Swaziland</option>
						<option value="TC" <?php if ($item == "TC") {echo "selected";} ?>>TC - Turks-and-Caicos</option>
						<option value="TD" <?php if ($item == "TD") {echo "selected";} ?>>TD - Tsh&#257;d (Tchad)</option>
						<option value="TF" <?php if ($item == "TF") {echo "selected";} ?>>TF - Terres australes et antarctiques françaises</option>
						<option value="TG" <?php if ($item == "TG") {echo "selected";} ?>>TG - Togo</option>
						<option value="TH" <?php if ($item == "TH") {echo "selected";} ?>>TH - Thai (Thaïland)</option>
						<option value="TJ" <?php if ($item == "TJ") {echo "selected";} ?>>TJ - Tadshikist&aacute;n (Tadjikistan)</option>
						<option value="TK" <?php if ($item == "TK") {echo "selected";} ?>>TK - Tokelau</option>
						<option value="TL" <?php if ($item == "TL") {echo "selected";} ?>>TL - Timor-Leste</option>
						<option value="TM" <?php if ($item == "TM") {echo "selected";} ?>>TM - T&uuml;rkmenistan</option>
						<option value="TN" <?php if ($item == "TN") {echo "selected";} ?>>TN - T&#363;nisiyya (Tunisia)</option>
						<option value="TO" <?php if ($item == "TO") {echo "selected";} ?>>TO - Tonga</option>
						<option value="TR" <?php if ($item == "TR") {echo "selected";} ?>>TR - T&uuml;rkiye (Turquie)</option>
						<option value="TT" <?php if ($item == "TT") {echo "selected";} ?>>TT - Trinidad and Tobago</option>
						<option value="TV" <?php if ($item == "TV") {echo "selected";} ?>>TV - Tuvalu</option>
						<option value="TW" <?php if ($item == "TW") {echo "selected";} ?>>TW - T&aacute;iw&#257;n (Taïwan)</option>
						<option value="TZ" <?php if ($item == "TZ") {echo "selected";} ?>>TZ - Tanzania</option>
						<option value="UA" <?php if ($item == "UA") {echo "selected";} ?>>UA - Ukra&iuml;na</option>
						<option value="UG" <?php if ($item == "UG") {echo "selected";} ?>>UG - Uganda</option>
						<option value="US" <?php if ($item == "US") {echo "selected";} ?>>US - United States of America</option>
						<option value="UY" <?php if ($item == "UY") {echo "selected";} ?>>UY - Uruguay</option>
						<option value="UZ" <?php if ($item == "UZ") {echo "selected";} ?>>UZ - O'zbekiston (Ouzb&eacute;kistan)</option>
						<option value="VA" <?php if ($item == "VA") {echo "selected";} ?>>VA - Vaticano</option>
						<option value="VC" <?php if ($item == "VC") {echo "selected";} ?>>VC - Saint Vincent and the Grenadines</option>
						<option value="VE" <?php if ($item == "VE") {echo "selected";} ?>>VE - Venezuela</option>
						<option value="VG" <?php if ($item == "VG") {echo "selected";} ?>>VG - British Virgin Islands</option>
						<option value="VI" <?php if ($item == "VI") {echo "selected";} ?>>VI - US Virgin Islands</option>
						<option value="VN" <?php if ($item == "VN") {echo "selected";} ?>>VN - Vi&ecirc;t Nam</option>
						<option value="VU" <?php if ($item == "VU") {echo "selected";} ?>>VU - Vanuatu</option>
						<option value="WF" <?php if ($item == "WF") {echo "selected";} ?>>WF - Wallis-et-Futuna</option>
						<option value="WS" <?php if ($item == "WS") {echo "selected";} ?>>WS - Samoa</option>
						<option value="YE" <?php if ($item == "YE") {echo "selected";} ?>>YE - Y&eacute;men</option>
						<option value="YT" <?php if ($item == "YT") {echo "selected";} ?>>YT - Mayotte</option>
						<option value="ZA" <?php if ($item == "ZA") {echo "selected";} ?>>ZA - Suid-Afrika (South Africa)</option>
						<option value="ZM" <?php if ($item == "ZM") {echo "selected";} ?>>ZM - Zambia</option>
						<option value="ZW" <?php if ($item == "ZW") {echo "selected";} ?>>ZW - Zimbabwe</option>
					</select>
					<label for="<?php echo $this->get_field_id('item_name'.$num); ?>"><?php echo __("Country name:"); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('item_name'.$num); ?>" name="<?php echo $this->get_field_name('item_name'.$num); ?>" type="text" value="<?php echo $item_name; ?>" />
					<label for="<?php echo $this->get_field_id('item_text'.$num); ?>"><?php echo __("Misc text:"); ?></label>
					<input class="widefat" id="<?php echo $this->get_field_id('item_text'.$num); ?>" name="<?php echo $this->get_field_name('item_text'.$num); ?>" type="text" value="<?php echo $item_text; ?>" />
					<a class="smcfi-delete hide-if-no-js"><img src="<?php echo plugins_url('images/delete.png', __FILE__ ); ?>" /> <?php echo __("Remove"); ?></a>
				</div>
			</div>
			
		<?php endforeach; 
		
		if ( isset($_GET['editwidget']) && $_GET['editwidget'] ) : ?>
			<table class='widefat'>
				<thead><tr><th><?php echo __("Item"); ?></th><th><?php echo __("Position/Action"); ?></th></tr></thead>
				<tbody>
					<?php foreach ($items as $num => $item) : ?>
					<tr>
						<td><?php echo esc_attr($item); ?></td>
						<td>
							<select id="<?php echo $this->get_field_id('position'.$num); ?>" name="<?php echo $this->get_field_name('position'.$num); ?>">
								<option><?php echo __('&mdash; Select &mdash;'); ?></option>
								<?php for($i=1; $i<=count($items); $i++) {
									if($i==$num){
										echo "<option value='$i' selected>$i</option>";
									}else{
										echo "<option value='$i'>$i</option>";
									}
								} ?>
								<option value="-1"><?php echo __("Delete"); ?></option>
							</select>
						</td>
					</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
			
			<div class="smcfi-row">
				<input type="checkbox" name="<?php echo $this->get_field_name('new_item'); ?>" id="<?php echo $this->get_field_id('new_item'); ?>" /> <label for="<?php echo $this->get_field_id('new_item'); ?>"><?php echo __("Add New Item"); ?></label>
			</div>
		<?php endif; ?>
			
		</div>
		<div class="smcfi-row hide-if-no-js">
			<a class="smcfi-add button-secondary"><img src="<?php echo plugins_url('images/add.png', __FILE__ )?>" /> <?php echo __("Add Item"); ?></a>
		</div>

		<input type="hidden" id="<?php echo $this->get_field_id('amount'); ?>" class="amount" name="<?php echo $this->get_field_name('amount'); ?>" value="<?php echo $amount ?>" />
		<input type="hidden" id="<?php echo $this->get_field_id('order'); ?>" class="order" name="<?php echo $this->get_field_name('order'); ?>" value="<?php echo implode(',',range(1,$amount)); ?>" />

		<div class="smcfi-row">
			<label for="<?php echo $this->get_field_id('ordered'); ?>"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" value="ordered" id="<?php echo $this->get_field_id('ordered'); ?>" <?php checked($type, "ordered"); ?> />  <?php echo __("Ordered"); ?></label>
			<label for="<?php echo $this->get_field_id('unordered'); ?>"><input type="radio" name="<?php echo $this->get_field_name('type'); ?>" value="unordered" id="<?php echo $this->get_field_id('unordered'); ?>" <?php checked($type, "unordered"); ?> /> <?php echo __("Unordered"); ?></label>
		</div>

		<div class="smcfi-row">
			<input type="checkbox" name="<?php echo $this->get_field_name('showname'); ?>" id="<?php echo $this->get_field_id('showname'); ?>" <?php checked($showname, 'on'); ?> /> <label for="<?php echo $this->get_field_id('showname'); ?>"><?php echo __("Display country names"); ?></label>
		</div>

<?php
	}
	
	public function smcfi_load_scripts($hook) {
		if( $hook != 'widgets.php') 
			return;
		if ( !isset($_GET['editwidget'])) {
			wp_enqueue_script( 'smcfi-sort-js', plugin_dir_url(__FILE__) .'js/smcfi-sort.js');
		}
		wp_enqueue_style( 'smcfi-admin-css', plugin_dir_url(__FILE__) .'css/smcfi-admin.css');
	}
}

add_action('widgets_init', create_function('', 'return register_widget("CountryFlagsInfoWidget");'));
?>