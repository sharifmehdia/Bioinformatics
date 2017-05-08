<div class="jumbotron lazy">
    <div class="container">        
    <a href="<?php echo base_url()?>"><img src="<?php echo base_url('theme/img/miRLiN_Logo.svg')?>"></a>
        <button type="button" class="btn btn-default top_info" aria-label="Right Align" data-toggle="modal" data-target="#myModal">
            <span class="glyphicon glyphicon-info-sign" aria-hidden="true"></span>
        </button>
        <!-- Modal -->
        <div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">miRLiN Help</h4>
                    </div>

<div class="modal-body">
                        <u>Search Box</u>
                        <br>
                        <br> The search box accepts a list of terms or miRNAs or a combination of both terms and miRNAs. The list can be delimited by newline, comma, semi-colon etc. There is limit of 50 search box entities. The miRNAs should use the prefix ‘hsa-mir-’ before the miRNA number, e.g., hsa-mir-523. The button ‘Rank miRNAs’, will output a prioritized list of related miRNAs having the closest semantic associations to the search box query. The button ‘Rank miRNAs &amp; Terms’, will output a prioritized list of closely related terms in addition to related miRNAs. This second option will only work if the query consists of only miRNAs.

                        <br>
                        <br>

                        <u>Top Left Panel: Ranked miRNAs (and terms)</u>
                        <br>
                        <br> The output of the tool is a ranked list of miRNAs (or ranked lists of both miRNAs and terms) based on the degree of association (cosine value) to the query. The top 50 miRNAs (and top 300 terms) are displayed. There is a check box next to each ranked entity that can be used to add them to the network graph. The top 5 entities are preselected.

                        <br>
                        <br>

                        <u>Top Right Panel: Network Graph</u>
                        <br>
                        <br> Selected miRNAs (and terms) can be visualized as a network graph, where the nodes represent either a miRNA or a term and the edges represent cosine values above 0.4. miRNAs are depicted by red ellipses and terms are represented by yellow rectangles. A single node can be selected by clicking on it. Multiple nodes can be selected by keeping Ctrl key pressed and clicking on the desired nodes. ‘Render Abstracts’ button will display abstracts relevant to the nodes.

                        <br>
                        <br>

                        <u>Bottom Panel: Abstracts</u>
                        <br>
                        <br> Multiple nodes can be selected from the graph display to retrieve their shared abstracts, if applicable. The abstracts are displayed with the selected terms and miRNAs highlighted for convenience.

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-danger" data-dismiss="modal">Close</button>

                    </div>
                </div>
            </div>
        </div>
        <h1><span class="red_text">miR</span>NA <span class="red_text">Li</span>terature <span class="red_text">N</span>etwork</h1>
        <h3> Enter search term or delimited list of search terms below </h3>
        <form autocomplete="off" id="search_form" method="get" action="<?=site_url('welcome/dot_product') ?>">
            <input autocomplete="off" id="list" name="list" required type="text" class="form-control search_box" placeholder="Enter Search Term" value="<?=$this->input->get('list'); ?>"> 
            <div id="result"></div>
            <div class="submits">
                <button type="submit" id="rank_search_btn" name="search_in" value="rank" class="btn_submit_left btn btn-danger btn-lg btn-block"> <span class="glyphicon glyphicon-link" aria-hidden="true"></span>Rank miRNAs </button>
                <button type="submit" id="rank_term_search_btn" name="search_in" value="term" class="btn_submit_right btn btn-danger btn-lg btn-block"> <span class="glyphicon glyphicon-link" aria-hidden="true"></span>Rank miRNAs and Terms </button>
            </div>
        </form>



    </div>
</div>
<?php if(isset($result) && !$result):?>
	<h1 class="text-center">No keyword found !</h1>

