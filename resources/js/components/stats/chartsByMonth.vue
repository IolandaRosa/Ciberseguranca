<script>
	/*jshint esversion: 6 */
	import { Line } from 'vue-chartjs';

	export default {
		props:['l','d1','s1','d2','s2','yString','xString'],
		extends: Line,
		mounted () {
		    // Overwriting base render method with actual data.
		    this.renderChart({
		    	labels: this.l,
		    	datasets: [
		    	{
		    		label: this.s1,
		    		data: this.d1,
		    		borderColor: '#f87979',
		    		fill:false,
		    	},
		    	{
		    		label: this.s2,
		    		data: this.d2,
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