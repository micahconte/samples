(function($) {
    /***
     *
     */
    function Dashboard(container,userId,username,eventIds) {
        var self = this;
        var grid;
        var container = container;

        var dummyProperty = "dp";

        var filters = {
        	eventId:"",
        	status: "",
        	venue: "",
        	seller: ""
        };

        var dataRefreshInterval;
        var currentBidCellsHighlight = {};

        //private properties
        this.eventIds = eventIds;
        this.dataView;
        this.columnPicker;
        this.contextMenu;
        this.$bidderHistoryDialog;
        this.ajaxBidderHistory;
        this.currentBidderHistoryId;
        this.userId = userId;
        this.username = username;
        this.formattersAndEditors = new AFW.Widget.QualDashboard.Editor();


				init();

        // private methods
        function init()
        {
        	initGrid();
        	initGridAndDataViewEvents();

        	initButtons();

        	renderInterval = setInterval(renderEverySecond,1000);
        }

				function initGrid()
				{
					var columns = [
						{
							id: "eventId",
							name: "Event",
							field: "event_id",
							sortable:true
						},
						{
							id: "assetId",
							name: "Asset ID",
							field: "asset_id",
							sortable:true
						},
						{
							id: "auctionId",
							name: "Event Asset ID",
							field: "event_asset_id",
							sortable:true
						},
						{
							id: "seller",
							name: "Seller",
							field: "sellername",
							sortable:true
						},
						{
							id: "expiresDisplay",
							name: "Remaining Time",
							width: 140,
							field: "end_datetime_revised",
							formatter: self.formattersAndEditors.RemainingTimeFormatter,
							cssClass: "remainingTime",
							sortable:true
						},
						{
							id: "highestBidder",
							name: "Highest Bidder",
							formatter: self.formattersAndEditors.HighestBidFormatter,
							cssClass: "qualdashboard-position-rel",
							width: 140,
							field: "curr_bid_web_username",
							sortable:true
						},
						{
							id: "status",
							name: "Auction Day Status",
							field: "status",
							width:120,
							sortable:true
						},
						{
							id: "currentBid",
							name: "Current Bid($)",
							width: 120,
							formatter: self.formattersAndEditors.CurrentBidFormatter,
							cssClass: "qualdashboard-position-rel",
							field: "curr_bid_amount",
							sortable:true
						},
						{
							id: "nextBid",
							name: "Next Bid",
							field: "nextBid",
							width: 120,
							cssClass: "qualdashboard-canedit qualdashboard-position-rel",
							formatter: self.formattersAndEditors.NextBidPersistantFormatter,
							editor: self.formattersAndEditors.NextBidEditor,
							editable: true,
							sortable:true,
							requireConfirm: true
						},
						{
							id: "reserve",
							name: "Reserve",
							field: "reserve_price",
							cssClass: "qualdashboard-canedit",
							formatter: self.formattersAndEditors.ParseIntFormatter,
							width: 120,
							editor: self.formattersAndEditors.ReserveEditor,
							editable: true,
							sortable:true,
							requireConfirm: true
						},
						{
							id: "stReservePercent",
							name: "ST Reserve %",
							field: "stReservePercent",
							cssClass: "qualdashboard-canedit",
							editor: self.formattersAndEditors.SubjectToReservePercentEditor,
							formatter: self.formattersAndEditors.SubjectToReservePercentFormatter,
							editable: true,
							sortable:true,
							requireConfirm: true
						},
						{
							id: "stReserveValue",
							name: "ST Reserve Value",
							field: "subject_to_price",
							cssClass: "qualdashboard-canedit",
							formatter: self.formattersAndEditors.ParseIntFormatter,
							editor: self.formattersAndEditors.StReserveValueEditor,
							width:120,
							editable: true,
							sortable:true,
							requireConfirm: true
						},
						{
							id: "bidIncrement",
							name: "Bid Increment",
							field: "bid_increment",
							cssClass: "qualdashboard-canedit",
							formatter: self.formattersAndEditors.ParseIntFormatter,
							editor: self.formattersAndEditors.BidIncrementEditor,
							minWidth:100,
							editable: true,
							sortable:true,
							requireConfirm: true
						},
						{
							id: "handoff",
							name: "Handoff",
							field: "auctioneer_web_username",
							cssClass: "qualdashboard-canedit",
							editor: self.formattersAndEditors.HandoffEditor,
							width: 170,
							minWidth:170,
							editable: true,
							sortable:true,
							userId: self.userId,
							username: self.username,
							requireConfirm: true
						},
						{
							id: "address",
							name: "Address",
							field: "address",
							sortable:true
						},
						{
							id: "city",
							name: "City",
							field: "city",
							sortable:true
						},
						{
							id: "state",
							name: "State",
							field: "state",
							sortable:true
						}
					];

					var cookieSettings = {
						key : "qualdashboard-column-options"
					}

					var options = {
						editable: true,
						autoEdit: true,
						enableAddRow: false,
						autoHeight: true,
						enableCellNavigation: true,
						defaultColumnWidth: 100,
						rowHeight: 32,
						enableColumnReorder: true,
						enableAsyncPostRender: false,
						syncColumnCellResize: true,
						cellHighlightCssClass: "qualdashboard-highlight",
						enableAutoTooltips: false,
						cookie : cookieSettings
					};

					var columnOptions = {
						cookie : cookieSettings
					}

					//self.dataView = new Slick.Data.QualDataView();
					self.dataView = new Slick.Data.DataView();
					grid = new Slick.Grid(container, self.dataView.rows, columns, options);
					self.columnPicker = new Slick.Controls.ColumnPicker(columns, grid, columnOptions);
					self.contextMenu = new AFW.Widget.QualDashboard.ContextMenu(columns,grid,{formatterFactory: self.formattersAndEditors});

					//TODO: test for JSON and $.cookie)
					//Take columns from cookie and populate grid accordingly
					if($.cookie(columnOptions.cookie.key))
					{
						function findColumnById(id)
						{
							for(var i=0, c = columns.length; i<c; i++)
							{
								if(columns[i].id == id)
									return columns[i];
							}
						}

						var userColumns = JSON.parse($.cookie(columnOptions.cookie.key));
						var userCol, origColumn;
						for(var i=0,c=userColumns.length; i < c; i++)
						{
							userCol = userColumns[i];
							origColumn = findColumnById(userCol.id);

							if(origColumn.formatter != undefined)
							{
								userCol.formatter = origColumn.formatter;
							}

							if(origColumn.editor != undefined)
							{
								userCol.editor = origColumn.editor;
							}
						}
						grid.setColumns(userColumns);
					}

					self.dataView.beginUpdate();
					self.dataView.setFilter(inlineFilter);
					self.dataView.endUpdate();
				}

				function initGridAndDataViewEvents()
				{
					grid.onColumnsReordered = function(){
						grid.resetCurrentCell();
					}

					grid.onSort = function(sortCol, sortAsc) {
						grid.resetCurrentCell();

						var sortcol = sortCol.field;
						self.dataView.fastSort(sortcol,sortAsc);
					}

					grid.onSelectedRowsChanged = function(){
						var selectedRows = grid.getSelectedRows();
						var item = self.dataView.getItemByIdx(selectedRows[0]);

						var event = $.Event("AfwQualDashboardAuctionSelected");
						event.item = item;
						container.trigger(event);
					}

					grid.onValidationError = function(currentCellNode, validationResults, currentRow, currentCell, column){
						alert(validationResults.msg);
					}

					grid.onCurrentCellChanged =  function(args){
						var row = args.row;
						var col = args.cell;

						//check to see if current bid cell is clicked
						var currentBidColumnIndex = grid.getColumnIndex("currentBid");
						if(col == currentBidColumnIndex)
						{
							grid.removeHighlightCell(row,col);
						}

					}

					grid.onClick = function(e, row, cell){
						var $target = $(e.target);
						var item = self.dataView.getItemByIdx(row);

						if($target.is(".qualdashboard-button.bid"))
						{
							grid.gotoCell(row,cell);
							grid.editCurrentCell();
							grid.getCellEditor().makeBid();
							return true;
						}

						//check auction status so as to not render nextbid editor
						if($target.is(".qualdashboard-canedit") && !(item.status.toLowerCase() == "active" || item.status.toLowerCase() == "open"))
						{
							grid.setSelectedRows([row]);
							return true;
						}
					}

					grid.onDblClick = function(e,row,cell){
						var item = self.dataView.getItemByIdx(row);
						var $target = $(e.target);
						
						//check auction status so as to not render nextbid editor
						if($target.is(".qualdashboard-canedit") && !(item.status.toLowerCase() == "active" || item.status.toLowerCase() == "open"))
						{
							grid.setSelectedRows([row]);
							return true;
						}
					}

					grid.onRowHover = function(e,row,cell,dataItem){
						var $cell = $(e.target);

						if(
							$cell.hasClass('qualdashboard-icon-magnify') &&
							(cell == grid.getColumnIndex("currentBid") || cell == grid.getColumnIndex("highestBidder")))
						{
							renderBidderHistoryDialog($cell,dataItem.event_asset_id);
						}
						else
						{
							if(self.ajaxBidderHistory)
							{
								self.ajaxBidderHistory.abort();
							}
							if(self.$bidderHistoryDialog)
							{
								self.$bidderHistoryDialog.hide();
								delete self.currentBidderHistoryId;
							}
						}

						return;
					}

					//handle trying to mouse into the dialog box
					container.bind("mouseleave",function(e){
						if(self.ajaxBidderHistory)
						{
							self.ajaxBidderHistory.abort();
						}
						if(self.$bidderHistoryDialog)
						{
							self.$bidderHistoryDialog.hide();
							delete self.currentBidderHistoryId;
						}
					});

					self.dataView.onRowCountChanged.subscribe(function(args) {
						grid.updateRowCount();
				    grid.render();
					});

					self.dataView.onRowsChanged.subscribe(function(rows) {
						grid.removeRows(rows);
						grid.render();
					});
				}

				function initButtons()
				{
					$('#qualdashboard-btnAcknowledgeAllCurrentBids').click(function(){

						var currentBidColumnIndex = grid.getColumnIndex("currentBid");
						var renderedRange = grid.getRenderedRange();
						for(var i = renderedRange.top, bottom = renderedRange.bottom; i <= bottom; i++)
						{
							grid.removeHighlightCell(i,currentBidColumnIndex,false);
						}

					});
				}

				function inlineFilter(item)
				{
					if(filters.eventId != "" && filters.eventId != item.eventId)
					{
						return false;
					}

					if(filters.seller != "" && filters.seller != item.seller)
					{
						return false;
					}

					if(filters.venue != "" && filters.venue != item.venue)
					{
						return false;
					}

					if(filters.status != "" && filters.status != item.status)
					{
						return false;
					}

					return true;
				}

				function simulateAuction()
				{
					var data = self.dataView.rows;
          var numberOfUpdates = Math.round(Math.random()*(data.length/5));
          var row;
          for (var i=0; i<numberOfUpdates; i++) {
              row = Math.round(Math.random()*(data.length-1));

              data[row]["highestBidder"] = self.dataView.getRandomDummyBidder();
              data[row]["currentBid"] += data[row]["bidIncrement"];
              data[row]["nextBid"] = data[row]["currentBid"] + data[row]["bidIncrement"];
							if(data[row]["currentBid"] >= data[row]["reserve"])
							{
								data[row]["status"] = "Reserve Met";
							}

              if (!currentBidCellsHighlight[row])
                  currentBidCellsHighlight[row] = {};

              currentBidCellsHighlight[row]["currentBid"] = true;

              //grid.removeRow(row);
              grid.highlightCell(row,grid.getColumnIndex("currentBid"),500);
          }
          //grid.setHighlightedCells(currentBidCellsHighlight);
          //grid.render();
				}

				function renderEverySecond()
				{
					//render countdown
					var remainingTimeColumnIndex = grid.getColumnIndex("expiresDisplay");
					/*
					var highestBidderColumnIndex = grid.getColumnIndex("highestBidder");
					var currentBidColumnIndex = grid.getColumnIndex("currentBid");
					var nextBidColumnIndex = grid.getColumnIndex("nextBid");
					var statusColumnIndex = grid.getColumnIndex("status");
					var bidIncrementColumnIndex = grid.getColumnIndex("bidIncrement");
					var handoffColumnIndex = grid.getColumnIndex("handoff");
					//*/

					var renderedRange = grid.getRenderedRange();
					for(var i = renderedRange.top, bottom = renderedRange.bottom; i <= bottom; i++)
					{
						grid.updateCell(i,remainingTimeColumnIndex);
						//grid.updateCell(i,highestBidderColumnIndex);
						//grid.updateCell(i,currentBidColumnIndex,false);
						//grid.updateCell(i,nextBidColumnIndex,false);
						//grid.updateCell(i,statusColumnIndex);
						//grid.updateCell(i,bidIncrementColumnIndex,false);
						//grid.updateCell(i,handoffColumnIndex,false);
					}
				}

				function loadQuickDetail()
				{
					var events = self.eventIds;
					var columns = [
						"bid_increment",
						"end_datetime_revised",
						"event_asset_id",
						"current_bid_amount",
						"reserve_price",
						"status",
						"auctioneer_web_user_id",
						"auctioneer_web_username"
					];

					$.ajax({
        		url:	"/ajax/dashboard/details/",
        		cache:	false,
        		type: "GET",
        		data:{
        			event_id: events,
        			column: columns
        		},
        		dataType : "json",
        		success:	onLoadQuickDetailSuccess,
        		error:	onLoadQuickDetailError,
        		complete: onLoadQuickDetailComplete
        	});

				}

				function onLoadQuickDetailSuccess(data, textStatus, jqXHR)
				{
					var items = data;
					var item,rowItem;

					var remainingTimeColumnIndex = grid.getColumnIndex("expiresDisplay");
					var highestBidderColumnIndex = grid.getColumnIndex("highestBidder");
					var currentBidColumnIndex = grid.getColumnIndex("currentBid");
					var nextBidColumnIndex = grid.getColumnIndex("nextBid");
					var statusColumnIndex = grid.getColumnIndex("status");
					var bidIncrementColumnIndex = grid.getColumnIndex("bidIncrement");
					var handoffColumnIndex = grid.getColumnIndex("handoff");
					var reserveColumnIndex = grid.getColumnIndex("reserve");

					var item,row;
					for(var i = 0, c = items.length; i < c; i++)
					{
						item = items[i];
						row = self.dataView.getRowById(item.event_asset_id);
						rowItem = self.dataView.getItemById(item.event_asset_id);

						rowItem.auctioneer_web_username	= item.auctioneer_web_username;
						rowItem.auctioneer_watch_status	= item.auctioneer_watch_status;

						if(rowItem.curr_bid_web_username != item.curr_bid_web_username)
						{
							rowItem.curr_bid_web_username = item.curr_bid_web_username;
							grid.updateCell(row,highestBidderColumnIndex);
						}

						if(rowItem.bid_increment != item.bid_increment)
						{
							rowItem.bid_increment = item.bid_increment;
							grid.updateCell(row,bidIncrementColumnIndex);
						}

						if(rowItem.reserve_price != item.reserve_price)
						{
							rowItem.reserve_price = item.reserve_price;
							grid.updateCell(row,reserveColumnIndex);
						}

						if(rowItem.status != item.status)
						{
							rowItem.status = item.status;
							grid.updateCell(row,statusColumnIndex);
						}

						if(rowItem.auctioneer_web_user_id	!= item.auctioneer_web_user_id)
						{
							rowItem.auctioneer_web_user_id = item.auctioneer_web_user_id;
							grid.updateCell(row,handoffColumnIndex);
						}

						if(rowItem.curr_bid_amount != item.curr_bid_amount)
						{
							rowItem.curr_bid_amount = item.curr_bid_amount;
							grid.highlightCell(self.dataView.getRowById(item.event_asset_id),grid.getColumnIndex("currentBid"),250);

							grid.updateCell(row,currentBidColumnIndex);
							grid.updateCell(row,nextBidColumnIndex);
						}

						if(rowItem.end_datetime_revised != item.end_datetime_revised)
						{
							rowItem.end_datetime_revised = item.end_datetime_revised;
							grid.flashCell(self.dataView.getRowById(item.event_asset_id),grid.getColumnIndex("expiresDisplay"));
						}

						//TODO: optimize below, especially else condition
						if(self.userId == item.auctioneer_web_user_id)
						{
							if(item.auctioneer_watch_status != 1)
							{
								grid.highlightCell(row,handoffColumnIndex,500);
							}
							else
							{
								grid.removeHighlightCell(row,handoffColumnIndex);
							}
						}
						else
						{
							if(item.auctioneer_watch_status != 1 && item.auctioneer_web_user_id)
							{
								grid.highlightCell(row,handoffColumnIndex);
							}
							else
							{
								grid.removeHighlightCell(row,handoffColumnIndex);
							}
						}

					}

					delete item;
					delete row;
				}

				function onLoadQuickDetailError(jqXHR, textStatus, errorThrown)
				{
				}

				function onLoadQuickDetailComplete()
				{
					setTimeout(loadQuickDetail,500)
					//loadQuickDetail();
				}

				function renderBidderHistoryDialog($cell,event_asset_id)
				{
					if(!self.$bidderHistoryDialog)
					{
						self.$bidderHistoryDialog = $('<div id="qualdashboard-bidderHistory"/>');
						$('body').append(self.$bidderHistoryDialog);

						self.$bidderHistoryDialog.css('position','absolute');
					}

					loadBidderHistoryDialog(event_asset_id,$cell);
				}

				function loadBidderHistoryDialog(event_asset_id,$cell)
				{
					var cellOffset,containerOffset,divTop;

					if(self.currentBidderHistoryId == event_asset_id)
					{
						return;
					}
					else
					{
						self.$bidderHistoryDialog.empty();
						var $loaderGif = $('<img class="qualdashboard-loading" width="1" height="1" src="{AFW:__static:/img/1x1.gif}"/>');
						self.$bidderHistoryDialog.append($loaderGif);

						cellOffset = $cell.offset();
						containerOffset = container.offset();
						divTop = cellOffset.top + self.$bidderHistoryDialog.outerHeight() + 20 > $(window).scrollTop() + $(window).height() ? $(window).scrollTop() + $(window).height() - self.$bidderHistoryDialog.outerHeight(): cellOffset.top + 20;

						self.$bidderHistoryDialog.css('top',divTop);
						self.$bidderHistoryDialog.css('left',cellOffset.left+$cell.width());
						self.$bidderHistoryDialog.show();
					}

					if(self.ajaxBidderHistory)
					{
						self.ajaxBidderHistory.abort();
					}

					self.currentBidderHistoryId = event_asset_id;

					self.ajaxBidderHistory = $.ajax({
						url: "/ajax/dashboard/bid_history/",
						type: "GET",
						cache: false,
						dataType: "json",
						data:{
							event_asset_id: event_asset_id
						},
						success: function(data){
							self.$bidderHistoryDialog.empty();

							var $recentTable = $('<table id="qualdashboard-bidderHistory-table" class="data-table"/>');
							var $recentTh = $('<tr><th>Bidder</th><th>Bid Amt.</th><th>Bid Time</th></tr>');

							var $uniqueTable = $('<table id="qualdashboard-uniqueBidderHistory-table" class="data-table"/>');
							var $uniqueTh = $('<tr><th>Bidder</th><th>Bid Amt.</th><th>Bid Time</th></tr>');

							$recentTable.append($recentTh);
							$uniqueTable.append($uniqueTh);

							self.$bidderHistoryDialog.append($('<h1>Recent Bids</h1>'));
							self.$bidderHistoryDialog.append($recentTable);
							self.$bidderHistoryDialog.append($('<h1>Unique Bidders</h1>'));
							self.$bidderHistoryDialog.append($uniqueTable);

							var recent;
							for(var i=0, c = data.recent.length; i < c; i++)
							{
								recent = data.recent[i];

								var $tr = $('<tr class="qualdashboard-bidderHistory-bidderRow"/>');
								$tr.addClass("recency"+data.recent[i].recency);
								var $tdBidder = $('<td/>'), $tdBidAmt = $('<td/>'), $tdBidDate = $('<td/>');

								$tdBidder.text(recent.web_username);
								$tdBidAmt.text('$' + recent.bid_amount);
								$tdBidDate.text(recent.created_datetime);

								$tr.append($tdBidder);
								$tr.append($tdBidAmt);
								$tr.append($tdBidDate);

								$recentTable.append($tr);
							}

							var unique;
							for(var i=0, c = data.unique.length; i < c; i++)
							{
								unique = data.unique[i];

								var $tr = $('<tr class="qualdashboard-bidderHistory-bidderRow"/>');
								var $tdBidder = $('<td/>'), $tdBidAmt = $('<td/>'), $tdBidDate = $('<td/>');

								$tdBidder.text(unique.web_username);
								$tdBidAmt.text('$' + unique.bid_amount);
								$tdBidDate.text(unique.bid_datetime);

								$tr.append($tdBidder);
								$tr.append($tdBidAmt);
								$tr.append($tdBidDate);

								$uniqueTable.append($tr);
							}

							cellOffset = $cell.offset();
							containerOffset = container.offset();
							divTop = cellOffset.top + self.$bidderHistoryDialog.outerHeight() + 20 > $(window).scrollTop() + $(window).height() ? $(window).scrollTop() + $(window).height() - self.$bidderHistoryDialog.outerHeight(): cellOffset.top + 20;

							self.$bidderHistoryDialog.css('top',divTop);
							self.$bidderHistoryDialog.css('left',cellOffset.left+$cell.width());
							self.$bidderHistoryDialog.show();
							//*/
						}
					});
				}

				//public methods
				function loadAuctionFullDetail(auctionId)
				{
        	$.ajax({
        		url:	"/ajax/dashboard/details/",
        		cache:	false,
        		type: "GET",
        		data:{
        			event_id: self.eventIds
        		},
        		dataType : "json",
        		success:	onLoadFullDetailSuccess,
        		error:	onLoadFullDetailError,
        		complete: onLoadFullDetailComplete
        	});
        	//*/

        	//self.dataView.loadRemoteData();
        }

        function applyFilters(myFilters)
        {
        	filters.eventId = myFilters.eventId;
        	filters.status = myFilters.status;
        	filters.venue = myFilters.venue;
        	filters.seller = myFilters.seller;

        	self.dataView.beginUpdate();
					self.dataView.setFilter(inlineFilter);
					self.dataView.endUpdate();
        }

				//event handler
				function onLoadFullDetailSuccess(data, textStatus, jqXHR)
				{
					var items = data;
					self.dataView.setItems(items,"event_asset_id");

					//setInterval(loadQuickDetail,1000);
					loadQuickDetail();
				}

				function onLoadFullDetailError(jqXHR, textStatus, errorThrown)
				{
				}

				function onLoadFullDetailComplete(jqXHR, textStatus)
				{

				}

        return {
            // properties
            "dummyProperty":  dummyProperty,  // note: neither the array or the data in it should be modified directly
            "container":			container,
						"grid":						grid,

            // methods
            "loadAuctionFullDetail": 		loadAuctionFullDetail,
            "applyFilters": 	applyFilters,

            // events
            "onDataLoaded":		null
        };
    }

    // AFW.Widget.AuctionSearch
    $.extend(true, window, {
    	AFW: {
    		Widget: {
    			QualDashboard: {
    				Dashboard : Dashboard
    			}
    		}
    	}
    });
})(jQuery);
