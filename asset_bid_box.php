<?php
$this->queue('css', array('src' => '/css/widgets/asset_bid_box.css', 'media' => 'screen'));
$this->queue('js', array('src' => '/js/jquery/jquery.countdown.js'));
$this->queue('js', array('src' => '/js/jquery/jquery.populate.js'));
$this->queue('js', array('src' => '/js/widgets/asset_bid_box.js'));

$params['asset_id'] = $this->vars['asset_id'];
$detail = System::Model('AssetDetail')->getEventAsset($params);
//Debug::dump($detail);

$asset_detail = new ArrayRegistry(System::Model('AssetDetail')->assetDetail($params));
//Debug::dump($asset_detail);

$user = System::UserAuth();
//Debug::dump($user);
$user_info = $user->getUser();
$user_id = $user_info['web_user_id'];
$user_name = $user_info['web_username'];
$auction_id = $detail['event_asset_id'];
$end_date = date('YmdHis', strtotime($detail['end_datetime_revised']));
$start_date = date('YmdHis', strtotime($detail['start_datetime']));
if ( $user_name == $detail['curr_bid_web_username'] and !empty($user_name)) {
	$detail['High_Bid'] = '<div class="high_bid">You are the High Bidder!</div>';
} else {
	$detail['High_Bid'] = '';
}

//Test event asset id to check multiple bid types
$params['event_asset_id'] = $auction_id;

//Get the bid phases for an event asset ID
$bid_phases = System::Model('AssetDetail')->getEventAssetPhase($params);
$phases_array = array('BUYOUT','ENGLISH_AUCTION','SEALED');
$phase = array();

//Check to see which phases to show in the bid box
foreach($bid_phases as $key=>$value){
	if(in_array($value['BID_TYPE_NAME'],$phases_array)){
		$phase[$value['BID_TYPE_NAME']]['STATUS'] = 1;
		$phase[$value['BID_TYPE_NAME']]['START_DATE'] = date('YmdHis', strtotime($value['START_DATETIME']));
		$phase[$value['BID_TYPE_NAME']]['END_DATE'] = date('YmdHis', strtotime($value['END_DATETIME']));
	}
}

//Debug::dump($phase);

//TODO: Added this just to show bidding process by default if there are no bid phases set..for testing.Can remove it later
$phase['ENGLISH_AUCTION']['STATUS'] = 1;

$params['web_user_id']= $user_id;
$register_status = System::Model('AssetDetail')->getAssetRegisterStatus($params);

/**
 * Javascript Interval Value
 * asaset_bid_box.js reads this from the hidden field of the same name.
 */
$asset_auction_status_ping_interval = 1000;

//Debug::dump($detail['High_Bid']);