<?php elseif(isset($result) && !isset($term_result)):?>
    <div class="container">
	
	
		<?php 
		$isset_list_vectors = (isset($result['list_vectors']) && $result['list_vectors'] );
		$isset_term_vectors = (isset($result['term_vectors']) && $result['term_vectors']);
		if( $isset_list_vectors || $isset_term_vectors ):?>
		<div class="row">
			
						
			<div class="col-md-12">
				<button class="btn btn-primary" type="button" id="show_hide">Show/Hide Search Entity Panel</button>
				<br /> &nbsp; <br /> 
			</div>
			
			
			<div class="col-md-12" style="margin-bottom:20px; padding:20px; border:1px solid silver" id="top_panel">
				<?php if($isset_list_vectors):?>
					<div class="col-md-6">
					<h4><?=count($result['list_vectors'])?> miRNA</h4>
					<?php $i=0; foreach($result['list_vectors'] as $list_vector): $i++?>
						<input type="checkbox" value="<?=$list_vector?>" data-type="rank" class="vectors_names" <?php if($i < 4) echo 'checked'?> /> <?=$list_vector?> <br />
					<?php endforeach?>
					</div>
				<?php endif?>
				
				<?php if($isset_term_vectors):?>
					<div class="col-md-6">
					<h4><?=count($result['term_vectors'])?> Terms</h4>
					<?php $i=0; foreach($result['term_vectors'] as $term_vector): $i++?>
						<input type="checkbox" value="<?=$term_vector?>" data-type="term" class="vectors_names" <?php if($i < 4) echo 'checked'?> /> <?=$term_vector?> <br />
					<?php endforeach?>
					</div>
				<?php endif?>
			</div>
			
		</div> 
		
			
		<?php endif;?>
		
		
		
                    <div class="row">
                        <div class="col-md-6" style="height: 450px; overflow-y: auto;">
                                                    <div class="border">
                        <h2 class=title_bold> Search Result </h2>
                       
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="title_result">miRNA</div>
                                        </th>
                                        <th>
                                            <div class="title_result">Score</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach($result['result'] as $name => $dot): $i++;?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" data-dot="<?php echo $dot?>" class="list_names" value="<?php echo $name?>" <?php if($i < 6) echo 'checked'?> />
                                                <a target="_blank" href="http://www.mirbase.org/cgi-bin/mirna_entry.pl?acc=<?=$result['mi_result'][$name]?>">
                                                    <?php echo $name?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo round($dot, 5)?>
                                            </td>

                                        </tr>
                                        <?php endforeach?>
                                </tbody>
                            </table>
                        
                        </div>
                        </div>

                        <div class="col-md-6" style="height: 450px">
                                            <h2 class=title_bold> Network</h2>

                            <div id="cytoscapeweb">
                                
                            </div>
                            <div class="connection_range">
                                <p class="range_text">Range Value: <span id="range_value">0.4</span></p>
                                <p>
                                    <input type="range" id="connection_range" name="connection_range" min="0.1" max="0.9" step="0.1" value="0.4" />
                                </p>
                            </div>
                        </div>
                        <div class="col-md-12">
                            <br>
                            <button type="button" class="btn btn-primary export_result" data-type="lists">Download CSV</button>
                        </div>

                    </div>

        <div class="row" style="margin-top: 80px">
            <div class="col-md-12">
                <div id="note">
                    <p>Abstracts</p>
                </div>
            </div>
        </div>

    </div>
		

<?php elseif(isset($term_result)):?>
    <div class="container">

