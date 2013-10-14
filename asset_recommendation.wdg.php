<?php

if(isset($this->vars['asset_id'])){

	$asset_params['ASSET_ID'] = $this->vars['asset_id'];
	$detail = System::Model('AssetDetail')->getAssetGlobalCorrelation($asset_params);// get global id from current asset id
	$like_params['ITEM_ID'] = $detail['GLOBAL_PROPERTY_ID'];

	$user_info = System::UserAuth()->getUser();// get user info for certona tracking
	$like_params['tracking_id'] = $user_info['session_index'];
	$like_params['session_id'] = $user_info['said'];

	$liked_products = System::Model('AssetDetail')->getAssetYouLike($like_params);// retrieve suggestion array based on global id
	//debug::dump($liked_products);
?>

<div class="also_like">
	<b class="span-7">You May Also Like</b><br/>

<?php

	if ( !empty($liked_products) ) {

		foreach ( $liked_products->product as $key => $val_array ) {

			$val_array = get_object_vars($val_array);

			$global_params['GLOBAL_PROPERTY_ID'] = $val_array['Item_ID'];
			$detail = System::Model('AssetDetail')->getAssetGlobalCorrelation($global_params);// get recommended asset id from global id

			if( !empty($detail) ) {
				$detail_params['asset_id'] = $detail['ASSET_ID'];
				$asset_detail = array_merge(System::Model('AssetDetail')->assetDetail($detail_params), System::Model('AssetDetail')->getEventAsset($detail_params)); // get asset detail
				//debug::dump($asset_detail);exit;
?>

			<span>
				<a href="/real-estate/auction/<?= isset($asset_detail['asset_id']) ? $asset_detail['asset_id'] : '' ?>"><img src="<?= !empty($asset_detail['images'][0]) ? $asset_detail['images'][0] :  __static('/img/assets/common/no-image.jpg') ?>" /></a>
				<!--<p><?= isset($asset_detail['product_title']) ? substr($asset_detail['product_title'], 0, 20) . '...' : '' ?></p>-->
				<p><?= isset($asset_detail['product_attribute']['City']) ? strtoupper($asset_detail['product_attribute']['City'].', '.$asset_detail['product_attribute']['State']) : '' ?></p>
				<p>Starting Bid: <?= isset($asset_detail['starting_bid'])? __d($asset_detail['starting_bid']):'' ?></p>
				<p><a href="/real-estate/auction/<?= isset($asset_detail['asset_id']) ? $asset_detail['asset_id'] : '' ?>"><img src="<?= __static('/img/more.png') ?>" alt="MORE" class="more-info-img" /></a></p>
			</span>

<?php
			}/* else {
?>

			<span>
				<a href="#"><img src="<?= __static('/img/assets/common/no-image.jpg') ?>" /></a>
				<p>TITLE</p>
				<p>Starting Bid:</p>
				<p><a href="#"><img src="<?= __static('/img/more.png') ?>" alt="MORE" class="more-info-img" /></a></p>
			</span>

<?php
			}*/
		}
	} else {
		for($i=0;$i<4;$i++) {
?>

		<span>
			<a href="#"><img src="<?= __static('/img/assets/common/no-image.jpg') ?>" /></a>
			<p>Title</p>
			<p>Starting Bid :</p>
			<p><a href="#"><img src="<?= __static('/img/more.png') ?>" alt="MORE" class="more-info-img" /></a></p>
		</span>

<?php	}
	} ?>

</div>

<?php } ?>
