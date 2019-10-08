<script>
	/*jshint esversion: 6 */
	import { Line } from 'vue-chartjs';

	export default {
		props:['l','d','s','yString','xString'],
		extends: Line,
		mounted () {
		    // Overwriting base render method with actual data.
		    this.renderChart({
		    	labels: this.l,
		    	datasets: [
		    	{
		    		label: this.s,
		    		data: this.d,
		    		borderColor: '#074080',
		    		fill:false,
		    	}
		    	]
		    },{
		    	responsive: true,
		    	maintainAspectRatio: false,
		    	scales: {
		    		xAxes: [{
		    			display: true,
		    			scaleLabel: {
		    				display: true,
		    				labelString: this.xString,
		    			}
		    		}],
		    		yAxes: [{
		    			display: true,
		    			scaleLabel: {
		    				display: true,
		    				labelString: this.yString,
		    			}
		    		}]
		    	}

		    });
		},

	};
</script>