<div class="row">
                  <div class="col-md-12">
                    <div class="col-md-6" style="height: 450px; overflow-y: auto;">
                        <div class="border">
                        <h2 class=title_bold> Search Result </h2>
                        <div class="col-md-6">
                        
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="title_result">miRNA</div>
                                        </th>
                                        <th>
                                            <div class="title_result">Score</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach($result['result'] as $name => $dot): $i++;?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" data-dot="<?php echo $dot?>" class="list_names" value="<?php echo $name?>" <?php if($i < 6) echo 'checked'?> />
                                                <a target="_blank" href="http://www.mirbase.org/cgi-bin/mirna_entry.pl?acc=<?=$result['mi_result'][$name]?>">
                                                    <?php echo $name?>
                                                </a>
                                            </td>
                                            <td>
                                                <?php echo round($dot, 5)?>
                                            </td>

                                        </tr>
                                        <?php endforeach?>
                                </tbody>
                            </table>
                        </div>
                        </div>

                        <div class="col-md-6 ">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>
                                            <div class="title_result">Terms</div>
                                        </th>
                                        <th>
                                            <div class="title_result">Score</div>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $i=0; foreach($term_result as $name => $dot): $i++;?>
                                        <tr>
                                            <td>
                                                <input type="checkbox" data-dot="<?php echo $dot?>" class="term_names" value="<?php echo $name?>" <?php if($i < 6) echo 'checked'?> />
                                                <?php echo $name?>

                                            </td>
                                            <td>
                                                <?php echo round($dot, 5)?>
                                            </td>

                                        </tr>
                                        <?php endforeach?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="col-md-6" style="height: 450px">
                    <h2 class=title_bold> Network</h2>
                        <div id="cytoscapeweb" style="border:3px solid #135665">
                            
                        </div>
                        <div class="connection_range">
                            <p class="range_text">Range Value: <span id="range_value">0.4</span></p>
                            <p>
                                <input type="range" id="connection_range" name="connection_range" min="0.1" max="0.9" step="0.1" value="0.4" />
                            </p>
                        </div>
                    </div>

                    <div class="col-md-12">
                        <br/>
                        <button type="button" class="btn btn-primary export_result" data-type="lists_terms">Download CSV</button>
                    </div>

                </div>

</div>
        <div class="row" style="margin-top: 80px">
            <div class="col-md-12">
                <div id="note">
                    <p>Abstracts</p>
                </div>
            </div>
        </div>

    </div>
<?php endif?>
<form method="post" action="<?php echo site_url('welcome/export_csv')?>" target="_blank">
	<input type="hidden" name="export_data"  id="export_data"/>