if(!empty($detail['event_asset_id'])){
?>
<div class="section-panel">

	<h2 class="ir auction-details">Auction Details</h2>

	<div class="section-panel-body">

		<input type="hidden" id="asset_auction_status_ping_interval" value="<?= $asset_auction_status_ping_interval ?>" />

		<form action="/ajax/dashboard/status_update/" method="get" id="asset-auction-status-form">
			<input type="hidden" name="event_asset_id[]" value="<?= $detail['event_asset_id'] ?>" />
		</form>

		<?php //if($phase['SEALED']['STATUS']==1 && $phase['SEALED']['END_DATE']< $start_date){
			if($phase['SEALED']['STATUS']==1){	?>
		<form action="/ajax/dashboard/pre_bid/" method="post" id="asset-auction-prebid-form">
			<table class="borderless vert-header">
				<tr>
					<td colspan="2" class="center">
						<div class="center">
							<?php if($register_status){?>
								<strong>You registered already.You can submit your Pre Bid.</strong>
							<?php }else { if( $user->isLogged() ) {?>
								<strong>To bid, please</strong>
								<a href="/registration/<?=$detail['event_asset_id']?>">Register for this auction</a>
							<?php }else{?>
								To Pre bid, please
								<a href="<?= __href('/account/login') ?>">login</a> then<br />
								<a href="#">register for this auction</a>
							<?php }?>
							<small>Have you completed the <a href="#" onclick="buyer_checklist()">buyers' checklist?</a></small>
							<?php }?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<div class="center font-14 bold color-red">
							PRE AUCTION BID
						</div>
					</td>
				</tr>
				<tr>
					<th>Starting Bid:</th>
					<td><span name="starting_bid" data="starting_bid"  class="font-18 color-ornage"><?= isset($detail['starting_bid']) ? __c($detail['starting_bid']) : '' ?></span></td>
				</tr>
<!--				<tr>
					<td colspan="2" class="center">
						<strong>Bidding not started </strong>
					</td>
				</tr>-->
				<tr>
					<td><span class="float-right font-18">Your Bid :</span></td>
					<td><span class="font-14 bold color-green">$<input type="text" name="prebid_amount" id="prebid_amount"  class="font-14 color-green bold bid-input" value="" /></span></td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<strong>Previously Valued to: <?= isset($asset_detail['asset_attribute']['Previou']) ? __c($asset_detail['asset_attribute']['Previou']) : ''  ?></strong>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center font-14 bold color-red" id="prebid_message" ></td>
				</tr>
				<tr>
					<td colspan="2" class="bid-button-container center">
						<?php if ($register_status) {?>
							<button id="prebid_button" type="submit" class="bid-button btn on" alt="Pre Auction Bid!">Pre-Auction Bid</button>
						<?	} else if($user->isLogged()){ ?>
							<em><a href="/registration/<?=$detail['event_asset_id']?>" class="btn on ">Please register to Pre-Bid.</a></em>
						<?php }else { ?>
							<em><a href="/account/login" class="btn on ">Please sign-in to Pre-Bid.</a></em>
						<?php } ?>
					</td>
				</tr>

<!--				<tr>
					<td colspan="2" class="center font-14 color-dark bold">Auction Starts in</td>
				</tr>

				<tr>
					<td colspan="2" class="center auction_countdown"><div id="auction_countdown"><?= isset($detail['start_datetime']) ? date('YmdHis', strtotime($detail['start_datetime'])) : ''; ?></div></td>
				</tr>-->
			</table>
			<input type="hidden"  name="event_asset_id" value="<?= $detail['event_asset_id'] ?>" />
			<input type="hidden"  name="bid_type" value="SEALED">
			<input type="hidden"  id="web_user" value="<?= $user_name ?>" />
		</form>
		<?php }?>
		<?php //if(($phase['ENGLISH_AUCTION']['STATUS'] == 1) || ($phase['SEALED']['STATUS']==1 && $start_date <= date('YmdHis')) ){
			if($phase['ENGLISH_AUCTION']['STATUS'] == 1){ ?>
		<form action="/ajax/dashboard/bid/" method="post" id="asset-auction-bidding-form">
			<table class="borderless vert-header">
				<tr>
					<td colspan="2" class="center">
						<div class="center">
							<?php if($register_status){?>
								<strong>You registered already.You can go ahead and Bid.</strong>
							<?php }else { if( $user->isLogged() ) {?>
								<strong>To bid, please</strong>
								<a href="/registration/<?=$detail['event_asset_id']?>">Register for this auction</a>
							<?php }else{?>
								To bid, please
								<a href="<?= __href('/account/login') ?>">login</a> then<br />
								<a href="#">register for this auction</a>
							<?php }?>
							<small>Have you completed the <a href="#" onclick="buyer_checklist()">buyers' checklist?</a></small>
							<?php }?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<div class="center font-14 bold color-red">
							ENGLISH AUCTION BID
						</div>
					</td>
				</tr>
				<tr>
					<th>Starting Bid:</th>
					<td><span name="starting_bid" data="starting_bid"  class="font-18 color-ornage"><?= isset($detail['starting_bid']) ? __c($detail['starting_bid']) : '' ?></span></td>
				</tr>
				<tr>
					<th>Bid Deposit:</th>
					<td></td>
				</tr>
				<tr>
					<th>Current Bid Amount:</th>
					<td><span name="curr_bid_amount" data="curr_bid_amount"><?= isset($detail['curr_bid_amount']) ? __c($detail['curr_bid_amount']) : '' ?></span></td>
				</tr>
				<?php if ( $start_date < date('YmdHis') && $end_date > date('YmdHis') ) {?>
				<tr>
					<th>Highest Bidder:</th>
					<td><span name="curr_bid_web_username" data="curr_bid_web_username"><?= isset($detail['curr_bid_web_username']) ? $detail['curr_bid_web_username'] : '' ?></span></td>
				</tr>
				<tr>
					<td colspan="2"><span name="High_Bid" data="High_Bid"><?=$detail['High_Bid']?></span></td>
				</tr>
				<?php } ?>
				<tr>
					<td colspan="2" class="center"><span class="font-14 bold color-red">Reserve Not Met</span></td>
				</tr>
				<?php if ( $start_date < date('YmdHis') && $end_date > date('YmdHis') ) {?>
				<tr>
					<td><span class="float-right font-18">Your Bid :</span></td>
					<td><span class="font-14 bold color-green">$<input type="text" name="bid_amount" id="bid_amount" data="next_bid_amount" class="font-14 color-green bold bid-input" value="<?= isset($detail['curr_bid_amount']) ? ($detail['curr_bid_amount'] + $detail['bid_increment']) : '' ?>" value="" /></span></td>
				</tr>
				<tr>
					<th class="right">Bid Increment:</th>
					<td><span name="bid_increment" data="bid_increment"><?= isset($detail['bid_increment']) ? __c($detail['bid_increment']) : '' ?></span></td>
				</tr>
				<tr>
					<td colspan="2" class="bid-button-container center">
						<?php if ($register_status) {
							if ( $end_date > date('YmdHis') ) { ?>
								<button id="bid_button" type="submit" class="bid-button btn on" alt="Bid Now!">Bid Now!</button>
							<?php }
							} else if($user->isLogged()){ ?>
							<em><a href="/registration/<?=$detail['event_asset_id']?>" class="btn on ">Please register to bid.</a></em>
						<?php }else { ?>
							<em><a href="/account/login" class="btn on ">Please sign-in to bid.</a></em>
						<?php } ?>
					</td>
				</tr>
				<?php } ?>
				<?php /*
				<tr>
					<td colspan="2"><b>Previously Valued to: <? //= number_format($detail['Previous_Value'])    ?></b></td>
				</tr>
				*/?>
				<?php
				if ( $end_date < date('YmdHis') ) {
					echo '<tr><td colspan="2" class="center auction_countdown">GONE!!!</td></tr>';
				} else if ( $start_date < date('YmdHis') && $end_date > date('YmdHis') ) {
					echo '<tr><td colspan="2" class="center font-14 color-dark bold">Auction Ends in</td></tr>';
					?>
					<tr>
						<td colspan="2" class="center auction_countdown"><div id="countdown" ><?= isset($detail['end_datetime_revised']) ? date('YmdHis', strtotime($detail['end_datetime_revised'])) : ''; ?></div></td>
					</tr>
					<?php
				} else if ( $start_date > date('YmdHis') ) {
					echo '<tr><td colspan="2" class="center"><a href="#"><img src="'.  __static("/img/assets/common/preSale-bidNow_online.gif").'" /></a></td></tr>';
					echo '<tr><td colspan="2" class="center font-14 color-dark bold">Auction Starts in</td></tr>';
					?>
					<tr>
						<td colspan="2" class="center auction_countdown"><div id="countdown" ><?= isset($detail['end_datetime_revised']) ? date('YmdHis', strtotime($detail['end_datetime_revised'])) : ''; ?></div></td>
					</tr>
				<?php } ?>


			</table>
			<?php /*if ( $end_date > date('YmdHis') ) { ?>
				<div>
					<p class="center">
						<span><strong>Online Event Details</strong></span><br>
						<span>Bidding starts: <strong> <?= date('m/d/Y', strtotime($detail['start_datetime'])) ?> </strong>End: <strong ><?= date('m/d/Y', strtotime($detail['end_datetime_revised'])) ?></strong></span>
					</p>
				</div>
			<?php } */?>
			<input type="hidden"  name="event_asset_id" value="<?= $detail['event_asset_id'] ?>" />
			<input type="hidden"  name="bid_type" value="ENGLISH_AUCTION">
			<?php /*
			<input type="hidden"  name="asset_id" value="<?= $detail['asset_id'] ?>" />
			<input type="hidden"  name="event_id" value="<?= $detail['event_id'] ?>" />
			 */?>
			<input type="hidden"  id="web_user" value="<?= $user_name ?>" />
		</form>
		<?php } ?>
		<?php //if($phase['SEALED']['STATUS']==1 &&  $start_date > $phase['SEALED']['END_DATE']){
			 if($phase['SEALED']['STATUS']==1){?>
		<form action="/ajax/dashboard/make_offer/" method="post" id="asset-auction-makeoffer-form">
			<table class="borderless vert-header">
				<tr>
					<td colspan="2" class="center">
						<div class="center">
							<?php if($register_status){?>
								<strong>You registered already.You can make an offer.</strong>
							<?php }else { if( $user->isLogged() ) {?>
								<strong>To Make an Offer, please</strong>
								<a href="/registration/<?=$detail['event_asset_id']?>">Register for this auction</a>
							<?php }else{?>
								To bid, please
								<a href="<?= __href('/account/login') ?>">login</a> then<br />
								<a href="#">register for this auction</a>
							<?php }?>
							<small>Have you completed the <a href="#" onclick="buyer_checklist()">buyers' checklist?</a></small>
							<?php }?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<div class="center font-14 bold color-red">
							MAKE AN OFFER
						</div>
					</td>
				</tr>
				<tr>
					<td><span class="float-right font-18">Your Offer :</span></td>
					<td><span class="font-14 bold color-green">$<input type="text" name="offer_amount" id="offer_amount"  class="font-14 color-green bold bid-input" value="" /></span></td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<strong>Previously Valued to:  <?= isset($asset_detail['asset_attribute']['Previou']) ? __c($asset_detail['asset_attribute']['Previou']) : ''  ?></strong>
					</td>
				</tr>

				<tr>
					<td colspan="2" class="center font-14 bold color-red" id="makeoffer_message" ></td>
				</tr>

				<tr>
					<td colspan="2" class="bid-button-container center">
						<?php if ($register_status) { ?>
						<button  id="makeoffer_button" type="submit" class="bid-button btn on" alt="Make Offer!">Make an Offer</button>
						<?php } else if($user->isLogged()){?>
						<em><a href="/registration/<?=$detail['event_asset_id']?>" class="btn on ">Please register to Make an Offer.</a></em>
						<?php }else{ ?>
						<em><a href="/account/login" class="btn on ">Please sign-in to Make an Offer.</a></em>
						<?php }?>
					</td>
				</tr>
			</table>
			<input type="hidden"  name="event_asset_id" value="<?= $detail['event_asset_id'] ?>" />
			<input type="hidden"  name="bid_type" value="SEALED">
			<input type="hidden"  id="web_user" value="<?= $user_name ?>" />
		</form>
		<?php }?>
		<?php
		//if(($phase['BUYOUT']['STATUS'] == 1) && ($phase['BUYOUT']['START_DATE'] <= date('YmdHis')) && ($phase['BUYOUT']['END_DATE'] > date('YmdHis'))){
			if($phase['BUYOUT']['STATUS'] == 1){?>
		<form action="/ajax/dashboard/buy_out/" method="post" id="asset-auction-buyout-form">
			<table class="borderless vert-header">
				<tr>
					<td colspan="2" class="center">
						<div class="center">
							<?php if($register_status){?>
								<strong>You registered already.You can buy now.</strong>
							<?php }else { if( $user->isLogged() ) {?>
								<strong>To buy, please</strong>
								<a href="/registration/<?=$detail['event_asset_id']?>">Register for this auction</a>
							<?php }else{?>
								To bid, please
								<a href="<?= __href('/account/login') ?>">login</a> then<br />
								<a href="#">register for this auction</a>
							<?php }?>
							<small>Have you completed the <a href="#" onclick="buyer_checklist()">buyers' checklist?</a></small>
							<?php }?>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center">
						<div class="center font-14 bold color-red">
							<strong>BUY NOW</strong>
						</div>
					</td>
				</tr>
				<tr>
					<th>Price:</th>
					<td><span name="buy_price" data="buy_price"  class="font-18 color-ornage"><?= isset($detail['reserve_price']) ? __c($detail['reserve_price']) : '' ?></span></td>
				</tr>
				<tr>
					<td colspan="2">
						<div class="center">
							<strong>If you opt to Buy Now auction ends. </strong>
						</div>
					</td>
				</tr>
				<tr>
					<td colspan="2" class="center font-14 bold color-red" id="buyout_message" ></td>
				</tr>
				<tr>
					<td colspan="2" class="bid-button-container center">
						<?php if ($register_status) {
							if ( $end_date > date('YmdHis') ) { ?>
								<button id="buyout_button" type="submit" class="bid-button btn on" alt="Buy Now!">Buy Now!</button>
							<?php }
							} else if($user->isLogged()){ ?>
							<em><a href="/registration/<?=$detail['event_asset_id']?>" class="btn on ">Please register to Buy.</a></em>
						<?php }else { ?>
							<em><a href="/account/login" class="btn on ">Please sign-in to Buy.</a></em>
						<?php } ?>
					</td>
				</tr>
			</table>
			<input type="hidden"  name="event_asset_id" value="<?= $detail['event_asset_id'] ?>" />
			<input type="hidden"  name="bid_type" value="BUYOUT">
			<input type="hidden"  id="web_user" value="<?= $user_name ?>" />
		</form>
		<?php }?>
	</div>
	<div class="footer center">
		<a href="#" class="button">Save to Dashboard</a><br />
		<a href="#">Event Details</a>
	</div>



</div>
<?php }?>
