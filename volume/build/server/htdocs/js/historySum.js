function getHistSum(set, stage, id, detail)
{
    // if the canvas already contains a graph, there is
    // no need to redraw.
    if ($("#mycanvas_"+id).hasClass('chartjs-render-monitor')) {
        return;
    }

    $.ajax({
            url: "/ajax/historySum.php?set="+set+"&stage="+stage+"&detail="+detail,
            method: "GET",
            dataType: 'json',
            success: function(data) {
                    var chartdata = {
                            labels: data.labels,
                            datasets: data.datasets
                    };

                    var ctx = $("#mycanvas_"+id);

                    Chart.scaleService.updateScaleDefaults('linear', {
                        ticks: {
                            min: 0,
                            max: 100,
							callback: function(value) {
								return value + "%"
							}
						},
						scaleLabel: {
           					display: true,
				        	labelString: "Percentage"
					    }
                    });

                    var barGraph = new Chart(ctx, {
                            type: 'line',
                            data: chartdata,
                            options: {
                                elements: {
                                    line: {
                                        tension: 0, // disables bezier curves
                                        fill: false
                                    }
                                },
						  		tooltips: {
							    	callbacks: {
							    		label: function(tooltipItem, data) {
											var dataset = data.datasets[tooltipItem.datasetIndex];
											var currentValue = dataset.data[tooltipItem.index]
											return dataset.label + ': ' + currentValue + '%';
										}
									}
								}
							}
                    });
            },
            error: function(data) {
                    console.log('Error!');
                    console.log(data);
            }
    });
}
