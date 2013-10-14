<?php
	$this->queue('js', array('src' => '/js/jquery/jquery-1.6.2.js'));

	$this->queue('js', array('src' => '/js/jquery/plugins/jquery.event.drag-2.0.min.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/jquery.event.drop-2.0.min.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/jquery.cookie.js'));

	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.columnpicker.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.editors.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.grid.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.model.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.pager.js'));
	$this->queue('js', array('src' => '/js/jquery/plugins/slickgrid/slick.remotemodel.js'));

	$this->queue('css', array('src' => '/js/jquery/plugins/slickgrid/slick.grid.css'));
	$this->queue('css', array('src' => '/js/jquery/plugins/slickgrid/slick.columnpicker.css'));
	$this->queue('css', array('src' => '/js/jquery/plugins/slickgrid/slick.pager.css'));

	$this->queue('js', array('src' => '/js/widgets/portal/qualdashboard/afw.watchlist.slick.contextmenu.js'));
	$this->queue('js', array('src' => '/js/widgets/portal/qualdashboard/qualdashboard.slick.editors.js'));
	$this->queue('js', array('src' => '/js/widgets/portal/qualdashboard/qualdashboard.js'));

	$this->queue('js', array('src' => '/js/json2.js'));

	$this->queue('css', array('src' => '/css/widgets/portal/qualdashboard/qualdashboard.css'));
?>

<script type="text/javascript">
$('document').ready(function(){

	var eventIds = [<?php echo implode(',',System::Datasphere()->watchlist['events']); ?>];
	window.AfwQualDashboard = new AFW.Widget.QualDashboard.Dashboard(
		$("#auction-qualdashboard-grid"),
		<?php echo System::UserAuth()->webauth['web_user_id'];?>,
		"<?php echo System::UserAuth()->webauth['web_username'];?>",
		eventIds
	);
	window.AfwQualDashboard.loadAuctionFullDetail();
});
</script>

<div id="auction-qualdashboard-grid">

</div>

<button id="qualdashboard-btnAcknowledgeAllCurrentBids">Acknowledge all Current Bids</button>