</form>
<script type="text/javascript">
	var nodes_connections = [];
	var node_names = [];
    window.onload = function() {

	
		$('#show_hide').click(function() {
			$('#top_panel').toggle();
		});
		
		function draw_nodes(first_time) {
			if(first_time) {
				$('.list_names:first').trigger('change');
				return false;
			}
			
			term_nodes = [];
			
			init_vars();
			
			
			
			var visual_style = {
				global: {
					backgroundColor: "#FFFFFF",
					
				},
				nodes: {
					shape: "ELLIPSE",
					borderWidth: 1,
					borderColor: "#ffffff",
					labelFontColor :"#ffffff",
					labelFontWeight : "bold", 
					width: {
						defaultValue: 120,
					},
					height: {
						defaultValue: 30,
					},
					color: {
						defaultValue: "#9A0B0B",
						discreteMapper: {
							attrName: "id",
							entries: term_nodes
						}
					},
					labelHorizontalAnchor: "center"
				},
				edges: {
					width: 3,
					color:  {
						defaultValue: "#0B94B1",
						discreteMapper: {
							attrName: "id",
							entries: connection_colors
						}
					}
				}
			};
			
			network_json = {
				// you need to specify a data schema for custom attributes!
				dataSchema: {
					nodes: [ { name: "label", type: "string" },
					],
					edges: [ { name: "label", type: "string" },
					]
				},
				// NOTE the custom attributes on nodes and edges
				data: {
					nodes: nodes,
					edges: edges
				}
			};

			// draw options
			var draw_options = {
				// your data goes here
				network: network_json,

				// set the style at initialisation
				visualStyle: visual_style,

				// hide pan zoom
				panZoomControlVisible: false
			};

			vis.draw(draw_options);
		
		}
	
		var term_nodes = [];
		
		var connection_colors = [];

		$('.term_names').change(function() {
			draw_nodes();
		});
		
        $('.list_names').change(function() {
			var value = $(this).val();
			var checked = $(this).prop('checked');
			$('.vectors_names[value='+value+']').prop('checked', checked);
			node_names = $('.list_names:checked').map(function() {
														return this.value;
													}).get();
			$.post( "<?php echo site_url('welcome/get_nodes_connections')?>", { node_names: node_names }).done(function( data ) {
                    nodes_connections = $.parseJSON(data);
					draw_nodes();
			});
		});
		
		document.addEventListener("DOMContentLoaded", function() {
			myHilitor2 = new Hilitor("playground");
			myHilitor2.setMatchType("left");
		}, false);
		
		$('.vectors_names').change(function() {
			var value = $(this).val();
			var checked = $(this).prop('checked');
			
			if($(this).attr('data-type') == 'rank') {
				$('.list_names[value='+value+']').prop('checked', checked);
			} else {
				$('.term_names[value='+value+']').prop('checked', checked);
			}
			$('.list_names:first').trigger('change');
		});

        function init_vars() {
            nodes = [];
            edges = [];
            var i = 1;
						
			$('.vectors_names:checked').each(function() {
				var value = $(this).val();
				if($(this).attr('data-type') == 'rank') {
					$('.list_names[value='+value+']').prop('checked', true);
				} else {
					$('.term_names[value='+value+']').prop('checked', true);
				}
				
			});
			
            var list_names = $('.list_names:checked');
			var range_value = parseFloat($('#connection_range').val());	



			
			
            list_names.each(function() {
                var node_data = {
                    id: i.toString(),
                    label: $(this).val(),
                };
                nodes.push(node_data);
				
                if(i > 1) {
					var is_implicit = (parseFloat($(this).attr('data-dot')) >= range_value);
                    for(var j=1; j<i; j++) {
                        var to = i - j;
						var edge_id = i+'to'+to;
						
						var i_name = node_names[i-1];
						var to_name = node_names[to-1];
						
						var is_explicit = (nodes_connections[i_name].indexOf(to_name) != -1);

						if(is_implicit || is_explicit) {
							var edge_data = {
								id: edge_id,
								target: to.toString(),
								source: i.toString(),
								label: i + ' to '+ to,
							};
							edges.push(edge_data);
							
							if(is_implicit && is_explicit) {
								connection_colors.push({ attrValue: edge_id, value: "#1447ff" });
							} else if(is_explicit) {
								connection_colors.push({ attrValue: edge_id, value: "#04d627" });
							}
							
						}
                    }

                }
                i++;
            });
			
			var is_term_names = $('.term_names').length > 0 ? true : false;
			var limit = i;
			if(is_term_names) {
				$('.term_names:checked').each(function() {
					var node_data = {
						id: i.toString(),
						label: $(this).val(),
					};
					nodes.push(node_data);
					term_nodes.push({ attrValue: i, value: "#138e28" });
					
					
					var is_implicit = (parseFloat($(this).attr('data-dot')) >= range_value);
                    for(var j=1; j<limit; j++) {
                        var to = j;
						var edge_id = i+'to'+to;
						
						if(is_implicit) {
							var edge_data = {
								id: edge_id,
								target: to.toString(),
								source: i.toString(),
								label: i + ' to '+ to,
							};
							edges.push(edge_data);
							connection_colors.push({ attrValue: edge_id, value: "#0fdbe2" });

						}
                    }
					
					
					i++;
				});
			} else {
				i++;
				$('.vectors_names:checked').each(function() {
					var value = $(this).val();
					if($(this).attr('data-type') == 'term') {
						var node_data = {
							id: 'z'+i.toString(),
							label: $(this).val(),
						};
						nodes.push(node_data);
						term_nodes.push({ attrValue: 'z'+i, value: "#00ce25" });
						i++;
					}
					
				});
			}
			
			
			
			
        }

        var nodes = [];
        var edges = [];


        // id of Cytoscape Web container div
        var div_id = "cytoscapeweb";
        // create a network model object
        var network_json = {
            // you need to specify a data schema for custom attributes!
            dataSchema: {
                nodes: [ { name: "label", type: "string" },
                ],
                edges: [ { name: "label", type: "string" },
                ]
            },
            // NOTE the custom attributes on nodes and edges
            data: {
                nodes: nodes,
                edges: edges
            }
        };

        // initialization options
        var options = {
            swfPath: "<?php echo base_url('theme/cyto/swf/CytoscapeWeb')?>",
            flashInstallerPath: "<?php echo base_url('theme/cyto/swf/playerProductInstall')?>"
        };

        var vis = new org.cytoscapeweb.Visualization(div_id, options);

        // callback when Cytoscape Web has finished drawing
        vis.ready(function() {

            // add a listener for when nodes and edges are clicked
            vis.addListener("click", "nodes", function(event) {
                    handle_click(event);
                })
                .addListener("click", "edges", function(event) {
                    handle_click(event);
                })
                .addListener("select", "nodes", function(event) {
                    var list_objects = vis.selected("nodes");
                    if(list_objects.length==1){
                        handle_click(event);
                    }
                    else{
                        handle_select(event);
                    }

                });

            function handle_select(event) {
                $( "#note" ).html( '<h1>Loading ...</h1>' );
                var list_objects = vis.selected("nodes");
                var list_labels = [];
                for(var i=0; i<list_objects.length; i++) {
                    list_labels.push(list_objects[i].data.label)
                }
                $.post( "<?php echo site_url('welcome/get_common_abstracts')?>", { list: list_labels }).done(function( data ) {
                    $( "#note" ).html( data );
                });


            }

            function handle_click(event) {
                var target = event.target;
                $( "#note" ).html( '<h1>Loading ...</h1>' );
                var hsi = target[0].data.label;

                $.get( "<?php echo site_url('welcome/get_abstracts')?>/"+hsi, function( data ) {
                    $( "#note" ).html( data );
                });
            }

            function clear() {
                document.getElementById("note").innerHTML = "";
            }

            function print(msg) {
                document.getElementById("note").innerHTML += "<p>" + msg + "</p>";
            }
			
			
        });


		draw_nodes(true);
		
		$('#connection_range').change(function(){
			var range = $(this).val();
			$('#range_value').text(range);
			draw_nodes();
		});

    };
