<template>
	<div>
		<template> 
			<div>
				<vue-good-table :columns="columns" :rows="items" :pagination-options="{ enabled: true, perPage: 5}">
					<template slot="table-row" slot-scope="props">
						{{props.formattedRow[props.column.field]}}
					</template>
				</vue-good-table>
			</div>  
		</template>
	</div>
</template>

<script type="text/javascript">
	/*jshint esversion: 6 */

	export default {
		props:['items'],
		data:
		function() {
			return {
				columns: [
				{
					label: 'Id',
					field: 'id',
					type:'number',
				},
				{
					label: 'Name', 
					field: 'name',
				}, {
					label: 'Type', 
					field: 'type',
				}, {
					label: 'Price',
					field: 'price',
					type:'number',
				},
				{
					label: 'Order Id', 
					field: 'order_id',
					type:'number',
				}, {
					label: 'Order State', 
					field: 'order_state',
				},
				],
			};
		},
	};
</script>