</script>

<style>
    * { margin: 0; padding: 0; font-family: Helvetica, Arial, Verdana, sans-serif; }
    html, body { height: 100%; width: 100%; padding: 0; margin: 0; }
    body { line-height: 1.5; color: #000000; font-size: 14px; }
    /* The Cytoscape Web container must have its dimensions set. */
    #cytoscapeweb { width: 100%; height: 90%; border:3px solid #135665}
    #note h1 {
        padding: 10px;
        background-color : #688eff;
        font-size: 16px;
    }
    #note {
        padding: 20px;
        border: 2px solid #688eff;
    }
    p { padding: 0 0.5em; margin: 0; }
    p:first-child { padding-top: 0.5em; }
	p.connection_range {
		margin: 10px;
		padding: 10px;
	}
</style>
<script type="text/javascript">
    $(function(){			
	
	
		$('.export_result').click(function(){
			if($(this).attr('data-type') == 'lists') {
				var data = [];
				$('.list_names').each(function() {
					data.push({
						label: $(this).val(),
						dot: $(this).attr('data-dot')
					});
				});
				
				var search = $('#list').val();
				var result = {
					data: data,
					search: search
				}
				var json_data = JSON.stringify(result);
				$('#export_data').val(json_data);
				$('#export_data').closest('form').submit();
			} else {
				var data = [];
				$('.list_names').each(function() {
					data.push({
						label: $(this).val(),
						dot: $(this).attr('data-dot')
					});
				});
				
				var term_data = [];
				$('.term_names').each(function() {
					data.push({
						label: $(this).val(),
						dot: $(this).attr('data-dot')
					});
				});
				
				var search = $('#list').val();
				var result = {
					data: data,
					term_data: data,
					search: search
				}
				var json_data = JSON.stringify(result);
				$('#export_data').val(json_data);
				$('#export_data').closest('form').submit();
			}
		});
		
		$("#list").autocomplete({
			source: function (request, response) {

				var searchid = request.term;

				var mapped;
				$('#search_form').find('.btn_submit').prop('disabled', true);
				$('#rank_search_btn').prop('disabled', false);
				$.ajax({
					url: '<?=site_url('welcome/ajax_search2') ?>/' + searchid,
					dataType : "json",
					success: function(data) {
						if(data.vectors) {
							$('#result').html(data.from);
							if($('#result').find('#from_type').val() == 'lists') {
								$('#search_form').find('.btn_submit').prop('disabled', false);
							}
							mapped = $.map(data.vectors, function (e) {
								return {
									label: e.name,
									value: e.name,
								};
								
							});
							response(mapped);
						} else {
							$('.ui-autocomplete').hide();
						}
						
					}
				});

			},
			select: function(event, ui) {
				$('#stock-exch').val(ui.item.value);
			},
			minLength: 1
		});
		

    });
</script>
